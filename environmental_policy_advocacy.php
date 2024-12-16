<?php
session_start();
require 'db.php'; // Include your database connection

// Fetch admin's email based on the provided email
$admin_email = '';
$stmt = $pdo->query("SELECT * FROM admins WHERE email = 'greenhorizon91@gmail.com' LIMIT 1");
if ($stmt) {
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    $admin_email = $admin['email'] ?? 'admin email not set'; // Default value if email is not found
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard_style.css?v=1.5">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
   body {
    font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    height: 100vh;
    background: url('images/bsu-main.png') no-repeat center center fixed; /* Set the background image */
    background-size: cover; /* Ensure the image covers the entire background */
}

/* Navbar styling */
.navbar {
    background: url('images/green-leaf.jpg') no-repeat center center fixed;
    background-size: cover; /* This makes sure the image covers the entire background */
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid greenyellow;
    position: fixed; /* Make navbar fixed */
    top: 0; /* Stick to the top */
    left: 0; /* Align to the left */
    right: 0; /* Span full width */
    z-index: 1000; /* Make sure it's above other elements */
    box-shadow: 2px 0 20px rgba(0, 0, 0, 0.7); /* Optional shadow */
    font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
}

.brand h1 {
    color: #006400;
    margin: 0; /* Remove default margin */
}

.nav-icons {
    display: flex; /* Use flexbox for alignment */
    align-items: center; /* Center items vertically */
    gap: 20px; /* Space between icons */
}

.nav-icons a {
    display: flex; /* Use flexbox for icon alignment */
    align-items: center; /* Center icon vertically */
    justify-content: center; /* Center icon horizontally */
    text-decoration: none; /* Remove underline from links */
    color: black; /* Default color for icons */
    font-size: 18px; /* Font size for icons */
}

.nav-icons a:hover {
    color: #45a049; /* Change color on hover */
}

.account-btn {
    background-color: #228b22; /* Green background for ACC button */
    color: white;
    font-size: 16px;
    border: none;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

/* Main container styling */
main {
    display: flex;
    height: calc(100vh - 60px); /* Adjust height for the navbar */
    margin-top: 60px; /* Add margin-top to avoid overlap with fixed navbar */
}
/* Settings button styling */
.settings-btn {
    position: fixed; /* Make it fixed */
    bottom: 20px; /* Position from the bottom */
    right: 20px; /* Position from the right */
    padding: 10px 15px; /* Add padding for a larger button */
    background-color: #228b22; /* Background color */
    color: white; /* Font color */
    border: none; /* No border */
    border-radius: 5px; /* Rounded corners */
    cursor: pointer; /* Add cursor effect */
    z-index: 1000; /* Ensure it's above other content */
}

.settings-btn:hover {
    background-color: #228b22; /* Hover color */
}
/* ACC Button Style */
.account-btn {
    background-color: #4CAF50; /* Green background for ACC button */
    color: white;
    font-size: 16px;
    border: none;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.account-btn:hover {
    background-color: #45a049; /* Slightly darker green on hover */
}

/* Dropdown Menu Style */
.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none; /* Initially hidden */
    position: absolute;
    right: 0; /* Align dropdown to the right */
    background-color: #ffffff; /* White background */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Shadow for depth */
    border-radius: 5px;
    padding: 10px 0;
    min-width: 150px; /* Minimum width */
    z-index: 1;
    font-family: Arial, sans-serif;
}

/* Dropdown Links */
.dropdown-content a {
    color: #333; /* Dark text color */
    padding: 8px 16px;
    text-decoration: none;
    display: block;
    transition: background-color 0.2s ease;
}

.dropdown-content a:hover {
    background-color: #f1f1f1; /* Light gray background on hover */
}



/* Style for the overlay container */
.overlay-container {
    display: none; /* Hidden by default */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 105%;
    background-color: rgba(0, 0, 0, 0.6); /* Semi-transparent background */
    z-index: 2; /* Sit on top of other content */
    justify-content: center;
    align-items: center;
}

.overlay-content {
    background-color: white;
    width: 60%;
    max-width: 800px;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 4px 8px rgba(0,0,0,0.2);
    position: relative;
    overflow: auto;
}

.overlay-content h2 {
    font-size: 24px;
    margin-bottom: 10px;
}

.overlay-content p {
    font-size: 16px;
    color: #555;
}

.close-overlay {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 24px;
    cursor: pointer;
    color: #aaa;
}

.close-overlay:hover {
    color: #000;
}

/* New styles for images */
.overlay-content img {
    width: 80%; /* Set the width to 80% of the overlay content */
    max-height: 300px; /* Limit the height to 300px */
    object-fit: cover; /* Maintain aspect ratio */
    margin: 10px 0; /* Add some margin for spacing */
}
/* Additional styles for the modal */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    overflow: auto;
}

.modal-content h2 {
    text-align: center;
    font-size: 24px;
    margin-bottom: 20px;
    border-bottom: 2px solid #888;
    padding-bottom: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 10px;
    text-align: left;
    border: 1px solid #888;
}

th {
    background-color: #f2f2f2;
    font-weight: bold;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* Dropdown styles */
.dropdown {
    position: relative;
    display: inline-block;
}

/* Dropdown styles */
.dropdown-content {
display: none; /* Keep it hidden by default */
position: absolute;
right: 0; /* Aligns the dropdown to the right side */
background-color: rgba(255, 255, 255, 0.8); /* White background with 80% opacity */
box-shadow: 0px 4px 8px rgba(0,0,0,0.2); /* Add a shadow */
z-index: 1; /* Ensure it sits on top */
border-radius: 10px; /* Rounded corners */
margin-top: 10px; /* Space between button and dropdown */
min-width: 120px; /* Ensure the dropdown has a minimum width */
}

.dropdown-content a {
color: red;
padding: 12px 20px; /* Increased padding to left and right */
text-decoration: none;
display: block;
white-space: nowrap; /* Prevents text from wrapping */
}

.dropdown-content a:hover {
text-decoration: underline; /* Underline only on hover */
}


.main-container {
display: flex;
flex-direction: column; /* Stack input box above the posts container */
align-items: center; /* Center horizontally */
justify-content: center; /* Center vertically if needed */
width: 100%; /* Full width of the viewport */
height: 100vh; /* Full height of the viewport */
}



.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
} /* Content Area */
        .content {
            text-align: center;
            margin-top: 100px; /* To account for fixed navbar height */
            padding: 20px;
        }

        .content img {
            display: block;
            margin: 20px auto;
            max-width: 80%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <!-- Header/Navbar -->
    <header class="navbar">
        <div class="brand">
            <h1>GreenHorizon</h1>
        </div>
        <div class="nav-icons">
            <!-- Home Icon -->
            <a href="dashboard.php" class="home-icon">
            <i class="fa-solid fa-house fa-xl" style="color: #228b22;"></i>
            </a>
            <!-- About Icon -->
            <a href="about2.php" class="about-icon">
                <i class="fa-solid fa-circle-info fa-xl" style="color: #228b22;"></i>
            </a>
            
            <div class="dropdown">
    <button class="account-btn">ACC</button>
    <div class="dropdown-content" id="acc-dropdown">
        <a href="#" class="account-settings">Account Settings</a>
        <a href="logout.php">Logout</a>
    </div>
</div><!-- Overlay Container for Account Settings -->
<div id="accountSettingsOverlay" class="overlay-container">
    <div class="overlay-content">
        <span class="close-overlay" onclick="closeAccountSettings()">&times;</span>
        <h2>Account Settings</h2>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($admin_email); ?></p>
        <!-- You can add more account settings details here -->
    </div>

<script>document.querySelector('.account-btn').addEventListener('click', function() {
    const dropdown = document.getElementById('acc-dropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block'; // Toggle display
});

// Close the dropdown if clicked outside of it
window.onclick = function(event) {
    if (!event.target.matches('.account-btn')) {
        const dropdowns = document.getElementsByClassName("dropdown-content");
        for (let i = 0; i < dropdowns.length; i++) {
            dropdowns[i].style.display = "none"; // Hide all dropdowns
        }
    }
}</script><script>
        // Function to open overlay
document.querySelectorAll('.open-overlay').forEach(item => {
    item.addEventListener('click', function(event) {
        event.preventDefault();
        const topic = item.getAttribute('data-topic');
        document.getElementById('overlayTitle').innerText = topic;
        
        // Set the image based on the topic
        let imageUrl = '';
        switch (topic) {
            case 'Sustainable Practices Awareness':
                imageUrl = 'images/Img1.jpg';
                break;
            case 'Eco-friendly Technology Integration':
                imageUrl = 'images/Img2.jpg';
                break;
            case 'Community-driven Environmental Action':
                imageUrl = 'images/Img3.jpg';
                break;
            case 'Climate Change Education':
                imageUrl = 'images/Img4.jpg';
                break;
            case 'Biodiversity Conservation':
                imageUrl = 'images/Img5.jpg';
                break;
            case 'Environmental Policy Advocacy':
                imageUrl = 'images/Img6.jpg';
                break;
            default:
                imageUrl = ''; // Fallback if no match
        }
        
        // Set the image source
        document.getElementById('overlayImage').src = imageUrl;
        document.getElementById('overlayText').innerText = `Content for ${topic} will be added here.`;
        document.getElementById('overlayContainer').style.display = 'flex';
    });
});

        // Function to close overlay
        function closeOverlay() {
            document.getElementById('overlayContainer').style.display = 'none';
        }

         // Function to open overlay for Account Settings
    document.querySelector('.account-settings').addEventListener('click', function(event) {
        event.preventDefault();
        document.getElementById('accountSettingsOverlay').style.display = 'flex';
    });

    // Function to close overlay for Account Settings
    function closeAccountSettings() {
        document.getElementById('accountSettingsOverlay').style.display = 'none';
    }

        // Close the overlay if clicked outside the content area
        window.onclick = function(event) {
            const overlayContainer = document.getElementById('overlayContainer');
            if (event.target === overlayContainer) {
                overlayContainer.style.display = "none";
            }
        }
    </script>

    </header>

    <!-- Main Content -->
    <div class="content">
    <img src="images/En1.png" alt="About Image 1">
        <img src="images/En2.png" alt="About Image 2">
        <img src="images/En3.png" alt="About Image 3">
        <img src="images/En4.png" alt="About Image 4">
    </div>
    
</body>
</html>
