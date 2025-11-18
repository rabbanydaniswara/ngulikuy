<?php
/**
 * Customer Dashboard - Production Ready
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

// Get customer orders
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
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        .gradient-bg { background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%); }
        .nav-active { border-bottom: 2px solid #3b82f6; color: #1f2937; }
        .status-completed { background-color: #dcfce7; color: #166534; }
        .status-in-progress { background-color: #fef3c7; color: #92400e; }
        .status-pending { background-color: #dbeafe; color: #1e40af; }
        .status-cancelled { background-color: #fecaca; color: #dc2626; }
    </style>
</head>
<body class="min-h-screen">
    
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="?tab=home" class="flex-shrink-0 flex items-center">
                        <i data-feather="tool" class="text-blue-600"></i>
                        <span class="ml-2 font-bold text-xl">NguliKuy</span>
                    </a>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="?tab=home" class="<?php echo $active_tab === 'home' ? 'nav-active' : 'text-gray-500'; ?> inline-flex items-center px-1 pt-1 text-sm font-medium">
                            Home
                        </a>
                        <a href="?tab=search" class="<?php echo $active_tab === 'search' ? 'nav-active' : 'text-gray-500'; ?> inline-flex items-center px-1 pt-1 text-sm font-medium">
                            Cari Tukang
                        </a>
                        <a href="?tab=orders" class="<?php echo $active_tab === 'orders' ? 'nav-active' : 'text-gray-500'; ?> inline-flex items-center px-1 pt-1 text-sm font-medium">
                            Pesanan Saya
                        </a>
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <div class="relative mr-4">
                        <i data-feather="bell" class="text-gray-500 hover:text-blue-600 cursor-pointer"></i>
                        <?php if ($pendingOrderCount > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                            <?php echo $pendingOrderCount; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-2">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="User" class="w-8 h-8 rounded-full">
                            <span class="text-sm font-medium"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            <a href="index.php?logout=1" class="text-gray-500 hover:text-blue-600">
                                <i data-feather="log-out" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Alert Messages -->
    <?php if (isset($success_message)): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="p-4 bg-green-100 text-green-700 rounded-lg">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="p-4 bg-red-100 text-red-700 rounded-lg">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <?php if ($active_tab === 'home'): ?>
            
            <!-- Hero Section -->
            <div class="gradient-bg rounded-xl text-white p-8 mb-8">
                <h1 class="text-3xl font-bold mb-4">Solusi Cepat untuk Kebutuhan Tukang Harian</h1>
                <p class="text-lg mb-6">Temukan tukang berpengalaman dengan mudah dan transparan</p>
                <a href="?tab=search" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-medium inline-block hover:bg-gray-100">
                    Cari Tukang Sekarang
                </a>
            </div>
            
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                            <i data-feather="users"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Tukang</p>
                            <h3 class="text-2xl font-bold"><?php echo count(getWorkers()); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                            <i data-feather="check-circle"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Pesanan Selesai</p>
                            <h3 class="text-2xl font-bold">
                                <?php echo count(array_filter(getCustomerOrders($customer_email), function($o) { 
                                    return $o['status'] === 'completed'; 
                                })); ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                            <i data-feather="clock"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Pesanan Pending</p>
                            <h3 class="text-2xl font-bold"><?php echo $pendingOrderCount; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Workers -->
            <div class="mb-12">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Tukang Terbaik</h2>
                    <a href="?tab=search" class="text-blue-600 hover:underline">Lihat Semua</a>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($topWorkers as $worker): ?>
                        <div class="bg-white rounded-xl shadow p-4 transition hover:shadow-lg">
                            <div class="flex justify-center mb-4">
                                <img src="<?php echo htmlspecialchars($worker['photo']); ?>" 
                                     alt="<?php echo htmlspecialchars($worker['name']); ?>"
                                     class="w-20 h-20 rounded-full object-cover">
                            </div>
                            <div class="text-center">
                                <h3 class="font-bold"><?php echo htmlspecialchars($worker['name']); ?></h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    <?php echo htmlspecialchars($worker['skills'][0] ?? ''); ?>
                                </p>
                                <div class="flex justify-center items-center mb-2">
                                    <div class="flex text-yellow-400 text-sm">
                                        <?php echo formatRating($worker['rating']); ?>
                                    </div>
                                    <span class="text-xs text-gray-500 ml-1">
                                        (<?php echo $worker['review_count']; ?>)
                                    </span>
                                </div>
                                <p class="text-sm font-bold text-blue-600 mb-3">
                                    <?php echo formatCurrency($worker['rate']); ?>/hari
                                </p>
                                <button onclick="openBookingModal('<?php echo $worker['id']; ?>')" 
                                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
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
                <h2 class="text-2xl font-bold mb-4">Cari Tukang</h2>
                
                <!-- Filter Form -->
                <form method="GET" class="bg-white rounded-lg shadow p-6">
                    <input type="hidden" name="tab" value="search">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Keahlian</label>
                            <select name="skill" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                            <input type="text" name="location" value="<?php echo htmlspecialchars($searchFilters['location'] ?? ''); ?>" 
                                   placeholder="Masukkan lokasi..." 
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                                <i data-feather="search" class="w-4 h-4 inline mr-2"></i>
                                Cari
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Workers Grid -->
            <?php if (empty($workers)): ?>
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <i data-feather="search" class="w-16 h-16 mx-auto text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada tukang ditemukan</h3>
                    <p class="text-gray-500">Coba ubah filter pencarian Anda</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($workers as $worker): ?>
                        <div class="bg-white rounded-xl shadow hover:shadow-lg transition">
                            <img src="<?php echo htmlspecialchars($worker['photo']); ?>" 
                                 alt="<?php echo htmlspecialchars($worker['name']); ?>"
                                 class="w-full h-48 object-cover rounded-t-lg">
                            <div class="p-4">
                                <h3 class="font-bold text-lg mb-1"><?php echo htmlspecialchars($worker['name']); ?></h3>
                                <p class="text-gray-600 text-sm mb-2">
                                    <?php echo htmlspecialchars(implode(', ', array_slice($worker['skills'], 0, 2))); ?>
                                </p>
                                <div class="flex items-center mb-2">
                                    <div class="flex text-yellow-400 text-sm mr-2">
                                        <?php echo formatRating($worker['rating']); ?>
                                    </div>
                                    <span class="text-xs text-gray-500">
                                        (<?php echo $worker['review_count']; ?> ulasan)
                                    </span>
                                </div>
                                <p class="text-gray-600 text-sm mb-3">
                                    <i data-feather="map-pin" class="w-4 h-4 inline"></i>
                                    <?php echo htmlspecialchars($worker['location']); ?>
                                </p>
                                <div class="flex items-center justify-between">
                                    <span class="text-blue-600 font-bold">
                                        <?php echo formatCurrency($worker['rate']); ?>/hari
                                    </span>
                                    <button onclick="openBookingModal('<?php echo $worker['id']; ?>')" 
                                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
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
                <h2 class="text-2xl font-bold mb-4">Pesanan Saya</h2>
                
                <!-- Status Filter -->
                <div class="bg-white rounded-lg shadow">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px overflow-x-auto">
                            <a href="?tab=orders&status=all" 
                               class="flex-shrink-0 <?php echo $order_status_filter === 'all' ? 'nav-active' : 'border-transparent text-gray-500 hover:border-gray-300'; ?> px-6 py-4 text-sm font-medium">
                                Semua
                            </a>
                            <a href="?tab=orders&status=pending" 
                               class="flex-shrink-0 <?php echo $order_status_filter === 'pending' ? 'nav-active' : 'border-transparent text-gray-500 hover:border-gray-300'; ?> px-6 py-4 text-sm font-medium">
                                Pending
                            </a>
                            <a href="?tab=orders&status=in-progress" 
                               class="flex-shrink-0 <?php echo $order_status_filter === 'in-progress' ? 'nav-active' : 'border-transparent text-gray-500 hover:border-gray-300'; ?> px-6 py-4 text-sm font-medium">
                                Sedang Dikerjakan
                            </a>
                            <a href="?tab=orders&status=completed" 
                               class="flex-shrink-0 <?php echo $order_status_filter === 'completed' ? 'nav-active' : 'border-transparent text-gray-500 hover:border-gray-300'; ?> px-6 py-4 text-sm font-medium">
                                Selesai
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
            
            <!-- Orders List -->
            <?php if (empty($customerOrders)): ?>
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <i data-feather="clipboard" class="w-16 h-16 mx-auto text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada pesanan</h3>
                    <p class="text-gray-500 mb-4">Mulai pesan tukang untuk pekerjaan Anda</p>
                    <a href="?tab=search" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                        Buat Pesanan
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($customerOrders as $order): 
                        $statusInfo = getStatusTextAndClass($order['status']);
                    ?>
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-bold"><?php echo htmlspecialchars($order['jobType']); ?></h3>
                                    <p class="text-sm text-gray-500">Order #<?php echo htmlspecialchars($order['jobId']); ?></p>
                                </div>
                                <span class="px-3 py-1 text-sm font-medium rounded-full <?php echo $statusInfo['class']; ?> mt-2 md:mt-0">
                                    <?php echo $statusInfo['text']; ?>
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-sm text-gray-600"><strong>Tukang:</strong> <?php echo htmlspecialchars($order['workerName']); ?></p>
                                    <p class="text-sm text-gray-600"><strong>Tanggal:</strong> 
                                        <?php echo date('d M Y', strtotime($order['startDate'])); ?> - 
                                        <?php echo date('d M Y', strtotime($order['endDate'])); ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600"><strong>Lokasi:</strong> <?php echo htmlspecialchars($order['location']); ?></p>
                                    <p class="text-sm text-gray-600"><strong>Total:</strong> 
                                        <span class="font-bold text-blue-600"><?php echo formatCurrency($order['price']); ?></span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex space-x-2">
                                <a href="detail_pesanan.php?id=<?php echo $order['jobId']; ?>" 
                                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                                    Lihat Detail
                                </a>
                                <?php if ($order['status'] === 'completed'): ?>
                                    <button onclick="openReviewModal('<?php echo $order['jobId']; ?>', '<?php echo $order['workerId']; ?>')" 
                                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                        <i data-feather="star" class="w-4 h-4 inline mr-1"></i>
                                        Beri Ulasan
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>

    <!-- Booking Modal -->
    <div id="bookingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="gradient-bg p-6 rounded-t-xl">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold text-white">Buat Pesanan</h3>
                        <button onclick="closeBookingModal()" class="text-white hover:text-gray-200">
                            <i data-feather="x" class="w-6 h-6"></i>
                        </button>
                    </div>
                </div>
                
                <form method="POST" class="p-6">
                    <?php echo csrfInput(); ?>
                    <input type="hidden" name="book_worker" value="1">
                    <input type="hidden" id="modal_worker_id" name="worker_id">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Pekerjaan</label>
                        <select name="job_type" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="start_date" required min="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="end_date" required min="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Pekerjaan</label>
                        <textarea name="job_location" required rows="3"
                                  class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Masukkan alamat lengkap lokasi pekerjaan..."></textarea>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                        <textarea name="job_notes" rows="2"
                                  class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Tambahkan catatan untuk tukang..."></textarea>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="button" onclick="closeBookingModal()" 
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Pesan Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div id="reviewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="gradient-bg p-6 rounded-t-xl">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold text-white">Beri Ulasan</h3>
                        <button onclick="closeReviewModal()" class="text-white hover:text-gray-200">
                            <i data-feather="x" class="w-6 h-6"></i>
                        </button>
                    </div>
                </div>
                
                <form method="POST" class="p-6">
                    <?php echo csrfInput(); ?>
                    <input type="hidden" name="submit_review" value="1">
                    <input type="hidden" id="review_job_id" name="job_id">
                    <input type="hidden" id="review_worker_id" name="worker_id">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                        <div class="flex space-x-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="rating" value="<?php echo $i; ?>" required class="hidden peer">
                                    <i data-feather="star" class="w-8 h-8 text-gray-300 peer-checked:text-yellow-400 peer-checked:fill-current hover:text-yellow-200"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Komentar</label>
                        <textarea name="comment" required rows="4"
                                  class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Bagaimana pengalaman Anda dengan tukang ini?"></textarea>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="button" onclick="closeReviewModal()" 
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
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
        }
        
        function closeBookingModal() {
            document.getElementById('bookingModal').classList.add('hidden');
        }
        
        function openReviewModal(jobId, workerId) {
            document.getElementById('review_job_id').value = jobId;
            document.getElementById('review_worker_id').value = workerId;
            document.getElementById('reviewModal').classList.remove('hidden');
        }
        
        function closeReviewModal() {
            document.getElementById('reviewModal').classList.add('hidden');
        }
        
        // Close modals on overlay click
        document.getElementById('bookingModal').addEventListener('click', function(e) {
            if (e.target === this) closeBookingModal();
        });
        
        document.getElementById('reviewModal').addEventListener('click', function(e) {
            if (e.target === this) closeReviewModal();
        });
    </script>
</body>
</html>