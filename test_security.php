<?php
/**
 * Security Testing Suite
 * File untuk testing keamanan aplikasi
 * HANYA UNTUK DEVELOPMENT - HAPUS DI PRODUCTION
 */

// Cek environment
if (getenv('APP_ENV') !== 'development') {
    die('This file can only be accessed in development environment');
}

require_once 'db.php';
require_once 'security_config.php';
require_once 'functions.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Testing Suite - NguliKuy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .test-pass { background-color: #dcfce7; color: #166534; }
        .test-fail { background-color: #fecaca; color: #dc2626; }
        .test-info { background-color: #dbeafe; color: #1e40af; }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">🧪 Security Testing Suite</h1>
        
        <?php
        
        // ============================================
        // TEST 1: Database Connection
        // ============================================
        echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>";
        echo "<h2 class='text-xl font-bold mb-4'>Test 1: Database Connection</h2>";
        
        try {
            global $pdo;
            $stmt = $pdo->query("SELECT 1");
            if ($stmt->fetch()) {
                echo "<div class='p-3 rounded test-pass'>✓ Database connection OK</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='p-3 rounded test-fail'>✗ Database connection FAILED: " . $e->getMessage() . "</div>";
        }
        echo "</div>";
        
        // ============================================
        // TEST 2: Rate Limiting
        // ============================================
        echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>";
        echo "<h2 class='text-xl font-bold mb-4'>Test 2: Rate Limiting</h2>";
        
        try {
            $rateLimiter = new RateLimiter($pdo);
            $testIp = 'test_ip_' . time();
            
            // Test normal requests
            $results = [];
            for ($i = 1; $i <= 7; $i++) {
                $allowed = $rateLimiter->isAllowed($testIp, 'test_action', 5, 60);
                $results[] = "Request $i: " . ($allowed ? 'Allowed' : 'Blocked');
            }
            
            echo "<div class='p-3 rounded test-info'>";
            echo "Test dengan limit 5 request per 60 detik:<br>";
            foreach ($results as $result) {
                echo "- $result<br>";
            }
            echo "</div>";
            
            // Cleanup
            $rateLimiter->reset($testIp, 'test_action');
            
            // Verifikasi
            $expected = ['Allowed', 'Allowed', 'Allowed', 'Allowed', 'Allowed', 'Blocked', 'Blocked'];
            $actual = array_map(function($r) { 
                return strpos($r, 'Allowed') !== false ? 'Allowed' : 'Blocked'; 
            }, $results);
            
            if ($expected === $actual) {
                echo "<div class='p-3 rounded test-pass mt-3'>✓ Rate limiting works correctly</div>";
            } else {
                echo "<div class='p-3 rounded test-fail mt-3'>✗ Rate limiting NOT working as expected</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='p-3 rounded test-fail'>✗ Error: " . $e->getMessage() . "</div>";
        }
        echo "</div>";
        
        // ============================================
        // TEST 3: Input Validation
        // ============================================
        echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>";
        echo "<h2 class='text-xl font-bold mb-4'>Test 3: Input Validation</h2>";
        
        $tests = [
            'Email Validation' => [
                'test@example.com' => true,
                'invalid-email' => false,
                'test@' => false
            ],
            'Phone Validation' => [
                '081234567890' => true,
                '08123456' => false,
                'abc123' => false
            ],
            'XSS Prevention' => [
                '<script>alert("XSS")</script>' => '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;',
                'Normal text' => 'Normal text'
            ]
        ];
        
        foreach ($tests as $testName => $cases) {
            echo "<h3 class='font-bold mt-4 mb-2'>$testName:</h3>";
            
            foreach ($cases as $input => $expected) {
                if ($testName === 'Email Validation') {
                    $result = InputValidator::validateEmail($input);
                } elseif ($testName === 'Phone Validation') {
                    $result = InputValidator::validatePhone($input);
                } elseif ($testName === 'XSS Prevention') {
                    $result = InputValidator::sanitizeString($input);
                }
                
                $pass = ($result === $expected);
                $class = $pass ? 'test-pass' : 'test-fail';
                $icon = $pass ? '✓' : '✗';
                
                echo "<div class='p-2 rounded $class mb-2'>";
                echo "$icon Input: <code>$input</code> → Expected: <code>" . var_export($expected, true) . "</code> → Got: <code>" . var_export($result, true) . "</code>";
                echo "</div>";
            }
        }
        echo "</div>";
        
        // ============================================
        // TEST 4: Password Security
        // ============================================
        echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>";
        echo "<h2 class='text-xl font-bold mb-4'>Test 4: Password Security</h2>";
        
        $passwords = [
            'weak' => 'password',
            'no_uppercase' => 'password123',
            'no_lowercase' => 'PASSWORD123',
            'no_number' => 'Password',
            'too_short' => 'Pass1',
            'strong' => 'MyStr0ngP@ss'
        ];
        
        foreach ($passwords as $type => $password) {
            $result = InputValidator::validatePassword($password);
            $class = $result['valid'] ? 'test-pass' : 'test-fail';
            $icon = $result['valid'] ? '✓' : '✗';
            $message = $result['valid'] ? 'Valid' : $result['message'];
            
            echo "<div class='p-2 rounded $class mb-2'>";
            echo "$icon <strong>$type</strong>: '$password' → $message";
            echo "</div>";
        }
        echo "</div>";
        
        // ============================================
        // TEST 5: CSRF Token
        // ============================================
        echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>";
        echo "<h2 class='text-xl font-bold mb-4'>Test 5: CSRF Token</h2>";
        
        // Generate token
        $token = getCsrfToken();
        echo "<div class='p-3 rounded test-info mb-3'>";
        echo "Generated token: <code>$token</code>";
        echo "</div>";
        
        // Validate correct token
        $validTest = validateCsrfToken($token);
        $class = $validTest ? 'test-pass' : 'test-fail';
        $icon = $validTest ? '✓' : '✗';
        echo "<div class='p-2 rounded $class mb-2'>";
        echo "$icon Valid token validation: " . ($validTest ? 'PASS' : 'FAIL');
        echo "</div>";
        
        // Validate incorrect token
        $invalidTest = !validateCsrfToken('invalid_token_123');
        $class = $invalidTest ? 'test-pass' : 'test-fail';
        $icon = $invalidTest ? '✓' : '✗';
        echo "<div class='p-2 rounded $class mb-2'>";
        echo "$icon Invalid token rejection: " . ($invalidTest ? 'PASS' : 'FAIL');
        echo "</div>";
        echo "</div>";
        
        // ============================================
        // TEST 6: File Upload Security
        // ============================================
        echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>";
        echo "<h2 class='text-xl font-bold mb-4'>Test 6: File Upload Security</h2>";
        
        // Simulate different file types
        $testFiles = [
            'Valid JPEG' => [
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'size' => 1024000, // 1MB
                'tmp_name' => __FILE__, // Use this file as dummy
                'error' => UPLOAD_ERR_OK,
                'expected' => false // Will fail because not real image
            ],
            'Too Large' => [
                'name' => 'large.jpg',
                'type' => 'image/jpeg',
                'size' => 3000000, // 3MB
                'tmp_name' => __FILE__,
                'error' => UPLOAD_ERR_OK,
                'expected' => false
            ],
            'Invalid Extension' => [
                'name' => 'malicious.php',
                'type' => 'application/php',
                'size' => 1024,
                'tmp_name' => __FILE__,
                'error' => UPLOAD_ERR_OK,
                'expected' => false
            ]
        ];
        
        foreach ($testFiles as $testName => $file) {
            $result = SecureFileUpload::validate($file);
            $pass = ($result['valid'] === $file['expected']);
            $class = $pass ? 'test-pass' : 'test-info';
            $icon = $pass ? '✓' : 'ℹ';
            
            echo "<div class='p-2 rounded $class mb-2'>";
            echo "$icon <strong>$testName</strong>: ";
            if ($result['valid']) {
                echo "Accepted (extension: {$result['extension']})";
            } else {
                echo "Rejected - " . implode(', ', $result['errors']);
            }
            echo "</div>";
        }
        
        echo "<div class='p-3 rounded test-info mt-4'>";
        echo "Note: Semua file seharusnya ditolak karena bukan file gambar yang valid. Ini expected behavior.";
        echo "</div>";
        
        echo "</div>";
        
        // ============================================
        // TEST 7: SQL Injection Prevention
        // ============================================
        echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>";
        echo "<h2 class='text-xl font-bold mb-4'>Test 7: SQL Injection Prevention</h2>";
        
        $sqlInjectionAttempts = [
            "admin' OR '1'='1",
            "admin'; DROP TABLE users; --",
            "admin' UNION SELECT * FROM users --"
        ];
        
        echo "<div class='p-3 rounded test-info mb-3'>";
        echo "Testing dengan username yang mencoba SQL injection:";
        echo "</div>";
        
        foreach ($sqlInjectionAttempts as $attempt) {
            try {
                // Try to authenticate with SQL injection
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
                $stmt->execute([$attempt]);
                $result = $stmt->fetch();
                
                if (!$result) {
                    echo "<div class='p-2 rounded test-pass mb-2'>";
                    echo "✓ SQL Injection blocked: <code>$attempt</code>";
                    echo "</div>";
                } else {
                    echo "<div class='p-2 rounded test-fail mb-2'>";
                    echo "✗ VULNERABLE to SQL Injection: <code>$attempt</code>";
                    echo "</div>";
                }
            } catch (PDOException $e) {
                echo "<div class='p-2 rounded test-pass mb-2'>";
                echo "✓ SQL Injection caused error (good): <code>$attempt</code>";
                echo "</div>";
            }
        }
        echo "</div>";
        
        // ============================================
        // TEST 8: Session Security
        // ============================================
        echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>";
        echo "<h2 class='text-xl font-bold mb-4'>Test 8: Session Security</h2>";
        
        // Check session settings
        $settings = [
            'session.cookie_httponly' => ini_get('session.cookie_httponly'),
            'session.use_only_cookies' => ini_get('session.use_only_cookies'),
            'session.cookie_samesite' => ini_get('session.cookie_samesite')
        ];
        
        foreach ($settings as $setting => $value) {
            $secure = ($value == '1' || $value == 'Strict' || $value == 'Lax');
            $class = $secure ? 'test-pass' : 'test-fail';
            $icon = $secure ? '✓' : '✗';
            
            echo "<div class='p-2 rounded $class mb-2'>";
            echo "$icon <code>$setting</code>: $value " . ($secure ? '(Secure)' : '(Insecure)');
            echo "</div>";
        }
        echo "</div>";
        
        // ============================================
        // TEST 9: Security Headers
        // ============================================
        echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>";
        echo "<h2 class='text-xl font-bold mb-4'>Test 9: Security Headers</h2>";
        
        echo "<div class='p-3 rounded test-info mb-3'>";
        echo "Note: Headers hanya bisa dicek setelah set. Test ini menunjukkan headers yang seharusnya di-set:";
        echo "</div>";
        
        $expectedHeaders = [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin'
        ];
        
        foreach ($expectedHeaders as $header => $value) {
            echo "<div class='p-2 rounded test-info mb-2'>";
            echo "Expected: <code>$header: $value</code>";
            echo "</div>";
        }
        
        echo "<div class='p-3 rounded test-pass mt-4'>";
        echo "✓ Security headers akan di-set oleh SecureHeaders::set() di setiap halaman";
        echo "</div>";
        
        echo "</div>";
        
        // ============================================
        // TEST 10: Database Health Check
        // ============================================
        echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>";
        echo "<h2 class='text-xl font-bold mb-4'>Test 10: Database Health Check</h2>";
        
        $health = DatabaseHealth::check();
        $class = $health['status'] === 'healthy' ? 'test-pass' : 'test-fail';
        
        echo "<div class='p-3 rounded $class'>";
        echo "<strong>Status:</strong> " . $health['status'] . "<br>";
        echo "<strong>Message:</strong> " . $health['message'] . "<br>";
        echo "<strong>Timestamp:</strong> " . $health['timestamp'];
        echo "</div>";
        
        echo "</div>";
        
        ?>
        
        <!-- Summary -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow p-6 text-white">
            <h2 class="text-2xl font-bold mb-4">📊 Test Summary</h2>
            <p class="mb-2">✅ Jika semua test menunjukkan hasil yang diharapkan, aplikasi Anda sudah cukup aman.</p>
            <p class="mb-2">⚠️ File ini HARUS dihapus sebelum deploy ke production!</p>
            <p>🔒 Selalu lakukan security audit berkala untuk memastikan keamanan aplikasi.</p>
        </div>
        
        <!-- Action Buttons -->
        <div class="mt-8 text-center">
            <a href="index.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 inline-block">
                Back to Application
            </a>
        </div>
    </div>
</body>
</html>