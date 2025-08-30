<?php
require_once 'config.php';

// Get election settings
$stmt = $pdo->query("SELECT * FROM election_settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_name']] = $row['setting_value'];
}

$election_title = $settings['election_title'] ?? 'Online Election';
$election_status = $settings['election_status'] ?? 'inactive';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $election_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        .feature-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .stats-section {
            background-color: #f8f9fa;
            padding: 60px 0;
        }
        .stat-card {
            text-align: center;
            padding: 30px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn-vote {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-vote:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-vote-yea me-2"></i>VoteSecure
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Home</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="vote.php"><i class="fas fa-vote-yea me-1"></i>Vote</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="results.php"><i class="fas fa-chart-bar me-1"></i>Results</a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin.php"><i class="fas fa-cog me-1"></i>Admin</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php"><i class="fas fa-user-plus me-1"></i>Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="display-4 mb-4">
                <i class="fas fa-vote-yea me-3"></i><?php echo $election_title; ?>
            </h1>
            <p class="lead mb-4">Secure, Transparent, and Easy Online Voting Platform</p>
            
            <?php if ($election_status === 'active'): ?>
                <div class="alert alert-success d-inline-block mb-4">
                    <i class="fas fa-check-circle me-2"></i>Election is currently <strong>ACTIVE</strong>
                </div>
            <?php else: ?>
                <div class="alert alert-warning d-inline-block mb-4">
                    <i class="fas fa-pause-circle me-2"></i>Election is currently <strong>INACTIVE</strong>
                </div>
            <?php endif; ?>

            <div class="mt-4">
                <?php if (isLoggedIn()): ?>
                    <?php if ($election_status === 'active'): ?>
                        <a href="vote.php" class="btn btn-vote btn-lg me-3">
                            <i class="fas fa-vote-yea me-2"></i>Cast Your Vote
                        </a>
                    <?php endif; ?>
                    <a href="results.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-chart-bar me-2"></i>View Results
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-vote btn-lg me-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Login to Vote
                    </a>
                    <a href="register.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Register Now
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Why Choose Our Platform?</h2>
                <p class="lead text-muted">Experience the future of democratic participation</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="fas fa-shield-alt fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Secure Voting</h5>
                            <p class="card-text">Advanced encryption and security measures ensure your vote is protected and confidential.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="fas fa-eye fa-3x text-success"></i>
                            </div>
                            <h5 class="card-title">Transparent Process</h5>
                            <p class="card-text">Real-time results and complete transparency in the voting process for maximum trust.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="fas fa-mobile-alt fa-3x text-info"></i>
                            </div>
                            <h5 class="card-title">Easy Access</h5>
                            <p class="card-text">Vote from anywhere, anytime with our user-friendly interface on any device.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <?php
                // Get voting statistics
                $total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
                $total_votes = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();
                $total_candidates = $pdo->query("SELECT COUNT(*) FROM candidates")->fetchColumn();
                $turnout_rate = $total_users > 0 ? round(($total_votes / $total_users) * 100, 1) : 0;
                ?>
                
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold"><?php echo $total_users; ?></h3>
                        <p class="text-muted">Registered Voters</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-vote-yea fa-3x text-success mb-3"></i>
                        <h3 class="fw-bold"><?php echo $total_votes; ?></h3>
                        <p class="text-muted">Votes Cast</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-user-tie fa-3x text-info mb-3"></i>
                        <h3 class="fw-bold"><?php echo $total_candidates; ?></h3>
                        <p class="text-muted">Candidates</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-percentage fa-3x text-warning mb-3"></i>
                        <h3 class="fw-bold"><?php echo $turnout_rate; ?>%</h3>
                        <p class="text-muted">Turnout Rate</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container text-center">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2025 VoteSecure. All rights reserved.</p>
                </div>
                <div class="col-md-6">
                    <p>Secure • Transparent • Democratic</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>