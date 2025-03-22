<?php
// Start session
session_start();

// Include database connection
include '../connect.php'; // Ensure this file initializes $con securely

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login.php");
    exit();
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// If the form is submitted to add payment
if (isset($_POST['add_payment'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    // Validate and sanitize inputs
    $stid = filter_input(INPUT_POST, 'stid', FILTER_SANITIZE_NUMBER_INT);
    $payment_name = filter_input(INPUT_POST, 'payment_name', FILTER_SANITIZE_STRING);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // Validate required fields
    if (empty($stid) || empty($payment_name) || empty($amount)) {
        die("All fields are required.");
    }

    // Insert into payments table using prepared statements
    $query = "INSERT INTO payments (student_id, payment_name, amount) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($con, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "isd", $stid, $payment_name, $amount);
        if (mysqli_stmt_execute($stmt)) {
            echo "Payment added successfully!";
        } else {
            die("Error executing query: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        die("Error preparing statement: " . mysqli_error($con));
    }
}

// Insert into alist table
if (isset($_POST['alist'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    // Validate and sanitize inputs
    $payments = $_POST['payment1']; // Array of payment names
    $amounts = $_POST['amount1'];   // Array of amounts

    // Validate required fields
    if (empty($payments) || empty($amounts)) {
        die("All fields are required.");
    }

    // Loop through the arrays and insert each payment into the database
    foreach ($payments as $index => $payment) {
        $payment1 = mysqli_real_escape_string($con, $payment);
        $amount1 = filter_var($amounts[$index], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        // Insert into alist table using prepared statements
        $query = "INSERT INTO alist (payment1, amount1) VALUES (?, ?)";
        $stmt = mysqli_prepare($con, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sd", $payment1, $amount1);
            if (!mysqli_stmt_execute($stmt)) {
                die("Error inserting into alist: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
        } else {
            die("Error preparing statement: " . mysqli_error($con));
        }
    }

    echo "Payments added to alist successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Information</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="../uploads/blogo.png" type="x-icon">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<header>
    <div class="menu-container">
      <button class="burger-button" onclick="toggleSidebar()">☰</button>
    </div>
    <div class="dropdown">
      <button class="dropdown-button"><i class="fa-solid fa-user"></i></button>
      <div class="dropdown-content">
         <a href="#"><i class="fa-solid fa-user"></i>&nbsp; Profile</a>
         <a href="#"><i class="fa-solid fa-gear"></i>&nbsp; Settings</a>
         <a href="../logout.php?logout=true"><i class="fa-solid fa-right-from-bracket"></i>&nbsp; Logout</a>
      </div>
    </div>
  </header>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="close">
        <span class="close-sidebar" onclick="toggleSidebar()"><i class="fa-solid fa-arrow-left"></i></span>
        <img src="../uploads/blogo.png" alt="Image" class="sidebar-image">
        <p class="sidebar-text">Your text goes here.</p>
    </div>
    
    <div class="sidebar-content">
      <a href="dashboard.php" class="sidebar-item"><i class="fa-solid fa-house"></i>&nbsp; Dashboard</a>
      <a href="user.php" class="sidebar-item"><i class="fa-solid fa-user"></i>&nbsp; User</a>
      <a href="approval.php" class="sidebar-item"><i class="fa-solid fa-credit-card"></i>&nbsp; Online Approval</a>
      <a href="strecord.php" class="sidebar-item"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Student Information</a>
      <a href="payrecord.php" class="sidebar-item"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Payment Record</a>
      <a href="onfees.php" class="sidebar-item"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Ongoing Fees</a>
    </div>
  </div>


  <div class="main-content">
    <div class="strecord">
      <h1>Student Information</h1>
      <div class="search-container1">
        <i class="fa-solid fa-magnifying-glass"></i><br><br>
        <input type="search" id="searchInput" placeholder="Search ID/student here...">
      </div><br><br>  
      
      <table id="dataTable">
        <thead>
          <tr>
            <th>Student ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Middle Name</th>
            <th>Birthday</th>
            <th>Age</th>
            <th>Email</th>
            <th>Address</th>
            <th>Program</th>
            <th>Year level</th>
          </tr>
        </thead>
        <tbody>
        <?php
        // Fetch students data using prepared statements
        $sql = "SELECT * FROM students";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if (!$result) {
                die("Database query failed: " . mysqli_error($con));
            }

            $index = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $stid = htmlspecialchars($row['stid']);
                echo '<tr id="student-row-'.$index.'" onclick="togglePayment('.$index.')">';
                echo '<td>'.'s'. $stid .'</td>';
                echo '<td>'. htmlspecialchars($row['lname']) .'</td>';
                echo '<td>'. htmlspecialchars($row['fname']) .'</td>';
                echo '<td>'. htmlspecialchars($row['mname']) .'</td>';
                echo '<td>'. htmlspecialchars(date('F j, Y', strtotime($row['bday']))) .'</td>';
                echo '<td>'. htmlspecialchars($row['age']) .'</td>';
                echo '<td>'. htmlspecialchars($row['email']) .'</td>';
                echo '<td>'. htmlspecialchars($row['address']) .'</td>';
                echo '<td>'. htmlspecialchars($row['program']) .'</td>';
                echo '<td>'. htmlspecialchars($row['level']) .'</td>';
                echo '</tr>';

                // Fetch payments for each student using prepared statements
                $payment_sql = "SELECT * FROM payments WHERE student_id = ?";
                $stmt2 = mysqli_prepare($con, $payment_sql);
                if ($stmt2) {
                    mysqli_stmt_bind_param($stmt2, "i", $stid);
                    mysqli_stmt_execute($stmt2);
                    $payment_result = mysqli_stmt_get_result($stmt2);
                    $payments = mysqli_fetch_all($payment_result, MYSQLI_ASSOC);
                    mysqli_stmt_close($stmt2);
                } else {
                    die("Error preparing statement: " . mysqli_error($con));
                }

                // Fetch alist data using prepared statements
                $alist_sql = "SELECT * FROM alist";
                $stmt3 = mysqli_prepare($con, $alist_sql);
                if ($stmt3) {
                    mysqli_stmt_execute($stmt3);
                    $alist_result = mysqli_stmt_get_result($stmt3);
                    $alist_payments = mysqli_fetch_all($alist_result, MYSQLI_ASSOC);
                    mysqli_stmt_close($stmt3);
                } else {
                    die("Error preparing statement: " . mysqli_error($con));
                }

                $total_amount = 0;
                $alist_total_amount = 0;

                echo '<tr id="payment-'.$index.'" class="payment-details" style="display:none;">
                        <td colspan="10">
                          <form action="" method="POST">
                            <input type="hidden" name="stid" value="'.$stid.'">
                            <input type="hidden" name="csrf_token" value="'.$_SESSION['csrf_token'].'">';

                if ($payments || $alist_payments) {
                    echo '<table class="list">
                            <thead>
                              <tr>
                                <th>Payment Name</th>
                                <th>Amount</th>
                              </tr>
                            </thead>
                            <tbody>';

                    // Display payments from payments table
                    foreach ($payments as $payment) {
                        $amount = $payment['amount'];
                        $total_amount += $amount;
                        echo '<tr>';
                        echo '<td>'. htmlspecialchars($payment['payment_name']) .'</td>';
                        echo '<td>'. number_format($amount, 0) .'</td>';
                        echo '</tr>';
                    }

                    // Display payments from alist table
                    foreach ($alist_payments as $alist_payment) {
                        $amount1 = $alist_payment['amount1'];
                        $alist_total_amount += $amount1;
                        echo '<tr>';
                        echo '<td>'. htmlspecialchars($alist_payment['payment1']) .'</td>';
                        echo '<td>'. number_format($amount1, 0) .'</td>';
                        echo '</tr>';
                    }

                    // Display total amount
                    echo '<tr style="font-weight: bold; background-color: #f2f2f2;">
                              <td>Total Amount</td>
                              <td>'. number_format($total_amount + $alist_total_amount, 0) .'</td>
                          </tr>';
                    echo '</tbody>
                          </table>';
                }

                // Add a new row for the input fields and the "Add Payment" button
                echo '<table class="list">
                        <tbody>
                          <tr>
                            <td><input type="text" name="payment_name" placeholder="Payment Name" required></td>
                            <td><input type="number" name="amount" step="0.01" placeholder="Amount" required></td>
                          </tr>
                          <tr>
                            <td colspan="2"><button class="stable" name="add_payment" type="submit">Add Payment</button></td>
                          </tr>
                        </tbody>
                      </table>';

                echo '</form>
                      </td>
                    </tr>';
                $index++;
            }
            mysqli_stmt_close($stmt);
        } else {
            die("Error preparing statement: " . mysqli_error($con));
        }
        ?>
        </tbody>
      </table>
      <button class="allist" id="modal5">Add list</button>
      <div id="noRecordMessage" style="display: none;">No record found</div>
    </div>
  </div>

  <!-- Modal for alist -->
  <div id="paymentModal5" class="modal5">
    <div class="modal-content5">
      <h2>Add Payment List</h2>
      <form id="paymentForm" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div id="paymentFields">
          <div class="payment-item">
            <input type="text" name="payment1[]" placeholder="Payment Name" required>
            <input type="number" name="amount1[]" step="0.01" placeholder="Amount" required>
          </div>
        </div>
        <button type="button" onclick="addPaymentField()">Add Another</button>
        <button type="submit" name="alist">Submit</button>
      </form>
    </div>
  </div>

  <script>
    function togglePayment(index) {
      var paymentRow = document.getElementById("payment-" + index);
      var currentDisplay = paymentRow.style.display;
      paymentRow.style.display = (currentDisplay === 'none' || currentDisplay === '') ? 'table-row' : 'none';
    }

    document.getElementById('searchInput').addEventListener('keyup', function() {
      var input = this.value.toLowerCase();
      var rows = document.querySelectorAll('#dataTable tbody tr');
      var noRecordMessage = document.getElementById('noRecordMessage');
      var found = false;

      rows.forEach(function(row, index) {
        if (row.id.startsWith("student-row")) {
          var text = row.innerText.toLowerCase();
          var paymentRow = document.getElementById("payment-" + index);
          row.style.display = text.includes(input) ? '' : 'none';
          paymentRow.style.display = 'none';
          if (text.includes(input)) found = true;
        }
      });

      noRecordMessage.style.display = found ? 'none' : 'block';
    }); 


    // Show modal when "Add list" button is clicked
    document.getElementById('modal5').addEventListener('click', function() {
      document.getElementById('paymentModal5').style.display = 'flex';
    });

    // Close modal when clicking outside the modal
    window.addEventListener('click', function(event) {
      if (event.target === document.getElementById('paymentModal5')) {
        document.getElementById('paymentModal5').style.display = 'none';
      }
    });

    // Add more payment fields dynamically
    function addPaymentField() {
      const paymentFields = document.getElementById('paymentFields');
      const newField = document.createElement('div');
      newField.className = 'payment-item';
      newField.innerHTML = `
        <input type="text" name="payment1[]" placeholder="Payment Name" required>
        <input type="number" name="amount1[]" step="0.01" placeholder="Amount" required>
      `;
      paymentFields.appendChild(newField);
    }
  </script>
  <script src="../js/script.js"></script>
</body>
</html>