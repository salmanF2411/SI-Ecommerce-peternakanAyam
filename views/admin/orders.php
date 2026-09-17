<?php
/**
 * Halaman Admin: Kelola Pesanan
 * FR-11 — Lihat, Rincian Detail & Update Status Pesanan
 *
 * @var array $orders
 * @var array $orderItems
 * @var array $user
 */

$modalPayload = [];
foreach ($orders as $order) {
    $oid = (int) $order['id'];
    $items = $orderItems[$oid] ?? [];
    $modalPayload[$oid] = [
        'id' => $order['id'],
        'order_code' => $order['order_code'],
        'customer_name' => $order['customer_name'] ?: ($order['name'] ?? 'Pelanggan'),
        'customer_phone' => $order['customer_phone'] ?: ($order['phone'] ?? '-'),
        'user_name' => $order['user_name'] ?? ($order['name'] ?? '-'),
        'user_email' => $order['user_email'] ?? '',
        'address' => $order['address'] ?: 'Tidak ada alamat yang dicantumkan',
        'payment_method' => $order['payment_method'] ?: 'Belum ditentukan',
        'notes' => !empty($order['notes']) ? $order['notes'] : '',
        'total' => money($order['total']),
        'status' => $order['status'],
        'status_class' => statusClass($order['status']),
        'created_at' => dateId($order['created_at'], true, true),
        'items' => $items,
    ];
}
?>

<!-- ── Scoped Styling untuk Tombol & Pop-up Modal ────────────────────── -->
<style>
    /* ── Tombol & Form Aksi di Tabel ── */
    .order-action-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-detail {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f0fdf4;
        color: #166534;
        border: 1.5px solid #86efac;
        padding: 6px 13px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 12px;
        cursor: pointer;
        transition: all .2s ease;
        white-space: nowrap;
        text-decoration: none;
    }

    .btn-detail:hover {
        background: #2f5d3a;
        color: #ffffff;
        border-color: #2f5d3a;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(47, 93, 58, 0.2);
    }

    .status-inline-form {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin: 0;
    }

    .status-select-sm {
        padding: 5px 8px;
        border: 1.5px solid #d1d5db;
        border-radius: 6px;
        font-size: 12px;
        background: #ffffff;
        color: #1f2937;
        outline: none;
        transition: border-color .2s ease;
    }

    .status-select-sm:focus {
        border-color: #2f5d3a;
    }

    .btn-save-sm {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #2f5d3a;
        color: #ffffff;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s ease;
    }

    .btn-save-sm:hover {
        background: #1e3d26;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(47, 93, 58, 0.25);
    }

    /* ── Pop-up Modal Backdrop & Dialog ── */
    .order-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        transition: opacity .25s ease;
    }

    .order-modal-backdrop.active {
        display: flex;
        opacity: 1;
        animation: modalBackdropFadeIn .25s ease forwards;
    }

    .order-modal-dialog {
        background: #ffffff;
        width: 100%;
        max-width: 820px;
        max-height: 90vh;
        border-radius: 16px;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.35);
        border: 1px solid rgba(226, 232, 240, 0.9);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transform: scale(0.96) translateY(12px);
        transition: transform .25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .order-modal-backdrop.active .order-modal-dialog {
        transform: scale(1) translateY(0);
        animation: modalSlideUp .25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes modalBackdropFadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes modalSlideUp {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(16px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    /* ── Header Modal ── */
    .order-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 22px 28px;
        border-bottom: 1px solid #e5e7eb;
        background: #fbfaf7;
    }

    .modal-eyebrow {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1.5px;
        color: #2f5d3a;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .modal-title {
        font-size: 22px;
        font-weight: 700;
        margin: 0 0 4px;
        color: #111827;
        font-family: 'DM Sans', sans-serif;
    }

    .modal-subtitle {
        font-size: 13px;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .modal-close-btn {
        background: transparent;
        border: none;
        color: #9ca3af;
        font-size: 24px;
        line-height: 1;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all .2s ease;
    }

    .modal-close-btn:hover {
        background: #fee2e2;
        color: #dc2626;
        transform: rotate(90deg);
    }

    /* ── Body Modal ── */
    .order-modal-body {
        padding: 24px 28px;
        overflow-y: auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Grid 2 Kolom untuk Info */
    .detail-grid {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 20px;
    }

    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    .detail-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 18px 20px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    }

    .card-title {
        font-size: 14px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .card-title i {
        color: #2f5d3a;
        font-size: 15px;
    }

    .info-row {
        display: flex;
        flex-direction: column;
        gap: 3px;
        margin-bottom: 12px;
    }

    .info-row:last-child {
        margin-bottom: 0;
    }

    .info-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6b7280;
    }

    .info-val {
        font-size: 13.5px;
        color: #111827;
        font-weight: 500;
    }

    .address-box {
        margin-top: 4px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 13px;
        line-height: 1.5;
        color: #374151;
        white-space: pre-line;
    }

    .notes-box {
        margin-top: 4px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 13px;
        line-height: 1.5;
        color: #92400e;
    }

    .btn-wa-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #86efac;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        margin-left: 8px;
        transition: all .2s;
    }

    .btn-wa-link:hover {
        background: #22c55e;
        color: #ffffff;
        border-color: #22c55e;
    }

    /* Highlight Box Pembayaran */
    .payment-highlight-box {
        margin-top: 14px;
        background: linear-gradient(135deg, #2f5d3a 0%, #1e3d26 100%);
        color: #ffffff;
        border-radius: 10px;
        padding: 16px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(47, 93, 58, 0.2);
    }

    .highlight-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #c8ddb7;
        display: block;
        margin-bottom: 4px;
    }

    .highlight-total {
        font-size: 26px;
        font-weight: 800;
        margin: 0;
        letter-spacing: -.5px;
    }

    .highlight-desc {
        font-size: 11px;
        color: #d4dfd3;
        display: block;
        margin-top: 6px;
    }

    .badge-payment {
        display: inline-block;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #1f2937;
        width: fit-content;
    }

    /* ── Bagian Daftar Produk ── */
    .detail-products-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
    }

    .products-card-title {
        font-size: 14px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .products-card-title i {
        color: #2f5d3a;
    }

    .modal-table-wrap {
        overflow-x: auto;
        border: 1px solid #f3f4f6;
        border-radius: 8px;
    }

    .modal-product-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .modal-product-table th {
        background: #fbfaf7;
        padding: 10px 14px;
        text-transform: uppercase;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .5px;
        color: #6b7280;
        border-bottom: 1.5px solid #e5e7eb;
    }

    .modal-product-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .modal-product-table tr:last-child td {
        border-bottom: none;
    }

    .item-product-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .item-thumb {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        flex-shrink: 0;
    }

    .item-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .item-name {
        font-weight: 700;
        color: #111827;
        font-size: 13.5px;
    }

    .item-unit {
        font-size: 11.5px;
        color: #6b7280;
    }

    .qty-badge {
        display: inline-block;
        background: #eef2ff;
        color: #4338ca;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
    }

    .modal-product-table tfoot td {
        background: #fbfaf7;
        border-top: 2px solid #e5e7eb;
        padding: 14px;
    }

    /* ── Footer Modal ── */
    .order-modal-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 28px;
        border-top: 1px solid #e5e7eb;
        background: #fbfaf7;
    }

    .btn-print {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ffffff;
        color: #374151;
        border: 1.5px solid #d1d5db;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s;
    }

    .btn-print:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .btn-close-modal {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #1f2937;
        color: #ffffff;
        border: none;
        padding: 8px 18px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s;
    }

    .btn-close-modal:hover {
        background: #111827;
    }

    /* ── Print Styling ── */
    @media print {
        body * {
            visibility: hidden;
        }

        #orderDetailModal,
        #orderDetailModal * {
            visibility: visible;
        }

        #orderDetailModal {
            position: absolute;
            inset: 0;
            background: transparent;
            padding: 0;
            display: block !important;
            opacity: 1 !important;
        }

        .order-modal-dialog {
            box-shadow: none;
            border: none;
            max-width: 100%;
            height: auto;
        }

        .modal-close-btn,
        .order-modal-footer {
            display: none !important;
        }
    }
</style>

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
            <input class="form-control" type="date" name="date_from" value="<?= e($_GET['date_from'] ?? '') ?>">
        </div>

        <div>
            <label class="form-label" style="margin-bottom:4px">Sampai Tanggal</label>
            <input class="form-control" type="date" name="date_to" value="<?= e($_GET['date_to'] ?? '') ?>">
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
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><b><?= e($order['order_code']) ?></b></td>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:2px">
                                <b
                                    style="font-size:13px"><?= e($order['customer_name'] ?: ($order['name'] ?? 'Pelanggan')) ?></b>
                                <small
                                    style="color:var(--muted)"><?= e($order['customer_phone'] ?: ($order['phone'] ?? '-')) ?></small>
                            </div>
                        </td>
                        <td><b><?= money($order['total']) ?></b></td>
                        <td>
                            <small><?= dateId($order['created_at']) ?></small>
                        </td>
                        <td>
                            <span
                                class="status status-badge <?= statusClass($order['status']) ?> <?= strtolower(trim($order['status'])) ?>">
                                <?= e($order['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="order-action-group">
                                <button type="button" class="btn-detail" onclick="openOrderDetail(<?= (int) $order['id'] ?>)"
                                    title="Lihat Rincian Pesanan">
                                    <i class="fa fa-eye"></i> Lihat Detail
                                </button>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <form class="status-inline-form" method="post">
                                        <input type="hidden" name="action" value="update_order">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <select name="status" class="status-select-sm" title="Ubah status pesanan">
                                            <option><?= e($order['status']) ?></option>
                                            <?php foreach (['Menunggu', 'Dikonfirmasi', 'Diproses', 'Selesai', 'Dibatalkan'] as $s): ?>
                                                <?php if ($s !== $order['status']): ?>
                                                    <option><?= $s ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn-save-sm" type="submit" title="Simpan perubahan status">
                                            <i class="fa fa-check"></i> Simpan
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$orders): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;padding:36px;color:var(--muted)">
                            Belum ada pesanan yang sesuai filter
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Modal Pop-up: Rincian Pesanan ──────────────────────────────── -->
<div id="orderDetailModal" class="order-modal-backdrop" onclick="handleBackdropClick(event)">
    <div class="order-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalOrderCode">
        <!-- Header -->
        <div class="order-modal-header">
            <div>
                <div class="modal-eyebrow">RINCIAN PESANAN</div>
                <h2 id="modalOrderCode" class="modal-title">Pesanan #---</h2>
                <div class="modal-subtitle">
                    <i class="fa fa-calendar-alt"></i> <span id="modalOrderDate">---</span>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:12px">
                <span id="modalOrderStatus" class="status status-badge">Menunggu</span>
                <button type="button" class="modal-close-btn" onclick="closeOrderDetailModal()" aria-label="Tutup popup"
                    title="Tutup">
                    &times;
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="order-modal-body">
            <!-- Grid 2 Kolom Info -->
            <div class="detail-grid">
                <!-- Kolom 1: Penerima & Pengiriman -->
                <div class="detail-card">
                    <div class="card-title">
                        <i class="fa fa-map-marker-alt"></i> Penerima & Tujuan Pengiriman
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nama Penerima</span>
                        <b id="modalCustomerName" class="info-val">-</b>
                    </div>
                    <div class="info-row">
                        <span class="info-label">No. Telepon / WhatsApp</span>
                        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:6px">
                            <span id="modalCustomerPhone" class="info-val">-</span>
                            <span id="modalWaBtnWrap"></span>
                        </div>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Akun Pemesan</span>
                        <span id="modalCustomerAccount" class="info-val">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Alamat Pengiriman Lengkap</span>
                        <div id="modalCustomerAddress" class="address-box">-</div>
                    </div>
                    <div class="info-row" style="margin-top:6px">
                        <span class="info-label">Catatan Pesanan dari Pelanggan</span>
                        <div id="modalCustomerNotes" class="notes-box">-</div>
                    </div>
                </div>

                <!-- Kolom 2: Pembayaran & Ringkasan -->
                <div class="detail-card" style="display:flex;flex-direction:column;justify-content:space-between">
                    <div>
                        <div class="card-title">
                            <i class="fa fa-credit-card"></i> Metode & Status Pembayaran
                        </div>
                        <div class="info-row">
                            <span class="info-label">Metode Pembayaran</span>
                            <div id="modalPaymentMethod" class="badge-payment">-</div>
                        </div>
                        <div class="info-row" style="margin-top:10px">
                            <span class="info-label">Status Pesanan Saat Ini</span>
                            <div style="margin-top:4px">
                                <span id="modalStatusBadgeInner" class="status status-badge">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Highlight Total -->
                    <div class="payment-highlight-box">
                        <span class="highlight-label">Total Pembayaran</span>
                        <h3 id="modalTotalAmount" class="highlight-total">Rp 0</h3>
                        <small class="highlight-desc">Termasuk total keseluruhan pesanan pelanggan</small>
                    </div>
                </div>
            </div>

            <!-- Card Produk yang Dipesan -->
            <div class="detail-products-card">
                <div class="products-card-title">
                    <i class="fa fa-box-open"></i> Produk yang Dipesan (<span id="modalItemCount">0</span> item)
                </div>
                <div class="modal-table-wrap">
                    <table class="modal-product-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th style="text-align:right">Harga Satuan</th>
                                <th style="text-align:center">Jumlah (Qty)</th>
                                <th style="text-align:right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="modalProductList">
                            <!-- Injected via JavaScript -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align:right;font-weight:700">Total Keseluruhan:</td>
                                <td id="modalTableTotal"
                                    style="text-align:right;font-weight:800;color:#2f5d3a;font-size:16px">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="order-modal-footer">
            <button type="button" class="btn-print" onclick="printOrderDetail()">
                <i class="fa fa-print"></i> Cetak Struk
            </button>
            <button type="button" class="btn-close-modal" onclick="closeOrderDetailModal()">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ── JavaScript Data & Logika Pop-up ─────────────────────────────── -->
<script>
    window.ORDER_DETAILS_DATA = <?= json_encode($modalPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    function openOrderDetail(orderId) {
        const data = window.ORDER_DETAILS_DATA[orderId];
        if (!data) {
            alert('Data pesanan tidak ditemukan.');
            return;
        }

        // 1. Header
        document.getElementById('modalOrderCode').textContent = 'Pesanan #' + data.order_code;
        document.getElementById('modalOrderDate').textContent = data.created_at;

        const statusBadge = document.getElementById('modalOrderStatus');
        statusBadge.textContent = data.status;
        statusBadge.className = 'status status-badge ' + data.status_class + ' ' + data.status.toLowerCase();

        const statusBadgeInner = document.getElementById('modalStatusBadgeInner');
        statusBadgeInner.textContent = data.status;
        statusBadgeInner.className = 'status status-badge ' + data.status_class + ' ' + data.status.toLowerCase();

        // 2. Info Pelanggan & Pengiriman
        document.getElementById('modalCustomerName').textContent = data.customer_name;
        document.getElementById('modalCustomerPhone').textContent = data.customer_phone;

        // WA Button
        const waWrap = document.getElementById('modalWaBtnWrap');
        waWrap.innerHTML = '';
        if (data.customer_phone && data.customer_phone !== '-') {
            let cleanPhone = data.customer_phone.replace(/\D/g, '');
            if (cleanPhone.startsWith('0')) {
                cleanPhone = '62' + cleanPhone.slice(1);
            }
            if (cleanPhone.length >= 9) {
                const waLink = document.createElement('a');
                waLink.href = 'https://wa.me/' + cleanPhone;
                waLink.target = '_blank';
                waLink.className = 'btn-wa-link';
                waLink.innerHTML = '<i class="fab fa-whatsapp"></i> Chat WA';
                waLink.title = 'Hubungi pelanggan via WhatsApp';
                waWrap.appendChild(waLink);
            }
        }

        // Akun Pemesan
        let accText = data.user_name;
        if (data.user_email) {
            accText += ' (' + data.user_email + ')';
        }
        document.getElementById('modalCustomerAccount').textContent = accText;

        // Alamat
        document.getElementById('modalCustomerAddress').textContent = data.address;

        // Catatan
        const notesEl = document.getElementById('modalCustomerNotes');
        if (data.notes && data.notes.trim() !== '') {
            notesEl.innerHTML = '<i class="fa fa-quote-left" style="opacity:.7;margin-right:4px"></i> ' + escapeHtml(data.notes);
            notesEl.style.display = 'block';
        } else {
            notesEl.innerHTML = '<span style="color:#9ca3af;font-style:italic">Tidak ada catatan dari pelanggan.</span>';
        }

        // 3. Info Pembayaran
        document.getElementById('modalPaymentMethod').textContent = data.payment_method;
        document.getElementById('modalTotalAmount').textContent = data.total;
        document.getElementById('modalTableTotal').textContent = data.total;

        // 4. Daftar Produk
        const tbody = document.getElementById('modalProductList');
        tbody.innerHTML = '';

        const items = data.items || [];
        document.getElementById('modalItemCount').textContent = items.length;

        if (items.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="4" style="text-align:center;padding:24px;color:#9ca3af">Rincian produk tidak ditemukan.</td>';
            tbody.appendChild(tr);
        } else {
            items.forEach(function (item) {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td>
                    <div class="item-product-cell">
                        <img src="${escapeHtml(item.image_url)}" class="item-thumb" alt="${escapeHtml(item.product_name)}" onerror="this.src='https://placehold.co/100x100/f6f1e7/2f5d3a?text=Produk'">
                        <div class="item-info">
                            <span class="item-name">${escapeHtml(item.product_name)}</span>
                            <span class="item-unit">Satuan: ${escapeHtml(item.unit || 'item')}</span>
                        </div>
                    </div>
                </td>
                <td style="text-align:right;font-weight:600">${item.formatted_price}</td>
                <td style="text-align:center">
                    <span class="qty-badge">${item.quantity} ${escapeHtml(item.unit || '')}</span>
                </td>
                <td style="text-align:right;font-weight:700;color:#111827">${item.formatted_subtotal}</td>
            `;
                tbody.appendChild(tr);
            });
        }

        // 5. Tampilkan Modal
        const modal = document.getElementById('orderDetailModal');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeOrderDetailModal() {
        const modal = document.getElementById('orderDetailModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function handleBackdropClick(event) {
        if (event.target && event.target.id === 'orderDetailModal') {
            closeOrderDetailModal();
        }
    }

    // Tutup dengan tombol Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            closeOrderDetailModal();
        }
    });

    // Cetak Rincian Struk
    function printOrderDetail() {
        window.print();
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>