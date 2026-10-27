<?php
/**
 * Halaman Kasir (POS).
 * Grid produk + keranjang belanja + pembayaran (Tunai / QRIS / Debit).
 */
$title = 'Kasir';
require_once 'includes/header.php';

$products = $pdo->query("SELECT p.*, COALESCE(c.name,'Tanpa Kategori') category
    FROM products p LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.stock > 0 ORDER BY p.name")->fetchAll();
$cats = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

// Pola QR dekoratif untuk panel pembayaran QRIS (simulasi, bukan kode asli)
$qrisModules = '';
mt_srand(20240601);
for ($y = 0; $y < 21; $y++) {
    for ($x = 0; $x < 21; $x++) {
        $inFinder = ($x < 7 && $y < 7) || ($x > 13 && $y < 7) || ($x < 7 && $y > 13);
        if ($inFinder || mt_rand(0, 1)) continue;
        $qrisModules .= '<rect x="' . ($x * 10) . '" y="' . ($y * 10) . '" width="10" height="10"/>';
    }
}
$qrisModules .= '<path fill-rule="evenodd" d="M0 0h70v70H0zm10 10v50h50V10z M15 15h40v40H15z M20 20h30v30H20z"/>
<path fill-rule="evenodd" d="M140 0h70v70h-70zm10 10v50h50V10z M155 15h40v40h-40z M160 20h30v30h-30z"/>
<path fill-rule="evenodd" d="M0 140h70v70H0zm10 10v50h50v-50z M15 155h40v40H15z M20 160h30v30H20z"/>';
?>
<div class="page-head">
  <div>
    <h1>Kasir</h1>
    <p>Pilih produk, atur keranjang, lalu proses pembayaran pelanggan.</p>
  </div>
</div>

<div class="kasir-layout">

  <!-- ===== Daftar produk ===== -->
  <section class="card panel">
    <div class="kasir-toolbar">
      <input id="productSearch" class="search" type="search" placeholder="Cari nama produk..." autocomplete="off">
    </div>

    <div class="category-tabs" id="categoryTabs">
      <button class="tab active" data-category="all">Semua</button>
      <?php foreach ($cats as $c): ?>
      <button class="tab" data-category="<?= e((string)$c['id']) ?>"><?= e($c['name']) ?></button>
      <?php endforeach; ?>
    </div>

    <div class="product-cards" id="productCards">
      <?php foreach ($products as $p):
        $payload = json_encode([
            'id'    => (int)$p['id'],
            'name'  => $p['name'],
            'price' => (float)$p['price'],
            'image' => $p['image_url'] ?: 'assets/img/coffee.svg',
            'stock' => (int)$p['stock'],
        ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP);
      ?>
      <div class="product-card" data-name="<?= e(strtolower($p['name'])) ?>" data-category="<?= e((string)$p['category_id']) ?>">
        <img class="pc-img" src="<?= e($p['image_url'] ?: 'assets/img/coffee.svg') ?>" alt="<?= e($p['name']) ?>">
        <strong><?= e($p['name']) ?></strong>
        <span class="pc-meta"><?= e($p['category']) ?> · Stok <?= e((string)$p['stock']) ?></span>
        <div class="pc-row">
          <span class="pc-price"><?= rupiah($p['price']) ?></span>
          <button class="add-btn" data-product='<?= $payload ?>' title="Tambah ke keranjang" aria-label="Tambah <?= e($p['name']) ?>"><?= svg_icon('plus', 15) ?></button>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (!$products): ?>
      <div class="empty no-result"><?= svg_icon('package', 30) ?><strong>Belum ada produk</strong>Tambahkan produk terlebih dahulu di menu Produk.</div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ===== Keranjang ===== -->
  <aside class="card cart" id="cartPanel">
    <div class="cart-head">
      <h3><?= svg_icon('cart', 18) ?> Keranjang <span class="cart-count" id="cartCount">0</span></h3>
      <button class="cart-clear" onclick="clearCart()"><?= svg_icon('trash', 13) ?> Hapus Semua</button>
    </div>

    <div class="cart-items-head">
      <h4>Item yang dibeli</h4>
    </div>
    <div id="cartItems" class="cart-items">
      <div class="empty"><?= svg_icon('cart', 30) ?><strong>Keranjang masih kosong</strong>Klik produk untuk menambahkannya.</div>
    </div>

    <div class="cart-summary">
      <div class="totals">
        <div class="total-row"><span>Subtotal</span><strong id="subTotal">Rp 0</strong></div>
        <div class="total-row grand"><span>Total</span><strong id="grandTotal">Rp 0</strong></div>
      </div>

      <div class="form-group" style="margin-top:14px">
        <label>Metode Pembayaran</label>
        <div class="pay-methods" id="payMethods">
          <label class="pay-option">
            <input type="radio" name="payment" value="Tunai" checked>
            <span class="pay-box"><?= svg_icon('cash', 18) ?> Tunai</span>
          </label>
          <label class="pay-option">
            <input type="radio" name="payment" value="QRIS">
            <span class="pay-box"><?= svg_icon('qrcode', 18) ?> QRIS</span>
          </label>
          <label class="pay-option">
            <input type="radio" name="payment" value="Debit">
            <span class="pay-box"><?= svg_icon('card', 18) ?> Debit</span>
          </label>
        </div>
      </div>

      <!-- Panel QRIS (muncul saat memilih QRIS) -->
      <div class="qris-panel" id="qrisPanel" hidden>
        <div class="qris-box">
          <svg viewBox="0 0 210 210" fill="#0f172a"><?= $qrisModules ?></svg>
        </div>
        <p>Scan dengan aplikasi pembayaran<br>Nominal: <strong id="qrisAmount">Rp 0</strong></p>
      </div>

      <!-- Input uang tunai -->
      <div class="form-group" id="cashSection" style="margin-top:12px">
        <label for="paid">Uang Dibayar</label>
        <input id="paid" class="input" type="number" min="0" placeholder="Masukkan nominal" inputmode="numeric">
        <div class="quick-cash">
          <button type="button" onclick="quickCash('exact')">Uang Pas</button>
          <button type="button" onclick="quickCash(10000)">10.000</button>
          <button type="button" onclick="quickCash(20000)">20.000</button>
          <button type="button" onclick="quickCash(50000)">50.000</button>
          <button type="button" onclick="quickCash(100000)">100.000</button>
        </div>
        <div class="total-row" style="margin-top:10px"><span>Kembalian</span><strong id="change" style="color:var(--green-700)">Rp 0</strong></div>
        <small id="changeHint" style="display:block;margin-top:6px;color:var(--muted);line-height:1.4;">Kembalian akan muncul otomatis saat uang lebih dari total.</small>
      </div>

      <div class="form-group" style="margin-top:14px">
        <small style="display:block;color:var(--muted);line-height:1.5;">Tekan tombol di bawah untuk konfirmasi pembayaran, lalu sistem akan menyimpan transaksi dan menampilkan nota.</small>
      </div>
      <div class="checkout-actions" style="display:flex;gap:10px;flex-direction:column;">
        <button class="checkout-btn" id="checkoutBtn" onclick="confirmCheckout()">
          <?= svg_icon('checkcircle', 19) ?> <span id="checkoutText">Checkout Tunai</span>
        </button>
      </div>
    </div>
  </aside>
</div>

<!-- Bar keranjang melayang untuk mobile -->
<button class="cart-fab" onclick="document.getElementById('cartPanel').scrollIntoView({behavior:'smooth'})">
  <span id="fabCount">0 produk</span>
  <span class="fab-total" id="fabTotal">Rp 0</span>
</button>

<script>
'use strict';
let cart = [];
let activeCat = 'all';

const el = id => document.getElementById(id);
const cartTotal = () => cart.reduce((s, i) => s + i.qty * i.price, 0);

/* ---------- Render keranjang ---------- */
function renderCart() {
  const box = el('cartItems');
  const total = cartTotal();
  const count = cart.reduce((s, i) => s + i.qty, 0);

  el('cartCount').textContent = count;
  el('subTotal').textContent = money(total);
  el('grandTotal').textContent = money(total);
  el('fabCount').textContent = count + ' produk';
  el('fabTotal').textContent = money(total);
  el('qrisAmount').textContent = money(total);

  if (!cart.length) {
    box.innerHTML = '<div class="empty"><?= svg_icon('cart', 30) ?><strong>Keranjang masih kosong</strong>Klik produk untuk menambahkannya.</div>';
    calcChange();
    return;
  }

  box.innerHTML = cart.map((i, idx) => `
    <div class="cart-item">
      <img class="product-thumb" src="${i.image}" alt="">
      <div class="cart-item-info">
        <strong>${i.name}</strong>
        <small>${money(i.price)}</small>
        <div class="qty">
          <button onclick="changeQty(${idx},-1)" aria-label="Kurangi">−</button>
          <span>${i.qty}</span>
          <button onclick="changeQty(${idx},1)" aria-label="Tambah">+</button>
        </div>
      </div>
      <div class="cart-item-sub">${money(i.qty * i.price)}<small>max ${i.stock}</small></div>
    </div>`).join('');

  calcChange();
}

/* ---------- Operasi keranjang ---------- */
function addToCart(item) {
  const found = cart.find(x => x.id === item.id);
  if (found) {
    if (found.qty < item.stock) found.qty++;
    else return showToast('Stok tidak mencukupi');
  } else {
    cart.push({ ...item, qty: 1 });
  }
  renderCart();
}

function changeQty(idx, delta) {
  const item = cart[idx];
  item.qty += delta;
  if (item.qty <= 0) cart.splice(idx, 1);
  else if (item.qty > item.stock) { item.qty = item.stock; showToast('Stok maksimal ' + item.stock); }
  renderCart();
}

function clearCart() {
  if (!cart.length) return;
  cart = [];
  renderCart();
  showToast('Keranjang dikosongkan');
}

/* ---------- Pembayaran ---------- */
function calcChange() {
  const total = cartTotal();
  const paid = Number(el('paid').value || 0);
  const change = Math.max(0, paid - total);
  el('change').textContent = money(change);

  const hint = el('changeHint');
  if (hint) {
    hint.textContent = paid > total
      ? 'Uang lebih: ' + money(change) + ' (siap untuk kembalian)'
      : paid === 0
        ? 'Kembalian akan muncul otomatis saat uang lebih dari total.'
        : 'Uang masih kurang dari total transaksi.';
  }
}

function quickCash(value) {
  el('paid').value = value === 'exact' ? cartTotal() : value;
  calcChange();
}

function setPayment(method) {
  const isCash = method === 'Tunai';
  el('cashSection').hidden = !isCash;
  el('qrisPanel').hidden = method !== 'QRIS';

  const checkoutText = el('checkoutText');
  if (checkoutText) {
    checkoutText.textContent = method === 'Tunai'
      ? 'Checkout Tunai'
      : method === 'QRIS'
        ? 'Checkout QRIS'
        : 'Checkout Debit';
  }

  calcChange();
}
document.querySelectorAll('input[name="payment"]').forEach(r =>
  r.addEventListener('change', () => setPayment(r.value))
);

/* ---------- Filter produk ---------- */
function filterProducts() {
  const q = el('productSearch').value.toLowerCase();
  let visible = 0;
  document.querySelectorAll('.product-card').forEach(card => {
    const show = card.dataset.name.includes(q) && (activeCat === 'all' || card.dataset.category === activeCat);
    card.style.display = show ? '' : 'none';
    if (show) visible++;
  });
  let empty = document.getElementById('noResult');
  if (!visible) {
    if (!empty) {
      empty = document.createElement('div');
      empty.id = 'noResult';
      empty.className = 'empty no-result';
      empty.innerHTML = '<strong>Produk tidak ditemukan</strong>Coba kata kunci atau kategori lain.';
      el('productCards').appendChild(empty);
    }
    empty.style.display = '';
  } else if (empty) {
    empty.style.display = 'none';
  }
}
el('productSearch').addEventListener('input', filterProducts);
el('paid').addEventListener('input', calcChange);

document.querySelectorAll('#categoryTabs .tab').forEach(tab =>
  tab.addEventListener('click', () => {
    document.querySelectorAll('#categoryTabs .tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    activeCat = tab.dataset.category;
    filterProducts();
  })
);

/* Klik kartu produk = tambah ke keranjang */
document.querySelectorAll('.add-btn').forEach(btn =>
  btn.addEventListener('click', e => {
    e.stopPropagation();
    addToCart(JSON.parse(btn.dataset.product));
  })
);
document.querySelectorAll('.product-card').forEach(card =>
  card.addEventListener('click', () => {
    const btn = card.querySelector('.add-btn');
    if (btn) addToCart(JSON.parse(btn.dataset.product));
  })
);

/* ---------- Checkout ---------- */
function confirmCheckout() {
  if (!cart.length) return showToast('Keranjang masih kosong');

  const payment = document.querySelector('input[name="payment"]:checked').value;
  const total = cartTotal();
  const paid = Number(el('paid').value || 0);

  if (payment === 'Tunai' && paid < total) {
    showToast('Nominal pembayaran kurang');
    return;
  }

  if (!confirm('Konfirmasi checkout dan cetak nota untuk transaksi ini?')) return;
  checkout();
}

async function checkout() {
  if (!cart.length) return showToast('Keranjang masih kosong');

  const total = cartTotal();
  const paid = Number(el('paid').value || 0);
  const payment = document.querySelector('input[name="payment"]:checked').value;

  if (payment === 'Tunai' && paid < total) return showToast('Nominal pembayaran kurang');

  const fd = new FormData();
  fd.append('cart', JSON.stringify(cart));
  fd.append('paid', paid);
  fd.append('payment', payment);

  const btn = document.querySelector('.checkout-btn');
  btn.disabled = true;
  btn.style.opacity = '.7';
  try {
    const r = await fetch('actions/checkout.php', { method: 'POST', body: fd });
    const d = await r.json();
    if (d.success) {
      showToast('Transaksi berhasil: ' + d.invoice);
      setTimeout(() => location.href = 'transaksi.php?invoice=' + encodeURIComponent(d.invoice), 700);
    } else {
      showToast(d.message || 'Transaksi gagal');
      btn.disabled = false;
      btn.style.opacity = '';
    }
  } catch (err) {
    showToast('Terjadi kesalahan koneksi');
    btn.disabled = false;
    btn.style.opacity = '';
  }
}

renderCart();
</script>
<?php require_once 'includes/footer.php'; ?>
