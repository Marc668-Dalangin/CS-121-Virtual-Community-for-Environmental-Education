<?php
require 'db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['submit'])) {
    $emailOrUsername = $_POST['emailOrUsername'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email");
    $stmt->execute(['email' => $emailOrUsername]);
    $user = $stmt->fetch();
    $email = $user ? $user['email'] : null;

    if ($user) {
        $token = bin2hex(random_bytes(50));
        $expiresAt = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)");
        $stmt->execute(['email' => $email, 'token' => $token, 'expires_at' => $expiresAt]);

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'greenhorizon91@gmail.com';
            $mail->Password = 'fkufgqsragbmylqp';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('greenhorizon91@gmail.com', 'Greenhorizon Support');
            $mail->addAddress($email);

            $resetLink = 'http://localhost/greenhorizon/reset_password.php?token=' . $token;

            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password - Greenhorizon';
            $mail->Body    = "
                <h3>Hello,</h3>
                <p>We received a request to reset your password. Click the link below to set a new password:</p>
                <a href='$resetLink'>Reset Password</a>
                <p>If you did not request this, please ignore this email.</p>
                <br>
                <p>Best regards,</p>
                <p>Greenhorizon Team</p>
            ";

            $mail->send();
            echo 'Reset password link has been sent to your email!';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
        
    } else {
        echo 'No account found with that email/username.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css?v=1.5">
</head>
<body>
    <header>
        <h1>Greenhorizon</h1>
    </header>

    <div class="login-container">
        <h2>Forgot Password</h2>
        <form action="" method="POST">
            <label for="emailOrUsername">Enter Username (Admin):</label>
            <input type="text" id="emailOrUsername" name="emailOrUsername" required>

            <button type="submit" name="submit">Send Reset Link</button>
        </form>

        <a href="login.php">Back to Login</a>
    </div>
</body>
</html>
