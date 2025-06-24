<?php
session_start();
require_once 'config.php';

// If already logged in, redirect to admin panel
if (isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

$error = '';
$success = '';

// Check current user count
$stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
$userCount = $stmt->fetch()['user_count'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Check if registration is allowed (max 2 users)
    if ($userCount >= 2) {
        $error = 'Maximum number of users (2) already registered. Registration is closed.';
    } elseif (!$username || !$password || !$confirm_password) {
        $error = 'Please fill all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $error = 'Username already exists';
        } else {
            // Create new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            if ($stmt->execute([$username, $hashed_password])) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
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
    <title>Register - Quiz Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .register-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
        .user-count {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="register-card">
                    <div class="register-header">
                        <h3 class="mb-0">Quiz Admin Panel</h3>
                        <p class="mb-0 mt-2">User Registration</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="user-count">
                            <strong>Current Users:</strong> <?php echo $userCount; ?>/2
                            <?php if ($userCount >= 2): ?>
                                <br><small class="text-danger">Registration Closed</small>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($userCount < 2): ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Register</button>
                            </form>
                        <?php endif; ?>
                        
                        <div class="mt-3 text-center">
                            <a href="login.php" class="btn btn-outline-secondary btn-sm">Back to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 