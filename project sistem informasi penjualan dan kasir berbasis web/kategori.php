<?php
/**
 * Kategori: form tambah/edit + daftar kategori.
 */
require_once 'config/auth.php';
require_admin();
$title = 'Kategori';
require_once 'includes/header.php';

$cats = $pdo->query('SELECT c.*, COUNT(p.id) total FROM categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id ORDER BY c.name')->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare('SELECT * FROM categories WHERE id=?');
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch();
}
?>
<div class="page-head">
  <div>
    <h1>Kategori</h1>
    <p>Kelola pengelompokan produk agar kasir lebih mudah menemukan barang.</p>
  </div>
</div>

<div class="dashboard-grid" style="margin-top:0">
  <!-- ===== Form kategori ===== -->
  <div class="card panel">
    <div class="panel-title">
      <h3><?= svg_icon('tag', 17) ?> <?= $edit ? 'Edit Kategori' : 'Tambah Kategori' ?></h3>
      <?php if ($edit): ?><a class="hint" href="kategori.php">Batalkan edit</a><?php endif; ?>
    </div>
    <form method="post" action="actions/category.php">
      <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">
      <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
      <div class="form-group">
        <label>Nama Kategori</label>
        <input class="input" name="name" required value="<?= e($edit['name'] ?? '') ?>" placeholder="Contoh: Minuman">
      </div>
      <div style="margin-top:16px;display:flex;gap:10px">
        <button class="btn btn-primary"><?= $edit ? svg_icon('check', 16) . ' Simpan Perubahan' : svg_icon('plus', 16) . ' Tambah Kategori' ?></button>
        <?php if ($edit): ?><a class="btn btn-ghost" href="kategori.php">Batal</a><?php endif; ?>
      </div>
    </form>
    <div class="form-note">
      <?= svg_icon('info', 16) ?>
      Menghapus kategori tidak menghapus produk di dalamnya — produk hanya kehilangan kategorinya.
    </div>
  </div>

  <!-- ===== Daftar kategori ===== -->
  <div class="card panel">
    <div class="panel-title">
      <h3><?= svg_icon('package', 17) ?> Daftar Kategori <span class="hint"><?= count($cats) ?> kategori</span></h3>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Nama Kategori</th><th>Jumlah Produk</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($cats as $c): ?>
          <tr>
            <td><strong><?= e($c['name']) ?></strong></td>
            <td><span class="badge <?= $c['total'] ? '' : 'gray' ?>"><?= (int)$c['total'] ?> produk</span></td>
            <td>
              <div class="actions" style="display:flex;gap:6px">
                <a class="btn-icon edit" href="kategori.php?edit=<?= $c['id'] ?>" title="Edit"><?= svg_icon('edit', 14) ?></a>
                <a class="btn-icon delete" href="actions/category.php?action=delete&id=<?= $c['id'] ?>"
                   onclick="return confirmDelete('Hapus kategori ini? Produk tidak akan ikut terhapus.')" title="Hapus"><?= svg_icon('trash', 14) ?></a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$cats): ?><tr><td colspan="3" class="empty">Belum ada kategori.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
