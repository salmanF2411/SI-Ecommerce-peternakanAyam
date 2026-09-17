<?php
require __DIR__ . '/config.php';

$page = $_GET['page'] ?? 'home';
$action = $_POST['action'] ?? null;

if ($action === 'register') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        flash('danger', 'Lengkapi data dengan email valid dan password minimal 6 karakter.');
        redirect('?page=register');
    }
    try {
        $stmt = db()->prepare('INSERT INTO users (name,email,phone,address,password) VALUES (?,?,?,?,?)');
        $stmt->execute([$name, $email, trim($_POST['phone'] ?? ''), trim($_POST['address'] ?? ''), password_hash($password, PASSWORD_DEFAULT)]);
        flash('success', 'Akun berhasil dibuat. Silakan login.');
        redirect('?page=login');
    } catch (PDOException $error) {
        flash('danger', 'Email sudah terdaftar.');
        redirect('?page=register');
    }
}
if ($action === 'login') {
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([trim($_POST['email'] ?? '')]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
        $_SESSION['user'] = $user;
        unset($_SESSION['cart']);
        $_SESSION['cart'] = getCart();
        logActivity('Login ke sistem');
        flash('success', 'Selamat datang kembali, ' . $user['name'] . '.');
        redirect($user['role'] === 'customer' ? '?page=home' : '?page=dashboard');
    }
    flash('danger', 'Email atau password tidak sesuai.');
    redirect('?page=login');
}
if ($action === 'logout') {
    logActivity('Logout dari sistem');
    unset($_SESSION['user']);
    unset($_SESSION['cart']);
    redirect('?page=home');
}
if ($action === 'add_cart') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    $user = currentUser();
    if (!$user) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'ok'             => false,
                'requires_login' => true,
                'message'        => 'Silakan masuk (login) ke akun Anda terlebih dahulu untuk menambahkan produk ke keranjang.',
                'redirect'       => '?page=login',
            ]);
            exit;
        }
        flash('warning', 'Silakan masuk (login) ke akun Anda terlebih dahulu untuk menambahkan produk ke keranjang.');
        redirect('?page=login');
    }

    $id       = (int) ($_POST['product_id'] ?? 0);
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

    $stmt = db()->prepare('SELECT * FROM products WHERE id = ? AND is_active = 1');
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product || (int) $product['stock'] <= 0) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'ok'      => false,
                'message' => 'Produk sedang habis atau tidak tersedia.',
            ]);
            exit;
        }
        flash('danger', 'Produk sedang habis.');
        redirect($_POST['redirect'] ?? ('?page=product&id=' . $id));
    }

    $cart       = getCart();
    $currentQty = (int) ($cart[$id] ?? 0);
    $maxStock   = (int) $product['stock'];
    $newQty     = min($currentQty + $quantity, $maxStock);
    $cart[$id]  = $newQty;
    saveCart($cart);

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'ok'           => true,
            'message'      => 'Berhasil menambahkan ' . $product['name'] . ' ke keranjang.',
            'product_name' => $product['name'],
            'added_qty'    => $quantity,
            'total_qty'    => $newQty,
            'cartCount'    => cartCount(),
            'cartTotal'    => money(cartTotal()),
        ]);
        exit;
    }

    flash('success', 'Produk masuk ke keranjang.');
    $redirectUrl = !empty($_POST['redirect']) && $_POST['redirect'] !== '?page=cart'
        ? $_POST['redirect']
        : ('?page=product&id=' . $id);
    redirect($redirectUrl);
}
if ($action === 'update_cart') {
    $user = currentUser();
    if (!$user) {
        flash('warning', 'Silakan masuk ke akun Anda terlebih dahulu.');
        redirect('?page=login');
    }
    $cart = getCart();
    foreach ($_POST['quantity'] ?? [] as $id => $qty) {
        $id  = (int) $id;
        $qty = (int) $qty;
        if ($qty > 0) $cart[$id] = $qty;
        else unset($cart[$id]);
    }
    saveCart($cart);
    flash('success', 'Keranjang diperbarui.');
    redirect('?page=cart');
}
if ($action === 'remove_cart') {
    $user = currentUser();
    if (!$user) {
        flash('warning', 'Silakan masuk ke akun Anda terlebih dahulu.');
        redirect('?page=login');
    }
    $id   = (int) ($_POST['product_id'] ?? 0);
    $cart = getCart();
    unset($cart[$id]);
    saveCart($cart);
    flash('success', 'Produk dihapus dari keranjang.');
    redirect('?page=cart');
}
if ($action === 'set_cart_qty') {
    $user = currentUser();
    if (!$user) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => 'Login required']);
            exit;
        }
        redirect('?page=login');
    }
    $id   = (int) ($_POST['product_id'] ?? 0);
    $qty  = (int) ($_POST['quantity']   ?? 0);
    $cart = getCart();
    if ($qty > 0) {
        // Validate stock
        $stmt = db()->prepare('SELECT stock FROM products WHERE id = ? AND is_active = 1');
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        if ($p) {
            $cart[$id] = min($qty, (int) $p['stock']);
        }
    } else {
        unset($cart[$id]);
    }
    saveCart($cart);
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        $newQty   = $cart[$id] ?? 0;
        $items    = cartItems();
        $subtotal = 0;
        foreach ($items as $item) {
            if ((int)$item['product']['id'] === $id) {
                $subtotal = $item['subtotal'];
            }
        }
        echo json_encode([
            'ok'         => true,
            'quantity'   => $newQty,
            'subtotal'   => 'Rp ' . number_format($subtotal, 0, ',', '.'),
            'cartTotal'  => 'Rp ' . number_format(cartTotal(), 0, ',', '.'),
            'cartCount'  => cartCount(),
        ]);
        exit;
    }
    redirect('?page=cart');
}
if ($action === 'checkout') {
    $user = requireLogin();
    $items = cartItems();
    if (!$items) {
        flash('danger', 'Keranjang masih kosong.');
        redirect('?page=cart');
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        foreach ($items as $item) if ($item['quantity'] > $item['product']['stock']) throw new RuntimeException('Stok ' . $item['product']['name'] . ' tidak mencukupi.');
        $code = 'RP-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -5));
        $stmt = $pdo->prepare('INSERT INTO orders (order_code,user_id,customer_name,phone,address,payment_method,notes,total) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([$code, $user['id'], trim($_POST['customer_name']), trim($_POST['phone']), trim($_POST['address']), $_POST['payment_method'], trim($_POST['notes'] ?? ''), cartTotal()]);
        $orderId = $pdo->lastInsertId();
        $detail = $pdo->prepare('INSERT INTO order_details (order_id,product_id,quantity,price,subtotal) VALUES (?,?,?,?,?)');
        foreach ($items as $item) $detail->execute([$orderId, $item['product']['id'], $item['quantity'], $item['product']['price'], $item['subtotal']]);
        $pdo->commit();
        clearCart();
        logActivity('Membuat pesanan ' . $code);
        flash('success', 'Pesanan berhasil dibuat dan menunggu konfirmasi admin.');
        redirect('?page=orders');
    } catch (Throwable $error) {
        $pdo->rollBack();
        flash('danger', $error->getMessage());
        redirect('?page=checkout');
    }
}
if ($action === 'update_order' && in_array((currentUser()['role'] ?? ''), ['admin', 'owner'], true)) {
    $user = requireStaff();
    $status = $_POST['status'];
    $orderId = (int) $_POST['order_id'];
    if ($user['role'] === 'owner') {
        flash('warning', 'Owner hanya memiliki akses pemantauan.');
        redirect('?page=dashboard');
    }
    db()->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $orderId]);
    if ($status === 'Diproses') {
        $details = db()->prepare('SELECT * FROM order_details WHERE order_id = ?');
        $details->execute([$orderId]);
        foreach ($details->fetchAll() as $detail) {
            db()->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?')->execute([$detail['quantity'], $detail['product_id'], $detail['quantity']]);
            db()->prepare("INSERT INTO stock_transactions (product_id,user_id,type,quantity,note) VALUES (?,?, 'Keluar', ?, ?)")->execute([$detail['product_id'], $user['id'], $detail['quantity'], 'Pesanan #' . $orderId]);
        }
    }
    logActivity('Memperbarui status pesanan #' . $orderId . ' menjadi ' . $status);
    flash('success', 'Status pesanan diperbarui.');
    redirect('?page=dashboard&tab=orders');
}
if ($action === 'save_product' && currentUser() && currentUser()['role'] === 'admin') {
    // Handle file upload
    $imageValue = trim($_POST['image_current'] ?? ''); // gambar lama (saat edit)
    if (!empty($_FILES['image_file']['name'])) {
        $uploaded = uploadProductImage($_FILES['image_file']);
        if ($uploaded) {
            $imageValue = $uploaded;
        }
    }

    $name        = trim($_POST['name']);
    $category_id = (int) $_POST['category_id'];
    $price       = (float) $_POST['price'];
    $stock       = (int) $_POST['stock'];
    $unit        = trim($_POST['unit']);
    $description = trim($_POST['description']);
    $is_active   = isset($_POST['is_active']) ? 1 : 0;

    if (!empty($_POST['id'])) {
        db()->prepare('UPDATE products SET name=?,category_id=?,price=?,stock=?,unit=?,image=?,description=?,is_active=? WHERE id=?')
            ->execute([$name, $category_id, $price, $stock, $unit, $imageValue, $description, $is_active, (int) $_POST['id']]);
        logActivity('Mengedit produk ' . $name);
    } else {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-')) . '-' . time();
        db()->prepare('INSERT INTO products (name,category_id,price,stock,unit,image,description,is_active,slug) VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([$name, $category_id, $price, $stock, $unit, $imageValue, $description, $is_active, $slug]);
        logActivity('Menambah produk ' . $name);
    }
    flash('success', 'Produk berhasil disimpan.');
    redirect('?page=dashboard&tab=products');
}
if ($action === 'delete_product' && currentUser() && currentUser()['role'] === 'admin') {
    db()->prepare('UPDATE products SET is_active = 0 WHERE id = ?')->execute([(int) $_POST['id']]);
    logActivity('Menonaktifkan produk #' . $_POST['id']);
    flash('success', 'Produk dinonaktifkan.');
    redirect('?page=dashboard&tab=products');
}
if ($action === 'activate_product' && currentUser() && currentUser()['role'] === 'admin') {
    db()->prepare('UPDATE products SET is_active = 1 WHERE id = ?')->execute([(int) $_POST['id']]);
    logActivity('Mengaktifkan kembali produk #' . $_POST['id']);
    flash('success', 'Produk berhasil diaktifkan kembali.');
    redirect('?page=dashboard&tab=products');
}

$flash = consumeFlash();
$user = currentUser();
function layoutStart(string $title, ?array $user = null): void
{
    global $flash, $page; ?>
    <!doctype html>
    <html lang="id">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> | Rumah Pitik</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="stylesheet" href="assets/style.css?v=<?= filemtime(__DIR__ . '/assets/style.css') ?>">
        <style>
            .brand-mark {
                overflow: hidden;
            }

            .brand-mark img {
                width: 100%;
                height: 100%;
                object-fit: contain;
                display: block;
            }
            /* ── Status Badges Pesanan (Warna Jelas & Kontras) ── */
            .status-badge, .status {
                display: inline-flex !important;
                align-items: center !important;
                gap: 6px !important;
                padding: 4px 12px !important;
                border-radius: 20px !important;
                font-size: 11px !important;
                font-weight: 700 !important;
                letter-spacing: .3px !important;
                white-space: nowrap !important;
                line-height: 1.4 !important;
                text-align: center !important;
            }

            .status-badge::before, .status::before {
                content: '' !important;
                display: inline-block !important;
                width: 7px !important;
                height: 7px !important;
                border-radius: 50% !important;
                background: currentColor !important;
                opacity: .95 !important;
                flex-shrink: 0 !important;
            }

            /* 1. Menunggu — Kuning / Amber Hangat */
            .status-menunggu,
            .status.menunggu,
            .status-badge.status-menunggu,
            .status-badge.menunggu,
            [class*="menunggu"] {
                background: #fef3c7 !important;
                color: #92400e !important;
                border: 1.5px solid #f59e0b !important;
            }

            /* 2. Dikonfirmasi — Biru Jelas */
            .status-dikonfirmasi,
            .status.dikonfirmasi,
            .status-badge.status-dikonfirmasi,
            .status-badge.dikonfirmasi,
            [class*="dikonfirmasi"] {
                background: #dbeafe !important;
                color: #1e40af !important;
                border: 1.5px solid #3b82f6 !important;
            }

            /* 3. Diproses — Ungu / Violet */
            .status-diproses,
            .status.diproses,
            .status-badge.status-diproses,
            .status-badge.diproses,
            [class*="diproses"] {
                background: #ede9fe !important;
                color: #5b21b6 !important;
                border: 1.5px solid #8b5cf6 !important;
            }

            /* 4. Selesai — Hijau Segar */
            .status-selesai,
            .status.selesai,
            .status-badge.status-selesai,
            .status-badge.selesai,
            [class*="selesai"] {
                background: #dcfce7 !important;
                color: #166534 !important;
                border: 1.5px solid #22c55e !important;
            }

            /* 5. Dibatalkan — Merah / Rose */
            .status-dibatalkan,
            .status.dibatalkan,
            .status-badge.status-dibatalkan,
            .status-badge.dibatalkan,
            [class*="dibatalkan"] {
                background: #fee2e2 !important;
                color: #991b1b !important;
                border: 1.5px solid #ef4444 !important;
            }
        </style>
        <?php if (!$user || $user['role'] === 'customer'): ?>
            <style>
                body.customer-page {
                    min-height: 100vh;
                    display: flex;
                    flex-direction: column;
                }

                body.customer-page .page-wrap {
                    flex: 1 0 auto;
                    width: 100%;
                }

                body.customer-page .site-header nav a.active {
                    color: var(--green);
                    position: relative;
                }

                body.customer-page .site-header nav a.active::after {
                    content: '';
                    position: absolute;
                    left: 14px;
                    right: 14px;
                    bottom: 1px;
                    height: 2px;
                    border-radius: 2px;
                    background: var(--green);
                }
            </style>
        <?php endif; ?>
        <?php if ($user && $user['role'] !== 'customer'): ?>
            <style>
                body.staff-page .sidebar nav a.active {
                    color: #fff;
                    background: rgba(255, 255, 255, .14);
                    box-shadow: inset 3px 0 0 #c8ddb7;
                }
            </style>
        <?php endif; ?>
        <script defer src="assets/app.js"></script>
    </head>

    <body class="<?= $user && $user['role'] !== 'customer' ? 'staff-page' : 'customer-page' ?>">
    <?php if ($user && $user['role'] !== 'customer'): ?>
        <aside class="sidebar">
            <a class="brand" href="?page=dashboard">
                <span class="brand-mark"><img src="assets/image/logo.png" alt="Logo Rumah Pitik"></span>
                <span>Rumah<br><b>Pitik</b></span>
            </a>
            <small>RUANG KERJA <?= strtoupper($user['role']) ?></small>
            <?php $adminTab = $_GET['tab'] ?? 'overview'; ?>
            <nav>
                <a class="<?= $page === 'dashboard' && $adminTab === 'overview' ? 'active' : '' ?>" href="?page=dashboard">⌂ Dashboard</a>
                <a class="<?= $page === 'dashboard' && $adminTab === 'products' ? 'active' : '' ?>" href="?page=dashboard&tab=products">▦ Produk</a>
                <a class="<?= $page === 'dashboard' && $adminTab === 'orders' ? 'active' : '' ?>" href="?page=dashboard&tab=orders">◷ Pesanan</a>
                <a class="<?= $page === 'dashboard' && $adminTab === 'reports' ? 'active' : '' ?>" href="?page=dashboard&tab=reports">▤ Laporan</a>
                <a class="<?= $page === 'dashboard' && $adminTab === 'logs' ? 'active' : '' ?>" href="?page=dashboard&tab=logs">◌ Aktivitas</a>
            </nav>
            <form method="post"><input type="hidden" name="action" value="logout"><button class="nav-logout">Keluar</button></form>
        </aside>
        <main class="app-main">
            <header class="app-header">
                <span><?= date('l, d F Y') ?></span>
                <strong><?= e($user['name']) ?></strong>
            </header>
    <?php else: ?>
        <header class="site-header">
            <a class="brand" href="?page=home">
                <span class="brand-mark"><img src="assets/image/logo.png" alt="Logo Rumah Pitik"></span>
                <span>Rumah <b>Pitik</b></span>
            </a>
            <button class="menu-toggle">Menu</button>
            <nav>
                <a class="<?= $page === 'home' ? 'active' : '' ?>" href="?page=home">Beranda</a>
                <a class="<?= $page === 'products' ? 'active' : '' ?>" href="?page=products">Produk</a>
                <a class="<?= $page === 'about' ? 'active' : '' ?>" href="?page=about">Tentang Kami</a>
                <a href="?page=home#how">Cara Pesan</a>
                <?php if ($user): ?>
                    <a class="<?= $page === 'orders' ? 'active' : '' ?>" href="?page=orders">Pesanan Saya</a>
                    <form method="post"><input type="hidden" name="action" value="logout"><button class="link-button">Keluar</button></form>
                <?php else: ?>
                    <a href="?page=login">Masuk</a>
                <?php endif; ?>
                <a class="cart-link <?= $page === 'cart' ? 'active' : '' ?>" href="?page=cart">🛒 <span><?= cartCount() ?></span></a>
            </nav>
        </header>
    <?php endif; ?>

    <div class="page-wrap">
        <?php if ($flash): ?>
            <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif;
}

function layoutEnd(?array $user = null): void
{
    if ($user && $user['role'] !== 'customer') {
        echo '</div></main>';
    } else {
        ?>
        </div>
        <footer>
            <div>
                <a class="brand" href="?page=home">
                    <span class="brand-mark"><img src="assets/image/logo.png" alt="Logo Rumah Pitik"></span>
                    <span>Rumah <b>Pitik</b></span>
                </a>
                <p>Produk segar dari peternakan dan kebun<br>untuk keluarga Indonesia.</p>
            </div>
            <div>
                <b>Jelajahi</b>
                <a href="?page=products">Katalog produk</a>
                <a href="?page=about">Tentang Kami</a>
                <a href="?page=home#how">Cara pesan</a>
            </div>
            <div>
                <b>Hubungi kami</b>
                <span>WhatsApp 0812-3456-7890</span>
                <span>Senin - Sabtu, 08.00 - 17.00</span>
            </div>
        </footer>
        <?php
    }
    echo '</body></html>';
}

if ($page === 'login' || $page === 'register') {
    layoutStart($page === 'login' ? 'Masuk' : 'Daftar');
    ?>
    <section class="auth-shell">
        <div class="auth-copy">
            <span class="eyebrow">RUMAH PITIK</span>
            <h1><?= $page === 'login' ? 'Selamat datang kembali.' : 'Mulai belanja lebih mudah.' ?></h1>
            <p>Akses katalog segar, pesanan, dan informasi stok dalam satu tempat.</p>
        </div>
        <form class="auth-card" method="post">
            <h2><?= $page === 'login' ? 'Masuk ke akun' : 'Buat akun pelanggan' ?></h2>
            <input type="hidden" name="action" value="<?= $page ?>">
            <?php if ($page === 'register'): ?>
                <label>Nama lengkap<input required name="name"></label>
                <label>Nomor telepon<input required name="phone"></label>
                <label>Alamat<textarea required name="address" rows="3"></textarea></label>
            <?php endif; ?>
            <label>Email<input required type="email" name="email"></label>
            <label>Password<input required type="password" name="password"></label>
            <button class="button primary wide"><?= $page === 'login' ? 'Masuk' : 'Daftar sekarang' ?></button>
            <p class="form-note">
                <?= $page === 'login'
                    ? 'Belum punya akun? <a href="?page=register">Daftar di sini</a>'
                    : 'Sudah punya akun? <a href="?page=login">Masuk</a>' ?>
            </p>
        </form>
    </section>
    <?php
    layoutEnd();
    exit;
}

                                                                                                                                                                            if ($page === 'about') {
                                                                                                                                                                                include __DIR__ . '/views/customer/tentang-kami.php';
                                                                                                                                                                                exit;
                                                                                                                                                                            }

                                                                                                                                                                            if ($page === 'products' || $page === 'home') {
                                                                                                                                                                                $categories = db()->query('SELECT * FROM categories')->fetchAll();
                                                                                                                                                                                $search = trim($_GET['search'] ?? '');
                                                                                                                                                                                $category = trim($_GET['category'] ?? '');
                                                                                                                                                                                $sql = 'SELECT p.*, c.name category_name FROM products p JOIN categories c ON c.id=p.category_id WHERE p.is_active=1';
                                                                                                                                                                                $params = [];
                                                                                                                                                                                if ($search) {
                                                                                                                                                                                    $sql .= ' AND p.name LIKE ?';
                                                                                                                                                                                    $params[] = '%' . $search . '%';
                                                                                                                                                                                }
                                                                                                                                                                                if ($category) {
                                                                                                                                                                                    $sql .= ' AND c.slug = ?';
                                                                                                                                                                                    $params[] = $category;
                                                                                                                                                                                }
                                                                                                                                                                                $sql .= ' ORDER BY p.id DESC';
                                                                                                                                                                                $stmt = db()->prepare($sql);
                                                                                                                                                                                $stmt->execute($params);
                                                                                                                                                                                $products = $stmt->fetchAll();
                                                                                                                                                                                layoutStart('Produk', $user);
                                                                                                                                                                                if ($page === 'home'): ?><section class="hero">
                            <div class="hero-copy"><span class="eyebrow">DARI KANDANG, UNTUK KELUARGA</span>
                                <h1>Segar, dekat,<br><em>dan apa adanya.</em></h1>
                                <p>Ayam, telur, dan pupuk pilihan dari Rumah Pitik. Dikelola dengan perhatian, dikirim untuk kebutuhan sehari-hari.</p><a class="button primary" href="?page=products">Jelajahi produk <span>→</span></a>
                            </div>
                            <div class="hero-image"><img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?auto=format&fit=crop&w=1200&q=85" alt="Peternakan ayam"></div>
                        </section>
                        <section class="intro" id="about">
                            <div><span class="eyebrow">KENAPA RUMAH PITIK</span>
                                <h2>Yang baik dimulai dari cara yang baik.</h2>
                            </div>
                            <p>Kami merawat produk dari sumbernya agar sampai di meja Anda dalam kondisi terbaik. Sederhana, jujur, dan konsisten.</p>
                        </section>
                        <section class="category-grid">
                            <div class="section-heading">
                                <div><span class="eyebrow">PILIH SESUAI KEBUTUHAN</span>
                                    <h2>Kategori produk</h2>
                                </div><a href="?page=products">Lihat semua →</a>
                            </div>
                            <div class="category-items"><?php foreach ($categories as $cat): ?><a href="?page=products&category=<?= e($cat['slug']) ?>"><span><?= $cat['name'] === 'Pupuk' ? '✦' : ($cat['name'] === 'Telur' ? '◒' : '♧') ?></span><b><?= e($cat['name']) ?></b><small>Jelajahi pilihan →</small></a><?php endforeach; ?></div>
                        </section>
                        <section class="product-section">
                            <div class="section-heading">
                                <div><span class="eyebrow">PILIHAN MINGGU INI</span>
                                    <h2>Produk favorit</h2>
                                </div>
                            </div>
                            <div class="product-grid"><?php foreach (array_slice($products, 0, 4) as $product) {
                                                                                                                                                                                        include __DIR__ . '/product-card.php';
                                                                                                                                                                                    } ?></div>
                        </section>
                        <section class="steps" id="how"><span class="eyebrow">BELANJA TANPA RIBET</span>
                            <h2>Empat langkah menuju<br>pesanan Anda.</h2>
                            <div class="step-grid">
                                <div><b>01</b>
                                    <h3>Pilih produk</h3>
                                    <p>Temukan kebutuhan Anda dari katalog kami.</p>
                                </div>
                                <div><b>02</b>
                                    <h3>Masukkan keranjang</h3>
                                    <p>Atur jumlah sesuai kebutuhan.</p>
                                </div>
                                <div><b>03</b>
                                    <h3>Checkout</h3>
                                    <p>Isi alamat dan metode pembayaran.</p>
                                </div>
                                <div><b>04</b>
                                    <h3>Kami proses</h3>
                                    <p>Admin mengonfirmasi pesanan Anda.</p>
                                </div>
                            </div>
                        </section><?php else: ?><section class="catalog-head"><span class="eyebrow">KATALOG PRODUK</span>
                            <h1>Temukan yang Anda<br><em>butuhkan hari ini.</em></h1>
                            <form class="search-form"><input name="search" value="<?= e($search) ?>" placeholder="Cari ayam, telur, pupuk..."><input type="hidden" name="page" value="products"><button class="button dark">Cari</button></form>
                        </section>
                        <div class="filter-row"><a class="<?= !$category ? 'active' : '' ?>" href="?page=products">Semua</a><?php foreach ($categories as $cat): ?><a class="<?= $category === $cat['slug'] ? 'active' : '' ?>" href="?page=products&category=<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></a><?php endforeach; ?></div>
                        <div class="product-grid catalog-grid"><?php foreach ($products as $product) {
                                                                                                                                                                                        include __DIR__ . '/product-card.php';
                                                                                                                                                                                    } ?></div><?php endif;
                                                                                                                                                                                            layoutEnd($user);
                                                                                                                                                                                            exit;
                                                                                                                                                                                        }

                                                                                                                                                                                        if ($page === 'product') {
    $stmt = db()->prepare('SELECT p.*, c.name category_name FROM products p JOIN categories c ON c.id=p.category_id WHERE p.id=? AND p.is_active=1');
    $stmt->execute([(int) $_GET['id']]);
    $product = $stmt->fetch();
    if (!$product) redirect('?page=products');

    layoutStart($product['name'], $user);
    ?>
    <section class="detail">
        <div class="detail-image">
            <img src="<?= e(productImage($product['image'])) ?>" alt="<?= e($product['name']) ?>">
        </div>
        <div class="detail-copy">
            <span class="eyebrow"><?= e($product['category_name']) ?></span>
            <h1><?= e($product['name']) ?></h1>
            <strong class="price-large"><?= money($product['price']) ?> <small>/ <?= e($product['unit']) ?></small></strong>
            <p><?= e($product['description']) ?></p>
            <div class="stock-line <?= $product['stock'] ? '' : 'out' ?>">
                ● <?= $product['stock'] ? 'Stok tersedia: ' . $product['stock'] . ' ' . $product['unit'] : 'Stok habis' ?>
            </div>
            <?php if ($product['stock']): ?>
                <form method="post" class="buy-form js-buy-form">
                    <input type="hidden" name="action" value="add_cart">
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <input type="hidden" name="redirect" value="?page=product&id=<?= (int) $product['id'] ?>">
                    <div class="detail-qty-control">
                        <button type="button" class="btn-qty-detail" data-action="minus" aria-label="Kurang">&minus;</button>
                        <input type="number" name="quantity" class="qty-detail-input" value="1" min="1" max="<?= (int) $product['stock'] ?>">
                        <button type="button" class="btn-qty-detail" data-action="plus" aria-label="Tambah">+</button>
                    </div>
                    <button type="submit" class="button primary btn-add-cart">Tambah ke keranjang →</button>
                </form>
            <?php endif; ?>
        </div>
    </section>
    <?php
    layoutEnd($user);
    exit;
}

                                                                                                                                                                                        if ($page === 'cart' || $page === 'checkout' || $page === 'orders') {
                                                                                                                                                                                            $user = requireLogin();
                                                                                                                                                                                            if ($page === 'orders') {
                                                                                                                                                                                                $stmt = db()->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC');
                                                                                                                                                                                                $stmt->execute([$user['id']]);
                                                                                                                                                                                                $orders = $stmt->fetchAll();
                                                                                                                                                                                                layoutStart('Pesanan Saya', $user); ?><section class="content-head"><span class="eyebrow">AKUN PELANGGAN</span>
                            <h1>Pesanan saya</h1>
                            <p>Pantau perjalanan pesanan Anda dari sini.</p>
                        </section>
                        <div class="order-list"><?php foreach ($orders as $order): ?><article class="order-row">
                                    <div><b><?= e($order['order_code']) ?></b><small><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></small></div><strong><?= money($order['total']) ?></strong><span class="status status-badge <?= statusClass($order['status']) ?> <?= strtolower(trim($order['status'])) ?>"><?= e($order['status']) ?></span>
                                </article><?php endforeach;
                                                                                                                                                                                                if (!$orders): ?><div class="empty">Belum ada pesanan. <a href="?page=products">Mulai belanja →</a></div><?php endif; ?></div><?php layoutEnd($user);
                                                                                                                                                                                                                                                                                                                                exit;
                                                                                                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                                                                                                            $items = cartItems();
                                                                                                                                                                                                                                                                                                                            layoutStart($page === 'cart' ? 'Keranjang' : 'Checkout', $user); ?><section class="content-head"><span class="eyebrow"><?= $page === 'cart' ? 'KERANJANG BELANJA' : 'LANGKAH TERAKHIR' ?></span>
                        <h1><?= $page === 'cart' ? 'Keranjang Anda' : 'Konfirmasi pesanan' ?></h1>
</section><?php if ($page === 'cart'): ?>
                        <div class="cart-layout" id="cartLayout">
                            <div class="cart-items" id="cartItems">
                                <?php if ($items): ?>
                                    <?php foreach ($items as $item): ?>
                                        <article class="cart-item" id="cart-row-<?= $item['product']['id'] ?>">
                                            <div class="cart-item-img">
                                                <img src="<?= e(productImage($item['product']['image'])) ?>" alt="<?= e($item['product']['name']) ?>">
                                            </div>
                                            <div class="cart-item-info">
                                                <div class="cart-item-name"><?= e($item['product']['name']) ?></div>
                                                <div class="cart-item-meta">
                                                    <span class="cart-item-category"><?= e($item['product']['category_name'] ?? '') ?></span>
                                                    <span class="cart-unit-price"><?= money($item['product']['price']) ?> / <?= e($item['product']['unit']) ?></span>
                                                </div>
                                                <div class="cart-item-stock">Stok: <?= (int)$item['product']['stock'] ?> <?= e($item['product']['unit']) ?></div>
                                            </div>
                                            <div class="cart-item-qty">
                                                <div class="qty-stepper" data-product-id="<?= $item['product']['id'] ?>" data-max="<?= (int)$item['product']['stock'] ?>">
                                                    <button type="button" class="qty-btn qty-minus" aria-label="Kurang">&minus;</button>
                                                    <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" min="1" max="<?= (int)$item['product']['stock'] ?>" readonly>
                                                    <button type="button" class="qty-btn qty-plus" aria-label="Tambah">+</button>
                                                </div>
                                            </div>
                                            <div class="cart-item-subtotal">
                                                <span class="cart-subtotal-label">Subtotal</span>
                                                <strong class="cart-subtotal-value" id="subtotal-<?= $item['product']['id'] ?>"><?= money($item['subtotal']) ?></strong>
                                            </div>
                                            <div class="cart-item-remove">
                                                <form method="post" class="remove-form" data-product-id="<?= $item['product']['id'] ?>">
                                                    <input type="hidden" name="action" value="remove_cart">
                                                    <input type="hidden" name="product_id" value="<?= $item['product']['id'] ?>">
                                                    <button type="submit" class="btn-remove" title="Hapus produk ini" aria-label="Hapus">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="cart-empty" id="cartEmpty">
                                        <div class="cart-empty-icon">&#x1F6D2;</div>
                                        <h3>Keranjang Anda kosong</h3>
                                        <p>Temukan produk segar pilihan kami dan tambahkan ke keranjang.</p>
                                        <a href="?page=products" class="button primary">Mulai Belanja &#8594;</a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <aside class="summary" id="cartSummary">
                                <h3>Ringkasan Belanja</h3>
                                <div class="summary-row">
                                    <span>Subtotal</span>
                                    <b id="summaryTotal"><?= money(cartTotal()) ?></b>
                                </div>
                                <div class="summary-row">
                                    <span>Pengiriman</span>
                                    <b>Dikonfirmasi admin</b>
                                </div>
                                <hr>
                                <div class="summary-row total">
                                    <span>Total</span>
                                    <b id="summaryGrand"><?= money(cartTotal()) ?></b>
                                </div>
                                <div class="summary-item-count">
                                    <span id="summaryCount"><?= cartCount() ?></span> item dalam keranjang
                                </div>
                                <a href="?page=checkout" class="button primary wide<?= !$items ? ' disabled-btn' : '' ?>" id="btnCheckout"<?= !$items ? ' aria-disabled="true" tabindex="-1"' : '' ?>>
                                    Lanjut ke Checkout &#8594;
                                </a>
                                <a href="?page=products" class="button dark wide center" style="margin-top:8px;">Lanjut Belanja</a>
                            </aside>
                        </div>

                        <script>
                        (function() {
                            var BASE = '<?= BASE_URL ?>/index.php';

                            function post(data, onSuccess) {
                                var fd = new FormData();
                                Object.keys(data).forEach(function(k) { fd.append(k, data[k]); });
                                fetch(BASE, {
                                    method: 'POST',
                                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                                    body: fd
                                })
                                .then(function(r) { return r.json(); })
                                .then(function(res) { if (res.ok) onSuccess(res); })
                                .catch(function() { window.location.href = '?page=cart'; });
                            }

                            function updateSummary(res) {
                                var total = document.getElementById('summaryTotal');
                                var grand = document.getElementById('summaryGrand');
                                var count = document.getElementById('summaryCount');
                                var badge = document.querySelector('.cart-link span');
                                var btn   = document.getElementById('btnCheckout');
                                if (total) total.textContent = res.cartTotal;
                                if (grand) grand.textContent = res.cartTotal;
                                if (count) count.textContent = res.cartCount;
                                if (badge) badge.textContent = res.cartCount;
                                if (btn) {
                                    if (res.cartCount === 0) {
                                        btn.classList.add('disabled-btn');
                                        btn.setAttribute('aria-disabled', 'true');
                                        btn.setAttribute('tabindex', '-1');
                                    } else {
                                        btn.classList.remove('disabled-btn');
                                        btn.removeAttribute('aria-disabled');
                                        btn.removeAttribute('tabindex');
                                    }
                                }
                            }

                            function checkEmpty() {
                                var rows = document.querySelectorAll('.cart-item[id^="cart-row-"]');
                                if (rows.length === 0) {
                                    var ci = document.getElementById('cartItems');
                                    ci.innerHTML = '<div class="cart-empty" id="cartEmpty"><div class="cart-empty-icon">&#x1F6D2;</div><h3>Keranjang Anda kosong</h3><p>Temukan produk segar pilihan kami dan tambahkan ke keranjang.</p><a href="?page=products" class="button primary">Mulai Belanja</a></div>';
                                }
                            }

                            document.querySelectorAll('.qty-stepper').forEach(function(stepper) {
                                var pid  = stepper.dataset.productId;
                                var max  = parseInt(stepper.dataset.max, 10);
                                var inp  = stepper.querySelector('.qty-input');
                                var mins = stepper.querySelector('.qty-minus');
                                var plus = stepper.querySelector('.qty-plus');

                                function setQty(val) {
                                    val = Math.max(1, Math.min(val, max));
                                    inp.value = val;
                                    mins.disabled = val <= 1;
                                    plus.disabled = val >= max;
                                    post({ action: 'set_cart_qty', product_id: pid, quantity: val }, function(res) {
                                        var el = document.getElementById('subtotal-' + pid);
                                        if (el) el.textContent = res.subtotal;
                                        updateSummary(res);
                                    });
                                }

                                mins.disabled = parseInt(inp.value, 10) <= 1;
                                plus.disabled = parseInt(inp.value, 10) >= max;

                                mins.addEventListener('click', function() { setQty(parseInt(inp.value, 10) - 1); });
                                plus.addEventListener('click', function() { setQty(parseInt(inp.value, 10) + 1); });
                            });

                            document.querySelectorAll('.remove-form').forEach(function(form) {
                                form.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    var pid = form.dataset.productId;
                                    var row = document.getElementById('cart-row-' + pid);
                                    if (row) {
                                        row.style.transition = 'opacity .3s, transform .3s';
                                        row.style.opacity = '0';
                                        row.style.transform = 'translateX(30px)';
                                    }
                                    post({ action: 'set_cart_qty', product_id: pid, quantity: 0 }, function(res) {
                                        if (row) row.remove();
                                        updateSummary(res);
                                        checkEmpty();
                                    });
                                });
                            });
                        })();
                        </script>
                        <?php else: ?><form method="post" class="checkout-layout"><input type="hidden" name="action" value="checkout">
                            <div class="checkout-form"><label>Nama penerima<input required name="customer_name" value="<?= e($user['name']) ?>"></label><label>Nomor telepon<input required name="phone" value="<?= e($user['phone']) ?>"></label><label>Alamat pengiriman<textarea required name="address" rows="4"><?= e($user['address']) ?></textarea></label><label>Metode pembayaran<select name="payment_method">
                                        <option>Transfer bank</option>
                                        <option>Cash on delivery</option>
                                        <option>Bayar di tempat</option>
                                    </select></label><label>Catatan <small>(opsional)</small><textarea name="notes" rows="3" placeholder="Contoh: kirim pagi hari"></textarea></label><button class="button primary">Buat pesanan &#8594;</button></div>
                            <aside class="summary">
                                <h3>Pesanan Anda</h3><?php foreach ($items as $item): ?><div><span><?= $item['quantity'] ?> &times; <?= e($item['product']['name']) ?></span><b><?= money($item['subtotal']) ?></b></div><?php endforeach; ?>
                                <hr>
                                <div class="total"><span>Total</span><b><?= money(cartTotal()) ?></b></div>
                            </aside>
                        </form><?php endif;
                        layoutEnd($user);
                        exit;
                        }

        if ($page === 'dashboard') {
            $user = requireStaff();
            $tab  = $_GET['tab'] ?? 'overview';

            $stats = [
                'products' => db()->query('SELECT COUNT(*) FROM products WHERE is_active=1')->fetchColumn(),
                'orders'   => db()->query("SELECT COUNT(*) FROM orders WHERE status NOT IN ('Selesai','Dibatalkan')")->fetchColumn(),
                'revenue'  => db()->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='Selesai'")->fetchColumn(),
                'stock'    => db()->query('SELECT COALESCE(SUM(stock),0) FROM products WHERE is_active=1')->fetchColumn(),
            ];

            layoutStart('Dashboard', $user);

            if ($tab === 'products' && $user['role'] === 'admin') {
                $products   = db()->query('SELECT p.*, c.name category_name FROM products p JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC')->fetchAll();
                $categories = db()->query('SELECT * FROM categories')->fetchAll();

                $editProduct = null;
                $showForm    = isset($_GET['new']) || isset($_GET['edit']);
                if (isset($_GET['edit'])) {
                    $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
                    $stmt->execute([(int) $_GET['edit']]);
                    $editProduct = $stmt->fetch() ?: null;
                }

                include __DIR__ . '/views/admin/products.php';

            } elseif ($tab === 'orders') {
                $sql    = "SELECT o.*, 
                                  COALESCE(NULLIF(o.customer_name, ''), u.name) AS customer_name,
                                  COALESCE(NULLIF(o.phone, ''), u.phone) AS customer_phone,
                                  u.name AS user_name, 
                                  u.email AS user_email 
                           FROM orders o 
                           LEFT JOIN users u ON u.id=o.user_id 
                           WHERE 1=1";
                $params = [];
                if (!empty($_GET['status_filter'])) {
                    $sql    .= ' AND o.status = ?';
                    $params[] = $_GET['status_filter'];
                }
                if (!empty($_GET['date_from'])) {
                    $sql    .= ' AND DATE(o.created_at) >= ?';
                    $params[] = $_GET['date_from'];
                }
                if (!empty($_GET['date_to'])) {
                    $sql    .= ' AND DATE(o.created_at) <= ?';
                    $params[] = $_GET['date_to'];
                }
                $sql .= ' ORDER BY o.created_at DESC';
                $stmt = db()->prepare($sql);
                $stmt->execute($params);
                $orders = $stmt->fetchAll();

                $orderIds = array_column($orders, 'id');
                $orderItems = [];
                if (!empty($orderIds)) {
                    $inClause = implode(',', array_fill(0, count($orderIds), '?'));
                    $stmtItems = db()->prepare("
                        SELECT od.*, p.name AS product_name, p.unit, p.image, p.price AS current_product_price
                        FROM order_details od
                        LEFT JOIN products p ON p.id = od.product_id
                        WHERE od.order_id IN ($inClause)
                        ORDER BY od.id ASC
                    ");
                    $stmtItems->execute($orderIds);
                    foreach ($stmtItems->fetchAll() as $item) {
                        $item['image_url'] = productImage($item['image'] ?? '');
                        $item['formatted_price'] = money($item['price']);
                        $item['formatted_subtotal'] = money($item['subtotal']);
                        $orderItems[$item['order_id']][] = $item;
                    }
                }

                include __DIR__ . '/views/admin/orders.php';

            } elseif ($tab === 'reports') {
                $period   = $_GET['period']    ?? 'bulanan';
                $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
                $dateTo   = $_GET['date_to']   ?? date('Y-m-d');

                if ($period === 'harian') {
                    $groupBy = "DATE(created_at)";
                    $label   = "DATE_FORMAT(created_at,'%d %M %Y')";
                } elseif ($period === 'tahunan') {
                    $groupBy = "YEAR(created_at)";
                    $label   = "YEAR(created_at)";
                } else {
                    $groupBy = "DATE_FORMAT(created_at,'%Y-%m')";
                    $label   = "DATE_FORMAT(created_at,'%M %Y')";
                }

                $stmt = db()->prepare("
                    SELECT {$label} period,
                           COUNT(*) transactions,
                           COALESCE(SUM(total), 0) revenue
                    FROM orders
                    WHERE status='Selesai'
                      AND DATE(created_at) BETWEEN ? AND ?
                    GROUP BY {$groupBy}
                    ORDER BY MIN(created_at) DESC
                    LIMIT 24
                ");
                $stmt->execute([$dateFrom, $dateTo]);
                $monthly = $stmt->fetchAll();

                $summaryStmt = db()->prepare("
                    SELECT COUNT(*) total_transactions,
                           COALESCE(SUM(od.quantity), 0) total_items,
                           COALESCE(SUM(o.total), 0) total_revenue
                    FROM orders o
                    LEFT JOIN order_details od ON od.order_id = o.id
                    WHERE o.status='Selesai'
                      AND DATE(o.created_at) BETWEEN ? AND ?
                ");
                $summaryStmt->execute([$dateFrom, $dateTo]);
                $summary = $summaryStmt->fetch();

                include __DIR__ . '/views/admin/reports.php';

            } elseif ($tab === 'logs') {
                $logs = db()->query('SELECT l.*, u.name FROM activity_logs l LEFT JOIN users u ON u.id=l.user_id ORDER BY l.created_at DESC LIMIT 50')->fetchAll();

                include __DIR__ . '/views/admin/logs.php';

            } else {
                $recentOrders = db()->query('SELECT o.*, u.name FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC LIMIT 5')->fetchAll();
                $lowStock     = db()->query('SELECT name, stock, unit FROM products WHERE is_active=1 ORDER BY stock ASC LIMIT 5')->fetchAll();

                include __DIR__ . '/views/admin/dashboard.php';
            }

            layoutEnd($user);
            exit;
        }

        redirect('?page=home');
