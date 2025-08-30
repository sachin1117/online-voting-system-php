<?php
require_once 'config.php';

// Check if user is admin
if (!isAdmin()) {
    showAlert('Access denied. Admin privileges required.', 'danger');
    redirectTo('index.php');
}

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
        
        // Add new candidate
        if (isset($_POST['add_candidate'])) {
            $name = sanitize($_POST['candidate_name']);
            $party = sanitize($_POST['candidate_party']);
            $description = sanitize($_POST['candidate_description']);
            
            if (empty($name)) {
                $error = 'Candidate name is required.';
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO candidates (name, party, description) VALUES (?, ?, ?)");
                    if ($stmt->execute([$name, $party, $description])) {
                        $success = 'Candidate added successfully.';
                    } else {
                        $error = 'Failed to add candidate.';
                    }
                } catch (PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
        
        // Delete candidate
        if (isset($_POST['delete_candidate'])) {
            $candidate_id = intval($_POST['candidate_id']);
            try {
                $stmt = $pdo->prepare("DELETE FROM candidates WHERE id = ?");
                if ($stmt->execute([$candidate_id])) {
                    $success = 'Candidate deleted successfully.';
                } else {
                    $error = 'Failed to delete candidate.';
                }
            } catch (PDOException $e) {
                $error = 'Cannot delete candidate: ' . $e->getMessage();
            }
        }
        
        // Update election settings
        if (isset($_POST['update_settings'])) {
            $election_title = sanitize($_POST['election_title']);
            $election_status = sanitize($_POST['election_status']);
            $results_public = isset($_POST['results_public']) ? 'true' : 'false';
            
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("UPDATE election_settings SET setting_value = ? WHERE setting_name = ?");
                $stmt->execute([$election_title, 'election_title']);
                $stmt->execute([$election_status, 'election_status']);
                $stmt->execute([$results_public, 'results_public']);
                
                $pdo->commit();
                $success = 'Settings updated successfully.';
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Failed to update settings: ' . $e->getMessage();
            }
        }
        
        // Reset election
        if (isset($_POST['reset_election'])) {
            if ($_POST['confirm_reset'] === 'RESET') {
                try {
                    $pdo->beginTransaction();
                    
                    // Delete all votes
                    $pdo->exec("DELETE FROM votes");
                    
                    // Reset candidate vote counts
                    $pdo->exec("UPDATE candidates SET vote_count = 0");
                    
                    // Reset user voted status
                    $pdo->exec("UPDATE users SET has_voted = 0 WHERE is_admin = 0");
                    
                    $pdo->commit();
                    $success = 'Election reset successfully. All votes have been cleared.';
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $error = 'Failed to reset election: ' . $e->getMessage();
                }
            } else {
                $error = 'Invalid confirmation. Type "RESET" to confirm.';
            }
        }
        
    } else {
        $error = 'Invalid request. Please try again.';
    }
}

// Get current settings
$stmt = $pdo->query("SELECT * FROM election_settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_name']] = $row['setting_value'];
}

// Get candidates
$candidates = $pdo->query("SELECT * FROM candidates ORDER BY name")->fetchAll();

// Get statistics
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
$total_votes = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();
$total_candidates = $pdo->query("SELECT COUNT(*) FROM candidates")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - VoteSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .admin-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: none;
        }
        .admin-header {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
            text-align: center;
            padding: 30px 20px;
            border-radius: 20px 20px 0 0;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: none;
            height: 100%;
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .section-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 30px;
        }
        .section-header {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            border-bottom: 2px solid #e9ecef;
        }
        .candidate-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
        }
        .btn-danger-custom {
            background: linear-gradient(45deg, #dc3545, #c82333);
            border: none;
            transition: all 0.3s ease;
        }
        .btn-danger-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
        }
        .alert {
            border-radius: 15px;
            border: none;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-vote-yea me-2"></i>VoteSecure
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user-shield me-1"></i>Admin: <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </span>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="admin-container">
            <div class="card admin-card">
                <div class="admin-header">
                    <h1 class="mb-0">
                        <i class="fas fa-cog me-3"></i>Admin Control Panel
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">Manage elections, candidates, and settings</p>
                </div>
                
                <div class="card-body p-4">
                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Statistics Overview -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-3">
                            <div class="stats-card">
                                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                <h3 class="fw-bold"><?php echo $total_users; ?></h3>
                                <p class="text-muted mb-0">Registered Voters</p>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stats-card">
                                <i class="fas fa-vote-yea fa-3x text-success mb-3"></i>
                                <h3 class="fw-bold"><?php echo $total_votes; ?></h3>
                                <p class="text-muted mb-0">Total Votes</p>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stats-card">
                                <i class="fas fa-user-tie fa-3x text-info mb-3"></i>
                                <h3 class="fw-bold"><?php echo $total_candidates; ?></h3>
                                <p class="text-muted mb-0">Candidates</p>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stats-card">
                                <i class="fas fa-percentage fa-3x text-warning mb-3"></i>
                                <h3 class="fw-bold">
                                    <?php echo $total_users > 0 ? round(($total_votes / $total_users) * 100, 1) : 0; ?>%
                                </h3>
                                <p class="text-muted mb-0">Turnout Rate</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Election Settings -->
                    <div class="section-card">
                        <div class="section-header">
                            <h4 class="mb-0">
                                <i class="fas fa-sliders-h me-2"></i>Election Settings
                            </h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="election_title" class="form-label fw-semibold">
                                                <i class="fas fa-heading me-2"></i>Election Title
                                            </label>
                                            <input type="text" class="form-control" id="election_title" name="election_title" 
                                                   value="<?php echo htmlspecialchars($settings['election_title'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="election_status" class="form-label fw-semibold">
                                                <i class="fas fa-toggle-on me-2"></i>Election Status
                                            </label>
                                            <select class="form-select" id="election_status" name="election_status">
                                                <option value="active" <?php echo ($settings['election_status'] ?? '') === 'active' ? 'selected' : ''; ?>>
                                                    Active (Voting Allowed)
                                                </option>
                                                <option value="inactive" <?php echo ($settings['election_status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>
                                                    Inactive (Voting Disabled)
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="results_public" name="results_public" 
                                               <?php echo ($settings['results_public'] ?? 'false') === 'true' ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-semibold" for="results_public">
                                            <i class="fas fa-eye me-2"></i>Make results publicly visible (without login)
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="submit" name="update_settings" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Settings
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Candidate Management -->
                    <div class="section-card">
                        <div class="section-header">
                            <h4 class="mb-0">
                                <i class="fas fa-user-tie me-2"></i>Candidate Management
                            </h4>
                        </div>
                        <div class="card-body">
                            <!-- Add New Candidate -->
                            <div class="mb-4">
                                <h5 class="mb-3">Add New Candidate</h5>
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="candidate_name" class="form-label fw-semibold">
                                                    <i class="fas fa-user me-2"></i>Candidate Name <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control" id="candidate_name" name="candidate_name" 
                                                       placeholder="Enter candidate name" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="candidate_party" class="form-label fw-semibold">
                                                    <i class="fas fa-flag me-2"></i>Party/Affiliation
                                                </label>
                                                <input type="text" class="form-control" id="candidate_party" name="candidate_party" 
                                                       placeholder="Enter party name">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Action</label>
                                                <div class="d-grid">
                                                    <button type="submit" name="add_candidate" class="btn btn-success">
                                                        <i class="fas fa-plus me-2"></i>Add Candidate
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="candidate_description" class="form-label fw-semibold">
                                            <i class="fas fa-info-circle me-2"></i>Description
                                        </label>
                                        <textarea class="form-control" id="candidate_description" name="candidate_description" 
                                                  rows="2" placeholder="Enter candidate description (optional)"></textarea>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Existing Candidates -->
                            <div>
                                <h5 class="mb-3">Existing Candidates (<?php echo count($candidates); ?>)</h5>
                                
                                <?php if (empty($candidates)): ?>
                                    <div class="alert alert-info" role="alert">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No candidates have been added yet.
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($candidates as $candidate): ?>
                                        <div class="candidate-item">
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <h6 class="fw-bold mb-1">
                                                        <i class="fas fa-user me-2"></i>
                                                        <?php echo htmlspecialchars($candidate['name']); ?>
                                                        <span class="badge bg-primary ms-2"><?php echo $candidate['vote_count']; ?> votes</span>
                                                    </h6>
                                                    <?php if (!empty($candidate['party'])): ?>
                                                        <p class="text-muted mb-1">
                                                            <i class="fas fa-flag me-1"></i>
                                                            <?php echo htmlspecialchars($candidate['party']); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($candidate['description'])): ?>
                                                        <p class="small text-muted mb-0">
                                                            <?php echo htmlspecialchars($candidate['description']); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="col-md-4 text-end">
                                                    <form method="POST" action="" class="d-inline" 
                                                          onsubmit="return confirm('Are you sure you want to delete this candidate?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                        <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                                                        <button type="submit" name="delete_candidate" class="btn btn-outline-danger btn-sm">
                                                            <i class="fas fa-trash me-1"></i>Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Danger Zone -->
                    <div class="section-card border-danger">
                        <div class="section-header bg-danger text-white">
                            <h4 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning" role="alert">
                                <i class="fas fa-warning me-2"></i>
                                <strong>Warning:</strong> The following actions are irreversible and will permanently delete data.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-danger">Reset Election</h6>
                                    <p class="small text-muted mb-3">
                                        This will delete all votes and reset the election. Users will be able to vote again.
                                    </p>
                                    
                                    <form method="POST" action="" onsubmit="return confirmReset()">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        
                                        <div class="mb-3">
                                            <label for="confirm_reset" class="form-label small">
                                                Type "RESET" to confirm:
                                            </label>
                                            <input type="text" class="form-control" id="confirm_reset" name="confirm_reset" 
                                                   placeholder="Type RESET to confirm">
                                        </div>
                                        
                                        <button type="submit" name="reset_election" class="btn btn-danger-custom text-white">
                                            <i class="fas fa-redo me-2"></i>Reset Election
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-danger">Export Data</h6>
                                    <p class="small text-muted mb-3">
                                        Download election data for backup or analysis purposes.
                                    </p>
                                    
                                    <a href="export.php" class="btn btn-outline-info" target="_blank">
                                        <i class="fas fa-download me-2"></i>Export Results (CSV)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-outline-primary me-2">
                            <i class="fas fa-home me-2"></i>Back to Home
                        </a>
                        <a href="results.php" class="btn btn-outline-success me-2">
                            <i class="fas fa-chart-bar me-2"></i>View Results
                        </a>
                        <a href="vote.php" class="btn btn-outline-info">
                            <i class="fas fa-vote-yea me-2"></i>Test Voting
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function confirmReset() {
            const confirmText = document.getElementById('confirm_reset').value;
            if (confirmText !== 'RESET') {
                alert('Please type "RESET" to confirm the action.');
                return false;
            }
            
            return confirm('Are you absolutely sure you want to reset the entire election?\n\nThis will:\n- Delete ALL votes\n- Reset candidate vote counts\n- Allow users to vote again\n\nThis action CANNOT be undone!');
        }
        
        // Auto-refresh stats every 30 seconds
        function refreshStats() {
            // In a real implementation, you would use AJAX to refresh only the stats
            // For this demo, we'll just add a visual indicator
            const statsCards = document.querySelectorAll('.stats-card');
            statsCards.forEach(card => {
                card.style.opacity = '0.7';
                setTimeout(() => {
                    card.style.opacity = '1';
                }, 200);
            });
        }
        
        setInterval(refreshStats, 30000);
        
        // Form validation for candidate addition
        document.getElementById('candidate_name').addEventListener('input', function() {
            if (this.value.trim().length < 2) {
                this.setCustomValidity('Candidate name must be at least 2 characters long');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>