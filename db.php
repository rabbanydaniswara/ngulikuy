<?php
/**
 * Improved Database Connection
 * PERBAIKAN: Definisi APP_INIT dipindah ke paling atas
 */

// ============================================
// DEFINISI KONSTANTA APP_INIT
// ============================================
// Konstanta ini HARUS didefinisikan PERTAMA KALI
// sebelum file lain di-load
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// ============================================
// KONFIGURASI DATABASE
// ============================================

// Gunakan environment variables untuk production
// Buat file .env untuk menyimpan kredensial (jangan commit ke git)
$db_config = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'name' => getenv('DB_NAME') ?: 'ngulikuy_db',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4'
];

// ============================================
// KONEKSI DATABASE DENGAN ERROR HANDLING
// ============================================

try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['name']};charset={$db_config['charset']}";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    
    $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], $options);
    
    // Set timezone MySQL
    $pdo->exec("SET time_zone = '+07:00'"); // WIB
    
} catch (PDOException $e) {
    // JANGAN tampilkan error detail di production
    $isDevelopment = (getenv('APP_ENV') === 'development' || !getenv('APP_ENV'));
    
    if ($isDevelopment) {
        die("Database Connection Failed: " . $e->getMessage());
    } else {
        // Log error
        error_log("Database Error: " . $e->getMessage());
        
        // Tampilkan pesan user-friendly
        die("
        <!DOCTYPE html>
        <html>
        <head>
            <title>Database Error</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .error-box { background: #fee; border: 1px solid #fcc; padding: 20px; border-radius: 5px; max-width: 500px; margin: 0 auto; }
                h1 { color: #c00; }
            </style>
        </head>
        <body>
            <div class='error-box'>
                <h1>Layanan Tidak Tersedia</h1>
                <p>Maaf, sistem sedang mengalami gangguan. Silakan coba beberapa saat lagi.</p>
                <p>Jika masalah berlanjut, hubungi administrator.</p>
            </div>
        </body>
        </html>
        ");
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
            // Savepoint untuk nested transaction
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
            // Release savepoint
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
            // Rollback to savepoint
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
     * Execute query dengan retry logic untuk deadlock
     */
    public static function executeWithRetry($callback, $maxRetries = 3) {
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            try {
                return $callback();
            } catch (PDOException $e) {
                // Cek jika deadlock (error code 1213)
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

// Inisialisasi helper
DatabaseHelper::init($pdo);

// ============================================
// QUERY CACHE SEDERHANA
// ============================================

class QueryCache {
    private static $cache = [];
    private static $enabled = true;
    private static $ttl = 300; // 5 menit
    
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
// DATABASE BACKUP (OPSIONAL)
// ============================================

class DatabaseBackup {
    
    public static function create($outputPath = null) {
        global $db_config;
        
        if (!$outputPath) {
            $outputPath = 'backups/backup_' . date('Y-m-d_H-i-s') . '.sql';
        }
        
        // Buat direktori jika belum ada
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Gunakan mysqldump jika tersedia
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg($db_config['user']),
            escapeshellarg($db_config['pass']),
            escapeshellarg($db_config['host']),
            escapeshellarg($db_config['name']),
            escapeshellarg($outputPath)
        );
        
        exec($command, $output, $returnVar);
        
        if ($returnVar === 0 && file_exists($outputPath)) {
            return [
                'success' => true,
                'file' => $outputPath,
                'size' => filesize($outputPath)
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Backup failed'
        ];
    }
    
    /**
     * Backup otomatis setiap hari (panggil via cron job)
     */
    public static function autoBackup() {
        $backupDir = 'backups';
        
        // Buat backup
        $result = self::create();
        
        if ($result['success']) {
            // Hapus backup lebih dari 7 hari
            self::cleanOldBackups($backupDir, 7);
            
            return $result;
        }
        
        return $result;
    }
    
    private static function cleanOldBackups($dir, $days) {
        if (!is_dir($dir)) return;
        
        $files = glob($dir . '/backup_*.sql');
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * $days) {
                    unlink($file);
                }
            }
        }
    }
}

// ============================================
// HEALTH CHECK
// ============================================

class DatabaseHealth {
    
    public static function check() {
        global $pdo;
        
        try {
            // Test query
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