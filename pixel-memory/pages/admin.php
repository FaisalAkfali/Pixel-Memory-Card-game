<?php
require_once '../includes/config.php';
require_once '../includes/classes/User.php';
require_once '../includes/classes/Game.php';

$user = new User();
$game = new Game();

if (!$user->isLoggedIn() || !$user->isAdmin()) {
    redirect('login.php');
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (isset($_GET['csrf_token']) && verifyCSRFToken($_GET['csrf_token'])) {
        $user->deleteUser($_GET['delete']);
    }
    redirect('admin.php');
}

if (isset($_GET['reset_scores']) && is_numeric($_GET['reset_scores'])) {
    if (isset($_GET['csrf_token']) && verifyCSRFToken($_GET['csrf_token'])) {
        $game->deleteScoresForUser((int) $_GET['reset_scores']);
    }
    redirect('admin.php');
}

$users = $user->getAllUsers();
$scores = $game->getAllScores(30);
$totalUsers = count($users);
$totalGames = $game->getTotalGames();
$avgMoves = $game->getAverageMoves();
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Pixel Memory</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <div class="pixel-bg"></div>
    
    <div class="dashboard-container">
        <div class="pixel-header">
            <div class="pixel-welcome"> ADMIN CONTROL PANEL</div>
            <div class="flex">
                <a href="dashboard.php"><button class="reset-btn"> DASHBOARD</button></a>
                <a href="../actions/logout.php"><button class="reset-btn"> LOGOUT</button></a>
            </div>
        </div>
        
        <div class="admin-stats">
            <div class="pixel-stat"><div class="pixel-stat-value"><?php echo e($totalUsers); ?></div><div class="pixel-stat-label">TOTAL USERS</div></div>
            <div class="pixel-stat"><div class="pixel-stat-value"><?php echo e($totalGames); ?></div><div class="pixel-stat-label">TOTAL GAMES</div></div>
            <div class="pixel-stat"><div class="pixel-stat-value"><?php echo e($avgMoves); ?></div><div class="pixel-stat-label">AVG MOVES</div></div>
        </div>
        
        <div class="pixel-stat">
            <div class="pixel-stat-label" style="font-size: 1rem;"> USER MANAGEMENT</div>
            <table class="pixel-table">
                <thead>
                    <tr><th>ID</th><th>USERNAME</th><th>EMAIL</th><th>ROLE</th><th>JOINED</th><th>ACTION</th></tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td><?php echo e($u['id']); ?></td>
                        <td><?php echo e($u['username']); ?></td>
                        <td><?php echo e($u['email']); ?></td>
                        <td><span style="color: <?php echo $u['role'] === 'admin' ? '#ffcc77' : '#2ecc71'; ?>"><?php echo e(strtoupper($u['role'])); ?></span></td>
                        <td><?php echo e(date('M d, Y', strtotime($u['created_at']))); ?></td>
                        <td>
                            <?php if($u['id'] != $user->getUserId()): ?>
                                <span class="admin-user-actions">
                                    <a href="?delete=<?php echo e($u['id']); ?>&csrf_token=<?php echo e($csrf_token); ?>"
                                       onclick="return confirm('Delete <?php echo e($u['username']); ?>?')"
                                       class="delete-btn">DELETE</a>
                                    <a href="?reset_scores=<?php echo e($u['id']); ?>&csrf_token=<?php echo e($csrf_token); ?>"
                                       onclick="return confirm('Reset all saved scores for <?php echo e($u['username']); ?>? This cannot be undone.')"
                                       class="reset-scores-btn">RESET SCORES</a>
                                </span>
                            <?php else: ?>
                                <span style="color:#666;">YOU</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="pixel-stat" style="margin-top: 30px;">
            <div class="pixel-stat-label" style="font-size: 1rem;">🎮 RECENT GAMES</div>
            <table class="pixel-table">
                <thead>
                    <tr><th>PLAYER</th><th>MOVES</th><th>PAIRS</th><th>MODE</th><th>DATE</th></tr>
                </thead>
                <tbody>
                    <?php foreach($scores as $score): ?>
                    <tr>
                        <td><?php echo e($score['username']); ?></td>
                        <td><?php echo e($score['moves']); ?></td>
                        <td><?php echo e($score['matched_pairs']); ?>/12</td>
                        <td><?php echo e(ucfirst($score['game_mode'])); ?></td>
                        <td><?php echo e(date('M d, H:i', strtotime($score['played_at']))); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="pixel-corners"></div>
    
    <script>
        $(document).ready(function() {
            $('.delete-btn').hover(
                function() { $(this).css('background', '#cc0000'); },
                function() { $(this).css('background', '#8b0000'); }
            );
            $('.reset-scores-btn').hover(
                function() { $(this).css('background', '#c9a227'); },
                function() { $(this).css('background', '#8b6914'); }
            );
        });
    </script>
    
    <script>
        const bg = document.querySelector('.pixel-bg');
        for(let i = 0; i < 50; i++) {
            const p = document.createElement('div');
            p.className = 'pixel-particle';
            p.style.width = Math.random() * 4 + 2 + 'px';
            p.style.height = p.style.width;
            p.style.left = Math.random() * 100 + '%';
            p.style.animationDelay = Math.random() * 8 + 's';
            p.style.animationDuration = Math.random() * 6 + 4 + 's';
            p.style.background = `hsl(${Math.random() * 60 + 100}, 70%, 55%)`;
            bg.appendChild(p);
        }
    </script>
</body>
</html>