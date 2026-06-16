<?php
/**
 * pages/karyawan/create.php – Tambah data karyawan baru.
 */

define('BASE_URL', rtrim(str_replace(['/pages/karyawan'], [''], dirname($_SERVER['SCRIPT_NAME'])), '/'));
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$db     = getDB();
$errors = [];
$input  = ['kode' => '', 'nama' => '', 'departemen' => '', 'jabatan' => ''];

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
        // Cek duplikat kode
        $cek = $db->prepare('SELECT id FROM karyawan WHERE kode = ?');
        $cek->execute([$input['kode']]);
        if ($cek->fetch()) {
            $errors[] = "Kode karyawan <strong>{$input['kode']}</strong> sudah digunakan.";
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare('INSERT INTO karyawan (kode, nama, departemen, jabatan) VALUES (?,?,?,?)');
        $stmt->execute(array_values($input));
        setFlash('success', "Karyawan <strong>{$input['nama']}</strong> berhasil ditambahkan.");
        redirect('pages/karyawan/index.php');
    }
}

$pageTitle = 'Tambah Karyawan';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
  <div>
    <h2 style="font-size:1.1rem; font-weight:700;">Tambah Karyawan</h2>
    <p style="color:var(--clr-text-muted); font-size:.84rem;">Tambah data alternatif karyawan baru ke sistem.</p>
  </div>
  <a href="<?= BASE_URL ?>/pages/karyawan/index.php" class="btn btn-outline btn-sm">← Kembali</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-error" style="margin-bottom:16px;">
  <?= implode('<br>', $errors) ?>
  <button class="alert-close" onclick="this.parentElement.remove()">×</button>
</div>
<?php endif; ?>

<div class="card" style="max-width:620px;">
  <div class="card-header">
    <div class="card-title">Form Data Karyawan</div>
  </div>
  <div class="card-body">
    <form method="POST" action="">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Kode Karyawan <span style="color:red;">*</span></label>
          <input class="form-control" type="text" name="kode"
                 value="<?= sanitize($input['kode']) ?>"
                 placeholder="Contoh: EMP006" maxlength="10" required>
        </div>
        <div class="form-group">
          <label class="form-label">Nama Lengkap <span style="color:red;">*</span></label>
          <input class="form-control" type="text" name="nama"
                 value="<?= sanitize($input['nama']) ?>"
                 placeholder="Nama lengkap karyawan" maxlength="100" required>
        </div>
        <div class="form-group">
          <label class="form-label">Departemen <span style="color:red;">*</span></label>
          <input class="form-control" type="text" name="departemen"
                 value="<?= sanitize($input['departemen']) ?>"
                 placeholder="Contoh: Teknologi Informasi" maxlength="100" required>
        </div>
        <div class="form-group">
          <label class="form-label">Jabatan <span style="color:red;">*</span></label>
          <input class="form-control" type="text" name="jabatan"
                 value="<?= sanitize($input['jabatan']) ?>"
                 placeholder="Contoh: Staff IT" maxlength="100" required>
        </div>
      </div>
      <div class="form-actions" style="margin-top:20px;">
        <button type="submit" class="btn btn-accent">Simpan Karyawan</button>
        <a href="<?= BASE_URL ?>/pages/karyawan/index.php" class="btn btn-outline">Batal</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>