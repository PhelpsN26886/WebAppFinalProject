<?php
// signup_handler.php – processes the Sign Up form (POST only)
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('signup.php');
}

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email']    ?? '');
$password =       $_POST['password'] ?? '';
$confirm  =       $_POST['confirm']  ?? '';

$errors = [];

// ── Validation ────────────────────────────────────────────────
if (strlen($username) < 3 || strlen($username) > 50) {
    $errors[] = 'Username must be between 3 and 50 characters.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long.';
}

if ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
}

// ── Check uniqueness ──────────────────────────────────────────
if (empty($errors)) {
    $pdo  = get_db();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $errors[] = 'That username or email is already registered.';
    }
}

// ── Save or return errors ─────────────────────────────────────
if (!empty($errors)) {
    // Bounce back with error message in query string
    $msg = urlencode(implode(' | ', $errors));
    redirect("signup.php?error=$msg");
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$pdo  = get_db();
$stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
$stmt->execute([$username, $email, $hash]);

$newId = (int)$pdo->lastInsertId();

// ── Auto-login after signup ───────────────────────────────────
$_SESSION['user_id']  = $newId;
$_SESSION['username'] = $username;

redirect('homePage.php');
