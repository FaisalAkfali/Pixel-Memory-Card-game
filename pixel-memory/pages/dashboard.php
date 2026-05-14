<?php
require_once '../includes/config.php';
require_once '../includes/classes/User.php';
require_once '../includes/classes/Game.php';

$user = new User();
$game = new Game();

if (!$user->isLoggedIn()) {
    redirect('login.php');
}

$userId = $user->getUserId();
$username = $user->getUsername();

$stats = $game->getUserStats($userId);
$recentScores = $game->getUserRecentScores($userId, 5);
$leaderboard = $game->getLeaderboard(5);

$bestScore = $stats['best_score'] ?? '--';
$totalGames = $stats['total_games'] ?? 0;
$avgMoves = $stats['avg_moves'] ? round($stats['avg_moves']) : '--';
$completedGames = $stats['completed_games'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Pixel Memory</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <div class="pixel-bg"></div>
    
    <div class="dashboard-container">
        <div class="pixel-header">
            <div class="pixel-welcome">WELCOME, <?php echo e(strtoupper($username)); ?>!</div>
            <div class="flex">
                <a href="../index.php"><button class="reset-btn">HOME</button></a>
                <a href="game.php"><button class="reset-btn">PLAY</button></a>
                <?php if($user->isAdmin()): ?>
                    <a href="admin.php"><button class="reset-btn">ADMIN</button></a>
                <?php endif; ?>
                <a href="../actions/logout.php"><button class="reset-btn">LOGOUT</button></a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="pixel-stat">
                <div class="pixel-stat-value"><?php echo e($bestScore); ?></div>
                <div class="pixel-stat-label">BEST SCORE</div>
            </div>
            <div class="pixel-stat">
                <div class="pixel-stat-value"><?php echo e($totalGames); ?></div>
                <div class="pixel-stat-label">GAMES PLAYED</div>
            </div>
            <div class="pixel-stat">
                <div class="pixel-stat-value"><?php echo e($completedGames); ?></div>
                <div class="pixel-stat-label">COMPLETED</div>
            </div>
            <div class="pixel-stat">
                <div class="pixel-stat-value"><?php echo e($avgMoves); ?></div>
                <div class="pixel-stat-label">AVG MOVES</div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div class="pixel-stat">
                <div class="pixel-stat-label" style="font-size: 1rem;">RECENT GAMES</div>
                <?php if(empty($recentScores)): ?>
                    <p style="margin-top: 20px;">No games yet. <a href="game.php">Play now!</a></p>
                <?php else: ?>
                    <table class="pixel-table" style="margin-top: 15px;">
                        <tr><th>MOVES</th><th>MODE</th><th>DATE</th></tr>
                        <?php foreach($recentScores as $score): ?>
                        <tr>
                            <td><?php echo e($score['moves']); ?></td>
                            <td><?php echo e(ucfirst($score['game_mode'])); ?></td>
                            <td><?php echo e(date('M d', strtotime($score['played_at']))); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>
            
            <div class="pixel-stat">
                <div class="pixel-stat-label" style="font-size: 1rem;">LEADERBOARD</div>
                <div id="leaderboard-container">
                    <table class="pixel-table" style="margin-top: 15px;">
                        <thead>
                            <tr><th>#</th><th>PLAYER</th><th>BEST</th><th>GAMES</th></tr>
                        </thead>
                        <tbody id="leaderboard-body">
                            <?php $rank = 1; foreach($leaderboard as $player): ?>
                            <tr>
                                <td><?php echo e($rank++); ?></td>
                                <td><?php echo e($player['username']); ?></td>
                                <td><?php echo e($player['best_score']); ?></td>
                                <td><?php echo e($player['games_played']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="pixel-corners"></div>
    
    <script>
        function refreshLeaderboard() {
            $.ajax({
                url: '../ajax-get-leaderboard.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    var html = '<table class="pixel-table" style="margin-top: 15px;">';
                    html += '<thead><tr><th>#</th><th>PLAYER</th><th>BEST</th><th>GAMES</th></tr></thead><tbody>';
                    $.each(data, function(index, player) {
                        html += '<tr>';
                        html += '<td>' + (index + 1) + '</td>';
                        html += '<td>' + player.username + '</td>';
                        html += '<td>' + player.best_score + '</td>';
                        html += '<td>' + player.games_played + '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                    $('#leaderboard-container').html(html);
                }
            });
        }
        
        setInterval(refreshLeaderboard, 30000);
        
        $(document).ready(function() {
            $('.pixel-stat').hover(
                function() { $(this).css('transform', 'translateY(-5px)'); },
                function() { $(this).css('transform', 'translateY(0)'); }
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