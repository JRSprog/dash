<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isset($_GET['id'])) {
    header("Location: archive_view.php");
    exit();
}

$original_id = (int)$_GET['id'];

// Get archived purchase
$stmt = $pdo->prepare("
    SELECT pa.*, u1.username as created_by_name, u2.username as archived_by_name
    FROM purchases_archive pa
    LEFT JOIN users u1 ON pa.created_by = u1.id
    LEFT JOIN users u2 ON pa.archived_by = u2.id
    WHERE pa.id_original = ?
");
$stmt->execute([$original_id]);
$purchase = $stmt->fetch();

if (!$purchase) {
    $_SESSION['error'] = "Archived purchase not found";
    header("Location: archive_view.php");
    exit();
}

// Get archived items
$stmt = $pdo->prepare("
    SELECT pia.*, p.name as product_name, p.sku
    FROM purchase_items_archive pia
    LEFT JOIN products p ON pia.product_id = p.id
    WHERE pia.purchase_id = ?
");
$stmt->execute([$purchase['id']]);
$items = $stmt->fetchAll();

$title = "Archived Purchase #" . htmlspecialchars($purchase['reference_no']);

include '../../includes/header.php';
?>

<!-- Display purchase details similar to your view.php but with archive info -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Archived Purchase Details</h3>
        <div class="card-tools">
            <a href="archive_view.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Archive
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Your existing purchase detail display code -->
        <!-- Add archive-specific information: -->
        <div class="alert alert-secondary">
            <h5><i class="fas fa-archive"></i> Archive Information</h5>
            <p>
                <strong>Archived By:</strong> <?= htmlspecialchars($purchase['archived_by_name']) ?><br>
                <strong>Archived At:</strong> <?= date('M d, Y H:i', strtotime($purchase['archived_at'])) ?><br>
                <strong>Reason:</strong> <?= htmlspecialchars($purchase['archive_reason']) ?>
            </p>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>