<?php
/**
 * Halaman: Katalog Produk
 * FR-03, FR-04 — Katalog & filter produk
 *
 * @var array      $categories
 * @var array      $products
 * @var string     $search
 * @var string     $category
 * @var array|null $user
 */
$pageTitle = 'Katalog Produk';
include __DIR__ . '/../layout/header.php';
?>

<!-- ── Catalog Head ────────────────────────────────────────────────── -->
<section class="catalog-head">
    <span class="eyebrow">KATALOG PRODUK</span>
    <h1>Temukan yang Anda<br><em>butuhkan hari ini.</em></h1>

    <!-- Search Form -->
    <form class="search-form" method="get" style="margin-top:24px">
        <input type="hidden" name="page" value="products">
        <input type="search" name="search" value="<?= e($search) ?>" placeholder="Cari ayam, telur, pupuk..."
            aria-label="Cari produk">
        <button type="submit">
            <i class="fa fa-search"></i> Cari
        </button>
    </form>
</section>

<!-- ── Filter Bar ─────────────────────────────────────────────────── -->
<div class="filter-bar" role="navigation" aria-label="Filter kategori">
    <a class="filter-btn <?= !$category ? 'active' : '' ?>" href="?page=products">
        Semua
    </a>
    <?php foreach ($categories as $cat): ?>
        <a class="filter-btn <?= $category === $cat['slug'] ? 'active' : '' ?>"
            href="?page=products&category=<?= e($cat['slug']) ?><?= $search ? '&search=' . e($search) : '' ?>">
            <?= e($cat['name']) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- ── Products Grid ──────────────────────────────────────────────── -->
<?php if ($products): ?>
    <div class="product-grid catalog-grid">
        <?php foreach ($products as $product):
            include __DIR__ . '/product-card.php';
        endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <i class="fa fa-box-open"></i>
        <h3>Produk tidak ditemukan</h3>
        <p>Coba kata kunci lain atau <a href="?page=products">lihat semua produk</a>.</p>
    </div>
<?php endif; ?>

<div style="height:80px"></div>

<?php include __DIR__ . '/../layout/footer.php'; ?>