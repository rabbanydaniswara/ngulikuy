<?php
/**
 * Performance Optimization
 * File untuk optimasi performa aplikasi
 */

if (!defined('APP_INIT')) {
    die('Direct access not permitted');
}

// ============================================
// 1. IMAGE OPTIMIZATION
// ============================================

class ImageOptimizer {
    
    /**
     * Resize dan optimize gambar upload
     */
    public static function optimizeUpload($sourcePath, $maxWidth = 800, $maxHeight = 800, $quality = 85) {
        // Cek apakah file gambar
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }
        
        list($width, $height, $type) = $imageInfo;
        
        // Jika sudah kecil, skip
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return true;
        }
        
        // Hitung dimensi baru dengan maintain aspect ratio
        $ratio = $width / $height;
        if ($width > $height) {
            $newWidth = $maxWidth;
            $newHeight = floor($maxWidth / $ratio);
        } else {
            $newHeight = $maxHeight;
            $newWidth = floor($maxHeight * $ratio);
        }
        
        // Load gambar berdasarkan tipe
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }
        
        // Buat gambar baru
        $destination = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency untuk PNG dan GIF
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($destination, $sourcePath, $quality);
                break;
            case IMAGETYPE_PNG:
                imagepng($destination, $sourcePath, floor($quality / 10));
                break;
            case IMAGETYPE_GIF:
                imagegif($destination, $sourcePath);
                break;
        }
        
        // Cleanup
        imagedestroy($source);
        imagedestroy($destination);
        
        return true;
    }
    
    /**
     * Generate thumbnail
     */
    public static function generateThumbnail($sourcePath, $thumbPath, $size = 150) {
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }
        
        list($width, $height, $type) = $imageInfo;
        
        // Load source
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }
        
        // Square crop
        $minSize = min($width, $height);
        $srcX = ($width - $minSize) / 2;
        $srcY = ($height - $minSize) / 2;
        
        $thumb = imagecreatetruecolor($size, $size);
        
        // Preserve transparency
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
            imagefilledrectangle($thumb, 0, 0, $size, $size, $transparent);
        }
        
        imagecopyresampled($thumb, $source, 0, 0, $srcX, $srcY, $size, $size, $minSize, $minSize);
        
        // Save thumbnail
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumb, $thumbPath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumb, $thumbPath, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumb, $thumbPath);
                break;
        }
        
        imagedestroy($source);
        imagedestroy($thumb);
        
        return true;
    }
}

// ============================================
// 2. DATABASE QUERY OPTIMIZATION
// ============================================

class OptimizedQueries {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get workers dengan pagination dan caching
     */
    public function getWorkersPaginated($page = 1, $perPage = 10, $filters = []) {
        $cacheKey = 'workers_' . md5(json_encode(['page' => $page, 'perPage' => $perPage, 'filters' => $filters]));
        
        // Cek cache
        $cached = QueryCache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT 
                    w.*,
                    COUNT(DISTINCT r.id) as review_count,
                    AVG(r.rating) as avg_rating
                FROM workers w
                LEFT JOIN reviews r ON w.id = r.workerId
                WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND w.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['skill'])) {
            $sql .= " AND JSON_CONTAINS(w.skills, ?)";
            $params[] = '"' . $filters['skill'] . '"';
        }
        
        if (!empty($filters['location'])) {
            $sql .= " AND w.location LIKE ?";
            $params[] = '%' . $filters['location'] . '%';
        }
        
        $sql .= " GROUP BY w.id";
        $sql .= " ORDER BY w.rating DESC, w.name ASC";
        $sql .= " LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $workers = $stmt->fetchAll();
        
        // Decode skills
        foreach ($workers as &$worker) {
            $worker['skills'] = json_decode($worker['skills'], true) ?: [];
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM workers WHERE 1=1";
        if (!empty($filters['status'])) {
            $countSql .= " AND status = ?";
        }
        
        $countStmt = $this->pdo->prepare($countSql);
        $countParams = !empty($filters['status']) ? [$filters['status']] : [];
        $countStmt->execute($countParams);
        $totalCount = $countStmt->fetchColumn();
        
        $result = [
            'workers' => $workers,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalCount,
                'total_pages' => ceil($totalCount / $perPage)
            ]
        ];
        
        // Cache result
        QueryCache::set($cacheKey, $result, 300); // 5 minutes
        
        return $result;
    }
    
    /**
     * Get jobs dengan JOIN yang optimal
     */
    public function getJobsOptimized($filters = []) {
        $sql = "SELECT 
                    j.*,
                    w.name as worker_name,
                    w.phone as worker_phone,
                    w.photo as worker_photo
                FROM jobs j
                LEFT JOIN workers w ON j.workerId = w.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND j.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['customer_email'])) {
            $sql .= " AND j.customerEmail = ?";
            $params[] = $filters['customer_email'];
        }
        
        if (!empty($filters['worker_id'])) {
            $sql .= " AND j.workerId = ?";
            $params[] = $filters['worker_id'];
        }
        
        $sql .= " ORDER BY j.createdAt DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Bulk insert untuk performa lebih baik
     */
    public function bulkInsertReviews($reviews) {
        if (empty($reviews)) {
            return false;
        }
        
        $placeholders = [];
        $params = [];
        
        foreach ($reviews as $review) {
            $placeholders[] = "(?, ?, ?, ?, ?)";
            $params[] = $review['jobId'];
            $params[] = $review['workerId'];
            $params[] = $review['customerId'];
            $params[] = $review['rating'];
            $params[] = $review['comment'];
        }
        
        $sql = "INSERT INTO reviews (jobId, workerId, customerId, rating, comment) VALUES " 
             . implode(', ', $placeholders);
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}

// ============================================
// 3. LAZY LOADING HELPER
// ============================================

class LazyLoader {
    
    /**
     * Generate lazy loading image HTML
     */
    public static function image($src, $alt = '', $class = '', $width = null, $height = null) {
        $attrs = [
            'src' => 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"%3E%3C/svg%3E',
            'data-src' => htmlspecialchars($src),
            'alt' => htmlspecialchars($alt),
            'class' => 'lazy ' . $class,
            'loading' => 'lazy'
        ];
        
        if ($width) $attrs['width'] = $width;
        if ($height) $attrs['height'] = $height;
        
        $attrString = '';
        foreach ($attrs as $key => $value) {
            $attrString .= " {$key}=\"{$value}\"";
        }
        
        return "<img{$attrString}>";
    }
    
    /**
     * JavaScript untuk lazy loading
     */
    public static function script() {
        return <<<'JS'
<script>
document.addEventListener('DOMContentLoaded', function() {
    const lazyImages = document.querySelectorAll('img.lazy');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback untuk browser lama
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            img.classList.remove('lazy');
        });
    }
});
</script>
JS;
    }
}

// ============================================
// 4. GZIP COMPRESSION
// ============================================

class OutputCompression {
    
    public static function enable() {
        // Cek apakah sudah di-compress oleh server
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && 
            strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false &&
            !headers_sent()) {
            
            ob_start('ob_gzhandler');
        }
    }
    
    public static function minifyHTML($html) {
        // Remove comments
        $html = preg_replace('/<!--(?!<!)[^\[>].*?-->/s', '', $html);
        
        // Remove whitespace
        $html = preg_replace('/\s+/', ' ', $html);
        
        // Remove whitespace between tags
        $html = preg_replace('/>\s+</', '><', $html);
        
        return trim($html);
    }
}

// ============================================
// 5. RESOURCE HINTS
// ============================================

class ResourceHints {
    
    /**
     * Generate DNS prefetch dan preconnect tags
     */
    public static function generate() {
        $hints = [
            'dns-prefetch' => [
                'https://cdn.tailwindcss.com',
                'https://cdn.jsdelivr.net',
                'https://unpkg.com',
                'https://fonts.googleapis.com',
                'https://fonts.gstatic.com'
            ],
            'preconnect' => [
                'https://cdn.tailwindcss.com',
                'https://fonts.googleapis.com'
            ]
        ];
        
        $html = '';
        
        foreach ($hints['dns-prefetch'] as $url) {
            $html .= "<link rel='dns-prefetch' href='{$url}'>\n";
        }
        
        foreach ($hints['preconnect'] as $url) {
            $html .= "<link rel='preconnect' href='{$url}' crossorigin>\n";
        }
        
        return $html;
    }
}

// ============================================
// 6. ASSET VERSIONING
// ============================================

class AssetVersion {
    
    /**
     * Add version query string untuk cache busting
     */
    public static function url($path) {
        if (file_exists($path)) {
            $version = filemtime($path);
            return $path . '?v=' . $version;
        }
        return $path;
    }
}

// ============================================
// 7. PERFORMANCE MONITORING
// ============================================

class PerformanceMonitor {
    private static $startTime;
    private static $startMemory;
    
    public static function start() {
        self::$startTime = microtime(true);
        self::$startMemory = memory_get_usage();
    }
    
    public static function end() {
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        
        $executionTime = round(($endTime - self::$startTime) * 1000, 2);
        $memoryUsed = round(($endMemory - self::$startMemory) / 1024 / 1024, 2);
        $peakMemory = round(memory_get_peak_usage() / 1024 / 1024, 2);
        
        return [
            'execution_time' => $executionTime . ' ms',
            'memory_used' => $memoryUsed . ' MB',
            'peak_memory' => $peakMemory . ' MB'
        ];
    }
    
    public static function log() {
        $stats = self::end();
        
        // Log jika execution time > 1 detik
        if ((float)$stats['execution_time'] > 1000) {
            SecurityLogger::log('WARNING', 'Slow page load', $stats);
        }
    }
}