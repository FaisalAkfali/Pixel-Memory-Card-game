<?php
require_once __DIR__ . '/Database.php';

class Game {
    private $db;
    private $pdo;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }
    
    public function saveScore($userId, $moves, $matchedPairs, $mode) {
        $stmt = $this->pdo->prepare("INSERT INTO game_scores (user_id, moves, matched_pairs, game_mode) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $moves, $matchedPairs, $mode]);
    }
    
    public function getLeaderboard($limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT u.username, MIN(gs.moves) as best_score, COUNT(gs.id) as games_played
            FROM game_scores gs
            JOIN users u ON gs.user_id = u.id
            WHERE gs.matched_pairs = 12
            GROUP BY u.id
            ORDER BY best_score ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getUserStats($userId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                MIN(moves) as best_score,
                COUNT(*) as total_games,
                AVG(moves) as avg_moves,
                SUM(CASE WHEN matched_pairs = 12 THEN 1 ELSE 0 END) as completed_games
            FROM game_scores 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    public function getUserRecentScores($userId, $limit = 5) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM game_scores 
            WHERE user_id = ? 
            ORDER BY played_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getAllScores($limit = 30) {
        $stmt = $this->pdo->prepare("
            SELECT u.username, u.id as user_id, gs.* 
            FROM game_scores gs 
            JOIN users u ON gs.user_id = u.id 
            ORDER BY gs.played_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getTotalGames() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM game_scores");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    public function getAverageMoves() {
        $stmt = $this->pdo->query("SELECT AVG(moves) as avg FROM game_scores");
        $result = $stmt->fetch();
        return round($result['avg'] ?? 0, 1);
    }

    public function deleteScoresForUser($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM game_scores WHERE user_id = ?");
        return $stmt->execute([(int) $userId]);
    }
}
?>