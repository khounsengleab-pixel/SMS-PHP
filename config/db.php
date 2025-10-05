<?php
// school-management/config/db.php

// --- Database Configuration ---
// Replace with your actual database credentials.
define('DB_HOST', 'localhost'); // Your database server (usually 'localhost')
define('DB_USER', 'root');      // Your database username
define('DB_PASS', '');          // Your database password
define('DB_NAME', 'school_management_db'); // Your database name

// --- Establish a Database Connection using PDO ---
// PDO is a modern and secure way to connect to databases in PHP.
try {
    // Data Source Name (DSN)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    // PDO options for better error handling and fetching
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
    ];

    // Create a new PDO instance
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    // If connection fails, stop the script and show an error message.
    // In a production environment, you might log this error instead of showing it to the user.
    die("Database connection failed: " . $e->getMessage());
}

// --- Session Management ---
// Start the session on every page that includes this config file.
// This ensures session variables are available throughout the site.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
