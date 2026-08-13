<?php
// Configuration & Data Store Setup
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'Functional Elegance');
define('DATA_FILE', __DIR__ . '/db/data.json');

// MySQL Database Credentials
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'formalwear_schema');

// Get PDO Database Connection
function get_db_connection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die("Koneksi Database Gagal: " . $e->getMessage());
        }
    }
    return $pdo;
}

// Get Data Store (Fallback & Compatibility)
function get_db_data() {
    if (!file_exists(DATA_FILE)) {
        return ['beranda' => [], 'katalog' => [], 'users' => []];
    }
    $json = file_get_contents(DATA_FILE);
    $data = json_decode($json, true) ?: [];
    return $data;
}

// Save Data Store
function save_db_data($data) {
    file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}



// Helper Functions
function is_logged_in() {
    return isset($_SESSION['user']);
}

function is_admin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
