<?php
/**
 * login.php – Halaman autentikasi pengguna.
 */

define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/session.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user']    = ['id' => $user['id'], 'nama' => $user['nama'], 'email' => $user['email']];
            redirect('index.php');
        } else {
            $error = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login – SPK Kedisiplinan Karyawan</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="login-page">
  <div class="login-card">

    <div class="login-logo">
      <div class="login-logo-icon">
        <!-- Ganti dengan <img src="assets/img/logo.png"> jika punya logo -->
        <span>SPK</span>
      </div>
      <p class="login-title">SPK Kedisiplinan Karyawan</p>
      <p class="login-sub">Metode Simple Additive Weighting</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error" style="margin:0 0 16px;"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group" style="margin-bottom:14px;">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email"
               placeholder="admin@spk.com"
               value="<?= sanitize($_POST['email'] ?? '') ?>" required autofocus>
      </div>
      <div class="form-group" style="margin-bottom:22px;">
        <label class="form-label">Password</label>
        <input class="form-control" type="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:11px;">
        Masuk ke Sistem
      </button>
    </form>

    <p style="text-align:center; margin-top:20px; font-size:.78rem; color:var(--clr-text-muted);">
      Demo: <strong>admin@spk.com</strong> / <strong>password</strong>
    </p>
  </div>
</div>
</body>
</html>