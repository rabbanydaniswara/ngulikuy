<?php
/**
 * Deployment Readiness Checker
 * Jalankan script ini untuk memastikan aplikasi siap di-deploy
 * 
 * Usage: php check_deployment.php
 * atau akses via browser (HARUS DIHAPUS SETELAH CEK!)
 */

// Prevent direct access in production
if (getenv('APP_ENV') === 'production') {
    die('This file should not exist in production environment!');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deployment Checker - NguliKuy</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
        }
        .content {
            padding: 30px;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .check-pass {
            background: #dcfce7;
            border-left: 4px solid #16a34a;
        }
        .check-fail {
            background: #fecaca;
            border-left: 4px solid #dc2626;
        }
        .check-warning {
            background: #fef3c7;
            border-left: 4px solid #ca8a04;
        }
        .check-info {
            background: #dbeafe;
            border-left: 4px solid #2563eb;
        }
        .status-icon {
            font-size: 24px;
            font-weight: bold;
            min-width: 30px;
        }
        .category {
            font-size: 20px;
            font-weight: bold;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }
        .summary {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 5px;
            font-weight: 600;
        }
        .btn:hover {
            background: #2563eb;
        }
        .code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 NguliKuy Deployment Checker</h1>
            <p>Memastikan aplikasi siap untuk production</p>
        </div>
        
        <div class="content">
            
            <?php
            
            $checks = [];
            $passed = 0;
            $failed = 0;
            $warnings = 0;
            
            // ========================================
            // 1. FILE CHECKS
            // ========================================
            
            echo '<div class="category">📁 File Structure</div>';
            
            $requiredFiles = [
                'db.php' => 'Database connection',
                'functions.php' => 'Core functions',
                'security_config.php' => 'Security configuration',
                'performance.php' => 'Performance optimization',
                'index.php' => 'Main login page',
                'admin_dashboard.php' => 'Admin dashboard',
                'customer_dashboard.php' => 'Customer dashboard',
                'worker_dashboard.php' => 'Worker dashboard',
                'ajax_handler.php' => 'AJAX handler',
                '.htaccess' => 'Apache configuration',
                '.gitignore' => 'Git ignore file'
            ];
            
            foreach ($requiredFiles as $file => $desc) {
                $exists = file_exists($file);
                $class = $exists ? 'check-pass' : 'check-fail';
                $icon = $exists ? '✓' : '✗';
                
                if ($exists) $passed++; else $failed++;
                
                echo "<div class='check-item $class'>";
                echo "<span><span class='status-icon'>$icon</span> <strong>$file</strong> - $desc</span>";
                echo "<span>" . ($exists ? 'Found' : 'Missing') . "</span>";
                echo "</div>";
            }
            
            // Check test files (should NOT exist)
            echo '<div class="category">🧪 Test Files (Should NOT Exist in Production)</div>';
            
            $testFiles = [
                'test_security.php',
                'phpinfo.php',
                'test.php',
                'debug.php'
            ];
            
            foreach ($testFiles as $file) {
                $exists = file_exists($file);
                $class = !$exists ? 'check-pass' : 'check-fail';
                $icon = !$exists ? '✓' : '✗';
                
                if (!$exists) $passed++; else $failed++;
                
                echo "<div class='check-item $class'>";
                echo "<span><span class='status-icon'>$icon</span> <strong>$file</strong></span>";
                echo "<span>" . (!$exists ? 'Not Found (Good)' : 'EXISTS - DELETE THIS!') . "</span>";
                echo "</div>";
            }
            
            // ========================================
            // 2. DIRECTORY CHECKS
            // ========================================
            
            echo '<div class="category">📂 Directory Structure</div>';
            
            $requiredDirs = [
                'uploads/workers' => ['writable' => true],
                'logs' => ['writable' => true],
                'backups' => ['writable' => true],
                'admin_pages' => ['writable' => false]
            ];
            
            foreach ($requiredDirs as $dir => $config) {
                $exists = is_dir($dir);
                $writable = is_writable($dir);
                
                if (!$exists) {
                    $class = 'check-fail';
                    $icon = '✗';
                    $status = 'Missing';
                    $failed++;
                } elseif ($config['writable'] && !$writable) {
                    $class = 'check-warning';
                    $icon = '⚠';
                    $status = 'Not Writable';
                    $warnings++;
                } else {
                    $class = 'check-pass';
                    $icon = '✓';
                    $status = 'OK';
                    $passed++;
                }
                
                echo "<div class='check-item $class'>";
                echo "<span><span class='status-icon'>$icon</span> <strong>$dir</strong></span>";
                echo "<span>$status</span>";
                echo "</div>";
            }
            
            // ========================================
            // 3. PHP CONFIGURATION
            // ========================================
            
            echo '<div class="category">⚙️ PHP Configuration</div>';
            
            // PHP Version
            $phpVersion = PHP_VERSION;
            $versionOK = version_compare($phpVersion, '7.4.0', '>=');
            $class = $versionOK ? 'check-pass' : 'check-fail';
            $icon = $versionOK ? '✓' : '✗';
            
            if ($versionOK) $passed++; else $failed++;
            
            echo "<div class='check-item $class'>";
            echo "<span><span class='status-icon'>$icon</span> <strong>PHP Version</strong></span>";
            echo "<span>$phpVersion " . ($versionOK ? '(OK)' : '(Need 7.4+)') . "</span>";
            echo "</div>";
            
            // Required Extensions
            $requiredExtensions = [
                'pdo_mysql' => 'MySQL PDO',
                'gd' => 'Image Processing',
                'mbstring' => 'Multibyte String',
                'session' => 'Session Support',
                'json' => 'JSON Support'
            ];
            
            foreach ($requiredExtensions as $ext => $desc) {
                $loaded = extension_loaded($ext);
                $class = $loaded ? 'check-pass' : 'check-fail';
                $icon = $loaded ? '✓' : '✗';
                
                if ($loaded) $passed++; else $failed++;
                
                echo "<div class='check-item $class'>";
                echo "<span><span class='status-icon'>$icon</span> <strong>$ext</strong> - $desc</span>";
                echo "<span>" . ($loaded ? 'Loaded' : 'Missing') . "</span>";
                echo "</div>";
            }
            
            // Upload settings
            $maxUpload = ini_get('upload_max_filesize');
            $postMax = ini_get('post_max_size');
            $memoryLimit = ini_get('memory_limit');
            
            echo "<div class='check-item check-info'>";
            echo "<span><span class='status-icon'>ℹ</span> <strong>Upload Max Filesize</strong></span>";
            echo "<span>$maxUpload</span>";
            echo "</div>";
            
            echo "<div class='check-item check-info'>";
            echo "<span><span class='status-icon'>ℹ</span> <strong>Post Max Size</strong></span>";
            echo "<span>$postMax</span>";
            echo "</div>";
            
            echo "<div class='check-item check-info'>";
            echo "<span><span class='status-icon'>ℹ</span> <strong>Memory Limit</strong></span>";
            echo "<span>$memoryLimit</span>";
            echo "</div>";
            
            // ========================================
            // 4. ENVIRONMENT CHECKS
            // ========================================
            
            echo '<div class="category">🌍 Environment Configuration</div>';
            
            // Check .env file
            $envExists = file_exists('.env');
            $class = $envExists ? 'check-pass' : 'check-warning';
            $icon = $envExists ? '✓' : '⚠';
            
            if ($envExists) $passed++; else $warnings++;
            
            echo "<div class='check-item $class'>";
            echo "<span><span class='status-icon'>$icon</span> <strong>.env file</strong></span>";
            echo "<span>" . ($envExists ? 'Found' : 'Not Found - Create manually') . "</span>";
            echo "</div>";
            
            // Check APP_ENV
            $appEnv = getenv('APP_ENV') ?: 'Not Set';
            $isProduction = ($appEnv === 'production');
            $class = $isProduction ? 'check-pass' : 'check-warning';
            $icon = $isProduction ? '✓' : '⚠';
            
            if ($isProduction) $passed++; else $warnings++;
            
            echo "<div class='check-item $class'>";
            echo "<span><span class='status-icon'>$icon</span> <strong>APP_ENV</strong></span>";
            echo "<span>$appEnv</span>";
            echo "</div>";
            
            // ========================================
            // 5. DATABASE CHECKS
            // ========================================
            
            echo '<div class="category">🗄️ Database Connection</div>';
            
            try {
                require_once 'db.php';
                
                // Test connection
                $stmt = $pdo->query("SELECT 1");
                $result = $stmt->fetch();
                
                if ($result) {
                    $passed++;
                    echo "<div class='check-item check-pass'>";
                    echo "<span><span class='status-icon'>✓</span> <strong>Database Connection</strong></span>";
                    echo "<span>Connected</span>";
                    echo "</div>";
                    
                    // Check tables
                    $requiredTables = ['users', 'workers', 'jobs', 'reviews'];
                    
                    foreach ($requiredTables as $table) {
                        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                        $exists = $stmt->rowCount() > 0;
                        
                        $class = $exists ? 'check-pass' : 'check-fail';
                        $icon = $exists ? '✓' : '✗';
                        
                        if ($exists) $passed++; else $failed++;
                        
                        echo "<div class='check-item $class'>";
                        echo "<span><span class='status-icon'>$icon</span> <strong>Table: $table</strong></span>";
                        echo "<span>" . ($exists ? 'Exists' : 'Missing') . "</span>";
                        echo "</div>";
                    }
                    
                    // Check rate_limits table
                    $stmt = $pdo->query("SHOW TABLES LIKE 'rate_limits'");
                    $rateTableExists = $stmt->rowCount() > 0;
                    
                    $class = $rateTableExists ? 'check-pass' : 'check-warning';
                    $icon = $rateTableExists ? '✓' : '⚠';
                    
                    if ($rateTableExists) $passed++; else $warnings++;
                    
                    echo "<div class='check-item $class'>";
                    echo "<span><span class='status-icon'>$icon</span> <strong>Table: rate_limits</strong></span>";
                    echo "<span>" . ($rateTableExists ? 'Exists' : 'Missing - Run add_indexes.sql') . "</span>";
                    echo "</div>";
                }
                
            } catch (Exception $e) {
                $failed++;
                echo "<div class='check-item check-fail'>";
                echo "<span><span class='status-icon'>✗</span> <strong>Database Connection</strong></span>";
                echo "<span>Failed</span>";
                echo "</div>";
                
                echo "<div class='check-item check-fail'>";
                echo "<span style='width:100%'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</span>";
                echo "</div>";
            }
            
            // ========================================
            // 6. SECURITY CHECKS
            // ========================================
            
            echo '<div class="category">🔒 Security Configuration</div>';
            
            // Check if security_config.php is accessible
            $securityConfigOK = file_exists('security_config.php');
            $class = $securityConfigOK ? 'check-pass' : 'check-fail';
            $icon = $securityConfigOK ? '✓' : '✗';
            
            if ($securityConfigOK) $passed++; else $failed++;
            
            echo "<div class='check-item $class'>";
            echo "<span><span class='status-icon'>$icon</span> <strong>Security Config</strong></span>";
            echo "<span>" . ($securityConfigOK ? 'Found' : 'Missing') . "</span>";
            echo "</div>";
            
            // Session security
            $httpOnly = ini_get('session.cookie_httponly');
            $useOnlyCookies = ini_get('session.use_only_cookies');
            
            $sessionOK = ($httpOnly && $useOnlyCookies);
            $class = $sessionOK ? 'check-pass' : 'check-warning';
            $icon = $sessionOK ? '✓' : '⚠';
            
            if ($sessionOK) $passed++; else $warnings++;
            
            echo "<div class='check-item $class'>";
            echo "<span><span class='status-icon'>$icon</span> <strong>Session Security</strong></span>";
            echo "<span>" . ($sessionOK ? 'Configured' : 'Check php.ini') . "</span>";
            echo "</div>";
            
            // ========================================
            // SUMMARY
            // ========================================
            
            $total = $passed + $failed + $warnings;
            $percentage = $total > 0 ? round(($passed / $total) * 100) : 0;
            
            $overallStatus = 'FAIL';
            $overallClass = 'check-fail';
            
            if ($failed === 0 && $warnings === 0) {
                $overallStatus = 'READY TO DEPLOY ✅';
                $overallClass = 'check-pass';
            } elseif ($failed === 0) {
                $overallStatus = 'READY WITH WARNINGS ⚠️';
                $overallClass = 'check-warning';
            }
            
            ?>
            
            <div class="summary">
                <h2 style="margin-top:0">📊 Summary</h2>
                
                <div class="summary-item">
                    <strong>Total Checks:</strong>
                    <span><?php echo $total; ?></span>
                </div>
                
                <div class="summary-item">
                    <strong>Passed:</strong>
                    <span style="color: #16a34a; font-weight: bold;"><?php echo $passed; ?></span>
                </div>
                
                <div class="summary-item">
                    <strong>Failed:</strong>
                    <span style="color: #dc2626; font-weight: bold;"><?php echo $failed; ?></span>
                </div>
                
                <div class="summary-item">
                    <strong>Warnings:</strong>
                    <span style="color: #ca8a04; font-weight: bold;"><?php echo $warnings; ?></span>
                </div>
                
                <div class="summary-item">
                    <strong>Success Rate:</strong>
                    <span style="font-weight: bold;"><?php echo $percentage; ?>%</span>
                </div>
                
                <div class="check-item <?php echo $overallClass; ?>" style="margin-top: 20px; font-size: 18px;">
                    <strong>Overall Status:</strong>
                    <strong><?php echo $overallStatus; ?></strong>
                </div>
                
                <?php if ($failed > 0): ?>
                <div style="margin-top: 20px; padding: 15px; background: #fecaca; border-radius: 6px; border-left: 4px solid #dc2626;">
                    <strong>⚠️ CRITICAL:</strong> Fix all failed checks before deploying to production!
                </div>
                <?php endif; ?>
                
                <?php if ($warnings > 0): ?>
                <div style="margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 6px; border-left: 4px solid #ca8a04;">
                    <strong>⚠️ NOTE:</strong> Address warnings for optimal security and performance.
                </div>
                <?php endif; ?>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="index.php" class="btn">← Back to Application</a>
                <a href="?refresh=1" class="btn" style="background: #10b981;">🔄 Re-check</a>
            </div>
            
            <div style="margin-top: 30px; padding: 20px; background: #f3f4f6; border-radius: 8px; text-align: center;">
                <p style="margin: 0; color: #6b7280; font-size: 14px;">
                    <strong>⚠️ IMPORTANT:</strong> Delete <span class="code">check_deployment.php</span> after deployment!
                </p>
            </div>
            
        </div>
    </div>
</body>
</html>