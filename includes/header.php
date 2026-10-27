<?php
/**
 * Header global semua halaman (kecuali login).
 * Menyusun: <head>, sidebar, topbar, dan pembuka <main>.
 */
require_once __DIR__ . '/../config/auth.php';
require_login();
require_once __DIR__ . '/icons.php';

$user = current_user();
$page = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title ?? 'Beranda') ?> · <?= APP_NAME ?></title>
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%230a7d5f'/%3E%3Cpath d='M9 11h14M16 11v12' stroke='%23fff' stroke-width='3.4' stroke-linecap='round'/%3E%3C/svg%3E">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_url('assets/style.css') ?>">
</head>
<body>
<div class="app-shell">

  <!-- Overlay mobile untuk sidebar -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <div class="brand-mark"><?= svg_icon('store', 22) ?></div>
      <div class="brand-text">
        <strong><?= APP_NAME ?></strong>
        <small>Point of Sale</small>
      </div>
      <button class="sidebar-close" id="sidebarClose" aria-label="Tutup menu"><?= svg_icon('x', 17) ?></button>
    </div>

    <nav class="sidebar-nav">
      <p class="nav-label">Menu Utama</p>
      <?php if (is_admin()): ?>
      <a href="dashboard.php" class="<?= $page === 'dashboard.php' ? 'active' : '' ?>"><?= svg_icon('dashboard') ?><span>Dashboard</span></a>
      <?php endif; ?>
      <a href="kasir.php" class="<?= $page === 'kasir.php' ? 'active' : '' ?>"><?= svg_icon('cart') ?><span>Kasir</span></a>

      <?php if (is_admin()): ?>
      <p class="nav-label">Manajemen</p>
      <a href="produk.php" class="<?= $page === 'produk.php' ? 'active' : '' ?>"><?= svg_icon('package') ?><span>Produk</span></a>
      <a href="kategori.php" class="<?= $page === 'kategori.php' ? 'active' : '' ?>"><?= svg_icon('tag') ?><span>Kategori</span></a>
      <?php endif; ?>

      <p class="nav-label">Laporan</p>
      <a href="transaksi.php" class="<?= $page === 'transaksi.php' ? 'active' : '' ?>"><?= svg_icon(is_admin() ? 'receipt' : 'history') ?><span><?= is_admin() ? 'Transaksi' : 'History' ?></span></a>
      <?php if (is_admin()): ?>
      <a href="laporan.php" class="<?= $page === 'laporan.php' ? 'active' : '' ?>"><?= svg_icon('chart') ?><span>Laporan</span></a>
      <?php endif; ?>

      <?php if (is_admin()): ?>
      <p class="nav-label">Sistem</p>
      <a href="pengaturan.php" class="<?= $page === 'pengaturan.php' ? 'active' : '' ?>"><?= svg_icon('settings') ?><span>Pengaturan</span></a>
      <?php endif; ?>
    </nav>

    <div class="sidebar-foot">
      <div class="sidebar-user">
        <div class="avatar"><?= e(strtoupper(mb_substr($user['name'], 0, 1))) ?></div>
        <div class="sidebar-user-info">
          <strong><?= e($user['name']) ?></strong>
          <small><?= is_admin() ? 'Super Admin' : 'Kasir' ?></small>
        </div>
        <a class="logout-btn" href="logout.php" title="Keluar dari sistem"><?= svg_icon('logout', 16) ?></a>
      </div>
    </div>
  </aside>

  <!-- Area utama -->
  <div class="main-wrap">
    <header class="topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="sidebarToggle" aria-label="Buka menu"><?= svg_icon('menu', 20) ?></button>
        <div class="topbar-title">
          <h1><?= e($title ?? APP_NAME) ?></h1>
          <p><?= date('l, d F Y') ?></p>
        </div>
      </div>
      <div class="topbar-actions">
        <span class="topbar-date"><?= svg_icon('calendar', 14) ?><?= date('d M Y') ?> · <?= date('H:i') ?> WIB</span>
        <button type="button" class="theme-toggle" id="themeToggle" aria-label="Ganti mode tampilan">Dark</button>
        <span class="user-chip"><span class="avatar"><?= e(strtoupper(mb_substr($user['name'], 0, 1))) ?></span><span class="chip-name"><?= e($user['name']) ?></span></span>
      </div>
    </header>

    <main class="page-content">
