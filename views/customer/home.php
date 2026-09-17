<?php
/**
 * Halaman: Homepage
 * FR-03, FR-04 — Katalog & kategori produk di halaman utama
 *
 * @var array      $categories
 * @var array      $products    Top 4 produk untuk featured
 * @var array|null $user
 */
$pageTitle = 'Beranda — Produk Segar dari Rumah Pitik';
include __DIR__ . '/../layout/header.php';
?>

<!-- ── Hero Section ──────────────────────────────────────────────── -->
<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow">DARI KANDANG, UNTUK KELUARGA</span>
        <h1>Segar, dekat, <em>dan apa adanya.</em></h1>
        <p>Ayam, telur, dan pupuk pilihan dari Rumah Pitik. Dikelola dengan perhatian, untuk kebutuhan sehari-hari Anda.
        </p>
        <div class="hero-actions">
            <a class="btn btn-primary btn-lg" href="?page=products">
                <i class="fa fa-store"></i> Jelajahi Produk
            </a>
            <a class="btn btn-outline btn-lg" href="?page=home#about">
                Tentang Kami
            </a>
        </div>
    </div>
    <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?auto=format&fit=crop&w=1200&q=85"
            alt="Peternakan ayam Rumah Pitik" loading="eager">
    </div>
</section>

<!-- ── About Section ─────────────────────────────────────────────── -->
<section class="intro" id="about">
    <div>
        <span class="eyebrow">KENAPA RUMAH PITIK</span>
        <h2>Yang baik dimulai dari cara yang baik.</h2>
    </div>
    <p>
        Kami merawat setiap produk dari sumbernya agar sampai di meja Anda dalam kondisi terbaik.
        CV Rumah Pitik berkomitmen untuk menyediakan ayam, telur, dan pupuk berkualitas dengan harga
        yang terjangkau dan pelayanan yang transparan. Sederhana, jujur, dan konsisten.
    </p>
</section>

<!-- ── Category Grid ──────────────────────────────────────────────── -->
<section class="category-grid" id="categories">
    <div class="section-heading">
        <div>
            <span class="eyebrow">PILIH SESUAI KEBUTUHAN</span>
            <h2>Kategori produk</h2>
        </div>
        <a href="?page=products">Lihat semua <i class="fa fa-arrow-right"></i></a>
    </div>

    <div class="category-items">
        <?php
        $catIcons = [
            'Ayam Hidup' => '<i class="fa fa-dove"></i>',
            'Ayam Potong' => '<i class="fa fa-drumstick-bite"></i>',
            'Pupuk' => '<i class="fa fa-leaf"></i>',
            'Telur' => '<i class="fa fa-egg"></i>',
        ];
        foreach ($categories as $cat):
            ?>
            <a class="category-item" href="?page=products&category=<?= e($cat['slug']) ?>">
                <div class="cat-icon">
                    <?= $catIcons[$cat['name']] ?? '<i class="fa fa-box"></i>' ?>
                </div>
                <div>
                    <b><?= e($cat['name']) ?></b>
                    <small>Jelajahi pilihan <i class="fa fa-arrow-right" style="font-size:9px"></i></small>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ── Featured Products ──────────────────────────────────────────── -->
<section class="product-section">
    <div class="section-heading">
        <div>
            <span class="eyebrow">PILIHAN MINGGU INI</span>
            <h2>Produk favorit</h2>
        </div>
        <a href="?page=products">Lihat semua <i class="fa fa-arrow-right"></i></a>
    </div>

    <div class="product-grid">
        <?php foreach (array_slice($products, 0, 4) as $product):
            include __DIR__ . '/product-card.php';
        endforeach; ?>
    </div>
</section>

<!-- ── How to Order ───────────────────────────────────────────────── -->
<section class="steps-section" id="how">
    <span class="eyebrow">BELANJA TANPA RIBET</span>
    <h2>Empat langkah menuju<br>pesanan Anda.</h2>
    <div class="step-grid">
        <div class="step-item">
            <span class="step-num"><i class="fa fa-search"></i> 01</span>
            <h3>Pilih produk</h3>
            <p>Temukan kebutuhan Anda dari katalog produk segar kami.</p>
        </div>
        <div class="step-item">
            <span class="step-num"><i class="fa fa-shopping-cart"></i> 02</span>
            <h3>Masukkan keranjang</h3>
            <p>Atur jumlah sesuai kebutuhan, bisa beberapa produk sekaligus.</p>
        </div>
        <div class="step-item">
            <span class="step-num"><i class="fa fa-clipboard-list"></i> 03</span>
            <h3>Checkout</h3>
            <p>Isi alamat pengiriman dan pilih metode pembayaran.</p>
        </div>
        <div class="step-item">
            <span class="step-num"><i class="fa fa-check-circle"></i> 04</span>
            <h3>Kami proses</h3>
            <p>Admin mengonfirmasi pesanan Anda dan segera diproses.</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../layout/footer.php'; ?>