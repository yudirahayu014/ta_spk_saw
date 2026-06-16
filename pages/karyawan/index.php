<?php
/**
 * pages/karyawan/index.php – Daftar & manajemen data karyawan.
 */

define('BASE_URL', rtrim(str_replace(['/pages/karyawan'], [''], dirname($_SERVER['SCRIPT_NAME'])), '/'));
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$db       = getDB();
$search   = trim($_GET['q'] ?? '');
$karyawan = [];

if ($search !== '') {
    $stmt = $db->prepare('SELECT * FROM karyawan WHERE nama LIKE ? OR kode LIKE ? OR departemen LIKE ? ORDER BY kode');
    $like = '%' . $search . '%';
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $db->query('SELECT * FROM karyawan ORDER BY kode');
}
$karyawan = $stmt->fetchAll();

$pageTitle = 'Data Karyawan';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
  <div>
    <h2 style="font-size:1.1rem; font-weight:700;">Data Karyawan</h2>
    <p style="color:var(--clr-text-muted); font-size:.84rem;">Kelola data alternatif (karyawan) untuk penilaian SAW.</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= BASE_URL ?>/pages/karyawan/create.php" class="btn btn-accent">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Karyawan
    </a>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      Daftar Karyawan (<?= count($karyawan) ?> data)
    </div>
    <!-- Form pencarian -->
    <form method="GET" style="display:flex; gap:8px;">
      <input class="form-control" type="search" name="q" placeholder="Cari nama / kode / departemen…"
             value="<?= sanitize($search) ?>" style="width:240px;">
      <button class="btn btn-outline btn-sm" type="submit">Cari</button>
      <?php if ($search): ?>
        <a href="?" class="btn btn-outline btn-sm">Reset</a>
      <?php endif; ?>
    </form>
  </div>
  <div class="card-body p0">
    <div class="table-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th style="width:40px;">No</th>
            <th>Kode</th>
            <th>Nama Karyawan</th>
            <th>Departemen</th>
            <th>Jabatan</th>
            <th>Terdaftar</th>
            <th style="text-align:center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($karyawan)): ?>
          <tr><td colspan="7">
            <div class="empty-state">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <p><?= $search ? "Tidak ada hasil untuk \"$search\"." : 'Belum ada data karyawan.' ?></p>
            </div>
          </td></tr>
          <?php else: foreach ($karyawan as $i => $k): ?>
          <tr>
            <td style="color:var(--clr-text-muted); text-align:center;"><?= $i + 1 ?></td>
            <td><code style="background:var(--clr-bg); padding:2px 7px; border-radius:5px;"><?= sanitize($k['kode']) ?></code></td>
            <td style="font-weight:600;"><?= sanitize($k['nama']) ?></td>
            <td><?= sanitize($k['departemen']) ?></td>
            <td style="color:var(--clr-text-muted); font-size:.84rem;"><?= sanitize($k['jabatan']) ?></td>
            <td style="color:var(--clr-text-muted); font-size:.82rem;"><?= date('d M Y', strtotime($k['created_at'])) ?></td>
            <td style="text-align:center;">
              <div style="display:flex; gap:6px; justify-content:center;">
                <a href="<?= BASE_URL ?>/pages/karyawan/edit.php?id=<?= $k['id'] ?>"
                   class="btn btn-warning btn-sm">Edit</a>
                <a href="<?= BASE_URL ?>/pages/karyawan/delete.php?id=<?= $k['id'] ?>"
                   class="btn btn-danger btn-sm"
                   data-confirm="Hapus karyawan <?= sanitize($k['nama']) ?>? Semua nilai absensinya juga akan terhapus.">
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>