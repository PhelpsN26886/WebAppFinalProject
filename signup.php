<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up – World Cuisine</title>
    <link rel="stylesheet" href="finalProjectStyles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Source+Serif+4:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--rustic-orange) 0%, var(--light-rustic-orange) 50%, #c94a10 100%);
            justify-content: center;
            padding: 40px 20px;
        }
        .auth-card {
            background: var(--cream-accent);
            border-radius: 24px;
            padding: 48px 44px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.25);
            animation: slideUp 0.5s ease forwards;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .auth-logo { text-align: center; margin-bottom: 32px; }
        .auth-logo h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 1.9rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--rustic-orange);
            margin: 0;
        }
        .auth-logo p {
            font-family: 'Oswald', sans-serif;
            font-size: 0.9rem;
            color: #888;
            margin: 6px 0 0;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .error-banner {
            background: #fde8e8;
            border-left: 4px solid #e53e3e;
            color: #c53030;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.88rem;
            margin-bottom: 20px;
            font-family: 'Oswald', sans-serif;
        }
        .auth-card form { gap: 0; }
        .field-group { margin-bottom: 18px; }
        .field-group label {
            display: block;
            font-family: 'Oswald', sans-serif;
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--navy-text);
            margin-bottom: 7px;
            margin-top: 0;
        }
        .field-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e8dfc9;
            border-radius: 10px;
            font-family: 'Oswald', sans-serif;
            font-size: 0.97rem;
            color: var(--navy-text);
            background: white;
            transition: border-color 0.2s, box-shadow 0.2s;
            margin-top: 0;
        }
        .field-group input:focus {
            outline: none;
            border-color: var(--rustic-orange);
            box-shadow: 0 0 0 3px rgba(214, 90, 31, 0.15);
        }
        .submit-btn {
            width: 100%;
            padding: 14px;
            background: var(--rustic-orange);
            color: white;
            border: none;
            border-radius: 999px;
            font-family: 'Oswald', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 14px rgba(214, 90, 31, 0.35);
        }
        .submit-btn:hover {
            background: var(--olive);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(122, 127, 42, 0.35);
        }
        .auth-footer { text-align: center; margin-top: 24px; font-family: 'Oswald', sans-serif; font-size: 0.9rem; color: #777; }
        .auth-footer a { color: var(--rustic-orange); font-weight: 700; text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }
        .back-link { display: block; text-align: center; margin-top: 16px; font-family: 'Oswald', sans-serif; font-size: 0.85rem; color: #999; text-decoration: none; }
        .back-link:hover { color: var(--rustic-orange); }
        .divider { display: flex; align-items: center; gap: 12px; margin: 24px 0 20px; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e8dfc9; }
        .divider span { font-family: 'Oswald', sans-serif; font-size: 0.8rem; color: #bbb; white-space: nowrap; }
        .password-hint { font-family: 'Oswald', sans-serif; font-size: 0.77rem; color: #aaa; margin-top: 5px; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="auth-logo">
        <h1>World Cuisine</h1>
        <p>Create your account</p>
    </div>

    <?php if (!empty($_GET['error'])): ?>
    <div class="error-banner">⚠ <?= htmlspecialchars(urldecode($_GET['error']), ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form action="signup_handler.php" method="POST">
        <div class="field-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   minlength="3" maxlength="50" required autocomplete="username">
        </div>
        <div class="field-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email"
                   required autocomplete="email">
        </div>
        <div class="field-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   minlength="8" required autocomplete="new-password">
            <p class="password-hint">Minimum 8 characters</p>
        </div>
        <div class="field-group">
            <label for="confirm">Confirm Password</label>
            <input type="password" id="confirm" name="confirm"
                   minlength="8" required autocomplete="new-password">
        </div>
        <button type="submit" class="submit-btn">Create Account</button>
    </form>

    <div class="divider"><span>already have an account?</span></div>
    <div class="auth-footer"><a href="signin.php">Sign in here →</a></div>
    <a href="homePage.php" class="back-link">← Back to home</a>
</div>

<script>
    const pwd = document.getElementById('password');
    const confirm = document.getElementById('confirm');
    const form = document.querySelector('form');
    form.addEventListener('submit', (e) => {
        if (pwd.value !== confirm.value) {
            e.preventDefault();
            confirm.setCustomValidity('Passwords do not match.');
            confirm.reportValidity();
        } else {
            confirm.setCustomValidity('');
        }
    });
    confirm.addEventListener('input', () => {
        confirm.setCustomValidity(pwd.value !== confirm.value ? 'Passwords do not match.' : '');
    });
</script>
</body>
</html>
