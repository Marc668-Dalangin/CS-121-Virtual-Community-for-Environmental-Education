<?php
// Start the session
session_start();

// Destroy all session data
$_SESSION = [];
session_destroy();

// Redirect the user to the login page
header("Location: login.php");
exit();
?>
