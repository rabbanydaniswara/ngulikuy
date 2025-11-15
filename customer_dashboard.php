<?php
/**
 * Customer Dashboard - Production Ready
 * Fixed: Prevent duplicate reviews
 * Polished: Enhanced UI/UX
 */

require_once 'functions.php';

// Set secure headers
SecureHeaders::set();

// Authentication check
redirectIfNotCustomer();

// Get customer info
$customer_email = $_SESSION['user'];
$customer_id = null;

// Get customer ID
global $pdo;
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$customer_email]);
$customer = $stmt->fetch();
$customer_id = $customer ? $customer['id'] : null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Rate limiting
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
                $worker = getWorkerById($worker_id);
                
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
        
        // Handle review submission - FIXED: Prevent duplicate reviews
        if (isset($_POST['submit_review'])) {
            $jobId = InputValidator::sanitizeString($_POST['job_id'] ?? '');
            $workerId = InputValidator::sanitizeString($_POST['worker_id'] ?? '');
            $rating = InputValidator::validateIntRange($_POST['rating'] ?? 0, 1, 5);
            $comment = InputValidator::sanitizeString($_POST['comment'] ?? '');
            
            if (!$rating) {
                $error_message = 'Rating harus dipilih (1-5 bintang).';
            } else {
                try {
                    // PERBAIKAN: Cek apakah sudah ada review untuk job ini
                    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE jobId = ? AND customerId = ?");
                    $stmtCheck->execute([$jobId, $customer_id]);
                    $reviewExists = $stmtCheck->fetchColumn() > 0;
                    
                    if ($reviewExists) {
                        $error_message = 'Anda sudah memberikan ulasan untuk pekerjaan ini.';
                    } else {
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
                        
                        SecurityLogger::log('INFO', 'Review submitted', ['job' => $jobId, 'rating' => $rating]);
                        $success_message = 'Terima kasih! Ulasan Anda telah berhasil dikirim.';
                    }
                    
                } catch (PDOException $e) {
                    DatabaseHelper::rollback();
                    SecurityLogger::logError('Review submission error: ' . $e->getMessage());
                    $error_message = 'Gagal mengirim ulasan. Silakan coba lagi.';
                }
            }
        }
    }
}

// Get data
$active_tab = $_GET['tab'] ?? 'home';
$order_status_filter = $_GET['status'] ?? 'all';

// Get workers untuk search
$workers = [];
$searchFilters = [];

if ($active_tab === 'search') {
    $searchFilters = [
        'skill' => $_GET['skill'] ?? '',
        'location' => $_GET['location'] ?? '',
        'status' => 'Available'
    ];
    
    if (!empty($searchFilters['skill']) || !empty($searchFilters['location'])) {
        $workers = searchWorkers($searchFilters);
    } else {
        $workers = getAvailableWorkers();
    }
}

// Get customer orders dengan info review
$customerOrders = [];
if ($active_tab === 'orders') {
    if ($order_status_filter === 'all') {
        $customerOrders = getCustomerOrders($customer_email);
    } else {
        $allOrders = getCustomerOrders($customer_email);
        $customerOrders = array_filter($allOrders, function($order) use ($order_status_filter) {
            return $order['status'] === $order_status_filter;
        });
    }
    
    // PERBAIKAN: Cek status review untuk setiap order
    foreach ($customerOrders as &$order) {
        $stmtReview = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE jobId = ? AND customerId = ?");
        $stmtReview->execute([$order['jobId'], $customer_id]);
        $order['has_review'] = $stmtReview->fetchColumn() > 0;
    }
}

// Get top workers untuk homepage
$topWorkers = [];
if ($active_tab === 'home') {
    $topWorkers = getTopRatedWorkers(4);
}

// Count pending orders
$pendingOrderCount = count(array_filter(
    getCustomerOrders($customer_email), 
    function($o) { return $o['status'] === 'pending'; }
));

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NguliKuy - Platform booking tukang harian terpercaya">
    <title><?php echo $active_tab === 'home' ? 'Dashboard' : ucfirst($active_tab); ?> - NguliKuy</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { 
            font-family: 'Poppins', sans-serif; 
            background: linear-gradient(to bottom right, #f8fafc 0%, #e2e8f0 100%);
        }
        .gradient-bg { 
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%); 
        }
        .nav-active { 
            border-bottom: 3px solid #3b82f6; 
            color: #1f2937; 
            font-weight: 600;
        }
        .status-completed { 
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534; 
            border: 1px solid #86efac;
        }
        .status-in-progress { 
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e; 
            border: 1px solid #fcd34d;
        }
        .status-pending { 
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af; 
            border: 1px solid #93c5fd;
        }
        .status-cancelled { 
            background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
            color: #dc2626; 
            border: 1px solid #f87171;
        }
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        .worker-card {
            position: relative;
            overflow: hidden;
        }
        .worker-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        .worker-card:hover::before {
            left: 100%;
        }
        .badge-new {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .7; }
        }
        .rating-star {
            transition: all 0.2s;
        }
        .rating-star:hover {
            transform: scale(1.2);
        }
        .modal-enter {
            animation: modalEnter 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        @keyframes modalEnter {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>
<body class="min-h-screen">
    
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50 backdrop-blur-sm bg-white/90">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="?tab=home" class="flex-shrink-0 flex items-center group">
                        <div class="p-2 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition-colors">
                            <i data-feather="tool" class="text-blue-600 w-6 h-6"></i>
                        </div>
                        <span class="ml-3 font-bold text-xl bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">NguliKuy</span>
                    </a>
                    <div class="hidden sm:ml-8 sm:flex sm:space-x-4">
                        <a href="?tab=home" class="<?php echo $active_tab === 'home' ? 'nav-active' : 'text-gray-600 hover:text-gray-900'; ?> inline-flex items-center px-3 pt-1 pb-1 text-sm font-medium transition-colors">
                            <i data-feather="home" class="w-4 h-4 mr-2"></i>
                            Home
                        </a>
                        <a href="?tab=search" class="<?php echo $active_tab === 'search' ? 'nav-active' : 'text-gray-600 hover:text-gray-900'; ?> inline-flex items-center px-3 pt-1 pb-1 text-sm font-medium transition-colors">
                            <i data-feather="search" class="w-4 h-4 mr-2"></i>
                            Cari Tukang
                        </a>
                        <a href="?tab=orders" class="<?php echo $active_tab === 'orders' ? 'nav-active' : 'text-gray-600 hover:text-gray-900'; ?> inline-flex items-center px-3 pt-1 pb-1 text-sm font-medium transition-colors">
                            <i data-feather="clipboard" class="w-4 h-4 mr-2"></i>
                            Pesanan Saya
                        </a>
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center space-x-4">
                    <div class="relative">
                        <button class="p-2 rounded-lg hover:bg-gray-100 transition-colors relative">
                            <i data-feather="bell" class="text-gray-600 w-5 h-5"></i>
                            <?php if ($pendingOrderCount > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold badge-new">
                                <?php echo $pendingOrderCount; ?>
                            </span>
                            <?php endif; ?>
                        </button>
                    </div>
                    <div class="flex items-center space-x-3 pl-3 border-l">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="User" class="w-9 h-9 rounded-full ring-2 ring-blue-100">
                        <span class="text-sm font-semibold text-gray-700"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <a href="index.php?logout=1" class="p-2 rounded-lg hover:bg-red-50 text-gray-600 hover:text-red-600 transition-colors" title="Logout">
                            <i data-feather="log-out" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Alert Messages -->
    <?php if (isset($success_message)): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="p-4 bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-500 text-green-800 rounded-lg shadow-md flex items-center">
                <i data-feather="check-circle" class="w-5 h-5 mr-3 text-green-600"></i>
                <span class="font-medium"><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="p-4 bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 text-red-800 rounded-lg shadow-md flex items-center">
                <i data-feather="alert-circle" class="w-5 h-5 mr-3 text-red-600"></i>
                <span class="font-medium"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <?php if ($active_tab === 'home'): ?>
            
            <!-- Hero Section -->
            <div class="gradient-bg rounded-2xl text-white p-10 mb-8 shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full -ml-24 -mb-24"></div>
                <div class="relative z-10">
                    <h1 class="text-4xl font-bold mb-4">Solusi Cepat untuk Kebutuhan Tukang Harian</h1>
                    <p class="text-lg mb-6 text-blue-100">Temukan tukang berpengalaman dengan mudah dan transparan</p>
                    <a href="?tab=search" class="inline-flex items-center bg-white text-blue-600 px-8 py-3 rounded-xl font-semibold hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <i data-feather="search" class="w-5 h-5 mr-2"></i>
                        Cari Tukang Sekarang
                    </a>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-lg p-6 card-hover border-t-4 border-blue-500">
                    <div class="flex items-center">
                        <div class="p-4 rounded-xl bg-gradient-to-br from-blue-100 to-blue-200 text-blue-600 mr-4">
                            <i data-feather="users" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Total Tukang</p>
                            <h3 class="text-3xl font-bold text-gray-800"><?php echo count(getWorkers()); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6 card-hover border-t-4 border-green-500">
                    <div class="flex items-center">
                        <div class="p-4 rounded-xl bg-gradient-to-br from-green-100 to-green-200 text-green-600 mr-4">
                            <i data-feather="check-circle" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Pesanan Selesai</p>
                            <h3 class="text-3xl font-bold text-gray-800">
                                <?php echo count(array_filter(getCustomerOrders($customer_email), function($o) { 
                                    return $o['status'] === 'completed'; 
                                })); ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6 card-hover border-t-4 border-yellow-500">
                    <div class="flex items-center">
                        <div class="p-4 rounded-xl bg-gradient-to-br from-yellow-100 to-yellow-200 text-yellow-600 mr-4">
                            <i data-feather="clock" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Pesanan Pending</p>
                            <h3 class="text-3xl font-bold text-gray-800"><?php echo $pendingOrderCount; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Workers -->
            <div class="mb-12">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-gray-800 flex items-center">
                        <i data-feather="star" class="w-8 h-8 mr-3 text-yellow-500"></i>
                        Tukang Terbaik
                    </h2>
                    <a href="?tab=search" class="text-blue-600 hover:text-blue-700 font-semibold flex items-center group">
                        Lihat Semua
                        <i data-feather="arrow-right" class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($topWorkers as $worker): ?>
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden worker-card card-hover">
                            <div class="relative h-32 bg-gradient-to-br from-blue-400 to-indigo-500">
                                <div class="absolute -bottom-12 left-1/2 transform -translate-x-1/2">
                                    <img src="<?php echo htmlspecialchars($worker['photo']); ?>" 
                                         alt="<?php echo htmlspecialchars($worker['name']); ?>"
                                         class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg">
                                </div>
                            </div>
                            <div class="pt-16 pb-6 px-6 text-center">
                                <h3 class="font-bold text-lg text-gray-800 mb-1"><?php echo htmlspecialchars($worker['name']); ?></h3>
                                <p class="text-sm text-gray-600 mb-3">
                                    <?php echo htmlspecialchars($worker['skills'][0] ?? ''); ?>
                                </p>
                                <div class="flex justify-center items-center mb-3">
                                    <div class="flex text-yellow-400 text-base">
                                        <?php echo formatRating($worker['rating']); ?>
                                    </div>
                                    <span class="text-xs text-gray-500 ml-2 font-medium">
                                        (<?php echo $worker['review_count']; ?> ulasan)
                                    </span>
                                </div>
                                <p class="text-base font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-4">
                                    <?php echo formatCurrency($worker['rate']); ?>/hari
                                </p>
                                <button onclick="openBookingModal('<?php echo $worker['id']; ?>')" 
                                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-3 rounded-xl hover:from-blue-700 hover:to-indigo-700 text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all">
                                    Pesan Sekarang
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
        <?php elseif ($active_tab === 'search'): ?>
            
            <!-- Search Header -->
            <div class="mb-6">
                <h2 class="text-3xl font-bold mb-6 text-gray-800 flex items-center">
                    <i data-feather="search" class="w-8 h-8 mr-3 text-blue-600"></i>
                    Cari Tukang
                </h2>
                
                <!-- Filter Form -->
                <form method="GET" class="bg-white rounded-2xl shadow-lg p-6">
                    <input type="hidden" name="tab" value="search">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i data-feather="tool" class="w-4 h-4 inline mr-1"></i>
                                Keahlian
                            </label>
                            <select name="skill" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors">
                                <option value="">Semua Keahlian</option>
                                <option value="Construction" <?php echo ($searchFilters['skill'] ?? '') === 'Construction' ? 'selected' : ''; ?>>Construction</option>
                                <option value="Moving" <?php echo ($searchFilters['skill'] ?? '') === 'Moving' ? 'selected' : ''; ?>>Moving</option>
                                <option value="Cleaning" <?php echo ($searchFilters['skill'] ?? '') === 'Cleaning' ? 'selected' : ''; ?>>Cleaning</option>
                                <option value="Gardening" <?php echo ($searchFilters['skill'] ?? '') === 'Gardening' ? 'selected' : ''; ?>>Gardening</option>
                                <option value="Plumbing" <?php echo ($searchFilters['skill'] ?? '') === 'Plumbing' ? 'selected' : ''; ?>>Plumbing</option>
                                <option value="Electrical" <?php echo ($searchFilters['skill'] ?? '') === 'Electrical' ? 'selected' : ''; ?>>Electrical</option>
                                <option value="Painting" <?php echo ($searchFilters['skill'] ?? '') === 'Painting' ? 'selected' : ''; ?>>Painting</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i data-feather="map-pin" class="w-4 h-4 inline mr-1"></i>
                                Lokasi
                            </label>
                            <input type="text" name="location" value="<?php echo htmlspecialchars($searchFilters['location'] ?? ''); ?>" 
                                   placeholder="Masukkan lokasi..." 
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-3 rounded-xl hover:from-blue-700 hover:to-indigo-700 font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all">
                                <i data-feather="search" class="w-5 h-5 inline mr-2"></i>
                                Cari
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Workers Grid -->
            <?php if (empty($workers)): ?>
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                    <div class="inline-block p-6 bg-gray-100 rounded-full mb-4">
                        <i data-feather="search" class="w-16 h-16 text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Tidak ada tukang ditemukan</h3>
                    <p class="text-gray-500 mb-6">Coba ubah filter pencarian Anda atau lihat semua tukang</p>
                    <a href="?tab=search" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold">
                        Reset Filter
                        <i data-feather="refresh-cw" class="w-4 h-4 ml-2"></i>
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($workers as $worker): ?>
                        <div class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all overflow-hidden worker-card">
                            <div class="relative">
                                <img src="<?php echo htmlspecialchars($worker['photo']); ?>" 
                                     alt="<?php echo htmlspecialchars($worker['name']); ?>"
                                     class="w-full h-56 object-cover">
                                <div class="absolute top-4 right-4">
                                    <span class="bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                        Tersedia
                                    </span>
                                </div>
                            </div>
                            <div class="p-6">
                                <h3 class="font-bold text-xl mb-2 text-gray-800"><?php echo htmlspecialchars($worker['name']); ?></h3>
                                <div class="flex flex-wrap gap-2 mb-3">
                                    <?php foreach (array_slice($worker['skills'], 0, 2) as $skill): ?>
                                        <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full">
                                            <?php echo htmlspecialchars($skill); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="flex items-center mb-3">
                                    <div class="flex text-yellow-400 text-base mr-2">
                                        <?php echo formatRating($worker['rating']); ?>
                                    </div>
                                    <span class="text-sm text-gray-600 font-medium">
                                        <?php echo number_format($worker['rating'], 1); ?>
                                    </span>
                                    <span class="text-xs text-gray-400 ml-1">
                                        (<?php echo $worker['review_count']; ?> ulasan)
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-4 flex items-center">
                                    <i data-feather="map-pin" class="w-4 h-4 mr-2 text-gray-400"></i>
                                    <?php echo htmlspecialchars($worker['location']); ?>
                                </p>
                                <div class="flex items-center justify-between pt-4 border-t">
                                    <span class="text-lg font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                                        <?php echo formatCurrency($worker['rate']); ?>/hari
                                    </span>
                                    <button onclick="openBookingModal('<?php echo $worker['id']; ?>')" 
                                            class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-2.5 rounded-xl hover:from-blue-700 hover:to-indigo-700 font-semibold shadow-md hover:shadow-lg transition-all">
                                        Pesan
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        <?php elseif ($active_tab === 'orders'): ?>
            
            <!-- Orders Header -->
            <div class="mb-6">
                <h2 class="text-3xl font-bold mb-6 text-gray-800 flex items-center">
                    <i data-feather="clipboard" class="w-8 h-8 mr-3 text-blue-600"></i>
                    Pesanan Saya
                </h2>
                
                <!-- Status Filter -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px overflow-x-auto">
                            <a href="?tab=orders&status=all" 
                               class="flex-shrink-0 <?php echo $order_status_filter === 'all' ? 'nav-active bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50'; ?> px-8 py-4 text-sm font-semibold transition-colors whitespace-nowrap">
                                <i data-feather="list" class="w-4 h-4 inline mr-2"></i>
                                Semua
                            </a>
                            <a href="?tab=orders&status=pending" 
                               class="flex-shrink-0 <?php echo $order_status_filter === 'pending' ? 'nav-active bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50'; ?> px-8 py-4 text-sm font-semibold transition-colors whitespace-nowrap">
                                <i data-feather="clock" class="w-4 h-4 inline mr-2"></i>
                                Pending
                            </a>
                            <a href="?tab=orders&status=in-progress" 
                               class="flex-shrink-0 <?php echo $order_status_filter === 'in-progress' ? 'nav-active bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50'; ?> px-8 py-4 text-sm font-semibold transition-colors whitespace-nowrap">
                                <i data-feather="loader" class="w-4 h-4 inline mr-2"></i>
                                Sedang Dikerjakan
                            </a>
                            <a href="?tab=orders&status=completed" 
                               class="flex-shrink-0 <?php echo $order_status_filter === 'completed' ? 'nav-active bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50'; ?> px-8 py-4 text-sm font-semibold transition-colors whitespace-nowrap">
                                <i data-feather="check-circle" class="w-4 h-4 inline mr-2"></i>
                                Selesai
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
            
            <!-- Orders List -->
            <?php if (empty($customerOrders)): ?>
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                    <div class="inline-block p-6 bg-gray-100 rounded-full mb-4">
                        <i data-feather="clipboard" class="w-16 h-16 text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Belum ada pesanan</h3>
                    <p class="text-gray-500 mb-6">Mulai pesan tukang untuk pekerjaan Anda</p>
                    <a href="?tab=search" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 font-semibold shadow-md hover:shadow-lg transition-all">
                        <i data-feather="plus" class="w-5 h-5 mr-2"></i>
                        Buat Pesanan
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($customerOrders as $order): 
                        $statusInfo = getStatusTextAndClass($order['status']);
                    ?>
                        <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b flex flex-col md:flex-row md:items-center md:justify-between">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800 flex items-center">
                                        <i data-feather="briefcase" class="w-5 h-5 mr-2 text-blue-600"></i>
                                        <?php echo htmlspecialchars($order['jobType']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Order #<?php echo htmlspecialchars($order['jobId']); ?>
                                        <span class="mx-2">•</span>
                                        <i data-feather="calendar" class="w-3 h-3 inline"></i>
                                        <?php echo date('d M Y', strtotime($order['createdAt'])); ?>
                                    </p>
                                </div>
                                <span class="px-4 py-2 text-sm font-bold rounded-xl <?php echo $statusInfo['class']; ?> mt-3 md:mt-0 inline-block shadow-sm">
                                    <?php echo $statusInfo['text']; ?>
                                </span>
                            </div>
                            
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div class="space-y-3">
                                        <div class="flex items-start">
                                            <i data-feather="user" class="w-5 h-5 text-gray-400 mr-3 mt-0.5"></i>
                                            <div>
                                                <p class="text-xs text-gray-500 font-medium">Tukang</p>
                                                <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($order['workerName']); ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <i data-feather="calendar" class="w-5 h-5 text-gray-400 mr-3 mt-0.5"></i>
                                            <div>
                                                <p class="text-xs text-gray-500 font-medium">Periode</p>
                                                <p class="text-sm font-semibold text-gray-800">
                                                    <?php echo date('d M Y', strtotime($order['startDate'])); ?> - 
                                                    <?php echo date('d M Y', strtotime($order['endDate'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-start">
                                            <i data-feather="map-pin" class="w-5 h-5 text-gray-400 mr-3 mt-0.5"></i>
                                            <div>
                                                <p class="text-xs text-gray-500 font-medium">Lokasi</p>
                                                <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($order['location']); ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <i data-feather="dollar-sign" class="w-5 h-5 text-gray-400 mr-3 mt-0.5"></i>
                                            <div>
                                                <p class="text-xs text-gray-500 font-medium">Total Biaya</p>
                                                <p class="text-lg font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                                                    <?php echo formatCurrency($order['price']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex flex-wrap gap-3 pt-4 border-t">
                                    <a href="detail_pesanan.php?id=<?php echo $order['jobId']; ?>" 
                                       class="inline-flex items-center px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 text-sm font-semibold transition-all">
                                        <i data-feather="eye" class="w-4 h-4 mr-2"></i>
                                        Lihat Detail
                                    </a>
                                    <?php if ($order['status'] === 'completed' && !$order['has_review']): ?>
                                        <button onclick="openReviewModal('<?php echo $order['jobId']; ?>', '<?php echo $order['workerId']; ?>')" 
                                                class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-xl hover:from-yellow-600 hover:to-orange-600 text-sm font-semibold shadow-md hover:shadow-lg transition-all">
                                            <i data-feather="star" class="w-4 h-4 mr-2"></i>
                                            Beri Ulasan
                                        </button>
                                    <?php elseif ($order['status'] === 'completed' && $order['has_review']): ?>
                                        <span class="inline-flex items-center px-5 py-2.5 bg-green-100 text-green-700 rounded-xl text-sm font-semibold">
                                            <i data-feather="check" class="w-4 h-4 mr-2"></i>
                                            Sudah Diulas
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>

    <!-- Booking Modal -->
    <div id="bookingModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 overflow-y-auto backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full modal-enter">
                <div class="gradient-bg p-6 rounded-t-2xl relative">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-2xl font-bold text-white">Buat Pesanan</h3>
                                <p class="text-blue-100 text-sm mt-1">Isi detail pekerjaan Anda</p>
                            </div>
                            <button onclick="closeBookingModal()" class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white/10 rounded-lg">
                                <i data-feather="x" class="w-6 h-6"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <form method="POST" class="p-6">
                    <?php echo csrfInput(); ?>
                    <input type="hidden" name="book_worker" value="1">
                    <input type="hidden" id="modal_worker_id" name="worker_id">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i data-feather="briefcase" class="w-4 h-4 mr-2 text-blue-600"></i>
                            Jenis Pekerjaan
                        </label>
                        <select name="job_type" required class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors">
                            <option value="">Pilih Jenis Pekerjaan</option>
                            <option value="Construction">Construction</option>
                            <option value="Moving">Moving</option>
                            <option value="Cleaning">Cleaning</option>
                            <option value="Gardening">Gardening</option>
                            <option value="Plumbing">Plumbing</option>
                            <option value="Electrical">Electrical</option>
                            <option value="Painting">Painting</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i data-feather="calendar" class="w-4 h-4 mr-2 text-blue-600"></i>
                                Tanggal Mulai
                            </label>
                            <input type="date" name="start_date" required min="<?php echo date('Y-m-d'); ?>"
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i data-feather="calendar" class="w-4 h-4 mr-2 text-blue-600"></i>
                                Tanggal Selesai
                            </label>
                            <input type="date" name="end_date" required min="<?php echo date('Y-m-d'); ?>"
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i data-feather="map-pin" class="w-4 h-4 mr-2 text-blue-600"></i>
                            Lokasi Pekerjaan
                        </label>
                        <textarea name="job_location" required rows="3"
                                  class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors resize-none"
                                  placeholder="Masukkan alamat lengkap lokasi pekerjaan..."></textarea>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i data-feather="file-text" class="w-4 h-4 mr-2 text-blue-600"></i>
                            Catatan (Opsional)
                        </label>
                        <textarea name="job_notes" rows="2"
                                  class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors resize-none"
                                  placeholder="Tambahkan catatan untuk tukang..."></textarea>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="button" onclick="closeBookingModal()" 
                                class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-semibold transition-all">
                            Batal
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 font-semibold shadow-lg hover:shadow-xl transition-all">
                            Pesan Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div id="reviewModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 overflow-y-auto backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full modal-enter">
                <div class="gradient-bg p-6 rounded-t-2xl relative">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-2xl font-bold text-white">Beri Ulasan</h3>
                                <p class="text-blue-100 text-sm mt-1">Bagikan pengalaman Anda</p>
                            </div>
                            <button onclick="closeReviewModal()" class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white/10 rounded-lg">
                                <i data-feather="x" class="w-6 h-6"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <form method="POST" class="p-6">
                    <?php echo csrfInput(); ?>
                    <input type="hidden" name="submit_review" value="1">
                    <input type="hidden" id="review_job_id" name="job_id">
                    <input type="hidden" id="review_worker_id" name="worker_id">
                    
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3 text-center">
                            Berikan Rating Anda
                        </label>
                        <div class="flex justify-center space-x-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="rating" value="<?php echo $i; ?>" required class="hidden peer">
                                    <i data-feather="star" class="rating-star w-10 h-10 text-gray-300 peer-checked:text-yellow-400 peer-checked:fill-current hover:text-yellow-300"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i data-feather="message-square" class="w-4 h-4 mr-2 text-blue-600"></i>
                            Komentar
                        </label>
                        <textarea name="comment" required rows="4"
                                  class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors resize-none"
                                  placeholder="Bagaimana pengalaman Anda dengan tukang ini?"></textarea>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="button" onclick="closeReviewModal()" 
                                class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-semibold transition-all">
                            Batal
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-3 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-xl hover:from-yellow-600 hover:to-orange-600 font-semibold shadow-lg hover:shadow-xl transition-all">
                            Kirim Ulasan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        feather.replace();
        
        function openBookingModal(workerId) {
            document.getElementById('modal_worker_id').value = workerId;
            document.getElementById('bookingModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeBookingModal() {
            document.getElementById('bookingModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function openReviewModal(jobId, workerId) {
            document.getElementById('review_job_id').value = jobId;
            document.getElementById('review_worker_id').value = workerId;
            document.getElementById('reviewModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeReviewModal() {
            document.getElementById('reviewModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        // Close modals on overlay click
        document.getElementById('bookingModal').addEventListener('click', function(e) {
            if (e.target === this) closeBookingModal();
        });
        
        document.getElementById('reviewModal').addEventListener('click', function(e) {
            if (e.target === this) closeReviewModal();
        });
        
        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeBookingModal();
                closeReviewModal();
            }
        });
    </script>
</body>
</html>