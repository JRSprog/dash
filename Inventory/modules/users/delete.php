<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

// Only admin can delete users
if ($_SESSION['user_role'] != 'admin') {
    header("Location: ../dashboard/");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?error=Invalid user ID");
    exit();
}

$user_id = (int)$_GET['id'];

// Prevent self-deletion
if ($user_id == $_SESSION['user_id']) {
    header("Location: index.php?error=You cannot delete yourself");
    exit();
}

try {
    global $pdo;
    
    // Prepare and execute deletion
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    if ($stmt->rowCount() > 0) {
        header("Location: index.php?success=User deleted successfully");
    } else {
        header("Location: index.php?error=User not found or already deleted");
    }
} catch (PDOException $e) {
    error_log("Error deleting user: " . $e->getMessage());
    header("Location: index.php?error=Error deleting user");
}

exit();