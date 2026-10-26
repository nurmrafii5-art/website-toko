<?php
require_once 'config/config.php';
require_once 'config/auth.php';
if(isset($_SESSION['user'])){header('Location: '.(($_SESSION['user']['role']??'')==='admin'?'dashboard.php':'kasir.php'));exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $username=trim($_POST['username']??'');$password=$_POST['password']??'';
  $st=$pdo->prepare('SELECT * FROM users WHERE username=? LIMIT 1');$st->execute([$username]);$u=$st->fetch();
  if($u && hash_equals($u['password'],$password)){$_SESSION['user']=['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role']];header('Location: '.($u['role']==='admin'?'dashboard.php':'kasir.php'));exit;}
  if($u){
    $error='Password salah, silakan coba lagi.';
  } else {
    $error='Username atau password salah.';
  }
}
require_once 'includes/icons.php';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login · <?= APP_NAME ?></title>
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%230a7d5f'/%3E%3Cpath d='M9 11h14M16 11v12' stroke='%23fff' stroke-width='3.4' stroke-linecap='round'/%3E%3C/svg%3E">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_url('assets/style.css') ?>">
</head>
<body>
<div class="login-page">

  <!-- Panel kiri: branding -->
  <aside class="login-hero">
    <div class="hero-inner">
      <div class="hero-brand">
        <div class="brand-mark"><?= svg_icon('store', 22) ?></div>
        <strong>TokoKu</strong>
      </div>
      <h1>Kelola penjualan &amp; kasir dengan lebih cepat.</h1>
      <p class="hero-sub">Sistem Informasi Penjualan &amp; Kasir untuk membantu transaksi, pengelolaan produk, stok, dan laporan menjadi lebih rapi dalam satu aplikasi.</p>
      <ul class="hero-feats">
        <li><span class="feat-icon"><?= svg_icon('cart', 16) ?></span> Kasir cepat: pencarian, keranjang &amp; QRIS</li>
        <li><span class="feat-icon"><?= svg_icon('package', 16) ?></span> Manajemen produk, kategori &amp; stok menipis</li>
        <li><span class="feat-icon"><?= svg_icon('chart', 16) ?></span> Laporan penjualan &amp; nota digital siap cetak</li>
      </ul>
      <div class="hero-stat">
        <div><strong>PHP Native</strong><small>Tanpa framework</small></div>
        <div><strong>MySQL</strong><small>Database aman</small></div>
        <div><strong>Real-time</strong><small>Stok langsung terpotong</small></div>
      </div>
    </div>
  </aside>

  <!-- Panel kanan: form login -->
  <main class="login-form-wrap">
    <div class="login-box">
      <div class="login-logo"><?= svg_icon('store', 24) ?></div>
      <h2>Selamat datang 👋</h2>
      <p>Login untuk melanjutkan ke sistem kasir TokoKu.</p>

      <?php if($error): ?><div class="form-note error"><?= svg_icon('alert', 16) ?><?= e($error) ?></div><?php endif; ?>

      <form method="post">
        <div class="form-group">
          <label>Username</label>
          <input class="input" name="username" placeholder="Masukkan username" required autofocus>
        </div>
        <div class="form-group">
          <label>Password</label>
          <div class="password-wrap">
            <input class="input" type="password" name="password" id="loginPassword" placeholder="Masukkan password" required>
            <button type="button" class="password-toggle" id="togglePassword" aria-label="Lihat password"><?= svg_icon('eye', 17) ?></button>
          </div>
        </div>
        <button class="btn btn-primary btn-lg btn-block"><?= svg_icon('logout', 17) ?> Masuk Sekarang</button>
      </form>

      <p class="login-footer">© <?= date('Y') ?> TokoKu — Sistem Informasi Penjualan &amp; Kasir</p>
    </div>
  </main>

</div>
<?php if($error): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const passwordInput = document.getElementById('loginPassword');
  if (passwordInput) {
    passwordInput.value = '';
    passwordInput.focus();
    passwordInput.select();
  }
});
</script>
<?php endif; ?>
<script src="<?= asset_url('assets/app.js') ?>"></script>
</body>
</html>
