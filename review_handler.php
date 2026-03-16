<?php
// review_handler.php – Submit a review (POST, logged-in users only)
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('all.php');
}

if (!is_logged_in()) {
    redirect('signin.php?error=' . urlencode('You must be signed in to leave a review.'));
}

$recipe_id = (int)($_POST['recipe_id'] ?? 0);
$rating    = (int)($_POST['rating']    ?? 0);
$comment   = trim($_POST['comment']    ?? '');

if ($recipe_id <= 0 || $rating < 1 || $rating > 5) {
    $err = urlencode('Invalid rating. Please select 1–5 stars.');
    redirect("recipe.php?id=$recipe_id&review_error=$err");
}

$pdo = get_db();

// Confirm recipe exists
$chk = $pdo->prepare('SELECT id FROM recipes WHERE id = ?');
$chk->execute([$recipe_id]);
if (!$chk->fetch()) {
    redirect('all.php');
}

// Insert review (UNIQUE constraint prevents duplicates)
try {
    $stmt = $pdo->prepare(
        'INSERT INTO reviews (recipe_id, user_id, rating, comment) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$recipe_id, current_user_id(), $rating, $comment ?: null]);
} catch (\PDOException $e) {
    if ($e->getCode() === '23000') {
        // Duplicate entry
        $err = urlencode('You have already reviewed this recipe.');
        redirect("recipe.php?id=$recipe_id&review_error=$err");
    }
    throw $e;
}

$ok = urlencode('Your review was posted successfully!');
redirect("recipe.php?id=$recipe_id&review_success=$ok");
