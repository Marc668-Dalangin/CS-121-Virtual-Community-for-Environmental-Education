<?php
require 'db.php';

$error_message = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = :token");
    $stmt->execute(['token' => $token]);
    $reset = $stmt->fetch();

    if ($reset) {
        $expiresAt = strtotime($reset['expires_at']);
        $currentTime = time();

        if ($currentTime > $expiresAt) {
            header("Location: forgot_password.php?error=expired");
            exit;
        }

        if (isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];

            $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_\-+=]).{8,}$/';
            if (!preg_match($passwordPattern, $newPassword)) {
                $error_message = "Password must be at least 8 characters long and include uppercase, lowercase, numbers, and symbols.";
            } elseif ($newPassword === $confirmPassword) {
                $emailOrUsername = $reset['email'];

                $adminStmt = $pdo->prepare("SELECT password FROM admins WHERE email = :email");
                $adminStmt->execute(['email' => $emailOrUsername]);
                $admin = $adminStmt->fetch();

                if ($admin && password_verify($newPassword, $admin['password'])) {
                    $error_message = "New password cannot be the same as the old password.";
                } else {

                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                    $updateStmt = $pdo->prepare("UPDATE admins SET password = :password WHERE email = :email");
                    if ($updateStmt->execute(['password' => $hashedPassword, 'email' => $emailOrUsername])) {

                        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = :token");
                        $stmt->execute(['token' => $token]);

                        header("Location: login.php");
                        exit;
                    } else {
                        $error_message = "Error updating password. User not found.";
                    }
                }
            } else {
                $error_message = "Passwords do not match!";
            }
        }
    } else {
        $error_message = "Invalid token. Please try again.";
    }
} else {
    $error_message = "No token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css?v=1.5">
</head>
<body>
    <div class="login-container">
        <h2>Reset Password</h2>
        <form method="POST">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Reset Password</button>
        </form>
        
        <?php if ($error_message): ?>
            <div class="error-notification"><?php echo $error_message; ?></div>
        <?php endif; ?>
    </div>

    <style>
        .error-notification {
            color: red;
            margin-top: 10px;
            font-size: 14px;
            text-align: left;
        }
    </style>
</body>
</html>
