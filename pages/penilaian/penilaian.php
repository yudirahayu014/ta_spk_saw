<?php
/**
 * pages/penilaian/input.php – Input dan edit nilai absensi karyawan.
 */

define('BASE_URL', rtrim(str_replace(['/pages/penilaian'], [''], dirname($_SERVER['SCRIPT_NAME'])), '/'));
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$db = getDB();

// Ambil semua karyawan dan kriteria
$karyawanList = $db->query('SELECT * FROM karyawan ORDER BY kode')->fetchAll();
$kriteriaList = $db->query('SELECT * FROM kriteria ORDER BY kode')->fetchAll();

// Daftar periode yang tersedia (+ periode baru)
$periodeRows = $db->query('SELECT DISTINCT periode FROM nilai_matriks ORDER BY periode DESC')->fetchAll(PDO::FETCH_COLUMN);
$periodeAktif = $_GET['periode'] ?? ($periodeRows[0] ?? date('Y-m'));

// Ambil semua nilai yang sudah ada untuk periode ini
$nilaiExisting = [];
$stmt = $db->prepare('SELECT karyawan_id, kriteria_id, nilai FROM nilai_matriks WHERE periode = ?');
$stmt->execute([$periodeAktif]);
foreach ($stmt->fetchAll() as $row) {
    $nilaiExisting[$row['karyawan_id']][$row['kriteria_id']] = $row['nilai'];
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $periode = trim($_POST['periode'] ?? '');
    $nilaiPost = $_POST['nilai'] ?? [];

    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode)) {
        $errors[] = 'Format periode tidak valid. Gunakan format YYYY-MM (contoh: 2025-01).';
    }

    if (empty($errors)) {
        $db->beginTransaction();
        try {
            foreach ($karyawanList as $k) {
                foreach ($kriteriaList as $kr) {
                    $nilai = $nilaiPost[$k['id']][$kr['id']] ?? null;
                    if ($nilai === null || $nilai === '') continue;
                    if (!is_numeric($nilai)) {
                        $errors[] = "Nilai untuk {$k['nama']} – {$kr['nama']} harus berupa angka.";
                        continue;
                    }

                    // INSERT or UPDATE (UPSERT)
                    $upsert = $db->prepare('
                        INSERT INTO nilai_matriks (karyawan_id, kriteria_id, nilai, periode)
                        VALUES (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)
                    ');
                    $upsert->execute([$k['id'], $kr['id'], (float)$nilai, $periode]);
                }
            }

            if (empty($errors)) {
                $db->commit();
                setFlash('success', "Nilai periode <strong>{$periode}</strong> berhasil disimpan.");
                redirect("pages/penilaian/input.php?periode={$periode}");
            } else {
                $db->rollBack();
            }
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Input Nilai Absensi';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
  <div>
    <h2 style="font-size:1.1rem; font-weight:700;">Input Nilai Absensi</h2>
    <p style="color:var(--clr-text-muted); font-size:.84rem;">Masukkan nilai setiap karyawan untuk tiap kriteria penilaian.</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= BASE_URL ?>/pages/penilaian/hasil.php?periode=<?= urlencode($periodeAktif) ?>" class="btn btn-accent btn-sm">
      Lihat Hasil SAW →
    </a>
  </div>
</div>

<!-- Filter Periode -->
<div class="card" style="margin-bottom:20px;">
  <div class="card-body" style="padding:16px 24px;">
    <form method="GET" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
      <div class="form-group" style="margin:0; min-width:200px;">
        <label class="form-label">Pilih / Buat Periode (YYYY-MM)</label>
        <input class="form-control" type="month" name="periode" value="<?= sanitize($periodeAktif) ?>">
      </div>
      <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
      <?php if (!empty($periodeRows)): ?>
      <div style="font-size:.82rem; color:var(--clr-text-muted);">
        Periode ada: <?= implode(', ', array_map('htmlspecialchars', $periodeRows)) ?>
      </div>
      <?php endif; ?>
    </form>
  </div>
</div>

<?php if ($errors): ?>
<div class="alert alert-error" style="margin-bottom:16px;"><?= implode('<br>', $errors) ?></div>
<?php endif; ?>

<?php if (empty($karyawanList) || empty($kriteriaList)): ?>
<div class="card">
  <div class="card-body">
    <div class="empty-state">
      <p>Pastikan data <a href="<?= BASE_URL ?>/pages/karyawan/index.php">karyawan</a> dan <a href="<?= BASE_URL ?>/pages/kriteria/index.php">kriteria</a> sudah ditambahkan terlebih dahulu.</p>
    </div>
  </div>
</div>
<?php else: ?>

<div class="card">
  <div class="card-header">
    <div class="card-title">
      Matriks Nilai – Periode: <strong style="color:var(--clr-accent);"><?= sanitize($periodeAktif) ?></strong>
    </div>
  </div>
  <div class="card-body p0">
    <form method="POST" action="">
      <input type="hidden" name="periode" value="<?= sanitize($periodeAktif) ?>">

      <div class="table-wrap">
        <table class="tbl">
          <thead>
            <tr>
              <th style="min-width:160px;">Karyawan</th>
              <?php foreach ($kriteriaList as $kr): ?>
              <th style="text-align:center; min-width:130px;">
                <div><?= sanitize($kr['kode']) ?></div>
                <div style="font-size:.7rem; font-weight:400; white-space:normal; max-width:120px;"><?= sanitize($kr['nama']) ?></div>
                <span class="badge badge-<?= $kr['atribut'] ?>" style="margin-top:3px;"><?= strtoupper($kr['atribut']) ?> | <?= $kr['bobot'] ?>%</span>
              </th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($karyawanList as $k): ?>
            <tr>
              <td>
                <div style="font-weight:600; font-size:.875rem;"><?= sanitize($k['nama']) ?></div>
                <div style="font-size:.76rem; color:var(--clr-text-muted);"><?= sanitize($k['kode']) ?> – <?= sanitize($k['departemen']) ?></div>
              </td>
              <?php foreach ($kriteriaList as $kr):
                $existingVal = $nilaiExisting[$k['id']][$kr['id']] ?? '';
              ?>
              <td style="text-align:center;">
                <input class="form-control" type="number" step="0.01" min="0"
                       name="nilai[<?= $k['id'] ?>][<?= $kr['id'] ?>]"
                       value="<?= sanitize((string)$existingVal) ?>"
                       placeholder="0"
                       style="text-align:center; max-width:100px; margin:0 auto;">
              </td>
              <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Keterangan satuan -->
      <div style="padding:14px 20px; border-top:1px solid var(--clr-border); background:var(--clr-bg);">
        <p style="font-size:.78rem; color:var(--clr-text-muted);">
          <strong>Panduan Pengisian:</strong>
          C1 = Persentase kehadiran (0–100) &nbsp;|&nbsp;
          C2 = Jumlah hari terlambat &nbsp;|&nbsp;
          C3 = Hari izin tidak resmi &nbsp;|&nbsp;
          C4 = Persentase lembur (0–100) &nbsp;|&nbsp;
          C5 = Jumlah pelanggaran
        </p>
      </div>

      <div style="padding:16px 20px; border-top:1px solid var(--clr-border); display:flex; gap:10px;">
        <button type="submit" class="btn btn-accent">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Simpan Semua Nilai
        </button>
        <a href="<?= BASE_URL ?>/pages/penilaian/hasil.php?periode=<?= urlencode($periodeAktif) ?>" class="btn btn-primary">
          Proses Perhitungan SAW →
        </a>
      </div>
    </form>
  </div>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>