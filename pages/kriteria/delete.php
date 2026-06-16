<?php
define('BASE_URL', rtrim(str_replace(['/pages/kriteria'], [''], dirname($_SERVER['SCRIPT_NAME'])), '/'));
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT nama FROM kriteria WHERE id = ?');
$stmt->execute([$id]);
$kriteria = $stmt->fetch();

if (!$kriteria) {
    setFlash('error', 'Kriteria tidak ditemukan.');
} else {
    $db->prepare('DELETE FROM kriteria WHERE id = ?')->execute([$id]);
    setFlash('success', "Kriteria <strong>{$kriteria['nama']}</strong> berhasil dihapus.");
}

redirect('pages/kriteria/index.php');