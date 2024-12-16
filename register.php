<?php
require 'db.php';

if (isset($_POST['register'])) {
    $firstName = $_POST['first_name'];
    $middleInitial = $_POST['middle_initial'];
    $lastName = $_POST['last_name'];
    $fullName = $firstName . ' ' . $middleInitial . '. ' . $lastName;
    $contactNumber = $_POST['contact_number'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password === $confirmPassword) {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $student = $stmt->fetch();

        if ($student) {
            echo "Email already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO students (full_name, contact_number, address, email, password) VALUES (:full_name, :contact_number, :address, :email, :password)");
            if ($stmt->execute(['full_name' => $fullName, 'contact_number' => $contactNumber, 'address' => $address, 'email' => $email, 'password' => $hashedPassword])) {
                echo "<script>alert('Registration successful! You can now log in.');</script>";
                echo "<script>window.location.href = 'login_student.php?type=student';</script>";
            } else {
                echo "Error registering student.";
            }
        }
    } else {
        echo "Passwords do not match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Greenhorizon - Student Registration</title>
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
        <h2>Student Registration</h2>
        <form method="POST" onsubmit="return validateForm()">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>
            <span class="error" id="first_name_error"></span>

            <label for="middle_initial">Middle Initial:</label>
            <input type="text" id="middle_initial" name="middle_initial" maxlength="1" required>
            <span class="error" id="middle_initial_error"></span>

            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>
            <span class="error" id="last_name_error"></span>

            <label for="contact_number">Contact Number:</label>
            <input type="text" id="contact_number" name="contact_number" pattern="09[0-9]{9}" maxlength="11" required placeholder="09xxxxxxxxx" title="Contact number must start with '09' and contain exactly 11 digits">
            <span id="error-message" style="color: red;"></span>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <div class="input-group">
                <input type="password" id="password" name="password" required>
                <button type="button" id="togglePassword">Show</button>
            </div>

            <label for="confirm_password">Confirm Password:</label>
            <div class="input-group">
                <input type="password" id="confirm_password" name="confirm_password" required>
                <button type="button" id="toggleConfirmPassword">Show</button>
            </div>

            <button type="submit" name="register">Register</button>
        </form>

        <a href="login_student.php?type=student">Already have an account? Log in here</a>
    </div>

    <script>
        function validateForm() {
            const address = document.getElementById('address').value;
            if (address.length === 1 || address.length === 2 || address.length === 3) {
                alert("Invalid Address.");
                return false;
            }

            const email = document.getElementById('email').value;
            if (!/^[\w.-]+@(gmail\.com|yahoo\.com|g\.batstate-u\.edu\.ph)$/.test(email)) {
            alert("Please enter a valid email.");
            return false;
            }

            const password = document.getElementById('password').value;
            const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_\-+=]).{8,}$/;
            if (!passwordPattern.test(password)) {
                alert("Password must be at least 8 characters long and include uppercase, lowercase, numbers, and symbols.");
                return false;
            }

            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                alert("Passwords do not match.");
                return false;
            }

            return true;
        }

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

        document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
            const confirmPasswordInput = document.getElementById('confirm_password');
            const toggleButton = this;

            if (confirmPasswordInput.type === "password") {
                confirmPasswordInput.type = "text";
                toggleButton.textContent = "Hide";
            } else {
                confirmPasswordInput.type = "password";
                toggleButton.textContent = "Show";
            }
        });
    </script>
</body>
</html>
