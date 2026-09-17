<?php
/**
 * Halaman: Detail Produk
 * FR-05 — Detail produk & tambah ke keranjang
 *
 * @var array      $product
 * @var array|null $user
 */
$pageTitle = $product['name'];
include __DIR__ . '/../layout/header.php';
?>

<section class="product-detail">
    <!-- Foto Produk -->
    <div class="detail-image">
        <img src="<?= e(productImage($product['image'])) ?>" alt="<?= e($product['name']) ?>">
    </div>

    <!-- Info Produk -->
    <div class="detail-copy">
        <span class="eyebrow"><?= e($product['category_name']) ?></span>
        <h1><?= e($product['name']) ?></h1>

        <strong class="detail-price">
            <?= money($product['price']) ?>
            <small>/ <?= e($product['unit']) ?></small>
        </strong>

        <p class="detail-desc"><?= e($product['description']) ?></p>

        <!-- Stock Indicator -->
        <?php if ($product['stock'] > 0): ?>
            <div class="stock-indicator available">
                <i class="fa fa-check-circle"></i>
                Stok tersedia: <b><?= $product['stock'] ?>     <?= e($product['unit']) ?></b>
            </div>
        <?php else: ?>
            <div class="stock-indicator out-of-stock">
                <i class="fa fa-times-circle"></i>
                Stok habis — segera tersedia kembali
            </div>
        <?php endif; ?>

        <!-- Buy Form -->
        <?php if ($product['stock'] > 0): ?>
            <form method="post" class="buy-form js-buy-form">
                <input type="hidden" name="action" value="add_cart">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="hidden" name="redirect" value="?page=product&id=<?= $product['id'] ?>">

                <div class="qty-wrapper"
                    style="display:flex;align-items:center;gap:6px;border:1.5px solid var(--line);border-radius:var(--radius);overflow:hidden">
                    <button type="button" data-action="minus" class="btn btn-sm btn-outline"
                        style="border:0;border-radius:0;height:44px">
                        <i class="fa fa-minus"></i>
                    </button>
                    <input class="qty-input" type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>"
                        style="border:0;outline:none">
                    <button type="button" data-action="plus" class="btn btn-sm btn-outline"
                        style="border:0;border-radius:0;height:44px">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>

                <button class="btn btn-primary btn-lg" type="submit" style="flex:1">
                    <i class="fa fa-cart-plus"></i> Tambah ke Keranjang
                </button>
            </form>
        <?php endif; ?>

        <!-- Back link -->
        <p style="margin-top:24px">
            <a href="?page=products" style="color:var(--muted);font-size:13px">
                <i class="fa fa-arrow-left"></i> Kembali ke katalog
            </a>
        </p>
    </div>
</section>

<?php include __DIR__ . '/../layout/footer.php'; ?>