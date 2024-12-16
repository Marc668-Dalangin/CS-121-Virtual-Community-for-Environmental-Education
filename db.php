<?php
$host = 'localhost';
$dbname = 'greenhorizon_db'; // Ensure this matches the database name in MySQL
$user = 'root'; // Default MySQL user for XAMPP
$pass = ''; // Default password for XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
