<?php
require_once 'config.php';

// Get election settings
$stmt = $pdo->prepare("SELECT setting_value FROM election_settings WHERE setting_name = ?");
$stmt->execute(['results_public']);
$results_public = $stmt->fetchColumn() === 'true';

// Check if user should be able to view results
if (!$results_public && !isLoggedIn()) {
    showAlert('Please login to view results.', 'warning');
    redirectTo('login.php');
}

// Get voting statistics
$total_votes = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
$turnout_rate = $total_users > 0 ? round(($total_votes / $total_users) * 100, 1) : 0;

// Get candidates with vote counts
$stmt = $pdo->query("
    SELECT c.*, 
           COALESCE(c.vote_count, 0) as votes,
           CASE 
               WHEN $total_votes > 0 THEN ROUND((c.vote_count / $total_votes) * 100, 1)
               ELSE 0 
           END as percentage
    FROM candidates c 
    ORDER BY c.vote_count DESC, c.name ASC
");
$candidates = $stmt->fetchAll();

// Determine winner(s)
$max_votes = $candidates[0]['votes'] ?? 0;
$winners = array_filter($candidates, function($candidate) use ($max_votes) {
    return $candidate['votes'] == $max_votes && $max_votes > 0;
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - VoteSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .results-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .results-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: none;
        }
        .results-header {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
            text-align: center;
            padding: 30px 20px;
            border-radius: 20px 20px 0 0;
        }
        .candidate-result {
            border-radius: 15px;
            margin-bottom: 20px;
            overflow: hidden;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            background: white;
        }
        .candidate-result:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .candidate-result.winner {
            border-color: #28a745;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }
        .candidate-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .vote-bar {
            height: 30px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 15px;
            transition: width 1s ease-in-out;
            position: relative;
            overflow: hidden;
        }
        .vote-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(255,255,255,0.3) 50%, 
                transparent 100%);
            animation: shimmer 2s infinite;
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: none;
            height: 100%;
        }
        .winner-crown {
            color: #ffd700;
            font-size: 2rem;
            margin-right: 10px;
        }
        .refresh-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
            transition: all 0.3s ease;
        }
        .refresh-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.6);
        }
        .live-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            background: #28a745;
            border-radius: 50%;
            animation: pulse 2s infinite;
            margin-right: 8px;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-vote-yea me-2"></i>VoteSecure
            </a>
            <div class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <span class="navbar-text me-3">
                        <i class="fas fa-user me-1"></i>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                    </span>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="results-container">
            <div class="card results-card">
                <div class="results-header">
                    <h1 class="mb-0">
                        <i class="fas fa-chart-bar me-3"></i>Election Results
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">
                        <span class="live-indicator"></span>Live Results
                    </p>
                </div>
                
                <div class="card-body p-4">
                    <?php displayAlert(); ?>
                    
                    <!-- Statistics Overview -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-3">
                            <div class="stats-card">
                                <i class="fas fa-vote-yea fa-3x text-primary mb-3"></i>
                                <h3 class="fw-bold"><?php echo $total_votes; ?></h3>
                                <p class="text-muted mb-0">Total Votes</p>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stats-card">
                                <i class="fas fa-users fa-3x text-info mb-3"></i>
                                <h3 class="fw-bold"><?php echo $total_users; ?></h3>
                                <p class="text-muted mb-0">Eligible Voters</p>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stats-card">
                                <i class="fas fa-percentage fa-3x text-success mb-3"></i>
                                <h3 class="fw-bold"><?php echo $turnout_rate; ?>%</h3>
                                <p class="text-muted mb-0">Turnout Rate</p>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stats-card">
                                <i class="fas fa-user-tie fa-3x text-warning mb-3"></i>
                                <h3 class="fw-bold"><?php echo count($candidates); ?></h3>
                                <p class="text-muted mb-0">Candidates</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Winner Announcement -->
                    <?php if (!empty($winners) && $total_votes > 0): ?>
                        <div class="alert alert-success text-center mb-5" style="border-radius: 15px; border: none;">
                            <h4 class="mb-3">
                                <i class="fas fa-crown winner-crown"></i>
                                <?php echo count($winners) > 1 ? 'Current Leaders' : 'Current Leader'; ?>
                            </h4>
                            <?php foreach ($winners as $winner): ?>
                                <h5 class="fw-bold text-success">
                                    <?php echo htmlspecialchars($winner['name']); ?>
                                    <span class="badge bg-success ms-2"><?php echo $winner['votes']; ?> votes</span>
                                </h5>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Candidates Results -->
                    <h4 class="mb-4">
                        <i class="fas fa-poll me-2"></i>Detailed Results
                    </h4>
                    
                    <?php if (empty($candidates)): ?>
                        <div class="alert alert-info text-center" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            No candidates available.
                        </div>
                    <?php else: ?>
                        <?php foreach ($candidates as $index => $candidate): ?>
                            <div class="candidate-result <?php echo in_array($candidate, $winners) && $total_votes > 0 ? 'winner' : ''; ?>">
                                <div class="p-4">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-secondary fs-6 me-3">
                                                    #<?php echo $index + 1; ?>
                                                </span>
                                                <?php if (!empty($candidate['image'])): ?>
                                                    <img src="uploads/<?php echo htmlspecialchars($candidate['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($candidate['name']); ?>" 
                                                         class="candidate-image me-3">
                                                <?php else: ?>
                                                    <div class="candidate-image d-flex align-items-center justify-content-center me-3" 
                                                         style="background: linear-gradient(45deg, #667eea, #764ba2);">
                                                        <i class="fas fa-user fa-2x text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="col">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <h5 class="fw-bold mb-1">
                                                        <?php if (in_array($candidate, $winners) && $total_votes > 0): ?>
                                                            <i class="fas fa-crown text-warning me-2"></i>
                                                        <?php endif; ?>
                                                        <?php echo htmlspecialchars($candidate['name']); ?>
                                                    </h5>
                                                    <?php if (!empty($candidate['party'])): ?>
                                                        <p class="text-muted mb-0">
                                                            <i class="fas fa-flag me-1"></i>
                                                            <?php echo htmlspecialchars($candidate['party']); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="col-md-8">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="fw-semibold">
                                                            <?php echo $candidate['votes']; ?> votes 
                                                            (<?php echo $candidate['percentage']; ?>%)
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="progress" style="height: 30px; border-radius: 15px;">
                                                        <div class="vote-bar" 
                                                             style="width: <?php echo $candidate['percentage']; ?>%"
                                                             data-bs-toggle="tooltip" 
                                                             title="<?php echo $candidate['votes']; ?> votes (<?php echo $candidate['percentage']; ?>%)">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Action Buttons -->
                    <div class="text-center mt-5">
                        <a href="index.php" class="btn btn-outline-primary me-2">
                            <i class="fas fa-home me-2"></i>Back to Home
                        </a>
                        
                        <?php if (isLoggedIn()): ?>
                            <?php
                            $stmt = $pdo->prepare("SELECT has_voted FROM users WHERE id = ?");
                            $stmt->execute([$_SESSION['user_id']]);
                            $user_has_voted = $stmt->fetchColumn();
                            ?>
                            
                            <?php if (!$user_has_voted): ?>
                                <a href="vote.php" class="btn btn-success">
                                    <i class="fas fa-vote-yea me-2"></i>Cast Your Vote
                                </a>
                            <?php endif; ?>
                            
                            <?php if (isAdmin()): ?>
                                <a href="admin.php" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-cog me-2"></i>Admin Panel
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Last Updated -->
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Last updated: <span id="lastUpdated"><?php echo date('Y-m-d H:i:s'); ?></span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-refresh button -->
    <button class="refresh-btn" onclick="location.reload()" title="Refresh Results">
        <i class="fas fa-sync-alt"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Auto-refresh every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
        
        // Animate vote bars on load
        window.addEventListener('load', function() {
            const bars = document.querySelectorAll('.vote-bar');
            bars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
            });
        });
        
        // Update last updated time
        function updateTime() {
            document.getElementById('lastUpdated').textContent = new Date().toLocaleString();
        }
        
        // Rotation animation for refresh button
        document.querySelector('.refresh-btn').addEventListener('click', function() {
            this.style.transform = 'rotate(360deg) scale(1.1)';
            setTimeout(() => {
                this.style.transform = '';
            }, 300);
        });
    </script>
</body>
</html>