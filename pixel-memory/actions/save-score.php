<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/classes/Game.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Security validation failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$moves = filter_input(INPUT_POST, 'moves', FILTER_VALIDATE_INT);
$matched_pairs = filter_input(INPUT_POST, 'matched_pairs', FILTER_VALIDATE_INT);
$game_mode = sanitize($_POST['game_mode'] ?? 'classic');
$user_id = $_SESSION['user_id'];

if (!$moves || $moves < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid moves value']);
    exit();
}

if (!$matched_pairs || $matched_pairs !== 12) {
    echo json_encode(['success' => false, 'message' => 'Complete the game first!']);
    exit();
}

$allowed_modes = ['off', 'challenge', 'helper', 'sabotage'];
if (!in_array($game_mode, $allowed_modes)) {
    $game_mode = 'classic';
}

$game = new Game();
$result = $game->saveScore($user_id, $moves, $matched_pairs, $game_mode);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Score saved! (' . $moves . ' moves)']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>