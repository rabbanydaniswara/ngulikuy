<?php
/**
 * Customer Dashboard - Versi Optimized
 * File ini menggantikan customer_dashboard.php
 * Dengan tambahan optimasi performa dan keamanan
 */

// Load dependencies
require_once 'functions.php';
require_once 'performance.php';

// Start performance monitoring
PerformanceMonitor::start();

// Set secure headers
SecureHeaders::set();

// Start secure session
SecureSession::start();

// Enable output compression
OutputCompression::enable();

// Authentication check
redirectIfNotCustomer();

// Get customer info
$customer_email = $_SESSION['user'];
$customer_id = null;

// Get customer ID (dengan caching untuk menghindari query berulang)
$cacheKey = 'customer_id_' . md5($customer_email);
$customer_id = QueryCache::get($cacheKey);

if ($customer_id === null) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$customer_email]);
    $customer = $stmt->fetch();
    $customer_id = $customer ? $customer['id'] : null;
    
    // Cache for 1 hour
    QueryCache::set($cacheKey, $customer_id, 3600);
}

// Initialize optimized queries
$optimizedQueries = new OptimizedQueries($pdo);

// Handle form submissions dengan rate limiting
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Rate limiting untuk form submission
    $rateLimiter = new RateLimiter($pdo);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    if (!$rateLimiter->isAllowed($ip, 'form_submit', 10, 60)) {
        $error_message = 'Terlalu banyak permintaan. Silakan tunggu sebentar.';
    } elseif (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $error_message = 'Sesi tidak valid. Silakan refresh halaman.';
    } else {
        
        // Handle booking
        if (isset($_POST['book_worker'])) {
            $worker_id = InputValidator::sanitizeString($_POST['worker_id'] ?? '');
            
            // Validate dates
            $startDate = $_POST['start_date'] ?? '';
            $endDate = $_POST['end_date'] ?? '';
            
            if (!InputValidator::validateDate($startDate) || !InputValidator::validateDate($endDate)) {
                $error_message = 'Format tanggal tidak valid.';
            } else {
                // Get worker info (with caching)
                $workerCacheKey = 'worker_' . $worker_id;
                $worker = QueryCache::get($workerCacheKey);
                
                if ($worker === null) {
                    $worker = getWorkerById($worker_id);
                    QueryCache::set($workerCacheKey, $worker, 300);
                }
                
                if ($worker) {
                    $jobData = [
                        'workerId' => $worker_id,
                        'workerName' => $worker['name'],
                        'jobType' => InputValidator::sanitizeString($_POST['job_type'] ?? ''),
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'location' => InputValidator::sanitizeString($_POST['job_location'] ?? ''),
                        'address' => InputValidator::sanitizeString($_POST['job_location'] ?? ''),
                        'status' => 'pending',
                        'customer' => $_SESSION['user_name'],
                        'customerPhone' => $_SESSION['user_phone'] ?? 'N/A',
                        'customerEmail' => $customer_email,
                        'description' => InputValidator::sanitizeString($_POST['job_notes'] ?? '')
                    ];
                    
                    // Calculate price
                    $start = new DateTime($startDate);
                    $end = new DateTime($endDate);
                    $days = $end->diff($start)->days + 1;
                    $jobData['price'] = $days > 0 ? $worker['rate'] * $days : 0;
                    
                    if (addJob($jobData)) {
                        // Clear cache
                        QueryCache::clear();
                        
                        SecurityLogger::log('INFO', 'Job created', ['customer' => $customer_email, 'worker' => $worker_id]);
                        $success_message = 'Pesanan berhasil dibuat!';
                    } else {
                        $error_message = 'Gagal membuat pesanan.';
                    }
                } else {
                    $error_message = 'Worker tidak ditemukan.';
                }
            }
        }
        
        // Handle review submission
        if (isset($_POST['submit_review'])) {
            $jobId = InputValidator::sanitizeString($_POST['job_id'] ?? '');
            $workerId = InputValidator::sanitizeString($_POST['worker_id'] ?? '');
            $rating = InputValidator::validateIntRange($_POST['rating'] ?? 0, 1, 5);
            $comment = InputValidator::sanitizeString($_POST['comment'] ?? '');
            
            if (!$rating) {
                $error_message = 'Rating harus dipilih (1-5 bintang).';
            } else {
                try {
                    DatabaseHelper::beginTransaction();
                    
                    // Insert review
                    $sql = "INSERT INTO reviews (jobId, workerId, customerId, rating, comment) 
                            VALUES (?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$jobId, $workerId, $customer_id, $rating, $comment]);
                    
                    // Update worker rating
                    $sqlAvg = "SELECT AVG(rating) as avg_rating FROM reviews WHERE workerId = ?";
                    $stmtAvg = $pdo->prepare($sqlAvg);
                    $stmtAvg->execute([$workerId]);
                    $newRating = $stmtAvg->fetch()['avg_rating'];
                    
                    $sqlUpdate = "UPDATE workers SET rating = ? WHERE id = ?";
                    $stmtUpdate = $pdo->prepare($sqlUpdate);
                    $stmtUpdate->execute([$newRating, $workerId]);
                    
                    DatabaseHelper::commit();
                    
                    // Clear cache
                    QueryCache::clear();
                    
                    SecurityLogger::log('INFO', 'Review submitted', ['job' => $jobId, 'rating' => $rating]);
                    $success_message = 'Ulasan berhasil dikirim!';
                    
                } catch (PDOException $e) {
                    DatabaseHelper::rollback();
                    
                    if ($e->getCode() == 1062) {
                        $error_message = 'Anda sudah memberi ulasan untuk pekerjaan ini.';
                    } else {
                        SecurityLogger::logError('Review submission error: ' . $e->getMessage());
                        $error_message = 'Gagal mengirim ulasan.';
                    }
                }
            }
        }
    }
}

// Get data dengan pagination dan caching
$active_tab = $_GET['tab'] ?? 'home';
$order_status_filter = $_GET['status'] ?? 'all';
$page = (int)($_GET['page'] ?? 1);

// Get workers dengan pagination (untuk tab search)
$workersData = null;
if ($active_tab === 'search') {
    $searchFilters = [
        'skill' => $_GET['skill'] ?? '',
        'location' => $_GET['location'] ?? '',
        'status' => 'Available'
    ];
    
    $cacheKey = 'workers_search_' . md5(json_encode(['page' => $page, 'filters' => $searchFilters]));
    $workersData = QueryCache::get($cacheKey);
    
    if ($workersData === null) {
        $workersData = $optimizedQueries->getWorkersPaginated($page, 12, $searchFilters);
        QueryCache::set($cacheKey, $workersData, 300);
    }
}

// Get customer orders (optimized)
$customerOrders = [];
if ($active_tab === 'orders') {
    $orderFilters = ['customer_email' => $customer_email];
    if ($order_status_filter !== 'all') {
        $orderFilters['status'] = $order_status_filter;
    }
    
    $cacheKey = 'orders_' . md5($customer_email . $order_status_filter);
    $customerOrders = QueryCache::get($cacheKey);
    
    if ($customerOrders === null) {
        $customerOrders = $optimizedQueries->getJobsOptimized($orderFilters);
        QueryCache::set($cacheKey, $customerOrders, 60); // Cache 1 minute untuk orders
    }
}

// Get top workers untuk homepage
$topWorkers = [];
if ($active_tab === 'home') {
    $cacheKey = 'top_workers';
    $topWorkers = QueryCache::get($cacheKey);
    
    if ($topWorkers === null) {
        $topWorkers = getTopRatedWorkers(4);
        QueryCache::set($cacheKey, $topWorkers, 600); // Cache 10 minutes
    }
}

// Hitung notifikasi pending orders (dengan caching)
$cacheKey = 'pending_count_' . md5($customer_email);
$pendingOrderCount = QueryCache::get($cacheKey);

if ($pendingOrderCount === null) {
    $pendingOrderCount = count(array_filter($customerOrders ?: getCustomerOrders($customer_email), 
        function($o) { return $o['status'] === 'pending'; }
    ));
    QueryCache::set($cacheKey, $pendingOrderCount, 60);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NguliKuy - Platform booking tukang harian terpercaya">
    <meta name="theme-color" content="#3b82f6">
    
    <title><?php echo $active_tab === 'home' ? 'Dashboard' : ucfirst($active_tab); ?> - NguliKuy</title>
    
    <?php echo ResourceHints::generate(); ?>
    
    <!-- Preload critical resources -->
    <link rel="preload" as="style" href="https://cdn.tailwindcss.com">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        .gradient-bg { background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%); }
        
        /* Lazy loading placeholder */
        img.lazy {
            opacity: 0;
            transition: opacity 0.3s;
        }
        img:not(.lazy) {
            opacity: 1;
        }
        
        /* Skeleton loader untuk performance feedback */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body>
    
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <!-- [Navigation code tetap sama, tidak perlu diubah] -->
    </nav>
    
    <!-- Alert Messages -->
    <?php if (isset($success_message)): ?>
        <div class="m-4 p-4 bg-green-100 text-green-700 rounded-lg">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="m-4 p-4 bg-red-100 text-red-700 rounded-lg">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <?php if ($active_tab === 'home'): ?>
            <!-- Homepage content -->
            <div class="gradient-bg rounded-xl text-white p-8 mb-8">
                <h1 class="text-3xl font-bold mb-4">Solusi Cepat untuk Kebutuhan Tukang Harian</h1>
                <p class="text-lg mb-6">Temukan tukang berpengalaman dengan mudah dan transparan</p>
                <a href="?tab=search" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-medium inline-block hover:bg-gray-100">
                    Cari Tukang Sekarang
                </a>
            </div>
            
            <!-- Top Workers dengan Lazy Loading -->
            <div class="mb-12">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Tukang Terbaik</h2>
                    <a href="?tab=search" class="text-blue-600 hover:underline">Lihat Semua</a>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($topWorkers as $worker): ?>
                        <div class="bg-white rounded-xl shadow p-4 transition hover:shadow-lg">
                            <div class="flex justify-center mb-4">
                                <?php 
                                echo LazyLoader::image(
                                    $worker['photo'], 
                                    $worker['name'],
                                    'w-20 h-20 rounded-full object-cover',
                                    80,
                                    80
                                );
                                ?>
                            </div>
                            <div class="text-center">
                                <h3 class="font-bold"><?php echo htmlspecialchars($worker['name']); ?></h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    <?php echo htmlspecialchars($worker['skills'][0] ?? ''); ?>
                                </p>
                                <div class="flex justify-center items-center mb-2">
                                    <div class="flex text-yellow-400">
                                        <?php echo formatRating($worker['rating']); ?>
                                    </div>
                                    <span class="text-xs text-gray-500 ml-1">
                                        (<?php echo $worker['review_count']; ?>)
                                    </span>
                                </div>
                                <p class="text-sm font-bold text-blue-600">
                                    <?php echo formatCurrency($worker['rate']); ?>/hari
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
        <?php elseif ($active_tab === 'search' && $workersData): ?>
            
            <!-- Search Results dengan Pagination -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold">
                    Hasil Pencarian 
                    <span class="text-gray-500 text-lg">
                        (<?php echo $workersData['pagination']['total']; ?> tukang)
                    </span>
                </h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php foreach ($workersData['workers'] as $worker): ?>
                    <div class="bg-white rounded-xl shadow p-4">
                        <?php 
                        echo LazyLoader::image(
                            $worker['photo'],
                            $worker['name'],
                            'w-full h-48 object-cover rounded-lg mb-4'
                        );
                        ?>
                        <h3 class="font-bold text-lg"><?php echo htmlspecialchars($worker['name']); ?></h3>
                        <p class="text-gray-600 text-sm mb-2">
                            <?php echo htmlspecialchars(implode(', ', array_slice($worker['skills'], 0, 2))); ?>
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-blue-600 font-bold">
                                <?php echo formatCurrency($worker['rate']); ?>/hari
                            </span>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                Pesan
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($workersData['pagination']['total_pages'] > 1): ?>
                <div class="flex justify-center space-x-2">
                    <?php for ($i = 1; $i <= $workersData['pagination']['total_pages']; $i++): ?>
                        <a href="?tab=search&page=<?php echo $i; ?>" 
                           class="px-4 py-2 rounded-lg <?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
            
        <?php elseif ($active_tab === 'orders'): ?>
            
            <!-- Orders list -->
            <!-- [Code orders tetap sama] -->
            
        <?php endif; ?>
        
    </div>
    
    <!-- Lazy Loading Script -->
    <?php echo LazyLoader::script(); ?>
    
    <script>
        // Initialize feather icons
        feather.replace();
        
        // Your other JavaScript here...
    </script>
    
    <?php
    // Performance monitoring (hanya di development)
    if (getenv('APP_ENV') === 'development') {
        PerformanceMonitor::log();
        $stats = PerformanceMonitor::end();
        echo "<!-- Performance: {$stats['execution_time']}, Memory: {$stats['memory_used']} -->";
    }
    ?>
</body>
</html>