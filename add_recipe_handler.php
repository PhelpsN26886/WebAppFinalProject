<?php
// add_recipe_handler.php – processes the Add Recipe form
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('add_recipe.php');
}

if (!is_logged_in()) {
    redirect('signin.php?error=' . urlencode('Please sign in to add a recipe.'));
}

$title        = trim($_POST['title']        ?? '');
$country      = trim($_POST['country']      ?? '');
$ingredients  = trim($_POST['ingredients']  ?? '');
$instructions = trim($_POST['instructions'] ?? '');

$errors = [];

if (strlen($title) < 2 || strlen($title) > 150) {
    $errors[] = 'Recipe title must be between 2 and 150 characters.';
}

if (strlen($country) < 2 || strlen($country) > 100) {
    $errors[] = 'Country of origin must be between 2 and 100 characters.';
}

if (strlen($ingredients) < 5) {
    $errors[] = 'Please list at least one ingredient.';
}

if (strlen($instructions) < 10) {
    $errors[] = 'Please provide instructions for making the recipe.';
}

if (!empty($errors)) {
    // Store prefill data in session so form repopulates
    $_SESSION['prefill'] = [
        'title'        => $title,
        'country'      => $country,
        'ingredients'  => $ingredients,
        'instructions' => $instructions,
    ];
    $err = urlencode(implode(' | ', $errors));
    redirect("add_recipe.php?error=$err");
}

$pdo  = get_db();
$stmt = $pdo->prepare(
    'INSERT INTO recipes (user_id, title, country, ingredients, instructions) VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([current_user_id(), $title, $country, $ingredients, $instructions]);

$new_id = (int)$pdo->lastInsertId();

// Go straight to the new recipe page
redirect("recipe.php?id=$new_id&review_success=" . urlencode('Your recipe has been published!'));
