<?php
/**
 * Halaman Admin: Laporan Stok
 * FR-14 — Laporan stok (awal, masuk, keluar, akhir)
 *
 * @var array $stockReport
 */
?>

<div class="dash-head">
    <div>
        <span class="eyebrow">LAPORAN STOK</span>
        <h1>Laporan Stok</h1>
        <p>Ringkasan pergerakan stok seluruh produk.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2>Ringkasan Stok Produk</h2>
        <button class="btn btn-outline btn-sm" onclick="window.print()">
            <i class="fa fa-print"></i> Cetak
        </button>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Stok Masuk</th>
                    <th>Stok Keluar</th>
                    <th>Stok Saat Ini</th>
                    <th>Satuan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stockReport as $row): ?>
                    <?php
                    $statusText = 'Tersedia';
                    $statusCls = 'status-selesai';
                    if ($row['stock'] == 0) {
                        $statusText = 'Habis';
                        $statusCls = 'status-dibatalkan';
                    } elseif ($row['stock'] <= 10) {
                        $statusText = 'Menipis';
                        $statusCls = 'status-menunggu';
                    }
                    ?>
                    <tr>
                        <td><b><?= e($row['name']) ?></b></td>
                        <td><?= e($row['category_name']) ?></td>
                        <td style="color:var(--green);font-weight:700">
                            +<?= $row['total_masuk'] ?? 0 ?>
                        </td>
                        <td style="color:var(--red);font-weight:700">
                            -<?= $row['total_keluar'] ?? 0 ?>
                        </td>
                        <td><b style="font-size:16px"><?= $row['stock'] ?></b></td>
                        <td><?= e($row['unit']) ?></td>
                        <td>
                            <span class="status-badge <?= $statusCls ?>">
                                <?= $statusText ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$stockReport): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;padding:32px;color:var(--muted)">
                            Belum ada data stok
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>