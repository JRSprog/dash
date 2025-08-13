<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Inventory System'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css?v1.2">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="icon" href="../../assets/images/jrs.png" type="x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-boxes"></i> InventoryJRSPro</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                        <a href="../dashboard/"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </li>
                    <li class="<?php echo strpos($_SERVER['PHP_SELF'], 'products/') !== false ? 'active' : ''; ?>">
                        <a href="../products/"><i class="fas fa-box"></i> Products</a>
                    </li>
                    <li class="<?php echo strpos($_SERVER['PHP_SELF'], 'purchases/') !== false ? 'active' : ''; ?>">
                        <a href="../purchases/"><i class="fas fa-shopping-cart"></i> Purchases</a>
                    </li>
                    </li>   
                    <li class="<?php echo strpos($_SERVER['PHP_SELF'], 'suppliers/') !== false ? 'active' : ''; ?>">
                        <a href="../suppliers/"><i class="fa-solid fa-truck-fast"></i> Suppliers</a>
                    </li>
                    <li class="<?php echo strpos($_SERVER['PHP_SELF'], 'sales/') !== false ? 'active' : ''; ?>">
                        <a href="../sales/"><i class="fa-solid fa-chart-line"></i> Sales</a>
                    </li>
                    <li class="<?php echo strpos($_SERVER['PHP_SELF'], 'categories/') !== false ? 'active' : ''; ?>">
                        <a href="../categories/"><i class="fa-solid fa-table-list"></i> Categories</a>
                    </li>
                    <li class="nav-dropdown <?php echo strpos($_SERVER['PHP_SELF'], 'reports/') !== false ? 'active' : ''; ?>">
                        <a href="javascript:void(0)" class="dropdown-toggle">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="../reports/sales.php"><i class="fas fa-file-invoice-dollar"></i> Sales Reports</a></li>
                            <li><a href="../reports/purchases.php"><i class="fas fa-shopping-cart"></i> Purchase Reports</a></li>
                        </ul>
                    </li>
                    <li class="<?php echo strpos($_SERVER['PHP_SELF'], 'archives/') !== false ? 'active' : ''; ?>">
                        <a href="../archives/archive_view.php"> <i class="fas fa-archive"></i> Archives</a>
                    </li>
                    <?php if ($_SESSION['user_role'] == 'admin') : ?>
                    <li class="<?php echo strpos($_SERVER['PHP_SELF'], 'users/') !== false ? 'active' : ''; ?>">
                        <a href="../users/"><i class="fas fa-users"></i> Users</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="top-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search here..." id="find">
                </div>
                <div class="user-profile">
                    <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <div class="dropdown">
                        <i class="fas fa-user-circle"></i>
                        <div class="dropdown-content">
                            <?php if (in_array($_SESSION['user_role'], ['admin', 'manager', 'staff'])) : ?>
                                <a href="../users/profile.php?id=<?php echo $_SESSION['user_id']; ?>">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                            <?php endif; ?>
                            <a href="../auth/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            <div class="content-container">
                <script src="../../assets/js/main.js"></script>
                <!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>