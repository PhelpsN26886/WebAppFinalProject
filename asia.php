<?php
require_once 'config.php';

$continent_name = 'Asia';

$continent_countries = [
    'Afghanistan', 'Armenia', 'Azerbaijan', 'Bahrain', 'Bangladesh',
    'Bhutan', 'Brunei', 'Cambodia', 'China', 'Cyprus', 'Georgia',
    'India', 'Indonesia', 'Iran', 'Iraq', 'Israel', 'Japan', 'Jordan',
    'Kazakhstan', 'Kuwait', 'Kyrgyzstan', 'Laos', 'Lebanon', 'Malaysia',
    'Maldives', 'Mongolia', 'Myanmar', 'Nepal', 'North Korea', 'Oman',
    'Pakistan', 'Palestine', 'Philippines', 'Qatar', 'Russia',
    'Saudi Arabia', 'Singapore', 'South Korea', 'Sri Lanka', 'Syria',
    'Taiwan', 'Tajikistan', 'Thailand', 'Timor-Leste', 'Turkey',
    'Turkmenistan', 'United Arab Emirates', 'Uzbekistan', 'Vietnam',
    'Yemen'
];

$pdo = get_db();

$placeholders = implode(',', array_fill(0, count($continent_countries), '?'));

$stmt = $pdo->prepare("
    SELECT r.id, r.title, r.country, r.created_at,
           u.username AS author,
           COALESCE(AVG(rv.rating), 0) AS avg_rating,
           COUNT(rv.id) AS review_count
    FROM recipes r
    JOIN users u ON u.id = r.user_id
    LEFT JOIN reviews rv ON rv.recipe_id = r.id
    WHERE r.country IN ($placeholders)
    GROUP BY r.id, u.username
    ORDER BY r.country ASC, r.title ASC
");

$stmt->execute($continent_countries);
$recipes = $stmt->fetchAll();

function star_html(float $rating): string {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) $html .= '<span class="star filled">★</span>';
        elseif ($rating >= $i - 0.5) $html .= '<span class="star half">★</span>';
        else $html .= '<span class="star empty">☆</span>';
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= h($continent_name) ?> Recipes – World Cuisine</title>
    <link rel="icon" type="image/png" href="worldcuisinefavicon.png">
    <link rel="stylesheet" href="finalProjectStyles.css">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Source+Serif+4:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    <style>
        html, body {
        min-height: 100%;
        margin: 0;
        }

        body {
        background: var(--cream-background);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        }

        .top-nav {
            position: sticky; top: 0; background: var(--navy-text); z-index: 100;
            padding: 14px 32px; display: flex; align-items: center; justify-content: space-between;
        }

        .nav-brand {
            font-family:'Source Serif 4', Georgia, serif;
            font-size:1.3rem; color:var(--cream-accent); text-decoration:none;
        }

        .nav-brand span { color: var(--rustic-orange); }

        .top-nav-buttons { display:flex; align-items:center; gap:12px; }

        .nav-user {
            font-family:'Oswald', sans-serif;
            font-size:0.9rem; color:var(--cream-accent);
        }

        .nav-user strong { color: var(--rustic-orange); }

        .top-nav .search-nav-link {
            padding:7px 18px;
            font-size:0.85rem;
            font-family:'Oswald', sans-serif;
        }

        .page-hero {
            width: 100%;
            box-sizing: border-box;
            background: linear-gradient(135deg, var(--navy-text), #2d3a5a);
            color: var(--cream-accent);
            padding: 52px 32px 44px;
            text-align: center;
        }

        .page-hero h1 {
            font-family:'Oswald', sans-serif;
            font-size:clamp(2rem,5vw,3.2rem);
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:0.04em;
            margin:0 0 10px;
        }

        .page-hero p {
            font-family:'Oswald', sans-serif;
            color:#aab;
            margin:0;
        }

        .page-content {
            max-width:1300px;
            margin:0 auto;
            padding:36px 24px 64px;
            flex: 1;
        }

        .back-link {
            display:inline-block;
            margin-bottom:26px;
            font-family:'Oswald', sans-serif;
            color:var(--rustic-orange);
            font-weight:700;
            text-decoration:none;
        }

        .recipe-grid {
            display:grid;
            grid-template-columns:repeat(2,1fr);
            gap:24px;
        }

        @media (min-width:1000px) {
            .recipe-grid { grid-template-columns:repeat(3,1fr); }
        }

        @media (max-width:650px) {
            .recipe-grid { grid-template-columns:1fr; }
        }

        .recipe-card {
            background:white;
            border-radius:18px;
            overflow:hidden;
            box-shadow:0 2px 12px rgba(0,0,0,0.07);
            transition:transform 0.22s ease, box-shadow 0.22s ease;
            display:flex;
            flex-direction:column;
            text-decoration:none;
            color:inherit;
        }

        .recipe-card:hover {
            transform:translateY(-5px);
            box-shadow:0 12px 32px rgba(0,0,0,0.14);
        }

        .card-flag {
            background:linear-gradient(135deg,var(--rustic-orange),var(--olive));
            padding:28px 20px 20px;
            min-height:90px;
            display:flex;
            align-items:flex-end;
        }

        .card-country-badge {
            background:rgba(255,255,255,0.2);
            color:white;
            padding:4px 12px;
            border-radius:999px;
            font-family:'Oswald',sans-serif;
            font-size:0.75rem;
            font-weight:700;
            letter-spacing:0.05em;
            text-transform:uppercase;
        }

        .card-body {
            padding:18px 20px;
            flex:1;
            display:flex;
            flex-direction:column;
        }

        .card-title {
            font-family:'Source Serif 4', Georgia, serif;
            font-size:1.15rem;
            color:var(--navy-text);
            margin:0 0 10px;
            line-height:1.3;
        }

        .card-stars {
            display:flex;
            align-items:center;
            gap:6px;
            margin-bottom:10px;
        }

        .star { font-size:1rem; }
        .star.filled { color:#f59e0b; }
        .star.half { color:#fcd34d; }
        .star.empty { color:#d4cfc8; }

        .card-rating-text {
            font-family:'Oswald', sans-serif;
            font-size:0.8rem;
            color:#999;
        }

        .card-meta {
            margin-top:auto;
            padding-top:12px;
            border-top:1px solid #f0ebe2;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        .card-author {
            font-family:'Oswald', sans-serif;
            font-size:0.8rem;
            color:#aaa;
        }

        .card-author strong { color:var(--olive); }

        .card-date {
            font-family:'Oswald', sans-serif;
            font-size:0.75rem;
            color:#ccc;
        }

        .empty-state {
            text-align:center;
            background:white;
            border-radius:18px;
            padding:70px 24px;
            box-shadow:0 2px 12px rgba(0,0,0,0.06);
            font-family:'Oswald', sans-serif;
            color:#999;
        }

        .empty-state h2 {
            font-family:'Source Serif 4', Georgia, serif;
            color:var(--navy-text);
            margin-bottom:8px;
        }
    </style>
</head>

<body>

<nav class="top-nav">
    <a href="homePage.php" class="nav-brand">World <span>Cuisine</span></a>

    <div class="top-nav-buttons">
        <?php if (is_logged_in()): ?>
            <span class="nav-user">Hello, <strong><?= h(current_username()) ?></strong></span>
            <a href="add_recipe.php" class="search-nav-link">+ Add Recipe</a>
            <a href="logout.php" class="search-nav-link">Log Out</a>
        <?php else: ?>
            <a href="signin.php" class="search-nav-link">Sign In</a>
            <a href="signup.php" class="search-nav-link">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<div class="page-hero">
    <h1><?= h($continent_name) ?> Recipes</h1>
    <p><?= count($recipes) ?> recipe<?= count($recipes) === 1 ? '' : 's' ?> found from <?= h($continent_name) ?></p>
</div>

<div class="page-content">
    <a href="worldmapsearch.php" class="back-link">← Back to World Map Search</a>

    <?php if (empty($recipes)): ?>
        <div class="empty-state">
            <h2>No <?= h($continent_name) ?> recipes yet</h2>
            <p>Once someone adds a recipe from this continent, it will show up here.</p>
        </div>
    <?php else: ?>
        <div class="recipe-grid">
            <?php foreach ($recipes as $recipe): ?>
                <a href="recipe.php?id=<?= (int)$recipe['id'] ?>" class="recipe-card">
                    <div class="card-flag">
                        <span class="card-country-badge"><?= h($recipe['country']) ?></span>
                    </div>

                    <div class="card-body">
                        <h2 class="card-title"><?= h($recipe['title']) ?></h2>

                        <div class="card-stars">
                            <?= star_html((float)$recipe['avg_rating']) ?>
                            <span class="card-rating-text">
                                <?= number_format((float)$recipe['avg_rating'], 1) ?>
                                (<?= (int)$recipe['review_count'] ?> <?= $recipe['review_count'] == 1 ? 'review' : 'reviews' ?>)
                            </span>
                        </div>

                        <div class="card-meta">
                            <span class="card-author">by <strong><?= h($recipe['author']) ?></strong></span>
                            <span class="card-date"><?= date('M j, Y', strtotime($recipe['created_at'])) ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<footer id="contact">
    <p>Contact Us: phelpsn@jbu.edu<br>(479) 866-2211</p>
</footer>

</body>
</html>