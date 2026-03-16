-- World Cuisine Recipe Search - Database Setup
-- Run this script once to initialize your MySQL database

CREATE DATABASE IF NOT EXISTS world_cuisine;
USE world_cuisine;

-- =====================
-- USERS TABLE
-- =====================
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          -- bcrypt hashed
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP
);

-- =====================
-- RECIPES TABLE
-- =====================
CREATE TABLE IF NOT EXISTS recipes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT          NOT NULL,
    title           VARCHAR(150) NOT NULL,
    country         VARCHAR(100) NOT NULL,
    ingredients     TEXT         NOT NULL,       -- JSON array or newline-delimited
    instructions    TEXT         NOT NULL,
    image_url       VARCHAR(255) DEFAULT NULL,
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================
-- REVIEWS TABLE
-- =====================
CREATE TABLE IF NOT EXISTS reviews (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id   INT  NOT NULL,
    user_id     INT  NOT NULL,
    rating      TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment     TEXT DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY one_review_per_user (recipe_id, user_id),   -- one review per user per recipe
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
);

-- =====================
-- HELPFUL VIEW: recipes with avg rating + review count
-- =====================
CREATE OR REPLACE VIEW recipes_with_stats AS
SELECT
    r.*,
    u.username                          AS author,
    COALESCE(AVG(rv.rating), 0)         AS avg_rating,
    COUNT(rv.id)                        AS review_count
FROM  recipes r
JOIN  users   u  ON u.id = r.user_id
LEFT  JOIN reviews rv ON rv.recipe_id = r.id
GROUP BY r.id, u.username;

-- =====================
-- SAMPLE DATA (optional - remove before production)
-- =====================

-- Demo user: password is "password123" (bcrypt hashed)
INSERT IGNORE INTO users (username, email, password) VALUES
('demo_chef', 'demo@worldcuisine.com', '$2y$12$KIqSRyBmCVBl5L6s0eGBGu3pHxVDFMFlHdIVjg1yq9mE2eC8QwKdC');

-- Sample recipes (user_id = 1 = demo_chef)
INSERT IGNORE INTO recipes (user_id, title, country, ingredients, instructions) VALUES
(1, 'Spaghetti Carbonara', 'Italy',
 '200g spaghetti\n150g guanciale or pancetta\n3 large eggs\n100g Pecorino Romano\nFreshly ground black pepper\nSalt',
 '1. Cook spaghetti in salted boiling water until al dente.\n2. Fry guanciale in a pan until crispy, no oil needed.\n3. Whisk eggs with grated Pecorino and black pepper.\n4. Reserve 1 cup pasta water, then drain pasta.\n5. Remove pan from heat, add pasta, toss with guanciale.\n6. Add egg mixture, toss quickly, adding pasta water gradually until creamy.\n7. Serve immediately topped with more Pecorino.'),

(1, 'Chicken Tikka Masala', 'India',
 '600g chicken breast (cubed)\n200ml plain yogurt\n400ml coconut cream\n1 can crushed tomatoes\n2 tbsp tikka masala paste\n1 onion (diced)\n3 garlic cloves\n1 tsp garam masala\nFresh coriander\nSalt & pepper',
 '1. Marinate chicken in yogurt and 1 tbsp tikka paste for 30 mins.\n2. Grill or pan-fry chicken until charred; set aside.\n3. Sauté onion and garlic in oil until soft.\n4. Add remaining tikka paste and cook 2 minutes.\n5. Pour in crushed tomatoes and coconut cream; simmer 10 minutes.\n6. Add chicken back in; simmer 10 more minutes.\n7. Season and garnish with fresh coriander. Serve with naan.'),

(1, 'Beef Tacos', 'Mexico',
 '500g ground beef\n8 small corn tortillas\n1 onion (diced)\n2 garlic cloves\n1 tbsp cumin\n1 tbsp chili powder\nSalt & pepper\nLime juice\nFresh cilantro\nPico de gallo\nShredded cheese',
 '1. Sauté onion and garlic in oil until translucent.\n2. Add ground beef; cook until browned, breaking it up.\n3. Season with cumin, chili powder, salt, and pepper.\n4. Warm tortillas on a dry skillet.\n5. Fill tortillas with beef.\n6. Top with pico de gallo, cheese, and cilantro.\n7. Squeeze fresh lime juice on top and serve.'),

(1, 'Pad Thai', 'Thailand',
 '200g rice noodles\n200g shrimp or chicken\n2 eggs\n3 tbsp fish sauce\n2 tbsp tamarind paste\n1 tbsp palm sugar\n2 spring onions\nBean sprouts\nCrushed peanuts\nLime wedges\n2 garlic cloves\nVegetable oil',
 '1. Soak rice noodles in warm water for 20 minutes; drain.\n2. Mix fish sauce, tamarind paste, and palm sugar into sauce.\n3. Stir-fry garlic in hot wok, add protein and cook through.\n4. Push to side, scramble eggs in center.\n5. Add noodles and sauce; toss everything together.\n6. Add bean sprouts and spring onions; stir-fry 1 minute.\n7. Serve topped with peanuts, extra lime, and chili flakes.'),

(1, 'Croissants', 'France',
 '500g all-purpose flour\n10g salt\n80g sugar\n10g instant yeast\n300ml warm milk\n250g cold unsalted butter (for lamination)\n1 egg (for egg wash)',
 '1. Mix flour, salt, sugar, yeast, and milk into a dough; knead 5 minutes.\n2. Chill dough 30 minutes.\n3. Roll out cold butter between parchment into a rectangle.\n4. Encase butter in dough; fold and roll 3 times (book fold), chilling 30 min between each.\n5. Roll out and cut triangles; roll from base to tip.\n6. Arrange on tray, prove 2 hours until puffy.\n7. Brush with egg wash; bake at 200°C for 18-20 minutes until deep golden.'),

(1, 'Peking Duck', 'China',
 '1 whole duck (about 2kg)\n2 tbsp honey\n1 tbsp soy sauce\n1 tsp Chinese five-spice\n2 tbsp hoisin sauce\nThin pancakes\nSpring onions\nCucumber strips',
 '1. Score duck skin and rub inside with five-spice.\n2. Mix honey and soy; brush all over duck.\n3. Hang or place on rack uncovered in fridge overnight to dry skin.\n4. Roast at 220°C for 30 min, then 180°C for 1 hour.\n5. Rest 15 minutes before carving.\n6. Slice skin and meat separately.\n7. Serve wrapped in pancakes with hoisin, spring onion, and cucumber.'),

(1, 'Shakshuka', 'Israel / Middle East',
 '6 eggs\n1 can crushed tomatoes\n1 red pepper (diced)\n1 onion (diced)\n3 garlic cloves\n1 tsp cumin\n1 tsp smoked paprika\n½ tsp chili flakes\nFresh parsley\nFeta cheese\nOlive oil\nSalt & pepper',
 '1. Sauté onion and red pepper in olive oil until soft.\n2. Add garlic, cumin, paprika, and chili; cook 2 minutes.\n3. Pour in crushed tomatoes; season and simmer 10 minutes.\n4. Make 6 wells in the sauce; crack an egg into each.\n5. Cover and cook until whites are set but yolks are still runny (5-7 min).\n6. Crumble feta on top and garnish with parsley.\n7. Serve directly from the pan with crusty bread.'),

(1, 'Borscht', 'Ukraine',
 '3 medium beets (peeled & grated)\n2 carrots (grated)\n1 onion (diced)\n2 potatoes (cubed)\n¼ cabbage (shredded)\n1L beef or vegetable broth\n2 tbsp tomato paste\n2 tbsp red wine vinegar\n3 garlic cloves\nFresh dill\nSour cream\nSalt & pepper',
 '1. Sauté onion, carrots, and beets in oil for 10 minutes.\n2. Add tomato paste; cook 3 more minutes.\n3. Pour in broth; bring to boil.\n4. Add potatoes and cabbage; cook 20 minutes until tender.\n5. Stir in vinegar and minced garlic; season generously.\n6. Simmer 5 more minutes.\n7. Serve topped with a dollop of sour cream and fresh dill.');

-- Sample reviews
INSERT IGNORE INTO reviews (recipe_id, user_id, rating, comment) VALUES
(1, 1, 5, 'Absolutely authentic! Perfectly creamy without any cream.'),
(2, 1, 4, 'Rich and flavorful. The coconut cream was a great touch.'),
(3, 1, 5, 'Quick and delicious weeknight meal!'),
(4, 1, 4, 'Tasted just like the street food in Bangkok.'),
(5, 1, 5, 'Worth every minute of effort. Bakery-quality at home.'),
(6, 1, 5, 'Incredible crispy skin. The pancake wraps made it feel so special.'),
(7, 1, 5, 'My new brunch staple. So easy and impressive.'),
(8, 1, 4, 'Deep, hearty, and beautiful color. Comfort food at its finest.');
