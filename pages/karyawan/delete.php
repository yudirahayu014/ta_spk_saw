<?php
/**
 * pages/karyawan/delete.php – Hapus data karyawan.
 */

define('BASE_URL', rtrim(str_replace(['/pages/karyawan'], [''], dirname($_SERVER['SCRIPT_NAME'])), '/'));
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT nama FROM karyawan WHERE id = ?');
$stmt->execute([$id]);
$karyawan = $stmt->fetch();

if (!$karyawan) {
    setFlash('error', 'Data karyawan tidak ditemukan.');
} else {
    // FK ON DELETE CASCADE akan otomatis hapus nilai_matriks terkait
    $del = $db->prepare('DELETE FROM karyawan WHERE id = ?');
    $del->execute([$id]);
    setFlash('success', "Karyawan {$karyawan['nama']} berhasil dihapus.");
}

redirect('pages/karyawan/index.php');