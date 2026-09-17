<?php
/**
 * Halaman Admin: Laporan Penjualan
 * FR-13 — Laporan penjualan dengan filter periode
 *
 * @var array  $monthly
 * @var array  $summary    total_transactions, total_items, total_revenue
 * @var string $period     harian|bulanan|tahunan
 * @var string $dateFrom
 * @var string $dateTo
 */
?>

<div class="dash-head">
    <div>
        <span class="eyebrow">LAPORAN PENJUALAN</span>
        <h1>Laporan Penjualan</h1>
        <p>Ringkasan transaksi selesai per periode.</p>
    </div>
</div>

<!-- ── Filter Form ────────────────────────────────────────────────── -->
<div class="panel" style="margin-bottom:24px">
    <form method="get" style="display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap">
        <input type="hidden" name="page" value="dashboard">
        <input type="hidden" name="tab" value="reports">

        <div class="form-group" style="margin:0">
            <label class="form-label">Periode</label>
            <select class="form-control" name="period" id="report-period">
                <option value="bulanan" <?= $period === 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                <option value="harian" <?= $period === 'harian' ? 'selected' : '' ?>>Harian</option>
                <option value="tahunan" <?= $period === 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
            </select>
        </div>

        <div id="date-range" style="display:flex;gap:10px;align-items:flex-end">
            <div class="form-group" style="margin:0">
                <label class="form-label">Dari</label>
                <input class="form-control" type="date" name="date_from" value="<?= e($dateFrom) ?>">
            </div>
            <div class="form-group" style="margin:0">
                <label class="form-label">Sampai</label>
                <input class="form-control" type="date" name="date_to" value="<?= e($dateTo) ?>">
            </div>
        </div>

        <button class="btn btn-primary" type="submit">
            <i class="fa fa-search"></i> Tampilkan
        </button>
        <a class="btn btn-outline" href="?page=dashboard&tab=reports">
            <i class="fa fa-undo"></i> Reset
        </a>
    </form>
</div>

<!-- ── Summary Cards ──────────────────────────────────────────────── -->
<div class="stat-grid" style="margin-bottom:24px">
    <div class="stat-card">
        <span class="stat-label"><i class="fa fa-receipt"></i> Total Transaksi</span>
        <b class="stat-value"><?= $summary['total_transactions'] ?></b>
        <span class="stat-meta">pesanan selesai</span>
    </div>
    <div class="stat-card">
        <span class="stat-label"><i class="fa fa-box"></i> Produk Terjual</span>
        <b class="stat-value"><?= $summary['total_items'] ?></b>
        <span class="stat-meta">unit / satuan</span>
    </div>
    <div class="stat-card green">
        <span class="stat-label"><i class="fa fa-money-bill-wave"></i> Total Pendapatan</span>
        <b class="stat-value" style="font-size:20px"><?= money($summary['total_revenue']) ?></b>
        <span class="stat-meta">akumulasi</span>
    </div>
    <div class="stat-card">
        <span class="stat-label"><i class="fa fa-chart-line"></i> Rata-rata / Transaksi</span>
        <b class="stat-value" style="font-size:20px">
            <?= $summary['total_transactions'] > 0
                ? money($summary['total_revenue'] / $summary['total_transactions'])
                : 'Rp 0' ?>
        </b>
        <span class="stat-meta">per pesanan</span>
    </div>
</div>

<!-- ── Tabel Laporan ──────────────────────────────────────────────── -->
<div class="panel">
    <div class="panel-header">
        <h2>Detail Laporan</h2>
        <span><?= count($monthly) ?> periode</span>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Periode</th>
                    <th>Total Transaksi</th>
                    <th>Produk Terjual</th>
                    <th>Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monthly as $row): ?>
                    <tr>
                        <td><b><?= e($row['period']) ?></b></td>
                        <td><?= $row['transactions'] ?> pesanan</td>
                        <td><?= $row['items_sold'] ?? '-' ?></td>
                        <td><b style="color:var(--green)"><?= money($row['revenue']) ?></b></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$monthly): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;padding:32px;color:var(--muted)">
                            Belum ada data transaksi selesai
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($monthly): ?>
        <div style="margin-top:20px;display:flex;gap:10px">
            <button class="btn btn-outline btn-sm" onclick="window.print()">
                <i class="fa fa-print"></i> Cetak
            </button>
        </div>
    <?php endif; ?>
</div>