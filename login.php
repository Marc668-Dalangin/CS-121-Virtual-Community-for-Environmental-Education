<?php
session_start();
require 'db.php';

if (isset($_POST['login'])) {
    $usernameOrEmail = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email");
    $stmt->execute(['email' => $usernameOrEmail]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['role'] = 'admin';
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Invalid Admin Credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Greenhorizon - Admin Login</title>
    <link rel="stylesheet" href="style.css?v=1.3">
    <style>
        .input-group {
            position: relative;
        }
        .input-group input {
            width: calc(100% - 63.5px);
            padding-right: 50px;
        }
        .input-group button {
            position: absolute;
            right: 0px;
            top: 35%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: blue;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <h1>Greenhorizon</h1>
    </header>
    <div class="login-container">
        <h2>Admin Login</h2>
        <form action="" method="POST">
            <label for="username">Admin Email:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password:</label>
            <div class="input-group">
                <input type="password" id="password" name="password" required>
                <button type="button" id="togglePassword">Show</button>
            </div>
            <button type="submit" name="login">Login</button>
            <a href="forgot_password.php">Forgot Password?</a>
        </form>
    </div>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const toggleButton = this;

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleButton.textContent = "Hide";
            } else {
                passwordInput.type = "password";
                toggleButton.textContent = "Show";
            }
        });
    </script>
</body>
</html>
