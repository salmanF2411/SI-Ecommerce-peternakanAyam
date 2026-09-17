<?php
/**
 * Komponen: Product Card
 * Digunakan di halaman home dan katalog
 *
 * @var array $product  Data produk (name, price, unit, stock, image, category_name, id)
 */
?>
<article class="product-card">
    <a href="?page=product&id=<?= $product['id'] ?>">
        <div class="product-image">
            <img
                src="<?= e(productImage($product['image'])) ?>"
                alt="<?= e($product['name']) ?>"
                loading="lazy"
            >
            <span class="product-badge"><?= e($product['category_name']) ?></span>
        </div>
        <div class="product-info">
            <h3><?= e($product['name']) ?></h3>
            <p class="product-price">
                <?= money($product['price']) ?>
                <small>/ <?= e($product['unit']) ?></small>
            </p>
            <div class="product-meta">
                <span class="<?= $product['stock'] > 0 ? 'stock-ok' : 'stock-out' ?>">
                    <i class="fa <?= $product['stock'] > 0 ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                    <?= $product['stock'] > 0 ? 'Tersedia' : 'Stok habis' ?>
                </span>
                <span class="text-green">Detail <i class="fa fa-arrow-right" style="font-size:10px"></i></span>
            </div>
        </div>
    </a>
</article>
