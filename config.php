<?php
// ============================================================
// AttendTrack – Configuration (MySQL / Standard LAMP Hosting)
// ============================================================

ob_start();

// ── App settings ─────────────────────────────────────────────
define('APP_NAME', 'AttendTrack');
define('APP_URL',  (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

// Upload directory — relative to this file's location (document root)
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', '/uploads/');

define('SESSION_TIMEOUT', 3600);       // 1 hour
define('TIMEZONE', 'Asia/Jakarta');

date_default_timezone_set(TIMEZONE);

// ── Database Configuration ────────────────────────────────────
// Edit these values to match your hosting MySQL credentials
define('DB_HOST',    'localhost');
define('DB_NAME',    'attendtrack');   // Your MySQL database name
define('DB_USER',    'root');          // Your MySQL username
define('DB_PASS',    '');              // Your MySQL password
define('DB_CHARSET', 'utf8mb4');
// ─────────────────────────────────────────────────────────────

// ── Session (standard PHP file sessions — works on all hosts) ─
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
    session_start();
}

// ── Database connection (PDO MySQL) ───────────────────────────
function getDB() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
    } catch (PDOException $e) {
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
              <title>Database Error</title>
              <style>body{font-family:sans-serif;max-width:600px;margin:80px auto;padding:20px}
              .box{background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:24px}
              h2{color:#991b1b;margin:0 0 12px}pre{font-size:.85rem;color:#7f1d1d;white-space:pre-wrap}</style>
              </head><body><div class="box">
              <h2>⚠️ Database Connection Failed</h2>
              <p>Check <code>config.php</code> — make sure DB_HOST, DB_NAME, DB_USER, and DB_PASS are correct.</p>
              <p>If the database does not exist yet, <a href="/install.php">run the installer</a> first.</p>
              <pre>' . htmlspecialchars($e->getMessage()) . '</pre>
              </div></body></html>';
        exit;
    }
    return $pdo;
}

// ── Auth helpers ──────────────────────────────────────────────
function isLoggedIn() {
    if (!isset($_SESSION['user_id'], $_SESSION['last_activity'], $_SESSION['role'])) {
        return false;
    }
    if ((time() - $_SESSION['last_activity']) >= SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        return false;
    }
    return true;
}

function isAdmin() {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        session_unset();
        header('Location: /index.php?msg=session_expired');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /dashboard.php');
        exit;
    }
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// ── Helper: escape HTML ───────────────────────────────────────
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}
