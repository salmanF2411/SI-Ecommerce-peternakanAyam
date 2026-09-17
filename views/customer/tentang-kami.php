<?php

/**
 * Halaman: Tentang Kami
 * Profil dan informasi usaha CV Rumah Pitik.
 *
 * @var array|null $user
 */
$pageTitle = 'Tentang Kami';
include __DIR__ . '/../layout/header.php';
?>
<style>
    .about-copy-section {
        padding: 80px 0;
        border-bottom: 1px solid var(--line);
    }

    .about-copy-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 70px;
    }

    .about-copy-grid h2 {
        font-size: clamp(1.8rem, 3vw, 2.6rem);
        margin-bottom: 18px;
    }

    .about-copy-grid p,
    .section-lead,
    .about-purpose p,
    .about-location p {
        color: var(--muted);
        line-height: 1.8;
    }

    .about-values {
        border-bottom: 1px solid var(--line);
    }

    .section-lead {
        max-width: 720px;
        margin-bottom: 40px;
    }

    .about-purpose {
        margin-top: 0;
    }

    .about-purpose ul {
        color: var(--muted);
        line-height: 2;
        padding-left: 20px;
        margin: 0;
    }

    .about-location {
        padding: 80px 0 20px;
    }

    .about-location h2 {
        margin-bottom: 12px;
    }

    @media (max-width: 800px) {
        .about-copy-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }

        .about-copy-section,
        .about-location {
            padding: 55px 0;
        }
    }
</style>
<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow">TENTANG CV RUMAH PITIK</span>
        <h1>Peternakan yang tumbuh bersama kebutuhan keluarga.</h1>
        <p>
            Berawal dari usaha peternakan ayam petelur yang dirintis pada tahun 2024,
            CV Rumah Pitik terus berkembang dalam menyediakan produk peternakan dan
            memenuhi kebutuhan pasar. Kini, melalui pemanfaatan teknologi berbasis web,
            Rumah Pitik menghadirkan informasi produk dan proses pemesanan yang lebih
            mudah dan terstruktur.
        </p>
        <a class="button primary" href="?page=products">Lihat produk <span>→</span></a>
    </div>
    <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?auto=format&fit=crop&w=1200&q=85"
            alt="Peternakan ayam CV Rumah Pitik">
    </div>
</section>

<section class="intro">
    <div>
        <span class="eyebrow">MENGENAL KAMI</span>
        <h2>Dari Cianjur untuk kebutuhan peternakan dan keluarga Indonesia.</h2>
    </div>
    <p>
        <strong>CV Rumah Pitik</strong> merupakan usaha yang bergerak di bidang peternakan ayam
        dan berlokasi di Kp. Seda Tengah, Ciharashas, Kecamatan Cilaku, Kabupaten Cianjur,
        Jawa Barat 43285. CV Rumah Pitik didirikan pada tahun <strong>2024 oleh Ibu Yanti Hariyanti</strong>.
    </p>
</section>

<section class="about-copy-section">
    <div class="about-copy-grid">
        <article>
            <span class="eyebrow">AWAL PERJALANAN</span>
            <h2>Dirintis dari 100 ekor ayam petelur.</h2>
            <p>
                Usaha ini pada awalnya dirintis sebagai peternakan ayam petelur dengan jumlah
                sekitar 100 ekor ayam di wilayah Cianjur. Seiring meningkatnya permintaan pasar
                terhadap telur dan adanya kondisi lingkungan yang kurang mendukung, CV Rumah Pitik
                kemudian mengembangkan lokasi kandangnya dengan membeli lahan seluas 50 ubin di
                Desa Ciharashas.
            </p>
        </article>
        <article>
            <span class="eyebrow">PEMILIHAN LOKASI</span>
            <h2>Tempat yang mendukung pertumbuhan usaha.</h2>
            <p>
                Pemilihan lokasi mempertimbangkan akses transportasi yang mudah, ketersediaan
                sumber air bersih, lokasi yang jauh dari keramaian dan pemukiman, daerah pemasaran
                yang terjangkau, keamanan bagi ayam, serta kemudahan memperoleh sarana produksi.
            </p>
        </article>
    </div>
</section>

<!-- <section class="steps about-values">
    <span class="eyebrow">PERKEMBANGAN RUMAH PITIK</span>
    <h2>Usaha peternakan yang terus beradaptasi.</h2>
    <p class="section-lead">
        Berawal dari skala awal peternakan ayam petelur, CV Rumah Pitik terus mengembangkan
        kegiatan usahanya sesuai kebutuhan pasar. Sistem berbasis web ini membantu pemasaran,
        pemesanan, pengelolaan produk, stok, dan transaksi penjualan agar lebih terstruktur
        dan efisien.
    </p>
    <div class="step-grid">
        <div>
            <b>01</b>
            <h3>Informasi produk</h3>
            <p>Pelanggan dapat melihat informasi produk Rumah Pitik dengan lebih mudah.</p>
        </div>
        <div>
            <b>02</b>
            <h3>Pemesanan daring</h3>
            <p>Pelanggan dapat melakukan pemesanan tanpa harus datang atau menghubungi admin secara langsung.</p>
        </div>
        <div>
            <b>03</b>
            <h3>Pengelolaan terstruktur</h3>
            <p>Data produk, stok, dan transaksi dapat dikelola dalam satu sistem.</p>
        </div>
        <div>
            <b>04</b>
            <h3>Pelayanan lebih efisien</h3>
            <p>Teknologi membantu memperluas pemasaran dan membuat proses pembelian lebih praktis.</p>
        </div>
    </div>
</section> -->

<!-- <section class="category-grid about-products">
    <div class="section-heading">
        <div>
            <span class="eyebrow">PRODUK RUMAH PITIK</span>
            <h2>Empat kategori untuk kebutuhan Anda.</h2>
        </div>
        <a href="?page=products">Lihat katalog <i class="fa fa-arrow-right"></i></a>
    </div>
    <div class="category-items">
        <a class="category-item" href="?page=products&category=ayam-hidup">
            <div class="cat-icon"><i class="fa fa-dove"></i></div>
            <div><b>Ayam Hidup</b><small>Untuk kebutuhan pelanggan</small></div>
        </a>
        <a class="category-item" href="?page=products&category=ayam-potong">
            <div class="cat-icon"><i class="fa fa-drumstick-bite"></i></div>
            <div><b>Ayam Potong</b><small>Praktis dipesan melalui sistem</small></div>
        </a>
        <a class="category-item" href="?page=products&category=pupuk">
            <div class="cat-icon"><i class="fa fa-leaf"></i></div>
            <div><b>Pupuk</b><small>Untuk kebutuhan pertanian</small></div>
        </a>
        <a class="category-item" href="?page=products&category=telur">
            <div class="cat-icon"><i class="fa fa-egg"></i></div>
            <div><b>Telur</b><small>Hasil peternakan ayam petelur</small></div>
        </a>
    </div>
</section> -->

<section class="intro about-purpose">
    <div>
        <span class="eyebrow">TUJUAN KAMI</span>
        <h2>Memudahkan informasi dan proses pemesanan.</h2>
    </div>
    <div>
        <p>CV Rumah Pitik melalui pengembangan sistem berbasis web berupaya memberikan kemudahan dalam penyampaian
            informasi produk dan proses pemesanan.</p>
        <ul>
            <li>Meningkatkan efisiensi proses pembelian</li>
            <li>Memperluas jangkauan pemasaran</li>
            <li>Membantu pengelolaan data produk</li>
            <li>Membantu pengelolaan stok</li>
            <li>Membantu pencatatan transaksi</li>
            <li>Menyediakan laporan secara lebih terstruktur</li>
        </ul>
    </div>
</section>

<section class="about-location">
    <span class="eyebrow">LOKASI KAMI</span>
    <h2>CV Rumah Pitik</h2>
    <p>Kp. Seda Tengah, Ciharashas<br>Kecamatan Cilaku, Kabupaten Cianjur<br>Jawa Barat 43285</p>
</section>

<?php include __DIR__ . '/../layout/footer.php'; ?>