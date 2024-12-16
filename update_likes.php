<?php
session_start();
require 'db.php'; 


error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $postId = $data['id'];
    $action = $data['action'];

    if ($action === 'like') {
        $stmt = $pdo->prepare("UPDATE posts SET likes = likes + 1 WHERE id = :id");
    } elseif ($action === 'dislike') {
        $stmt = $pdo->prepare("UPDATE posts SET dislikes = dislikes + 1 WHERE id = :id");
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
    }

    $stmt->execute([':id' => $postId]);
    echo json_encode(['status' => 'success']);
    exit;
}
?>