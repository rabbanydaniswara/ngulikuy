<?php 
declare(strict_types=1);

/**
 * FUNCTIONS.PHP - NguliKuy Core Logic
 * * Urutan Load:
 * 1. db.php (Koneksi Database)
 * 2. security_config.php (Konfigurasi Keamanan)
 * 3. Session Start
 */

require_once 'db.php';
require_once 'security_config.php';

// --- SESSION START ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// 1. AUTHENTICATION & ROLE HELPERS
// ==========================================

function isLoggedIn(): bool {
    return isset($_SESSION['user']) && isset($_SESSION['user_role']);
}

function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

function isCustomer(): bool {
    return isLoggedIn() && $_SESSION['user_role'] === 'customer';
}

function isWorker(): bool {
    return isLoggedIn() && $_SESSION['user_role'] === 'worker';
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

function redirectIfNotWorker(): void {
    if (!isWorker()) {
        header('Location: index.php');
        exit();
    }
}

function logout(): void {
    session_destroy();
    header('Location: index.php');
    exit();
}

function authenticate(string $username, string $password): bool {
    global $pdo;
    
    if (!InputValidator::validateEmail($username) && strlen($username) < 3) {
        SecurityLogger::logLoginAttempt($username, false);
        return false;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            
            $_SESSION['user_id'] = $user['id']; // <-- ADD THIS
            $_SESSION['user'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_phone'] = $user['phone'] ?? '';
            $_SESSION['user_address'] = $user['alamat'] ?? '';
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

// ==========================================
// 2. CSRF SECURITY
// ==========================================

function getCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(string $tokenFromForm): bool {
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $tokenFromForm);
}

function csrfInput(): string {
    return '<input type="hidden" name="csrf_token" value="' . getCsrfToken() . '">';
}

// ==========================================
// 3. FORMATTING & UPLOADS
// ==========================================

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
    $validation = SecureFileUpload::validate($file);
    
    if (!$validation['valid']) {
        return ['success' => false, 'error' => implode(', ', $validation['errors'])];
    }
    
    $uploadDir = 'uploads/workers/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileName = SecureFileUpload::generateSecureFilename($validation['extension']);
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        chmod($filePath, 0644);
        SecurityLogger::log('INFO', 'File uploaded: ' . $fileName);
        return ['success' => true, 'file_path' => $filePath, 'file_name' => $fileName];
    }
    
    return ['success' => false, 'error' => 'Gagal mengupload file.'];
}

function getDefaultWorkerPhoto(): string {
    return 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&h=150&fit=crop&crop=face';
}

// ==========================================
// 4. JOB MANAGEMENT FUNCTIONS
// ==========================================

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

function getJobById(string $jobId): ?array {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT j.*, w.name as worker_full_name, w.photo as worker_photo, w.phone as worker_phone, w.email as worker_email
                               FROM jobs j
                               LEFT JOIN workers w ON j.workerId = w.id
                               WHERE j.jobId = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();
        return $job ?: null;
    } catch (PDOException $e) {
        error_log("Error getting job by ID ($jobId): " . $e->getMessage());
        return null;
    }
}

function verifyCustomerOwnsJob(string $jobId, string $customerEmail): bool {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE jobId = ? AND customerEmail = ?");
        $stmt->execute([$jobId, $customerEmail]);
        return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error verifying job ownership ($jobId, $customerEmail): " . $e->getMessage());
        return false;
    }
}

function getJobs(): array {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM jobs ORDER BY createdAt DESC");
    return $stmt->fetchAll();
}

function getCustomerOrders(string $customerEmail): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE customerEmail = ? ORDER BY createdAt DESC");
    $stmt->execute([$customerEmail]);
    return $stmt->fetchAll();
}

function getWorkerJobs(string $workerProfileId): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE workerId = ? ORDER BY createdAt DESC");
    $stmt->execute([$workerProfileId]);
    return $stmt->fetchAll();
}

function getRecentJobs(int $limit = 5): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM jobs ORDER BY createdAt DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function addJob(array $jobData): bool {
    global $pdo;
    
    DatabaseHelper::beginTransaction();

    try {
        $sql = "INSERT INTO jobs (jobId, posted_job_id, workerId, workerName, jobType, startDate, endDate, customer, customerPhone, customerEmail, price, location, address, description, status, createdAt)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
        $stmt = $pdo->prepare($sql);
        
        $jobId = generateJobId();
        $createdAt = date('Y-m-d H:i:s');
        
        $stmt->execute([
            $jobId,
            $jobData['posted_job_id'] ?? null,
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
        
        if (isset($jobData['workerId']) && ($jobData['status'] === 'in-progress' || $jobData['status'] === 'pending')) {
            updateWorkerStatus((string)$jobData['workerId'], 'Assigned');
        }
        
        DatabaseHelper::commit();
        return true;

    } catch (Exception $e) {
        DatabaseHelper::rollback();
        error_log("Error adding job: " . $e->getMessage());
        return false;
    }
}

function updateJobStatus(string $jobId, string $status): bool {
    global $pdo;
    DatabaseHelper::beginTransaction();
    
    try {
        $stmtJob = $pdo->prepare("SELECT workerId, status FROM jobs WHERE jobId = ? FOR UPDATE");
        $stmtJob->execute([$jobId]);
        $job = $stmtJob->fetch();

        if (!$job) {
            DatabaseHelper::rollback();
            return false;
        }

        $sql = "UPDATE jobs SET status = ?, updatedAt = NOW() WHERE jobId = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $jobId]);
        
        if ($job['workerId'] && in_array($status, ['completed', 'cancelled'])) {
            updateWorkerStatus($job['workerId'], 'Available');
        }
        
        DatabaseHelper::commit();
        return true;

    } catch (Exception $e) {
        DatabaseHelper::rollback();
        error_log("Error updating job status: " . $e->getMessage());
        return false;
    }
}

function deleteJob(string $jobId): bool {
    global $pdo;
    
    DatabaseHelper::beginTransaction();
    
    try {
        $job = null;
        $stmtJob = $pdo->prepare("SELECT workerId, status FROM jobs WHERE jobId = ?");
        $stmtJob->execute([$jobId]);
        $job = $stmtJob->fetch();
        
        if ($job && $job['workerId'] && ($job['status'] === 'in-progress' || $job['status'] === 'pending')) {
            updateWorkerStatus($job['workerId'], 'Available');
        }

        $sql = "DELETE FROM jobs WHERE jobId = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$jobId]);
        
        DatabaseHelper::commit();
        return true;

    } catch (Exception $e) {
        DatabaseHelper::rollback();
        error_log("Error deleting job: " . $e->getMessage());
        return false;
    }
}

// ==========================================
// 5. WORKER MANAGEMENT FUNCTIONS
// ==========================================

function generateWorkerId(): string {
    global $pdo;
    $stmt = $pdo->query("SELECT id FROM workers ORDER BY CAST(SUBSTR(id, 4) AS UNSIGNED) DESC LIMIT 1");
    $lastWorker = $stmt->fetch();
    
    $lastId = 0;
    if ($lastWorker) {
        $lastId = intval(substr($lastWorker['id'], 3));
    }
    return 'KUL' . str_pad((string)($lastId + 1), 3, '0', STR_PAD_LEFT);
}

function getWorkers(): array {
    global $pdo;
    
    $sql = "SELECT w.*, COUNT(r.id) as review_count
            FROM workers w
            LEFT JOIN reviews r ON w.id = r.workerId
            GROUP BY w.id
            ORDER BY w.name ASC";
            
    $stmt = $pdo->query($sql);
    $workers = $stmt->fetchAll();
    
    foreach ($workers as &$worker) {
        $worker['skills'] = json_decode((string)$worker['skills'], true) ?: [];
    }
    return $workers;
}

function getAvailableWorkers(): array {
    global $pdo;
    
    $sql = "SELECT w.*, COUNT(r.id) as review_count
            FROM workers w
            LEFT JOIN reviews r ON w.id = r.workerId
            WHERE w.status = 'Available'
            GROUP BY w.id
            ORDER BY w.name ASC";
            
    $stmt = $pdo->query($sql);
    $workers = $stmt->fetchAll();
    
    foreach ($workers as &$worker) {
        $worker['skills'] = json_decode((string)$worker['skills'], true) ?: [];
    }
    return $workers;
}

function getTopRatedWorkers(int $limit = 4): array {
    global $pdo;

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

function searchWorkers(array $criteria = []): array {
    global $pdo;
    
    $sql = "SELECT w.*, COUNT(r.id) as review_count
            FROM workers w
            LEFT JOIN reviews r ON w.id = r.workerId
            WHERE w.status = 'Available'";
    $params = [];
    
    if (!empty($criteria['skill'])) {
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
        $sql .= " AND w.rating >= ?";
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

function addWorker(array $workerData): bool {
    global $pdo;
    
    $pdo->beginTransaction();

    try {
        $id = generateWorkerId();
        
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
        
        $defaultPassword = 'ngulikuy123';
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
        
        $sqlUser = "INSERT INTO users (username, password, role, name, phone, worker_profile_id)
                    VALUES (?, ?, ?, ?, ?, ?)";
        $stmtUser = $pdo->prepare($sqlUser);
        $stmtUser->execute([
            $workerData['email'] ?? '',
            $hashedPassword,
            'worker',
            $workerData['name'] ?? '',
            $workerData['phone'] ?? '',
            $id
        ]);
        
        $pdo->commit();
        return true;

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
            throw new Exception("Gagal menambah kuli: Email '" . $workerData['email'] . "' sudah digunakan untuk akun lain.");
        }
        return false;
    }
}

function updateWorker(string $workerId, array $updatedData): bool {
    global $pdo;
    
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

function updateWorkerStatus(string $workerId, string $status): bool {
    global $pdo;
    try {
        $sql = "UPDATE workers SET status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $workerId]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        throw $e;
    }
}

function deleteWorker(string $workerId): bool {
    global $pdo;
    
    $pdo->beginTransaction();

    try {
        $sql = "DELETE FROM workers WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$workerId]);
        
        $sqlUser = "DELETE FROM users WHERE worker_profile_id = ?";
        $pdo->prepare($sqlUser)->execute([$workerId]);
        
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

// ==========================================
// 6. REVIEW & VALIDATION FUNCTIONS
// ==========================================

/**
 * Cek apakah customer sudah memberikan review untuk job tertentu
 */
function hasCustomerReviewedJob(string $jobId, int $customerId): bool {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE jobId = ? AND customerId = ?");
        $stmt->execute([$jobId, $customerId]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        SecurityLogger::logError('Error checking review status: ' . $e->getMessage());
        return true; // Return true untuk mencegah submit jika ada error
    }
}

/**
 * Cek apakah job bisa di-review (harus completed dan milik customer)
 */
function canReviewJob(string $jobId, string $customerEmail): bool {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT status, customerEmail FROM jobs WHERE jobId = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();
        
        if (!$job) {
            return false;
        }
        
        return $job['status'] === 'completed' && $job['customerEmail'] === $customerEmail;
    } catch (PDOException $e) {
        SecurityLogger::logError('Error checking job review permission: ' . $e->getMessage());
        return false;
    }
}

function getAllReviews(): array {
    global $pdo;
    try {
        $sql = "SELECT 
                    r.id as review_id, 
                    r.rating, 
                    r.comment, 
                    r.createdAt as review_date,
                    j.jobId, 
                    j.jobType,
                    u.name as customer_name, 
                    u.username as customer_email,
                    w.id as worker_id, 
                    w.name as worker_name
                FROM reviews r
                LEFT JOIN users u ON r.customerId = u.id
                LEFT JOIN workers w ON r.workerId = w.id
                LEFT JOIN jobs j ON r.jobId = j.jobId
                ORDER BY r.createdAt DESC";
                
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();

    } catch (PDOException $e) {
        error_log("Error getting all reviews: " . $e->getMessage());
        return [];
    }
}

function deleteReview(int $reviewId): bool {
    global $pdo;
    
    $pdo->beginTransaction();

    try {
        $stmtGetWorker = $pdo->prepare("SELECT workerId FROM reviews WHERE id = ?");
        $stmtGetWorker->execute([$reviewId]);
        $reviewData = $stmtGetWorker->fetch();
        
        if (!$reviewData) {
            $pdo->rollBack();
            return false;
        }
        $workerId = $reviewData['workerId'];

        $sql = "DELETE FROM reviews WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $deleted = $stmt->execute([$reviewId]);

        if ($deleted && $workerId) {
            $sqlAvg = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE workerId = ?");
            $sqlAvg->execute([$workerId]);
            
            $newRating = $sqlAvg->fetch()['avg_rating'] ?? 4.0;

            $sqlUpdateWorker = $pdo->prepare("UPDATE workers SET rating = ? WHERE id = ?");
            $sqlUpdateWorker->execute([$newRating, $workerId]);
        }
        
        $pdo->commit();
        return $deleted;

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting review ($reviewId): " . $e->getMessage());
        return false;
    }
}

// ==========================================
// 7. DASHBOARD & UTILITIES
// ==========================================

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

function getStatusClass(string $status, string $type = 'job'): string {
    if ($type === 'job') {
        $classes = [
            'completed' => 'status-completed',
            'in-progress' => 'status-in-progress',
            'pending' => 'status-pending',
            'cancelled' => 'status-cancelled'
        ];
        return $classes[$status] ?? 'status-pending';
    } else {
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
    $class = 'bg-gray-200 text-gray-800';

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

function exportData(): void {
    global $pdo;
    
    $workersStmt = $pdo->query("SELECT * FROM workers");
    $workers = $workersStmt->fetchAll();
    
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