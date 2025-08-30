<?php
require_once 'config.php';

// Check if user is admin
if (!isAdmin()) {
    die('Access denied. Admin privileges required.');
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="election_results_' . date('Y-m-d_H-i-s') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Write CSV header
fputcsv($output, ['Candidate Name', 'Party', 'Vote Count', 'Percentage', 'Description']);

// Get total votes for percentage calculation
$total_votes = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();

// Get candidates with vote counts
$stmt = $pdo->query("
    SELECT c.*, 
           COALESCE(c.vote_count, 0) as votes,
           CASE 
               WHEN $total_votes > 0 THEN ROUND((c.vote_count / $total_votes) * 100, 2)
               ELSE 0 
           END as percentage
    FROM candidates c 
    ORDER BY c.vote_count DESC, c.name ASC
");

// Write candidate data
while ($row = $stmt->fetch()) {
    fputcsv($output, [
        $row['name'],
        $row['party'] ?: 'Independent',
        $row['votes'],
        $row['percentage'] . '%',
        $row['description'] ?: 'No description'
    ]);
}

// Add summary information
fputcsv($output, []);
fputcsv($output, ['ELECTION SUMMARY']);
fputcsv($output, ['Total Votes Cast', $total_votes]);
fputcsv($output, ['Total Registered Voters', $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn()]);
fputcsv($output, ['Total Candidates', $pdo->query("SELECT COUNT(*) FROM candidates")->fetchColumn()]);
fputcsv($output, ['Turnout Rate', ($total_votes > 0 ? round(($total_votes / $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn()) * 100, 2) : 0) . '%']);
fputcsv($output, ['Export Date', date('Y-m-d H:i:s')]);

fclose($output);
exit;
?>