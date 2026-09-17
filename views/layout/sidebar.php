<?php

/**
 * Layout: Sidebar + Header untuk halaman Admin/Owner
 *
 * @var array  $user
 * @var string $pageTitle
 */
$user = $user ?? currentUser();
$tab = $_GET['tab'] ?? 'overview';
$isAdmin = $user['role'] === 'admin';
$isOwner = $user['role'] === 'owner';
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Dashboard') ?> | Rumah Pitik Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= filemtime(ROOT_PATH . '/assets/css/style.css') ?>">
</head>

<body>

    <!-- ── Sidebar ──────────────────────────────────────────────────────── -->
    <aside class="sidebar">
        <a class="brand" href="?page=dashboard">
            <span class="brand-mark"><img src="<?= BASE_URL ?>/assets/image/logo.png" alt="Logo Rumah Pitik"></span>
            <span>Rumah <b>Pitik</b></span>
        </a>
        <small class="sidebar-role">Ruang Kerja <?= strtoupper($user['role']) ?></small>

        <nav>
            <!-- Dashboard -->
            <a href="?page=dashboard" class="<?= $tab === 'overview' ? 'active' : '' ?>">
                <i class="fa fa-home"></i> Dashboard
            </a>

            <?php if ($isAdmin): ?>
                <!-- Admin Menu -->
                <small class="sidebar-section">Data Master</small>
                <a href="?page=dashboard&tab=categories" class="<?= $tab === 'categories' ? 'active' : '' ?>">
                    <i class="fa fa-tags"></i> Kategori
                </a>
                <a href="?page=dashboard&tab=products" class="<?= $tab === 'products' ? 'active' : '' ?>">
                    <i class="fa fa-box"></i> Produk
                </a>
                <a href="?page=dashboard&tab=customers" class="<?= $tab === 'customers' ? 'active' : '' ?>">
                    <i class="fa fa-users"></i> Pelanggan
                </a>

                <div class="sidebar-divider"></div>
                <small class="sidebar-section">Transaksi</small>
                <a href="?page=dashboard&tab=orders" class="<?= $tab === 'orders' ? 'active' : '' ?>">
                    <i class="fa fa-shopping-bag"></i> Pesanan
                </a>
                <a href="?page=dashboard&tab=stock" class="<?= $tab === 'stock' ? 'active' : '' ?>">
                    <i class="fa fa-warehouse"></i> Stok
                </a>

                <div class="sidebar-divider"></div>
                <small class="sidebar-section">Laporan</small>
                <a href="?page=dashboard&tab=reports" class="<?= $tab === 'reports' ? 'active' : '' ?>">
                    <i class="fa fa-chart-bar"></i> Laporan Penjualan
                </a>
                <a href="?page=dashboard&tab=reports-stock" class="<?= $tab === 'reports-stock' ? 'active' : '' ?>">
                    <i class="fa fa-chart-line"></i> Laporan Stok
                </a>
                <a href="?page=dashboard&tab=logs" class="<?= $tab === 'logs' ? 'active' : '' ?>">
                    <i class="fa fa-history"></i> Log Aktivitas
                </a>

            <?php elseif ($isOwner): ?>
                <!-- Owner Menu -->
                <small class="sidebar-section">Monitoring</small>
                <a href="?page=dashboard&tab=orders" class="<?= $tab === 'orders' ? 'active' : '' ?>">
                    <i class="fa fa-shopping-bag"></i> Monitoring Penjualan
                </a>
                <a href="?page=dashboard&tab=stock" class="<?= $tab === 'stock' ? 'active' : '' ?>">
                    <i class="fa fa-warehouse"></i> Monitoring Stok
                </a>

                <div class="sidebar-divider"></div>
                <small class="sidebar-section">Laporan</small>
                <a href="?page=dashboard&tab=reports" class="<?= $tab === 'reports' ? 'active' : '' ?>">
                    <i class="fa fa-chart-bar"></i> Laporan Penjualan
                </a>
                <a href="?page=dashboard&tab=reports-stock" class="<?= $tab === 'reports-stock' ? 'active' : '' ?>">
                    <i class="fa fa-chart-line"></i> Laporan Stok
                </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-divider"></div>
        <form method="post">
            <input type="hidden" name="action" value="logout">
            <button class="btn-sidebar-logout" type="submit">
                <i class="fa fa-sign-out-alt"></i> Keluar
            </button>
        </form>
    </aside>

    <!-- ── App Main Area ─────────────────────────────────────────────────── -->
    <div class="app-main">

        <!-- App Header -->
        <header class="app-header">
            <div class="app-header-left">
                <i class="fa fa-calendar-alt" style="margin-right:6px;color:var(--green)"></i>
                <?= date('l, d F Y') ?>
            </div>
            <div class="app-header-right">
                <div class="app-header-user">
                    <div class="app-header-user-avatar">
                        <?= strtoupper(substr($user['name'], 0, 2)) ?>
                    </div>
                    <?= e($user['name']) ?>
                    <span style="color:var(--muted);font-size:11px;font-weight:400">(<?= ucfirst($user['role']) ?>)</span>
                </div>
            </div>
        </header>

        <div class="app-wrap">
            <?php include __DIR__ . '/flash.php'; ?>