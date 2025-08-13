<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

try {
    // Check if the request method is DELETE
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        throw new Exception('Invalid request method');
    }

    // Get the product ID from the query string
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        throw new Exception('Invalid product ID');
    }

    // Check if the product exists
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) {
        throw new Exception('Product not found');
    }

    // Delete the product
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $success = $stmt->execute([$id]);

    if (!$success) {
        throw new Exception('Failed to delete product');
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Product deleted successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}