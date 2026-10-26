<?php
/**
 * Data Produk: form tambah/edit + daftar produk.
 */
require_once 'config/auth.php';
require_admin();
$title = 'Produk';
require_once 'includes/header.php';

$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare('SELECT * FROM products WHERE id=?');
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch();
}
$products = $pdo->query("SELECT p.*, c.name category FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC")->fetchAll();
$cats = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
?>
<div class="page-head">
  <div>
    <h1>Data Produk</h1>
    <p>Kelola produk, harga, kategori, dan stok barang toko.</p>
  </div>
  <div class="page-head-actions">
    <a href="produk.php#form" class="btn btn-primary"><?= svg_icon('plus', 16) ?> Tambah Produk</a>
  </div>
</div>

<!-- ===== Form produk ===== -->
<div class="card panel" id="form">
  <div class="panel-title">
    <h3><?= svg_icon('package', 17) ?> <?= $edit ? 'Edit Produk: ' . e($edit['name']) : 'Produk Baru' ?></h3>
    <?php if ($edit): ?><a class="hint" href="produk.php">Batalkan edit</a><?php endif; ?>
  </div>
  <form method="post" action="actions/product.php">
    <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">
    <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
    <div class="form-grid">
      <div class="form-group">
        <label>Kode Produk</label>
        <input class="input" name="code" required value="<?= e($edit['code'] ?? '') ?>" placeholder="Contoh: P007">
      </div>
      <div class="form-group">
        <label>Nama Produk</label>
        <input class="input" name="name" required value="<?= e($edit['name'] ?? '') ?>" placeholder="Contoh: Kopi Hitam">
      </div>
      <div class="form-group">
        <label>Kategori</label>
        <select class="input" name="category_id">
          <option value="">Tanpa Kategori</option>
          <?php foreach ($cats as $c): ?>
          <option value="<?= $c['id'] ?>" <?= isset($edit['category_id']) && $edit['category_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>URL Ikon / Gambar</label>
        <input class="input" name="image_url" value="<?= e($edit['image_url'] ?? '') ?>" placeholder="assets/img/coffee.svg">
      </div>
      <div class="form-group">
        <label>Harga (Rp)</label>
        <input class="input" type="number" name="price" required min="0" value="<?= e($edit['price'] ?? '') ?>" placeholder="0">
      </div>
      <div class="form-group">
        <label>Stok</label>
        <input class="input" type="number" name="stock" required min="0" value="<?= e($edit['stock'] ?? '') ?>" placeholder="0">
      </div>
    </div>
    <div style="margin-top:18px;display:flex;gap:10px">
      <button class="btn btn-primary"><?= $edit ? svg_icon('check', 16) . ' Simpan Perubahan' : svg_icon('plus', 16) . ' Tambah Produk' ?></button>
      <?php if ($edit): ?><a class="btn btn-ghost" href="produk.php">Batal</a><?php endif; ?>
    </div>
  </form>
</div>

<!-- ===== Tabel produk ===== -->
<div class="card panel" style="margin-top:18px">
  <div class="panel-title">
    <h3><?= svg_icon('package', 17) ?> Daftar Produk <span class="hint"><?= count($products) ?> item</span></h3>
  </div>
  <div class="toolbar">
    <input id="tableSearch" class="search" type="search" placeholder="Cari produk di tabel...">
  </div>
  <div class="table-wrap">
    <table class="table" id="productTable">
      <thead>
        <tr><th>No</th><th>Kode</th><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php foreach ($products as $i => $p): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><strong><?= e($p['code']) ?></strong></td>
          <td>
            <div class="cell-product">
              <img class="product-thumb" src="<?= e($p['image_url'] ?: 'assets/img/coffee.svg') ?>" alt="<?= e($p['name']) ?>">
              <strong><?= e($p['name']) ?></strong>
            </div>
          </td>
          <td><span class="badge <?= $p['category'] ? '' : 'gray' ?>"><?= e($p['category'] ?: 'Tanpa Kategori') ?></span></td>
          <td><?= rupiah($p['price']) ?></td>
          <td>
            <?php if ($p['stock'] <= 0): ?><span class="badge red">Habis</span>
            <?php elseif ($p['stock'] <= 5): ?><span class="badge orange"><?= e((string)$p['stock']) ?> · Menipis</span>
            <?php else: ?><span class="badge"><?= e((string)$p['stock']) ?></span><?php endif; ?>
          </td>
          <td>
            <div class="actions" style="display:flex;gap:6px">
              <a class="btn-icon edit" href="produk.php?edit=<?= $p['id'] ?>" title="Edit"><?= svg_icon('edit', 14) ?></a>
              <a class="btn-icon delete" href="actions/product.php?action=delete&id=<?= $p['id'] ?>"
                 onclick="return confirmDelete('Hapus produk <?= e($p['name']) ?>?')" title="Hapus"><?= svg_icon('trash', 14) ?></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$products): ?><tr><td colspan="7" class="empty">Belum ada produk.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.getElementById('tableSearch').addEventListener('input', e => {
  const q = e.target.value.toLowerCase();
  document.querySelectorAll('#productTable tbody tr').forEach(r => {
    r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
<?php require_once 'includes/footer.php'; ?>
