<?php
// includes/auth-check.php

// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../modules/auth/login.php");
    exit();
}

// Define user roles and their permissions
$permissions = [
    'admin' => [
        'dashboard', 'products', 'categories', 'suppliers', 
        'purchases', 'sales', 'reports', 'users', 'archives'
    ],
    'manager' => [
        'dashboard', 'products', 'categories', 'suppliers',
        'purchases', 'sales', 'reports', 'users'
    ],
    'staff' => [
        'dashboard', 'products', 'sales', 'users'
    ]
];

// Get current module from URL
$current_module = explode('/', $_SERVER['PHP_SELF']);
$current_module = $current_module[count($current_module) - 2]; // Get the module name

// Check if user has permission to access the current module
$user_role = $_SESSION['user_role'] ?? 'staff'; // Default to staff if role not set

if (!in_array($current_module, $permissions[$user_role])) {
    // Unauthorized access - show message and redirect
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="../../assets/images/jrs.png" type="x-icon">
        <title>Unauthorized Access</title>
        <style>
            .unauthorized-container {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.8);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                color: white;
                font-family: Arial, sans-serif;
                text-align: center;
            }
            .unauthorized-message {
                background-color: #dc3545;
                padding: 2rem;
                border-radius: 5px;
                box-shadow: 0 0 20px rgba(0,0,0,0.5);
            }
        </style>
    </head>
    <body>
        <div class="unauthorized-container">
            <div class="unauthorized-message">
                <h1>Access Denied</h1>
                <p>You are not authorized to view this page.</p>
                <p>Redirecting you back to the dashboard...</p>
            </div>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = "../dashboard/";
            }, 2000);
        </script>
    </body>
    </html>
    ';
    exit();
}
?>