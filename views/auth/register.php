<?php
/**
 * Halaman: Register
 * FR-01 — Registrasi pelanggan
 */
$pageTitle = 'Buat Akun';
include __DIR__ . '/../layout/header.php';
?>

<section class="auth-shell">
    <!-- Copy -->
    <div class="auth-copy">
        <span class="eyebrow">RUMAH PITIK</span>
        <h1>Mulai belanja lebih mudah.</h1>
        <p>Daftar sekarang dan nikmati katalog produk segar dari Rumah Pitik langsung di genggaman Anda.</p>
    </div>

    <!-- Card -->
    <div class="auth-card">
        <h2>Buat akun pelanggan</h2>
        <form method="post" novalidate>
            <input type="hidden" name="action" value="register">

            <div class="form-group">
                <label class="form-label" for="reg-name">Nama lengkap</label>
                <input
                    class="form-control"
                    type="text"
                    id="reg-name"
                    name="name"
                    placeholder="Ahmad Suryadi"
                    autocomplete="name"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="reg-phone">Nomor telepon</label>
                <input
                    class="form-control"
                    type="tel"
                    id="reg-phone"
                    name="phone"
                    placeholder="0812-3456-7890"
                    autocomplete="tel"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="reg-address">Alamat pengiriman</label>
                <textarea
                    class="form-control"
                    id="reg-address"
                    name="address"
                    rows="3"
                    placeholder="Jl. Merdeka No. 10, Kecamatan..."
                    required
                ></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="reg-email">Email</label>
                <input
                    class="form-control"
                    type="email"
                    id="reg-email"
                    name="email"
                    placeholder="nama@email.com"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="reg-password">Password <small style="color:var(--muted);font-weight:400">(min. 6 karakter)</small></label>
                <input
                    class="form-control"
                    type="password"
                    id="reg-password"
                    name="password"
                    placeholder="••••••••"
                    autocomplete="new-password"
                    minlength="6"
                    required
                >
            </div>

            <button class="btn btn-primary btn-full" type="submit" style="margin-top:8px">
                <i class="fa fa-user-plus"></i> Daftar sekarang
            </button>
        </form>

        <p class="form-note">
            Sudah punya akun? <a href="?page=login">Masuk di sini</a>
        </p>
    </div>
</section>

<?php include __DIR__ . '/../layout/footer.php'; ?>
