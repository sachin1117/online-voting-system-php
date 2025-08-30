<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    showAlert('Please login to cast your vote.', 'warning');
    redirectTo('login.php');
}

// Get election status
$stmt = $pdo->prepare("SELECT setting_value FROM election_settings WHERE setting_name = 'election_status'");
$stmt->execute();
$election_status = $stmt->fetchColumn();

if ($election_status !== 'active') {
    showAlert('Voting is currently not active.', 'warning');
    redirectTo('index.php');
}

// Check if user has already voted
$stmt = $pdo->prepare("SELECT has_voted FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$has_voted = $stmt->fetchColumn();

if ($has_voted) {
    showAlert('You have already cast your vote. Thank you for participating!', 'info');
    redirectTo('results.php');
}

$success = '';
$error = '';

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['vote'])) {
    if (isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
        $candidate_id = intval($_POST['candidate_id']);
        
        if (empty($candidate_id)) {
            $error = 'Please select a candidate to vote for.';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Verify candidate exists
                $stmt = $pdo->prepare("SELECT id, name FROM candidates WHERE id = ?");
                $stmt->execute([$candidate_id]);
                $candidate = $stmt->fetch();
                
                if (!$candidate) {
                    $error = 'Invalid candidate selection.';
                } else {
                    // Double-check user hasn't voted
                    $stmt = $pdo->prepare("SELECT has_voted FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    if ($stmt->fetchColumn()) {
                        $error = 'You have already voted!';
                    } else {
                        // Record the vote
                        $stmt = $pdo->prepare("INSERT INTO votes (user_id, candidate_id, ip_address) VALUES (?, ?, ?)");
                        $stmt->execute([$_SESSION['user_id'], $candidate_id, getClientIP()]);
                        
                        // Update candidate vote count
                        $stmt = $pdo->prepare("UPDATE candidates SET vote_count = vote_count + 1 WHERE id = ?");
                        $stmt->execute([$candidate_id]);
                        
                        // Mark user as voted
                        $stmt = $pdo->prepare("UPDATE users SET has_voted = 1 WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        
                        $pdo->commit();
                        
                        showAlert('Your vote has been successfully recorded! Thank you for participating.', 'success');
                        redirectTo('results.php');
                    }
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Voting failed. Please try again.';
            }
        }
    } else {
        $error = 'Invalid request. Please try again.';
    }
}

// Get all candidates
$stmt = $pdo->query("SELECT * FROM candidates ORDER BY name");
$candidates = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cast Your Vote - VoteSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .voting-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .voting-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: none;
        }
        .voting-header {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            text-align: center;
            padding: 30px 20px;
            border-radius: 20px 20px 0 0;
        }
        .candidate-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
            background: white;
        }
        .candidate-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .candidate-card.selected {
            border-color: #28a745;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }
        .candidate-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .btn-vote {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 10px;
            padding: 15px 40px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .btn-vote:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }
        .btn-vote:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .security-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 20px;
            border-left: 5px solid #17a2b8;
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
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </span>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="voting-container">
            <div class="card voting-card">
                <div class="voting-header">
                    <h1 class="mb-0">
                        <i class="fas fa-vote-yea me-3"></i>Cast Your Vote
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">Choose your preferred candidate</p>
                </div>
                
                <div class="card-body p-4">
                    <!-- Security Information -->
                    <div class="security-info mb-4">
                        <h5 class="text-info mb-3">
                            <i class="fas fa-shield-alt me-2"></i>Voting Security Information
                        </h5>
                        <div class="row text-center">
                            <div class="col-md-3">
                                <i class="fas fa-lock fa-2x text-success mb-2"></i>
                                <small class="d-block">Secure Encryption</small>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-user-secret fa-2x text-success mb-2"></i>
                                <small class="d-block">Anonymous Voting</small>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-ban fa-2x text-success mb-2"></i>
                                <small class="d-block">One Vote Only</small>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-eye fa-2x text-success mb-2"></i>
                                <small class="d-block">Transparent Results</small>
                            </div>
                        </div>
                    </div>

                    <?php displayAlert(); ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="voteForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="candidate_id" id="selectedCandidate" value="">
                        
                        <div class="mb-4">
                            <h4 class="mb-3">
                                <i class="fas fa-users me-2"></i>Select Your Candidate:
                            </h4>
                            
                            <?php if (empty($candidates)): ?>
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No candidates are currently available for voting.
                                </div>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach ($candidates as $candidate): ?>
                                        <div class="col-md-6">
                                            <div class="candidate-card p-4 h-100" onclick="selectCandidate(<?php echo $candidate['id']; ?>)">
                                                <div class="text-center mb-3">
                                                    <?php if (!empty($candidate['image'])): ?>
                                                        <img src="uploads/<?php echo htmlspecialchars($candidate['image']); ?>" 
                                                             alt="<?php echo htmlspecialchars($candidate['name']); ?>" 
                                                             class="candidate-image">
                                                    <?php else: ?>
                                                        <div class="candidate-image d-flex align-items-center justify-content-center" 
                                                             style="background: linear-gradient(45deg, #667eea, #764ba2);">
                                                            <i class="fas fa-user fa-3x text-white"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <h5 class="text-center fw-bold mb-2">
                                                    <?php echo htmlspecialchars($candidate['name']); ?>
                                                </h5>
                                                
                                                <?php if (!empty($candidate['party'])): ?>
                                                    <p class="text-center text-muted mb-2">
                                                        <i class="fas fa-flag me-1"></i>
                                                        <?php echo htmlspecialchars($candidate['party']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($candidate['description'])): ?>
                                                    <p class="text-center small text-muted">
                                                        <?php echo htmlspecialchars($candidate['description']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <div class="text-center mt-3">
                                                    <div class="form-check d-inline-block">
                                                        <input class="form-check-input" type="radio" name="candidate_radio" 
                                                               value="<?php echo $candidate['id']; ?>" id="candidate<?php echo $candidate['id']; ?>">
                                                        <label class="form-check-label fw-semibold" for="candidate<?php echo $candidate['id']; ?>">
                                                            Select This Candidate
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($candidates)): ?>
                            <div class="text-center mt-4">
                                <button type="submit" name="vote" class="btn btn-vote text-white" id="voteButton" disabled>
                                    <i class="fas fa-vote-yea me-2"></i>
                                    Confirm My Vote
                                </button>
                            </div>
                            
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Once submitted, your vote cannot be changed
                                </small>
                            </div>
                        <?php endif; ?>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <a href="index.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-home me-2"></i>Back to Home
                        </a>
                        <a href="results.php" class="btn btn-outline-info">
                            <i class="fas fa-chart-bar me-2"></i>View Current Results
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function selectCandidate(candidateId) {
            // Remove selection from all cards
            document.querySelectorAll('.candidate-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Remove checked state from all radio buttons
            document.querySelectorAll('input[name="candidate_radio"]').forEach(radio => {
                radio.checked = false;
            });
            
            // Select the clicked candidate
            event.currentTarget.classList.add('selected');
            document.getElementById('candidate' + candidateId).checked = true;
            document.getElementById('selectedCandidate').value = candidateId;
            
            // Enable vote button
            document.getElementById('voteButton').disabled = false;
        }
        
        // Add click event to radio buttons
        document.querySelectorAll('input[name="candidate_radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    selectCandidate(this.value);
                }
            });
        });
        
        // Confirm before submitting vote
        document.getElementById('voteForm').addEventListener('submit', function(e) {
            const selectedCandidate = document.querySelector('.candidate-card.selected');
            if (!selectedCandidate) {
                e.preventDefault();
                alert('Please select a candidate before voting.');
                return;
            }
            
            const candidateName = selectedCandidate.querySelector('h5').textContent.trim();
            if (!confirm(`Are you sure you want to vote for "${candidateName}"?\n\nThis action cannot be undone.`)) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>