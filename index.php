<?php

declare(strict_types=1);

require __DIR__ . '/config.php';

$page = $_GET['page'] ?? 'home';
$action = $_POST['action'] ?? null;

// ─── Action Handlers ────────────────────────────────────────────────────────
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
                'ok' => false,
                'requires_login' => true,
                'message' => 'Silakan masuk (login) ke akun Anda terlebih dahulu untuk menambahkan produk ke keranjang.',
                'redirect' => '?page=login',
            ]);
            exit;
        }
        flash('warning', 'Silakan masuk (login) ke akun Anda terlebih dahulu untuk menambahkan produk ke keranjang.');
        redirect('?page=login');
    }

    $id = (int) ($_POST['product_id'] ?? 0);
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

    $stmt = db()->prepare('SELECT * FROM products WHERE id = ? AND is_active = 1');
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product || (int) $product['stock'] <= 0) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'ok' => false,
                'message' => 'Produk sedang habis atau tidak tersedia.',
            ]);
            exit;
        }
        flash('danger', 'Produk sedang habis.');
        redirect($_POST['redirect'] ?? ('?page=product&id=' . $id));
    }

    $cart = getCart();
    $currentQty = (int) ($cart[$id] ?? 0);
    $maxStock = (int) $product['stock'];
    $newQty = min($currentQty + $quantity, $maxStock);
    $cart[$id] = $newQty;
    saveCart($cart);

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'ok' => true,
            'message' => 'Berhasil menambahkan ' . $product['name'] . ' ke keranjang.',
            'product_name' => $product['name'],
            'added_qty' => $quantity,
            'total_qty' => $newQty,
            'cartCount' => cartCount(),
            'cartTotal' => money(cartTotal()),
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
        $id = (int) $id;
        $qty = (int) $qty;
        if ($qty > 0) {
            $cart[$id] = $qty;
        } else {
            unset($cart[$id]);
        }
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
    $id = (int) ($_POST['product_id'] ?? 0);
    $cart = getCart();
    unset($cart[$id]);
    saveCart($cart);
    flash('success', 'Produk dihapus dari keranjang.');
    redirect('?page=cart');
}

if ($action === 'set_cart_qty') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    $user = currentUser();
    if (!$user) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => 'Login required']);
            exit;
        }
        redirect('?page=login');
    }
    $id = (int) ($_POST['product_id'] ?? 0);
    $qty = (int) ($_POST['quantity'] ?? 0);
    $cart = getCart();
    if ($qty > 0) {
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
    if ($isAjax) {
        header('Content-Type: application/json');
        $newQty = $cart[$id] ?? 0;
        $items = cartItems();
        $subtotal = 0;
        foreach ($items as $item) {
            if ((int) $item['product']['id'] === $id) {
                $subtotal = $item['subtotal'];
            }
        }
        echo json_encode([
            'ok' => true,
            'quantity' => $newQty,
            'subtotal' => money($subtotal),
            'cartTotal' => money(cartTotal()),
            'cartCount' => cartCount(),
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
        foreach ($items as $item) {
            if ($item['quantity'] > $item['product']['stock']) {
                throw new RuntimeException('Stok ' . $item['product']['name'] . ' tidak mencukupi.');
            }
        }
        $code = 'RP-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -5));
        $stmt = $pdo->prepare('INSERT INTO orders (order_code,user_id,customer_name,phone,address,payment_method,notes,total) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([$code, $user['id'], trim($_POST['customer_name']), trim($_POST['phone']), trim($_POST['address']), $_POST['payment_method'], trim($_POST['notes'] ?? ''), cartTotal()]);
        $orderId = $pdo->lastInsertId();
        $detail = $pdo->prepare('INSERT INTO order_details (order_id,product_id,quantity,price,subtotal) VALUES (?,?,?,?,?)');
        foreach ($items as $item) {
            $detail->execute([$orderId, $item['product']['id'], $item['quantity'], $item['product']['price'], $item['subtotal']]);
        }
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
    $imageValue = trim($_POST['image_current'] ?? '');
    if (!empty($_FILES['image_file']['name'])) {
        $uploaded = uploadProductImage($_FILES['image_file']);
        if ($uploaded) {
            $imageValue = $uploaded;
        }
    }

    $name = trim($_POST['name']);
    $category_id = (int) $_POST['category_id'];
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    $unit = trim($_POST['unit']);
    $description = trim($_POST['description']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

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

if ($action === 'save_category' && currentUser() && currentUser()['role'] === 'admin') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if (!$slug && $name) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }
    if ($name && $slug) {
        try {
            $stmt = db()->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)');
            $stmt->execute([$name, $slug]);
            logActivity('Menambah kategori ' . $name);
            flash('success', 'Kategori "' . $name . '" berhasil ditambahkan.');
        } catch (PDOException $e) {
            flash('danger', 'Kategori atau slug sudah ada.');
        }
    } else {
        flash('danger', 'Nama kategori wajib diisi.');
    }
    redirect('?page=dashboard&tab=categories');
}

if ($action === 'add_stock' && currentUser() && currentUser()['role'] === 'admin') {
    $user = currentUser();
    $productId = (int) ($_POST['product_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 0);
    $note = trim($_POST['note'] ?? '');

    if ($productId > 0 && $quantity > 0) {
        $stmt = db()->prepare('SELECT id, name, stock FROM products WHERE id = ?');
        $stmt->execute([$productId]);
        $prod = $stmt->fetch();
        if ($prod) {
            db()->prepare('UPDATE products SET stock = stock + ? WHERE id = ?')->execute([$quantity, $productId]);
            recordStockMovement($productId, (int) $user['id'], 'Masuk', $quantity, $note ?: 'Penambahan stok manual');
            logActivity('Menambah stok ' . $prod['name'] . ' (+' . $quantity . ')');
            flash('success', 'Stok ' . $prod['name'] . ' berhasil ditambahkan.');
        } else {
            flash('danger', 'Produk tidak ditemukan.');
        }
    } else {
        flash('danger', 'Pilih produk dan masukkan jumlah stok yang valid.');
    }
    redirect('?page=dashboard&tab=stock');
}

// ─── Export Excel: Laporan Penjualan (.xlsx) ────────────────────────────────
if ($page === 'export-sales' && currentUser() && in_array(currentUser()['role'], ['admin', 'owner'], true)) {
    requireStaff();

    $period = $_GET['period'] ?? 'bulanan';
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');

    if ($period === 'harian') {
        $groupBy = "DATE(o.created_at)";
        $label = "DATE_FORMAT(o.created_at,'%d %M %Y')";
    } elseif ($period === 'tahunan') {
        $groupBy = "YEAR(o.created_at)";
        $label = "YEAR(o.created_at)";
    } else {
        $groupBy = "DATE_FORMAT(o.created_at,'%Y-%m')";
        $label = "DATE_FORMAT(o.created_at,'%M %Y')";
    }

    $stmt = db()->prepare("
        SELECT {$label} period,
               COUNT(DISTINCT o.id) transactions,
               COALESCE(SUM(od.quantity), 0) items_sold,
               COALESCE(SUM(o.total), 0) revenue
        FROM orders o
        LEFT JOIN order_details od ON od.order_id = o.id
        WHERE o.status='Selesai'
          AND DATE(o.created_at) BETWEEN ? AND ?
        GROUP BY {$groupBy}
        ORDER BY MIN(o.created_at) DESC
        LIMIT 200
    ");
    $stmt->execute([$dateFrom, $dateTo]);
    $rows = $stmt->fetchAll();

    $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $ss->getActiveSheet();
    $sheet->setTitle('Laporan Penjualan');

    // Judul
    $sheet->mergeCells('A1:D1');
    $sheet->setCellValue('A1', 'LAPORAN PENJUALAN — CV RUMAH PITIK');
    $sheet->getStyle('A1')->applyFromArray([
        'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '2F5D3A']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    ]);
    $sheet->mergeCells('A2:D2');
    $sheet->setCellValue('A2', 'Periode: ' . $dateFrom . ' s/d ' . $dateTo . '  |  Dicetak: ' . date('d/m/Y H:i'));
    $sheet->getStyle('A2')->applyFromArray([
        'font' => ['size' => 10, 'italic' => true, 'color' => ['rgb' => '777777']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    ]);

    // Header kolom
    $cols = ['A' => 'Periode', 'B' => 'Total Transaksi', 'C' => 'Produk Terjual (unit)', 'D' => 'Total Pendapatan (Rp)'];
    foreach ($cols as $c => $h) {
        $sheet->setCellValue($c . '4', $h);
    }
    $sheet->getStyle('A4:D4')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5D3A']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
    ]);

    // Data
    $r = 5;
    foreach ($rows as $row) {
        $sheet->setCellValue('A' . $r, $row['period']);
        $sheet->setCellValue('B' . $r, (int) $row['transactions']);
        $sheet->setCellValue('C' . $r, (int) $row['items_sold']);
        $sheet->setCellValue('D' . $r, (float) $row['revenue']);
        $sheet->getStyle('D' . $r)->getNumberFormat()->setFormatCode('#,##0');
        if ($r % 2 === 0) {
            $sheet->getStyle('A' . $r . ':D' . $r)->applyFromArray([
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F7F1']],
            ]);
        }
        $sheet->getStyle('A' . $r . ':D' . $r)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        ]);
        $r++;
    }
    foreach (['A', 'B', 'C', 'D'] as $c) {
        $sheet->getColumnDimension($c)->setAutoSize(true);
    }
    $sheet->getStyle('B5:D' . ($r - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    $sheet->getRowDimension(4)->setRowHeight(20);

    $filename = 'Laporan_Penjualan_' . $dateFrom . '_sd_' . $dateTo . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss))->save('php://output');
    exit;
}

// ─── Export Excel: Laporan Stok (.xlsx) ─────────────────────────────────────
if ($page === 'export-stock' && currentUser() && in_array(currentUser()['role'], ['admin', 'owner'], true)) {
    requireStaff();

    $stockReport = db()->query("
        SELECT p.id, p.name, p.stock, p.unit, c.name AS category_name,
            COALESCE(SUM(CASE WHEN st.type = 'Masuk'  THEN st.quantity ELSE 0 END), 0) AS total_masuk,
            COALESCE(SUM(CASE WHEN st.type = 'Keluar' THEN st.quantity ELSE 0 END), 0) AS total_keluar
        FROM products p
        JOIN categories c ON c.id = p.category_id
        LEFT JOIN stock_transactions st ON st.product_id = p.id
        WHERE p.is_active = 1
        GROUP BY p.id, p.name, p.stock, p.unit, c.name
        ORDER BY p.name ASC
    ")->fetchAll();

    $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $ss->getActiveSheet();
    $sheet->setTitle('Laporan Stok');

    // Judul
    $sheet->mergeCells('A1:G1');
    $sheet->setCellValue('A1', 'LAPORAN STOK PRODUK — CV RUMAH PITIK');
    $sheet->getStyle('A1')->applyFromArray([
        'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '2F5D3A']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    ]);
    $sheet->mergeCells('A2:G2');
    $sheet->setCellValue('A2', 'Tanggal Cetak: ' . date('d/m/Y H:i'));
    $sheet->getStyle('A2')->applyFromArray([
        'font' => ['size' => 10, 'italic' => true, 'color' => ['rgb' => '777777']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    ]);

    // Header kolom
    $cols = ['A' => 'Produk', 'B' => 'Kategori', 'C' => 'Stok Masuk', 'D' => 'Stok Keluar', 'E' => 'Stok Saat Ini', 'F' => 'Satuan', 'G' => 'Status'];
    foreach ($cols as $c => $h) {
        $sheet->setCellValue($c . '4', $h);
    }
    $sheet->getStyle('A4:G4')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5D3A']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
    ]);

    // Data
    $r = 5;
    foreach ($stockReport as $row) {
        if ($row['stock'] == 0) {
            $status = 'Habis';
            $sc = 'C0392B';
        } elseif ($row['stock'] <= 10) {
            $status = 'Menipis';
            $sc = 'E67E22';
        } else {
            $status = 'Tersedia';
            $sc = '27AE60';
        }

        $sheet->setCellValue('A' . $r, $row['name']);
        $sheet->setCellValue('B' . $r, $row['category_name']);
        $sheet->setCellValue('C' . $r, (int) $row['total_masuk']);
        $sheet->setCellValue('D' . $r, (int) $row['total_keluar']);
        $sheet->setCellValue('E' . $r, (int) $row['stock']);
        $sheet->setCellValue('F' . $r, $row['unit']);
        $sheet->setCellValue('G' . $r, $status);
        $sheet->getStyle('G' . $r)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => $sc]],
        ]);
        if ($r % 2 === 0) {
            $sheet->getStyle('A' . $r . ':G' . $r)->applyFromArray([
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F7F1']],
            ]);
        }
        $sheet->getStyle('A' . $r . ':G' . $r)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        ]);
        $r++;
    }
    foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $c) {
        $sheet->getColumnDimension($c)->setAutoSize(true);
    }
    $sheet->getStyle('C5:E' . ($r - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension(4)->setRowHeight(20);

    $filename = 'Laporan_Stok_' . date('Y-m-d') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss))->save('php://output');
    exit;
}

// ─── Flash & User Context ──────────────────────────────────────────────────
$flash = consumeFlash();
$user = currentUser();

// Fallback layout helpers for backward compatibility
function layoutStart(string $title, ?array $user = null): void
{
    $pageTitle = $title;
    include __DIR__ . '/views/layout/header.php';
}

function layoutEnd(?array $user = null): void
{
    include __DIR__ . '/views/layout/footer.php';
}

// ─── Routing: Views ────────────────────────────────────────────────────────
if ($page === 'login') {
    if ($user) {
        redirect(isStaff() ? '?page=dashboard' : '?page=home');
    }
    include __DIR__ . '/views/auth/login.php';
    exit;
}

if ($page === 'register') {
    if ($user) {
        redirect(isStaff() ? '?page=dashboard' : '?page=home');
    }
    include __DIR__ . '/views/auth/register.php';
    exit;
}

if ($page === 'home') {
    $categories = db()->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
    $products = db()->query('SELECT p.*, c.name category_name FROM products p JOIN categories c ON c.id=p.category_id WHERE p.is_active=1 ORDER BY p.id DESC')->fetchAll();
    include __DIR__ . '/views/customer/home.php';
    exit;
}

if ($page === 'products') {
    $categories = db()->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
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
    include __DIR__ . '/views/customer/products.php';
    exit;
}

if ($page === 'product') {
    $stmt = db()->prepare('SELECT p.*, c.name category_name FROM products p JOIN categories c ON c.id=p.category_id WHERE p.id=? AND p.is_active=1');
    $stmt->execute([(int) ($_GET['id'] ?? 0)]);
    $product = $stmt->fetch();
    if (!$product) {
        flash('warning', 'Produk tidak ditemukan atau tidak tersedia.');
        redirect('?page=products');
    }
    include __DIR__ . '/views/customer/product-detail.php';
    exit;
}

if ($page === 'about') {
    include __DIR__ . '/views/customer/tentang-kami.php';
    exit;
}

if ($page === 'cart') {
    $items = cartItems();
    include __DIR__ . '/views/customer/cart.php';
    exit;
}

if ($page === 'checkout') {
    $user = requireLogin();
    $items = cartItems();
    if (!$items) {
        flash('danger', 'Keranjang masih kosong.');
        redirect('?page=cart');
    }
    include __DIR__ . '/views/customer/checkout.php';
    exit;
}

if ($page === 'orders') {
    $user = requireLogin();
    $stmt = db()->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC');
    $stmt->execute([$user['id']]);
    $orders = $stmt->fetchAll();
    include __DIR__ . '/views/customer/orders.php';
    exit;
}

if ($page === 'dashboard') {
    $user = requireStaff();
    $tab = $_GET['tab'] ?? 'overview';

    $tabTitles = [
        'overview' => 'Dashboard',
        'categories' => 'Kategori Produk',
        'products' => 'Kelola Produk',
        'customers' => 'Data Pelanggan',
        'orders' => 'Pesanan',
        'stock' => 'Manajemen Stok',
        'reports' => 'Laporan Penjualan',
        'reports-stock' => 'Laporan Stok',
        'logs' => 'Log Aktivitas',
    ];
    $pageTitle = $tabTitles[$tab] ?? 'Dashboard';

    // Start admin layout (Sidebar & Header)
    include __DIR__ . '/views/layout/sidebar.php';

    if ($tab === 'categories' && $user['role'] === 'admin') {
        $categories = db()->query("
            SELECT c.*, COUNT(p.id) AS product_count 
            FROM categories c 
            LEFT JOIN products p ON p.category_id = c.id 
            GROUP BY c.id 
            ORDER BY c.name ASC
        ")->fetchAll();
        include __DIR__ . '/views/admin/categories.php';

    } elseif ($tab === 'products' && $user['role'] === 'admin') {
        $products = db()->query('SELECT p.*, c.name category_name FROM products p JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC')->fetchAll();
        $categories = db()->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();

        $editProduct = null;
        $showForm = isset($_GET['new']) || isset($_GET['edit']);
        if (isset($_GET['edit'])) {
            $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([(int) $_GET['edit']]);
            $editProduct = $stmt->fetch() ?: null;
        }
        include __DIR__ . '/views/admin/products.php';

    } elseif ($tab === 'customers' && $user['role'] === 'admin') {
        $customers = db()->query("SELECT * FROM users WHERE role = 'customer' ORDER BY id DESC")->fetchAll();
        include __DIR__ . '/views/admin/customers.php';

    } elseif ($tab === 'orders') {
        $sql = "SELECT o.*, 
                       COALESCE(NULLIF(o.customer_name, ''), u.name) AS customer_name,
                       COALESCE(NULLIF(o.phone, ''), u.phone) AS customer_phone,
                       u.name AS user_name, 
                       u.email AS user_email 
                FROM orders o 
                LEFT JOIN users u ON u.id=o.user_id 
                WHERE 1=1";
        $params = [];
        if (!empty($_GET['status_filter'])) {
            $sql .= ' AND o.status = ?';
            $params[] = $_GET['status_filter'];
        }
        if (!empty($_GET['date_from'])) {
            $sql .= ' AND DATE(o.created_at) >= ?';
            $params[] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $sql .= ' AND DATE(o.created_at) <= ?';
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

    } elseif ($tab === 'stock') {
        $products = db()->query("
            SELECT p.*, c.name AS category_name 
            FROM products p 
            JOIN categories c ON c.id = p.category_id 
            WHERE p.is_active = 1 
            ORDER BY p.name ASC
        ")->fetchAll();

        $stockHistory = db()->query("
            SELECT st.*, p.name AS product_name, u.name AS admin_name 
            FROM stock_transactions st 
            JOIN products p ON p.id = st.product_id 
            LEFT JOIN users u ON u.id = st.user_id 
            ORDER BY st.id DESC 
            LIMIT 30
        ")->fetchAll();
        include __DIR__ . '/views/admin/stock.php';

    } elseif ($tab === 'reports') {
        $period = $_GET['period'] ?? 'bulanan';
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');

        if ($period === 'harian') {
            $groupBy = "DATE(created_at)";
            $label = "DATE_FORMAT(created_at,'%d %M %Y')";
        } elseif ($period === 'tahunan') {
            $groupBy = "YEAR(created_at)";
            $label = "YEAR(created_at)";
        } else {
            $groupBy = "DATE_FORMAT(created_at,'%Y-%m')";
            $label = "DATE_FORMAT(created_at,'%M %Y')";
        }

        $stmt = db()->prepare("
            SELECT {$label} period,
                   COUNT(DISTINCT o.id) transactions,
                   COALESCE(SUM(od.quantity), 0) items_sold,
                   COALESCE(SUM(o.total), 0) revenue
            FROM orders o
            LEFT JOIN order_details od ON od.order_id = o.id
            WHERE o.status='Selesai'
              AND DATE(o.created_at) BETWEEN ? AND ?
            GROUP BY {$groupBy}
            ORDER BY MIN(o.created_at) DESC
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

    } elseif ($tab === 'reports-stock') {
        $stockReport = db()->query("
            SELECT p.id, p.name, p.stock, p.unit, c.name AS category_name,
                COALESCE(SUM(CASE WHEN st.type = 'Masuk' THEN st.quantity ELSE 0 END), 0) AS total_masuk,
                COALESCE(SUM(CASE WHEN st.type = 'Keluar' THEN st.quantity ELSE 0 END), 0) AS total_keluar
            FROM products p
            JOIN categories c ON c.id = p.category_id
            LEFT JOIN stock_transactions st ON st.product_id = p.id
            WHERE p.is_active = 1
            GROUP BY p.id, p.name, p.stock, p.unit, c.name
            ORDER BY p.name ASC
        ")->fetchAll();
        include __DIR__ . '/views/admin/reports-stock.php';

    } elseif ($tab === 'logs' && $user['role'] === 'admin') {
        $logs = db()->query('SELECT l.*, u.name FROM activity_logs l LEFT JOIN users u ON u.id=l.user_id ORDER BY l.created_at DESC LIMIT 50')->fetchAll();
        include __DIR__ . '/views/admin/logs.php';

    } else {
        $stats = [
            'products' => db()->query('SELECT COUNT(*) FROM products WHERE is_active=1')->fetchColumn(),
            'orders' => db()->query("SELECT COUNT(*) FROM orders WHERE status NOT IN ('Selesai','Dibatalkan')")->fetchColumn(),
            'revenue' => db()->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='Selesai'")->fetchColumn(),
            'stock' => db()->query('SELECT COALESCE(SUM(stock),0) FROM products WHERE is_active=1')->fetchColumn(),
        ];
        $recentOrders = db()->query('SELECT o.*, u.name FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC LIMIT 5')->fetchAll();
        $lowStock = db()->query('SELECT name, stock, unit FROM products WHERE is_active=1 ORDER BY stock ASC LIMIT 5')->fetchAll();
        include __DIR__ . '/views/admin/dashboard.php';
    }

    // End admin layout
    include __DIR__ . '/views/layout/admin-footer.php';
    exit;
}

redirect('?page=home');
