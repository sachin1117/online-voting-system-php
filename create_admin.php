<?php
// Run this file ONCE to create/update admin user
require_once 'config.php';

// Admin credentials - CHANGE THESE!
$admin_username = 'admin';
$admin_email = 'admin@voting.com';
$admin_password = 'admin123';  // Change this to your desired password
$admin_fullname = 'System Administrator';

try {
    // Hash the password properly
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$admin_username, $admin_email]);
    $existing_admin = $stmt->fetch();
    
    if ($existing_admin) {
        // Update existing admin user
        $stmt = $pdo->prepare("UPDATE users SET password = ?, full_name = ?, is_admin = 1 WHERE username = ? OR email = ?");
        $result = $stmt->execute([$hashed_password, $admin_fullname, $admin_username, $admin_email]);
        
        if ($result) {
            echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin: 20px; border: 1px solid #c3e6cb;'>";
            echo "<h3>✅ Admin User Updated Successfully!</h3>";
            echo "<p><strong>Username:</strong> $admin_username</p>";
            echo "<p><strong>Email:</strong> $admin_email</p>";
            echo "<p><strong>Password:</strong> $admin_password</p>";
            echo "<p><strong>Status:</strong> Admin privileges granted</p>";
            echo "</div>";
        } else {
            throw new Exception("Failed to update admin user");
        }
    } else {
        // Create new admin user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, is_admin) VALUES (?, ?, ?, ?, 1)");
        $result = $stmt->execute([$admin_username, $admin_email, $hashed_password, $admin_fullname]);
        
        if ($result) {
            echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin: 20px; border: 1px solid #c3e6cb;'>";
            echo "<h3>✅ Admin User Created Successfully!</h3>";
            echo "<p><strong>Username:</strong> $admin_username</p>";
            echo "<p><strong>Email:</strong> $admin_email</p>";
            echo "<p><strong>Password:</strong> $admin_password</p>";
            echo "<p><strong>Status:</strong> Admin privileges granted</p>";
            echo "</div>";
        } else {
            throw new Exception("Failed to create admin user");
        }
    }
    
    // Show login instructions
    echo "<div style='background: #cce5ff; color: #004085; padding: 20px; border-radius: 10px; margin: 20px; border: 1px solid #99ccff;'>";
    echo "<h3>📝 Login Instructions:</h3>";
    echo "<p>1. Go to <a href='login.php' style='color: #0066cc;'><strong>login.php</strong></a></p>";
    echo "<p>2. Enter username: <code style='background: #f8f9fa; padding: 2px 6px; border-radius: 4px;'>$admin_username</code></p>";
    echo "<p>3. Enter password: <code style='background: #f8f9fa; padding: 2px 6px; border-radius: 4px;'>$admin_password</code></p>";
    echo "<p>4. After login, go to <a href='admin.php' style='color: #0066cc;'><strong>Admin Panel</strong></a></p>";
    echo "</div>";
    
    // Security warning
    echo "<div style='background: #fff3cd; color: #856404; padding: 20px; border-radius: 10px; margin: 20px; border: 1px solid #ffeaa7;'>";
    echo "<h3>⚠️ Security Warning:</h3>";
    echo "<p><strong>IMPORTANT:</strong> Delete this file (create_admin.php) after creating the admin user for security reasons!</p>";
    echo "<p>You can also change the admin password from the admin panel later.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 10px; margin: 20px; border: 1px solid #f5c6cb;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User - VoteSecure</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .back-btn {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
        }
        .back-btn:hover {
            background: #0056b3;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Admin User Setup</h1>
            <p>This utility creates or updates the admin user account</p>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="back-btn">🏠 Go to Homepage</a>
            <a href="login.php" class="back-btn">🔐 Go to Login</a>
        </div>
    </div>
</body>
</html>