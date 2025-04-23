<?php
session_start();
require_once 'config/db.php';
require_once 'includes/auth.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Process signup form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if email already exists
        if (emailExists($conn, $email)) {
            $error = "Email already in use";
        } else {
            // Create new user
            $user_id = createUser($conn, $name, $email, $password);
            
            if ($user_id) {
                $success = "Account created successfully! You can now login.";
                
                // Optionally, auto-login the user
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                // Redirect to profile setup page
                header("Location: profile.php?first_login=true");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Facebook Clone</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="signup-container">
        <div class="signup-header">
            <h1>facebook</h1>
            <p>Create a new account</p>
        </div>
        
        <div class="signup-form">
            <form method="POST" action="signup.php">
                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="form-group">
                    <input type="text" name="name" placeholder="Full Name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email Address" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                
                <div class="form-group">
                    <p class="terms">By clicking Sign Up, you agree to our Terms, Privacy Policy and Cookies Policy.</p>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-success">Sign Up</button>
                </div>
                
                <div class="login-link">
                    <p>Already have an account? <a href="login.php">Log In</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>