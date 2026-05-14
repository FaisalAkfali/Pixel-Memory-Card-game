<?php
require_once '../includes/config.php';
require_once '../includes/classes/User.php';

$user = new User();

if ($user->isLoggedIn()) {
    redirect($user->isAdmin() ? 'admin.php' : 'game.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed. Please try again.';
    } else {
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            $error = 'Please fill in all fields';
        } else {
            if ($user->login($username, $password)) {
                if ($user->isAdmin()) {
                    redirect('admin.php');
                } else {
                    redirect('game.php');
                }
            } else {
                $error = 'Invalid username or password';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Pixel Memory</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="pixel-bg"></div>
    
    <div class="auth-container">
        <div class="pixel-card">
            <div class="pixel-title">LOGIN</div>
            <div class="pixel-subtitle">ENTER YOUR CREDENTIALS</div>
            <div class="text-center mb-20">
                <a href="../index.php" class="pixel-link">◀ HOME — HOW TO PLAY ▶</a>
            </div>
            
            <?php if($error): ?>
                <div class="error-message"><?php echo e($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <?php echo csrfInput(); ?>
                <input type="text" name="username" class="pixel-input" placeholder="USERNAME or EMAIL" required>
                <input type="password" name="password" class="pixel-input" placeholder="PASSWORD" required>
                <button type="submit" class="pixel-btn">▶ LOGIN ◀</button>
            </form>
            
            <div class="text-center mt-20">
                <span class="pixel-subtitle">NO ACCOUNT?</span>
                <a href="register.php" class="pixel-link">▶  REGISTER HERE  ◀</a>
            </div>
        </div>
    </div>
    
    <div class="pixel-corners"></div>
    
    <script>
        const bg = document.querySelector('.pixel-bg');
        for(let i = 0; i < 40; i++) {
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