<?php
/**
 * Laporan Penjualan: ringkasan periode, grafik, dan produk terlaris.
 */
require_once 'config/auth.php';
require_admin();
$title = 'Laporan';
require_once 'includes/header.php';

$from = $_GET['from'] ?? date('Y-m-d', strtotime('-6 days'));
$to   = $_GET['to'] ?? date('Y-m-d');

// Ringkasan periode
$s = $pdo->prepare('SELECT COUNT(*) transaksi, COALESCE(SUM(total),0) penjualan FROM sales WHERE DATE(created_at) BETWEEN ? AND ?');
$s->execute([$from, $to]);
$sum = $s->fetch();

// Produk terlaris pada periode
$items = $pdo->prepare("SELECT p.name, SUM(si.qty) terjual, SUM(si.subtotal) omzet
    FROM sale_items si
    JOIN sales s ON s.id = si.sale_id
    JOIN products p ON p.id = si.product_id
    WHERE DATE(s.created_at) BETWEEN ? AND ?
    GROUP BY p.id, p.name
    ORDER BY terjual DESC LIMIT 8");
$items->execute([$from, $to]);
$items = $items->fetchAll();

// Grafik 8 hari terakhir sampai tanggal "to"
$chart = [];
for ($i = 0; $i < 8; $i++) {
    $d = date('Y-m-d', strtotime("-$i days", strtotime($to)));
    $q = $pdo->prepare('SELECT COALESCE(SUM(total),0) total FROM sales WHERE DATE(created_at) = ?');
    $q->execute([$d]);
    $chart[] = [$d, (float)$q->fetch()['total']];
}
$chart = array_reverse($chart);
$max = max(array_column($chart, 1)) ?: 1;
$chartTotal = array_sum(array_column($chart, 1));
$bestMax = 1;
foreach ($items as $it) $bestMax = max($bestMax, (int)$it['terjual']);
?>
<div class="page-head">
  <div>
    <h1>Laporan Penjualan</h1>
    <p>Analisis penjualan berdasarkan periode yang dipilih.</p>
  </div>
  <div class="page-head-actions">
    <button class="btn btn-primary" onclick="window.print()"><?= svg_icon('printer', 16) ?> Cetak Laporan</button>
  </div>
</div>

<!-- Filter periode -->
<div class="card">
  <div class="filter-card">
    <form method="get" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;flex:1">
      <div class="form-group">
        <label>Dari Tanggal</label>
        <input class="input" type="date" name="from" value="<?= e($from) ?>">
      </div>
      <div class="form-group">
        <label>Sampai Tanggal</label>
        <input class="input" type="date" name="to" value="<?= e($to) ?>">
      </div>
      <button class="btn btn-primary"><?= svg_icon('search', 15) ?> Tampilkan</button>
    </form>
  </div>
</div>

<!-- Ringkasan periode -->
<div class="grid-4" style="margin-top:18px">
  <div class="card stat-card">
    <div class="stat-icon"><?= svg_icon('receipt', 20) ?></div>
    <div class="stat-info">
      <small>Total Transaksi</small>
      <strong><?= (int)$sum['transaksi'] ?></strong>
    </div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon blue"><?= svg_icon('wallet', 20) ?></div>
    <div class="stat-info">
      <small>Total Penjualan</small>
      <strong><?= rupiah($sum['penjualan']) ?></strong>
    </div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon purple"><?= svg_icon('bag', 20) ?></div>
    <div class="stat-info">
      <small>Produk Terjual</small>
      <strong><?= (int)array_sum(array_column($items, 'terjual')) ?></strong>
    </div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon amber"><?= svg_icon('calendar', 20) ?></div>
    <div class="stat-info">
      <small>Periode</small>
      <strong style="font-size:15px"><?= date('d M', strtotime($from)) ?> – <?= date('d M Y', strtotime($to)) ?></strong>
    </div>
  </div>
</div>

<!-- Grafik + produk terlaris -->
<div class="dashboard-grid">
  <div class="card panel">
    <div class="panel-title">
      <h3><?= svg_icon('chart', 17) ?> Grafik Penjualan (8 Hari)</h3>
      <span class="hint">Total: <?= rupiah($chartTotal) ?></span>
    </div>
    <div class="chart">
      <?php foreach ($chart as $c): ?>
      <div class="bar-wrap"><div class="bar" style="--h:<?= max(8, round($c[1] / $max * 190)) ?>px" title="<?= rupiah($c[1]) ?>"></div></div>
      <?php endforeach; ?>
    </div>
    <div class="chart-labels">
      <?php foreach ($chart as $c): ?><span><?= date('d M', strtotime($c[0])) ?></span><?php endforeach; ?>
    </div>
  </div>

  <div class="card panel">
    <div class="panel-title">
      <h3><?= svg_icon('star', 17) ?> Produk Terlaris</h3>
      <span class="hint">Periode terpilih</span>
    </div>
    <?php if (!$items): ?>
    <div class="empty"><?= svg_icon('bag', 30) ?><strong>Belum ada penjualan</strong>Tidak ada produk terjual pada periode ini.</div>
    <?php else: ?>
    <div class="rank-list">
      <?php foreach ($items as $i => $it): ?>
      <div class="rank-item">
        <span class="rank-num"><?= $i + 1 ?></span>
        <div class="rank-info">
          <strong><?= e($it['name']) ?></strong>
          <div class="rank-bar"><i style="width:<?= max(6, round(((int)$it['terjual'] / $bestMax) * 100)) ?>%"></i></div>
        </div>
        <div class="rank-value"><?= (int)$it['terjual'] ?> terjual<small><?= rupiah($it['omzet']) ?></small></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
