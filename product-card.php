<article class="product-card"><a href="?page=product&id=<?= $product['id'] ?>">
        <div class="product-image"><img src="<?= e(productImage($product['image'])) ?>"
                alt="<?= e($product['name']) ?>"><span><?= e($product['category_name']) ?></span></div>
        <div class="product-info">
            <h3><?= e($product['name']) ?></h3>
            <p><?= money($product['price']) ?> <small>/ <?= e($product['unit']) ?></small></p>
            <div class="product-meta"><span class="<?= $product['stock'] ? '' : 'out' ?>">●
                    <?= $product['stock'] ? 'Tersedia' : 'Stok habis' ?></span><span>Detail →</span>
            </div>
        </div>
    </a></article>