<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth-check.php';

// Verify permissions
if (!in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    $_SESSION['error'] = "You don't have permission to archive purchases";
    header("Location: ../purchases/index.php");
    exit();
}

// Validate input
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid purchase ID";
    header("Location: ../purchases/index.php");
    exit();
}

$purchase_id = (int)$_GET['id'];
$archive_reason = isset($_GET['reason']) ? trim($_GET['reason']) : 'Manual archive by ' . $_SESSION['username'];

try {
    // Start transaction
    $pdo->beginTransaction();

    // 1. Verify purchase exists and is received
    $stmt = $pdo->prepare("
        SELECT p.*, u.username as created_by_name 
        FROM purchases p
        LEFT JOIN users u ON p.created_by = u.id
        WHERE p.id = ? AND p.status = 'received'
        FOR UPDATE
    ");
    $stmt->execute([$purchase_id]);
    $purchase = $stmt->fetch();

    if (!$purchase) {
        throw new Exception("Purchase order not found or not eligible for archiving");
    }

    // 2. Archive purchase
    $stmt = $pdo->prepare("
        INSERT INTO purchases_archive (
            supplier_id, reference_no, total_amount, status,
            date_ordered, date_received, created_by, created_at,
            archived_at, archived_by, archive_reason, id_original
        ) VALUES (
            :supplier_id, :reference_no, :total_amount, :status,
            :date_ordered, :date_received, :created_by, :created_at,
            NOW(), :archived_by, :archive_reason, :id_original
        )
    ");
    $stmt->execute([
        ':supplier_id' => $purchase['supplier_id'],
        ':reference_no' => $purchase['reference_no'],
        ':total_amount' => $purchase['total_amount'],
        ':status' => $purchase['status'],
        ':date_ordered' => $purchase['date_ordered'],
        ':date_received' => $purchase['date_received'],
        ':created_by' => $purchase['created_by'],
        ':created_at' => $purchase['created_at'],
        ':archived_by' => $_SESSION['user_id'],
        ':archive_reason' => $archive_reason,
        ':id_original' => $purchase['id']
    ]);
    $archived_purchase_id = $pdo->lastInsertId();

    // 3. Archive purchase items
    $stmt = $pdo->prepare("
        INSERT INTO purchase_items_archive (
            purchase_id, product_id, quantity,
            unit_price, total_price, archived_at, id_original
        ) SELECT 
            :new_purchase_id, product_id, quantity,
            unit_price, total_price, NOW(), id
        FROM purchase_items
        WHERE purchase_id = :original_purchase_id
    ");
    $stmt->execute([
        ':new_purchase_id' => $archived_purchase_id,
        ':original_purchase_id' => $purchase_id
    ]);
    $items_archived = $stmt->rowCount();

    // 4. Delete from active tables
    $stmt = $pdo->prepare("DELETE FROM purchase_items WHERE purchase_id = ?");
    $stmt->execute([$purchase_id]);

    $stmt = $pdo->prepare("DELETE FROM purchases WHERE id = ?");
    $stmt->execute([$purchase_id]);

    // Commit transaction
    $pdo->commit();

    $_SESSION['success'] = sprintf(
        "Successfully archived purchase #%s (%d items)",
        $purchase['reference_no'],
        $items_archived
    );

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    error_log("Archive Error: " . $e->getMessage());
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = $e->getMessage();
    error_log("Archive Error: " . $e->getMessage());
}

header("Location: ../purchases/index.php");
exit();
?>