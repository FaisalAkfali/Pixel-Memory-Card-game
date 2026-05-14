<?php
require_once '../includes/config.php';
require_once '../includes/classes/User.php';

$user = new User();

if ($user->isLoggedIn()) {
    redirect($user->isAdmin() ? 'admin.php' : 'game.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed.';
    } else {
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } else {
            $result = $user->register($username, $email, $password);
            
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['error'];
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
    <title>Register | Pixel Memory</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="pixel-bg"></div>
    
    <div class="auth-container">
        <div class="pixel-card">
            <div class="pixel-title">▶ REGISTER ◀</div>
            <div class="pixel-subtitle">CREATE YOUR ARCADE ID</div>
            <div class="text-center mb-20">
                <a href="../index.php" class="pixel-link">◀ HOME — HOW TO PLAY ▶</a>
            </div>
            
            <?php if($error): ?>
                <div class="error-message"><?php echo e($error); ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="success-message"><?php echo e($success); ?> <a href="login.php">Login here</a></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <?php echo csrfInput(); ?>
                <input type="text" name="username" class="pixel-input" placeholder="USERNAME (3-20 chars)" value="<?php echo e($username ?? ''); ?>" required>
                <input type="email" name="email" class="pixel-input" placeholder="EMAIL" value="<?php echo e($email ?? ''); ?>" required>
                <input type="password" name="password" class="pixel-input" placeholder="PASSWORD (min 6 chars)" required>
                <input type="password" name="confirm_password" class="pixel-input" placeholder="CONFIRM PASSWORD" required>
                <button type="submit" class="pixel-btn">▶ CREATE ACCOUNT ◀</button>
            </form>
            
            <div class="text-center mt-20">
                <span class="pixel-subtitle">ALREADY HAVE AN ACCOUNT?</span>
                <a href="login.php" class="pixel-link">▶ LOGIN ◀</a>
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