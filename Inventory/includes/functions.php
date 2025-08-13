<?php
/**
 * Get all products with optional category filter
 * @param int|null $category_id Filter by category ID
 * @return array Array of products
 */
function getProducts() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE deleted = 0");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get single product by ID
 * @param int $id Product ID
 * @return array|null Product data or null if not found
 */
function getProductById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                           FROM products p
                           LEFT JOIN categories c ON p.category_id = c.id
                           WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}


/**
 * Get all categories with hierarchy support
 * @return array Array of categories
 */
function getCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY parent_id, name");
    $categories = $stmt->fetchAll() ?: [];
    
    // Build hierarchical array if needed
    $result = [];
    foreach ($categories as $category) {
        if (!$category['parent_id']) {
            $result[$category['id']] = $category;
        } else {
            $result[$category['parent_id']]['children'][] = $category;
        }
    }
    return $result;
}

/**
 * Get category name by ID
 * @param int|null $id Category ID
 * @return string Category name or 'Uncategorized'
 */
function getCategoryName($id) {
    if (!$id) return 'Uncategorized';
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
    return $category['name'] ?? 'Uncategorized';
}

/**
 * Get all suppliers with optional active filter
 * @param bool $active_only Only return active suppliers
 * @return array Array of suppliers
 */
/**
 * Get all suppliers with optional active filter
 * @param bool $active_only Only return active suppliers
 * @return array Array of suppliers
 */
function getSuppliers($active_only = true) {
    global $pdo;
    $sql = "SELECT * FROM suppliers";
    if ($active_only) {
        $sql .= " WHERE is_active = 1";
    }
    $sql .= " ORDER BY id";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll() ?: [];
}

/**
 * Get supplier name by ID
 * @param int|null $id Supplier ID
 * @return string Supplier name or 'N/A'
 */
function getSupplierName($id) {
    if (!$id) return 'N/A';
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT name FROM suppliers WHERE id = ?");
    $stmt->execute([$id]);
    $supplier = $stmt->fetch();
    return $supplier['name'] ?? 'N/A';
}

/**
 * Get supplier details by ID
 * @param int $id Supplier ID
 * @return array|null Supplier data or null if not found
 */
function getSupplierById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/**
 * Get all purchases with optional filters
 * @param array $filters Array of filters (status, supplier_id, date_from, date_to)
 * @return array Array of purchases
 */
function getPurchases($filters = []) {
    global $pdo;
    
    $sql = "SELECT p.*, s.name as supplier_name 
            FROM purchases p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            WHERE 1=1";
    $params = [];
    
    if (!empty($filters['status'])) {
        $sql .= " AND p.status = ?";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['supplier_id'])) {
        $sql .= " AND p.supplier_id = ?";
        $params[] = $filters['supplier_id'];
    }
    
    if (!empty($filters['date_from'])) {
        $sql .= " AND p.date_ordered >= ?";
        $params[] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $sql .= " AND p.date_ordered <= ?";
        $params[] = $filters['date_to'];
    }
    
    $sql .= " ORDER BY p.date_ordered DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll() ?: [];
}

/**
 * Get purchase by ID
 * @param int $id Purchase ID
 * @return array|null Purchase data or null if not found
 */
function getPurchaseById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, s.name as supplier_name 
                           FROM purchases p
                           LEFT JOIN suppliers s ON p.supplier_id = s.id
                           WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getPurchaseItems($purchase_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM purchase_items WHERE purchase_id = ?");
        $stmt->execute([$purchase_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching purchase items: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all sales with optional filters
 * @param array $filters Array of filters (date_from, date_to, payment_method)
 * @return array Array of sales
 */
function getSales($filters = []) {
    global $pdo;
    
    $sql = "SELECT s.*, u.username as created_by_username 
            FROM sales s
            LEFT JOIN users u ON s.created_by = u.id
            WHERE 1=1";
    $params = [];
    
    if (!empty($filters['date_from'])) {
        $sql .= " AND s.created_at >= ?";
        $params[] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $sql .= " AND s.created_at <= ?";
        $params[] = $filters['date_to'];
    }
    
    if (!empty($filters['payment_method'])) {
        $sql .= " AND s.payment_method = ?";
        $params[] = $filters['payment_method'];
    }
    
    $sql .= " ORDER BY s.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll() ?: [];
}

/**
 * Get all users with optional role filter
 * @param string|null $role Filter by role
 * @return array Array of users
 */
function getUsers() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM users ORDER BY username");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching users: " . $e->getMessage());
        return [];
    }
}

function getUserById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching user: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user's display name by ID
 * @param int $id User ID
 * @return string User's display name
 */
function getUserName($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT username, full_name FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if ($user) {
        return !empty($user['full_name']) ? $user['full_name'] : $user['username'];
    }
    return 'Unknown User';
}

/**
 * Get product count with optional low stock filter
 * @param bool $low_stock_only Only count low stock items
 * @return int Product count
 */
function getProductCount($low_stock_only = false) {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM products";
    if ($low_stock_only) {
        $sql .= " WHERE quantity > 0 AND quantity <= min_quantity";
    }
    $stmt = $pdo->query($sql);
    return (int)$stmt->fetchColumn();
}

/**
 * Get low stock product count
 * @return int Count of low stock products
 */
function getLowStockCount() {
    return getProductCount(true);
}

/**
 * Get today's sales total
 * @return float Total sales amount for today
 */
function getSalesToday() {
    global $pdo;
    $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE DATE(created_at) = CURDATE()");
    return (float)$stmt->fetchColumn();
}

/**
 * Get today's purchases total
 * @return float Total purchases amount for today
 */
function getPurchasesToday() {
    global $pdo;
    $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM purchases WHERE DATE(date_ordered) = CURDATE()");
    return (float)$stmt->fetchColumn();
}

/**
 * Get recent sales
 * @param int $limit Number of recent sales to return
 * @return array Array of recent sales
 */
function getRecentSales($limit = 5) {
    global $pdo;
    // Cast limit to integer for safety
    $limit = (int)$limit;
    $stmt = $pdo->prepare("SELECT s.*, u.username as created_by_username 
                           FROM sales s
                           LEFT JOIN users u ON s.created_by = u.id
                           ORDER BY s.created_at DESC 
                           LIMIT " . $limit);
    $stmt->execute();
    return $stmt->fetchAll() ?: [];
}

/**
 * Get dates for last 7 days
 * @return array Array of formatted dates
 */
function getLast7DaysDates() {
    $dates = [];
    for ($i = 6; $i >= 0; $i--) {
        $dates[] = date('D', strtotime("-$i days"));
    }
    return $dates;
}

/**
 * Get sales amounts for last 7 days
 * @return array Array of sales amounts indexed by day (0=Sunday)
 */
function getLast7DaysSales() {
    global $pdo;
    $sales = array_fill(0, 7, 0);
    
    $stmt = $pdo->query("
        SELECT DAYOFWEEK(created_at)-1 as day_index, SUM(total_amount) as total 
        FROM sales 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DAYOFWEEK(created_at)
        ORDER BY day_index
    ");
    
    while ($row = $stmt->fetch()) {
        $sales[$row['day_index']] = (float)$row['total'];
    }
    
    return $sales;
}

/**
 * Get top selling products
 * @param int $limit Number of products to return
 * @return array Array of top selling products
 */
function getTopSellingProducts($limit = 5) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.image_path, SUM(si.quantity) as total_sold 
            FROM sale_items si
            JOIN products p ON si.product_id = p.id
            GROUP BY si.product_id
            ORDER BY total_sold DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log("Error in getTopSellingProducts(): " . $e->getMessage());
        return [];
    }
}

/**
 * Get sales by product ID
 * @param int $product_id Product ID
 * @return array Array of sale items for the product
 */
function getProductSales($product_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT si.*, s.created_at, s.invoice_no, s.customer_name
        FROM sale_items si
        JOIN sales s ON si.sale_id = s.id
        WHERE si.product_id = ?
        ORDER BY s.created_at DESC
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll() ?: [];
}


/**
 * Get complete sale information by ID with all related data
 * @param int $id Sale ID
 * @return array|null Sale data with items or null if not found
 */
function getSaleById(int $id): ?array {
    global $pdo;
    
    try {
        // Updated query to match your actual schema
        $stmt = $pdo->prepare("
            SELECT s.*, 
                   u.username as created_by_username,
                   u.full_name as created_by_name
            FROM sales s
            LEFT JOIN users u ON s.created_by = u.id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        $sale = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sale) {
            return null;
        }
        
        // Get sale items with product details
        $stmt = $pdo->prepare("
            SELECT si.*, 
                   p.name as product_name, 
                   p.sku as product_sku, 
                   p.image_path,
                   p.cost_price as product_cost
            FROM sale_items si
            JOIN products p ON si.product_id = p.id
            WHERE si.sale_id = ?
        ");
        $stmt->execute([$id]);
        $sale['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        // Calculate additional metrics
        $sale['total_cost'] = array_reduce($sale['items'], function($carry, $item) {
            return $carry + ($item['quantity'] * $item['product_cost']);
        }, 0);
        
        $sale['profit'] = $sale['total_amount'] - $sale['total_cost'];
        
        return $sale;
        
    } catch (PDOException $e) {
        error_log("Database error in getSaleById(): " . $e->getMessage());
        return null;
    }
}



/**
 * Send password reset email
 */
function sendPasswordResetEmail(string $email): bool {
    global $pdo;
    
    try {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return false;
        }
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database
        $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?")
            ->execute([$token, $expires, $user['id']]);
        
        // Send email
        $reset_link = "http://yourdomain.com/auth/reset_password.php?token=$token";
        $subject = "Password Reset Request";
        $message = "Click this link to reset your password: $reset_link\n\n";
        $message .= "This link will expire in 1 hour.";
        
        return mail($email, $subject, $message);
        
    } catch (PDOException $e) {
        error_log("Password reset error: " . $e->getMessage());
        return false;
    }
}

/**
 * Reset user password
 */
function resetPassword(string $token, string $password): bool {
    global $pdo;
    
    try {
        // Check if valid token exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return false;
        }
        
        // Update password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?")
            ->execute([$hashed_password, $user['id']]);
        
        return true;
        
    } catch (PDOException $e) {
        error_log("Password reset error: " . $e->getMessage());
        return false;
    }
}

/**
 * Log user activity
 * @param int $user_id User ID performing the action
 * @param string $action Description of the activity
 * @return bool True if logged successfully
 */
function logActivity($user_id, $action) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action) VALUES (?, ?)");
        return $stmt->execute([$user_id, $action]);
    } catch (PDOException $e) {
        error_log("Failed to log activity: " . $e->getMessage());
        return false;
    }
}


/**
 * Generate pagination HTML
 * 
 * @param int $current_page Current page number
 * @param int $total_pages Total number of pages
 * @param string $url_base Base URL for pagination links (must include ? or &)
 * @return string HTML pagination links
 */
function pagination($current_page, $total_pages, $url_base = '?') {
    if ($total_pages <= 1) {
        return '';
    }

    $html = '<ul class="pagination pagination-sm m-0 float-right">';
    
    // Previous button
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url_base . 'page=' . ($current_page - 1) . '">&laquo;</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';
    }

    // Page numbers
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    // Always show first page
    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url_base . 'page=1">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current_page) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $url_base . 'page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    // Always show last page
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $url_base . 'page=' . $total_pages . '">' . $total_pages . '</a></li>';
    }

    // Next button
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url_base . 'page=' . ($current_page + 1) . '">&raquo;</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';
    }

    $html .= '</ul>';
    
    return $html;
}



/// Function to get all categories
function getAllCategories($pdo, $parent_id = null) {
    $categories = array();
    $sql = "SELECT * FROM categories WHERE parent_id " . ($parent_id === null ? "IS NULL" : "= :parent_id") . " ORDER BY id";
    $stmt = $pdo->prepare($sql);
    
    if ($parent_id !== null) {
        $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get category by ID
function getCategoryById($pdo, $id) {
    $sql = "SELECT * FROM categories WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to add a new category
function addCategory($pdo, $name, $parent_id = null) {
    $sql = "INSERT INTO categories (name, parent_id) VALUES (:name, :parent_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    
    if ($parent_id === null) {
        $stmt->bindValue(':parent_id', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
    }
    
    return $stmt->execute();
}

// Function to update a category
function updateCategory($pdo, $id, $name, $parent_id = null) {
    $sql = "UPDATE categories SET name = :name, parent_id = :parent_id WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    
    if ($parent_id === null) {
        $stmt->bindValue(':parent_id', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
    }
    
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}

// Function to delete a category
function deleteCategory($pdo, $id) {
    // First, set parent_id to NULL for any child categories
    $sql = "UPDATE categories SET parent_id = NULL WHERE parent_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Then delete the category
    $sql = "DELETE FROM categories WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}

// Function to get all parent categories (for dropdowns)
function getAllParentCategories($pdo, $exclude_id = null) {
    $sql = "SELECT * FROM categories WHERE parent_id IS NULL";
    $params = [];
    
    if ($exclude_id) {
        $sql .= " AND id != :exclude_id";
        $params[':exclude_id'] = $exclude_id;
    }
    
    $sql .= " ORDER BY name";
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to check if category has children
function hasChildren($pdo, $category_id) {
    $sql = "SELECT COUNT(*) as count FROM categories WHERE parent_id = :category_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] > 0;
}


function getMonthlySales() {
    global $pdo;
    $monthlySales = array_fill(0, 12, 0);
    
    $stmt = $pdo->query("SELECT MONTH(created_at) as month, SUM(total_amount) as total 
                         FROM sales 
                         WHERE YEAR(created_at) = YEAR(CURRENT_DATE())
                         GROUP BY MONTH(created_at)");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $monthlySales[$row['month'] - 1] = (float)$row['total'];
    }
    
    return $monthlySales;
}

function getStockLevels() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT 
        SUM(CASE WHEN quantity <= min_quantity THEN 1 ELSE 0 END) as low_stock,
        SUM(CASE WHEN quantity > min_quantity AND quantity <= min_quantity * 2 THEN 1 ELSE 0 END) as in_stock,
        SUM(CASE WHEN quantity > min_quantity * 2 THEN 1 ELSE 0 END) as overstock
        FROM products");
    
    return $stmt->fetch(PDO::FETCH_NUM);
}

function getCategorySales() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT c.name as category_name, SUM(si.quantity * si.unit_price) as total_sales
                         FROM sale_items si
                         JOIN products p ON si.product_id = p.id
                         JOIN categories c ON p.category_id = c.id
                         GROUP BY c.name
                         ORDER BY total_sales DESC
                         LIMIT 10");
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



function getSalesForDate($date, $detailed = false) {
    global $pdo;
    
    if ($detailed) {
        $stmt = $pdo->prepare("SELECT * FROM sales 
                              WHERE DATE(created_at) = ? 
                              ORDER BY created_at DESC");
        $stmt->execute([$date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as total 
                              FROM sales 
                              WHERE DATE(created_at) = ?");
        $stmt->execute([$date]);
        return $stmt->fetchColumn();
    }
}

function getPurchasesForDate($date) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as total 
                          FROM purchases 
                          WHERE DATE(created_at) = ?");
    $stmt->execute([$date]);
    return $stmt->fetchColumn();
}


/**
 * Get a specific purchase item by purchase ID and product ID
 * 
 * @param int $purchase_id
 * @param int $product_id
 * @return array|false The purchase item data or false if not found
 */
function getPurchaseItem($purchase_id, $product_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM purchase_items 
                          WHERE purchase_id = ? AND product_id = ?");
    $stmt->execute([$purchase_id, $product_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

?>