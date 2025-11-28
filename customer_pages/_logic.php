<?php
/**
 * Customer Dashboard - Logic
 * Handles data fetching, form submissions, and authentication for the customer dashboard.
 */

require_once 'functions.php';

// Set secure headers
SecureHeaders::set();

// Authentication check
redirectIfNotCustomer();

// Get customer info
$customer_email = $_SESSION['user'];
$customer_id = $_SESSION['user_id'] ?? null;

// Fetch up-to-date customer data from the database
$customer_data = getCustomerDataById($customer_id);
$customer_address = $customer_data['alamat_lengkap'] ?? 'Alamat tidak tersedia';

// Also update session with fresh data to avoid inconsistencies in other parts of the application
$_SESSION['user_name'] = $customer_data['nama_lengkap'] ?? $_SESSION['user_name'];
$_SESSION['user_phone'] = $customer_data['telepon'] ?? $_SESSION['user_phone'];
$_SESSION['user_address'] = $customer_data['alamat_lengkap'] ?? 'Alamat tidak tersedia';


// Initialize variables for messages
$success_message = '';
$error_message = '';

// Check for Flash Messages (from Redirects)
if (isset($_SESSION['flash_success'])) {
    $success_message = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['flash_error'])) {
    $error_message = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $error_message = 'Sesi tidak valid. Silakan refresh halaman.';
    } else {
        
        // --- Handle booking ---
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
                        'id_pekerja' => $worker_id,
                        'nama_pekerja' => $worker['nama'],
                        'jenis_pekerjaan' => InputValidator::sanitizeString($_POST['job_type'] ?? ''),
                        'tanggal_mulai' => $startDate,
                        'tanggal_selesai' => $endDate,
                        'lokasi' => InputValidator::sanitizeString($_POST['job_location'] ?? ''),
                        'alamat_lokasi' => InputValidator::sanitizeString($_POST['job_location'] ?? ''),
                        'status_pekerjaan' => 'pending',
                        'nama_pelanggan' => $_SESSION['user_name'],
                        'telepon_pelanggan' => $_SESSION['user_phone'] ?? 'N/A',
                        'email_pelanggan' => $customer_email,
                        'deskripsi' => InputValidator::sanitizeString($_POST['job_notes'] ?? '')
                    ];
                    
                    // Calculate price
                    $start = new DateTime($startDate);
                    $end = new DateTime($endDate);
                    $days = $end->diff($start)->days + 1;
                    $jobData['harga'] = $days > 0 ? $worker['tarif_per_jam'] * $days : 0;
                    
                    if (addWorkerToJob($jobData)) {
                        SecurityLogger::log('INFO', 'Job created', ['customer' => $customer_email, 'worker' => $worker_id]);
                        
                        // REDIRECT to prevent duplicate submissions on refresh
                        $_SESSION['flash_success'] = 'Pesanan berhasil dibuat!';
                        header("Location: customer_dashboard.php?tab=orders");
                        exit;
                    } else {
                        $error_message = 'Gagal membuat pesanan.';
                    }
                } else {
                    $error_message = 'Worker tidak ditemukan.';
                }
            }
        }
        
        // --- Handle review submission ---
        if (isset($_POST['submit_review'])) {
            $jobId = InputValidator::sanitizeString($_POST['job_id'] ?? '');
            $workerId = InputValidator::sanitizeString($_POST['worker_id'] ?? '');
            $rating = InputValidator::validateIntRange($_POST['rating'] ?? 0, 1, 5);
            $comment = InputValidator::sanitizeString($_POST['comment'] ?? '');
            
            if (empty($workerId)) {
                $error_message = 'Gagal mengirim ulasan: ID Pekerja tidak ditemukan.';
            } elseif (!$rating) {
                $error_message = 'Rating harus dipilih (1-5 bintang).';
            } else {
                try {
                    // Check if review already exists
                    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM ulasan WHERE id_pekerjaan = ? AND id_pelanggan = ?");
                    $stmtCheck->execute([$jobId, $customer_id]);
                    $reviewExists = $stmtCheck->fetchColumn() > 0;
                    
                    if ($reviewExists) {
                        $error_message = 'Anda sudah memberikan ulasan untuk pekerjaan ini.';
                    } else {
                        DatabaseHelper::beginTransaction();
                        
                        // Insert review
                        $sql = "INSERT INTO ulasan (id_pekerjaan, id_pekerja, id_pelanggan, rating, komentar) 
                                VALUES (?, ?, ?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$jobId, $workerId, $customer_id, $rating, $comment]);
                        
                        // Update worker rating
                        $sqlAvg = "SELECT AVG(rating) as avg_rating FROM ulasan WHERE id_pekerja = ?";
                        $stmtAvg = $pdo->prepare($sqlAvg);
                        $stmtAvg->execute([$workerId]);
                        $newRating = $stmtAvg->fetch()['avg_rating'];
                        
                        $sqlUpdate = "UPDATE pekerja SET rating = ? WHERE id_pekerja = ?";
                        $stmtUpdate = $pdo->prepare($sqlUpdate);
                        $stmtUpdate->execute([$newRating, $workerId]);
                        
                        DatabaseHelper::commit();
                        // ...
                        
                        SecurityLogger::log('INFO', 'Review submitted', ['job' => $jobId, 'rating' => $rating]);
                        
                        // REDIRECT to prevent duplicate submissions
                        $_SESSION['flash_success'] = 'Terima kasih! Ulasan Anda telah berhasil dikirim.';
                        header("Location: customer_dashboard.php?tab=orders&status=completed");
                        exit;
                    }
                    
                } catch (PDOException $e) {
                    DatabaseHelper::rollback();
                    SecurityLogger::logError('Review submission error: ' . $e->getMessage());
                    $error_message = 'Gagal mengirim ulasan. Silakan coba lagi.';
                }
            }
        }

        // --- Handle new job posting ---
        if (isset($_POST['post_new_job'])) {
            $title = InputValidator::sanitizeString($_POST['job_title'] ?? '');
            $job_type = InputValidator::sanitizeString($_POST['job_type'] ?? '');
            $description = InputValidator::sanitizeString($_POST['job_description'] ?? '');
            $location = InputValidator::sanitizeString($_POST['job_location'] ?? '');
            $budget = !empty($_POST['job_budget']) ? InputValidator::validateFloat($_POST['job_budget']) : null;

            if (empty($title) || empty($job_type) || empty($description) || empty($location)) {
                $error_message = 'Harap isi semua kolom yang wajib diisi.';
            } else {
                try {
                    $sql = "INSERT INTO lowongan_diposting (id_pelanggan, judul_lowongan, deskripsi_lowongan, jenis_pekerjaan, lokasi, anggaran) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$customer_id, $title, $description, $job_type, $location, $budget]);
                    
                    SecurityLogger::log('INFO', 'Posted job created', ['customer' => $customer_email, 'title' => $title]);
                    
                    $_SESSION['flash_success'] = 'Pekerjaan Anda telah berhasil diposting!';
                    header("Location: customer_dashboard.php?tab=my_jobs");
                    exit;

                } catch (PDOException $e) {
                    // Log the detailed technical error for debugging
                    SecurityLogger::logError('Database error while posting job: ' . $e->getMessage());

                    // Provide a more specific, user-friendly error message
                    if (str_contains($e->getMessage(), 'out of range')) {
                        $error_message = 'Gagal memposting pekerjaan: Nilai anggaran terlalu besar. Harap periksa kembali nominal yang Anda masukkan.';
                    } else {
                        $error_message = 'Gagal memposting pekerjaan karena ada masalah teknis pada database. Silakan coba lagi nanti.';
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
$postedJobs = []; // Initialize posted jobs array

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

// Get customer's posted jobs
if ($active_tab === 'my_jobs') {
    $stmt = $pdo->prepare("
        SELECT 
            pj.*, 
            j.status_pekerjaan as job_status,
            pj.status_lowongan as posted_job_status
        FROM lowongan_diposting pj
        LEFT JOIN pekerjaan j ON pj.id_lowongan = j.id_lowongan_diposting
        WHERE pj.id_pelanggan = ? 
        ORDER BY pj.dibuat_pada DESC
    ");
    $stmt->execute([$customer_id]);
    $postedJobs = $stmt->fetchAll();
}

// Get customer orders dengan info review
$customerOrders = [];
if ($active_tab === 'orders') {
    if ($order_status_filter === 'all') {
        $customerOrders = getCustomerOrders($customer_email);
    } else {
        $allOrders = getCustomerOrders($customer_email);
        $customerOrders = array_filter($allOrders, function($order) use ($order_status_filter) {
            return $order['status_pekerjaan'] === $order_status_filter;
        });
    }
    
    // Check review status for each order
    // BUG FIX: using &$order reference without unset caused array corruption in later loops
    foreach ($customerOrders as &$order) {
        $order['has_review'] = hasCustomerReviewedJob($order['id_pekerjaan'], (int)$customer_id);
    }
    unset($order); // CRITICAL FIX: Unlink the reference to prevent duplicate display issues
}

// Get top workers untuk homepage
$topWorkers = [];
if ($active_tab === 'home') {
    $topWorkers = getTopRatedWorkers(4);
}

// Count pending orders
$pendingOrderCount = count(array_filter(
    getCustomerOrders($customer_email), 
    function($o) { return $o['status_pekerjaan'] === 'pending'; }
));

// --- Gabungkan semua data worker untuk modal ---
$allWorkersForModal = [];
$worker_data_sources = [$workers, $topWorkers];

// Ambil data worker dari pesanan
if (!empty($customerOrders)) {
    $orderWorkerIds = array_filter(array_column($customerOrders, 'id_pekerja'));
    if(!empty($orderWorkerIds)) {
        $placeholders = implode(',', array_fill(0, count($orderWorkerIds), '?'));
        $stmt = $pdo->prepare("SELECT * FROM pekerja WHERE id_pekerja IN ($placeholders)");
        $stmt->execute($orderWorkerIds);
        $orderWorkers = $stmt->fetchAll();
        $worker_data_sources[] = $orderWorkers;
    }
}

foreach ($worker_data_sources as $source) {
    if (!empty($source)) {
        foreach ($source as $worker) {
            if (!isset($allWorkersForModal[$worker['id_pekerja']])) {
                 if (isset($worker['keahlian']) && !is_array($worker['keahlian'])) {
                    $worker['keahlian'] = json_decode($worker['keahlian'], true) ?: [];
                }
                $allWorkersForModal[$worker['id_pekerja']] = $worker;
            }
        }
    }
}

