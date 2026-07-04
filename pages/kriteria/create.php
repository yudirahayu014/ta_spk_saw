<?php
/**
 * pages/kriteria/create.php – Tambah kriteria baru.
 */

define('BASE_URL', rtrim(str_replace(['/pages/kriteria'], [''], dirname($_SERVER['SCRIPT_NAME'])), '/'));
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$db     = getDB();
$errors = [];
$input  = ['kode' => '', 'nama' => '', 'bobot' => '', 'atribut' => 'benefit', 'keterangan' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = [
        'kode'       => strtoupper(trim($_POST['kode']       ?? '')),
        'nama'       => trim($_POST['nama']       ?? ''),
        'bobot'      => trim($_POST['bobot']      ?? ''),
        'atribut'    => trim($_POST['atribut']    ?? 'benefit'),
        'keterangan' => trim($_POST['keterangan'] ?? ''),
    ];

    if (empty($input['kode']))  $errors[] = 'Kode kriteria wajib diisi.';
    if (empty($input['nama']))  $errors[] = 'Nama kriteria wajib diisi.';
    if (!is_numeric($input['bobot']) || $input['bobot'] <= 0 || $input['bobot'] > 100)
        $errors[] = 'Bobot harus angka antara 1–100.';
    if (!in_array($input['atribut'], ['benefit', 'cost']))
        $errors[] = 'Atribut tidak valid.';

    if (empty($errors)) {
        $cek = $db->prepare('SELECT id FROM kriteria WHERE kode = ?');
        $cek->execute([$input['kode']]);
        if ($cek->fetch()) $errors[] = "Kode {$input['kode']} sudah digunakan.";
    }

    if (empty($errors)) {
        $stmt = $db->prepare('INSERT INTO kriteria (kode, nama, bobot, atribut, keterangan) VALUES (?,?,?,?,?)');
        $stmt->execute([$input['kode'], $input['nama'], $input['bobot'], $input['atribut'], $input['keterangan']]);
        setFlash('success', "Kriteria {$input['nama']} berhasil ditambahkan.");
        redirect('pages/kriteria/index.php');
    }
}

$pageTitle = 'Tambah Kriteria';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
  <div>
    <h2 style="font-size:1.1rem; font-weight:700;">Tambah Kriteria</h2>
    <p style="color:var(--clr-text-muted); font-size:.84rem;">Tambah kriteria penilaian baru untuk metode SAW.</p>
  </div>
  <a href="<?= BASE_URL ?>/pages/kriteria/index.php" class="btn btn-outline btn-sm">← Kembali</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-error" style="margin-bottom:16px;"><?= implode('<br>', $errors) ?></div>
<?php endif; ?>

<div class="card" style="max-width:620px;">
  <div class="card-header"><div class="card-title">Form Kriteria</div></div>
  <div class="card-body">
    <form method="POST">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Kode Kriteria <span style="color:red;">*</span></label>
          <input class="form-control" type="text" name="kode"
                 value="<?= sanitize($input['kode']) ?>" placeholder="Contoh: C6" maxlength="10" required>
        </div>
        <div class="form-group">
          <label class="form-label">Bobot (%) <span style="color:red;">*</span></label>
          <input class="form-control" type="number" name="bobot"
                 value="<?= sanitize($input['bobot']) ?>" min="1" max="100" step="0.01" required>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Nama Kriteria <span style="color:red;">*</span></label>
          <input class="form-control" type="text" name="nama"
                 value="<?= sanitize($input['nama']) ?>" placeholder="Nama kriteria penilaian" maxlength="150" required>
        </div>
        <div class="form-group">
          <label class="form-label">Atribut <span style="color:red;">*</span></label>
          <select class="form-control" name="atribut">
            <option value="benefit" <?= $input['atribut']==='benefit' ? 'selected' : '' ?>>Benefit (Semakin tinggi semakin baik)</option>
            <option value="cost"    <?= $input['atribut']==='cost'    ? 'selected' : '' ?>>Cost (Semakin rendah semakin baik)</option>
          </select>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Keterangan</label>
          <textarea class="form-control" name="keterangan" rows="2"
                    placeholder="Deskripsi singkat kriteria ini…"><?= sanitize($input['keterangan']) ?></textarea>
        </div>
      </div>
      <div class="form-actions" style="margin-top:20px;">
        <button type="submit" class="btn btn-accent">Simpan Kriteria</button>
        <a href="<?= BASE_URL ?>/pages/kriteria/index.php" class="btn btn-outline">Batal</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>