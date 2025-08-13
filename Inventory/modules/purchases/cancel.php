<?php
// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth-check.php';

// Initialize debug logging
$logFile = __DIR__ . '/cancel_debug.log';
file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] NEW REQUEST ======================\n", FILE_APPEND);

try {
    // Validate inputs
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    if (!isset($_POST['purchase_id']) || !is_numeric($_POST['purchase_id'])) {
        throw new Exception("Invalid purchase ID");
    }

    $purchase_id = (int)$_POST['purchase_id'];

    // Database operations
    $pdo->beginTransaction();

    // Update purchase status to 'cancelled'
    $updateSql = "UPDATE purchases SET status = 'cancelled' WHERE id = ? AND status = 'pending'";
    $stmt = $pdo->prepare($updateSql);
    $stmt->execute([$purchase_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception("Purchase order cannot be cancelled (already received or cancelled)");
    }

    $pdo->commit();

    // Direct redirect on success
    header("Location: index.php?cancel_success=1");
    exit();

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Store error in session and redirect back
    $_SESSION['error'] = $e->getMessage();
    header("Location: view.php?id=".$purchase_id);
    exit();
}
?>