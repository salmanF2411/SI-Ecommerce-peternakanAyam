<?php
/**
 * Halaman Admin: Kelola Pesanan
 * FR-11 — Lihat & update status pesanan
 *
 * @var array  $orders
 * @var array  $user
 */
?>

<div class="dash-head">
    <div>
        <span class="eyebrow">MANAJEMEN PESANAN</span>
        <h1><?= $user['role'] === 'owner' ? 'Monitoring Penjualan' : 'Kelola Pesanan' ?></h1>
        <p><?= count($orders) ?> pesanan tercatat</p>
    </div>
</div>

<!-- ── Filter ─────────────────────────────────────────────────────── -->
<div class="panel" style="margin-bottom:20px">
    <form method="get" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <input type="hidden" name="page" value="dashboard">
        <input type="hidden" name="tab" value="orders">

        <div>
            <label class="form-label" style="margin-bottom:4px">Status</label>
            <select class="form-control" name="status_filter" style="min-width:160px">
                <option value="">Semua Status</option>
                <?php foreach (['Menunggu', 'Dikonfirmasi', 'Diproses', 'Selesai', 'Dibatalkan'] as $s): ?>
                <option value="<?= $s ?>" <?= ($_GET['status_filter'] ?? '') === $s ? 'selected' : '' ?>>
                    <?= $s ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="form-label" style="margin-bottom:4px">Dari Tanggal</label>
            <input class="form-control" type="date" name="date_from"
                value="<?= e($_GET['date_from'] ?? '') ?>">
        </div>

        <div>
            <label class="form-label" style="margin-bottom:4px">Sampai Tanggal</label>
            <input class="form-control" type="date" name="date_to"
                value="<?= e($_GET['date_to'] ?? '') ?>">
        </div>

        <div style="margin-top:20px">
            <button class="btn btn-primary" type="submit">
                <i class="fa fa-filter"></i> Filter
            </button>
            <a class="btn btn-outline btn-sm" href="?page=dashboard&tab=orders" style="margin-left:8px">Reset</a>
        </div>
    </form>
</div>

<!-- ── Orders Table ────────────────────────────────────────────────── -->
<div class="panel">
    <div class="panel-header">
        <h2>Daftar Pesanan</h2>
        <span><?= count($orders) ?> pesanan</span>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <?php if ($user['role'] === 'admin'): ?>
                    <th>Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><b><?= e($order['order_code']) ?></b></td>
                    <td>
                        <div style="display:flex;flex-direction:column;gap:2px">
                            <b style="font-size:13px"><?= e($order['name']) ?></b>
                            <small style="color:var(--muted)"><?= e($order['phone']) ?></small>
                        </div>
                    </td>
                    <td><b><?= money($order['total']) ?></b></td>
                    <td>
                        <small><?= dateId($order['created_at']) ?></small>
                    </td>
                    <td>
                        <span class="status status-badge <?= statusClass($order['status']) ?> <?= strtolower(trim($order['status'])) ?>">
                            <?= e($order['status']) ?>
                        </span>
                    </td>
                    <?php if ($user['role'] === 'admin'): ?>
                    <td>
                        <form class="inline-form" method="post">
                            <input type="hidden" name="action"   value="update_order">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <select name="status">
                                <option><?= e($order['status']) ?></option>
                                <?php foreach (['Menunggu', 'Dikonfirmasi', 'Diproses', 'Selesai', 'Dibatalkan'] as $s): ?>
                                <?php if ($s !== $order['status']): ?>
                                <option><?= $s ?></option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fa fa-check"></i> Simpan
                            </button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if (!$orders): ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:32px;color:var(--muted)">
                        Belum ada pesanan
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
