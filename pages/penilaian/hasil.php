<?php
/**
 * pages/penilaian/hasil.php
 * Halaman utama hasil perhitungan SAW beserta tombol cetak/laporan terintegrasi.
 * Menampilkan: Matriks Keputusan → Normalisasi → Perangkingan.
 */

define('BASE_URL', rtrim(str_replace(['/pages/penilaian'], [''], dirname($_SERVER['SCRIPT_NAME'])), '/'));
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../algorithm/SAW.php';

requireLogin();

$db = getDB();

// Ambil periode
$periodeRows  = $db->query('SELECT DISTINCT periode FROM nilai_matriks ORDER BY periode DESC')->fetchAll(PDO::FETCH_COLUMN);
$periodeAktif = $_GET['periode'] ?? ($periodeRows[0] ?? date('Y-m'));

// Ambil data kriteria dan karyawan
$kriteriaList = $db->query('SELECT * FROM kriteria ORDER BY kode')->fetchAll();
$karyawanList = $db->query('SELECT * FROM karyawan ORDER BY kode')->fetchAll();

// Bangun matriks keputusan [karyawan_id => [kriteria_id => nilai]]
$matriksKeputusan = [];
$karyawanMap      = [];
foreach ($karyawanList as $k) {
    $matriksKeputusan[$k['id']] = [];
    $karyawanMap[$k['id']]      = $k;
}

$stmt = $db->prepare('SELECT karyawan_id, kriteria_id, nilai FROM nilai_matriks WHERE periode = ?');
$stmt->execute([$periodeAktif]);
foreach ($stmt->fetchAll() as $row) {
    $matriksKeputusan[$row['karyawan_id']][$row['kriteria_id']] = $row['nilai'];
}

// Hapus karyawan yang tidak punya data nilai sama sekali
$matriksKeputusan = array_filter($matriksKeputusan, fn($v) => !empty($v));

$sawResult = null;
$hasData   = !empty($matriksKeputusan) && !empty($kriteriaList);

if ($hasData) {
    $saw = new SAW($kriteriaList, $matriksKeputusan);
    $saw->normalize()->rank();
    $sawResult = $saw->getRingkasan();
}

$pageTitle = 'Hasil Penilaian SAW';
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Header Halaman -->
<div class="page-header">
  <div>
    <h2 style="font-size:1.1rem; font-weight:700;">Hasil Penilaian SAW</h2>
    <p style="color:var(--clr-text-muted); font-size:.84rem;">
      Perhitungan lengkap metode Simple Additive Weighting – Periode:
      <strong style="color:var(--clr-accent);"><?= sanitize($periodeAktif) ?></strong>
    </p>
  </div>
  <div class="page-header-actions">
    <!-- Filter Periode -->
    <form method="GET" style="display:flex; gap:8px;">
      <input class="form-control" type="month" name="periode" value="<?= sanitize($periodeAktif) ?>" style="width:160px;">
      <button class="btn btn-outline btn-sm" type="submit">Tampilkan</button>
    </form>
    <!-- Tombol Laporan Terintegrasi -->
    <?php if ($hasData): ?>
    <button onclick="printHasil()" class="btn btn-print">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
      Cetak Laporan
    </button>
    <a href="<?= BASE_URL ?>/pages/penilaian/export.php?periode=<?= urlencode($periodeAktif) ?>"
       class="btn btn-accent">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Export HTML
    </a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/pages/penilaian/input.php?periode=<?= urlencode($periodeAktif) ?>"
       class="btn btn-outline btn-sm">← Edit Nilai</a>
  </div>
</div>

<?php if (!$hasData): ?>
<div class="card">
  <div class="card-body">
    <div class="empty-state">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      <p>Belum ada data nilai untuk periode <strong><?= sanitize($periodeAktif) ?></strong>.</p>
      <a href="<?= BASE_URL ?>/pages/penilaian/input.php?periode=<?= urlencode($periodeAktif) ?>"
         class="btn btn-accent btn-sm" style="margin-top:12px;">Input Nilai Sekarang</a>
    </div>
  </div>
</div>
<?php else:

$rankingData = $sawResult['nilai_preferensi']; // [karyawan_id => Vi]
$normaData   = $sawResult['matriks_normalisasi'];
$keputusanData = $sawResult['matriks_keputusan'];

$rank = 1;
$medals = ['🥇','🥈','🥉'];

?>

<!-- =========================================
     LANGKAH 1: MATRIKS KEPUTUSAN
     ========================================= -->
<div class="card" style="margin-bottom:20px;">
  <div class="card-header">
    <div class="card-title">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
      Langkah 1 – Matriks Keputusan (X)
    </div>
    <span style="font-size:.8rem; color:var(--clr-text-muted);">Nilai mentah tiap alternatif per kriteria</span>
  </div>
  <div class="card-body p0">
    <div class="table-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>Alternatif</th>
            <?php foreach ($kriteriaList as $kr): ?>
            <th style="text-align:center;">
              <?= sanitize($kr['kode']) ?><br>
              <span style="font-size:.7rem; font-weight:400;"><?= sanitize($kr['nama']) ?></span><br>
              <span class="badge badge-<?= $kr['atribut'] ?>"><?= strtoupper($kr['atribut']) ?></span>
            </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($keputusanData as $karyawanId => $nilaiRow): ?>
          <tr>
            <td>
              <strong><?= sanitize($karyawanMap[$karyawanId]['nama']) ?></strong>
              <div style="font-size:.76rem; color:var(--clr-text-muted);"><?= sanitize($karyawanMap[$karyawanId]['kode']) ?></div>
            </td>
            <?php foreach ($kriteriaList as $kr): ?>
            <td style="text-align:center; font-weight:600;">
              <?= number_format((float)($nilaiRow[$kr['id']] ?? 0), 2) ?>
            </td>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- =========================================
     LANGKAH 2: MATRIKS NORMALISASI
     ========================================= -->
<div class="card" style="margin-bottom:20px;">
  <div class="card-header">
    <div class="card-title">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 20h.01"/><path d="M7 20v-4"/><path d="M12 20v-8"/><path d="M17 20V8"/><path d="M22 4v16"/></svg>
      Langkah 2 – Matriks Normalisasi (R)
    </div>
    <span style="font-size:.8rem; color:var(--clr-text-muted);">
      Benefit: r = x / max(x) &nbsp;|&nbsp; Cost: r = min(x) / x
    </span>
  </div>
  <div class="card-body p0">
    <div class="table-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>Alternatif</th>
            <?php foreach ($kriteriaList as $kr): ?>
            <th style="text-align:center;">
              <?= sanitize($kr['kode']) ?> (W=<?= $kr['bobot'] ?>%)
            </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($normaData as $karyawanId => $nilaiRow): ?>
          <tr>
            <td>
              <strong><?= sanitize($karyawanMap[$karyawanId]['nama']) ?></strong>
            </td>
            <?php foreach ($kriteriaList as $kr): ?>
            <?php $r = $nilaiRow[$kr['id']] ?? 0; ?>
            <td style="text-align:center;">
              <span style="font-weight:600; color:<?= $r >= 0.8 ? 'var(--clr-accent)' : ($r >= 0.5 ? 'var(--clr-warning)' : 'var(--clr-danger)') ?>;">
                <?= number_format($r, 4) ?>
              </span>
            </td>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- =========================================
     LANGKAH 3: PERANGKINGAN
     ========================================= -->
<div class="card">
  <div class="card-header">
    <div class="card-title">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
      Langkah 3 – Hasil Perangkingan (V)
    </div>
    <span style="font-size:.8rem; color:var(--clr-text-muted);">V<sub>i</sub> = Σ (w<sub>j</sub> × r<sub>ij</sub>)</span>
  </div>
  <div class="card-body p0">
    <div class="table-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th style="text-align:center; width:60px;">Ranking</th>
            <th>Karyawan</th>
            <th>Departemen</th>
            <?php foreach ($kriteriaList as $kr): ?>
            <th style="text-align:center;"><?= sanitize($kr['kode']) ?> (r×w)</th>
            <?php endforeach; ?>
            <th style="text-align:center;">Nilai Vi</th>
            <th style="text-align:center;">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rankingData as $karyawanId => $vi): ?>
          <?php
            $k    = $karyawanMap[$karyawanId];
            $icon = $medals[$rank - 1] ?? '🏅';
            $wj   = [];
            foreach ($kriteriaList as $kr) {
                $bobot = (float)$kr['bobot'] / 100;
                $r     = $normaData[$karyawanId][$kr['id']] ?? 0;
                $wj[$kr['id']] = round($bobot * $r, 4);
            }
          ?>
          <tr style="<?= $rank === 1 ? 'background:rgba(15,169,104,.05);' : '' ?>">
            <td style="text-align:center;">
              <div class="medal"><?= $icon ?></div>
              <div class="tbl-rank">#<?= $rank ?></div>
            </td>
            <td>
              <strong><?= sanitize($k['nama']) ?></strong>
              <div style="font-size:.76rem; color:var(--clr-text-muted);"><?= sanitize($k['kode']) ?></div>
            </td>
            <td style="font-size:.84rem;"><?= sanitize($k['departemen']) ?></td>
            <?php foreach ($kriteriaList as $kr): ?>
            <td style="text-align:center; font-size:.82rem; color:var(--clr-text-muted);">
              <?= number_format($wj[$kr['id']], 4) ?>
            </td>
            <?php endforeach; ?>
            <td style="text-align:center;">
              <strong style="font-size:1rem; color:var(--clr-primary);"><?= number_format($vi, 4) ?></strong>
              <div style="margin-top:4px;">
                <div class="progress-bar-wrap" style="width:100px; margin:0 auto;">
                  <div class="progress-bar-fill" style="width:<?= round($vi * 100) ?>%;"></div>
                </div>
              </div>
            </td>
            <td style="text-align:center;">
              <?php if ($rank === 1): ?>
                <span class="badge" style="background:#FEF3E2;color:#D97706;">Terbaik</span>
              <?php elseif ($rank === 2): ?>
                <span class="badge" style="background:#EBF2FF;color:#3B82F6;">Sangat Baik</span>
              <?php elseif ($rank <= count($rankingData) - 1): ?>
                <span class="badge" style="background:#F0FDF4;color:#16A34A;">Baik</span>
              <?php else: ?>
                <span class="badge" style="background:#FEE2E2;color:#EF4444;">Perlu Perhatian</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php $rank++; endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Footer laporan terintegrasi -->
    <div style="padding:16px 24px; border-top:1px solid var(--clr-border); background:var(--clr-bg); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
      <div style="font-size:.82rem; color:var(--clr-text-muted);">
        Periode Penilaian: <strong><?= sanitize($periodeAktif) ?></strong> &nbsp;|&nbsp;
        Jumlah Karyawan: <strong><?= count($rankingData) ?></strong> &nbsp;|&nbsp;
        Dicetak: <?= date('d M Y H:i') ?>
      </div>
      <div style="display:flex; gap:8px;">
        <button onclick="printHasil()" class="btn btn-print btn-sm">
          🖨️ Cetak / Simpan PDF
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Rumus SAW untuk referensi -->
<div class="card" style="margin-top:20px;">
  <div class="card-header">
    <div class="card-title">Keterangan Bobot Kriteria</div>
  </div>
  <div class="card-body">
    <div style="display:flex; gap:12px; flex-wrap:wrap;">
      <?php foreach ($kriteriaList as $kr): ?>
      <div style="background:var(--clr-bg); border-radius:8px; padding:10px 14px; min-width:160px;">
        <div style="font-weight:700; font-size:.85rem;"><?= sanitize($kr['kode']) ?> – <?= $kr['bobot'] ?>%</div>
        <div style="font-size:.78rem; color:var(--clr-text-muted); margin-top:2px;"><?= sanitize($kr['nama']) ?></div>
        <span class="badge badge-<?= $kr['atribut'] ?>" style="margin-top:6px;"><?= strtoupper($kr['atribut']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>