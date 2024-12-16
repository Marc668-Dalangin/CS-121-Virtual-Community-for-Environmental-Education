<?php
session_start();
require 'db.php';

$admin_email = '';
$stmt = $pdo->query("SELECT * FROM admins WHERE email = 'greenhorizon91@gmail.com' LIMIT 1");
if ($stmt) {
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    $admin_email = $admin['email'] ?? 'admin email not set';
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
    background: url('images/bsu-main.png') no-repeat center center fixed;
    background-size: cover;
}

.navbar {
    background: url('images/green-leaf.jpg') no-repeat center center fixed;
    background-size: cover;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid greenyellow;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    box-shadow: 2px 0 20px rgba(0, 0, 0, 0.7);
    font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
}

.brand h1 {
    color: #006400;
    margin: 0;
}

.nav-icons {
    display: flex;
    align-items: center;
    gap: 20px;
}

.nav-icons a {
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: black;
    font-size: 18px;
}

.nav-icons a:hover {
    color: #45a049;
}

.account-btn {
    background-color: #228b22;
    color: white;
    font-size: 16px;
    border: none;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
}


main {
    display: flex;
    height: calc(100vh - 60px);
    margin-top: 60px;
}

.settings-btn {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 10px 15px;
    background-color: #228b22;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    z-index: 1000;
}

.settings-btn:hover {
    background-color: #228b22;
}

.account-btn {
    background-color: #4CAF50;
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
    background-color: #45a049;
}

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    background-color: #ffffff;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    border-radius: 5px;
    padding: 10px 0;
    min-width: 150px;
    z-index: 1;
    font-family: Arial, sans-serif;
}

.dropdown-content a {
    color: #333;
    padding: 8px 16px;
    text-decoration: none;
    display: block;
    transition: background-color 0.2s ease;
}

.dropdown-content a:hover {
    background-color: #f1f1f1;
}

.overlay-container {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 105%;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 2;
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

.overlay-content img {
    width: 80%;
    max-height: 300px;
    object-fit: cover;
    margin: 10px 0; 
}

.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto; 
    background-color: rgba(0,0,0,0.4);
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

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
display: none;
position: absolute;
right: 0;
background-color: rgba(255, 255, 255, 0.8);
box-shadow: 0px 4px 8px rgba(0,0,0,0.2);
z-index: 1;
border-radius: 10px;
margin-top: 10px;
min-width: 120px;
}

.dropdown-content a {
color: red;
padding: 12px 20px;
text-decoration: none;
display: block;
white-space: nowrap;
}

.dropdown-content a:hover {
text-decoration: underline;
}


.main-container {
display: flex;
flex-direction: column;
align-items: center;
justify-content: center;
width: 100%;
height: 100vh;
}



.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
        .content {
            text-align: center;
            margin-top: 100px;
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
    <header class="navbar">
        <div class="brand">
            <h1>GreenHorizon</h1>
        </div>
        <div class="nav-icons">
            <a href="dashboard.php" class="home-icon">
            <i class="fa-solid fa-house fa-xl" style="color: #228b22;"></i>
            </a>
            <a href="about.php" class="about-icon">
                <i class="fa-solid fa-circle-info fa-xl" style="color: #228b22;"></i>
            </a>
            
            <div class="dropdown">
    <button class="account-btn">ACC</button>
    <div class="dropdown-content" id="acc-dropdown">
        <a href="#" class="account-settings">Account Settings</a>
        <a href="logout.php">Logout</a>
    </div>
</div>
<div id="accountSettingsOverlay" class="overlay-container">
    <div class="overlay-content">
        <span class="close-overlay" onclick="closeAccountSettings()">&times;</span>
        <h2>Account Settings</h2>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($admin_email); ?></p>
    </div>

<script>document.querySelector('.account-btn').addEventListener('click', function() {
    const dropdown = document.getElementById('acc-dropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
});


window.onclick = function(event) {
    if (!event.target.matches('.account-btn')) {
        const dropdowns = document.getElementsByClassName("dropdown-content");
        for (let i = 0; i < dropdowns.length; i++) {
            dropdowns[i].style.display = "none"; 
        }
    }
}</script><script>
        
document.querySelectorAll('.open-overlay').forEach(item => {
    item.addEventListener('click', function(event) {
        event.preventDefault();
        const topic = item.getAttribute('data-topic');
        document.getElementById('overlayTitle').innerText = topic;
        
        
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
                imageUrl = '';
        }
        
        
        document.getElementById('overlayImage').src = imageUrl;
        document.getElementById('overlayText').innerText = `Content for ${topic} will be added here.`;
        document.getElementById('overlayContainer').style.display = 'flex';
    });
});


        function closeOverlay() {
            document.getElementById('overlayContainer').style.display = 'none';
        }

        
    document.querySelector('.account-settings').addEventListener('click', function(event) {
        event.preventDefault();
        document.getElementById('accountSettingsOverlay').style.display = 'flex';
    });

    
    function closeAccountSettings() {
        document.getElementById('accountSettingsOverlay').style.display = 'none';
    }

        
        window.onclick = function(event) {
            const overlayContainer = document.getElementById('overlayContainer');
            if (event.target === overlayContainer) {
                overlayContainer.style.display = "none";
            }
        }
    </script>

    </header>

    
    <div class="content">
        <img src="images/about1.png" alt="About Image 1">
        <img src="images/about2.png" alt="About Image 2">
        <img src="images/about3.png" alt="About Image 3">
        <img src="images/about4.png" alt="About Image 4">
        <img src="images/about5.png" alt="About Image 5">
        <img src="images/about6.png" alt="About Image 6">
    </div>
    
</body>
</html>
