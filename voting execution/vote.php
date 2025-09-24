<?php
session_start();
require 'db.php';

// Only logged in users
if (!isset($_SESSION['voter_id'])) {
    die("Not logged in.");
}

$voter_id = $_SESSION['voter_id'];
$candidate = $_POST['candidate'] ?? null;

if (!$candidate) {
    die("No candidate selected.");
}

// Check if already voted
$stmt = $conn->prepare("SELECT has_voted FROM voters WHERE id=?");
$stmt->bind_param("i", $voter_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if ($row['has_voted'] == 1) {
    die("You already voted!");
}

// Record vote (make votes table if needed)
$voteStmt = $conn->prepare("INSERT INTO votes (voter_id, candidate) VALUES (?, ?)");
$voteStmt->bind_param("is", $voter_id, $candidate);
$voteStmt->execute();

// Mark as voted
$update = $conn->prepare("UPDATE voters SET has_voted=1 WHERE id=?");
$update->bind_param("i", $voter_id);
$update->execute();

$_SESSION['has_voted'] = 1;

$voteStmt->close();
$update->close();
$conn->close();

header("Location: homepage.php");
exit;
?>
