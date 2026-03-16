<?php
// add_recipe.php – form to add a new recipe (logged-in only)
require_once 'config.php';

if (!is_logged_in()) {
    redirect('signin.php?error=' . urlencode('Please sign in to add a recipe.'));
}

$error   = $_GET['error']   ?? null;
$success = $_GET['success'] ?? null;

$prefill = $_SESSION['prefill'] ?? [];
unset($_SESSION['prefill']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Recipe – World Cuisine</title>
    <link rel="stylesheet" href="finalProjectStyles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Source+Serif+4:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        body { background: var(--cream-background); align-items: stretch; }

        /* ── Nav ── */
        .top-nav {
            position: sticky; top: 0; background: var(--navy-text);
            padding: 14px 32px; display: flex; align-items: center;
            justify-content: space-between; z-index: 100;
        }
        .nav-brand { font-family:'Source Serif 4',Georgia,serif; font-size:1.3rem; color:var(--cream-accent); text-decoration:none; }
        .nav-brand span { color: var(--rustic-orange); }
        .top-nav-buttons { display:flex; align-items:center; gap:12px; }
        .nav-user { font-family:'Oswald',sans-serif; font-size:0.9rem; color:var(--cream-accent); }
        .nav-user strong { color: var(--rustic-orange); }
        .top-nav .search-nav-link { padding:7px 18px; font-size:0.85rem; font-family:'Oswald',sans-serif; }

        /* ── Page Hero ── */
        .page-hero {
            background: linear-gradient(135deg, var(--navy-text), #2d3a5a);
            padding: 44px 32px 36px; text-align: center;
        }
        .page-hero h1 {
            font-family:'Oswald',sans-serif; font-size:clamp(1.8rem,4vw,2.6rem);
            font-weight:700; text-transform:uppercase; letter-spacing:0.03em;
            color:white; margin:0 0 8px;
        }
        .page-hero p { font-family:'Oswald',sans-serif; color:#aab; margin:0; }

        /* ── Form Layout ── */
        .form-wrap { max-width:760px; margin:0 auto; padding:40px 24px 80px; width:100%; }

        .form-card {
            background:white; border-radius:20px; padding:40px 44px;
            box-shadow:0 4px 24px rgba(0,0,0,0.08);
        }

        .alert { padding:12px 16px; border-radius:10px; font-family:'Oswald',sans-serif; font-size:0.88rem; margin-bottom:22px; }
        .alert-error   { background:#fde8e8; border-left:4px solid #e53e3e; color:#c53030; }
        .alert-success { background:#e6f4ea; border-left:4px solid #38a169; color:#276749; }

        .form-card form { gap:0; }

        .field-group { margin-bottom:22px; }

        .field-group label {
            display:block; font-family:'Oswald',sans-serif; font-size:0.82rem;
            font-weight:700; text-transform:uppercase; letter-spacing:0.06em;
            color:var(--navy-text); margin-bottom:8px; margin-top:0;
        }

        .field-hint { font-family:'Oswald',sans-serif; font-size:0.78rem; color:#aaa; margin-top:5px; }

        .form-input {
            width:100%; padding:12px 16px; border:2px solid #e8dfc9; border-radius:10px;
            font-family:'Oswald',sans-serif; font-size:0.97rem; color:var(--navy-text);
            background:white; transition:border-color 0.2s, box-shadow 0.2s; margin-top:0;
        }
        .form-input:focus {
            outline:none; border-color:var(--rustic-orange);
            box-shadow:0 0 0 3px rgba(214,90,31,0.12);
        }
        textarea.form-input { resize:vertical; min-height:130px; line-height:1.6; }

        .divider-label {
            font-family:'Oswald',sans-serif; font-size:1.1rem; font-weight:700;
            text-transform:uppercase; letter-spacing:0.04em; color:var(--navy-text);
            margin:32px 0 18px; padding-bottom:10px;
            border-bottom:2px solid var(--cream-background);
        }

        .submit-btn {
            width:100%; padding:15px; background:var(--rustic-orange); color:white;
            border:none; border-radius:999px; font-family:'Oswald',sans-serif;
            font-size:1rem; font-weight:700; cursor:pointer; margin-top:12px;
            transition:background 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow:0 4px 14px rgba(214,90,31,0.35);
        }
        .submit-btn:hover { background:var(--olive); transform:translateY(-1px); box-shadow:0 6px 18px rgba(122,127,42,0.35); }

        .cancel-link {
            display:block; text-align:center; margin-top:16px;
            font-family:'Oswald',sans-serif; font-size:0.88rem; color:#999; text-decoration:none;
        }
        .cancel-link:hover { color:var(--rustic-orange); }
    </style>
</head>
<body>

<nav class="top-nav">
    <a href="homePage.php" class="nav-brand">World <span>Cuisine</span></a>
    <div class="top-nav-buttons">
        <span class="nav-user">Hello, <strong><?= h(current_username()) ?></strong></span>
        <a href="logout.php" class="search-nav-link">Log Out</a>
    </div>
</nav>

<div class="page-hero">
    <h1>Share a Recipe</h1>
    <p>Add your authentic dish to the World Cuisine collection</p>
</div>

<div class="form-wrap">
    <div class="form-card">

        <?php if ($error): ?>
            <div class="alert alert-error">⚠ <?= h(urldecode($error)) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">✓ <?= h(urldecode($success)) ?></div>
        <?php endif; ?>

        <form action="add_recipe_handler.php" method="POST">

            <h3 class="divider-label">Basic Information</h3>

            <div class="field-group">
                <label for="title">Recipe Title *</label>
                <input type="text" id="title" name="title" class="form-input"
                       placeholder="e.g. Nonna's Authentic Carbonara"
                       value="<?= h($prefill['title'] ?? '') ?>"
                       required maxlength="150">
            </div>

            <div class="field-group">
                <label for="country">Country of Origin *</label>
                <input type="text" id="country" name="country" class="form-input"
                       placeholder="e.g. Italy"
                       value="<?= h($prefill['country'] ?? '') ?>"
                       required maxlength="100">
            </div>

            <h3 class="divider-label">Recipe Details</h3>

            <div class="field-group">
                <label for="ingredients">Ingredients *</label>
                <textarea id="ingredients" name="ingredients" class="form-input"
                          placeholder="One ingredient per line:&#10;200g spaghetti&#10;150g guanciale&#10;3 large eggs"
                          required><?= h($prefill['ingredients'] ?? '') ?></textarea>
                <p class="field-hint">Enter one ingredient per line with quantity and unit.</p>
            </div>

            <div class="field-group">
                <label for="instructions">Instructions *</label>
                <textarea id="instructions" name="instructions" class="form-input"
                          style="min-height:200px;"
                          placeholder="One step per line:&#10;1. Boil the pasta in heavily salted water…&#10;2. Fry the guanciale until crispy…"
                          required><?= h($prefill['instructions'] ?? '') ?></textarea>
                <p class="field-hint">Enter each step on its own line. Start with the step number (e.g. "1. Preheat oven…").</p>
            </div>

            <button type="submit" class="submit-btn">🍽 Publish Recipe</button>
        </form>

        <a href="all.php" class="cancel-link">← Back to all recipes</a>
    </div>
</div>

<footer id="contact">
    <p>Contact Us: phelpsn@jbu.edu<br>(479) 866-2211</p>
</footer>

</body>
</html>
