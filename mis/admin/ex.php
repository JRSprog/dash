<?php
session_start();
include '../connect.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$updateMessage = '';

if (isset($_GET['stid'])) {
    $id = intval($_GET['stid']); // Ensure the ID is an integer
}

if (isset($_POST['submit'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $id = intval($_POST['stid']); // Ensure the ID is an integer
    $nbalance = floatval($_POST['newBalance']); // Ensure the balance is a float

    // Update student balance
    $updateQuery = "UPDATE students SET balance = ? WHERE stid = ?";
    $stmt = mysqli_prepare($con, $updateQuery);
    mysqli_stmt_bind_param($stmt, 'di', $nbalance, $id);
    $res = mysqli_stmt_execute($stmt);

    if ($res) {
        $updateMessage = 'success';
    } else {
        $updateMessage = 'error: ' . mysqli_error($con);
    }

    // History insertion
    $sels = $_POST['sel'];
    $new = floatval($_POST['cBalance']); // Ensure the balance is a float
    $date = $_POST['date'];

    // Check if the student exists
    $checkStudentQuery = "SELECT stid FROM students WHERE stid = ?";
    $stmt = mysqli_prepare($con, $checkStudentQuery);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) == 0) {
        die("Error: Student ID '$id' does not exist in the students table.");
    }

    // Insert into history table
    $insertQuery = "INSERT INTO history (sel, cbalance, date, studentId) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $insertQuery);
    mysqli_stmt_bind_param($stmt, 'sdsi', $sels, $new, $date, $id);
    $insert = mysqli_stmt_execute($stmt);

    if (!$insert) {
        die("Error inserting into history: " . mysqli_error($con));
    }
}

if (isset($_POST['action'])) {
  $rname = mysqli_real_escape_string($con, $_POST['rname']);
  $rstid = mysqli_real_escape_string($con, $_POST['rstid']);
  $rparticular = mysqli_real_escape_string($con, $_POST['rparticular']);
  $ramount = floatval($_POST['ramount']); // Ensure amount is treated as a float
  $rdate = mysqli_real_escape_string($con, $_POST['rdate']);
  
  // Determine the type based on the action or default to "Cashier" if empty
  if ($_POST['action'] == 'approve') {
      $rtype = "Hma/Aub"; // Set type to "Hma/Aub" if approved
  } else {
      $rtype = "Cashier"; // Set type to "Cashier" if rejected or if type is empty
  }

  // If the type is empty (e.g., not provided in the form), default to "Cashier"
  if (empty($rtype)) {
      $rtype = "Cashier";
  }

  // Debugging: Check the value of $rtype
  echo "<script>console.log('Type: " . $rtype . "');</script>";

  // Prepare statement for inserting into record table
  $stmt1 = $con->prepare("INSERT INTO record (name, stid, particular, amount, date, type) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt1->bind_param("sssdss", $rname, $rstid, $rparticular, $ramount, $rdate, $rtype);

  if ($stmt1->execute()) {
      $insertMessage = "success";
      // Debugging: Log success message
      echo "<script>console.log('Record inserted successfully.');</script>";
  } else {
      $insertMessage = "error: " . $stmt1->error;
      // Debugging: Log error message
      echo "<script>console.log('Error inserting record: " . $stmt1->error . "');</script>";
  }

  $stmt1->close();

  $stid = intval($_POST['stid']);
  $status = ($_POST['action'] == 'approve') ? 'approved' : 'rejected';

  $updateStatusQuery = "UPDATE approval SET status = ? WHERE stid = ?";
  $stmt = mysqli_prepare($con, $updateStatusQuery);
  mysqli_stmt_bind_param($stmt, 'si', $status, $stid);
  $res = mysqli_stmt_execute($stmt);

  if ($res) {
      echo '<script>
      function verifyAction() {
          let confirmAction = confirm("Are you sure you want to proceed?");
          if (confirmAction) {
              alert("Action verified successfully!");
          } else {
              alert("Action canceled.");
          }
      }
      </script>';
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Approval</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="../uploads/blogo.png" type="x-icon">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/styles.css">
  <!-- SweetAlert Library -->
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
      <a href="#">Option 1</a>
      <a href="#">Option 2</a>
      <a href="#">Option 3</a>
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
    <a href="approval.php" class="sidebar-item"><i class="fa-solid fa-credit-card"></i>&nbsp; Online Approval</a>
    <a href="strecord.php" class="sidebar-item"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Student Information</a>
    <a href="payrecord.php" class="sidebar-item"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Payment Record</a>
    <a href="onfees.php" class="sidebar-item"><i class="fa-solid fa-clipboard-list"></i>&nbsp; Ongoing Fees</a>
  </div>
</div>

<div class="main-content">
  <div class="parent">
    <h1 style="text-align: center;">Approval Payment Online</h1>
    <?php
    // Fetch only pending approvals
    $kunin = "SELECT * FROM approval WHERE status = 'pending';";
    $hasa = mysqli_query($con, $kunin);
    while ($linya = mysqli_fetch_assoc($hasa)) {
        echo '<div class="child" id="child-' . htmlspecialchars($linya['stid']) . '">
            <br>';
        echo '<form method="post" action="" class="approval-form" onsubmit="return verifyAction(this);">';
        echo '<p>Name: <strong><input type="text" name="rname" value="' . htmlspecialchars($linya['name']) . '"></strong></p>';
        echo '<p>Student ID: <strong><input type="text" name="rstid" value="' . htmlspecialchars($linya['stid']) . '" readonly></strong></p>';
        echo '<p>Particular: <strong><input type="text" name="rparticular" value="' . htmlspecialchars($linya['particular']) . '"></strong></p>';
        echo '<p>Proof of Screenshot [click image]:</p>';
        echo '<img src="../uploads/blogo.png" class="zoom-image" id="zoom-img">';
        echo '<p>Amount: <strong><input type="number" name="ramount" value="' . htmlspecialchars($linya['amount']) . '"></strong></p>';
        $displayDate = date('F j, Y', strtotime($linya['date']));
        echo '<p>Date: <strong><input type="text" name="rdate_display" value="' . htmlspecialchars($displayDate) . '" readonly></strong></p>';
        
        echo '<input type="hidden" name="rdate" value="' . htmlspecialchars($linya['date']) . '">';
        echo '<input type="hidden" name="stid" value="' . htmlspecialchars($linya['stid']) . '">
              <button type="submit" name="action" value="approve" class="app">Approve</button>
              <button type="submit" name="action" value="reject" class="reject">Reject</button>
            </form>';
        echo '</div>';
    }
    ?>
  </div>

  <div class="form-container">
    <h1>Student Balance</h1>
    <div class="search-container1">
      <i class="fa-solid fa-magnifying-glass"></i><br><br>
      <input type="search" id="searchInput" placeholder="Search here...">
    </div>
    <table id="dataTable">
      <thead>
        <tr>
          <th>Student ID</th>
          <th>Lastname</th>
          <th>Firstname</th>
          <th>Middlename</th>
          <th>Balance</th>
          <th>Action</th>
        </tr>
      </thead>
      <?php
      $select = "SELECT * FROM students";
      $sql = mysqli_query($con, $select);
      while ($row = mysqli_fetch_assoc($sql)) {
        echo '<tbody>';
        echo '<tr>';
        echo '<td>'.'s' . htmlspecialchars($row['stid']) . '</td>';
        echo '<td>' . htmlspecialchars($row['lname']) . '</td>';
        echo '<td>' . htmlspecialchars($row['fname']) . '</td>';
        echo '<td>' . htmlspecialchars($row['mname']) . '</td>';
        echo '<td>' . htmlspecialchars(number_format($row['balance'])) . '</td>';
        echo '<td><button class="update" id="modal" data-id="' . $row['stid'] . '" data-stid="' . htmlspecialchars($row['stid']) . '" data-balance="' . htmlspecialchars($row['balance']) . '">Update Balance</button></td>';
        echo '</tr>';
        echo '</tbody>';
      }
      ?>
    </table>
  </div>

  <!-- Modal1 -->
  <div class="modal" id="updateModal">
    <div class="modal-content">
      <span class="close1" id="closeModal">&times;</span>
      <h2>Update Student Balance</h2><br>
      <form id="updateForm" method="post">
        <input type="hidden" id="studentId" name="id" required>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <label for="studentId">Student ID:</label>
        <input type="text" id="stid" name="stid" required readonly><br><br>

        <label for="">Particular :</label><br><br>
        <select name="sel" style="padding: 10px; width:80%;">
             <option value="">select here....</option>
             <option value="Prelim exam">Prelim exam</option>
             <option value="Midterm exam">Midterm exam</option>
             <option value="Final exam">Final exam</option>
             <option value="Stage play[Philippines Stagers]">Stage play[Philippines Stagers]</option>
        </select><br><br>

        <label for="cBalance">Current Balance:</label>
        <input type="text" id="cBalance" name="cBalance" required readonly><br><br>

        <label for="newBalance">New Balance:</label>
        <input type="number" id="newBalance" name="newBalance" required><br><br>
           
        <input type="datetime-local" id="datetime" name="date"><br><br>

        <button type="submit" name="submit">Submit</button>
      </form>
    </div>
  </div>

  <!-- Image overlay for zoom -->
  <div class="overlay2" id="overlay2">
    <span class="close2" id="close2">&times;</span>
    <img class="overlay-image" id="overlay-image" />
  </div>
</div>

<script>
// Function to verify action before form submission
function verifyAction(form) {
    let confirmAction = confirm("Are you sure you want to proceed?");
    if (confirmAction) {
        return true; // Allow form submission
    } else {
        alert("Action canceled.");
        return false; // Prevent form submission
    }
}

// Get the modal and buttons
var updateModal = document.getElementById("updateModal");
var updateClose = document.getElementById("closeModal");
var editButtons = document.querySelectorAll(".update");

editButtons.forEach(function(button) {
    button.addEventListener("click", function() {
        var stid = this.getAttribute("data-stid");
        var amount = this.getAttribute("data-balance");

        document.getElementById("stid").value = stid;
        document.getElementById("cBalance").value = amount; 
        updateModal.style.display = "block";
    });
});
updateClose.addEventListener("click", function() {
    updateModal.style.display = "none";
});

window.addEventListener("click", function(event) {
    if (event.target === updateModal) {
        updateModal.style.display = "none";
    }
});

<?php if ($updateMessage == 'success'): ?>
    Swal.fire({
        icon: 'success',
        title: 'Balance Updated',
        text: 'The student\'s balance has been successfully updated!',
    });
<?php elseif ($updateMessage == 'error'): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'There was an issue updating the balance. Please try again.',
    });
<?php endif; ?>

// Function to set today's date and time automatically
window.onload = function() {
  const today = new Date(); 
  const dd = String(today.getDate()).padStart(2, '0'); 
  const mm = String(today.getMonth() + 1).padStart(2, '0'); 
  const yyyy = today.getFullYear(); 
  const hours = String(today.getHours()).padStart(2, '0');
  const minutes = String(today.getMinutes()).padStart(2, '0'); 
  const formattedDateTime = `${yyyy}-${mm}-${dd}T${hours}:${minutes}`;
  document.getElementById('datetime').value = formattedDateTime;
}
</script>

<script src="../js/script.js"></script>
</body>
</html>