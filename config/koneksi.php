<?php
/**
 * config/koneksi.php
 * Konfigurasi dan koneksi database menggunakan PDO.
 * Menggunakan PDO untuk keamanan (prepared statements) dan portabilitas.
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'spk_kedisiplinan');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'SPK Kedisiplinan Karyawan');
define('APP_VERSION', '1.0.0');

/**
 * Mendapatkan instance koneksi PDO (Singleton).
 * Koneksi hanya dibuat sekali selama satu request.
 *
 * @return PDO
 * @throws RuntimeException jika koneksi gagal
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Di production, jangan tampilkan pesan error mentah
            throw new RuntimeException('Koneksi database gagal: ' . $e->getMessage());
        }
    }

    return $pdo;
}