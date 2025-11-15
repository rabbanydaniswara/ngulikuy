<?php
/**
 * Deployment Preparation Tool
 * Jalankan ini di local sebelum upload ke hosting
 * 
 * Usage: php prepare_deployment.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deployment Preparation - NguliKuy</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .content {
            padding: 40px;
        }
        .section {
            margin-bottom: 40px;
        }
        .section-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #3b82f6;
            color: #1f2937;
        }
        .check-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            margin: 10px 0;
            border-radius: 8px;
            font-size: 15px;
        }
        .check-pass {
            background: #d1fae5;
            border-left: 5px solid #10b981;
        }
        .check-fail {
            background: #fee2e2;
            border-left: 5px solid #ef4444;
        }
        .check-warning {
            background: #fef3c7;
            border-left: 5px solid #f59e0b;
        }
        .check-info {
            background: #dbeafe;
            border-left: 5px solid #3b82f6;
        }
        .icon {
            font-size: 24px;
            margin-right: 15px;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 10px 5px;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
        }
        .btn-success {
            background: #10b981;
        }
        .btn-success:hover {
            background: #059669;
        }
        .btn-danger {
            background: #ef4444;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .code {
            background: #f3f4f6;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #dc2626;
        }
        .file-list {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .file-list ul {
            list-style: none;
        }
        .file-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .file-list li:last-child {
            border-bottom: none;
        }
        .summary-box {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 30px;
            border-radius: 10px;
            margin-top: 30px;
            border: 2px solid #3b82f6;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 16px;
        }
        .summary-item strong {
            color: #1f2937;
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e5e7eb;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            transition: width 0.5s ease;
        }
        .action-list {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .action-list h3 {
            color: #92400e;
            margin-bottom: 15px;
        }
        .action-list ol {
            margin-left: 20px;
        }
        .action-list li {
            margin: 10px 0;
            color: #78350f;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Deployment Preparation Tool</h1>
            <p>Memastikan project siap untuk upload ke hosting</p>
        </div>
        
        <div class="content">
            <?php
            
            $passed = 0;
            $failed = 0;
            $warnings = 0;
            $issues = [];
            $actions = [];
            
            // ============================================
            // 1. CHECK FILES YANG HARUS ADA
            // ============================================
            
            echo '<div class="section">';
            echo '<div class="section-title">📁 File Structure Check</div>';
            
            $requiredFiles = [
                '.htaccess' => 'Apache configuration',
                'db.php' => 'Database connection',
                'functions.php' => 'Core functions',
                'security_config.php' => 'Security configuration',
                'index.php' => 'Login page',
                'admin_dashboard.php' => 'Admin dashboard',
                'customer_dashboard.php' => 'Customer dashboard',
                'worker_dashboard.php' => 'Worker dashboard',
                'ajax_handler.php' => 'AJAX handler'
            ];
            
            foreach ($requiredFiles as $file => $desc) {
                $exists = file_exists($file);
                $class = $exists ? 'check-pass' : 'check-fail';
                $icon = $exists ? '✅' : '❌';
                
                if ($exists) {
                    $passed++;
                } else {
                    $failed++;
                    $issues[] = "File <span class='code'>$file</span> tidak ditemukan";
                }
                
                echo "<div class='check-item $class'>";
                echo "<span><span class='icon'>$icon</span> <strong>$file</strong> - $desc</span>";
                echo "<span>" . ($exists ? 'Found' : 'Missing') . "</span>";
                echo "</div>";
            }
            
            echo '</div>';
            
            // ============================================
            // 2. CHECK FILES YANG HARUS DIHAPUS
            // ============================================
            
            echo '<div class="section">';
            echo '<div class="section-title">🗑️ Files to Remove (Security)</div>';
            
            $shouldNotExist = [
                'check_deployment.php' => 'Deployment checker',
                'test_security.php' => 'Security test file',
                'phpinfo.php' => 'PHP info file',
                'prepare_deployment.php' => 'This file (delete after checking)',
                '.git' => 'Git repository',
                '.gitignore' => 'Git ignore (optional)',
                'README_DEV.md' => 'Development readme'
            ];
            
            $filesToDelete = [];
            
            foreach ($shouldNotExist as $file => $desc) {
                $exists = file_exists($file);
                $class = !$exists ? 'check-pass' : 'check-warning';
                $icon = !$exists ? '✅' : '⚠️';
                
                if (!$exists) {
                    $passed++;
                } else {
                    $warnings++;
                    $filesToDelete[] = $file;
                }
                
                echo "<div class='check-item $class'>";
                echo "<span><span class='icon'>$icon</span> <strong>$file</strong> - $desc</span>";
                echo "<span>" . (!$exists ? 'Not Found (Good)' : 'EXISTS - DELETE!') . "</span>";
                echo "</div>";
            }
            
            echo '</div>';
            
            // ============================================
            // 3. CHECK DIRECTORIES
            // ============================================
            
            echo '<div class="section">';
            echo '<div class="section-title">📂 Directory Check</div>';
            
            $requiredDirs = [
                'admin_pages' => false,
                'uploads' => true,
                'uploads/workers' => true,
                'logs' => true,
                'backups' => true
            ];
            
            foreach ($requiredDirs as $dir => $needsWritable) {
                $exists = is_dir($dir);
                $writable = is_writable($dir);
                
                if (!$exists) {
                    $class = 'check-fail';
                    $icon = '❌';
                    $status = 'Missing';
                    $failed++;
                    $issues[] = "Directory <span class='code'>$dir</span> tidak ditemukan";
                } elseif ($needsWritable && !$writable) {
                    $class = 'check-warning';
                    $icon = '⚠️';
                    $status = 'Not Writable (Fix di server)';
                    $warnings++;
                } else {
                    $class = 'check-pass';
                    $icon = '✅';
                    $status = 'OK';
                    $passed++;
                }
                
                echo "<div class='check-item $class'>";
                echo "<span><span class='icon'>$icon</span> <strong>$dir/</strong></span>";
                echo "<span>$status</span>";
                echo "</div>";
            }
            
            echo '</div>';
            
            // ============================================
            // 4. CHECK ENVIRONMENT FILE
            // ============================================
            
            echo '<div class="section">';
            echo '<div class="section-title">🔧 Environment Configuration</div>';
            
            $envExample = file_exists('.env.example');
            $envExists = file_exists('.env');
            
            // .env.example should exist
            if ($envExample) {
                $passed++;
                echo "<div class='check-item check-pass'>";
                echo "<span><span class='icon'>✅</span> <strong>.env.example</strong> - Template file</span>";
                echo "<span>Found</span>";
                echo "</div>";
            } else {
                $failed++;
                echo "<div class='check-item check-fail'>";
                echo "<span><span class='icon'>❌</span> <strong>.env.example</strong> - Template file</span>";
                echo "<span>Missing - Create from artifacts</span>";
                echo "</div>";
                $issues[] = "File <span class='code'>.env.example</span> tidak ada";
            }
            
            // .env should NOT exist in repo (akan dibuat di server)
            if (!$envExists) {
                $passed++;
                echo "<div class='check-item check-pass'>";
                echo "<span><span class='icon'>✅</span> <strong>.env</strong> - Will be created in server</span>";
                echo "<span>Not in repo (Good)</span>";
                echo "</div>";
            } else {
                $warnings++;
                echo "<div class='check-item check-warning'>";
                echo "<span><span class='icon'>⚠️</span> <strong>.env</strong> - Contains credentials</span>";
                echo "<span>Found - Don't upload to server!</span>";
                echo "</div>";
                $actions[] = "Jangan upload file <span class='code'>.env</span> ke server";
            }
            
            echo '</div>';
            
            // ============================================
            // 5. CHECK DATABASE EXPORT
            // ============================================
            
            echo '<div class="section">';
            echo '<div class="section-title">💾 Database Export Check</div>';
            
            $sqlFiles = glob('*.sql');
            
            if (count($sqlFiles) > 0) {
                $passed++;
                echo "<div class='check-item check-pass'>";
                echo "<span><span class='icon'>✅</span> <strong>Database Export</strong></span>";
                echo "<span>" . count($sqlFiles) . " file(s) found</span>";
                echo "</div>";
                
                echo "<div class='file-list'>";
                echo "<strong>SQL Files:</strong>";
                echo "<ul>";
                foreach ($sqlFiles as $sql) {
                    $size = filesize($sql);
                    $sizeKB = round($size / 1024, 2);
                    echo "<li>📄 $sql (" . $sizeKB . " KB)</li>";
                }
                echo "</ul>";
                echo "</div>";
            } else {
                $warnings++;
                echo "<div class='check-item check-warning'>";
                echo "<span><span class='icon'>⚠️</span> <strong>Database Export</strong></span>";
                echo "<span>No SQL file found</span>";
                echo "</div>";
                $actions[] = "Export database dari phpMyAdmin/Laragon";
            }
            
            echo '</div>';
            
            // ============================================
            // 6. CHECK PROTECTION FILES
            // ============================================
            
            echo '<div class="section">';
            echo '<div class="section-title">🛡️ Security Protection Files</div>';
            
            $protectionFiles = [
                'uploads/workers/.htaccess' => 'Protect uploads directory',
                'logs/.htaccess' => 'Protect logs directory',
                'backups/.htaccess' => 'Protect backups directory'
            ];
            
            foreach ($protectionFiles as $file => $desc) {
                $exists = file_exists($file);
                $class = $exists ? 'check-pass' : 'check-warning';
                $icon = $exists ? '✅' : '⚠️';
                
                if ($exists) {
                    $passed++;
                } else {
                    $warnings++;
                    $actions[] = "Buat file <span class='code'>$file</span> untuk proteksi";
                }
                
                echo "<div class='check-item $class'>";
                echo "<span><span class='icon'>$icon</span> <strong>$file</strong> - $desc</span>";
                echo "<span>" . ($exists ? 'Found' : 'Create manually') . "</span>";
                echo "</div>";
            }
            
            echo '</div>';
            
            // ============================================
            // 7. SUMMARY & RECOMMENDATIONS
            // ============================================
            
            $total = $passed + $failed + $warnings;
            $percentage = $total > 0 ? round(($passed / $total) * 100) : 0;
            
            $readyToDeploy = ($failed === 0);
            
            echo '<div class="section">';
            echo '<div class="section-title">📊 Summary Report</div>';
            
            echo '<div class="summary-box">';
            echo '<h3 style="margin-bottom: 20px; font-size: 20px;">Deployment Readiness Score</h3>';
            
            echo '<div class="progress-bar">';
            echo "<div class='progress-fill' style='width: {$percentage}%'>{$percentage}%</div>";
            echo '</div>';
            
            echo '<div class="summary-item">';
            echo '<strong>Total Checks:</strong>';
            echo "<span style='font-weight:bold'>$total</span>";
            echo '</div>';
            
            echo '<div class="summary-item">';
            echo '<strong>Passed:</strong>';
            echo "<span style='color: #10b981; font-weight:bold'>$passed ✅</span>";
            echo '</div>';
            
            echo '<div class="summary-item">';
            echo '<strong>Failed:</strong>';
            echo "<span style='color: #ef4444; font-weight:bold'>$failed ❌</span>";
            echo '</div>';
            
            echo '<div class="summary-item">';
            echo '<strong>Warnings:</strong>';
            echo "<span style='color: #f59e0b; font-weight:bold'>$warnings ⚠️</span>";
            echo '</div>';
            
            echo '<hr style="margin: 20px 0; border: none; border-top: 2px solid #cbd5e1;">';
            
            if ($readyToDeploy) {
                echo '<div style="text-align: center; padding: 20px; background: #d1fae5; border-radius: 8px;">';
                echo '<h2 style="color: #065f46; margin-bottom: 10px;">🎉 Ready to Deploy!</h2>';
                echo '<p style="color: #047857;">Project Anda siap untuk di-upload ke hosting</p>';
                echo '</div>';
            } else {
                echo '<div style="text-align: center; padding: 20px; background: #fee2e2; border-radius: 8px;">';
                echo '<h2 style="color: #991b1b; margin-bottom: 10px;">⚠️ Fix Issues First</h2>';
                echo '<p style="color: #dc2626;">Perbaiki error yang ditemukan sebelum deploy</p>';
                echo '</div>';
            }
            
            echo '</div>';
            
            // ============================================
            // 8. ACTION ITEMS
            // ============================================
            
            if (!empty($issues) || !empty($actions) || !empty($filesToDelete)) {
                echo '<div class="action-list">';
                echo '<h3>⚡ Action Items - Yang Harus Dilakukan:</h3>';
                echo '<ol>';
                
                if (!empty($issues)) {
                    foreach ($issues as $issue) {
                        echo "<li><strong>FIX:</strong> $issue</li>";
                    }
                }
                
                if (!empty($filesToDelete)) {
                    echo "<li><strong>DELETE:</strong> Hapus file berikut sebelum upload:";
                    echo "<ul style='margin-left: 20px;'>";
                    foreach ($filesToDelete as $file) {
                        echo "<li><span class='code'>$file</span></li>";
                    }
                    echo "</ul>";
                    echo "</li>";
                }
                
                if (!empty($actions)) {
                    foreach ($actions as $action) {
                        echo "<li>$action</li>";
                    }
                }
                
                echo '</ol>';
                echo '</div>';
            }
            
            echo '</div>'; // end section
            
            // ============================================
            // 9. NEXT STEPS
            // ============================================
            
            echo '<div class="section" style="background: #f0fdf4; padding: 30px; border-radius: 10px;">';
            echo '<div class="section-title" style="border-color: #10b981;">🚀 Next Steps</div>';
            
            if ($readyToDeploy) {
                echo '<ol style="margin-left: 20px; line-height: 2;">';
                echo '<li>✅ Export database dari Laragon/phpMyAdmin (jika belum)</li>';
                echo '<li>✅ Hapus file testing yang tidak diperlukan</li>';
                echo '<li>✅ Zip semua files (kecuali .git dan .env)</li>';
                echo '<li>✅ Login ke cPanel hosting</li>';
                echo '<li>✅ Buat database MySQL baru</li>';
                echo '<li>✅ Import file SQL ke database</li>';
                echo '<li>✅ Upload files via FTP atau File Manager</li>';
                echo '<li>✅ Buat file <span class="code">.env</span> di server</li>';
                echo '<li>✅ Set file permissions yang benar</li>';
                echo '<li>✅ Test aplikasi</li>';
                echo '<li>✅ Ganti password admin</li>';
                echo '</ol>';
            } else {
                echo '<ol style="margin-left: 20px; line-height: 2;">';
                echo '<li>❌ Fix semua error yang ditemukan</li>';
                echo '<li>❌ Lengkapi file yang kurang</li>';
                echo '<li>❌ Jalankan checker ini lagi</li>';
                echo '</ol>';
            }
            
            echo '</div>';
            
            ?>
            
            <div style="text-align: center; margin-top: 40px; padding-top: 40px; border-top: 2px solid #e5e7eb;">
                <?php if ($readyToDeploy): ?>
                    <a href="DEPLOY_GUIDE.md" class="btn btn-success" target="_blank">
                        📘 Open Deployment Guide
                    </a>
                    <a href="?download_checklist=1" class="btn">
                        📋 Download Checklist
                    </a>
                <?php else: ?>
                    <a href="?" class="btn">
                        🔄 Re-check
                    </a>
                <?php endif; ?>
                
                <br><br>
                
                <p style="color: #6b7280; font-size: 14px;">
                    <strong>⚠️ REMINDER:</strong> Delete file <span class="code">prepare_deployment.php</span> setelah checking selesai!
                </p>
            </div>
        </div>
    </div>
</body>
</html>