<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>World Cuisine's Recipe Search</title>
    <link rel="icon" type="image/png" href="worldcuisinefavicon.png">
    <link rel="stylesheet" href="finalProjectStyles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Source+Serif+4:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        header h1 {
            font-family: 'Oswald', sans-serif;
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        .search-nav-link { font-family: 'Oswald', sans-serif; letter-spacing: 0.04em; }
        .search-input { font-family: 'Oswald', sans-serif; }
        .nav-user {
            font-family: 'Oswald', sans-serif;
            font-size: 0.95rem;
            color: var(--navy-text);
            background: white;
            padding: 6px 16px;
            border-radius: 999px;
            letter-spacing: 0.04em;
        }
        .nav-user strong { color: var(--rustic-orange); }
        .top-nav { background: transparent; }
    </style>
</head>
<body>

<nav class="top-nav">
    <div class="top-nav-buttons">
        <?php if (is_logged_in()): ?>
            <span class="nav-user"><strong><?= h(current_username()) ?></strong></span>
            <a href="add_recipe.php" class="search-nav-link" style="background:var(--rustic-orange);color:white;font-weight:700;">+ Add Recipe</a>
            <a href="logout.php" class="search-nav-link">Log Out</a>
        <?php else: ?>
            <a href="signin.php" class="search-nav-link">Sign In</a>
            <a href="signup.php" class="search-nav-link">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<header id="top">
    <h1>Explore Authentic World Cuisines.</h1>
    <button id="photoButton">
        <img src="downarrow-removebg.png" alt="Arrow Button" width="52" height="52" id="scroll-btn">
    </button>
</header>

<section id="search-section">
    <div class="search-image-wrapper">
        <img src="food-world-map.jpg" alt="World Cuisine Food Map" class="search-image">
    </div>
    <form action="all.php" method="GET">
        <div class="search-container">
            <input type="text" name="q" class="search-input"
                   placeholder="Search recipes by name or country…"
                   aria-label="Search recipes" autocomplete="off">
            <button type="submit" class="search-button" aria-label="Search"></button>
        </div>
    </form>
    <nav class="search-nav">
        <a href="worldmapsearch.php" class="search-nav-link">Continent Search</a>
        <a href="all.php"  class="search-nav-link">View All</a>
    </nav>
</section>

<footer id="contact">
    <p>Contact Us: phelpsn@jbu.edu // westc@jbu.edu<br>(479) 866-2211 // (913)-201-8993</p>
</footer>

</body>
</html>
<script>
document.getElementById("scroll-btn").addEventListener("click", () => {
    document.getElementById("search-section").scrollIntoView({ behavior: "smooth", block: "center" });
});
</script>
