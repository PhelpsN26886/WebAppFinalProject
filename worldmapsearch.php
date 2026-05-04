<?php
require_once 'config.php';

$continents = [
    ['name' => 'Africa', 'file' => 'africa.php', 'image' => 'africa.png', 'description' => 'Explore recipes, ingredients, and traditions across Africa.'],
    ['name' => 'Antarctica', 'file' => 'antarctica.php', 'image' => 'antarctica.png', 'description' => 'Explore cold-climate food history and research-station cuisine.'],
    ['name' => 'Australia', 'file' => 'australia.php', 'image' => 'australia.png', 'description' => 'Explore Australian dishes, flavors, and regional food culture.'],
    ['name' => 'Asia', 'file' => 'asia.php', 'image' => 'asia.png', 'description' => 'Explore rich food traditions from across Asia.'],
    ['name' => 'Europe', 'file' => 'europe.php', 'image' => 'europe.png', 'description' => 'Explore classic and modern European cuisine.'],
    ['name' => 'South America', 'file' => 'southamerica.php', 'image' => 'southamerica.png', 'description' => 'Explore bold flavors and dishes from South America.'],
    ['name' => 'North America', 'file' => 'northamerica.php', 'image' => 'northamerica.png', 'description' => 'Explore recipes and food traditions across North America.'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Continent Search – World Cuisine</title>

    <link rel="icon" type="image/png" href="worldcuisinefavicon.png">
    <link rel="stylesheet" href="finalProjectStyles.css">

    <style>
        html, body {
            min-height: 100%;
            margin: 0;
        }

        body {
            background-color: var(--cream-background);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        .top-nav {
            position: sticky;
            top: 0;
            background: var(--navy-text);
            z-index: 100;
            padding: 14px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-brand {
            font-family: 'Source Serif 4', Georgia, serif;
            font-size: 1.3rem;
            color: var(--cream-accent);
            text-decoration: none;
        }

        .nav-brand span {
            color: var(--rustic-orange);
        }

        .top-nav-buttons {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-user {
            font-family: 'Oswald', sans-serif;
            font-size: 0.9rem;
            color: var(--cream-accent);
        }

        .nav-user strong {
            color: var(--rustic-orange);
        }

        .nav-add-btn {
            background: var(--rustic-orange);
            color: white;
            text-decoration: none;
            padding: 8px 20px;
            border-radius: 999px;
            font-family: 'Oswald', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            transition: background 0.2s, transform 0.15s;
        }

        .nav-add-btn:hover {
            background: var(--olive);
            transform: translateY(-1px);
        }

        .page-hero {
            background: var(--navy-text);
            color: var(--cream-accent);
            padding: 52px 32px 44px;
            text-align: center;
        }

        .page-hero h1 {
            font-family: 'Oswald', sans-serif;
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin: 0 0 10px;
        }

        .page-hero p {
            font-family: 'Oswald', sans-serif;
            color: #aab;
            margin: 0;
            font-size: 1rem;
        }

        .page-content {
            max-width: 1300px;
            width: 100%;
            margin: 0 auto;
            padding: 38px 24px 64px;
            box-sizing: border-box;
            flex: 1;
        }

        .continent-list {
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        .continent-card {
            width: 100%;
            min-height: 260px;
            background: white;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
            transition: transform 0.22s ease, box-shadow 0.22s ease;
            display: grid;
            grid-template-columns: 42% 58%;
            text-decoration: none;
            color: inherit;
        }

        .continent-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.14);
        }

        .continent-image-wrap {
            background: linear-gradient(135deg, var(--rustic-orange), var(--olive));
            min-height: 260px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .continent-image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .image-placeholder {
            font-family: 'Oswald', sans-serif;
            color: white;
            text-align: center;
            padding: 24px;
            font-size: 1rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .continent-body {
            padding: 34px 38px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .continent-label {
            width: fit-content;
            background: var(--cream-background);
            color: var(--olive);
            padding: 5px 14px;
            border-radius: 999px;
            font-family: 'Oswald', sans-serif;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 14px;
        }

        .continent-title {
            font-family: 'Source Serif 4', Georgia, serif;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            color: var(--navy-text);
            margin: 0 0 12px;
            line-height: 1.1;
        }

        .continent-description {
            font-family: 'Oswald', sans-serif;
            font-size: 1rem;
            color: #777;
            margin: 0 0 22px;
            max-width: 620px;
        }

        .continent-action {
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            color: var(--rustic-orange);
            letter-spacing: 0.03em;
        }

        footer {
            margin-top: auto;
            width: 100%;
        }

        @media (max-width: 760px) {
            .top-nav {
                flex-direction: column;
                gap: 12px;
            }

            .continent-card {
                grid-template-columns: 1fr;
            }

            .continent-image-wrap {
                min-height: 210px;
            }

            .continent-body {
                padding: 26px 24px;
            }
        }
    </style>
</head>

<body>

<nav class="top-nav">
    <a href="homePage.php" class="nav-brand">World <span>Cuisine</span></a>

    <div class="top-nav-buttons">
        <?php if (is_logged_in()): ?>
            <span class="nav-user">Hello, <strong><?= h(current_username()) ?></strong></span>
            <a href="add_recipe.php" class="nav-add-btn">+ Add Recipe</a>
            <a href="logout.php" class="search-nav-link">Log Out</a>
        <?php else: ?>
            <a href="signin.php" class="search-nav-link">Sign In</a>
            <a href="signup.php" class="search-nav-link">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<div class="page-hero">
    <h1>Continent Search</h1>
    <p>Choose a continent to explore recipes by region</p>
</div>

<main class="page-content">
    <div class="continent-list">
        <?php foreach ($continents as $continent): ?>
            <a href="<?= h($continent['file']) ?>" class="continent-card">
                <div class="continent-image-wrap">
                    <img src="<?= h($continent['image']) ?>" alt="<?= h($continent['name']) ?> continent image"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">

                    <div class="image-placeholder" style="display:none;">
                        Add <?= h($continent['name']) ?> Image Here
                    </div>
                </div>

                <div class="continent-body">
                    <span class="continent-label">Continent</span>
                    <h2 class="continent-title"><?= h($continent['name']) ?></h2>
                    <p class="continent-description"><?= h($continent['description']) ?></p>
                    <span class="continent-action">Explore <?= h($continent['name']) ?> →</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</main>

<footer id="contact">
    <p>Contact Us: phelpsn@jbu.edu // westc@jbu.edu<br>(479) 866-2211 // (913)-201-8993</p>
</footer>

</body>
</html>