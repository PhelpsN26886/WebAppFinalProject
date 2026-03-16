<?php
// signin_handler.php – processes the Sign In form (POST only)
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('signin.php');
}

$identifier = trim($_POST['identifier'] ?? '');   // username OR email
$password   =       $_POST['password']  ?? '';

// ── Look up user by username or email ─────────────────────────
$pdo  = get_db();
$stmt = $pdo->prepare(
    'SELECT id, username, password FROM users WHERE username = ? OR email = ? LIMIT 1'
);
$stmt->execute([$identifier, $identifier]);
$user = $stmt->fetch();

// ── Verify password ───────────────────────────────────────────
if (!$user || !password_verify($password, $user['password'])) {
    $msg = urlencode('Invalid username / email or password.');
    redirect("signin.php?error=$msg");
}

// ── Start session ─────────────────────────────────────────────
$_SESSION['user_id']  = (int)$user['id'];
$_SESSION['username'] = $user['username'];

redirect('homePage.php');
