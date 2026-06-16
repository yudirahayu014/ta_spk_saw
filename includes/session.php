<?php
/**
 * includes/session.php
 * Manajemen sesi, autentikasi, dan helper global.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ------------------------------------------------------------------
// Auth Helpers
// ------------------------------------------------------------------

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function currentUser(): array
{
    return $_SESSION['user'] ?? [];
}

// ------------------------------------------------------------------
// Flash Message Helpers
// ------------------------------------------------------------------

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ------------------------------------------------------------------
// CSRF Protection
// ------------------------------------------------------------------

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ------------------------------------------------------------------
// Sanitasi & Validasi
// ------------------------------------------------------------------

function sanitize(mixed $value): string
{
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}