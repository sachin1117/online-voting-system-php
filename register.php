<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $full_name = sanitize($_POST['full_name']);
        $phone = sanitize($_POST['phone']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validation
        if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
            $error = 'Please fill in all required fields.';
        } elseif (strlen($username) < 3) {
            $error = 'Username must be at least 3 characters long.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            try {
                // Check if username or email already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                
                if ($stmt->fetch()) {
                    $error = 'Username or email already exists.';
                } else {
                    // Insert new user
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone) VALUES (?, ?, ?, ?, ?)");
                    
                    if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone])) {
                        $success = 'Registration successful! You can now login.';
                    } else {
                        $error = 'Registration failed. Please try again.';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Registration failed. Please try again.';
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
    <title>Register - VoteSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        .register-container {
            max-width: 500px;
            width: 100%;
        }
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: none;
        }
        .register-header {
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
        .btn-register {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
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
            <div class="register-container">
                <div class="card register-card">
                    <div class="register-header">
                        <h2 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>
                            Create Account
                        </h2>
                        <p class="mb-0 mt-2 opacity-75">Join our secure voting platform</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success; ?>
                                <div class="mt-2">
                                    <a href="login.php" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-sign-in-alt me-1"></i>Login Now
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label fw-semibold">
                                        <i class="fas fa-user me-2"></i>Username <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="username" name="username" 
                                               placeholder="Choose username" required 
                                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                                        <span class="input-group-text">
                                            <i class="fas fa-user text-muted"></i>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label fw-semibold">
                                        <i class="fas fa-envelope me-2"></i>Email <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="email" class="form-control" id="email" name="email" 
                                               placeholder="your@email.com" required 
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope text-muted"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label fw-semibold">
                                    <i class="fas fa-id-card me-2"></i>Full Name <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           placeholder="Enter your full name" required 
                                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                                    <span class="input-group-text">
                                        <i class="fas fa-id-card text-muted"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label fw-semibold">
                                    <i class="fas fa-phone me-2"></i>Phone Number
                                </label>
                                <div class="input-group">
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           placeholder="Enter phone number (optional)" 
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    <span class="input-group-text">
                                        <i class="fas fa-phone text-muted"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label fw-semibold">
                                        <i class="fas fa-lock me-2"></i>Password <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Create password" required>
                                        <span class="input-group-text">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                    </div>
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label for="confirm_password" class="form-label fw-semibold">
                                        <i class="fas fa-lock me-2"></i>Confirm Password <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                               placeholder="Confirm password" required>
                                        <span class="input-group-text">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-register text-white w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="text-muted mb-0">Already have an account?</p>
                            <a href="login.php" class="text-decoration-none fw-semibold">
                                <i class="fas fa-sign-in-alt me-1"></i>Login here
                            </a>
                        </div>
                        
                        <hr class="my-3">
                        
                        <div class="text-center">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-home me-2"></i>Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Password confirmation validation -->
    <script>
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        document.getElementById('password').addEventListener('input', function() {
            const confirmPassword = document.getElementById('confirm_password');
            confirmPassword.dispatchEvent(new Event('input'));
        });
    </script>
</body>
</html>