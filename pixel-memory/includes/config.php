<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

require_once __DIR__ . '/csrf.php';

require_once __DIR__ . '/functions.php';

$host = 'localhost';
$dbname = 'memory_game_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database Connection Failed");
}
?>