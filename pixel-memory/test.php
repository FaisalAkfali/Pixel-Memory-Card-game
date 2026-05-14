<?php
require_once 'includes/config.php';
require_once 'includes/classes/Database.php';
require_once 'includes/classes/Game.php';

echo "<h1 style='color:green'>✅ Database Connection Test</h1>";

$game = new Game();
$stats = $game->getTotalGames();
$leaderboard = $game->getLeaderboard(3);

echo "<p>Total Games in Database: " . $stats . "</p>";
echo "<h3>Leaderboard:</h3>";
echo "<ul>";
foreach($leaderboard as $player) {
    echo "<li>" . $player['username'] . " - Best: " . $player['best_score'] . " moves</li>";
}
echo "</ul>";
echo "<p>If you see this, your database is working!</p>";
?>