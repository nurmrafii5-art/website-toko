<?php
require_once 'config/auth.php';
require_admin();
$title = 'Dashboard';
require_once 'includes/header.php';

$today = date('Y-m-d');

// Ringkasan hari ini
$todaySales = $pdo->prepare("SELECT COALESCE(SUM(total),0) total, COUNT(*) cnt FROM sales WHERE DATE(created_at)=?");
$todaySales->execute([$today]);
$ts = $todaySales->fetch();

$productCount = (int)$pdo->query('SELECT COUNT(*) c FROM products')->fetch()['c'];

// Stok menipis (stok <= 5)
$lowStock = $pdo->query('SELECT p.name, p.stock, p.image_url FROM products p WHERE p.stock <= 5 ORDER BY p.stock ASC LIMIT 5')->fetchAll();

// Grafik penjualan 7 hari terakhir
$days = []; $vals = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $days[] = date('d M', strtotime($d));
    $q = $pdo->prepare('SELECT COALESCE(SUM(total),0) total FROM sales WHERE DATE(created_at)=?');
    $q->execute([$d]);
    $vals[] = (float)$q->fetch()['total'];
}
$max = max($vals) ?: 1;
$weekTotal = array_sum($vals);

// Transaksi terbaru
$recent = $pdo->query("SELECT s.invoice_no, s.total, s.payment_method, s.created_at, COALESCE(u.name,'-') kasir
    FROM sales s LEFT JOIN users u ON u.id = s.user_id
    ORDER BY s.created_at DESC LIMIT 6")->fetchAll();

// Produk terlaris
$best = $pdo->query("SELECT p.id, p.name, p.image_url, SUM(si.qty) terjual, SUM(si.subtotal) omzet
    FROM sale_items si
    JOIN sales s ON s.id = si.sale_id
    JOIN products p ON p.id = si.product_id
    GROUP BY p.id, p.name, p.image_url
    ORDER BY terjual DESC LIMIT 5")->fetchAll();
$bestMax = 1;
foreach ($best as $b) $bestMax = max($bestMax, (int)$b['terjual']);
?>
<div class="page-head">
  <div>
    <h1>Dashboard</h1>
    <p>Selamat datang kembali, <?= e(current_user()['name']) ?>! Ringkasan toko hari ini.</p>
  </div>
  <div class="page-head-actions">
    <a class="btn btn-primary" href="kasir.php"><?= svg_icon('cart', 16) ?> Buka Kasir</a>
  </div>
</div>

<!-- Kartu ringkasan -->
<div class="grid-4">
  <div class="card stat-card">
    <div class="stat-icon"><?= svg_icon('wallet', 20) ?></div>
    <div class="stat-info">
      <small>Total Penjualan Hari Ini</small>
      <strong><?= rupiah($ts['total']) ?></strong>
      <span class="trend"><?= svg_icon('trending', 13) ?> Minggu ini <?= rupiah($weekTotal) ?></span>
    </div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon blue"><?= svg_icon('cart', 20) ?></div>
    <div class="stat-info">
      <small>Jumlah Transaksi Hari Ini</small>
      <strong><?= (int)$ts['cnt'] ?></strong>
      <span class="trend"><?= svg_icon('checkcircle', 13) ?> Transaksi berhasil</span>
    </div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon purple"><?= svg_icon('package', 20) ?></div>
    <div class="stat-info">
      <small>Jumlah Produk</small>
      <strong><?= $productCount ?></strong>
      <span class="trend"><?= svg_icon('checkcircle', 13) ?> Produk terdaftar</span>
    </div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon amber"><?= svg_icon('alert', 20) ?></div>
    <div class="stat-info">
      <small>Stok Menipis</small>
      <strong><?= count($lowStock) ?></strong>
      <span class="trend warn"><?= svg_icon('alert', 13) ?> Perlu dicek</span>
    </div>
  </div>
</div>

<!-- Grafik + stok menipis -->
<div class="dashboard-grid">
  <div class="card panel">
    <div class="panel-title">
      <h3><?= svg_icon('chart', 17) ?> Grafik Penjualan (7 Hari Terakhir)</h3>
      <span class="hint">Total: <?= rupiah($weekTotal) ?></span>
    </div>
    <div class="chart">
      <?php foreach ($vals as $i => $v): ?>
      <div class="bar-wrap"><div class="bar" style="--h:<?= max(7, round(($v / $max) * 190)) ?>px" title="<?= rupiah($v) ?>"></div></div>
      <?php endforeach; ?>
    </div>
    <div class="chart-labels">
      <?php foreach ($days as $d): ?><span><?= e($d) ?></span><?php endforeach; ?>
    </div>
  </div>

  <div class="card panel">
    <div class="panel-title">
      <h3><?= svg_icon('alert', 17) ?> Stok Menipis</h3>
      <a class="hint" href="produk.php">Kelola →</a>
    </div>
    <div class="stock-list">
      <?php if (!$lowStock): ?>
      <div class="empty"><?= svg_icon('checkcircle', 30) ?><strong>Semua stok aman</strong>Tidak ada produk dengan stok ≤ 5.</div>
      <?php else: foreach ($lowStock as $p): ?>
      <div class="stock-item">
        <img class="product-thumb" src="<?= e($p['image_url'] ?: 'assets/img/coffee.svg') ?>" alt="<?= e($p['name']) ?>">
        <div class="stock-info">
          <strong><?= e($p['name']) ?></strong>
          <small>Sisa stok: <?= e((string)$p['stock']) ?></small>
        </div>
        <span class="badge <?= $p['stock'] <= 2 ? 'red' : 'orange' ?>"><?= $p['stock'] <= 2 ? 'Kritis' : 'Menipis' ?></span>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<!-- Transaksi terbaru + produk terlaris -->
<div class="dashboard-grid">
  <div class="card panel">
    <div class="panel-title">
      <h3><?= svg_icon('receipt', 17) ?> Transaksi Terbaru</h3>
      <a class="hint" href="transaksi.php">Lihat semua →</a>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>No. Transaksi</th><th>Kasir</th><th>Waktu</th><th>Metode</th><th style="text-align:right">Total</th></tr></thead>
        <tbody>
          <?php if (!$recent): ?>
          <tr><td colspan="5" class="empty">Belum ada transaksi. Mulai dari halaman Kasir.</td></tr>
          <?php else: foreach ($recent as $r): ?>
          <tr>
            <td><strong><?= e($r['invoice_no']) ?></strong></td>
            <td><?= e($r['kasir']) ?></td>
            <td><?= date('d M, H:i', strtotime($r['created_at'])) ?></td>
            <td><span class="badge <?= $r['payment_method'] === 'QRIS' ? 'blue' : ($r['payment_method'] === 'Debit' ? 'purple' : '') ?>"><?= e($r['payment_method']) ?></span></td>
            <td style="text-align:right"><strong><?= rupiah($r['total']) ?></strong></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card panel">
    <div class="panel-title">
      <h3><?= svg_icon('star', 17) ?> Produk Terlaris</h3>
    </div>
    <?php if (!$best): ?>
    <div class="empty"><?= svg_icon('bag', 30) ?><strong>Belum ada data penjualan</strong>Produk terlaris akan muncul setelah ada transaksi.</div>
    <?php else: ?>
    <div class="rank-list">
      <?php foreach ($best as $i => $b): ?>
      <div class="rank-item">
        <span class="rank-num"><?= $i + 1 ?></span>
        <div class="rank-info">
          <strong><?= e($b['name']) ?></strong>
          <div class="rank-bar"><i style="width:<?= max(6, round(((int)$b['terjual'] / $bestMax) * 100)) ?>%"></i></div>
        </div>
        <div class="rank-value"><?= (int)$b['terjual'] ?> terjual<small><?= rupiah($b['omzet']) ?></small></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
