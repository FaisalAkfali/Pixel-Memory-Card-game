<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/classes/User.php';

$user = new User();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Memory | How to Play</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="pixel-bg"></div>

    <div class="dashboard-container">
        <div class="pixel-header">
            <div class="pixel-welcome">PIXEL MEMORY</div>
            <div class="flex">
                <?php if ($user->isLoggedIn()): ?>
                <a href="pages/game.php"><button class="reset-btn">PLAY</button></a>
                <a href="pages/dashboard.php"><button class="reset-btn">DASHBOARD</button></a>
                <?php if ($user->isAdmin()): ?>
                <a href="pages/admin.php"><button class="reset-btn">ADMIN</button></a>
                <?php endif; ?>
                <a href="actions/logout.php"><button class="reset-btn">LOGOUT</button></a>
                <?php else: ?>
                <a href="pages/login.php"><button class="reset-btn">LOGIN</button></a>
                <a href="pages/register.php"><button class="reset-btn">REGISTER</button></a>
                <?php endif; ?>
            </div>
        </div>

        <div class="pixel-stat" style="text-align: left; margin-bottom: 24px;">
            <div class="pixel-title" style="text-align: center; margin-bottom: 8px;">ARCADE MEMORY MATCH</div>
            <p class="pixel-subtitle" style="text-align: center; margin-bottom: 20px;">Flip cards, find pairs, beat the clock — or the AI.</p>
            <p style="color: #c8e6d0; line-height: 1.6; font-size: 0.95rem;">
                Pixel Memory is a retro-styled matching game. Each card hides a pixel-art playing card face.
                Your goal is to turn over two cards at a time: when they show the same card, they stay matched.
                When they differ, they flip back — so remember what you saw. Clear the whole board in as few moves as you can.
            </p>
        </div>

        <div class="pixel-stat" style="text-align: left; margin-bottom: 24px;">
            <div class="pixel-stat-label" style="font-size: 1rem; margin-bottom: 16px;">HOW TO PLAY</div>
            <ol style="color: #c8e6d0; line-height: 1.8; padding-left: 1.25rem; font-size: 0.9rem;">
                <li><strong style="color: #ffcc77;">Log in</strong> — scores can be saved after a full win.</li>
                <li><strong style="color: #ffcc77;">Choose a mode</strong> on the play screen (Classic, Challenge, Helper, or Sabotage).</li>
                <li><strong style="color: #ffcc77;">Memorize</strong> — at the start, all cards show briefly; use that moment to spot pairs.</li>
                <li><strong style="color: #ffcc77;">Click two cards</strong> per try. A match keeps them face-up; a miss flips them down again.</li>
                <li><strong style="color: #ffcc77;">Finish</strong> when every pair is found — then save your score from the game bar.</li>
            </ol>
        </div>

        <div class="pixel-stat" style="text-align: left; margin-bottom: 24px;">
            <div class="pixel-stat-label" style="font-size: 1rem; margin-bottom: 16px;">GAME MODES</div>
            <ul style="color: #c8e6d0; line-height: 1.75; padding-left: 1.25rem; font-size: 0.9rem; list-style: none;">
                <li style="margin-bottom: 10px;"><span style="color: #7eff9a;">CLASSIC</span> — Solo practice. No AI tricks; just you.</li>
                <li style="margin-bottom: 10px;"><span style="color: #7eff9a;">CHALLENGE</span> — After you miss a pair, the AI takes a turn. Whoever matches may go again — same as table rules.</li>
                <li style="margin-bottom: 10px;"><span style="color: #7eff9a;">HELPER</span> — The AI help you when it remembers where your first card’s twin might be.</li>
                <li style="margin-bottom: 10px;"><span style="color: #7eff9a;">SABOTAGE</span> — Every few moves, unmatched cards shuffle — stay sharp.</li>
            </ul>
        </div>

        <div class="pixel-stat" style="text-align: center;">
            <div class="pixel-stat-label" style="font-size: 1rem; margin-bottom: 18px;">READY?</div>
            <?php if ($user->isLoggedIn()): ?>
            <p style="color: #a8d8c0; font-size: 0.9rem; margin-bottom: 20px;">Head to the game and pick a mode, or open your dashboard for stats.</p>
            <div class="flex" style="justify-content: center; flex-wrap: wrap;">
                <a href="pages/game.php"><button class="pixel-btn">PLAY</button></a>
                <a href="pages/dashboard.php"><button class="pixel-btn" style="margin-left: 12px;">DASHBOARD</button></a>
            </div>
            <?php else: ?>
            <p style="color: #a8d8c0; font-size: 0.9rem; margin-bottom: 20px;">Create an account or sign in to play and appear on the leaderboard.</p>
            <div class="flex" style="justify-content: center; flex-wrap: wrap;">
                <a href="pages/register.php"><button class="pixel-btn">REGISTER</button></a>
                <a href="pages/login.php"><button class="pixel-btn" style="margin-left: 12px;">LOGIN</button></a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="pixel-corners"></div>

    <script>
        const bg = document.querySelector('.pixel-bg');
        if (bg) {
            for (let i = 0; i < 40; i++) {
                const p = document.createElement('div');
                p.className = 'pixel-particle';
                p.style.width = Math.random() * 4 + 2 + 'px';
                p.style.height = p.style.width;
                p.style.left = Math.random() * 100 + '%';
                p.style.animationDelay = Math.random() * 8 + 's';
                p.style.animationDuration = Math.random() * 6 + 4 + 's';
                p.style.background = 'hsl(' + (Math.random() * 60 + 100) + ', 70%, 55%)';
                bg.appendChild(p);
            }
        }
    </script>
</body>
</html>
