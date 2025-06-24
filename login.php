<?php
session_start();
require_once 'config.php';

// If already logged in, redirect to admin panel
if (isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.html');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Please enter both username and password';
    }
}

// Check current user count for registration link
$stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
$userCount = $stmt->fetch()['user_count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Quiz Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .login-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
        .user-count {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            margin-bottom: 15px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-card">
                    <div class="login-header">
                        <h3 class="mb-0">Quiz Admin Panel</h3>
                        <p class="mb-0 mt-2">Please login to continue</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="user-count">
                            <strong>Current Users:</strong> <?php echo $userCount; ?>/2
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                Default credentials: admin / admin123
                            </small>
                        </div>
                        
                        <?php if ($userCount < 2): ?>
                            <hr>
                            <div class="text-center">
                                <small class="text-muted">Don't have an account?</small><br>
                                <a href="register.php" class="btn btn-outline-success btn-sm mt-2">Register New User</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 