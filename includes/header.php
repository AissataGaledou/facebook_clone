<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);

// Get notification counts if logged in
$notifications_count = 0;
$messages_count = 0;
$friend_requests_count = 0;

if ($logged_in) {
    // These would normally fetch from database
    // $notifications_count = getNotificationsCount($conn, $_SESSION['user_id']);
    // $messages_count = getUnreadMessagesCount($conn, $_SESSION['user_id']);
    // $friend_requests_count = getFriendRequestsCount($conn, $_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | Facebook Clone' : 'Facebook Clone'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js" defer></script>
</head>
<body>
    <header class="main-header">
        <div class="header-left">
            <div class="logo">
                <a href="index.php">
                    <h1>facebook</h1>
                </a>
            </div>
            <?php if ($logged_in): ?>
            <div class="search-bar">
                <input type="text" placeholder="Search Facebook">
                <button type="submit" class="search-icon"></button>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($logged_in): ?>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="friends.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'friends.php' ? 'active' : ''; ?>">Friends</a></li>
                <li><a href="groups.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'groups.php' ? 'active' : ''; ?>">Groups</a></li>
                <li><a href="watch.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'watch.php' ? 'active' : ''; ?>">Watch</a></li>
            </ul>
        </nav>
        
        <div class="header-right">
            <div class="header-icons">
                <a href="notifications.php" class="icon-btn notifications-icon">
                    <?php if ($notifications_count > 0): ?>
                    <span class="badge"><?php echo $notifications_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="messages.php" class="icon-btn messages-icon">
                    <?php if ($messages_count > 0): ?>
                    <span class="badge"><?php echo $messages_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="friend_requests.php" class="icon-btn friend-requests-icon">
                    <?php if ($friend_requests_count > 0): ?>
                    <span class="badge"><?php echo $friend_requests_count; ?></span>
                    <?php endif; ?>
                </a>
            </div>
            
            <div class="user-menu">
                <div class="profile-dropdown">
                    <button class="dropdown-btn">
                        <img src="<?php echo isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture']) ? htmlspecialchars($_SESSION['profile_picture']) : 'assets/images/default-profile.png'; ?>" alt="Profile">
                    </button>
                    <div class="dropdown-content">
                        <a href="profile.php">
                            <div class="dropdown-profile">
                                <img src="<?php echo isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture']) ? htmlspecialchars($_SESSION['profile_picture']) : 'assets/images/default-profile.png'; ?>" alt="Profile">
                                <div class="dropdown-name">
                                    <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                                    <span>See your profile</span>
                                </div>
                            </div>
                        </a>
                        <hr>
                        <a href="settings.php">Settings & Privacy</a>
                        <a href="help.php">Help & Support</a>
                        <a href="logout.php">Log Out</a>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="header-right">
            <a href="login.php" class="btn btn-light">Log In</a>
            <a href="signup.php" class="btn btn-primary">Sign Up</a>
        </div>
        <?php endif; ?>
    </header>
    
    <div class="content-container">