<?php
/**
 * Halaman: Pesanan Saya
 * FR-08 — Daftar & status pesanan pelanggan
 *
 * @var array  $orders
 * @var array  $user
 */
$pageTitle = 'Pesanan Saya';
include __DIR__ . '/../layout/header.php';
?>

<section class="content-head">
    <span class="eyebrow">AKUN PELANGGAN</span>
    <h1>Pesanan saya</h1>
    <p>Pantau perjalanan pesanan Anda dari sini.</p>
</section>

<div class="order-list">
    <?php if ($orders): ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-card-code">
                    <b><?= e($order['order_code']) ?></b>
                    <small><i class="fa fa-calendar-alt"
                            style="margin-right:4px;color:var(--green)"></i><?= dateId($order['created_at']) ?></small>
                </div>
                <div>
                    <small style="color:var(--muted);font-size:12px">Metode Pembayaran</small>
                    <b style="font-size:13px"><?= e($order['payment_method']) ?></b>
                </div>
                <div class="order-total">
                    <?= money($order['total']) ?>
                </div>
                <div>
                    <span
                        class="status status-badge <?= statusClass($order['status']) ?> <?= strtolower(trim($order['status'])) ?>">
                        <?= e($order['status']) ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fa fa-box-open"></i>
            <h3>Belum ada pesanan</h3>
            <p><a href="?page=products">Mulai belanja sekarang</a></p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>