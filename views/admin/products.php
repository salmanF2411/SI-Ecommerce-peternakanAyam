<?php
/**
 * Halaman Admin: Kelola Produk
 * FR-09 — CRUD produk
 *
 * @var array $products
 * @var array $categories
 * @var bool  $showForm     Tampilkan form tambah/edit
 * @var array $editProduct  Data produk yang diedit (optional)
 */
?>

<div class="dash-head">
    <div>
        <span class="eyebrow">MANAJEMEN PRODUK</span>
        <h1>Kelola Produk</h1>
        <p><?= count($products) ?> produk tercatat di sistem</p>
    </div>
    <a href="?page=dashboard&tab=products&new=1"
       style="display:inline-flex;align-items:center;gap:8px;
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
</div>

<!-- ── Form Tambah / Edit ─────────────────────────────────────────── -->
<?php if ($showForm): ?>
<div class="panel">
    <div class="panel-header">
        <h2><?= isset($editProduct) ? 'Edit Produk' : 'Tambah Produk Baru' ?></h2>
        <a href="?page=dashboard&tab=products" style="font-size:13px;color:var(--muted)">
            <i class="fa fa-times"></i> Batal
        </a>
    </div>

    <form class="product-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save_product">
        <?php if (isset($editProduct)): ?>
        <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
        <input type="hidden" name="image_current" value="<?= e($editProduct['image'] ?? '') ?>">
        <?php endif; ?>

        <div class="form-group">
            <label class="form-label">Nama Produk</label>
            <input class="form-control" type="text" name="name"
                value="<?= e($editProduct['name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Kategori</label>
            <select class="form-control" name="category_id" required>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"
                    <?= (($editProduct['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Harga (Rp)</label>
            <input class="form-control" type="number" name="price" min="0" step="500"
                value="<?= $editProduct['price'] ?? '' ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Stok</label>
            <input class="form-control" type="number" name="stock" min="0"
                value="<?= $editProduct['stock'] ?? 0 ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Satuan</label>
            <select class="form-control" name="unit">
                <?php foreach (['kg', 'ekor', 'karung', 'rak', 'pcs', 'gram'] as $unit): ?>
                <option <?= (($editProduct['unit'] ?? 'kg') === $unit) ? 'selected' : '' ?>>
                    <?= $unit ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Status</label>
            <label style="display:flex;align-items:center;gap:8px;font-weight:400;margin-top:10px;cursor:pointer">
                <input type="checkbox" name="is_active" value="1"
                    <?= ($editProduct['is_active'] ?? 1) ? 'checked' : '' ?>>
                Aktif di katalog
            </label>
        </div>

        <div class="form-group full">
            <label class="form-label">Deskripsi Produk</label>
            <textarea class="form-control" name="description" rows="4" required><?= e($editProduct['description'] ?? '') ?></textarea>
        </div>

        <!-- ── Upload Foto Produk ── -->
        <div class="form-group full">
            <label class="form-label">Foto Produk</label>

            <?php if (!empty($editProduct['image'])): ?>
            <!-- Preview gambar yang sudah ada -->
            <div id="current-image-wrap" style="margin-bottom:12px">
                <p style="font-size:12px;color:var(--muted);margin-bottom:6px">Foto saat ini:</p>
                <img id="current-preview"
                    src="<?= e(productImage($editProduct['image'])) ?>"
                    alt="Foto produk saat ini"
                    style="max-height:140px;border-radius:var(--radius);border:1px solid var(--line);display:block">
            </div>
            <?php endif; ?>

            <div style="border:2px dashed var(--line);border-radius:var(--radius);padding:20px;text-align:center;background:var(--surface);position:relative;cursor:pointer"
                 id="upload-drop-zone"
                 onclick="document.getElementById('product-image-file').click()">
                <input type="file"
                    id="product-image-file"
                    name="image_file"
                    accept="image/jpeg,image/png,image/webp"
                    style="display:none">
                <div id="upload-placeholder">
                    <i class="fa fa-cloud-upload" style="font-size:28px;color:var(--muted);margin-bottom:8px;display:block"></i>
                    <p style="margin:0;font-size:14px;color:var(--muted)">
                        Klik atau seret foto ke sini<br>
                        <span style="font-size:12px">Format: JPG, PNG, WebP · Maks 5 MB</span>
                    </p>
                </div>
                <div id="upload-preview-wrap" style="display:none">
                    <img id="image-new-preview" src="" alt="Preview baru"
                        style="max-height:160px;border-radius:var(--radius);margin-bottom:8px;border:1px solid var(--line)">
                    <p id="upload-filename" style="font-size:12px;color:var(--muted);margin:0"></p>
                    <button type="button" id="btn-remove-image"
                        onclick="event.stopPropagation();removeImagePreview()"
                        style="margin-top:8px;font-size:12px;color:var(--danger);background:none;border:none;cursor:pointer">
                        <i class="fa fa-times"></i> Hapus pilihan
                    </button>
                </div>
            </div>
        </div>

        <div class="full" style="display:flex;gap:12px;margin-top:8px">
            <button class="btn btn-primary" type="submit">
                <i class="fa fa-save"></i> Simpan Produk
            </button>
            <a class="btn btn-outline" href="?page=dashboard&tab=products">Batal</a>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- ── Products Table ─────────────────────────────────────────────── -->
<div class="panel">
    <div class="panel-header">
        <h2>Daftar Produk</h2>
        <span><?= count($products) ?> produk</span>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $i => $item): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <img class="table-img"
                            src="<?= e(productImage($item['image'])) ?>"
                            alt="<?= e($item['name']) ?>"
                            style="width:52px;height:52px;object-fit:cover;border-radius:8px;border:1px solid var(--line)">
                    </td>
                    <td><b><?= e($item['name']) ?></b></td>
                    <td><?= e($item['category_name']) ?></td>
                    <td><?= money($item['price']) ?> <small style="color:var(--muted)">/ <?= e($item['unit']) ?></small></td>
                    <td><?= $item['stock'] ?> <?= e($item['unit']) ?></td>
                    <td>
                        <span class="status-badge <?= $item['is_active'] ? 'status-selesai' : 'status-dibatalkan' ?>">
                            <?= $item['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <!-- Tombol Edit (selalu tampil) -->
                            <a class="btn btn-outline btn-sm"
                                href="?page=dashboard&tab=products&edit=<?= $item['id'] ?>">
                                <i class="fa fa-edit"></i> Edit
                            </a>

                            <?php if ($item['is_active']): ?>
                            <!-- Tombol Nonaktifkan -->
                            <form method="post" style="display:inline">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <button
                                    class="btn btn-danger btn-sm"
                                    type="submit"
                                    data-confirm="Nonaktifkan produk '<?= e($item['name']) ?>'?"
                                >
                                    <i class="fa fa-ban"></i> Nonaktifkan
                                </button>
                            </form>
                            <?php else: ?>
                            <!-- Tombol Aktifkan Kembali -->
                            <form method="post" style="display:inline">
                                <input type="hidden" name="action" value="activate_product">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <button
                                    class="btn btn-sm"
                                    style="background:var(--green);color:#fff;border:none"
                                    type="submit"
                                    data-confirm="Aktifkan kembali produk '<?= e($item['name']) ?>'?"
                                >
                                    <i class="fa fa-check"></i> Aktifkan
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$products): ?>
                <tr><td colspan="8" class="text-center text-muted" style="padding:32px">Belum ada produk</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    const fileInput   = document.getElementById('product-image-file');
    const dropZone    = document.getElementById('upload-drop-zone');
    const placeholder = document.getElementById('upload-placeholder');
    const previewWrap = document.getElementById('upload-preview-wrap');
    const newPreview  = document.getElementById('image-new-preview');
    const filename    = document.getElementById('upload-filename');
    const currentWrap = document.getElementById('current-image-wrap');

    if (!fileInput) return;

    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        showPreview(file);
    });

    // Drag & drop
    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.style.borderColor = 'var(--green)';
        this.style.background  = 'rgba(47,93,58,.06)';
    });
    dropZone.addEventListener('dragleave', function () {
        this.style.borderColor = 'var(--line)';
        this.style.background  = 'var(--surface)';
    });
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        this.style.borderColor = 'var(--line)';
        this.style.background  = 'var(--surface)';
        const file = e.dataTransfer.files[0];
        if (file) {
            // DataTransfer ke input
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            showPreview(file);
        }
    });

    function showPreview(file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            newPreview.src    = e.target.result;
            filename.textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
            placeholder.style.display = 'none';
            previewWrap.style.display = 'block';
            if (currentWrap) currentWrap.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }

    window.removeImagePreview = function () {
        fileInput.value   = '';
        newPreview.src    = '';
        placeholder.style.display = 'block';
        previewWrap.style.display = 'none';
        if (currentWrap) currentWrap.style.display = 'block';
    };
})();
</script>
