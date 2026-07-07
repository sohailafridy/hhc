<?php
/**
 * Application Configuration
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone for the application (matches PKR currency region)
date_default_timezone_set('Asia/Karachi');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hhc');
define('CURRENCY', 'PKR ');


// online configuration
// define('DB_HOST', 'localhost');
// define('DB_USER', 'u898904160_hhcs_user');
// define('DB_PASS', 'r9$x:Oh7;Q');
// define('DB_NAME', 'u898904160_hhcs');
// define('CURRENCY', 'PKR ');
// line no 39 $base_url = "$protocol://$host";





// Application URL/Path Configuration
// IMPORTANT: Update these values when moving to production
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_url = "$protocol://$host/hhc";
define('BASE_URL', $base_url);
define('BASE_PATH', __DIR__);

// Error Reporting (Turn off for production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection (PDO)
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // If the database doesn't exist, we might want to handle it or show a setup page
    // For now, die with a message
    die("Connection failed: " . $e->getMessage() . ". <br>Make sure to create a database named '" . DB_NAME . "' and import 'database.sql'.");
}

// Helper Functions
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

function check_auth() {
    if (!isset($_SESSION['admin_id'])) {
        redirect('/login.php');
    }
}

?>





