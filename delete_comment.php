<?php
session_start();
require 'db.php'; // Include your database connection

// Check if a comment ID is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $commentId = $data['comment_id'] ?? null;

    if ($commentId) {
        // Prepare the SQL statement to delete the comment
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = :comment_id");
        $stmt->bindParam(':comment_id', $commentId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Return a success response
            echo json_encode(['status' => 'success']);
        } else {
            // Return an error response
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete comment.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Comment ID not provided.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>