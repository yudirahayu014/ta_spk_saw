<?php
/**
 * index.php – Dashboard utama aplikasi SPK.
 */

define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/session.php';

requireLogin();

$db = getDB();

// Ambil statistik ringkasan
$totalKaryawan = $db->query('SELECT COUNT(*) FROM karyawan')->fetchColumn();
$totalKriteria = $db->query('SELECT COUNT(*) FROM kriteria')->fetchColumn();
$totalNilai    = $db->query('SELECT COUNT(DISTINCT karyawan_id) FROM nilai_matriks')->fetchColumn();
$periodeAktif  = $db->query('SELECT periode FROM nilai_matriks ORDER BY periode DESC LIMIT 1')->fetchColumn() ?: '-';

// Ambil data karyawan terbaru
$karyawan = $db->query('SELECT * FROM karyawan ORDER BY created_at DESC LIMIT 5')->fetchAll();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon green">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div>
      <div class="stat-label">Total Karyawan</div>
      <div class="stat-value"><?= $totalKaryawan ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
    </div>
    <div>
      <div class="stat-label">Jumlah Kriteria</div>
      <div class="stat-value"><?= $totalKriteria ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    </div>
    <div>
      <div class="stat-label">Data Dinilai</div>
      <div class="stat-value"><?= $totalNilai ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </div>
    <div>
      <div class="stat-label">Periode Aktif</div>
      <div class="stat-value" style="font-size:1.1rem;"><?= sanitize($periodeAktif) ?></div>
    </div>
  </div>
</div>

<!-- Info SAW + Daftar Karyawan -->
<div style="display:grid; grid-template-columns:1fr 1.4fr; gap:20px; flex-wrap:wrap;">

  <!-- Tentang Metode SAW -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Metode SAW
      </div>
    </div>
    <div class="card-body">
      <!-- <p style="font-size:.875rem; color:var(--clr-text-muted); margin-bottom:16px;">
        <strong>Simple Additive Weighting (SAW)</strong> adalah metode pengambilan keputusan multi-kriteria dengan
        mencari penjumlahan terbobot dari rating kinerja pada setiap alternatif di semua atribut.
      </p> -->
      <div class="saw-steps">
        <!-- <div class="saw-step active">
          <div class="saw-step-num">Langkah 1</div>
          <div class="saw-step-label">Matriks Keputusan</div>
        </div> -->
        <!-- <div class="saw-step">
          <div class="saw-step-num">Langkah 2</div>
          <div class="saw-step-label">Normalisasi</div>
        </div>
        <div class="saw-step">
          <div class="saw-step-num">Langkah 3</div>
          <div class="saw-step-label">Perangkingan</div>
        </div> -->
      </div>
      <table class="tbl" style="font-size:.8rem;">
        <thead><tr><th>Kriteria</th><th>Bobot</th><th>Atribut</th></tr></thead>
        <tbody>
          <?php
          $kriteriaList = $db->query('SELECT * FROM kriteria')->fetchAll();
          foreach ($kriteriaList as $k): ?>
          <tr>
            <td><?= sanitize($k['kode']) ?> – <?= sanitize($k['nama']) ?></td>
            <td><?= $k['bobot'] ?>%</td>
            <td><span class="badge badge-<?= $k['atribut'] ?>"><?= strtoupper($k['atribut']) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div style="margin-top:16px;">
        <a href="<?= BASE_URL ?>/pages/penilaian/hasil.php" class="btn btn-accent btn-sm">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          Lihat Hasil SAW
        </a>
      </div>
    </div>
  </div>

  <!-- Daftar Karyawan -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        Daftar Karyawan
      </div>
      <a href="<?= BASE_URL ?>/pages/karyawan/index.php" class="btn btn-outline btn-sm">Lihat Semua</a>
    </div>
    <div class="card-body p0">
      <div class="table-wrap">
        <table class="tbl">
          <thead>
            <tr>
              <th>Kode</th><th>Nama</th><th>Departemen</th><th>Jabatan</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($karyawan)): ?>
            <tr><td colspan="4">
              <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <p>Belum ada data karyawan.</p>
              </div>
            </td></tr>
            <?php else: foreach ($karyawan as $k): ?>
            <tr>
              <td><code><?= sanitize($k['kode']) ?></code></td>
              <td style="font-weight:600;"><?= sanitize($k['nama']) ?></td>
              <td><?= sanitize($k['departemen']) ?></td>
              <td style="color:var(--clr-text-muted); font-size:.82rem;"><?= sanitize($k['jabatan']) ?></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>