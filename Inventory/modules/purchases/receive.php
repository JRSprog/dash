<?php
// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth-check.php';

// Initialize debug logging
$logFile = __DIR__ . '/receive_debug.log';
file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] NEW REQUEST ======================\n", FILE_APPEND);

try {
    // Validate inputs
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    if (!isset($_POST['purchase_id']) || !is_numeric($_POST['purchase_id'])) {
        throw new Exception("Invalid purchase ID");
    }

    if (!isset($_POST['receive_date']) || !strtotime($_POST['receive_date'])) {
        throw new Exception("Invalid receive date");
    }

    
    $purchase_id = (int)$_POST['purchase_id'];
    $receive_date = date('Y-m-d', strtotime($_POST['receive_date']));

    // Database operations
    $pdo->beginTransaction();

    // Update purchase status
    $updateSql = "UPDATE purchases SET status = 'received', date_received = ? WHERE id = ? AND status = 'pending'";
    $stmt = $pdo->prepare($updateSql);
    $stmt->execute([$receive_date, $purchase_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception("Purchase order was already received or is not pending");
    }

    // Update product quantities
    $itemsSql = "SELECT product_id, quantity FROM purchase_items WHERE purchase_id = ?";
    $itemsStmt = $pdo->prepare($itemsSql);
    $itemsStmt->execute([$purchase_id]);
    
    $productUpdateSql = "UPDATE products SET quantity = quantity + ? WHERE id = ?";
    $productStmt = $pdo->prepare($productUpdateSql);

    $updatedProducts = 0;
    while ($item = $itemsStmt->fetch(PDO::FETCH_ASSOC)) {
        $productStmt->execute([$item['quantity'], $item['product_id']]);
        $updatedProducts += $productStmt->rowCount();
    }

    $pdo->commit();

    // Direct redirect on success
    header("Location: index.php?success=1&updated=".$updatedProducts);
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