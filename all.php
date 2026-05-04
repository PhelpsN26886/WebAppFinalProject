<?php
// all.php – Browse all recipes with sorting & pagination
require_once 'config.php';

$allowed_sorts = [
    'recent'    => ['label' => 'Most Recent',  'order' => 'r.created_at DESC'],
    'popular'   => ['label' => 'Most Popular', 'order' => 'review_count DESC, avg_rating DESC'],
    'top_rated' => ['label' => 'Top Rated',    'order' => 'avg_rating DESC, review_count DESC'],
    'az'        => ['label' => 'A → Z',        'order' => 'r.title ASC'],
];

$sort     = array_key_exists($_GET['sort'] ?? '', $allowed_sorts) ? $_GET['sort'] : 'recent';
$country  = trim($_GET['country'] ?? '');
$search   = trim($_GET['q'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset   = ($page - 1) * $per_page;
$order    = $allowed_sorts[$sort]['order'];

$pdo = get_db();

$where  = [];
$params = [];
if ($country !== '') { $where[] = 'r.country = ?';                    $params[] = $country; }
if ($search  !== '') { $where[] = '(r.title LIKE ? OR r.country LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM recipes r $where_sql");
$count_stmt->execute($params);
$total       = (int)$count_stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total / $per_page));
$page        = min($page, $total_pages);
$offset      = ($page - 1) * $per_page;

$stmt = $pdo->prepare("
    SELECT r.id, r.title, r.country, r.created_at,
           u.username AS author,
           COALESCE(AVG(rv.rating), 0) AS avg_rating,
           COUNT(rv.id) AS review_count
    FROM   recipes r
    JOIN   users   u  ON u.id = r.user_id
    LEFT   JOIN reviews rv ON rv.recipe_id = r.id
    $where_sql
    GROUP  BY r.id, u.username
    ORDER  BY $order
    LIMIT  $per_page OFFSET $offset
");
$stmt->execute($params);
$recipes = $stmt->fetchAll();

$countries = $pdo->query('SELECT DISTINCT country FROM recipes ORDER BY country ASC')->fetchAll(PDO::FETCH_COLUMN);

function star_html(float $rating): string {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i)           $html .= '<span class="star filled">★</span>';
        elseif ($rating >= $i - 0.5) $html .= '<span class="star half">★</span>';
        else                         $html .= '<span class="star empty">☆</span>';
    }
    return $html;
}

function page_url(array $overrides = []): string {
    $params = array_merge([
        'sort'    => $_GET['sort']    ?? 'recent',
        'country' => $_GET['country'] ?? '',
        'q'       => $_GET['q']       ?? '',
        'page'    => $_GET['page']    ?? 1,
    ], $overrides);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return 'all.php?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Recipes – World Cuisine</title>
    <link rel="icon" type="image/png" href="worldcuisinefavicon.png">
    <link rel="stylesheet" href="finalProjectStyles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Source+Serif+4:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        body { background-color: var(--cream-background); align-items: stretch; min-height: 100vh; }

        /* ── Top Nav ── */
        .top-nav {
            position: sticky; top: 0;
            background: var(--navy-text);
            z-index: 100;
            padding: 14px 32px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .nav-brand { font-family:'Source Serif 4',Georgia,serif; font-size:1.3rem; color:var(--cream-accent); text-decoration:none; }
        .nav-brand span { color: var(--rustic-orange); }
        .top-nav-buttons { display:flex; align-items:center; gap:12px; }
        .nav-user { font-family:'Oswald',sans-serif; font-size:0.9rem; color:var(--cream-accent); }
        .nav-user strong { color: var(--rustic-orange); }
        .top-nav .search-nav-link { padding:7px 18px; font-size:0.85rem; font-family:'Oswald',sans-serif; }
        .nav-add-btn {
            background: var(--rustic-orange); color: white; text-decoration: none;
            padding: 8px 20px; border-radius: 999px; font-family:'Oswald',sans-serif;
            font-size:0.85rem; font-weight:700; transition: background 0.2s, transform 0.15s;
        }
        .nav-add-btn:hover { background: var(--olive); transform: translateY(-1px); }

        /* ── Page Hero ── */
        .page-hero { background: var(--navy-text); color: var(--cream-accent); padding: 48px 32px 40px; text-align: center; }
        .page-hero h1 { font-family:'Oswald',sans-serif; font-size:clamp(2rem,5vw,3rem); font-weight:700; text-transform:uppercase; letter-spacing:0.03em; margin:0 0 8px; }
        .page-hero p  { font-family:'Oswald',sans-serif; color:#aab; margin:0; font-size:1rem; }

        /* ── Controls Bar ── */
        .controls-bar {
            background: white; border-bottom: 1px solid #e5ddd0;
            padding: 18px 32px; display:flex; flex-wrap:wrap; gap:14px;
            align-items:center; justify-content:space-between;
            position:sticky; top:52px; z-index:90;
        }
        .controls-left { display:flex; gap:12px; flex-wrap:wrap; align-items:center; flex:1; }
        .search-bar-wrap {
            display:flex; align-items:center; background:var(--cream-background);
            border-radius:999px; padding:8px 16px; gap:8px;
            border:2px solid #e5ddd0; transition:border-color 0.2s;
        }
        .search-bar-wrap:focus-within { border-color: var(--rustic-orange); }
        .search-bar-wrap input {
            border:none; outline:none; background:transparent;
            font-family:'Oswald',sans-serif; font-size:0.92rem; color:var(--navy-text);
            min-width:220px; margin:0; padding:0;
        }
        .search-bar-wrap input::placeholder { color:#bbb; }
        .controls-select {
            padding:8px 14px; border:2px solid #e5ddd0; border-radius:999px;
            background:var(--cream-background); font-family:'Oswald',sans-serif;
            font-size:0.88rem; color:var(--navy-text); cursor:pointer; outline:none; transition:border-color 0.2s;
        }
        .controls-select:focus { border-color: var(--rustic-orange); }
        .sort-tabs { display:flex; gap:8px; flex-wrap:wrap; }
        .sort-tab {
            font-family:'Oswald',sans-serif; font-size:0.82rem; font-weight:700;
            padding:7px 16px; border-radius:999px; text-decoration:none;
            color:var(--navy-text); background:var(--cream-background);
            border:2px solid #e5ddd0; transition:all 0.2s; white-space:nowrap;
        }
        .sort-tab:hover, .sort-tab.active { background:var(--navy-text); border-color:var(--navy-text); color:var(--cream-accent); }
        .result-count { font-family:'Oswald',sans-serif; font-size:0.85rem; color:#999; white-space:nowrap; }

        /* ── Main Content ── */
        .page-content { max-width:1300px; margin:0 auto; padding:36px 24px 60px; }

        /* ── Recipe Grid ── */
        .recipe-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:24px; }
        @media (min-width:1000px) { .recipe-grid { grid-template-columns:repeat(3,1fr); } }
        @media (max-width:600px)  { .recipe-grid { grid-template-columns:1fr; } }

        /* ── Recipe Card ── */
        .recipe-card {
            background:white; border-radius:18px; overflow:hidden;
            box-shadow:0 2px 12px rgba(0,0,0,0.07);
            transition:transform 0.22s ease, box-shadow 0.22s ease;
            display:flex; flex-direction:column; text-decoration:none; color:inherit;
        }
        .recipe-card:hover { transform:translateY(-5px); box-shadow:0 12px 32px rgba(0,0,0,0.14); }
        .card-flag {
            background:linear-gradient(135deg,var(--rustic-orange),var(--olive));
            padding:28px 20px 20px; min-height:90px; display:flex; align-items:flex-end;
        }
        .card-country-badge {
            background:rgba(255,255,255,0.2); backdrop-filter:blur(8px);
            color:white; padding:4px 12px; border-radius:999px;
            font-family:'Oswald',sans-serif; font-size:0.75rem; font-weight:700;
            letter-spacing:0.05em; text-transform:uppercase;
        }
        .card-body { padding:18px 20px; flex:1; display:flex; flex-direction:column; }
        .card-title { font-family:'Source Serif 4',Georgia,serif; font-size:1.15rem; color:var(--navy-text); margin:0 0 10px; line-height:1.3; }
        .card-stars { display:flex; align-items:center; gap:6px; margin-bottom:10px; }
        .star { font-size:1rem; }
        .star.filled { color:#f59e0b; }
        .star.half   { color:#fcd34d; }
        .star.empty  { color:#d4cfc8; }
        .card-rating-text { font-family:'Oswald',sans-serif; font-size:0.8rem; color:#999; }
        .card-meta { margin-top:auto; padding-top:12px; border-top:1px solid #f0ebe2; display:flex; justify-content:space-between; align-items:center; }
        .card-author { font-family:'Oswald',sans-serif; font-size:0.8rem; color:#aaa; }
        .card-author strong { color:var(--olive); }
        .card-date { font-family:'Oswald',sans-serif; font-size:0.75rem; color:#ccc; }

        /* ── Empty State ── */
        .empty-state { text-align:center; padding:80px 20px; color:#bbb; }
        .empty-state .empty-icon { font-size:3.5rem; margin-bottom:16px; }
        .empty-state h3 { font-family:'Source Serif 4',Georgia,serif; font-size:1.5rem; color:#ccc; margin:0 0 8px; }
        .empty-state p  { font-family:'Oswald',sans-serif; font-size:0.95rem; }

        /* ── Pagination ── */
        .pagination { display:flex; justify-content:center; align-items:center; gap:8px; margin-top:48px; flex-wrap:wrap; }
        .pg-btn {
            display:inline-flex; align-items:center; justify-content:center;
            width:40px; height:40px; border-radius:10px;
            font-family:'Oswald',sans-serif; font-size:0.9rem; font-weight:700;
            text-decoration:none; color:var(--navy-text); background:white;
            border:2px solid #e5ddd0; transition:all 0.18s;
        }
        .pg-btn:hover { background:var(--rustic-orange); color:white; border-color:var(--rustic-orange); }
        .pg-btn.current { background:var(--navy-text); color:white; border-color:var(--navy-text); pointer-events:none; }
        .pg-btn.disabled { opacity:0.35; pointer-events:none; }
        .pg-btn.wide { width:auto; padding:0 16px; }

        /* ── CTA Banner ── */
        .cta-banner {
            background:linear-gradient(135deg,var(--navy-text),#2d3a5a);
            color:var(--cream-accent); border-radius:18px; padding:32px 36px;
            margin-bottom:36px; display:flex; align-items:center;
            justify-content:space-between; gap:20px; flex-wrap:wrap;
        }
        .cta-banner-text h3 { font-family:'Source Serif 4',Georgia,serif; font-size:1.3rem; margin:0 0 4px; }
        .cta-banner-text p  { font-family:'Oswald',sans-serif; font-size:0.9rem; color:#aab; margin:0; }
        .cta-btn {
            background:var(--rustic-orange); color:white; text-decoration:none;
            padding:12px 28px; border-radius:999px; font-family:'Oswald',sans-serif;
            font-weight:700; font-size:0.95rem; white-space:nowrap;
            transition:background 0.2s, transform 0.15s;
            box-shadow:0 4px 14px rgba(214,90,31,0.4);
        }
        .cta-btn:hover { background:var(--olive); transform:translateY(-1px); }
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
    <h1>Explore All Recipes</h1>
    <p><?= number_format($total) ?> authentic world recipes waiting for you</p>
</div>

<div class="controls-bar">
    <div class="controls-left">
        <form method="GET" action="all.php" style="display:contents">
            <input type="hidden" name="sort"    value="<?= h($sort) ?>">
            <input type="hidden" name="country" value="<?= h($country) ?>">
            <div class="search-bar-wrap">
                <input type="text" name="q" value="<?= h($search) ?>" placeholder="Search recipes or countries…">
            </div>
        </form>
        <form method="GET" action="all.php" id="country-form" style="display:contents">
            <input type="hidden" name="sort" value="<?= h($sort) ?>">
            <input type="hidden" name="q"    value="<?= h($search) ?>">
            <input type="hidden" name="page" value="1">
            <select name="country" class="controls-select" onchange="document.getElementById('country-form').submit()">
                <option value="">All Countries</option>
                <?php foreach ($countries as $c): ?>
                    <option value="<?= h($c) ?>" <?= $country === $c ? 'selected' : '' ?>><?= h($c) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="sort-tabs">
        <?php foreach ($allowed_sorts as $key => $info): ?>
            <a href="<?= page_url(['sort' => $key, 'page' => 1]) ?>"
               class="sort-tab <?= $sort === $key ? 'active' : '' ?>">
                <?= h($info['label']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <span class="result-count">
        <?php
            $from = $total > 0 ? min($offset + 1, $total) : 0;
            $to   = min($offset + $per_page, $total);
            echo $from . ' - ' . $to . ' of ' . number_format($total);
        ?>
    </span>
</div>

<div class="page-content">

    <?php if (!is_logged_in()): ?>
    <div class="cta-banner">
        <div class="cta-banner-text">
            <h3>Share Your Family's Recipes</h3>
            <p>Sign up for free to add recipes, leave reviews, and connect with food lovers worldwide.</p>
        </div>
        <a href="signup.php" class="cta-btn">Get Started Free →</a>
    </div>
    <?php endif; ?>

    <?php if (empty($recipes)): ?>
        <div class="empty-state">
            <div class="empty-icon">🍽️</div>
            <h3>No recipes found</h3>
            <p>Try adjusting your search or filter, or be the first to add one!</p>
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

    <?php if ($total_pages > 1): ?>
    <nav class="pagination">
        <a href="<?= page_url(['page' => $page - 1]) ?>" class="pg-btn wide <?= $page <= 1 ? 'disabled' : '' ?>">← Prev</a>
        <?php
        $window = 2; $start = max(1, $page - $window); $end = min($total_pages, $page + $window);
        if ($start > 1): ?><a href="<?= page_url(['page' => 1]) ?>" class="pg-btn">1</a><?php
            if ($start > 2): ?><span class="pg-btn" style="border:none;background:none">…</span><?php endif;
        endif;
        for ($i = $start; $i <= $end; $i++): ?>
            <a href="<?= page_url(['page' => $i]) ?>" class="pg-btn <?= $i === $page ? 'current' : '' ?>"><?= $i ?></a>
        <?php endfor;
        if ($end < $total_pages):
            if ($end < $total_pages - 1): ?><span class="pg-btn" style="border:none;background:none">…</span><?php endif; ?>
            <a href="<?= page_url(['page' => $total_pages]) ?>" class="pg-btn"><?= $total_pages ?></a>
        <?php endif; ?>
        <a href="<?= page_url(['page' => $page + 1]) ?>" class="pg-btn wide <?= $page >= $total_pages ? 'disabled' : '' ?>">Next →</a>
    </nav>
    <?php endif; ?>

</div>

<footer id="contact">
    <p>Contact Us: phelpsn@jbu.edu // westc@jbu.edu<br>(479) 866-2211 // (913)-201-8993</p>
</footer>

</body>
</html>
