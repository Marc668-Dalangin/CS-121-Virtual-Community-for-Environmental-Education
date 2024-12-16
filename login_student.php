<?php
session_start();
require 'db.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM students WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $student = $stmt->fetch();

    if ($student && password_verify($password, $student['password'])) {
        $_SESSION['user_id'] = $student['id'];
        $_SESSION['email'] = $student['email'];
        $_SESSION['full_name'] = $student['full_name'];
        $_SESSION['address'] = $student['address'];
        $_SESSION['role'] = 'student';
        header("Location: student_dashboard.php");
        exit();
    } else {
        echo "Invalid Student Credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Greenhorizon - Student Login</title>
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
        <h2>Login</h2>
        <form action="" method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password:</label>
            <div class="input-group">
                <input type="password" id="password" name="password" required>
                <button type="button" id="togglePassword">Show</button>
            </div>
            <button type="submit" name="login">Login</button>
            <a href="forgot_password_student.php">Forgot Password?</a>
            <a href="register.php">Don't have an account? Register here</a>
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
