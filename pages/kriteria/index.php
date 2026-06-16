<?php
/**
 * pages/kriteria/index.php – Manajemen data kriteria SAW.
 */

define('BASE_URL', rtrim(str_replace(['/pages/kriteria'], [''], dirname($_SERVER['SCRIPT_NAME'])), '/'));
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$db       = getDB();
$kriteria = $db->query('SELECT * FROM kriteria ORDER BY kode')->fetchAll();

// Hitung total bobot
$totalBobot = array_sum(array_column($kriteria, 'bobot'));

$pageTitle = 'Data Kriteria';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
  <div>
    <h2 style="font-size:1.1rem; font-weight:700;">Data Kriteria</h2>
    <p style="color:var(--clr-text-muted); font-size:.84rem;">Kelola kriteria dan bobot penilaian metode SAW.</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= BASE_URL ?>/pages/kriteria/create.php" class="btn btn-accent">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Kriteria
    </a>
  </div>
</div>

<!-- Peringatan jika total bobot tidak 100% -->
<?php if (round($totalBobot, 2) != 100.00): ?>
<div class="alert alert-warning" style="margin-bottom:16px;">
  ⚠️ Total bobot saat ini <strong><?= $totalBobot ?>%</strong>. Pastikan total bobot = <strong>100%</strong> agar perhitungan SAW akurat.
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <div class="card-title">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      Daftar Kriteria SAW
    </div>
    <div style="font-size:.84rem; color:<?= round($totalBobot)==100 ? 'var(--clr-accent)' : 'var(--clr-danger)' ?>; font-weight:700;">
      Total Bobot: <?= $totalBobot ?>%
    </div>
  </div>
  <div class="card-body p0">
    <div class="table-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>Kode</th>
            <th>Nama Kriteria</th>
            <th style="text-align:center;">Bobot (%)</th>
            <th style="text-align:center;">Visual Bobot</th>
            <th style="text-align:center;">Atribut</th>
            <th>Keterangan</th>
            <th style="text-align:center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($kriteria)): ?>
          <tr><td colspan="7">
            <div class="empty-state"><p>Belum ada data kriteria.</p></div>
          </td></tr>
          <?php else: foreach ($kriteria as $k): ?>
          <tr>
            <td><strong><?= sanitize($k['kode']) ?></strong></td>
            <td><?= sanitize($k['nama']) ?></td>
            <td style="text-align:center; font-weight:700; color:var(--clr-primary);"><?= $k['bobot'] ?>%</td>
            <td style="min-width:120px;">
              <div class="progress-bar-wrap">
                <div class="progress-bar-fill" style="width:<?= min($k['bobot'], 100) ?>%;"></div>
              </div>
            </td>
            <td style="text-align:center;">
              <span class="badge badge-<?= $k['atribut'] ?>"><?= strtoupper($k['atribut']) ?></span>
            </td>
            <td style="font-size:.82rem; color:var(--clr-text-muted); max-width:220px;"><?= sanitize($k['keterangan']) ?></td>
            <td style="text-align:center;">
              <div style="display:flex; gap:6px; justify-content:center;">
                <a href="<?= BASE_URL ?>/pages/kriteria/edit.php?id=<?= $k['id'] ?>"
                   class="btn btn-warning btn-sm">Edit</a>
                <a href="<?= BASE_URL ?>/pages/kriteria/delete.php?id=<?= $k['id'] ?>"
                   class="btn btn-danger btn-sm"
                   data-confirm="Hapus kriteria <?= sanitize($k['nama']) ?>? Semua nilai terkait juga akan terhapus.">
                  Hapus
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Penjelasan Atribut -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:20px;">
  <div class="card">
    <div class="card-body">
      <div style="display:flex; gap:10px; align-items:flex-start;">
        <span class="badge badge-benefit" style="margin-top:2px;">BENEFIT</span>
        <div>
          <strong style="font-size:.875rem;">Atribut Benefit (Maksimum)</strong>
          <p style="font-size:.82rem; color:var(--clr-text-muted); margin-top:4px;">
            Semakin tinggi nilai, semakin baik. Normalisasi: <code>r = x / max(x)</code>
          </p>
        </div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-body">
      <div style="display:flex; gap:10px; align-items:flex-start;">
        <span class="badge badge-cost" style="margin-top:2px;">COST</span>
        <div>
          <strong style="font-size:.875rem;">Atribut Cost (Minimum)</strong>
          <p style="font-size:.82rem; color:var(--clr-text-muted); margin-top:4px;">
            Semakin rendah nilai, semakin baik. Normalisasi: <code>r = min(x) / x</code>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>