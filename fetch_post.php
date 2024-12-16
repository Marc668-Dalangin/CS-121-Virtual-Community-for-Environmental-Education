<?php
require 'db.php'; // Include database connection

try {
    $stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['posts' => $posts]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
