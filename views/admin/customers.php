<?php
/**
 * Halaman Admin: Pelanggan
 *
 * @var array $customers
 */
?>

<div class="dash-head">
    <div>
        <span class="eyebrow">DATA PELANGGAN</span>
        <h1>Pelanggan</h1>
        <p><?= count($customers) ?> pelanggan terdaftar</p>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2>Daftar Pelanggan</h2>
        <span><?= count($customers) ?> pelanggan</span>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th>Alamat</th>
                    <th>Terdaftar</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $i => $c): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:32px;height:32px;border-radius:50%;background:var(--green);color:#fff;display:grid;place-items:center;font-size:11px;font-weight:700;flex-shrink:0">
                                <?= strtoupper(substr($c['name'], 0, 2)) ?>
                            </div>
                            <b><?= e($c['name']) ?></b>
                        </div>
                    </td>
                    <td><?= e($c['email']) ?></td>
                    <td><?= e($c['phone'] ?? '-') ?></td>
                    <td style="max-width:200px">
                        <small><?= e(mb_strimwidth($c['address'] ?? '-', 0, 60, '...')) ?></small>
                    </td>
                    <td><small><?= dateId($c['created_at']) ?></small></td>
                    <td>
                        <span class="status-badge <?= $c['is_active'] ? 'status-selesai' : 'status-dibatalkan' ?>">
                            <?= $c['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$customers): ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:32px;color:var(--muted)">
                        Belum ada pelanggan terdaftar
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
