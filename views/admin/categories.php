<?php
/**
 * Halaman Admin: Kelola Kategori
 * PRD Section 5.2, 6.2 — Master Kategori Produk
 *
 * @var array $categories
 * @var array $user
 */
?>

<div class="dash-head">
    <div>
        <span class="eyebrow">MASTER DATA</span>
        <h1>Kategori Produk</h1>
        <p>Kelola kategori untuk mengelompokkan produk di katalog.</p>
    </div>
</div>

<div class="report-grid" style="align-items:start">
    <!-- Form Tambah Kategori -->
    <?php if ($user['role'] === 'admin'): ?>
        <div class="panel">
            <div class="panel-header">
                <h2><i class="fa fa-folder-plus" style="color:var(--green);margin-right:8px"></i>Tambah Kategori</h2>
            </div>
            <form method="post">
                <input type="hidden" name="action" value="save_category">
                <div class="form-group">
                    <label class="form-label">Nama Kategori</label>
                    <input class="form-control" type="text" name="name" placeholder="Contoh: Pakan Ternak" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Slug URL (Opsional)</label>
                    <input class="form-control" type="text" name="slug" placeholder="otomatis jika kosong">
                </div>
                <button class="btn btn-primary" type="submit">
                    <i class="fa fa-save"></i> Simpan Kategori
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Daftar Kategori -->
    <div class="panel" style="<?= $user['role'] !== 'admin' ? 'grid-column: 1 / -1' : '' ?>">
        <div class="panel-header">
            <h2>Daftar Kategori</h2>
            <span><?= count($categories) ?> kategori</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Slug</th>
                        <th>Jumlah Produk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $i => $cat): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><b><?= e($cat['name']) ?></b></td>
                            <td><code><?= e($cat['slug']) ?></code></td>
                            <td>
                                <span class="badge"
                                    style="background:var(--cream);color:var(--dark);padding:4px 10px;border-radius:12px;font-weight:600">
                                    <?= $cat['product_count'] ?? 0 ?> produk
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$categories): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted" style="padding:24px">Belum ada kategori</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>