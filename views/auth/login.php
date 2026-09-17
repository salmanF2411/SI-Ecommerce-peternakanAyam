<?php
/**
 * Halaman: Login
 * FR-02 — Login pengguna
 */
$pageTitle = 'Masuk ke Akun';
include __DIR__ . '/../layout/header.php';
?>

<section class="auth-shell">
    <!-- Copy -->
    <div class="auth-copy">
        <span class="eyebrow">RUMAH PITIK</span>
        <h1>Selamat datang kembali.</h1>
        <p>Akses katalog segar, pesanan, dan informasi stok dalam satu tempat.</p>
    </div>

    <!-- Card -->
    <div class="auth-card">
        <h2>Masuk ke akun</h2>
        <form method="post" novalidate>
            <input type="hidden" name="action" value="login">

            <div class="form-group">
                <label class="form-label" for="login-email">Email</label>
                <input class="form-control" type="email" id="login-email" name="email" placeholder="nama@email.com"
                    autocomplete="email" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="login-password">Password</label>
                <input class="form-control" type="password" id="login-password" name="password" placeholder="••••••••"
                    autocomplete="current-password" required>
            </div>

            <button class="btn btn-primary btn-full" type="submit" style="margin-top:8px">
                <i class="fa fa-sign-in-alt"></i> Masuk
            </button>
        </form>

        <p class="form-note">
            Belum punya akun? <a href="?page=register">Daftar di sini</a>
        </p>
    </div>
</section>

<?php include __DIR__ . '/../layout/footer.php'; ?>