<?php
/**
 * Halaman: Keranjang Belanja
 * FR-06 — Kelola keranjang (Otomatis update kuantitas dengan tombol + dan -)
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

<div class="cart-layout" id="cartLayout">
    <!-- Daftar Item Keranjang -->
    <div class="cart-items-wrap" id="cartItems">
        <?php if ($items): ?>
            <?php foreach ($items as $item): ?>
                <div class="cart-item" id="cart-row-<?= $item['product']['id'] ?>">
                    <img class="cart-item-img" src="<?= e(productImage($item['product']['image'])) ?>"
                        alt="<?= e($item['product']['name']) ?>">
                    <div class="cart-item-info">
                        <b><?= e($item['product']['name']) ?></b>
                        <small><?= money($item['product']['price']) ?> / <?= e($item['product']['unit']) ?></small>
                        <small style="color:var(--muted)">Stok: <?= (int) $item['product']['stock'] ?> <?= e($item['product']['unit']) ?></small>
                    </div>

                    <!-- Stepper Kuantitas (+ dan -) -->
                    <div class="cart-item-qty">
                        <div class="qty-stepper" data-product-id="<?= $item['product']['id'] ?>"
                            data-max="<?= (int) $item['product']['stock'] ?>">
                            <button type="button" class="qty-btn qty-minus" aria-label="Kurang"
                                <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>&minus;</button>
                            <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" min="1"
                                max="<?= (int) $item['product']['stock'] ?>" readonly>
                            <button type="button" class="qty-btn qty-plus" aria-label="Tambah"
                                <?= $item['quantity'] >= (int) $item['product']['stock'] ? 'disabled' : '' ?>>+</button>
                        </div>
                    </div>

                    <!-- Subtotal Item Otomatis -->
                    <div class="cart-item-subtotal">
                        <span class="cart-subtotal-label">Subtotal</span>
                        <strong class="cart-subtotal-value" id="subtotal-<?= $item['product']['id'] ?>">
                            <?= money($item['subtotal']) ?>
                        </strong>
                    </div>

                    <!-- Tombol Hapus Produk -->
                    <div class="cart-item-remove">
                        <form method="post" class="remove-form" data-product-id="<?= $item['product']['id'] ?>">
                            <input type="hidden" name="action" value="remove_cart">
                            <input type="hidden" name="product_id" value="<?= $item['product']['id'] ?>">
                            <button type="submit" class="btn-remove" title="Hapus produk ini dari keranjang" aria-label="Hapus">
                                <i class="fa fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="cart-empty" id="cartEmpty">
                <div class="cart-empty-icon">&#x1F6D2;</div>
                <h3>Keranjang Anda kosong</h3>
                <p>Temukan produk segar pilihan kami dan tambahkan ke keranjang.</p>
                <a href="?page=products" class="btn btn-primary">Mulai Belanja &rarr;</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Ringkasan Pesanan (Order Summary) -->
    <aside class="order-summary" id="cartSummary">
        <h3>Ringkasan</h3>
        <div class="summary-row">
            <span>Subtotal</span>
            <b id="summaryTotal"><?= money(cartTotal()) ?></b>
        </div>
        <div class="summary-row">
            <span>Pengiriman</span>
            <b>Dikonfirmasi admin</b>
        </div>
        <hr style="border:0;border-top:1px solid var(--line);margin:14px 0">
        <div class="summary-row summary-total">
            <span>Total</span>
            <b id="summaryGrand" style="font-size:18px;color:var(--green)"><?= money(cartTotal()) ?></b>
        </div>

        <div class="summary-item-count" style="margin:12px 0 6px;text-align:center;font-size:12px;color:var(--muted)">
            <span id="summaryCount"><?= cartCount() ?></span> item dalam keranjang
        </div>

        <a class="btn btn-primary btn-full<?= !$items ? ' disabled-btn' : '' ?>" href="?page=checkout"
            id="btnCheckout" <?= !$items ? ' aria-disabled="true" tabindex="-1"' : '' ?> style="margin-top:16px">
            Lanjut Checkout <i class="fa fa-arrow-right"></i>
        </a>
        <a class="btn btn-outline btn-full" href="?page=products" style="margin-top:8px">
            <i class="fa fa-store"></i> Lanjut Belanja
        </a>
    </aside>
</div>

<script>
(function () {
    var BASE = '<?= BASE_URL ?>/index.php';

    function post(data, onSuccess) {
        var fd = new FormData();
        Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
        fetch(BASE, {
            method: 'POST',
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: fd
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.ok && onSuccess) onSuccess(res);
        })
        .catch(function (err) {
            console.error('Cart update error:', err);
        });
    }

    function updateSummary(res) {
        var total = document.getElementById('summaryTotal');
        var grand = document.getElementById('summaryGrand');
        var count = document.getElementById('summaryCount');
        var badges = document.querySelectorAll('.cart-link span, .nav-cart .badge, .badge, .cart-badge');
        var btn = document.getElementById('btnCheckout');

        if (total) total.textContent = res.cartTotal;
        if (grand) grand.textContent = res.cartTotal;
        if (count) count.textContent = res.cartCount;

        badges.forEach(function (badge) {
            badge.textContent = res.cartCount;
            badge.classList.remove('cart-bump');
            void badge.offsetWidth;
            badge.classList.add('cart-bump');
        });

        if (btn) {
            if (res.cartCount === 0) {
                btn.classList.add('disabled-btn');
                btn.setAttribute('aria-disabled', 'true');
                btn.setAttribute('tabindex', '-1');
            } else {
                btn.classList.remove('disabled-btn');
                btn.removeAttribute('aria-disabled');
                btn.removeAttribute('tabindex');
            }
        }
    }

    function checkEmpty() {
        var rows = document.querySelectorAll('.cart-item[id^="cart-row-"]');
        if (rows.length === 0) {
            var ci = document.getElementById('cartItems');
            if (ci) {
                ci.innerHTML = '<div class="cart-empty" id="cartEmpty"><div class="cart-empty-icon">&#x1F6D2;</div><h3>Keranjang Anda kosong</h3><p>Temukan produk segar pilihan kami dan tambahkan ke keranjang.</p><a href="?page=products" class="btn btn-primary">Mulai Belanja &rarr;</a></div>';
            }
        }
    }

    // Pasang event listener untuk setiap stepper kuantitas (+ dan -)
    document.querySelectorAll('.qty-stepper').forEach(function (stepper) {
        var pid = stepper.dataset.productId;
        var max = parseInt(stepper.dataset.max, 10);
        var inp = stepper.querySelector('.qty-input');
        var mins = stepper.querySelector('.qty-minus');
        var plus = stepper.querySelector('.qty-plus');

        function setQty(val) {
            val = Math.max(1, Math.min(val, max));
            inp.value = val;
            mins.disabled = (val <= 1);
            plus.disabled = (val >= max);

            post({ action: 'set_cart_qty', product_id: pid, quantity: val }, function (res) {
                var el = document.getElementById('subtotal-' + pid);
                if (el) el.textContent = res.subtotal;
                updateSummary(res);
            });
        }

        mins.disabled = parseInt(inp.value, 10) <= 1;
        plus.disabled = parseInt(inp.value, 10) >= max;

        mins.addEventListener('click', function () {
            setQty(parseInt(inp.value, 10) - 1);
        });

        plus.addEventListener('click', function () {
            setQty(parseInt(inp.value, 10) + 1);
        });
    });

    // Pasang event listener untuk form hapus produk
    document.querySelectorAll('.remove-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!confirm('Hapus produk ini dari keranjang?')) return;

            var pid = form.dataset.productId;
            var row = document.getElementById('cart-row-' + pid);
            if (row) {
                row.style.transition = 'opacity .3s, transform .3s';
                row.style.opacity = '0';
                row.style.transform = 'translateX(30px)';
            }

            post({ action: 'set_cart_qty', product_id: pid, quantity: 0 }, function (res) {
                if (row) row.remove();
                updateSummary(res);
                checkEmpty();
            });
        });
    });
})();
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>