<?php
/**
 * Transaksi: riwayat (admin) + nota pembelanjaan (cetak).
 */
require_once 'config/auth.php';
require_login();
$isAdmin = is_admin();
$invoice = $_GET['invoice'] ?? '';

$title = 'Transaksi';
require_once 'includes/header.php';

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-d');
$sales = []; $total = 0;
if ($isAdmin) {
    $q = $pdo->prepare("SELECT s.*, COALESCE(u.name,'-') kasir FROM sales s LEFT JOIN users u ON u.id = s.user_id
        WHERE DATE(s.created_at) BETWEEN ? AND ? ORDER BY s.created_at DESC");
    $q->execute([$from, $to]);
    $sales = $q->fetchAll();
} else {
    $q = $pdo->prepare("SELECT s.*, COALESCE(u.name,'-') kasir FROM sales s LEFT JOIN users u ON u.id = s.user_id
        WHERE s.user_id = ? AND DATE(s.created_at) BETWEEN ? AND ? ORDER BY s.created_at DESC");
    $q->execute([current_user()['id'], $from, $to]);
    $sales = $q->fetchAll();
}
$total = array_sum(array_column($sales, 'total'));

// Nota transaksi (jika parameter invoice ada)
$receipt = null; $receiptItems = [];
if ($invoice !== '') {
    $receiptQuery = $pdo->prepare("SELECT s.*, COALESCE(u.name,'-') kasir FROM sales s LEFT JOIN users u ON u.id = s.user_id WHERE s.invoice_no = ? LIMIT 1");
    $receiptQuery->execute([$invoice]);
    $receipt = $receiptQuery->fetch();
    if ($receipt) {
        $itemQuery = $pdo->prepare("SELECT si.*, p.name FROM sale_items si JOIN products p ON p.id = si.product_id WHERE si.sale_id = ? ORDER BY si.id");
        $itemQuery->execute([$receipt['id']]);
        $receiptItems = $itemQuery->fetchAll();
    }
}
?>
<?php
$heading = $invoice !== '' ? ($isAdmin ? 'Riwayat Transaksi' : 'Nota Pembelanjaan') : ($isAdmin ? 'Riwayat Transaksi' : 'History Penjualan');
$subtitle = $invoice !== '' ? ($isAdmin ? 'Lihat seluruh transaksi kasir, periode, dan metode pembayaran.' : 'Nota transaksi pelanggan — siap dicetak.') : ($isAdmin ? 'Lihat seluruh transaksi kasir, periode, dan metode pembayaran.' : 'Lihat riwayat penjualan Anda, lengkap dengan metode pembayaran dan nota.');
?>
<div class="page-head">
  <div>
    <h1><?= $heading ?></h1>
    <p><?= $subtitle ?></p>
  </div>
  <div class="page-head-actions">
    <?php if ($invoice === '' || $isAdmin): ?><a class="btn btn-ghost" href="kasir.php"><?= svg_icon('cart', 16) ?> Buka Kasir</a><?php endif; ?>
    <?php if ($receipt): ?><button class="btn btn-primary" onclick="window.print()"><?= svg_icon('printer', 16) ?> Cetak Nota</button><?php endif; ?>
  </div>
</div>

<?php if ($receipt): ?>
<div class="receipt-actions print-hide">
  <a class="btn btn-light" href="transaksi.php">← Kembali ke History</a>
</div>
<section class="receipt print-receipt">
  <div class="receipt-head">
    <div class="receipt-logo"><?= svg_icon('store', 20) ?></div>
    <strong><?= APP_NAME ?></strong>
    <span>Nota Pembelanjaan</span>
  </div>
  <div class="receipt-meta">
    <div><span>No. Transaksi</span><strong><?= e($receipt['invoice_no']) ?></strong></div>
    <div><span>Tanggal</span><span><?= date('d/m/Y H:i', strtotime($receipt['created_at'])) ?></span></div>
    <div><span>Kasir</span><span><?= e($receipt['kasir']) ?></span></div>
  </div>
  <div class="receipt-items">
    <?php foreach ($receiptItems as $item): ?>
    <div class="receipt-item">
      <div>
        <strong><?= e($item['name']) ?></strong>
        <small><?= e((string)$item['qty']) ?> × <?= rupiah($item['price']) ?></small>
      </div>
      <b><?= rupiah($item['subtotal']) ?></b>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="receipt-totals">
    <div class="grand"><span>Total</span><strong><?= rupiah($receipt['total']) ?></strong></div>
    <div><span>Bayar (<?= e($receipt['payment_method']) ?>)</span><span><?= rupiah($receipt['paid']) ?></span></div>
    <div><span>Kembalian</span><span><?= rupiah($receipt['change_amount']) ?></span></div>
  </div>
  <p class="receipt-thanks">Terima kasih sudah berbelanja 🙏</p>
</section>
<?php endif; ?>

<?php if ($invoice === ''): ?>
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
      <button class="btn btn-primary"><?= svg_icon('search', 15) ?> Terapkan</button>
    </form>
    <span class="badge"><?= count($sales) ?> transaksi</span>
  </div>
</div>

<div class="card panel" style="margin-top:18px">
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr><th>No</th><th>No. Transaksi</th><th>Tanggal</th><th>Kasir</th><th>Metode</th><th style="text-align:right">Total</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($sales as $i => $s): ?>
        <tr <?= $invoice === $s['invoice_no'] ? 'style="background:var(--green-50)"' : '' ?>>
          <td><?= $i + 1 ?></td>
          <td><strong><?= e($s['invoice_no']) ?></strong></td>
          <td><?= date('d-m-Y H:i', strtotime($s['created_at'])) ?></td>
          <td><?= e($s['kasir']) ?></td>
          <td><span class="badge <?= $s['payment_method'] === 'QRIS' ? 'blue' : ($s['payment_method'] === 'Debit' ? 'purple' : '') ?>"><?= e($s['payment_method']) ?></span></td>
          <td style="text-align:right"><strong><?= rupiah($s['total']) ?></strong></td>
          <td><a class="btn-icon view" href="transaksi.php?invoice=<?= urlencode($s['invoice_no']) ?>&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>" title="Lihat nota"><?= svg_icon('receipt', 14) ?></a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$sales): ?><tr><td colspan="7" class="empty">Belum ada transaksi pada periode ini.</td></tr><?php endif; ?>
      </tbody>
      <?php if ($sales): ?>
      <tfoot>
        <tr><th colspan="5" style="text-align:right">Total Penjualan Periode Ini</th><th style="text-align:right"><?= rupiah($total) ?></th><th></th></tr>
      </tfoot>
      <?php endif; ?>
    </table>
  </div>
</div>
<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>
