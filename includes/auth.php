<?php
/**
 * Authentication and user-related functions
 */

/**
 * Authenticate a user with email and password
 * 
 * @param PDO $conn Database connection
 * @param string $email User email
 * @param string $password User password
 * @return array|false User data if authenticated, false otherwise
 */
function authenticateUser($conn, $email, $password) {
    try {
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Remove password from the returned data
            unset($user['password']);
            return $user;
        }
        
        return false;
    } catch (PDOException $e) {
        // Log error
        error_log("Authentication error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if an email already exists in the database
 * 
 * @param PDO $conn Database connection
 * @param string $email Email to check
 * @return bool True if email exists, false otherwise
 */
function emailExists($conn, $email) {
    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        // Log error
        error_log("Email check error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a new user
 * 
 * @param PDO $conn Database connection
 * @param string $name User's full name
 * @param string $email User's email
 * @param string $password User's password
 * @return int|false User ID if created, false otherwise
 */
function createUser($conn, $name, $email, $password) {
    try {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, created_at) VALUES (:name, :email, :password, NOW())");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password_hash);
        $stmt->execute();
        
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        // Log error
        error_log("User creation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user profile information
 * 
 * @param PDO $conn Database connection
 * @param int $user_id User ID
 * @return array|false User data if found, false otherwise
 */
function getUserProfile($conn, $user_id) {
    try {
        $stmt = $conn->prepare("
            SELECT id, name, email, bio, location, birthday, profile_picture, cover_picture, created_at 
            FROM users 
            WHERE id = :user_id
        ");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error
        error_log("Get profile error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update user profile information
 * 
 * @param PDO $conn Database connection
 * @param int $user_id User ID
 * @param string $bio User bio
 * @param string $location User location
 * @param string $birthday User birthday
 * @return bool True if updated, false otherwise
 */
function updateProfileInfo($conn, $user_id, $bio, $location, $birthday) {
    try {
        $stmt = $conn->prepare("
            UPDATE users 
            SET bio = :bio, location = :location, birthday = :birthday 
            WHERE id = :user_id
        ");
        $stmt->bindParam(':bio', $bio);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':birthday', $birthday);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return true;
    } catch (PDOException $e) {
        // Log error
        error_log("Update profile error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update user profile picture
 * 
 * @param PDO $conn Database connection
 * @param int $user_id User ID
 * @param string $picture_path Path to profile picture
 * @return bool True if updated, false otherwise
 */
function updateProfilePicture($conn, $user_id, $picture_path) {
    try {
        $stmt = $conn->prepare("
            UPDATE users 
            SET profile_picture = :picture_path 
            WHERE id = :user_id
        ");
        $stmt->bindParam(':picture_path', $picture_path);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return true;
    } catch (PDOException $e) {
        // Log error
        error_log("Update profile picture error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user posts
 * 
 * @param PDO $conn Database connection
 * @param int $user_id User ID
 * @return array Posts data
 */
function getUserPosts($conn, $user_id) {
    try {
        $stmt = $conn->prepare("
            SELECT p.*, 
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as likes_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count
            FROM posts p
            WHERE p.user_id = :user_id
            ORDER BY p.created_at DESC
        ");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error
        error_log("Get posts error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get friend status between two users
 * 
 * @param PDO $conn Database connection
 * @param int $user_id Current user ID
 * @param int $friend_id Friend user ID
 * @return string|null Friend status or null
 */
function getFriendStatus($conn, $user_id, $friend_id) {
    try {
        // Check if they are friends
        $stmt = $conn->prepare("
            SELECT status FROM friends 
            WHERE (user_id = :user_id AND friend_id = :friend_id) 
               OR (user_id = :friend_id AND friend_id = :user_id)
        ");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':friend_id', $friend_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return null; // No relationship
        }
        
        if ($result['status'] === 'accepted') {
            return 'friends';
        }
        
        // Check who sent the request
        $stmt = $conn->prepare("
            SELECT id FROM friends 
            WHERE user_id = :user_id AND friend_id = :friend_id AND status = 'pending'
        ");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':friend_id', $friend_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return 'request_sent';
        } else {
            return 'request_received';
        }
    } catch (PDOException $e) {
        // Log error
        error_log("Get friend status error: " . $e->getMessage());
        return null;
    }
}

/**
 * Format timestamp to "time ago" format
 * 
 * @param string $timestamp MySQL timestamp
 * @return string Formatted time
 */
function getTimeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    
    if ($time_difference < 60) {
        return 'Just now';
    } elseif ($time_difference < 3600) {
        return floor($time_difference / 60) . ' minutes ago';
    } elseif ($time_difference < 86400) {
        return floor($time_difference / 3600) . ' hours ago';
    } elseif ($time_difference < 604800) {
        return floor($time_difference / 86400) . ' days ago';
    } elseif ($time_difference < 2592000) {
        return floor($time_difference / 604800) . ' weeks ago';
    } elseif ($time_difference < 31536000) {
        return floor($time_difference / 2592000) . ' months ago';
    } else {
        return floor($time_difference / 31536000) . ' years ago';
    }
}

/**
 * Get newsfeed posts (posts from user and friends)
 * 
 * @param PDO $conn Database connection
 * @param int $user_id User ID
 * @param int $limit Limit number of posts
 * @param int $offset Offset for pagination
 * @return array Posts data
 */
function getNewsfeedPosts($conn, $user_id, $limit = 20, $offset = 0) {
    try {
        // Get posts from user and friends
        $stmt = $conn->prepare("
            SELECT p.*, 
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as likes_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count
            FROM posts p
            WHERE p.user_id = :user_id
            OR p.user_id IN (
                SELECT IF(user_id = :user_id, friend_id, user_id) 
                FROM friends 
                WHERE (user_id = :user_id OR friend_id = :user_id) 
                AND status = 'accepted'
            )
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error
        error_log("Get newsfeed posts error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get post comments
 * 
 * @param PDO $conn Database connection
 * @param int $post_id Post ID
 * @param int $limit Limit number of comments
 * @return array Comments data
 */
function getPostComments($conn, $post_id, $limit = 5) {
    try {
        $stmt = $conn->prepare("
            SELECT c.*, 
                (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) as likes_count
            FROM comments c
            WHERE c.post_id = :post_id
            ORDER BY c.created_at ASC
            LIMIT :limit
        ");
        $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error
        error_log("Get post comments error: " . $e->getMessage());
        return [];
    }
}

/**
 * Check if post is liked by user
 * 
 * @param PDO $conn Database connection
 * @param int $post_id Post ID
 * @param int $user_id User ID
 * @return bool True if liked, false otherwise
 */
function isPostLikedByUser($conn, $post_id, $user_id) {
    try {
        $stmt = $conn->prepare("
            SELECT id FROM post_likes
            WHERE post_id = :post_id AND user_id = :user_id
        ");
        $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        // Log error
        error_log("Check post like error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user's friends
 * 
 * @param PDO $conn Database connection
 * @param int $user_id User ID
 * @return array Friends data
 */
function getUserFriends($conn, $user_id) {
    try {
        $stmt = $conn->prepare("
            SELECT u.id, u.name, u.profile_picture
            FROM users u
            JOIN friends f ON (u.id = f.friend_id OR u.id = f.user_id)
            WHERE (f.user_id = :user_id OR f.friend_id = :user_id)
            AND f.status = 'accepted'
            AND u.id != :user_id
            ORDER BY u.name ASC
        ");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error
        error_log("Get user friends error: " . $e->getMessage());
        return [];
    }
}