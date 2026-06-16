<?php
/**
 * pages/karyawan/edit.php – Edit data karyawan.
 */

define('BASE_URL', rtrim(str_replace(['/pages/karyawan'], [''], dirname($_SERVER['SCRIPT_NAME'])), '/'));
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM karyawan WHERE id = ?');
$stmt->execute([$id]);
$karyawan = $stmt->fetch();

if (!$karyawan) {
    setFlash('error', 'Data karyawan tidak ditemukan.');
    redirect('pages/karyawan/index.php');
}

$errors = [];
$input  = $karyawan;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = [
        'kode'       => strtoupper(trim($_POST['kode']       ?? '')),
        'nama'       => trim($_POST['nama']       ?? ''),
        'departemen' => trim($_POST['departemen'] ?? ''),
        'jabatan'    => trim($_POST['jabatan']    ?? ''),
    ];

    if (empty($input['kode']))       $errors[] = 'Kode karyawan wajib diisi.';
    if (empty($input['nama']))       $errors[] = 'Nama karyawan wajib diisi.';
    if (empty($input['departemen'])) $errors[] = 'Departemen wajib diisi.';
    if (empty($input['jabatan']))    $errors[] = 'Jabatan wajib diisi.';

    if (empty($errors)) {
        $cek = $db->prepare('SELECT id FROM karyawan WHERE kode = ? AND id != ?');
        $cek->execute([$input['kode'], $id]);
        if ($cek->fetch()) {
            $errors[] = "Kode <strong>{$input['kode']}</strong> sudah digunakan karyawan lain.";
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare('UPDATE karyawan SET kode=?, nama=?, departemen=?, jabatan=? WHERE id=?');
        $stmt->execute([$input['kode'], $input['nama'], $input['departemen'], $input['jabatan'], $id]);
        setFlash('success', "Data karyawan <strong>{$input['nama']}</strong> berhasil diperbarui.");
        redirect('pages/karyawan/index.php');
    }
}

$pageTitle = 'Edit Karyawan';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
  <div>
    <h2 style="font-size:1.1rem; font-weight:700;">Edit Karyawan</h2>
    <p style="color:var(--clr-text-muted); font-size:.84rem;">Perbarui data karyawan: <strong><?= sanitize($karyawan['nama']) ?></strong></p>
  </div>
  <a href="<?= BASE_URL ?>/pages/karyawan/index.php" class="btn btn-outline btn-sm">← Kembali</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-error" style="margin-bottom:16px;"><?= implode('<br>', $errors) ?></div>
<?php endif; ?>

<div class="card" style="max-width:620px;">
  <div class="card-header">
    <div class="card-title">Form Edit Karyawan</div>
  </div>
  <div class="card-body">
    <form method="POST" action="">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Kode Karyawan <span style="color:red;">*</span></label>
          <input class="form-control" type="text" name="kode"
                 value="<?= sanitize($input['kode']) ?>" maxlength="10" required>
        </div>
        <div class="form-group">
          <label class="form-label">Nama Lengkap <span style="color:red;">*</span></label>
          <input class="form-control" type="text" name="nama"
                 value="<?= sanitize($input['nama']) ?>" maxlength="100" required>
        </div>
        <div class="form-group">
          <label class="form-label">Departemen <span style="color:red;">*</span></label>
          <input class="form-control" type="text" name="departemen"
                 value="<?= sanitize($input['departemen']) ?>" maxlength="100" required>
        </div>
        <div class="form-group">
          <label class="form-label">Jabatan <span style="color:red;">*</span></label>
          <input class="form-control" type="text" name="jabatan"
                 value="<?= sanitize($input['jabatan']) ?>" maxlength="100" required>
        </div>
      </div>
      <div class="form-actions" style="margin-top:20px;">
        <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
        <a href="<?= BASE_URL ?>/pages/karyawan/index.php" class="btn btn-outline">Batal</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>