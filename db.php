<?php
/**
 * Database Connection - Production Ready
 * Optimized for shared hosting (Niagahoster/cPanel)
 */

// ============================================ 
// DEFINISI KONSTANTA APP_INIT
// ============================================ 
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// ============================================ 
// LOAD ENVIRONMENT VARIABLES
// ============================================ 
function loadEnv($path = '.env') {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse line
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Set environment variable
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
    
    return true;
}

/**
 * Helper function to detect AJAX requests.
 * Checks for the X-Requested-With header.
 * @return bool
 */
function isAjaxRequest(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Load .env file
loadEnv(__DIR__ . '/.env');

// ============================================ 
// DATABASE CONFIGURATION
// ============================================ 
$db_config = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'name' => getenv('DB_NAME') ?: 'ngulikuy_db',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4'
];

// ============================================ 
// ENVIRONMENT DETECTION
// ============================================ 
$isProduction = (getenv('APP_ENV') === 'production');
$isDebug = (getenv('APP_DEBUG') === 'true');

// Set error reporting for debugging JSON error
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
// Ensure log directory exists for PHP errors
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
ini_set('error_log', $logDir . '/php_error.log');

// ============================================ 
// DATABASE CONNECTION
// ============================================ 
try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['name']};charset={$db_config['charset']}";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false, // Disable persistent connections on shared hosting
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    
    // Create PDO instance
    $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], $options);
    
    // Set timezone
    $timezone = getenv('APP_TIMEZONE') ?: 'Asia/Jakarta';
    $pdo->exec("SET time_zone = '+07:00'"); // WIB
    
    // Set SQL mode for compatibility
    $pdo->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
    
} catch (PDOException $e) {
    // Log error
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/database_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $errorMsg = "[{$timestamp}] Database Connection Error: " . $e->getMessage() . "\n";
    @file_put_contents($logFile, $errorMsg, FILE_APPEND);
    
    // Determine if it's an AJAX request
    $isAjax = isAjaxRequest();

    if ($isAjax) {
        header('Content-Type: application/json');
        http_response_code(500); // Internal Server Error
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan pada server. (Database tidak tersedia)',
            'error_details' => $isDebug ? $e->getMessage() : 'Internal Server Error'
        ]);
        exit;
    } else {
        // Show appropriate error based on environment
        if ($isProduction && !$isDebug) {
            // Production: User-friendly error
            http_response_code(503);
            die('<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan Tidak Tersedia - Ngulikuy</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .error-container {
            background: white;
            border-radius: 10px;
            padding: 40px;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .error-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        p {
            color: #666;
            line-height: 1.6;
            margin: 0 0 20px 0;
        }
        .error-code {
            font-size: 12px;
            color: #999;
            font-family: monospace;
            margin-top: 20px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>Layanan Sedang Tidak Tersedia</h1>
        <p>Maaf, kami sedang mengalami gangguan teknis. Tim kami sedang bekerja untuk memperbaikinya.</p>
        <p>Silakan coba beberapa saat lagi.</p>
        <p style="margin-top: 30px;">
            <a href="/" style="color: #667eea; text-decoration: none; font-weight: 600;">← Kembali ke Halaman Utama</a>
        </p>
        <div class="error-code">Error Code: DB_CONNECTION_FAILED</div>
    </div>
</body>
</html>
');
        } else {
            // Development: Detailed error
            die("<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Error</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .error-box { background: white; border-left: 4px solid #e74c3c; padding: 20px; border-radius: 5px; max-width: 800px; margin: 0 auto; }
        h1 { color: #e74c3c; margin: 0 0 10px 0; }
        p { color: #666; line-height: 1.6; margin: 0 0 20px 0; }
        .error-details { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 13px; margin: 15px 0; overflow-x: auto; }
        .checklist { margin: 20px 0; }
        .checklist li { margin: 10px 0; }
    </style>
</head>
<body>
    <div class='error-box'>
        <h1>❌ Database Connection Failed</h1>
        <p><strong>Error Message:</strong></p>
        <div class='error-details'>" . htmlspecialchars($e->getMessage()) . "</div>
        
        <h3>🔧 Troubleshooting Steps:</h3>
        <ul class='checklist'>
            <li>✓ Pastikan file <code>.env</code> sudah dibuat dan berisi credentials yang benar</li>
            <li>✓ Cek apakah MySQL service sedang berjalan</li>
            <li>✓ Verifikasi database name, username, dan password di cPanel</li>
            <li>✓ Pastikan database user sudah di-assign ke database</li>
            <li>✓ Cek apakah database sudah diimport</li>
        </ul>
        
        <h3>📋 Current Configuration:</h3>
        <div class='error-details'>
            Host: " . htmlspecialchars($db_config['host']) . "<br>
            Database: " . htmlspecialchars($db_config['name']) . "<br>
            User: " . htmlspecialchars($db_config['user']) . "<br>
            Password: " . (empty($db_config['pass']) ? '(empty)' : '***') . "
        </div>
    </div>
</body>
</html>");
        }
    }
}

// ============================================ 
// DATABASE HELPER CLASS
// ============================================ 
class DatabaseHelper {
    private static $pdo;
    private static $transactionCount = 0;
    
    public static function init($pdoInstance) {
        self::$pdo = $pdoInstance;
    }
    
    /**
     * Begin nested transaction
     */
    public static function beginTransaction() {
        if (self::$transactionCount === 0) {
            self::$pdo->beginTransaction();
        } else {
            // Savepoint for nested transaction
            self::$pdo->exec("SAVEPOINT trans" . self::$transactionCount);
        }
        self::$transactionCount++;
    }
    
    /**
     * Commit nested transaction
     */
    public static function commit() {
        self::$transactionCount--;
        
        if (self::$transactionCount === 0) {
            self::$pdo->commit();
        } else {
            self::$pdo->exec("RELEASE SAVEPOINT trans" . self::$transactionCount);
        }
    }
    
    /**
     * Rollback nested transaction
     */
    public static function rollback() {
        self::$transactionCount--;
        
        if (self::$transactionCount === 0) {
            self::$pdo->rollBack();
        } else {
            self::$pdo->exec("ROLLBACK TO SAVEPOINT trans" . self::$transactionCount);
        }
    }
    
    /**
     * Check if in transaction
     */
    public static function inTransaction() {
        return self::$pdo->inTransaction();
    }
    
    /**
     * Get last insert ID
     */
    public static function lastInsertId() {
        return self::$pdo->lastInsertId();
    }
    
    /**
     * Execute query with retry logic for deadlock
     */
    public static function executeWithRetry($callback, $maxRetries = 3) {
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            try {
                return $callback();
            } catch (PDOException $e) {
                // Check if deadlock (error code 1213)
                if ($e->getCode() == 1213 && $attempt < $maxRetries - 1) {
                    $attempt++;
                    usleep(100000); // Wait 100ms
                    continue;
                }
                throw $e;
            }
        }
    }
}

// Initialize helper
DatabaseHelper::init($pdo);

// ============================================ 
// QUERY CACHE (Simple in-memory cache)
// ============================================ 
class QueryCache {
    private static $cache = [];
    private static $enabled = true;
    private static $ttl = 300; // 5 minutes
    
    public static function get($key) {
        if (!self::$enabled) return null;
        
        if (isset(self::$cache[$key])) {
            $item = self::$cache[$key];
            if (time() < $item['expires']) {
                return $item['data'];
            } else {
                unset(self::$cache[$key]);
            }
        }
        return null;
    }
    
    public static function set($key, $data, $ttl = null) {
        if (!self::$enabled) return;
        
        $ttl = $ttl ?? self::$ttl;
        self::$cache[$key] = [
            'data' => $data,
            'expires' => time() + $ttl
        ];
    }
    
    public static function delete($key) {
        unset(self::$cache[$key]);
    }
    
    public static function clear() {
        self::$cache = [];
    }
    
    public static function disable() {
        self::$enabled = false;
    }
    
    public static function enable() {
        self::$enabled = true;
    }
}

// ============================================ 
// CONNECTION HEALTH CHECK
// ============================================ 
class DatabaseHealth {
    public static function check() {
        global $pdo;
        
        try {
            $stmt = $pdo->query("SELECT 1");
            $result = $stmt->fetch();
            
            if ($result) {
                return [
                    'status' => 'healthy',
                    'message' => 'Database connection OK',
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }
        } catch (PDOException $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Database connection failed',
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
}

// ============================================ 
// SUCCESS - CONNECTION ESTABLISHED
// ============================================ 
// Optionally log successful connection (only in development)
if (!$isProduction && $isDebug) {
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/database_success.log';
    $timestamp = date('Y-m-d H:i:s');
    $successMsg = "[{$timestamp}] Database connected successfully\n";
    @file_put_contents($logFile, $successMsg, FILE_APPEND);
}
