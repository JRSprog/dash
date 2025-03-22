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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Record</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="../uploads/blogo.png" type="x-icon">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/styles.css">
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
        <p class="sidebar-text"></p>
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
    <h1>Student Record</h1>
    <div class="search-container1">
            <i class="fa-solid fa-magnifying-glass"></i><br><br>
            <input type="search" id="searchInput" placeholder="Search here...">
            <button class="voice" id="recognition"><i class="fa-solid fa-microphone"></i></button>
        </div><br><br>  
    <table  id="dataTable">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Particular</th>
                <th>Amount</th>
                <th>Payment Date</th>
                <th>Payment type</th>
            </tr>
        </thead>
        <tbody>
          <?php 
          $select = "SELECT * FROM record ORDER BY date ASC";
          $result = mysqli_query($con,$select);
          while($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
                echo '<td>'.'s'. htmlspecialchars($row['stid']) .'</td>';
                echo '<td>'. htmlspecialchars($row['name']) .'</td>';
                echo '<td>'. htmlspecialchars($row['particular']) .'</td>';
                echo '<td>'. htmlspecialchars($row['amount']) .'</td>';
                echo '<td>'. htmlspecialchars(date('F j, Y', strtotime($row['date']))) .'</td>';
                echo '<td>'. htmlspecialchars($row['type']) .'</td>';
            echo '</tr>';
          }
          ?> 
        </tbody>
    </table>
</div>
</div>

<script>
  // Search functionality
  document.getElementById('searchInput').addEventListener('keyup', function() {
    var input = this.value.toLowerCase();
    var rows = document.querySelectorAll('#dataTable tbody tr');
    var found = false;

    rows.forEach(function(row) {
        var cells = row.getElementsByTagName('td');
        var match = false;

        for (var i = 0; i < cells.length; i++) {
            if (cells[i].textContent.toLowerCase().indexOf(input) > -1) {
                match = true;
                found = true;
                break;
            }
        }

        row.style.display = match ? '' : 'none';
    });

    // Speak feedback if no data is found
    if (!found && input.trim() !== '') {
      speak("There is no data, boss.");
    }
  });

  // Voice command functionality
  const recognitionButton = document.getElementById('recognition');
  const searchInput = document.getElementById('searchInput');

  // Check if the browser supports the Web Speech API
  if ('webkitSpeechRecognition' in window) {
    const recognition = new webkitSpeechRecognition();
    recognition.continuous = false; // Stop after one command
    recognition.interimResults = false; // Only final results
    recognition.lang = 'en-US'; // Set language

    recognitionButton.addEventListener('click', () => {
      // Speak "Waiting for your command, boss" first
      speak("Waiting for your command, boss.", () => {
        // Start voice recognition after speaking is done
        recognition.start();
        recognitionButton.innerHTML = '<i class="fa-solid fa-microphone-slash"></i>'; // Change icon
      });
    });

    recognition.onresult = (event) => {
      const transcript = event.results[0][0].transcript; // Get the recognized text
      searchInput.value = transcript; // Set the search input value
      searchInput.dispatchEvent(new Event('keyup')); // Trigger the search
      recognitionButton.innerHTML = '<i class="fa-solid fa-microphone"></i>'; // Reset icon
    };

    recognition.onerror = (event) => {
      console.error('Voice recognition error:', event.error);
      recognitionButton.innerHTML = '<i class="fa-solid fa-microphone"></i>'; // Reset icon
    };

    recognition.onend = () => {
      recognitionButton.innerHTML = '<i class="fa-solid fa-microphone"></i>'; // Reset icon
    };
  } else {
    // If the browser doesn't support the Web Speech API
    recognitionButton.style.display = 'none'; // Hide the button
    console.warn('Your browser does not support the Web Speech API.');
  }

  // Function to speak text using SpeechSynthesis
  function speak(text, callback) {
    if ('speechSynthesis' in window) {
      const utterance = new SpeechSynthesisUtterance(text);
      utterance.voice = getFemaleVoice(); // Set a female voice
      utterance.rate = 1; // Normal speed
      utterance.pitch = 1; // Normal pitch

      // Call the callback after speaking is done
      utterance.onend = () => {
        if (callback) callback();
      };

      speechSynthesis.speak(utterance);
    } else {
      console.warn('Your browser does not support speech synthesis.');
    }
  }

  // Function to get a female voice
  function getFemaleVoice() {
    const voices = speechSynthesis.getVoices();
    const femaleVoice = voices.find(voice => voice.name.includes('Female') || voice.lang.includes('en-US'));
    return femaleVoice || voices[0]; // Fallback to the first available voice
  }

  // Load voices when the page loads
  window.speechSynthesis.onvoiceschanged = () => {
    console.log('Voices loaded');
  };
</script>
<script src="../js/script.js"></script>
</body>
</html>