<?php
session_start();
require_once 'includes/classes/Database.php';
require_once 'includes/classes/Game.php';

header('Content-Type: application/json');

$game = new Game();
$leaderboard = $game->getLeaderboard(5);

echo json_encode($leaderboard);
?>