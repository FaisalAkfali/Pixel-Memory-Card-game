<?php
require_once __DIR__ . '/Database.php';

class User {
    private $db;
    private $pdo;
    private $id;
    private $username;
    private $email;
    private $role;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }
    
    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $this->id = $user['id'];
            $this->username = $user['username'];
            $this->email = $user['email'];
            $this->role = $user['role'];
            
            $_SESSION['user_id'] = $this->id;
            $_SESSION['username'] = $this->username;
            $_SESSION['role'] = $this->role;
            $_SESSION['email'] = $this->email;
            
            return true;
        }
        return false;
    }
    
    public function register($username, $email, $password) {
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            return ['success' => false, 'error' => 'Username must be 3-20 characters (letters, numbers, underscore)'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid email format'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'error' => 'Password must be at least 6 characters'];
        }
        
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Username or email already exists'];
        }
        
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'student')");
        
        if ($stmt->execute([$username, $email, $hash])) {
            return ['success' => true, 'message' => 'Account created successfully'];
        }
        
        return ['success' => false, 'error' => 'Registration failed'];
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    public function getUsername() {
        return $_SESSION['username'] ?? null;
    }
    
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    public function getRole() {
        return $_SESSION['role'] ?? null;
    }
    
    public function logout() {
        $_SESSION = array();
        session_destroy();
    }
    
    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
    public function deleteUser($userId) {
        if ($userId == $this->getUserId()) {
            return false;
        }
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$userId]);
    }
}
?>