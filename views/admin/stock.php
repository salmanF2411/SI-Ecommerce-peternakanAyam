<?php
/**
 * Halaman Admin: Kelola Stok
 * FR-10 — Stok produk, stok masuk, riwayat
 *
 * @var array $products
 * @var array $stockHistory
 * @var array $user
 */
?>

<div class="dash-head">
    <div>
        <span class="eyebrow">MANAJEMEN STOK</span>
        <h1><?= $user['role'] === 'owner' ? 'Monitoring Stok' : 'Kelola Stok' ?></h1>
        <p>Pantau stok produk dan catat pergerakan barang.</p>
    </div>
    <?php if ($user['role'] === 'admin'): ?>
    <button class="btn btn-primary" onclick="document.getElementById('form-stok').style.display='block';this.style.display='none'">
        <i class="fa fa-plus"></i> Tambah Stok Masuk
    </button>
    <?php endif; ?>
</div>

<!-- ── Form Stok Masuk ────────────────────────────────────────────── -->
<?php if ($user['role'] === 'admin'): ?>
<div class="panel" id="form-stok" style="display:none;margin-bottom:24px">
    <div class="panel-header">
        <h2><i class="fa fa-arrow-down" style="color:var(--green)"></i> Tambah Stok Masuk</h2>
        <button onclick="this.closest('#form-stok').style.display='none'" style="background:none;color:var(--muted);font-size:13px">
            <i class="fa fa-times"></i> Tutup
        </button>
    </div>
    <form method="post" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;align-items:end">
        <input type="hidden" name="action" value="add_stock">
        <div class="form-group" style="margin:0">
            <label class="form-label">Produk</label>
            <select class="form-control" name="product_id" required>
                <option value="">-- Pilih Produk --</option>
                <?php foreach ($products as $p): ?>
                <option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (Stok: <?= $p['stock'] ?> <?= e($p['unit']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0">
            <label class="form-label">Jumlah Masuk</label>
            <input class="form-control" type="number" name="quantity" min="1" placeholder="0" required>
        </div>
        <div class="form-group" style="margin:0">
            <label class="form-label">Keterangan</label>
            <input class="form-control" type="text" name="note" placeholder="Contoh: Pembelian dari supplier">
        </div>
        <div style="grid-column:1/-1;display:flex;gap:10px">
            <button class="btn btn-primary" type="submit">
                <i class="fa fa-save"></i> Simpan Stok Masuk
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- ── Tabel Stok Produk ──────────────────────────────────────────── -->
<div class="panel" style="margin-bottom:24px">
    <div class="panel-header">
        <h2>Stok Produk Saat Ini</h2>
        <span><?= count($products) ?> produk aktif</span>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Satuan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $i => $item): ?>
                <?php
                $stockStatus = 'Tersedia';
                $statusCls   = 'status-selesai';
                if ($item['stock'] == 0) {
                    $stockStatus = 'Habis';
                    $statusCls   = 'status-dibatalkan';
                } elseif ($item['stock'] <= 10) {
                    $stockStatus = 'Menipis';
                    $statusCls   = 'status-menunggu';
                }
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><b><?= e($item['name']) ?></b></td>
                    <td><?= e($item['category_name']) ?></td>
                    <td>
                        <b style="font-size:16px;<?= $item['stock'] <= 10 ? 'color:var(--red)' : 'color:var(--green)' ?>">
                            <?= $item['stock'] ?>
                        </b>
                    </td>
                    <td><?= e($item['unit']) ?></td>
                    <td>
                        <span class="status-badge <?= $statusCls ?>">
                            <?= $stockStatus ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Riwayat Pergerakan Stok ────────────────────────────────────── -->
<div class="panel">
    <div class="panel-header">
        <h2>Riwayat Stok</h2>
        <span>30 transaksi terbaru</span>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Produk</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th>Keterangan</th>
                    <th>Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stockHistory as $row): ?>
                <tr>
                    <td><small><?= dateId($row['created_at']) ?></small></td>
                    <td><?= e($row['product_name']) ?></td>
                    <td>
                        <span class="status-badge <?= $row['type'] === 'Masuk' ? 'status-diproses' : ($row['type'] === 'Keluar' ? 'status-menunggu' : 'status-dikonfirmasi') ?>">
                            <?= e($row['type']) ?>
                        </span>
                    </td>
                    <td><b><?= $row['quantity'] ?></b></td>
                    <td><?= e($row['note'] ?? '-') ?></td>
                    <td><?= e($row['admin_name'] ?? 'Sistem') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$stockHistory): ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:32px;color:var(--muted)">
                        Belum ada riwayat stok
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
