<?php
session_start();
require_once 'config/db.php';
require_once 'includes/auth.php';

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user = getUserProfile($conn, $user_id);

// Get newsfeed posts (from user and friends)
$newsfeed_posts = getNewsfeedPosts($conn, $user_id);

// Set page title
$page_title = "Home";

// Include header
include 'includes/header.php';
?>

<div class="main-container">
    <div class="sidebar-left">
        <div class="user-profile-short">
            <a href="profile.php">
                <img src="<?php echo !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'assets/images/default-profile.png'; ?>" alt="Profile">
                <span><?php echo htmlspecialchars($user['name']); ?></span>
            </a>
        </div>
        
        <nav class="sidebar-menu">
            <ul>
                <li>
                    <a href="friends.php">
                        <span class="menu-icon friends-icon"></span>
                        <span class="menu-text">Friends</span>
                    </a>
                </li>
                <li>
                    <a href="groups.php">
                        <span class="menu-icon groups-icon"></span>
                        <span class="menu-text">Groups</span>
                    </a>
                </li>
                <li>
                    <a href="marketplace.php">
                        <span class="menu-icon marketplace-icon"></span>
                        <span class="menu-text">Marketplace</span>
                    </a>
                </li>
                <li>
                    <a href="watch.php">
                        <span class="menu-icon watch-icon"></span>
                        <span class="menu-text">Watch</span>
                    </a>
                </li>
                <li>
                    <a href="memories.php">
                        <span class="menu-icon memories-icon"></span>
                        <span class="menu-text">Memories</span>
                    </a>
                </li>
                <li>
                    <a href="saved.php">
                        <span class="menu-icon saved-icon"></span>
                        <span class="menu-text">Saved</span>
                    </a>
                </li>
                <li>
                    <a href="pages.php">
                        <span class="menu-icon pages-icon"></span>
                        <span class="menu-text">Pages</span>
                    </a>
                </li>
                <li>
                    <a href="events.php">
                        <span class="menu-icon events-icon"></span>
                        <span class="menu-text">Events</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="footer-links">
            <small>
                <a href="privacy.php">Privacy</a> · 
                <a href="terms.php">Terms</a> · 
                <a href="advertising.php">Advertising</a> · 
                <a href="cookies.php">Cookies</a> · 
                <a href="more.php">More</a> · 
                Facebook Clone © <?php echo date('Y'); ?>
            </small>
        </div>
    </div>
    
    <div class="main-content">
        <div class="stories-container">
            <div class="stories-header">
                <h3>Stories</h3>
                <a href="stories.php">See All</a>
            </div>
            
            <div class="stories-list">
                <div class="story create-story">
                    <div class="create-story-icon"></div>
                    <div class="story-title">Create Story</div>
                </div>
                
                <!-- Sample stories - would be dynamically generated -->
                <div class="story">
                    <div class="story-image"></div>
                    <div class="story-author">Friend Name</div>
                </div>
                <!-- Additional story items would be placed here -->
            </div>
        </div>
        
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
                        
                        <label for="tag_friends">
                            <span class="icon tag-icon"></span>
                            <span>Tag Friends</span>
                        </label>
                        <input type="text" id="tag_friends" name="tag_friends" style="display:none">
                        
                        <label for="feeling">
                            <span class="icon feeling-icon"></span>
                            <span>Feeling/Activity</span>
                        </label>
                        <input type="text" id="feeling" name="feeling" style="display:none">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Post</button>
                </div>
            </form>
        </div>
        
        <div class="posts-container">
            <?php if (empty($newsfeed_posts)): ?>
                <div class="no-posts">
                    <p>No posts to display. Add friends to see their posts here!</p>
                </div>
            <?php else: ?>
                <?php foreach ($newsfeed_posts as $post): ?>
                    <?php 
                    // Get post author information
                    $post_author = getUserProfile($conn, $post['user_id']);
                    ?>
                    <div class="post">
                        <div class="post-header">
                            <div class="post-author">
                                <a href="profile.php?id=<?php echo $post['user_id']; ?>">
                                    <img src="<?php echo !empty($post_author['profile_picture']) ? htmlspecialchars($post_author['profile_picture']) : 'assets/images/default-profile.png'; ?>" alt="Profile">
                                </a>
                                <div class="post-details">
                                    <a href="profile.php?id=<?php echo $post['user_id']; ?>" class="author-name"><?php echo htmlspecialchars($post_author['name']); ?></a>
                                    <div class="post-time"><?php echo getTimeAgo($post['created_at']); ?></div>
                                </div>
                            </div>
                            
                            <div class="post-options">
                                <button class="options-btn">...</button>
                                <div class="options-dropdown">
                                    <ul>
                                        <?php if ($post['user_id'] == $user_id): ?>
                                            <li><a href="edit_post.php?id=<?php echo $post['id']; ?>">Edit Post</a></li>
                                            <li><a href="includes/delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Are you sure you want to delete this post?')">Delete Post</a></li>
                                        <?php else: ?>
                                            <li><a href="includes/hide_post.php?id=<?php echo $post['id']; ?>">Hide Post</a></li>
                                            <li><a href="includes/report_post.php?id=<?php echo $post['id']; ?>">Report Post</a></li>
                                        <?php endif; ?>
                                        <li><a href="save_post.php?id=<?php echo $post['id']; ?>">Save Post</a></li>
                                    </ul>
                                </div>
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
                            <div class="likes-count">
                                <span class="like-icon"></span>
                                <span><?php echo $post['likes_count']; ?></span>
                            </div>
                            <div class="comments-shares">
                                <span><?php echo $post['comments_count']; ?> comments</span>
                                <span><?php echo $post['shares_count'] ?? 0; ?> shares</span>
                            </div>
                        </div>
                        
                        <div class="post-actions">
                            <button class="post-action like-btn <?php echo isPostLikedByUser($conn, $post['id'], $user_id) ? 'liked' : ''; ?>" data-post-id="<?php echo $post['id']; ?>">
                                <span class="action-icon like-icon"></span>
                                <span>Like</span>
                            </button>
                            <button class="post-action comment-btn" data-post-id="<?php echo $post['id']; ?>">
                                <span class="action-icon comment-icon"></span>
                                <span>Comment</span>
                            </button>
                            <button class="post-action share-btn" data-post-id="<?php echo $post['id']; ?>">
                                <span class="action-icon share-icon"></span>
                                <span>Share</span>
                            </button>
                        </div>
                        
                        <div class="comments-section" id="comments-section-<?php echo $post['id']; ?>">
                            <?php 
                            // Get post comments
                            $comments = getPostComments($conn, $post['id']);
                            ?>
                            
                            <?php if (!empty($comments)): ?>
                                <div class="comments-list">
                                    <?php foreach ($comments as $comment): ?>
                                        <?php 
                                        // Get comment author information
                                        $comment_author = getUserProfile($conn, $comment['user_id']);
                                        ?>
                                        <div class="comment">
                                            <a href="profile.php?id=<?php echo $comment['user_id']; ?>">
                                                <img src="<?php echo !empty($comment_author['profile_picture']) ? htmlspecialchars($comment_author['profile_picture']) : 'assets/images/default-profile.png'; ?>" alt="Profile">
                                            </a>
                                            <div class="comment-content">
                                                <div class="comment-bubble">
                                                    <a href="profile.php?id=<?php echo $comment['user_id']; ?>" class="comment-author"><?php echo htmlspecialchars($comment_author['name']); ?></a>
                                                    <p><?php echo htmlspecialchars($comment['content']); ?></p>
                                                </div>
                                                <div class="comment-actions">
                                                    <a href="#" class="comment-like">Like</a>
                                                    <a href="#" class="comment-reply">Reply</a>
                                                    <span class="comment-time"><?php echo getTimeAgo($comment['created_at']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form class="add-comment" action="includes/add_comment.php" method="POST">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <img src="<?php echo !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'assets/images/default-profile.png'; ?>" alt="Profile">
                                <input type="text" name="content" placeholder="Write a comment..." required>
                                <button type="submit" class="send-icon"></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="sidebar-right">
        <div class="contacts-header">
            <h3>Contacts</h3>
            <div class="contact-actions">
                <button class="search-icon"></button>
                <button class="options-icon"></button>
            </div>
        </div>
        
        <div class="contacts-list">
            <?php 
            // Get user's friends
            $friends = getUserFriends($conn, $user_id);
            ?>
            
            <?php if (!empty($friends)): ?>
                <?php foreach ($friends as $friend): ?>
                    <div class="contact">
                        <a href="profile.php?id=<?php echo $friend['id']; ?>">
                            <div class="contact-status <?php echo rand(0, 1) ? 'online' : ''; ?>"></div>
                            <img src="<?php echo !empty($friend['profile_picture']) ? htmlspecialchars($friend['profile_picture']) : 'assets/images/default-profile.png'; ?>" alt="Profile">
                            <span><?php echo htmlspecialchars($friend['name']); ?></span>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-contacts">
                    <p>Add friends to see them here!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="group-conversations">
            <h3>Group Conversations</h3>
            <div class="create-group">
                <button class="create-group-btn">
                    <span class="plus-icon"></span>
                    <span>Create New Group</span>
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>