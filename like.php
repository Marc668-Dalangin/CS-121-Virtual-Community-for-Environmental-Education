<?php
session_start();
require 'db.php'; // Include your database connection

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON payload
    $data = json_decode(file_get_contents('php://input'), true);
    $postId = $data['post_id'] ?? null;
    $userId = $_SESSION['user_id'] ?? null; // Assuming the user ID is stored in the session
    $userType = 'student'; // Set this based on your application logic

    if (!$postId || !$userId) {
        echo json_encode(['status' => 'error', 'message' => 'Post ID and User ID are required']);
        exit;
    }

    try {
        // Check if the user has already liked the post
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM posts_likes WHERE post_id = :post_id AND user_id = :user_id AND user_type = :user_type");
        $checkStmt->execute(['post_id' => $postId, 'user_id' => $userId, 'user_type' => $userType]);
        $hasLiked = $checkStmt->fetchColumn() > 0;

        if ($hasLiked) {
            echo json_encode(['status' => 'error', 'message' => 'You have already liked this post.']);
            exit;
        }

        // Increment the like count for the post
        $stmt = $pdo->prepare("UPDATE posts SET likes = likes + 1 WHERE id = :id");
        $stmt->execute([':id' => $postId]);

        // Insert a record into posts_likes to track the like
        $insertStmt = $pdo->prepare("INSERT INTO posts_likes (post_id, user_id, user_type) VALUES (:post_id, :user_id, :user_type)");
        $insertStmt->execute(['post_id' => $postId, 'user_id' => $userId, 'user_type' => $userType]);

        // Fetch the updated like count
        $stmt = $pdo->prepare("SELECT likes FROM posts WHERE id = :id");
        $stmt->execute([':id' => $postId]);
        $likes = $stmt->fetchColumn();

        echo json_encode(['status' => 'success', 'likes' => $likes]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}