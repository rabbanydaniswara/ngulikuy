<?php
/**
 * Security Configuration File
 * File baru untuk menangani keamanan aplikasi
 */

// Pastikan file ini tidak diakses langsung
if (!defined('APP_INIT')) {
    die('Direct access not permitted');
}

// ============================================
// 1. RATE LIMITING
// ============================================

class RateLimiter {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->createTableIfNotExists();
    }
    
    private function createTableIfNotExists() {
        $sql = "CREATE TABLE IF NOT EXISTS rate_limits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            identifier VARCHAR(255) NOT NULL,
            action VARCHAR(100) NOT NULL,
            attempts INT DEFAULT 1,
            last_attempt DATETIME DEFAULT CURRENT_TIMESTAMP,
            blocked_until DATETIME NULL,
            INDEX idx_identifier_action (identifier, action)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Rate limiter table creation failed: " . $e->getMessage());
        }
    }
    
    /**
     * Cek apakah request diperbolehkan
     * @param string $identifier - IP address atau user ID
     * @param string $action - login, register, upload, etc
     * @param int $maxAttempts - maksimal percobaan
     * @param int $timeWindow - dalam detik
     * @return bool
     */
    public function isAllowed($identifier, $action, $maxAttempts = 5, $timeWindow = 300) {
        // Bersihkan record lama
        $this->cleanup();
        
        // Cek apakah sedang diblok
        $sql = "SELECT blocked_until FROM rate_limits 
                WHERE identifier = ? AND action = ? 
                AND blocked_until > NOW()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$identifier, $action]);
        
        if ($stmt->fetch()) {
            return false; // Masih diblok
        }
        
        // Hitung attempts dalam time window
        $sql = "SELECT attempts, last_attempt FROM rate_limits 
                WHERE identifier = ? AND action = ? 
                AND last_attempt > DATE_SUB(NOW(), INTERVAL ? SECOND)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$identifier, $action, $timeWindow]);
        $record = $stmt->fetch();
        
        if (!$record) {
            // Record baru
            $this->recordAttempt($identifier, $action);
            return true;
        }
        
        if ($record['attempts'] >= $maxAttempts) {
            // Block untuk 15 menit
            $this->blockUser($identifier, $action, 900);
            return false;
        }
        
        // Update attempt
        $this->incrementAttempt($identifier, $action);
        return true;
    }
    
    private function recordAttempt($identifier, $action) {
        $sql = "INSERT INTO rate_limits (identifier, action, attempts, last_attempt) 
                VALUES (?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE 
                attempts = 1, last_attempt = NOW()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$identifier, $action]);
    }
    
    private function incrementAttempt($identifier, $action) {
        $sql = "UPDATE rate_limits 
                SET attempts = attempts + 1, last_attempt = NOW() 
                WHERE identifier = ? AND action = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$identifier, $action]);
    }
    
    private function blockUser($identifier, $action, $seconds) {
        $sql = "UPDATE rate_limits 
                SET blocked_until = DATE_ADD(NOW(), INTERVAL ? SECOND) 
                WHERE identifier = ? AND action = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$seconds, $identifier, $action]);
    }
    
    public function reset($identifier, $action) {
        $sql = "DELETE FROM rate_limits WHERE identifier = ? AND action = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$identifier, $action]);
    }
    
    private function cleanup() {
        // Hapus record lebih dari 1 hari
        $sql = "DELETE FROM rate_limits 
                WHERE last_attempt < DATE_SUB(NOW(), INTERVAL 1 DAY)";
        $this->pdo->exec($sql);
    }
}

// ============================================
// 2. INPUT VALIDATION
// ============================================

class InputValidator {
    
    /**
     * Sanitize string untuk mencegah XSS
     */
    public static function sanitizeString($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validasi email
     */
    public static function validateEmail($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validasi nomor telepon Indonesia
     */
    public static function validatePhone($phone) {
        // Hapus karakter non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Format Indonesia: 08xx, 62xxx, atau +62xxx
        if (preg_match('/^(\+?62|0)8[1-9][0-9]{6,9}$/', $phone)) {
            return $phone;
        }
        return false;
    }
    
    /**
     * Validasi password kuat
     */
    public static function validatePassword($password) {
        // Minimal 8 karakter, ada huruf besar, kecil, dan angka
        if (strlen($password) < 8) {
            return ['valid' => false, 'message' => 'Password minimal 8 karakter'];
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'message' => 'Password harus mengandung huruf besar'];
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'message' => 'Password harus mengandung huruf kecil'];
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'message' => 'Password harus mengandung angka'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validasi tanggal
     */
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Validasi integer dalam range
     */
    public static function validateIntRange($value, $min = null, $max = null) {
        if (!is_numeric($value)) {
            return false;
        }
        
        $intValue = (int)$value;
        
        if ($min !== null && $intValue < $min) {
            return false;
        }
        
        if ($max !== null && $intValue > $max) {
            return false;
        }
        
        return $intValue;
    }
}

// ============================================
// 3. LOGGING SYSTEM
// ============================================

class SecurityLogger {
    private static $logFile = 'logs/security.log';
    
    public static function init() {
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    public static function log($level, $message, $context = []) {
        self::init();
        
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $user = $_SESSION['user'] ?? 'Guest';
        
        $logEntry = sprintf(
            "[%s] [%s] [IP: %s] [User: %s] %s\n",
            $timestamp,
            $level,
            $ip,
            $user,
            $message
        );
        
        if (!empty($context)) {
            $logEntry .= "Context: " . json_encode($context) . "\n";
        }
        
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
    }
    
    public static function logLoginAttempt($username, $success) {
        $message = $success 
            ? "Successful login: {$username}" 
            : "Failed login attempt: {$username}";
        self::log($success ? 'INFO' : 'WARNING', $message);
    }
    
    public static function logSecurityEvent($event, $details = []) {
        self::log('SECURITY', $event, $details);
    }
    
    public static function logError($error, $context = []) {
        self::log('ERROR', $error, $context);
    }
}

// ============================================
// 4. SECURE HEADERS
// ============================================

class SecureHeaders {
    
    public static function set() {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy (sesuaikan dengan kebutuhan)
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://unpkg.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com",
            "img-src 'self' data: https: http:",
            "font-src 'self' https://fonts.gstatic.com",
            "connect-src 'self'",
            "frame-ancestors 'self'"
        ]);
        header("Content-Security-Policy: $csp");
        
        // HSTS (hanya jika menggunakan HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}

// ============================================
// 5. FILE UPLOAD SECURITY
// ============================================

class SecureFileUpload {
    
    private static $allowedMimes = [
        'image/jpeg',
        'image/jpg', 
        'image/png',
        'image/gif'
    ];
    
    private static $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    private static $maxSize = 2097152; // 2MB
    
    public static function validate($file) {
        $errors = [];
        
        // Cek error upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'errors' => ['Upload gagal. Error code: ' . $file['error']]];
        }
        
        // Cek ukuran
        if ($file['size'] > self::$maxSize) {
            $errors[] = 'Ukuran file maksimal 2MB';
        }
        
        // Cek ekstensi
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::$allowedExtensions)) {
            $errors[] = 'Ekstensi file tidak diizinkan';
        }
        
        // Cek MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, self::$allowedMimes)) {
            $errors[] = 'Tipe file tidak diizinkan';
        }
        
        // Cek apakah benar-benar gambar
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $errors[] = 'File bukan gambar yang valid';
        }
        
        if (!empty($errors)) {
            return ['valid' => false, 'errors' => $errors];
        }
        
        return ['valid' => true, 'extension' => $extension];
    }
    
    public static function generateSecureFilename($extension) {
        return 'worker_' . bin2hex(random_bytes(16)) . '.' . $extension;
    }
}

// ============================================
// 6. SESSION SECURITY
// ============================================

class SecureSession {
    
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            // Konfigurasi session yang aman
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                ini_set('session.cookie_secure', 1);
            }
            
            session_start();
            
            // Regenerate session ID secara periodik
            self::regenerateIfNeeded();
            
            // Validasi session
            self::validate();
        }
    }
    
    private static function regenerateIfNeeded() {
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 menit
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    private static function validate() {
        // Validasi user agent
        if (!isset($_SESSION['user_agent'])) {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        } elseif ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            // Session hijacking detected
            SecurityLogger::logSecurityEvent('Session hijacking attempt detected');
            self::destroy();
            header('Location: index.php?error=session_invalid');
            exit;
        }
        
        // Validasi IP (opsional, bisa menyebabkan masalah dengan dynamic IP)
        // Uncomment jika diperlukan
        /*
        if (!isset($_SESSION['ip_address'])) {
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        } elseif ($_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
            SecurityLogger::logSecurityEvent('IP address mismatch detected');
            self::destroy();
            header('Location: index.php?error=session_invalid');
            exit;
        }
        */
    }
    
    public static function destroy() {
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
}

// ============================================
// 7. SQL INJECTION PREVENTION
// ============================================

class SafeQuery {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Execute safe SELECT query
     */
    public function select($table, $columns = '*', $where = [], $orderBy = null, $limit = null) {
        $sql = "SELECT {$columns} FROM {$table}";
        
        if (!empty($where)) {
            $conditions = [];
            foreach (array_keys($where) as $column) {
                $conditions[] = "{$column} = ?";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($where));
        
        return $stmt;
    }
}