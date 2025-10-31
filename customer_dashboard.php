<?php
require_once 'functions.php';

redirectIfNotCustomer();

$customer_email = $_SESSION['user'];

// PERBAIKAN (Efisiensi): Ambil ID customer sekali saja saat halaman dimuat
global $pdo;
$stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmtUser->execute([$customer_email]);
$customer = $stmtUser->fetch();
$customer_id = $customer ? $customer['id'] : null;
// Variabel $customer_id sekarang tersedia untuk seluruh halaman (untuk submit review & cek N+1)


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- VALIDASI CSRF ---
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $error_message = 'Sesi tidak valid atau telah kedaluwarsa. Silakan coba lagi.';
    } else {
    // --- AKHIR VALIDASI CSRF ---

        if (isset($_POST['book_worker'])) {
            $worker_id = $_POST['worker_id'] ?? '';
            $worker = getWorkerById($worker_id); // Gunakan fungsi yg sudah ada
            
            $jobData = [
                'workerId' => $worker_id,
                'workerName' => $worker ? $worker['name'] : 'Unknown Worker',
                'jobType' => $_POST['job_type'] ?? '',
                'startDate' => $_POST['start_date'] ?? '',
                'endDate' => $_POST['end_date'] ?? '',
                'location' => $_POST['job_location'] ?? '',
                'address' => $_POST['job_location'] ?? '',
                'status' => 'pending',
                'customer' => $_SESSION['user_name'],
                'customerPhone' => $_SESSION['user_phone'] ?? 'N/A',
                'customerEmail' => $customer_email,
                // Hitung harga berdasarkan rate worker dan jumlah hari
                'price' => 0, // Akan dihitung di bawah
                'description' => $_POST['job_notes'] ?? ''
            ];

            // Hitung harga jika worker dan tanggal valid
            if ($worker && !empty($jobData['startDate']) && !empty($jobData['endDate'])) {
                $start = new DateTime($jobData['startDate']);
                $end = new DateTime($jobData['endDate']);
                // +1 karena include hari awal dan akhir
                $days = $end->diff($start)->days + 1; 
                if ($days > 0) {
                     $jobData['price'] = $worker['rate'] * $days;
                }
            }
            
            if (addJob($jobData)) {
                $success_message = 'Pesanan berhasil dibuat! Kami akan menghubungi Anda untuk konfirmasi.';
            } else {
                $error_message = 'Gagal membuat pesanan. Silakan coba lagi.';
            }
        }

        // --- LOGIKA REVIEW ---
        if (isset($_POST['submit_review'])) {
            $jobId = $_POST['job_id'];
            $workerId = $_POST['worker_id'];
            $rating = (int)$_POST['rating'];
            $comment = $_POST['comment'] ?? '';
            
            // PERBAIKAN (Efisiensi): Gunakan $customer_id yang sudah diambil di atas
            $customerId = $customer_id; 

            if ($rating > 0 && $rating <= 5) {
                try {
                    // Gunakan $pdo yang sudah global dari atas (jika $customer_id diambil di atas)
                    global $pdo; 
                    $sqlReview = "INSERT INTO reviews (jobId, workerId, customerId, rating, comment) VALUES (?, ?, ?, ?, ?)";
                    $stmtReview = $pdo->prepare($sqlReview);
                    $stmtReview->execute([$jobId, $workerId, $customerId, $rating, $comment]);

                    $sqlAvg = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE workerId = ?");
                    $sqlAvg->execute([$workerId]);
                    $newRating = $sqlAvg->fetch()['avg_rating'];

                    $sqlUpdateWorker = $pdo->prepare("UPDATE workers SET rating = ? WHERE id = ?");
                    $sqlUpdateWorker->execute([$newRating, $workerId]);
                    
                    $success_message = 'Ulasan Anda berhasil dikirim! Terima kasih.';

                } catch (PDOException $e) {
                    if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) { // 1062 = Duplicate entry
                         // Cek apakah review sudah ada
                        global $pdo; // $pdo mungkin belum di-deklarasi jika $customer_id null
                        $stmtCheckReview = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE jobId = ? AND customerId = ?");
                        $stmtCheckReview->execute([$jobId, $customerId]);
                        if ($stmtCheckReview->fetchColumn() > 0) {
                             $error_message = 'Anda sudah pernah memberi ulasan untuk pekerjaan ini.';
                        } else {
                             // Error lain, mungkin UNIQUE constraint lain
                             $error_message = 'Gagal menyimpan ulasan. Error tidak diketahui.'; 
                        }
                    } else {
                        $error_message = 'Gagal mengirim ulasan: ' . $e->getMessage();
                    }
                }
            } else {
                $error_message = 'Rating harus dipilih (1-5 bintang).';
            }
        }
    } // <-- Penutup blok 'else' dari validasi CSRF
}

// Get data for display
$workers = getWorkers();
$availableWorkers = getAvailableWorkers();
$topWorkers = getTopRatedWorkers(4);
$customerOrders = getCustomerOrders($customer_email);

// Set active tab
$active_tab = $_GET['tab'] ?? 'home';
$order_status_filter = $_GET['status'] ?? 'all';

// KUNCI RESPONSIVE: Hitung notifikasi di sini agar bisa dipakai di menu mobile & desktop
$pendingOrderCount = count(array_filter($customerOrders, function($order) { 
    return $order['status'] === 'pending'; 
}));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NguliKuy - Customer Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
        }
        .active-tab {
            border-left: 4px solid #3b82f6;
            background-color: #eff6ff;
        }
        .worker-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .nav-active {
            border-bottom: 2px solid #3b82f6;
            color: #1f2937;
        }
        /* Style untuk link menu mobile yang aktif */
        .mobile-nav-active {
            background-color: #eff6ff;
            color: #3b82f6;
        }
        .status-available {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-assigned {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-completed {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-in-progress {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-pending {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-cancelled {
            background-color: #fecaca;
            color: #dc2626;
        }
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            max-height: 90vh;
            overflow-y: auto;
        }
        /* Ini adalah CSS yang memperbaiki bintangnya */
        .star-icon.filled {
            color: #f59e0b; /* Tailwind 'amber-500' */
            fill: #f59e0b;
        }
    </style>
</head>
<body class="min-h-screen">
    
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <i data-feather="tool" class="text-blue-600"></i>
                        <span class="ml-2 font-bold text-xl">NguliKuy</span>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="?tab=home" class="<?php echo $active_tab === 'home' ? 'nav-active' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Beranda
                        </a>
                        <a href="?tab=search" class="<?php echo $active_tab === 'search' ? 'nav-active' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Cari Kuli
                        </a>
                        <a href="?tab=orders" class="<?php echo $active_tab === 'orders' ? 'nav-active' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Pesanan Saya
                        </a>
                    </div>
                </div>

                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <div class="relative">
                        <i data-feather="bell" class="text-gray-500 hover:text-blue-600 cursor-pointer"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                            <?php echo $pendingOrderCount; ?>
                        </span>
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

                <div class="flex items-center sm:hidden">
                    <button id="mobile-menu-button" type="button" class="p-2 inline-flex items-center justify-center rounded-md text-gray-500 hover:text-blue-600 hover:bg-gray-100" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Buka menu</span>
                        <i id="mobile-menu-icon" data-feather="menu" class="block h-6 w-6"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="hidden sm:hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="?tab=home" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $active_tab === 'home' ? 'mobile-nav-active' : 'text-gray-700 hover:bg-gray-50'; ?>">Beranda</a>
                <a href="?tab=search" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $active_tab === 'search' ? 'mobile-nav-active' : 'text-gray-700 hover:bg-gray-50'; ?>">Cari Kuli</a>
                <a href="?tab=orders" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $active_tab === 'orders' ? 'mobile-nav-active' : 'text-gray-700 hover:bg-gray-50'; ?>">Pesanan Saya</a>
            </div>
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="flex items-center px-4">
                    <div class="flex-shrink-0">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="User" class="w-10 h-10 rounded-full">
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                        <div class="text-sm font-medium text-gray-500"><?php echo htmlspecialchars($_SESSION['user']); ?></div>
                    </div>
                    <div class="ml-auto flex-shrink-0">
                         <div class="relative p-1 rounded-full text-gray-500 hover:text-blue-600">
                            <i data-feather="bell" class="w-6 h-6"></i>
                            <span class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                                <?php echo $pendingOrderCount; ?>
                            </span>
                         </div>
                    </div>
                </div>
                <div class="mt-3 px-2 space-y-1">
                    <a href="index.php?logout=1" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <?php if ($active_tab === 'home'): ?>
            <div id="homeContent" class="content-section">
                <div class="gradient-bg rounded-xl text-white p-6 md:p-8 mb-8"> <h1 class="text-2xl md:text-3xl font-bold mb-4">Solusi Cepat untuk Kebutuhan Tukang Harian</h1>
                    <p class="text-lg mb-6">Temukan tukang berpengalaman dengan mudah dan transparan</p>
                    <a href="?tab=search" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-medium inline-block hover:bg-gray-100 transition">Cari Tukang Sekarang</a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                    <div class="bg-white p-6 rounded-xl shadow worker-card">
                        <div class="text-blue-600 mb-4">
                            <i data-feather="zap" class="w-8 h-8"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2">Cepat</h3>
                        <p class="text-gray-600">Pesanan diproses dalam hitungan menit dengan tukang siap kerja</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow worker-card">
                        <div class="text-blue-600 mb-4">
                            <i data-feather="shield" class="w-8 h-8"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2">Terpercaya</h3>
                        <p class="text-gray-600">Tukang terverifikasi dengan rating dan ulasan transparan</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow worker-card">
                        <div class="text-blue-600 mb-4">
                            <i data-feather="dollar-sign" class="w-8 h-8"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2">Harga Jelas</h3>
                        <p class="text-gray-600">Tidak ada biaya tersembunyi, semua harga sudah termasuk pajak</p>
                    </div>
                </div>

                <div class="mb-12">
                    <h2 class="text-2xl font-bold mb-6">Cara Kerja NguliKuy</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 md:gap-4"> <div class="text-center">
                            <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i data-feather="search" class="w-6 h-6"></i>
                            </div>
                            <p>Cari tukang berdasarkan keahlian dan lokasi</p>
                        </div>
                        <div class="text-center">
                            <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i data-feather="calendar" class="w-6 h-6"></i>
                            </div>
                            <p>Pilih jadwal dan buat pesanan</p>
                        </div>
                        <div class="text-center">
                            <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i data-feather="credit-card" class="w-6 h-6"></i>
                            </div>
                            <p>Bayar secara online atau tunai di tempat</p>
                        </div>
                        <div class="text-center">
                            <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i data-feather="check-circle" class="w-6 h-6"></i>
                            </div>
                            <p>Tukang datang sesuai jadwal dan pekerjaan selesai</p>
                        </div>
                    </div>
                </div>

                <div class="mb-12">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Tukang Terbaik</h2>
                        <a href="?tab=search" class="text-blue-600 hover:underline">Lihat Semua</a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" id="topWorkersGrid">
                        <?php foreach ($topWorkers as $worker): ?>
                            <div class="worker-card bg-white rounded-xl shadow p-4 transition duration-300 cursor-pointer">
                                <div class="flex justify-center mb-4">
                                    <img src="<?php echo htmlspecialchars($worker['photo']); ?>" alt="<?php echo htmlspecialchars($worker['name']); ?>" class="w-20 h-20 rounded-full object-cover">
                                </div>
                                <div class="text-center">
                                    <h3 class="font-bold"><?php echo htmlspecialchars($worker['name']); ?></h3>
                                    <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($worker['skills'][0] ?? ''); ?></p>
                                    <div class="flex justify-center items-center mb-2">
                                        <div class="flex text-yellow-400">
                                            <?php echo formatRating($worker['rating']); ?>
                                        </div>
                                        <span class="text-xs text-gray-500 ml-1">(<?php echo $worker['review_count']; ?> ulasan)</span>
                                    </div>
                                    <p class="text-sm font-bold text-blue-600"><?php echo formatCurrency($worker['rate']); ?>/hari</p>
                                    <button type="button" 
                                            class="mt-3 w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 book-worker-btn"
                                            data-worker-id="<?php echo htmlspecialchars($worker['id']); ?>"
                                            data-worker-name="<?php echo htmlspecialchars($worker['name']); ?>"
                                            data-worker-rate="<?php echo $worker['rate']; ?>"
                                            data-worker-skills="<?php echo htmlspecialchars(implode(', ', $worker['skills'])); ?>">
                                        Pesan Sekarang
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        <?php elseif ($active_tab === 'search'): ?>
            <div id="searchContent" class="content-section">
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="md:w-1/4">
                        <div class="bg-white rounded-xl shadow p-6 sticky top-4">
                            <h3 class="font-bold text-lg mb-4">Filter</h3>
                            
                            <form method="GET">
                                <input type="hidden" name="tab" value="search">
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Keahlian</label>
                                    <select name="skill" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Semua Keahlian</option>
                                        <option value="Construction" <?php echo ($_GET['skill'] ?? '') === 'Construction' ? 'selected' : ''; ?>>Construction</option>
                                        <option value="Moving" <?php echo ($_GET['skill'] ?? '') === 'Moving' ? 'selected' : ''; ?>>Moving</option>
                                        <option value="Cleaning" <?php echo ($_GET['skill'] ?? '') === 'Cleaning' ? 'selected' : ''; ?>>Cleaning</option>
                                        <option value="Gardening" <?php echo ($_GET['skill'] ?? '') === 'Gardening' ? 'selected' : ''; ?>>Gardening</option>
                                        <option value="Plumbing" <?php echo ($_GET['skill'] ?? '') === 'Plumbing' ? 'selected' : ''; ?>>Plumbing</option>
                                        <option value="Electrical" <?php echo ($_GET['skill'] ?? '') === 'Electrical' ? 'selected' : ''; ?>>Electrical</option>
                                        <option value="Painting" <?php echo ($_GET['skill'] ?? '') === 'Painting' ? 'selected' : ''; ?>>Painting</option>
                                    </select>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                                    <input type="text" name="location" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Kota/Kecamatan" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
                                </div>
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga per Hari</label>
                                    <div class="flex items-center space-x-2">
                                        <input type="number" name="min_price" class="w-1/2 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Min" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>">
                                        <span>-</span>
                                        <input type="number" name="max_price" class="w-1/2 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Max" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Rating Minimal</label>
                                    <select name="min_rating" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="0">Semua Rating</option>
                                        <option value="4.5" <?php echo ($_GET['min_rating'] ?? '') === '4.5' ? 'selected' : ''; ?>>4.5 bintang ke atas</option>
                                        <option value="4.0" <?php echo ($_GET['min_rating'] ?? '') === '4.0' ? 'selected' : ''; ?>>4.0 bintang ke atas</option>
                                        <option value="3.5" <?php echo ($_GET['min_rating'] ?? '') === '3.5' ? 'selected' : ''; ?>>3.5 bintang ke atas</option>
                                        <option value="3.0" <?php echo ($_GET['min_rating'] ?? '') === '3.0' ? 'selected' : ''; ?>>3.0 bintang ke atas</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="w-full gradient-bg text-white py-2 px-4 rounded-lg hover:opacity-90">Terapkan Filter</button>
                                <a href="?tab=search" class="w-full mt-2 bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300 block text-center">Reset Filter</a>
                            </form>
                        </div>
                    </div>
                    
                    <div class="md:w-3/4">
                        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                            <h2 class="text-2xl font-bold">Hasil Pencarian</h2>
                            <div class="relative w-full md:w-auto">
                                <input type="text" id="searchInput" placeholder="Cari tukang..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-full">
                                <i data-feather="search" class="absolute left-3 top-2.5 text-gray-400"></i>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6" id="workersList">
                            <?php
                            // Apply filters using searchWorkers function
                            $searchCriteria = [
                                'skill' => $_GET['skill'] ?? '',
                                'location' => $_GET['location'] ?? '',
                                'min_price' => $_GET['min_price'] ?? '',
                                'max_price' => $_GET['max_price'] ?? '',
                                'min_rating' => $_GET['min_rating'] ?? ''
                            ];
                            $filteredWorkers = searchWorkers($searchCriteria); 
                            
                            if (empty($filteredWorkers)): ?>
                                <div class="text-center py-12">
                                    <i data-feather="users" class="w-16 h-16 text-gray-400 mx-auto mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada tukang tersedia</h3>
                                    <p class="text-gray-500">Tidak ada tukang yang cocok dengan filter Anda atau semua sedang sibuk.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($filteredWorkers as $worker): ?>
                                    <div class="worker-card bg-white rounded-xl shadow p-6 transition duration-300">
                                        <div class="flex flex-col md:flex-row gap-6">
                                            <div class="md:w-1/4">
                                                <img src="<?php echo htmlspecialchars($worker['photo']); ?>" alt="<?php echo htmlspecialchars($worker['name']); ?>" class="w-full rounded-lg object-cover h-48 md:h-full">
                                            </div>
                                            <div class="md:w-3/4">
                                                <div class="flex flex-col md:flex-row justify-between items-start mb-2">
                                                    <div>
                                                        <h3 class="font-bold text-xl"><?php echo htmlspecialchars($worker['name']); ?></h3>
                                                        <p class="text-gray-600"><?php echo htmlspecialchars(implode(', ', $worker['skills'])); ?> | <?php echo htmlspecialchars($worker['location']); ?></p>
                                                    </div>
                                                    <div class="flex items-center mt-2 md:mt-0">
                                                        <div class="flex text-yellow-400 mr-1">
                                                            <?php echo formatRating($worker['rating']); ?>
                                                        </div>
                                                        <span class="text-sm">(<?php echo $worker['review_count']; ?> ulasan)</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-4">
                                                    <h4 class="font-semibold mb-1">Keahlian:</h4>
                                                    <div class="flex flex-wrap gap-2">
                                                        <?php foreach ($worker['skills'] as $skill): ?>
                                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded"><?php echo htmlspecialchars($skill); ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-4">
                                                    <h4 class="font-semibold mb-1">Pengalaman:</h4>
                                                    <p class="text-sm"><?php echo htmlspecialchars($worker['description'] ?? 'Tidak ada deskripsi'); ?></p>
                                                </div>
                                                
                                                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                                                    <div>
                                                        <h4 class="font-semibold mb-1">Harga:</h4>
                                                        <p class="text-blue-600 font-bold"><?php echo formatCurrency($worker['rate']); ?>/hari</p>
                                                    </div>
                                                    <div class="flex space-x-2 w-full sm:w-auto">
                                                        <button class="flex-1 sm:flex-none flex items-center justify-center text-white gradient-bg px-4 py-2 rounded-lg hover:opacity-90">
                                                            <i data-feather="message-square" class="w-4 h-4 mr-1"></i> Chat
                                                        </button>
                                                        <button type="button" 
                                                                class="book-worker-btn flex-1 sm:flex-none flex items-center justify-center text-white bg-green-600 px-4 py-2 rounded-lg hover:bg-green-700"
                                                                data-worker-id="<?php echo htmlspecialchars($worker['id']); ?>"
                                                                data-worker-name="<?php echo htmlspecialchars($worker['name']); ?>"
                                                                data-worker-rate="<?php echo $worker['rate']; ?>"
                                                                data-worker-skills="<?php echo htmlspecialchars(implode(', ', $worker['skills'])); ?>">
                                                            <i data-feather="calendar" class="w-4 h-4 mr-1"></i> Pesan
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($active_tab === 'orders'): ?>
            <div id="ordersContent" class="content-section">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-6">Pesanan Saya</h2>
                    
                    <div class="bg-white rounded-xl shadow overflow-hidden">
                        <div class="border-b border-gray-200">
                            <nav class="flex -mb-px overflow-x-auto">
                                <a href="?tab=orders&status=all" class="flex-shrink-0 <?php echo $order_status_filter === 'all' ? 'px-6 py-4 text-sm font-medium text-gray-900 border-b-2 border-blue-500' : 'px-6 py-4 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300'; ?>">
                                    Semua
                                </a>
                                <a href="?tab=orders&status=pending" class="flex-shrink-0 <?php echo $order_status_filter === 'pending' ? 'px-6 py-4 text-sm font-medium text-gray-900 border-b-2 border-blue-500' : 'px-6 py-4 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300'; ?>">
                                    Menunggu
                                </a>
                                <a href="?tab=orders&status=in-progress" class="flex-shrink-0 <?php echo $order_status_filter === 'in-progress' ? 'px-6 py-4 text-sm font-medium text-gray-900 border-b-2 border-blue-500' : 'px-6 py-4 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300'; ?>">
                                    Berjalan
                                </a>
                                <a href="?tab=orders&status=completed" class="flex-shrink-0 <?php echo $order_status_filter === 'completed' ? 'px-6 py-4 text-sm font-medium text-gray-900 border-b-2 border-blue-500' : 'px-6 py-4 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300'; ?>">
                                    Selesai
                                </a>
                            </nav>
                        </div>
                        
                        <div class="p-0 md:p-6"> <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pesanan</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tukang</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Pekerjaan</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php
                                        // PERBAIKAN (N+1 Query): Ambil semua ID job yang sudah direview oleh customer ini dalam satu query
                                        global $pdo;
                                        $stmtReviewed = $pdo->prepare("SELECT DISTINCT jobId FROM reviews WHERE customerId = ?");
                                        $stmtReviewed->execute([$customer_id]);
                                        $reviewedJobIds = $stmtReviewed->fetchAll(PDO::FETCH_COLUMN);
                                        // $reviewedJobIds sekarang adalah array seperti ['JOB001', 'JOB003']
                                        
                                        $filteredOrders = $customerOrders;
                                        if ($order_status_filter !== 'all') {
                                            $filteredOrders = array_filter($customerOrders, function($order) use ($order_status_filter) {
                                                return $order['status'] === $order_status_filter;
                                            });
                                        }
                                        
                                        if (empty($filteredOrders)): ?>
                                            <tr>
                                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                                    <i data-feather="clipboard" class="w-8 h-8 mx-auto mb-2"></i>
                                                    <p>Belum ada pesanan</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($filteredOrders as $order): ?>
                                                <?php
                                                $statusClass = [
                                                    'completed' => 'status-completed',
                                                    'in-progress' => 'status-in-progress',
                                                    'pending' => 'status-pending',
                                                    'cancelled' => 'status-cancelled'
                                                ][$order['status']] ?? 'status-pending';
                                                
                                                $statusText = [
                                                    'completed' => 'Selesai',
                                                    'in-progress' => 'Berjalan',
                                                    'pending' => 'Menunggu',
                                                    'cancelled' => 'Dibatalkan'
                                                ][$order['status']] ?? 'Menunggu';
                                                ?>
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($order['jobId']); ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <span><?php echo htmlspecialchars($order['workerName']); ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($order['jobType']); ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($order['startDate']); ?> - <?php echo htmlspecialchars($order['endDate']); ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo formatCurrency($order['price']); ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 py-1 text-xs rounded-full <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        
                                                        <a href="detail_pesanan.php?id=<?php echo htmlspecialchars($order['jobId']); ?>" class="text-blue-600 hover:text-blue-800 mr-2" title="Lihat Detail">
                                                            <i data-feather="info"></i>
                                                        </a>
                                                        <?php if ($order['status'] === 'completed'): ?>
                                                            <?php
                                                            // PERBAIKAN (N+1 Query): Hapus query dari dalam loop
                                                            // Ganti pengecekan DB dengan pengecekan array PHP
                                                            $hasReviewed = in_array($order['jobId'], $reviewedJobIds);
                                                            ?>
                                                            <?php if (!$hasReviewed): // Tampilkan tombol review only if not reviewed ?>
                                                            <button type="button" 
                                                                    class="text-purple-600 hover:text-purple-800 review-btn" 
                                                                    data-job-id="<?php echo htmlspecialchars($order['jobId']); ?>"
                                                                    data-worker-id="<?php echo htmlspecialchars($order['workerId']); ?>"
                                                                    data-worker-name="<?php echo htmlspecialchars($order['workerName']); ?>"
                                                                    title="Beri Ulasan">
                                                                <i data-feather="star"></i>
                                                            </button>
                                                            <?php else: // Show checkmark if reviewed ?>
                                                                <span class="text-green-500" title="Sudah Direview">
                                                                    <i data-feather="check-circle"></i>
                                                                </span>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                        
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="bookingModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity modal-overlay" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="gradient-bg p-6 text-center text-white">
                    <h2 class="text-xl font-bold">Pesan Tukang</h2>
                    <p id="selectedWorkerName" class="text-blue-100 mt-1"></p>
                    <p id="selectedWorkerSkills" class="text-blue-100 text-sm mt-1"></p>
                    <p id="selectedWorkerRate" class="text-blue-100 text-sm mt-1 font-semibold"></p>
                </div>
                <form method="POST" class="p-6 modal-content">
                    <input type="hidden" name="book_worker" value="1">
                    <input type="hidden" id="workerId" name="worker_id">
                    <input type="hidden" id="workerName" name="worker_name">
                    <input type="hidden" id="workerRate" name="worker_rate">
                    <?php echo csrfInput(); ?> <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Pekerjaan</label>
                        <select name="job_type" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Pilih Jenis Pekerjaan</option>
                            <option value="Construction">Construction (Bangunan)</option>
                            <option value="Moving">Moving (Pindahan)</option>
                            <option value="Cleaning">Cleaning (Kebersihan)</option>
                            <option value="Gardening">Gardening (Taman)</option>
                            <option value="Plumbing">Plumbing (Perpipaan)</option>
                            <option value="Electrical">Electrical (Kelistrikan)</option>
                            <option value="Painting">Painting (Pengecatan)</option>
                            <option value="Other">Lainnya</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                            <input type="date" name="end_date" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Pengerjaan</label>
                        <textarea name="job_location" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Alamat lengkap lokasi pengerjaan..." required></textarea>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan</label>
                        <textarea name="job_notes" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Detail pekerjaan, spesifikasi, atau instruksi khusus..."></textarea>
                    </div>
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold text-gray-700 mb-2">Ringkasan Biaya</h4>
                        <div class="flex justify-between text-sm mb-1">
                            <span>Harga per hari:</span>
                            <span id="dailyRateSummary">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-sm mb-1">
                            <span>Jumlah hari:</span>
                            <span id="daysCount">0 hari</span>
                        </div>
                        <div class="border-t pt-2 mt-2">
                            <div class="flex justify-between font-semibold">
                                <span>Total Perkiraan:</span>
                                <span id="totalPrice">Rp 0</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">* Harga final dapat berubah sesuai kompleksitas pekerjaan</p>
                    </div>
                    <div class="flex flex-col sm:flex-row-reverse sm:space-x-4 sm:space-x-reverse">
                        <button type="submit" class="w-full sm:w-auto gradient-bg px-4 py-2 rounded-lg text-white hover:opacity-90 transition duration-200">
                            Pesan Sekarang
                        </button>
                        <button type="button" id="cancelBooking" class="w-full sm:w-auto mt-2 sm:mt-0 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition duration-200">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="reviewModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity modal-overlay" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="gradient-bg p-6 text-center text-white">
                    <h2 class="text-xl font-bold">Beri Ulasan</h2>
                    <p id="reviewWorkerName" class="text-blue-100 mt-1"></p>
                </div>
                <form method="POST" class="p-6 modal-content">
                    <input type="hidden" name="submit_review" value="1">
                    <input type="hidden" id="reviewJobId" name="job_id">
                    <input type="hidden" id="reviewWorkerId" name="worker_id">
                    <?php echo csrfInput(); ?> <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rating Anda</label>
                        <div class="flex justify-center space-x-2" id="starRating">
                            <input type="hidden" name="rating" id="ratingValue" value="0">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i data-feather="star" class="star-icon w-8 h-8 text-gray-400 cursor-pointer" data-value="<?php echo $i; ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Komentar Anda (Opsional)</label>
                        <textarea name="comment" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" rows="4" placeholder="Bagaimana pengalaman Anda dengan tukang ini?"></textarea>
                    </div>
                    <div class="flex flex-col sm:flex-row-reverse sm:space-x-4 sm:space-x-reverse">
                        <button type="submit" class="w-full sm:w-auto gradient-bg px-4 py-2 rounded-lg text-white hover:opacity-90 transition duration-200">
                            Kirim Ulasan
                        </button>
                        <button type="button" id="cancelReview" class="w-full sm:w-auto mt-2 sm:mt-0 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition duration-200">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        // Initialize feather icons
        feather.replace();

        // --- KUNCI RESPONSIVE #5: JavaScript untuk Hamburger Menu ---
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileMenuIcon = document.getElementById('mobile-menu-icon');

        if (mobileMenuButton) {
            mobileMenuButton.addEventListener('click', () => {
                const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
                mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
                
                // Toggle menu visibility
                mobileMenu.classList.toggle('hidden');
                
                // Toggle icon (menu vs x)
                if (mobileMenu.classList.contains('hidden')) {
                    mobileMenuIcon.setAttribute('data-feather', 'menu');
                } else {
                    mobileMenuIcon.setAttribute('data-feather', 'x');
                }
                feather.replace(); // Re-render icon
            });
        }
        // --- Akhir Script Hamburger ---


        // Booking Modal Functionality
        const bookingModal = document.getElementById('bookingModal');
        const bookWorkerBtns = document.querySelectorAll('.book-worker-btn');
        const cancelBookingBtn = document.getElementById('cancelBooking');
        const dailyRateSummary = document.getElementById('dailyRateSummary');
        const daysCount = document.getElementById('daysCount');
        const totalPrice = document.getElementById('totalPrice');
        const startDateInput = bookingModal.querySelector('input[name="start_date"]'); // Scope to booking modal
        const endDateInput = bookingModal.querySelector('input[name="end_date"]');     // Scope to booking modal

        // Format currency function
        function formatCurrency(amount) {
            // Pastikan amount adalah angka sebelum memanggil toString
            if (typeof amount !== 'number') {
                amount = parseInt(amount) || 0;
            }
            return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Calculate days between dates
        function calculateDays(startDate, endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            if (isNaN(start.getTime()) || isNaN(end.getTime()) || end < start) return 0; // Check for valid dates
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            return diffDays + 1; // Include both start and end date
        }

        // Update price summary
        function updatePriceSummary(rate, startDate, endDate) {
            if (startDate && endDate) {
                const days = calculateDays(startDate, endDate);
                if (days > 0) {
                    const total = rate * days;
                    dailyRateSummary.textContent = formatCurrency(rate);
                    daysCount.textContent = days + ' hari';
                    totalPrice.textContent = formatCurrency(total);
                } else {
                    dailyRateSummary.textContent = formatCurrency(rate);
                    daysCount.textContent = '0 hari';
                    totalPrice.textContent = formatCurrency(0);
                }
            } else {
                dailyRateSummary.textContent = formatCurrency(rate);
                daysCount.textContent = '0 hari';
                totalPrice.textContent = formatCurrency(0);
            }
        }

        // Open booking modal
        bookWorkerBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const workerId = this.dataset.workerId;
                const workerName = this.dataset.workerName;
                const workerRate = parseInt(this.dataset.workerRate);
                const workerSkills = this.dataset.workerSkills;

                document.getElementById('selectedWorkerName').textContent = workerName;
                document.getElementById('selectedWorkerSkills').textContent = 'Keahlian: ' + workerSkills;
                document.getElementById('selectedWorkerRate').textContent = formatCurrency(workerRate) + '/hari';
                
                document.getElementById('workerId').value = workerId;
                document.getElementById('workerName').value = workerName;
                document.getElementById('workerRate').value = workerRate;

                // Pastikan form ada sebelum di-reset
                const bookingForm = bookingModal.querySelector('form');
                if(bookingForm) {
                    bookingForm.reset();
                }
                updatePriceSummary(workerRate, '', '');

                // Reset min date for end date
                if(endDateInput) endDateInput.min = startDateInput.min;

                bookingModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });
        });

        // Close booking modal
        function closeBookingModal() {
            bookingModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        if (cancelBookingBtn) {
            cancelBookingBtn.addEventListener('click', closeBookingModal);
        }

        // Update price when dates change
        if (startDateInput && endDateInput) { // Ensure elements exist
            startDateInput.addEventListener('change', updatePriceCalculation);
            endDateInput.addEventListener('change', updatePriceCalculation);

            function updatePriceCalculation() {
                const rateEl = document.getElementById('workerRate');
                const rate = rateEl ? parseInt(rateEl.value) : 0;
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;
                
                updatePriceSummary(rate, startDate, endDate);
            }

            // Validate end date is after start date
            startDateInput.addEventListener('change', function() {
                if (endDateInput.value && this.value > endDateInput.value) {
                    endDateInput.value = '';
                }
                // Set min date for end date
                endDateInput.min = this.value;
                updatePriceCalculation(); // Update harga setelah min date diset
            });

            endDateInput.addEventListener('change', function() {
                if (startDateInput.value && this.value < startDateInput.value) {
                    alert('Tanggal selesai harus setelah tanggal mulai');
                    this.value = '';
                }
                updatePriceCalculation(); // Update harga setelah validasi
            });
        }


        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const workersList = document.getElementById('workersList');
                if (workersList) {
                    const workers = workersList.querySelectorAll('.worker-card');
                    workers.forEach(worker => {
                        const workerNameEl = worker.querySelector('h3');
                        const workerInfoEl = worker.querySelector('p.text-gray-600');
                        
                        const workerName = workerNameEl ? workerNameEl.textContent.toLowerCase() : '';
                        const workerInfo = workerInfoEl ? workerInfoEl.textContent.toLowerCase() : '';
                        
                        if (workerName.includes(searchTerm) || workerInfo.includes(searchTerm)) {
                            worker.style.display = 'block';
                        } else {
                            worker.style.display = 'none';
                        }
                    });
                }
            });
        }

        // --- SCRIPT MODAL REVIEW BARU ---
        const reviewModal = document.getElementById('reviewModal');
        const cancelReviewBtn = document.getElementById('cancelReview');
        const starRatingContainer = document.getElementById('starRating');
        
        // PENTING: Ambil ikon BINTANG setelah feather.replace() mengubahnya menjadi SVG
        // Kita ambil elemen 'i' di HTML, tapi feather akan mengubahnya menjadi 'svg'
        // Jadi kita tetap query 'star-icon'
        const starIcons = reviewModal ? reviewModal.querySelectorAll('.star-icon') : [];
        const ratingValueInput = document.getElementById('ratingValue');

        // Open review modal
        document.querySelectorAll('.review-btn').forEach(button => {
            button.addEventListener('click', function() {
                const jobId = this.dataset.jobId; 
                const workerId = this.dataset.workerId;
                const workerName = this.dataset.workerName; 
                
                const reviewJobIdEl = document.getElementById('reviewJobId');
                const reviewWorkerIdEl = document.getElementById('reviewWorkerId');
                const reviewWorkerNameEl = document.getElementById('reviewWorkerName');

                if (reviewJobIdEl) reviewJobIdEl.value = jobId;
                if (reviewWorkerIdEl) reviewWorkerIdEl.value = workerId;
                if (reviewWorkerNameEl) reviewWorkerNameEl.textContent = 'Untuk: ' + workerName;
                
                const reviewForm = reviewModal.querySelector('form');
                if (reviewForm) {
                    reviewForm.reset();
                }
                // Panggil resetStars() SETELAH .reset() form
                // .reset() akan mengembalikan input hidden ke value="0"
                resetStars(); 
                
                if (reviewModal) {
                    reviewModal.classList.remove('hidden');
                }
                document.body.style.overflow = 'hidden';
            });
        });

        // Close review modal
        function closeReviewModal() {
            if (reviewModal) {
                reviewModal.classList.add('hidden');
            }
            document.body.style.overflow = 'auto';
        }
        if (cancelReviewBtn) {
            cancelReviewBtn.addEventListener('click', closeReviewModal);
        }

        // Star rating interactivity
        if (starIcons.length > 0) {
            starIcons.forEach(star => {
                star.addEventListener('mouseover', function() {
                    fillStars(this.dataset.value);
                });
                
                star.addEventListener('mouseout', function() {
                    if (ratingValueInput) {
                        fillStars(ratingValueInput.value);
                    }
                });
                
                star.addEventListener('click', function() {
                    if (ratingValueInput) {
                        // 1. Set nilai input hidden
                        ratingValueInput.value = this.dataset.value;
                        // 2. Update tampilan bintang berdasarkan nilai baru
                        fillStars(this.dataset.value); 
                    }
                });
            });
        }

        // ===== INI FUNGSI YANG DIPERBAIKI =====
        function fillStars(value) {
            if (!starIcons) return;
            
            // Konversi nilai (yang bisa jadi string) ke angka. 
            // Jika 'value' itu undefined atau 0, ratingValue akan jadi 0.
            const ratingValue = parseInt(value) || 0; 

            starIcons.forEach(star => {
                const starValue = parseInt(star.dataset.value);
                
                // Logika sederhana:
                // Jika nilai bintang (1, 2, 3...) <= nilai rating yang diklik (misal 4)
                if (starValue <= ratingValue) {
                    // Tambahkan kelas 'filled'. CSS akan membuatnya kuning.
                    star.classList.add('filled');
                } else {
                    // Hapus kelas 'filled'. CSS akan membuatnya abu-abu.
                    star.classList.remove('filled');
                }
                // Kita TIDAK perlu .innerHTML atau feather.replace() di sini.
            });
        }
        
        function resetStars() {
            if (ratingValueInput) {
                ratingValueInput.value = 0;
            }
            // Panggil fillStars dengan nilai 0 untuk membuat semua bintang abu-abu
            fillStars(0);
        }

        // Close modal with Escape key (berlaku untuk kedua modal)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (bookingModal && !bookingModal.classList.contains('hidden')) {
                    closeBookingModal();
                }
                if (reviewModal && !reviewModal.classList.contains('hidden')) {
                    closeReviewModal();
                }
            }
        });
        
        // Close modal when clicking outside (berlaku untuk kedua modal)
        document.addEventListener('click', function(e) {
             if (e.target.classList.contains('modal-overlay')) {
                closeBookingModal();
                closeReviewModal();
            }
        });

    </script>
</body>
</html>