<?php
session_start();
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$dbname = 'greenhorizon_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed.']));
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['comment_id']) && isset($_SESSION['user_id'])) {
        $commentId = filter_var($input['comment_id'], FILTER_VALIDATE_INT);
        $userId = $_SESSION['user_id']; // Assuming user ID is stored in the session

        // Check if commentId is valid
        if ($commentId === false) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid comment ID.']);
            exit;
        }

        // Insert the report into the database
        $stmt = $pdo->prepare("INSERT INTO reported_comments (comment_id, reported_by) VALUES (:comment_id, :reported_by)");
        $stmt->bindParam(':comment_id', $commentId);
        $stmt->bindParam(':reported_by', $userId);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Comment reported successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to report comment.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input or user not logged in.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>