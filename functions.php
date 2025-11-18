<?php 
declare(strict_types=1);

/**
 * PERBAIKAN: Load db.php TERLEBIH DAHULU
 * Ini akan mendefinisikan APP_INIT sebelum security_config.php dimuat
 */
require_once 'db.php'; // <-- INI HARUS PALING ATAS!

/**
 * Setelah db.php dimuat, baru load security_config.php
 */
require_once 'security_config.php';

/**
 * Utility Functions for NguliKuy Application (MySQL Version)
 */

// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user']) && isset($_SESSION['user_role']);
}

function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

function isCustomer(): bool {
    return isLoggedIn() && $_SESSION['user_role'] === 'customer';
}

function redirectIfNotLoggedIn(): void {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

function redirectIfNotAdmin(): void {
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

function redirectIfNotCustomer(): void {
    if (!isCustomer()) {
        header('Location: index.php');
        exit();
    }
}

function logout(): void {
    session_destroy();
    header('Location: index.php');
    exit();
}

// --- FUNGSI CSRF (DARI TAHAP 1) ---

/**
 * Membuat atau mengambil CSRF token yang ada di session.
 *
 * @return string Token CSRF
 */
function getCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Memvalidasi CSRF token yang dikirim dari form.
 *
 * @param string $tokenFromForm Token dari $_POST atau input lainnya.
 * @return bool True jika valid, false jika tidak.
 */
function validateCsrfToken(string $tokenFromForm): bool {
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $tokenFromForm);
}

/**
 * Helper untuk mencetak hidden input field CSRF.
 *
 * @return string HTML untuk input field
 */
function csrfInput(): string {
    return '<input type="hidden" name="csrf_token" value="' . getCsrfToken() . '">';
}

// --- AKHIR FUNGSI CSRF ---


function formatCurrency(int|float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatRating(int|float $rating): string {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
    
    $stars = str_repeat('★', (int)$fullStars);
    $stars .= $halfStar ? '½' : '';
    $stars .= str_repeat('☆', (int)$emptyStars);
    
    return $stars;
}

function handlePhotoUpload(array $file): array {
    // Validasi menggunakan class baru
    $validation = SecureFileUpload::validate($file);
    
    if (!$validation['valid']) {
        return ['success' => false, 'error' => implode(', ', $validation['errors'])];
    }
    
    $uploadDir = 'uploads/workers/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate secure filename
    $fileName = SecureFileUpload::generateSecureFilename($validation['extension']);
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        chmod($filePath, 0644);
        SecurityLogger::log('INFO', 'File uploaded: ' . $fileName);
        return ['success' => true, 'file_path' => $filePath, 'file_name' => $fileName];
    }
    
    return ['success' => false, 'error' => 'Gagal mengupload file.'];
}


/**
 * FUNGSI-FUNGSI YANG DIMIGRASI KE MySQL
 * Semua fungsi JSON (readJSON, writeJSON, dll.) telah dihapus.
 */

function authenticate(string $username, string $password): bool {
    global $pdo;
    
    // Rate limiting
    $rateLimiter = new RateLimiter($pdo);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    if (!$rateLimiter->isAllowed($ip, 'login', 5, 300)) {
        SecurityLogger::logSecurityEvent('Rate limit exceeded for login', ['ip' => $ip, 'username' => $username]);
        return false;
    }
    
    // Input validation
    if (!InputValidator::validateEmail($username) && strlen($username) < 3) {
        SecurityLogger::logLoginAttempt($username, false);
        return false;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Reset rate limit on successful login
            $rateLimiter->reset($ip, 'login');
            
            // Set session
            $_SESSION['user'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_phone'] = $user['phone'] ?? '';
            $_SESSION['worker_profile_id'] = $user['worker_profile_id'] ?? null;
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            SecurityLogger::logLoginAttempt($username, true);
            return true;
        }

        SecurityLogger::logLoginAttempt($username, false);
        return false;
        
    } catch (PDOException $e) {
        SecurityLogger::logError('Authentication error: ' . $e->getMessage());
        return false;
    }
}

// === KODE BERBAHAYA YANG DIKOMENTARI TELAH DIHAPUS ===
// Blok fungsi 'authenticate' yang lama (plain-text) telah dihapus dari sini.

/**
 * Get Job Details by ID
 * Mengambil detail satu job beserta beberapa info worker-nya.
 */
function getJobById(string $jobId): ?array {
    global $pdo;
    try {
        // Query ini menggabungkan tabel jobs dan workers
        $stmt = $pdo->prepare("SELECT j.*, w.name as worker_full_name, w.photo as worker_photo, w.phone as worker_phone, w.email as worker_email
                               FROM jobs j
                               LEFT JOIN workers w ON j.workerId = w.id
                               WHERE j.jobId = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();
        return $job ?: null; // Mengembalikan array asosiatif detail job, atau null jika tidak ditemukan
    } catch (PDOException $e) {
        error_log("Error getting job by ID ($jobId): " . $e->getMessage());
        return null; // Mengembalikan null jika ada error DB
    }
}

/**
 * Verify if the logged-in customer owns the job
 * Fungsi keamanan untuk memastikan customer hanya bisa melihat pesanannya sendiri.
 */
function verifyCustomerOwnsJob(string $jobId, string $customerEmail): bool {
    global $pdo;
     try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE jobId = ? AND customerEmail = ?");
        $stmt->execute([$jobId, $customerEmail]);
        // Mengembalikan true jika ada 1 baris (cocok), false jika 0
        return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error verifying job ownership ($jobId, $customerEmail): " . $e->getMessage());
        return false; // Anggap tidak cocok jika ada error DB
    }
}

/**
 * Get All Reviews with Details
 * Mengambil semua review dan menggabungkannya dengan info user, worker, dan job.
 */
function getAllReviews(): array {
    global $pdo;
    try {
        // Query ini menggabungkan tabel reviews dengan users, workers, dan jobs
        $sql = "SELECT 
                    r.id as review_id, 
                    r.rating, 
                    r.comment, 
                    r.createdAt as review_date,
                    j.jobId, 
                    j.jobType,
                    u.name as customer_name, 
                    u.username as customer_email, -- Ambil email jika nama tidak ada
                    w.id as worker_id, 
                    w.name as worker_name
                FROM reviews r
                LEFT JOIN users u ON r.customerId = u.id
                LEFT JOIN workers w ON r.workerId = w.id
                LEFT JOIN jobs j ON r.jobId = j.jobId
                ORDER BY r.createdAt DESC"; // Urutkan dari terbaru
                
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(); // Mengembalikan array berisi semua review

    } catch (PDOException $e) {
        error_log("Error getting all reviews: " . $e->getMessage());
        return []; // Mengembalikan array kosong jika ada error
    }
}

/**
 * Delete Review by ID
 * Fungsi untuk menghapus review (opsional, untuk tombol hapus nanti)
 * * --- DIPERBARUI DENGAN TRANSAKSI ---
 */
function deleteReview(int $reviewId): bool {
    global $pdo;
    
    // --- TRANSAKSI ---
    $pdo->beginTransaction();

    try {
        // 1. Dapatkan workerId dari review yang akan dihapus
        $stmtGetWorker = $pdo->prepare("SELECT workerId FROM reviews WHERE id = ?");
        $stmtGetWorker->execute([$reviewId]);
        $reviewData = $stmtGetWorker->fetch();
        
        if (!$reviewData) {
            $pdo->rollBack(); // Batalkan jika review tidak ada
            return false;
        }
        $workerId = $reviewData['workerId'];

        // 2. Hapus review
        $sql = "DELETE FROM reviews WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $deleted = $stmt->execute([$reviewId]);

        // 3. Jika berhasil dihapus DAN workerId ada, hitung ulang rating worker
        if ($deleted && $workerId) {
             $sqlAvg = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE workerId = ?");
             $sqlAvg->execute([$workerId]);
             
             $newRating = $sqlAvg->fetch()['avg_rating'] ?? 4.0; // Default 4.0 jika tidak ada review

             $sqlUpdateWorker = $pdo->prepare("UPDATE workers SET rating = ? WHERE id = ?");
             $sqlUpdateWorker->execute([$newRating, $workerId]);
        }
        
        // --- TRANSAKSI ---
        $pdo->commit(); // Sukses, simpan semua perubahan
        return $deleted; // Mengembalikan true jika berhasil dihapus

    } catch (PDOException $e) {
        // --- TRANSAKSI ---
        $pdo->rollBack(); // Ada error, batalkan semua
        error_log("Error deleting review ($reviewId): " . $e->getMessage());
        return false;
    }
}

function getWorkers(): array {
    global $pdo;
    
    // Kueri ini di-JOIN dengan tabel reviews untuk MENGHITUNG ulasan
    $sql = "SELECT w.*, COUNT(r.id) as review_count
            FROM workers w
            LEFT JOIN reviews r ON w.id = r.workerId
            GROUP BY w.id
            ORDER BY w.name ASC"; // Urutkan berdasarkan nama
            
    $stmt = $pdo->query($sql); // <-- UBAH BARIS INI (gunakan $sql)
    $workers = $stmt->fetchAll();
    
    // Decode 'skills' dari JSON string ke array PHP
    foreach ($workers as &$worker) {
        $worker['skills'] = json_decode((string)$worker['skills'], true) ?: [];
    }
    return $workers;
}

function getJobs(): array {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM jobs ORDER BY createdAt DESC");
    return $stmt->fetchAll();
}

function getAvailableWorkers(): array {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM workers WHERE status = 'Available'");
    $workers = $stmt->fetchAll();
    
    foreach ($workers as &$worker) {
        $worker['skills'] = json_decode((string)$worker['skills'], true) ?: [];
    }
    return $workers;
}

function getTopRatedWorkers(int $limit = 4): array {
    global $pdo;

    // Kueri ini juga di-JOIN untuk MENGHITUNG ulasan
    $sql = "SELECT w.*, COUNT(r.id) as review_count
            FROM workers w
            LEFT JOIN reviews r ON w.id = r.workerId
            WHERE w.status = 'Available'
            GROUP BY w.id
            ORDER BY w.rating DESC, review_count DESC
            LIMIT ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$limit]);
    $workers = $stmt->fetchAll();
    
    foreach ($workers as &$worker) {
        $worker['skills'] = json_decode((string)$worker['skills'], true) ?: [];
    }
    return $workers;
}

function getCustomerOrders(string $customerEmail): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE customerEmail = ? ORDER BY createdAt DESC");
    $stmt->execute([$customerEmail]);
    return $stmt->fetchAll();
}

function generateWorkerId(): string {
    global $pdo;
    // Logika ini meniru logika lama Anda, tapi mengambil data dari DB
    $stmt = $pdo->query("SELECT id FROM workers ORDER BY CAST(SUBSTR(id, 4) AS UNSIGNED) DESC LIMIT 1");
    $lastWorker = $stmt->fetch();
    
    $lastId = 0;
    if ($lastWorker) {
        $lastId = intval(substr($lastWorker['id'], 3));
    }
    return 'KUL' . str_pad((string)($lastId + 1), 3, '0', STR_PAD_LEFT);
}

function generateJobId(): string {
    global $pdo;
    $stmt = $pdo->query("SELECT jobId FROM jobs ORDER BY CAST(SUBSTR(jobId, 4) AS UNSIGNED) DESC LIMIT 1");
    $lastJob = $stmt->fetch();
    
    $lastId = 0;
    if ($lastJob) {
        $lastId = intval(substr($lastJob['jobId'], 3));
    }
    return 'JOB' . str_pad((string)($lastId + 1), 3, '0', STR_PAD_LEFT);
}

function addWorker(array $workerData): bool {
    global $pdo;
    
    // --- SEKARANG MENGGUNAKAN TRANSAKSI ---
    $pdo->beginTransaction();

    try {
        // 1. Buat ID Kuli
        $id = generateWorkerId();
        
        // 2. INSERT ke tabel 'workers' (Profil Kuli)
        $sqlWorker = "INSERT INTO workers (id, name, email, phone, location, skills, status, rate, experience, description, photo, rating, completedJobs, joinDate)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtWorker = $pdo->prepare($sqlWorker);
        $skillsJson = json_encode($workerData['skills'] ?? []);
        
        $stmtWorker->execute([
            $id,
            $workerData['name'] ?? '',
            $workerData['email'] ?? '',
            $workerData['phone'] ?? '',
            $workerData['location'] ?? '',
            $skillsJson,
            $workerData['status'] ?? 'Available',
            $workerData['rate'] ?? 0,
            $workerData['experience'] ?? '',
            $workerData['description'] ?? '',
            $workerData['photo'] ?? getDefaultWorkerPhoto(),
            $workerData['rating'] ?? 4.0,
            $workerData['completedJobs'] ?? 0,
            $workerData['joinDate'] ?? date('Y-m-d')
        ]);
        
        // 3. Buat Akun Login untuk Kuli di tabel 'users'
        // Anda bisa memberi password acak, tapi untuk kemudahan kita set default 'ngulikuy123'
        $defaultPassword = 'ngulikuy123';
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
        
        $sqlUser = "INSERT INTO users (username, password, role, name, phone, worker_profile_id)
                    VALUES (?, ?, ?, ?, ?, ?)";
        $stmtUser = $pdo->prepare($sqlUser);
        $stmtUser->execute([
            $workerData['email'] ?? '',
            $hashedPassword,
            'worker', // Set role sebagai 'worker'
            $workerData['name'] ?? '',
            $workerData['phone'] ?? '',
            $id // Tautkan ke ID Kuli (KULXXX)
        ]);
        
        // 4. Jika semua berhasil, commit
        $pdo->commit();
        return true;

    } catch (PDOException $e) {
        // Jika salah satu gagal, batalkan semua
        $pdo->rollBack();
        error_log($e->getMessage());
        // Kirim pesan error spesifik jika email duplikat
        if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
             throw new Exception("Gagal menambah kuli: Email '" . $workerData['email'] . "' sudah digunakan untuk akun lain.");
        }
        return false;
    }
}

/**
 * --- DIPERBARUI DENGAN TRANSAKSI ---
 */
function addJob(array $jobData): bool {
    global $pdo;
    
    // --- TRANSAKSI ---
    $pdo->beginTransaction();

    try {
        // 1. Insert ke tabel jobs
        $sql = "INSERT INTO jobs (jobId, workerId, workerName, jobType, startDate, endDate, customer, customerPhone, customerEmail, price, location, address, description, status, createdAt)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
        $stmt = $pdo->prepare($sql);
        
        $jobId = generateJobId();
        $createdAt = date('Y-m-d H:i:s');
        
        $stmt->execute([
            $jobId,
            $jobData['workerId'] ?? null,
            $jobData['workerName'] ?? 'Unknown',
            $jobData['jobType'] ?? '',
            $jobData['startDate'] ?? null,
            $jobData['endDate'] ?? null,
            $jobData['customer'] ?? '',
            $jobData['customerPhone'] ?? '',
            $jobData['customerEmail'] ?? '',
            $jobData['price'] ?? 0,
            $jobData['location'] ?? '',
            $jobData['address'] ?? '',
            $jobData['description'] ?? '',
            $jobData['status'] ?? 'pending',
            $createdAt
        ]);
        
        // 2. Update status worker
        if (isset($jobData['workerId']) && ($jobData['status'] === 'in-progress' || $jobData['status'] === 'pending')) {
            // Kita panggil fungsi updateWorkerStatus
            // Jika fungsi ini gagal, dia akan melempar Exception (karena ATTR_ERRMODE)
            // dan akan ditangkap oleh blok catch di bawah
            updateWorkerStatus((string)$jobData['workerId'], 'Assigned');
        }
        
        // --- TRANSAKSI ---
        $pdo->commit(); // Sukses, simpan semua perubahan
        return true;

    } catch (Exception $e) { // Gunakan Exception umum
        // --- TRANSAKSI ---
        $pdo->rollBack(); // Ada error, batalkan semua
        error_log("Error adding job: " . $e->getMessage());
        return false;
    }
}

function updateWorkerStatus(string $workerId, string $status): bool {
    global $pdo;
    // Fungsi ini hanya 1 query, tidak perlu transaksi
    // Tapi dia akan 'throw' error jika gagal, yang akan ditangkap
    // oleh fungsi lain yang memanggilnya (seperti addJob)
    try {
        $sql = "UPDATE workers SET status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $workerId]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        throw $e; // Lempar error agar transaksi di 'addJob' bisa menangkapnya
    }
}

/**
 * --- DIPERBARUI DENGAN TRANSAKSI ---
 * (Walaupun updateJobStatus sudah aman, lebih baik
 * membungkus logika get+update dalam transaksi
 * untuk mencegah 'race condition')
 */
function updateJobStatus(string $jobId, string $status): bool {
    global $pdo;
    $pdo->beginTransaction();
    
    try {
        // LOCK baris ini untuk transaksi
        $stmtJob = $pdo->prepare("SELECT workerId, status FROM jobs WHERE jobId = ? FOR UPDATE");
        $stmtJob->execute([$jobId]);
        $job = $stmtJob->fetch();

        if (!$job) {
            $pdo->rollBack();
            return false;
        }

        // Update status job
        $sql = "UPDATE jobs SET status = ?, updatedAt = NOW() WHERE jobId = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $jobId]);
        
        // Update status worker
        if ($job['workerId'] && in_array($status, ['completed', 'cancelled'])) {
            updateWorkerStatus($job['workerId'], 'Available');
        }
        
        $pdo->commit();
        return true;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating job status: " . $e->getMessage());
        return false;
    }
}

function getWorkerById(string $workerId): ?array {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM workers WHERE id = ?");
        $stmt->execute([$workerId]);
        $worker = $stmt->fetch();
        
        if ($worker) {
            $worker['skills'] = json_decode((string)$worker['skills'], true) ?: [];
        }
        return $worker ?: null;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return null;
    }
}

function updateWorker(string $workerId, array $updatedData): bool {
    global $pdo;
    
    // Fungsi ini hanya 1 query, tidak perlu transaksi
    try {
        $params = [
            'name' => $updatedData['name'] ?? '',
            'email' => $updatedData['email'] ?? '',
            'phone' => $updatedData['phone'] ?? '',
            'location' => $updatedData['location'] ?? '',
            'skills' => json_encode($updatedData['skills'] ?? []),
            'status' => $updatedData['status'] ?? 'Available',
            'rate' => $updatedData['rate'] ?? 0,
            'experience' => $updatedData['experience'] ?? '',
            'description' => $updatedData['description'] ?? '',
            'id' => $workerId
        ];
        
        // Hanya update foto jika ada foto baru
        if (!empty($updatedData['photo'])) {
            $sql = "UPDATE workers SET name = :name, email = :email, phone = :phone, location = :location, 
                    skills = :skills, status = :status, rate = :rate, experience = :experience, 
                    description = :description, photo = :photo WHERE id = :id";
            $params['photo'] = $updatedData['photo'];
        } else {
            $sql = "UPDATE workers SET name = :name, email = :email, phone = :phone, location = :location, 
                    skills = :skills, status = :status, rate = :rate, experience = :experience, 
                    description = :description WHERE id = :id";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return true;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

function deleteWorker(string $workerId): bool {
    global $pdo;
    
    $pdo->beginTransaction();

    try {
        // 1. Hapus worker dari tabel 'workers'
        $sql = "DELETE FROM workers WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$workerId]);
        
        // 2. Hapus akun user kuli dari tabel 'users'
        $sqlUser = "DELETE FROM users WHERE worker_profile_id = ?";
        $pdo->prepare($sqlUser)->execute([$workerId]);
        
        // 3. Update job yang terkait (Ini sudah ada di kode Anda, dan sudah benar)
        $sqlJobs = "UPDATE jobs SET workerId = NULL, workerName = 'Deleted Worker' WHERE workerId = ?";
        $pdo->prepare($sqlJobs)->execute([$workerId]);
        
        $pdo->commit();
        return true;

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting worker: " . $e->getMessage());
        return false;
    }
}

/**
 * --- DIPERBARUI DENGAN TRANSAKSI ---
 */
function deleteJob(string $jobId): bool {
    global $pdo;
    
    // --- TRANSAKSI ---
    $pdo->beginTransaction();
    
    try {
        // 1. Dapatkan job untuk info worker
        $job = null;
        $stmtJob = $pdo->prepare("SELECT workerId, status FROM jobs WHERE jobId = ?");
        $stmtJob->execute([$jobId]);
        $job = $stmtJob->fetch();
        
        // 2. Bebaskan worker jika job masih 'pending' atau 'in-progress'
        if ($job && $job['workerId'] && ($job['status'] === 'in-progress' || $job['status'] === 'pending')) {
            updateWorkerStatus($job['workerId'], 'Available');
        }

        // 3. Hapus job
        $sql = "DELETE FROM jobs WHERE jobId = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$jobId]);
        
        // --- TRANSAKSI ---
        $pdo->commit();
        return true;

    } catch (Exception $e) { // Tangkap Exception umum
        // --- TRANSAKSI ---
        $pdo->rollBack();
        error_log("Error deleting job: " . $e->getMessage());
        return false;
    }
}

function searchWorkers(array $criteria = []): array {
    global $pdo;
    
    $sql = "SELECT w.*, COUNT(r.id) as review_count
            FROM workers w
            LEFT JOIN reviews r ON w.id = r.workerId
            WHERE w.status = 'Available'";
    $params = [];
    
    if (!empty($criteria['skill'])) {
        // Tanda '?' dalam JSON_CONTAINS perlu di-quote
        $sql .= " AND JSON_CONTAINS(skills, ?)";
        $params[] = '"' . $criteria['skill'] . '"';
    }
    
    if (!empty($criteria['location'])) {
        $sql .= " AND location LIKE ?";
        $params[] = '%' . $criteria['location'] . '%';
    }
    
    if (!empty($criteria['min_price'])) {
        $sql .= " AND rate >= ?";
        $params[] = intval($criteria['min_price']);
    }
    
    if (!empty($criteria['max_price'])) {
        $sql .= " AND rate <= ?";
        $params[] = intval($criteria['max_price']);
    }
    
    if (!empty($criteria['min_rating'])) {
        $sql .= " AND w.rating >= ?"; // Tambahkan 'w.'
        $params[] = floatval($criteria['min_rating']);
    }
    
    $sql .= " GROUP BY w.id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $workers = $stmt->fetchAll();
        
        foreach ($workers as &$worker) {
            $worker['skills'] = json_decode((string)$worker['skills'], true) ?: [];
        }
        return $workers;
        
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}

function getDashboardStats(): array {
    global $pdo;
    
    try {
        $stats = [];
        
        $stats['total_workers'] = $pdo->query("SELECT COUNT(*) FROM workers")->fetchColumn();
        $stats['available_workers'] = $pdo->query("SELECT COUNT(*) FROM workers WHERE status = 'Available'")->fetchColumn();
        $stats['on_job_workers'] = $pdo->query("SELECT COUNT(*) FROM workers WHERE status = 'Assigned'")->fetchColumn();
        $stats['active_jobs'] = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status IN ('in-progress', 'pending')")->fetchColumn();
        $stats['completed_jobs'] = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'completed'")->fetchColumn();
        $stats['pending_jobs'] = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'pending'")->fetchColumn();
        
        return $stats;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return array_fill_keys(['total_workers', 'available_workers', 'on_job_workers', 'active_jobs', 'completed_jobs', 'pending_jobs'], 0);
    }
}

function getRecentJobs(int $limit = 5): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM jobs ORDER BY createdAt DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function exportData(): void {
    // Fungsi ini sekarang mengambil dari DB, bukan file
    global $pdo;
    
    $workersStmt = $pdo->query("SELECT * FROM workers");
    $workers = $workersStmt->fetchAll();
    
    // Decode skills untuk ekspor yang konsisten
    foreach ($workers as &$worker) {
        $worker['skills'] = json_decode((string)$worker['skills'], true) ?: [];
    }
    
    $jobsStmt = $pdo->query("SELECT * FROM jobs");
    $jobs = $jobsStmt->fetchAll();
    
    $data = [
        'workers' => $workers,
        'jobs' => $jobs,
        'exported_at' => date('Y-m-d H:i:s'),
        'total_workers' => count($workers),
        'total_jobs' => count($jobs)
    ];
    
    $filename = 'ngulikuy_export_' . date('Y-m-d_H-i-s') . '.json';
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Fungsi lain dari file asli Anda yang tidak perlu diubah (seperti log, dll.)
// ... (tambahkan fungsi lain jika ada, misal: validateWorkerData, logActivity, dll.)
// ...

function getDefaultWorkerPhoto(): string {
    return 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&h=150&fit=crop&crop=face';
}

function getStatusClass(string $status, string $type = 'job'): string {
     if ($type === 'job') {
         $classes = [
             'completed' => 'status-completed',
             'in-progress' => 'status-in-progress',
             'pending' => 'status-pending',
             'cancelled' => 'status-cancelled'
         ];
         return $classes[$status] ?? 'status-pending';
     } else { // type === 'worker'
         $classes = [
             'Available' => 'status-available',
             'Assigned' => 'status-assigned',
             'On Leave' => 'status-on-leave'
         ];
         return $classes[$status] ?? 'status-available';
     }
}

function getStatusTextAndClass(string $status): array {
    $text = 'Tidak Diketahui';
    $class = 'bg-gray-200 text-gray-800'; // Default

    switch ($status) {
        case 'pending':
            $text = 'Menunggu Konfirmasi';
            $class = 'bg-yellow-100 text-yellow-800';
            break;
        case 'in-progress':
            $text = 'Sedang Dikerjakan';
            $class = 'bg-blue-100 text-blue-800';
            break;
        case 'completed':
            $text = 'Selesai';
            $class = 'bg-green-100 text-green-800';
            break;
        case 'cancelled':
            $text = 'Dibatalkan';
            $class = 'bg-red-100 text-red-800';
            break;
    }
    return ['text' => $text, 'class' => $class];
}

function isWorker(): bool {
    return isLoggedIn() && $_SESSION['user_role'] === 'worker';
}

function redirectIfNotWorker(): void {
    if (!isWorker()) {
        header('Location: index.php');
        exit();
    }
}

// Fungsi untuk mengambil job khusus untuk kuli yang sedang login
function getWorkerJobs(string $workerProfileId): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE workerId = ? ORDER BY createdAt DESC");
    $stmt->execute([$workerProfileId]);
    return $stmt->fetchAll();
}