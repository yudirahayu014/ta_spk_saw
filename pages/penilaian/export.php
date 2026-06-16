<?php
/**
 * pages/penilaian/export.php
 * Generate laporan HTML yang bisa disimpan / dicetak mandiri (tanpa sidebar).
 */

define('BASE_URL', rtrim(str_replace(['/pages/penilaian'], [''], dirname($_SERVER['SCRIPT_NAME'])), '/'));
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../algorithm/SAW.php';

requireLogin();

$db           = getDB();
$periodeAktif = $_GET['periode'] ?? date('Y-m');
$kriteriaList = $db->query('SELECT * FROM kriteria ORDER BY kode')->fetchAll();
$karyawanList = $db->query('SELECT * FROM karyawan ORDER BY kode')->fetchAll();

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
$matriksKeputusan = array_filter($matriksKeputusan, fn($v) => !empty($v));

if (empty($matriksKeputusan)) {
    setFlash('error', 'Tidak ada data untuk diekspor pada periode ini.');
    redirect("pages/penilaian/hasil.php?periode={$periodeAktif}");
}

$saw = new SAW($kriteriaList, $matriksKeputusan);
$saw->normalize()->rank();
$ringkasan   = $saw->getRingkasan();
$rankingData = $ringkasan['nilai_preferensi'];
$normaData   = $ringkasan['matriks_normalisasi'];
$medals      = ['🥇','🥈','🥉'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan SAW – Periode <?= sanitize($periodeAktif) ?></title>
  <style>
    * { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:'Segoe UI',Arial,sans-serif; color:#1A2535; font-size:13px; padding:24px; }
    h1 { font-size:1.3rem; color:#1E3A5F; }
    h2 { font-size:1rem; color:#1E3A5F; margin:20px 0 8px; }
    .meta { color:#6B7A99; font-size:.82rem; margin-top:4px; }
    table { width:100%; border-collapse:collapse; margin-bottom:16px; font-size:.82rem; }
    th { background:#1E3A5F; color:#fff; padding:7px 10px; text-align:left; }
    td { padding:7px 10px; border-bottom:1px solid #DDE3EE; }
    tr:nth-child(even) td { background:#F4F6FB; }
    .rank1 { background:#FEF3E2 !important; font-weight:700; }
    .badge { display:inline-block; padding:2px 8px; border-radius:20px; font-size:.72rem; font-weight:600; }
    .benefit { background:#E6F7F0; color:#0FA968; }
    .cost    { background:#FEE2E2; color:#EF4444; }
    .footer  { margin-top:24px; padding-top:12px; border-top:1px solid #DDE3EE; font-size:.78rem; color:#6B7A99; }
    @media print { .no-print { display:none; } }
  </style>
</head>
<body>

<div style="text-align:center; margin-bottom:20px; border-bottom:2px solid #1E3A5F; padding-bottom:14px;">
  <h1>LAPORAN PENILAIAN KEDISIPLINAN KARYAWAN</h1>
  <h1 style="font-size:1rem; font-weight:400; margin-top:4px;">Menggunakan Metode Simple Additive Weighting (SAW)</h1>
  <p class="meta">Periode: <strong><?= sanitize($periodeAktif) ?></strong> &nbsp;|&nbsp; Dicetak: <?= date('d F Y, H:i') ?></p>
</div>

<div class="no-print" style="margin-bottom:16px;">
  <button onclick="window.print()" style="background:#1E3A5F;color:#fff;border:none;padding:8px 18px;border-radius:6px;cursor:pointer;font-size:.875rem;">🖨️ Cetak / Simpan PDF</button>
  <a href="javascript:history.back()" style="margin-left:10px; font-size:.84rem; color:#1E3A5F;">← Kembali</a>
</div>

<!-- Kriteria -->
<h2>1. Data Kriteria &amp; Bobot</h2>
<table>
  <thead><tr><th>Kode</th><th>Nama Kriteria</th><th>Bobot</th><th>Atribut</th></tr></thead>
  <tbody>
    <?php foreach ($kriteriaList as $kr): ?>
    <tr>
      <td><?= sanitize($kr['kode']) ?></td>
      <td><?= sanitize($kr['nama']) ?></td>
      <td><?= $kr['bobot'] ?>%</td>
      <td><span class="badge <?= $kr['atribut'] ?>"><?= strtoupper($kr['atribut']) ?></span></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- Matriks Keputusan -->
<h2>2. Matriks Keputusan (X)</h2>
<table>
  <thead>
    <tr>
      <th>Karyawan</th>
      <?php foreach ($kriteriaList as $kr): ?><th><?= sanitize($kr['kode']) ?></th><?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($ringkasan['matriks_keputusan'] as $kid => $row): ?>
    <tr>
      <td><?= sanitize($karyawanMap[$kid]['nama']) ?></td>
      <?php foreach ($kriteriaList as $kr): ?>
      <td><?= number_format((float)($row[$kr['id']] ?? 0), 2) ?></td>
      <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- Matriks Normalisasi -->
<h2>3. Matriks Normalisasi (R)</h2>
<table>
  <thead>
    <tr>
      <th>Karyawan</th>
      <?php foreach ($kriteriaList as $kr): ?><th><?= sanitize($kr['kode']) ?></th><?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($normaData as $kid => $row): ?>
    <tr>
      <td><?= sanitize($karyawanMap[$kid]['nama']) ?></td>
      <?php foreach ($kriteriaList as $kr): ?>
      <td><?= number_format($row[$kr['id']] ?? 0, 4) ?></td>
      <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- Hasil Perangkingan -->
<h2>4. Hasil Perangkingan (V<sub>i</sub>)</h2>
<table>
  <thead>
    <tr><th>Ranking</th><th>Kode</th><th>Nama Karyawan</th><th>Departemen</th><th>Nilai Vi</th></tr>
  </thead>
  <tbody>
    <?php $rank = 1; foreach ($rankingData as $kid => $vi):
      $k = $karyawanMap[$kid]; ?>
    <tr class="<?= $rank===1 ? 'rank1' : '' ?>">
      <td style="text-align:center;"><?= $medals[$rank-1] ?? '' ?> #<?= $rank ?></td>
      <td><?= sanitize($k['kode']) ?></td>
      <td><strong><?= sanitize($k['nama']) ?></strong></td>
      <td><?= sanitize($k['departemen']) ?></td>
      <td><strong><?= number_format($vi, 4) ?></strong></td>
    </tr>
    <?php $rank++; endforeach; ?>
  </tbody>
</table>

<div class="footer">
  <p><strong>Keterangan:</strong> Nilai Vi dihitung menggunakan rumus SAW: V<sub>i</sub> = Σ (w<sub>j</sub> × r<sub>ij</sub>).
  Semakin tinggi nilai Vi, semakin disiplin karyawan tersebut.</p>
  <p style="margin-top:6px;">Laporan ini dibuat otomatis oleh Sistem Pendukung Keputusan Penilaian Kedisiplinan Karyawan.</p>
</div>
</body>
</html>