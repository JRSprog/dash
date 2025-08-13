<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    try {
        // Check if supplier has purchases before deleting
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE supplier_id = ?");
        $stmt->execute([$id]);
        $purchaseCount = $stmt->fetchColumn();
        
        if ($purchaseCount > 0) {
            // Instead of deleting, mark as inactive
            $stmt = $pdo->prepare("UPDATE suppliers SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Supplier has purchases and was deactivated instead of deleted";
        } else {
            // No purchases - safe to delete
            $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Supplier deleted successfully";
        }
        
        header("Location: index.php?success=" . urlencode($message));
        exit();
    } catch (PDOException $e) {
        header("Location: index.php?error=" . urlencode("Error deleting supplier: " . $e->getMessage()));
        exit();
    }
}

header("Location: index.php");
exit();