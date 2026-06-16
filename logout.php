<?php
/**
 * logout.php – Hancurkan sesi dan redirect ke login.
 */

define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/session.php';

session_destroy();
header('Location: ' . BASE_URL . '/login.php');
exit;