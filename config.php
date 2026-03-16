<?php
// ============================================================
//  config.php  –  Database connection & shared utilities
//  Include this file at the top of every PHP page.
// ============================================================

// ── Database credentials ─────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // ← change to your MySQL username
define('DB_PASS', '');              // ← change to your MySQL password
define('DB_NAME', 'world_cuisine');

// ── Session start (safe to call multiple times) ───────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── PDO connection (lazy singleton) ──────────────────────────
function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // In production you'd log this, not expose it
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// ── Auth helpers ──────────────────────────────────────────────
function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

function current_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function current_username(): ?string {
    return $_SESSION['username'] ?? null;
}

// ── Redirect helper ───────────────────────────────────────────
function redirect(string $url): never {
    header("Location: $url");
    exit;
}

// ── Sanitise output helper ────────────────────────────────────
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
