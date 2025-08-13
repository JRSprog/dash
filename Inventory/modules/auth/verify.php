<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_GET['token'])) {
    header("Location: login.php?error=Invalid verification link");
    exit();
}

$token = $_GET['token'];

try {
    // Check if token exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = ? AND verified_at IS NULL");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header("Location: login.php?error=Invalid or expired verification link");
        exit();
    }
    
    // Mark as verified
    $pdo->prepare("UPDATE users SET verified_at = NOW(), verification_token = NULL WHERE id = ?")
        ->execute([$user['id']]);
    
    header("Location: login.php?success=Your account has been verified. You can now login.");
    exit();
    
} catch (PDOException $e) {
    error_log("Verification error: " . $e->getMessage());
    header("Location: login.php?error=Verification failed. Please try again.");
    exit();
}