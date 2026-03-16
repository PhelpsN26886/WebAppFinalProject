<?php
// recipe.php – View a single recipe and its reviews
require_once 'config.php';

$id  = (int)($_GET['id'] ?? 0);
$pdo = get_db();

$stmt = $pdo->prepare("
    SELECT r.*, u.username AS author,
           COALESCE(AVG(rv.rating), 0) AS avg_rating,
           COUNT(rv.id) AS review_count
    FROM   recipes r
    JOIN   users   u  ON u.id = r.user_id
    LEFT   JOIN reviews rv ON rv.recipe_id = r.id
    WHERE  r.id = ?
    GROUP  BY r.id, u.username
");
$stmt->execute([$id]);
$recipe = $stmt->fetch();

if (!$recipe) {
    http_response_code(404);
    die('<p style="font-family:sans-serif;padding:40px">Recipe not found. <a href="all.php">Browse recipes</a></p>');
}

$rev_stmt = $pdo->prepare("
    SELECT rv.*, u.username FROM reviews rv
    JOIN   users u ON u.id = rv.user_id
    WHERE  rv.recipe_id = ?
    ORDER  BY rv.created_at DESC
");
$rev_stmt->execute([$id]);
$reviews = $rev_stmt->fetchAll();

$user_review = null;
if (is_logged_in()) {
    $chk = $pdo->prepare('SELECT id FROM reviews WHERE recipe_id = ? AND user_id = ?');
    $chk->execute([$id, current_user_id()]);
    $user_review = $chk->fetchColumn();
}

$review_error   = $_GET['review_error']   ?? null;
$review_success = $_GET['review_success'] ?? null;

function star_html(float $rating): string {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i)            $html .= '<span class="star filled">★</span>';
        elseif ($rating >= $i - 0.5) $html .= '<span class="star half">★</span>';
        else                         $html .= '<span class="star empty">☆</span>';
    }
    return $html;
}

$ingredients  = array_filter(array_map('trim', explode("\n", $recipe['ingredients'])));
$instructions = array_filter(array_map('trim', explode("\n", $recipe['instructions'])));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($recipe['title']) ?> – World Cuisine</title>
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

        /* ── Hero ── */
        .recipe-hero {
            background: linear-gradient(135deg, var(--navy-text) 0%, #2d3a5a 100%);
            padding: 52px 32px 44px; text-align: center;
        }
        .country-badge {
            display: inline-block; background: var(--rustic-orange); color: white;
            padding: 5px 18px; border-radius: 999px; font-family:'Oswald',sans-serif;
            font-size: 0.8rem; font-weight: 700; letter-spacing: 0.07em;
            text-transform: uppercase; margin-bottom: 18px;
        }
        .recipe-hero h1 {
            font-family:'Oswald',sans-serif; font-size:clamp(1.8rem,5vw,3rem);
            font-weight:700; letter-spacing:0.03em; color:white; margin:0 0 16px;
        }
        .hero-meta { display:flex; justify-content:center; align-items:center; gap:24px; flex-wrap:wrap; }
        .hero-stars { display:flex; align-items:center; gap:6px; }
        .star { font-size:1.3rem; }
        .star.filled { color:#f59e0b; }
        .star.half   { color:#fcd34d; }
        .star.empty  { color:#555; }
        .hero-rating-num { font-family:'Oswald',sans-serif; color:#aab; font-size:0.95rem; }
        .hero-author { font-family:'Oswald',sans-serif; color:#aab; font-size:0.92rem; }
        .hero-author strong { color: var(--cream-accent); }

        /* ── Content Layout ── */
        .recipe-content {
            max-width: 960px; margin: 0 auto; padding: 40px 24px 80px;
            display: grid; grid-template-columns: 300px 1fr; gap: 36px; align-items: start;
        }
        @media (max-width:700px) { .recipe-content { grid-template-columns:1fr; } }

        /* ── Ingredients Sidebar ── */
        .ingredients-card {
            background: white; border-radius: 18px; padding: 28px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06); position: sticky; top: 80px;
        }
        .card-heading {
            font-family:'Oswald',sans-serif; font-size:1.2rem; font-weight:700;
            text-transform:uppercase; letter-spacing:0.03em; color:var(--navy-text);
            margin:0 0 18px; padding-bottom:12px; border-bottom:2px solid var(--cream-background);
        }
        .ingredient-list { list-style:none; margin:0; padding:0; }
        .ingredient-list li {
            font-family:'Oswald',sans-serif; font-size:0.95rem; color:var(--navy-text);
            padding:8px 0; border-bottom:1px solid #f5f0e8;
            display:flex; align-items:flex-start; gap:8px;
        }
        .ingredient-list li::before { content:'•'; color:var(--rustic-orange); font-size:1.2rem; line-height:1; flex-shrink:0; }
        .ingredient-list li:last-child { border-bottom:none; }

        /* ── Instructions ── */
        .instructions-card {
            background:white; border-radius:18px; padding:32px;
            box-shadow:0 2px 12px rgba(0,0,0,0.06); margin-bottom:28px;
        }
        .step { display:flex; gap:18px; margin-bottom:22px; align-items:flex-start; }
        .step:last-child { margin-bottom:0; }
        .step-num {
            background:var(--rustic-orange); color:white; width:32px; height:32px;
            border-radius:50%; display:flex; align-items:center; justify-content:center;
            font-family:'Oswald',sans-serif; font-weight:700; font-size:0.85rem; flex-shrink:0;
        }
        .step-text { font-family:'Oswald',sans-serif; font-size:0.97rem; line-height:1.65; color:var(--navy-text); padding-top:4px; }

        /* ── Reviews ── */
        .reviews-card { background:white; border-radius:18px; padding:32px; box-shadow:0 2px 12px rgba(0,0,0,0.06); }
        .reviews-header { display:flex; justify-content:space-between; align-items:baseline; margin-bottom:24px; }
        .review-item { padding:18px 0; border-bottom:1px solid #f5f0e8; }
        .review-item:last-child { border-bottom:none; }
        .review-meta { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; flex-wrap:wrap; gap:8px; }
        .reviewer-name { font-family:'Oswald',sans-serif; font-weight:700; color:var(--olive); font-size:0.9rem; }
        .review-date   { font-family:'Oswald',sans-serif; font-size:0.8rem; color:#bbb; }
        .review-stars .star { font-size:0.9rem; }
        .review-comment { font-family:'Oswald',sans-serif; font-size:0.92rem; color:#555; line-height:1.6; margin:8px 0 0; }

        /* ── Add Review Form ── */
        .add-review-card { background:var(--cream-background); border:2px dashed #e5ddd0; border-radius:18px; padding:28px; margin-top:24px; }
        .add-review-card h3 { font-family:'Oswald',sans-serif; font-size:1.1rem; font-weight:700; text-transform:uppercase; letter-spacing:0.03em; color:var(--navy-text); margin:0 0 18px; }
        .star-picker { display:flex; gap:4px; margin-bottom:14px; }
        .star-picker input[type="radio"] { display:none; }
        .star-picker label { font-size:2rem; color:#d4cfc8; cursor:pointer; transition:color 0.15s; margin:0; }
        .review-textarea {
            width:100%; padding:12px 16px; border:2px solid #e5ddd0; border-radius:12px;
            font-family:'Oswald',sans-serif; font-size:0.92rem; color:var(--navy-text);
            resize:vertical; min-height:100px; margin-top:0; transition:border-color 0.2s;
        }
        .review-textarea:focus { outline:none; border-color:var(--rustic-orange); }
        .submit-review-btn {
            margin-top:14px; padding:11px 28px; background:var(--rustic-orange); color:white;
            border:none; border-radius:999px; font-family:'Oswald',sans-serif; font-weight:700;
            font-size:0.92rem; cursor:pointer; transition:background 0.2s, transform 0.15s;
        }
        .submit-review-btn:hover { background:var(--olive); transform:translateY(-1px); }

        .login-cta-box {
            background:var(--cream-background); border-radius:14px; padding:22px 24px;
            margin-top:24px; text-align:center; font-family:'Oswald',sans-serif; font-size:0.95rem; color:#888;
        }
        .login-cta-box a { color:var(--rustic-orange); font-weight:700; text-decoration:none; }

        .alert { padding:12px 16px; border-radius:10px; font-family:'Oswald',sans-serif; font-size:0.88rem; margin-bottom:18px; }
        .alert-error   { background:#fde8e8; border-left:4px solid #e53e3e; color:#c53030; }
        .alert-success { background:#e6f4ea; border-left:4px solid #38a169; color:#276749; }

        .back-breadcrumb {
            display:inline-flex; align-items:center; gap:6px; font-family:'Oswald',sans-serif;
            font-size:0.88rem; color:#999; text-decoration:none; margin-bottom:28px; transition:color 0.2s;
        }
        .back-breadcrumb:hover { color:var(--rustic-orange); }
    </style>
</head>
<body>

<nav class="top-nav">
    <a href="homePage.php" class="nav-brand">World <span>Cuisine</span></a>
    <div class="top-nav-buttons">
        <?php if (is_logged_in()): ?>
            <span class="nav-user">Hello, <strong><?= h(current_username()) ?></strong></span>
            <a href="add_recipe.php" class="search-nav-link" style="background:var(--rustic-orange);color:white;border-radius:999px;padding:7px 18px;font-weight:700;text-decoration:none;">+ Add Recipe</a>
            <a href="logout.php" class="search-nav-link">Log Out</a>
        <?php else: ?>
            <a href="signin.php" class="search-nav-link">Sign In</a>
            <a href="signup.php" class="search-nav-link">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<div class="recipe-hero">
    <div class="country-badge"><?= h($recipe['country']) ?></div>
    <h1><?= h($recipe['title']) ?></h1>
    <div class="hero-meta">
        <div class="hero-stars">
            <?= star_html((float)$recipe['avg_rating']) ?>
            <span class="hero-rating-num">
                <?= number_format((float)$recipe['avg_rating'], 1) ?>
                (<?= $recipe['review_count'] ?> <?= $recipe['review_count'] == 1 ? 'review' : 'reviews' ?>)
            </span>
        </div>
        <span class="hero-author">
            by <strong><?= h($recipe['author']) ?></strong>
            &nbsp;·&nbsp; <?= date('F j, Y', strtotime($recipe['created_at'])) ?>
        </span>
    </div>
</div>

<div class="recipe-content">

    <aside>
        <div class="ingredients-card">
            <h2 class="card-heading">Ingredients</h2>
            <ul class="ingredient-list">
                <?php foreach ($ingredients as $ing): ?>
                    <li><?= h($ing) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>

    <div class="main-col">
        <a href="all.php" class="back-breadcrumb">← All Recipes</a>

        <div class="instructions-card">
            <h2 class="card-heading">Instructions</h2>
            <?php $step_n = 0; foreach ($instructions as $step): $step_n++; ?>
                <div class="step">
                    <div class="step-num"><?= $step_n ?></div>
                    <p class="step-text"><?= h(preg_replace('/^\d+\.\s*/', '', $step)) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="reviews-card">
            <div class="reviews-header">
                <h2 class="card-heading" style="margin:0;padding:0;border:none;">
                    Reviews <span style="color:#aaa;font-size:0.85rem;font-family:'Oswald',sans-serif;font-weight:400;">(<?= $recipe['review_count'] ?>)</span>
                </h2>
            </div>

            <?php if ($review_error): ?>
                <div class="alert alert-error">⚠ <?= h(urldecode($review_error)) ?></div>
            <?php endif; ?>
            <?php if ($review_success): ?>
                <div class="alert alert-success">✓ <?= h(urldecode($review_success)) ?></div>
            <?php endif; ?>

            <?php if (empty($reviews)): ?>
                <p style="font-family:'Oswald',sans-serif;color:#bbb;font-size:0.92rem;">No reviews yet. Be the first!</p>
            <?php else: ?>
                <?php foreach ($reviews as $rev): ?>
                <div class="review-item">
                    <div class="review-meta">
                        <span class="reviewer-name"><?= h($rev['username']) ?></span>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="review-stars"><?= star_html((float)$rev['rating']) ?></div>
                            <span class="review-date"><?= date('M j, Y', strtotime($rev['created_at'])) ?></span>
                        </div>
                    </div>
                    <?php if ($rev['comment']): ?>
                        <p class="review-comment"><?= h($rev['comment']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (is_logged_in() && !$user_review): ?>
            <div class="add-review-card">
                <h3>Leave a Review</h3>
                <form action="review_handler.php" method="POST">
                    <input type="hidden" name="recipe_id" value="<?= $id ?>">
                    <div style="margin-bottom:14px;">
                        <label style="font-family:'Oswald',sans-serif;font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--navy-text);display:block;margin-bottom:8px;">Your Rating</label>
                        <div class="star-picker" id="star-picker">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" <?= $i===5?'required':'' ?>>
                                <label for="star<?= $i ?>">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <label style="font-family:'Oswald',sans-serif;font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--navy-text);display:block;margin-bottom:8px;">
                        Comment <span style="color:#bbb;font-weight:400;">(optional)</span>
                    </label>
                    <textarea name="comment" class="review-textarea" placeholder="Share your experience cooking this dish…"></textarea>
                    <button type="submit" class="submit-review-btn">Submit Review</button>
                </form>
            </div>
            <?php elseif (is_logged_in() && $user_review): ?>
            <div class="login-cta-box" style="background:#f0f8f0;">
                ✓ You've already reviewed this recipe.
            </div>
            <?php else: ?>
            <div class="login-cta-box">
                <a href="signin.php">Sign in</a> or <a href="signup.php">sign up</a> to leave a review.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer id="contact">
    <p>Contact Us: phelpsn@jbu.edu<br>(479) 866-2211</p>
</footer>

<script>
const starPicker = document.getElementById('star-picker');
if (starPicker) {
    const labels = [...starPicker.querySelectorAll('label')].reverse();
    const inputs = [...starPicker.querySelectorAll('input')].reverse();
    function highlight(upTo) {
        labels.forEach((lbl, i) => { lbl.style.color = i <= upTo ? '#f59e0b' : '#d4cfc8'; });
    }
    labels.forEach((lbl, i) => {
        lbl.addEventListener('mouseover', () => highlight(i));
        lbl.addEventListener('mouseout', () => { const checked = inputs.findIndex(inp => inp.checked); highlight(checked >= 0 ? checked : -1); });
    });
    inputs.forEach((inp, i) => { inp.addEventListener('change', () => highlight(i)); });
}
</script>
</body>
</html>
