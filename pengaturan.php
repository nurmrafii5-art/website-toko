<?php
/**
 * Pengaturan aplikasi.
 */
require_once 'config/auth.php';
require_admin();
$title = 'Pengaturan';
require_once 'includes/header.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['store_name'] = trim($_POST['store_name'] ?? 'TokoKu');
    $msg = 'Pengaturan tampilan berhasil disimpan.';
}
?>
<div class="page-head">
  <div>
    <h1>Pengaturan</h1>
    <p>Konfigurasi sederhana aplikasi kasir TokoKu.</p>
  </div>
</div>

<div class="grid-2" style="align-items:start">
  <!-- ===== Informasi toko ===== -->
  <div class="card panel">
    <div class="panel-title">
      <h3><?= svg_icon('store', 17) ?> Informasi Toko</h3>
    </div>
    <?php if ($msg): ?><div class="form-note"><?= svg_icon('checkcircle', 16) ?><?= e($msg) ?></div><?php endif; ?>
    <form method="post" style="margin-top:<?= $msg ? '14px' : '0' ?>">
      <div class="form-grid">
        <div class="form-group">
          <label>Nama Toko</label>
          <input class="input" name="store_name" value="<?= e($_SESSION['store_name'] ?? 'TokoKu') ?>">
        </div>
        <div class="form-group">
          <label>Mata Uang</label>
          <input class="input" value="Rupiah (Rp)" disabled>
        </div>
        <div class="form-group full">
          <label>Deskripsi</label>
          <input class="input" value="Sistem Informasi Penjualan &amp; Kasir" disabled>
        </div>
      </div>
      <button class="btn btn-primary" style="margin-top:16px"><?= svg_icon('check', 16) ?> Simpan Perubahan</button>
    </form>
  </div>

  <div style="display:grid;gap:18px">
    <!-- ===== Tentang aplikasi ===== -->
    <div class="card panel">
      <div class="panel-title">
        <h3><?= svg_icon('info', 17) ?> Tentang Aplikasi</h3>
      </div>
      <div class="table-wrap" style="margin:0;padding:0">
        <table class="table">
          <tbody>
            <tr><td style="color:var(--muted)">Versi</td><td style="text-align:right"><strong>1.1</strong></td></tr>
            <tr><td style="color:var(--muted)">Teknologi</td><td style="text-align:right"><span class="badge">PHP Native</span> <span class="badge blue">MySQL</span></td></tr>
            <tr><td style="color:var(--muted)">Login sebagai</td><td style="text-align:right"><strong><?= e(current_user()['name']) ?></strong> (Super Admin)</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ===== Sesi / keluar ===== -->
    <div class="card panel">
      <div class="panel-title">
        <h3><?= svg_icon('logout', 17) ?> Sesi</h3>
      </div>
      <p style="color:var(--muted);font-size:13px;margin-bottom:14px">Keluar dari sistem dan akhiri sesi kamu di perangkat ini.</p>
      <a class="btn btn-danger" href="logout.php"><?= svg_icon('logout', 16) ?> Keluar dari Sistem</a>
    </div>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
