<?php
/**
 * Database connection configuration
 */

// Database credentials
$host = 'localhost';
$dbname = 'facebook_clone';
$username = 'root';
$password = 'AYE@@GLD';
$charset = 'utf8mb4';

// DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create PDO instance
    $conn = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Log error
    error_log("Database connection failed: " . $e->getMessage());
    
    // Display user-friendly message
    die("Sorry, there was a problem connecting to our database. Please try again later.");
}