<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $content = trim($_POST['content']);
    $privacy = isset($_POST['privacy']) ? $_POST['privacy'] : 'public';
    
    // Validate input
    if (empty($content) && !isset($_FILES['post_image'])) {
        $_SESSION['error'] = "Your post cannot be empty";
        header("Location: ../index.php");
        exit();
    }
    
    // Initialize image path
    $image_path = null;
    
    // Handle image upload
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['post_image']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed)) {
            $new_filename = 'post_' . $user_id . '_' . time() . '.' . $file_ext;
            $upload_path = '../assets/images/posts/' . $new_filename;
            
            if (move_uploaded_file($_FILES['post_image']['tmp_name'], $upload_path)) {
                $image_path = 'assets/images/posts/' . $new_filename;
            } else {
                $_SESSION['error'] = "Failed to upload image";
                header("Location: ../index.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid file type. Allowed: jpg, jpeg, png, gif";
            header("Location: ../index.php");
            exit();
        }
    }
    
    try {
        // Insert post into database
        $stmt = $conn->prepare("
            INSERT INTO posts (user_id, content, image, privacy, created_at) 
            VALUES (:user_id, :content, :image, :privacy, NOW())
        ");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':image', $image_path);
        $stmt->bindParam(':privacy', $privacy);
        $stmt->execute();
        
        $_SESSION['success'] = "Post created successfully";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error creating post: " . $e->getMessage();
    }
    
    // Redirect back to index
    header("Location: ../index.php");
    exit();
} else {
    // If not POST request, redirect to index
    header("Location: ../index.php");
    exit();
}