<?php
/**
 * Halaman: Checkout
 * FR-07 — Form checkout & konfirmasi pesanan
 *
 * @var array  $items
 * @var array  $user
 */
$pageTitle = 'Checkout';
include __DIR__ . '/../layout/header.php';
?>

<section class="content-head">
    <span class="eyebrow">LANGKAH TERAKHIR</span>
    <h1>Konfirmasi pesanan</h1>
    <p>Isi informasi penerima dan pilih metode pembayaran.</p>
</section>

<form method="post" class="checkout-layout">
    <input type="hidden" name="action" value="checkout">

    <!-- Checkout Form -->
    <div class="checkout-box">
        <h3><i class="fa fa-user" style="color:var(--green);margin-right:8px"></i>Informasi Pemesan</h3>

        <div class="form-group">
            <label class="form-label" for="co-name">Nama penerima</label>
            <input class="form-control" type="text" id="co-name" name="customer_name" value="<?= e($user['name']) ?>"
                required>
        </div>

        <div class="form-group">
            <label class="form-label" for="co-phone">Nomor telepon</label>
            <input class="form-control" type="tel" id="co-phone" name="phone" value="<?= e($user['phone']) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="co-address">Alamat pengiriman</label>
            <textarea class="form-control" id="co-address" name="address" rows="4"
                required><?= e($user['address']) ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Metode pembayaran</label>

            <div class="payment-options">
                <!-- QRIS Option -->
                <label class="payment-option" id="payment-qris-label">
                    <input type="radio" name="payment_method" value="QRIS" id="pay-qris" checked>
                    <span class="payment-option-card">
                        <span class="payment-option-icon"><i class="fa fa-qrcode"></i></span>
                        <span class="payment-option-text">
                            <strong>QRIS</strong>
                            <small>Scan QR untuk pembayaran</small>
                        </span>
                        <span class="payment-option-check"><i class="fa fa-check-circle"></i></span>
                    </span>
                </label>

                <!-- Bayar di Tempat Option -->
                <label class="payment-option" id="payment-cod-label">
                    <input type="radio" name="payment_method" value="Bayar di Tempat" id="pay-cod">
                    <span class="payment-option-card">
                        <span class="payment-option-icon"><i class="fa fa-hand-holding-usd"></i></span>
                        <span class="payment-option-text">
                            <strong>Bayar di Tempat</strong>
                            <small>Bayar saat pesanan diterima</small>
                        </span>
                        <span class="payment-option-check"><i class="fa fa-check-circle"></i></span>
                    </span>
                </label>
            </div>

            <!-- QRIS Image & Info (shown when QRIS is selected) -->
            <div class="qris-panel" id="qris-panel">
                <div class="qris-image-wrapper">
                    <img src="assets/image/Qris.jpeg" alt="QRIS Pembayaran" class="qris-image">
                </div>
                <div class="qris-info">
                    <i class="fa fa-info-circle"></i>
                    <span>Tunjukkan bukti pembayaran ketika Anda menerima pesanan.</span>
                </div>
            </div>

            <!-- COD Info (shown when Bayar di Tempat is selected) -->
            <div class="cod-panel" id="cod-panel" style="display:none">
                <div class="cod-info">
                    <i class="fa fa-wallet"></i>
                    <span>Siapkan uang pas saat pesanan tiba. Pembayaran dilakukan langsung kepada kurir.</span>
                </div>
            </div>
        </div>

        <script>
        (function() {
            const qrisRadio  = document.getElementById('pay-qris');
            const codRadio   = document.getElementById('pay-cod');
            const qrisPanel  = document.getElementById('qris-panel');
            const codPanel   = document.getElementById('cod-panel');

            function togglePayment() {
                if (qrisRadio.checked) {
                    qrisPanel.style.display = 'block';
                    codPanel.style.display  = 'none';
                } else {
                    qrisPanel.style.display = 'none';
                    codPanel.style.display  = 'block';
                }
            }

            qrisRadio.addEventListener('change', togglePayment);
            codRadio.addEventListener('change', togglePayment);
            togglePayment();
        })();
        </script>

        <div class="form-group">
            <label class="form-label" for="co-notes">Catatan <span
                    style="font-weight:400;color:var(--muted)">(opsional)</span></label>
            <textarea class="form-control" id="co-notes" name="notes" rows="3"
                placeholder="Contoh: kirim pagi hari, hubungi sebelum antar..."></textarea>
        </div>

        <button class="btn btn-primary btn-full btn-lg" type="submit">
            <i class="fa fa-check"></i> Buat Pesanan
        </button>
    </div>

    <!-- Order Summary -->
    <aside class="order-summary">
        <h3><i class="fa fa-receipt" style="color:var(--green);margin-right:8px"></i>Ringkasan Pesanan</h3>

        <?php foreach ($items as $item): ?>
            <div class="summary-row">
                <span><?= $item['quantity'] ?> × <?= e($item['product']['name']) ?></span>
                <b><?= money($item['subtotal']) ?></b>
            </div>
        <?php endforeach; ?>

        <div class="summary-row summary-total" style="margin-top:8px">
            <span>Total</span>
            <b><?= money(cartTotal()) ?></b>
        </div>

        <a class="btn btn-outline btn-full" href="?page=cart" style="margin-top:16px">
            <i class="fa fa-arrow-left"></i> Kembali ke Keranjang
        </a>
    </aside>
</form>

<div style="height:40px"></div>

<?php include __DIR__ . '/../layout/footer.php'; ?>