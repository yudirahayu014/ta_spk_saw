<?php
/**
 * includes/header.php
 * Template header & sidebar yang digunakan oleh semua halaman.
 * Memerlukan variabel $pageTitle yang di-set oleh halaman pemanggil.
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/session.php';

requireLogin();

$pageTitle = $pageTitle ?? APP_NAME;
$flash     = getFlash();

// Tentukan menu aktif berdasarkan URI
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath   = str_replace(rtrim(BASE_URL, '/'), '', $currentUri);

function isMenuActive(string $path, string $current): string
{
    return (str_starts_with($current, $path)) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= sanitize($pageTitle) ?> – <?= APP_NAME ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- ===================== SIDEBAR ===================== -->
<aside class="sidebar" id="sidebar">

  <!-- Logo Area -->
  <div class="sidebar-brand">
    <div class="brand-logo">
      <!-- Ganti src dengan path logo Anda -->
      <img src="<?= BASE_URL ?>/assets/img/logo.png"
           alt="Logo Aplikasi"
           onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
      <div class="brand-logo-placeholder" style="display:none;">
        <span>SPK</span>
      </div>
    </div>
    <div class="brand-text">
      <span class="brand-title">SPK Disiplin</span>
      <span class="brand-sub">Metode SAW</span>
    </div>
  </div>

  <!-- Navigasi -->
  <nav class="sidebar-nav">
    <div class="nav-section-label">MENU UTAMA</div>

    <a href="<?= BASE_URL ?>/index.php"
       class="nav-item <?= isMenuActive('/index.php', $basePath) === 'active' && $basePath === '/index.php' ? 'active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Dashboard
    </a>

    <div class="nav-section-label">DATA MASTER</div>

    <a href="<?= BASE_URL ?>/pages/karyawan/index.php"
       class="nav-item <?= isMenuActive('/pages/karyawan', $basePath) ?>">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Data Karyawan
    </a>

    <a href="<?= BASE_URL ?>/pages/kriteria/index.php"
       class="nav-item <?= isMenuActive('/pages/kriteria', $basePath) ?>">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      Data Kriteria
    </a>

    <div class="nav-section-label">PENILAIAN</div>

    <a href="<?= BASE_URL ?>/pages/penilaian/input.php"
       class="nav-item <?= isMenuActive('/pages/penilaian/input', $basePath) ?>">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Input Nilai Absensi
    </a>

    <a href="<?= BASE_URL ?>/pages/penilaian/hasil.php"
       class="nav-item <?= isMenuActive('/pages/penilaian/hasil', $basePath) ?>">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      Hasil Penilaian SAW
    </a>
  </nav>

  <!-- Info User -->
  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar"><?= strtoupper(substr(currentUser()['nama'] ?? 'A', 0, 1)) ?></div>
      <div class="user-detail">
        <span class="user-name"><?= sanitize(currentUser()['nama'] ?? 'Admin') ?></span>
        <span class="user-role">Administrator</span>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/logout.php" class="btn-logout" title="Keluar">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </a>
  </div>
</aside>

<!-- ===================== MAIN CONTENT ===================== -->
<div class="main-wrapper">

  <!-- Top Bar -->
  <header class="topbar">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <h1 class="topbar-title"><?= sanitize($pageTitle) ?></h1>
    <div class="topbar-right">
      <span class="topbar-badge">SAW</span>
    </div>
  </header>

  <!-- Flash Message -->
  <?php if ($flash): ?>
  <div class="alert alert-<?= sanitize($flash['type']) ?>" id="flash-message">
    <?= sanitize($flash['message']) ?>
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
  </div>
  <?php endif; ?>

  <!-- Page Content -->
  <main class="content">