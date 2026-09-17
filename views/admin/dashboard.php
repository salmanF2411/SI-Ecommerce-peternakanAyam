<?php
/**
 * Halaman: Dashboard Admin / Owner
 * FR-13 — Overview statistik & pesanan terbaru
 *
 * @var array  $user
 * @var array  $stats     products, orders, revenue, stock
 * @var array  $recentOrders
 * @var array  $lowStock
 */
$pageTitle = 'Dashboard';
?>

<!-- ── Dashboard Head ──────────────────────────────────────────────── -->
<div class="dash-head">
    <div>
        <span class="eyebrow">RINGKASAN HARI INI</span>
        <h1>Halo, <?= e(explode(' ', $user['name'])[0]) ?>.</h1>
        <p>Pantau operasional Rumah Pitik dari satu ruang kerja.</p>
    </div>
    <?php if ($user['role'] === 'admin'): ?>
        <a href="?page=dashboard&tab=products&new=1" style="display:inline-flex;align-items:center;gap:8px;
              background:#2f5d3a;color:#fff;
              padding:10px 20px;border-radius:8px;
              font-weight:600;font-size:14px;text-decoration:none;
              border:2px solid #2f5d3a;
              transition:background .2s,box-shadow .2s;
              box-shadow:0 2px 8px rgba(47,93,58,.25)"
            onmouseover="this.style.background='#1e3d26';this.style.boxShadow='0 4px 14px rgba(47,93,58,.4)'"
            onmouseout="this.style.background='#2f5d3a';this.style.boxShadow='0 2px 8px rgba(47,93,58,.25)'">
            <i class="fa fa-plus"></i> Tambah Produk
        </a>
    <?php endif; ?>
</div>

<!-- ── Stat Cards ──────────────────────────────────────────────────── -->
<div class="stat-grid">
    <div class="stat-card green">
        <span class="stat-label"><i class="fa fa-box"></i> Produk Aktif</span>
        <b class="stat-value"><?= $stats['products'] ?></b>
        <span class="stat-meta">di katalog</span>
    </div>
    <div class="stat-card">
        <span class="stat-label"><i class="fa fa-shopping-bag"></i> Pesanan Berjalan</span>
        <b class="stat-value"><?= $stats['orders'] ?></b>
        <span class="stat-meta">perlu ditangani</span>
    </div>
    <div class="stat-card">
        <span class="stat-label"><i class="fa fa-money-bill-wave"></i> Pendapatan (Selesai)</span>
        <b class="stat-value" style="font-size:22px"><?= money($stats['revenue']) ?></b>
        <span class="stat-meta">total transaksi</span>
    </div>
    <div class="stat-card">
        <span class="stat-label"><i class="fa fa-warehouse"></i> Total Stok</span>
        <b class="stat-value"><?= $stats['stock'] ?></b>
        <span class="stat-meta">unit tersedia</span>
    </div>
</div>

<!-- ── Bottom Grid ─────────────────────────────────────────────────── -->
<div class="report-grid" style="margin-bottom:40px">

    <!-- Pesanan Terbaru -->
    <div class="panel">
        <div class="panel-header">
            <h2><i class="fa fa-clock" style="color:var(--green);margin-right:8px"></i>Pesanan Terbaru</h2>
            <a href="?page=dashboard&tab=orders" style="font-size:12px;color:var(--green)">Lihat semua →</a>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><b><?= e($order['order_code']) ?></b></td>
                            <td><?= e($order['name']) ?></td>
                            <td><?= money($order['total']) ?></td>
                            <td>
                                <span
                                    class="status status-badge <?= statusClass($order['status']) ?> <?= strtolower(trim($order['status'])) ?>">
                                    <?= e($order['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$recentOrders): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted" style="padding:24px">Belum ada pesanan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stok Produk -->
    <div class="panel">
        <div class="panel-header">
            <h2><i class="fa fa-exclamation-triangle" style="color:var(--yellow);margin-right:8px"></i>Stok Menipis</h2>
            <a href="?page=dashboard&tab=stock" style="font-size:12px;color:var(--green)">Kelola stok →</a>
        </div>
        <?php foreach ($lowStock as $item): ?>
            <div class="stock-row">
                <span><?= e($item['name']) ?></span>
                <b class="<?= $item['stock'] <= 10 ? 'low' : '' ?>">
                    <?= $item['stock'] ?>     <?= e($item['unit']) ?>
                </b>
            </div>
        <?php endforeach; ?>
        <?php if (!$lowStock): ?>
            <p class="text-muted text-center" style="padding:24px 0">Semua stok aman</p>
        <?php endif; ?>
    </div>

</div>