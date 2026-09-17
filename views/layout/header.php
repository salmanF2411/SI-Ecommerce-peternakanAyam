<?php

/**
 * Layout: Header / Navbar untuk halaman pelanggan
 *
 * @var array|null $user
 * @var string     $pageTitle
 */
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Rumah Pitik — Ayam, telur, dan pupuk segar langsung dari peternakan untuk kebutuhan Anda.">
    <title><?= e($pageTitle ?? 'Beranda') ?> | Rumah Pitik</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= filemtime(ROOT_PATH . '/assets/css/style.css') ?>">
</head>

<body class="customer-page">

    <!-- ── Site Header ────────────────────────────────────────────────────── -->
    <header class="site-header" id="site-header">
        <!-- Brand -->
        <a class="brand" href="?page=home">
            <span class="brand-mark"><img src="<?= BASE_URL ?>/assets/image/logo.png" alt="Logo Rumah Pitik"></span>
            <span>Rumah <b>Pitik</b></span>
        </a>

        <!-- Desktop Navigation -->
        <nav class="site-nav" id="site-nav">
            <a href="?page=home">Beranda</a>
            <a href="?page=products">Produk</a>
            <a href="?page=home#about">Tentang Kami</a>
            <a href="?page=home#how">Cara Pesan</a>

            <?php if ($user): ?>
                <a href="?page=orders"><i class="fa fa-box-open"></i> Pesanan Saya</a>
                <form method="post" style="display:inline">
                    <input type="hidden" name="action" value="logout">
                    <button class="btn-nav-logout" type="submit">
                        <i class="fa fa-sign-out-alt"></i> Keluar
                    </button>
                </form>
            <?php else: ?>
                <a href="?page=login" class="btn btn-outline btn-sm">
                    <i class="fa fa-user"></i> Masuk
                </a>
                <a href="?page=register" class="btn btn-primary btn-sm">Daftar</a>
            <?php endif; ?>

            <!-- Cart -->
            <a class="nav-cart" href="?page=cart">
                <i class="fa fa-shopping-cart"></i>
                <span class="badge"><?= cartCount() ?></span>
            </a>
        </nav>

        <!-- Mobile Toggle -->
        <button class="menu-toggle" id="menu-toggle" aria-label="Buka menu" aria-expanded="false">
            <i class="fa fa-bars"></i>
        </button>
    </header>

    <div class="page-wrap">
        <?php include __DIR__ . '/flash.php'; ?>