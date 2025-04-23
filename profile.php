<?php
session_start();
require_once 'config/db.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user ID (either the logged-in user or the profile being viewed)
$profile_id = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['user_id'];
$is_own_profile = ($profile_id === $_SESSION['user_id']);

// Check if it's first login
$first_login = isset($_GET['first_login']) && $_GET['first_login'] === 'true';

// Get user profile data
$user = getUserProfile($conn, $profile_id);
if (!$user) {
    // User not found
    header("Location: index.php");
    exit();
}

$message = '';
$error = '';

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_own_profile) {
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed)) {
            $new_filename = 'user_' . $profile_id . '_' . time() . '.' . $file_ext;
            $upload_path = 'assets/images/profile/' . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                // Update profile picture in database
                updateProfilePicture($conn, $profile_id, $upload_path);
                $user['profile_picture'] = $upload_path;
            } else {
                $error = "Failed to upload profile picture";
            }
        } else {
            $error = "Invalid file type. Allowed: jpg, jpeg, png, gif";
        }
    }
    
    // Update profile information
    $bio = trim($_POST['bio']);
    $location = trim($_POST['location']);
    $birthday = trim($_POST['birthday']);
    
    if (updateProfileInfo($conn, $profile_id, $bio, $location, $birthday)) {
        $message = "Profile updated successfully";
        $user['bio'] = $bio;
        $user['location'] = $location;
        $user['birthday'] = $birthday;
    } else {
        $error = "Failed to update profile information";
    }
}

// Get user posts
$posts = getUserPosts($conn, $profile_id);

// Get friend status if viewing someone else's profile
$friend_status = null;
if (!$is_own_profile) {
    $friend_status = getFriendStatus($conn, $_SESSION['user_id'], $profile_id);
}

include 'includes/header.php';
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-cover">
            <?php if (!empty($user['cover_picture'])): ?>
                <img src="<?php echo htmlspecialchars($user['cover_picture']); ?>" alt="Cover Photo">
            <?php endif; ?>
            
            <?php if ($is_own_profile): ?>
                <div class="cover-update">
                    <a href="#" class="btn btn-light">Update Cover Photo</a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="profile-info">
            <div class="profile-picture">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                <?php else: ?>
                    <img src="assets/images/default-profile.png" alt="Default Profile">
                <?php endif; ?>
                
                <?php if ($is_own_profile): ?>
                    <div class="picture-update">
                        <form id="picture-form" method="POST" enctype="multipart/form-data">
                            <label for="profile_picture" class="btn btn-light btn-circle">
                                <i class="camera-icon"></i>
                            </label>
                            <input type="file" id="profile_picture" name="profile_picture" style="display:none" onchange="document.getElementById('picture-form').submit()">
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="profile-name">
                <h1><?php echo htmlspecialchars($user['name']); ?></h1>
                <?php if (!empty($user['bio'])): ?>
                    <p class="bio"><?php echo htmlspecialchars($user['bio']); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if (!$is_own_profile): ?>
                <div class="profile-actions">
                    <?php if ($friend_status === 'friends'): ?>
                        <a href="includes/friend_action.php?action=unfriend&id=<?php echo $profile_id; ?>" class="btn btn-light">Unfriend</a>
                        <a href="messages.php?user=<?php echo $profile_id; ?>" class="btn btn-primary">Message</a>
                    <?php elseif ($friend_status === 'request_sent'): ?>
                        <button class="btn btn-light" disabled>Friend Request Sent</button>
                    <?php elseif ($friend_status === 'request_received'): ?>
                        <a href="includes/friend_action.php?action=accept&id=<?php echo $profile_id; ?>" class="btn btn-primary">Accept Friend Request</a>
                        <a href="includes/friend_action.php?action=decline&id=<?php echo $profile_id; ?>" class="btn btn-light">Decline</a>
                    <?php else: ?>
                        <a href="includes/friend_action.php?action=add&id=<?php echo $profile_id; ?>" class="btn btn-primary">Add Friend</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="profile-actions">
                    <a href="#" class="btn btn-light edit-profile-btn">Edit Profile</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="profile-content">
        <div class="profile-sidebar">
            <div class="profile-details">
                <h3>About</h3>
                <ul>
                    <?php if (!empty($user['location'])): ?>
                        <li>
                            <span class="icon location-icon"></span>
                            <span>Lives in <?php echo htmlspecialchars($user['location']); ?></span>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (!empty($user['birthday'])): ?>
                        <li>
                            <span class="icon birthday-icon"></span>
                            <span>Born on <?php echo htmlspecialchars($user['birthday']); ?></span>
                        </li>
                    <?php endif; ?>
                    
                    <li>
                        <span class="icon join-icon"></span>
                        <span>Joined <?php echo date('F Y', strtotime($user['created_at'])); ?></span>
                    </li>
                </ul>
                
                <?php if ($is_own_profile): ?>
                    <div class="edit-details">
                        <a href="#" class="edit-profile-btn">Edit Details</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="profile-friends">
                <h3>Friends</h3>
                <!-- Friends list would go here -->
                <div class="friends-preview">
                    <!-- Show 6-9 friend previews here -->
                </div>
                <a href="friends.php?id=<?php echo $profile_id; ?>" class="see-all-friends">See All Friends</a>
            </div>
        </div>
        
        <div class="profile-posts">
            <?php if ($is_own_profile): ?>
                <div class="create-post">
                    <form action="includes/create_post.php" method="POST" enctype="multipart/form-data">
                        <div class="post-input">
                            <img src="<?php echo !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'assets/images/default-profile.png'; ?>" alt="Profile">
                            <textarea name="content" placeholder="What's on your mind, <?php echo explode(' ', $user['name'])[0]; ?>?"></textarea>
                        </div>
                        
                        <div class="post-actions">
                            <div class="upload-options">
                                <label for="post_image">
                                    <span class="icon photo-icon"></span>
                                    <span>Photo/Video</span>
                                </label>
                                <input type="file" id="post_image" name="post_image" style="display:none">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Post</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <div class="posts-list">
                <?php if (empty($posts)): ?>
                    <div class="no-posts">
                        <p>No posts to display</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="post">
                            <div class="post-header">
                                <img src="<?php echo !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'assets/images/default-profile.png'; ?>" alt="Profile">
                                <div class="post-info">
                                    <a href="profile.php?id=<?php echo $post['user_id']; ?>" class="post-author"><?php echo htmlspecialchars($user['name']); ?></a>
                                    <span class="post-time"><?php echo getTimeAgo($post['created_at']); ?></span>
                                </div>
                            </div>
                            
                            <div class="post-content">
                                <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                                
                                <?php if (!empty($post['image'])): ?>
                                    <div class="post-image">
                                        <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image">
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="post-stats">
                                <span class="likes"><?php echo $post['likes_count']; ?> Likes</span>
                                <span class="comments"><?php echo $post['comments_count']; ?> Comments</span>
                            </div>
                            
                            <div class="post-actions">
                                <button class="like-btn" data-post-id="<?php echo $post['id']; ?>">Like</button>
                                <button class="comment-btn" data-post-id="<?php echo $post['id']; ?>">Comment</button>
                                <button class="share-btn" data-post-id="<?php echo $post['id']; ?>">Share</button>
                            </div>
                            
                            <!-- Comments would go here -->
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($is_own_profile): ?>
<!-- Profile Edit Modal (Hidden by default) -->
<div class="modal" id="editProfileModal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Profile</h2>
        
        <?php if (!empty($message)): ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea name="bio" id="bio" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="birthday">Birthday</label>
                <input type="date" name="birthday" id="birthday" value="<?php echo htmlspecialchars($user['birthday'] ?? ''); ?>">
            </div>
            
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>