<?php
session_start();
include '../connect.php';

// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php"); // Redirect to login page if not logged in
    exit;
}

// Get the logged-in student's ID from the session
$student_id = $_SESSION['student_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ongoing Fees</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../uploads/blogo.png" type="x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/st.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- Header -->
<header>
    <div class="menu-container">
        <button class="burger-button" onclick="toggleSidebar()">☰</button>
    </div>
    <div class="dropdown">
        <button class="dropdown-button"><i class="fa-solid fa-user"></i></button>
        <div class="dropdown-content">
           <a href="profile.php"><i class="fa-solid fa-user"></i>&nbsp; Profile</a>
           <a href="changepass.php"><i class="fa-solid fa-gear"></i>&nbsp; Settings</a>
           <a href="../logout.php?logout=true"><i class="fa-solid fa-right-from-bracket"></i>&nbsp; Logout</a>
        </div>
    </div>
</header>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="close">
        <span class="close-sidebar" onclick="toggleSidebar()"><i class="fa-solid fa-arrow-left"></i></span>
        <img src="../uploads/blogo.png" alt="Image" class="sidebar-image">
        <p class="sidebar-text">s<?php echo $student_id; ?></p>
    </div>
    
    <div class="sidebar-content">
        <a href="dashboard.php" class="sidebar-item"><i class="fa-solid fa-house"></i>&nbsp; Dashboard</a>
        <a href="accstate.php" class="sidebar-item"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Account Statement</a>
        <a href="stonfees.php" class="sidebar-item"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Ongoing Fees</a>
    </div>
</div><br>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="stfees">
        <h2>Ongoing Fees</h2>
        <?php
        // SQL query to fetch data for the logged-in student
        $select = "SELECT s.stid, s.program, st.payname, st.parname, st.amount, st.date 
                   FROM students s
                   LEFT JOIN stfees st ON s.stid = st.stid
                   WHERE s.stid = ?"; // Filter by the logged-in student's ID

        $stmt = $con->prepare($select);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if (!$res) {
            die("<p style='color: red;'>Query Failed: " . mysqli_error($con) . "</p>");
        }

        // Display data
        if (mysqli_num_rows($res) > 0) {
            while ($row = mysqli_fetch_assoc($res)) {
                echo '<div class="parentstpay">';
                echo '<div class="stpay">'; 
                echo '<p>Name of payment: <strong>' . htmlspecialchars($row['payname']) . '</strong></p>';
                echo '<p>Particular: <strong>' . htmlspecialchars($row['parname']) . '</strong></p>';
                echo '<p>Amount: <strong>₱ ' . htmlspecialchars(number_format($row['amount'], 2)) . '</strong></p>';
                echo '<p>Date release: <strong>' . htmlspecialchars(date('F j, Y', strtotime($row['date']))) . '</strong></p>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo "<p>No ongoing fees found.</p>";
        }
        ?>

        <?php
        // SQL query to fetch fees data for the logged-in student
        $select1 = "SELECT s.program, f.selct, f.pname, f.paname, f.amount, f.date 
                   FROM students s
                   LEFT JOIN fees f ON s.program = f.selct
                   WHERE s.stid = ?"; // Filter by the logged-in student's ID

        $stmt1 = $con->prepare($select1);
        $stmt1->bind_param("i", $student_id);
        $stmt1->execute();
        $res1 = $stmt1->get_result();

        if (!$res1) {
            die("<p style='color: red;'>Query Failed: " . mysqli_error($con) . "</p>");
        }

        // Display data
        if (mysqli_num_rows($res1) > 0) {
            while ($row1 = mysqli_fetch_assoc($res1)) {
                echo '<div class="parentstpay">';
                echo '<div class="stpay">'; 
                echo '<p>Name of payment: <strong>' . htmlspecialchars($row1['pname']) . '</strong></p>';
                echo '<p>Particular: <strong>' . htmlspecialchars($row1['paname']) . '</strong></p>';
                echo '<p>Amount: <strong>₱ ' . htmlspecialchars(number_format($row1['amount'], 2)) . '</strong></p>';
                echo '<p>Date release: <strong>' . htmlspecialchars(date('F j, Y', strtotime($row1['date']))) . '</strong></p>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo "<p>No ongoing fees found.</p>";
        }
        ?>
    </div>
</div>
<script src="../js/script.js"></script>
</body>
</html>