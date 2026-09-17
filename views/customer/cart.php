<?php
/**
 * Halaman: Keranjang Belanja
 * FR-06 — Kelola keranjang
 *
 * @var array      $items
 * @var array|null $user
 */
$pageTitle = 'Keranjang Belanja';
include __DIR__ . '/../layout/header.php';
?>

<section class="content-head">
    <span class="eyebrow">KERANJANG BELANJA</span>
    <h1>Keranjang Anda</h1>
    <p>Periksa kembali pesanan Anda sebelum melanjutkan ke checkout.</p>
</section>

<?php if ($items): ?>
    <form method="post">
        <input type="hidden" name="action" value="update_cart">
        <div class="cart-layout">

            <!-- Cart Items -->
            <div class="cart-items-wrap">
                <?php foreach ($items as $item): ?>
                    <div class="cart-item">
                        <img class="cart-item-img" src="<?= e(productImage($item['product']['image'])) ?>"
                            alt="<?= e($item['product']['name']) ?>">
                        <div class="cart-item-info">
                            <b><?= e($item['product']['name']) ?></b>
                            <small><?= money($item['product']['price']) ?> / <?= e($item['product']['unit']) ?></small>
                        </div>
                        <input class="cart-qty" type="number" name="quantity[<?= $item['product']['id'] ?>]"
                            value="<?= $item['quantity'] ?>" min="0" max="<?= $item['product']['stock'] ?>"
                            aria-label="Jumlah <?= e($item['product']['name']) ?>">
                        <div class="cart-item-subtotal">
                            <?= money($item['subtotal']) ?>
                        </div>
                        <button type="submit" class="btn-remove" name="quantity[<?= $item['product']['id'] ?>]" value="0"
                            data-confirm="Hapus produk ini dari keranjang?" title="Hapus">
                            <i class="fa fa-trash-alt"></i>
                        </button>
                    </div>
                <?php endforeach; ?>

                <div style="padding:20px 0">
                    <button class="btn btn-outline btn-sm" type="submit" name="save" value="1">
                        <i class="fa fa-sync-alt"></i> Perbarui Keranjang
                    </button>
                </div>
            </div>

            <!-- Order Summary -->
            <aside class="order-summary">
                <h3>Ringkasan</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <b><?= money(cartTotal()) ?></b>
                </div>
                <div class="summary-row">
                    <span>Pengiriman</span>
                    <b>Dikonfirmasi admin</b>
                </div>
                <div class="summary-row summary-total">
                    <span>Total</span>
                    <b><?= money(cartTotal()) ?></b>
                </div>

                <a class="btn btn-primary btn-full" href="?page=checkout" style="margin-top:20px">
                    <i class="fa fa-arrow-right"></i> Lanjut Checkout
                </a>
                <a class="btn btn-outline btn-full" href="?page=products" style="margin-top:8px">
                    <i class="fa fa-store"></i> Lanjut Belanja
                </a>
            </aside>
        </div>
    </form>
<?php else: ?>
    <div class="empty-state" style="margin-bottom:80px">
        <i class="fa fa-shopping-cart"></i>
        <h3>Keranjang masih kosong</h3>
        <p><a href="?page=products">Mulai belanja sekarang</a></p>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>