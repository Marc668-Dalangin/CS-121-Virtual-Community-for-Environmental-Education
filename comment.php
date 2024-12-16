<?php
session_start();
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$postId = $data['post_id'] ?? null;
$comment = $data['comment'] ?? '';

// Check if the user is an admin or a student
$userEmail = $_SESSION['email'] ?? 'Guest'; // This will capture the admin's email if logged in

if ($postId && !empty($comment)) {
    // Insert the comment into the database
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_email, comment) VALUES (:post_id, :user_email, :comment)");
    $stmt->execute([
        ':post_id' => $postId,
        ':user_email' => $userEmail,
        ':comment' => $comment,
    ]);

    // Fetch the newly created comment to return
    $newCommentId = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = :id");
    $stmt->execute([':id' => $newCommentId]);
    $newComment = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'comment' => $newComment]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid post ID or empty comment.']);
}
?>