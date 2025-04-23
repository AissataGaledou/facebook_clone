<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Return JSON error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $post_id = $_POST['post_id'];
    $content = trim($_POST['content']);
    
    // Validate input
    if (empty($content)) {
        // Return JSON error response
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
        exit();
    }
    
    try {
        // Insert comment into database
        $stmt = $conn->prepare("
            INSERT INTO comments (post_id, user_id, content, created_at) 
            VALUES (:post_id, :user_id, :content, NOW())
        ");
        $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':content', $content);
        $stmt->execute();
        
        $comment_id = $conn->lastInsertId();
        
        // Get post owner ID for notification
        $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = :post_id");
        $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->execute();
        $post_owner = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Create notification for post owner (if not the same user)
        if ($post_owner['user_id'] != $user_id) {
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, from_user_id, type, reference_id, created_at) 
                VALUES (:user_id, :from_user_id, 'comment', :post_id, NOW())
            ");
            $stmt->bindParam(':user_id', $post_owner['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':from_user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
            $stmt->execute();
        }
        
        // If this is an AJAX request, return success response
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            // Get user info
            $stmt = $conn->prepare("SELECT name, profile_picture FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Comment added successfully',
                'comment' => [
                    'id' => $comment_id,
                    'content' => $content,
                    'user_id' => $user_id,
                    'name' => $user['name'],
                    'profile_picture' => $user['profile_picture'] ?: 'assets/images/default-profile.png',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]);
            exit();
        }
        
        // Redirect back to post
        header("Location: ../index.php");
        exit();
    } catch (PDOException $e) {
        // Return JSON error response
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error adding comment: ' . $e->getMessage()]);
        exit();
    }
} else {
    // If not POST request, redirect to index
    header("Location: ../index.php");
    exit();
}