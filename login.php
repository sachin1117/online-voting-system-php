<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, username, password, full_name, is_admin FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    
                    showAlert('Welcome back, ' . $user['full_name'] . '!', 'success');
                    redirectTo('index.php');
                } else {
                    $error = 'Invalid username or password.';
                }
            } catch (PDOException $e) {
                $error = 'Login failed. Please try again.';
            }
        }
    } else {
        $error = 'Invalid request. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VoteSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: none;
        }
        .login-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            text-align: center;
            padding: 30px 20px;
            border-radius: 20px 20px 0 0;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
        }
        .form-control:focus + .input-group-text {
            border-color: #667eea;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="login-container">
                <div class="card login-card">
                    <div class="login-header">
                        <h2 class="mb-0">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Welcome Back
                        </h2>
                        <p class="mb-0 mt-2 opacity-75">Sign in to your account</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">
                                    <i class="fas fa-user me-2"></i>Username or Email
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="username" name="username" 
                                           placeholder="Enter your username or email" required>
                                    <span class="input-group-text">
                                        <i class="fas fa-user text-muted"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter your password" required>
                                    <span class="input-group-text">
                                        <i class="fas fa-lock text-muted"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-login text-white w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="text-muted mb-0">Don't have an account?</p>
                            <a href="register.php" class="text-decoration-none fw-semibold">
                                <i class="fas fa-user-plus me-1"></i>Register here
                            </a>
                        </div>
                        
                        <hr class="my-3">
                        
                        <div class="text-center">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-home me-2"></i>Back to Home
                            </a>
                        </div>
                        
                        <!-- Demo Credentials -->
                        <div class="mt-4 p-3" style="background-color: #f8f9fa; border-radius: 10px;">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-info-circle me-2"></i>Demo Credentials:
                            </h6>
                            <small class="text-muted">
                                <strong>Admin:</strong> admin / admin123<br>
                                <strong>User:</strong> Register a new account
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>