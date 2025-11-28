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
SecureSession::start();

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
        header('Location: login.php');
        exit();
    }
}

function redirectIfNotAdmin(): void {
    if (!isAdmin()) {
        header('Location: login.php');
        exit();
    }
}

function redirectIfNotCustomer(): void {
    if (!isCustomer()) {
        header('Location: login.php');
        exit();
    }
}

function redirectIfNotWorker(): void {
    if (!isWorker()) {
        header('Location: login.php');
        exit();
    }
}

function logout(): void {
    session_destroy();
    header('Location: login.php');
    exit();
}

function authenticate(string $username, string $password): bool {
    global $pdo;
    
    if (!InputValidator::validateEmail($username) && strlen($username) < 3) {
        SecurityLogger::logLoginAttempt($username, false);
        return false;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE nama_pengguna = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['kata_sandi'])) {
            
            $_SESSION['user_id'] = $user['id_pengguna'];
            $_SESSION['user'] = $user['nama_pengguna'];
            $_SESSION['user_role'] = $user['peran'];
            $_SESSION['user_name'] = $user['nama_lengkap'];
            $_SESSION['user_phone'] = $user['telepon'] ?? '';
            $_SESSION['user_address'] = $user['alamat_lengkap'] ?? '';
            $_SESSION['user_photo'] = $user['url_foto'] ?? ''; // Set user photo URL
            $_SESSION['worker_profile_id'] = $user['id_profil_pekerja'] ?? null;
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

function getCustomerDataById(int $customer_id): ?array {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id_pengguna, nama_pengguna, nama_lengkap, telepon, alamat_lengkap FROM pengguna WHERE id_pengguna = ?");
        $stmt->execute([$customer_id]);
        $customerData = $stmt->fetch();
        return $customerData ?: null;
    } catch (PDOException $e) {
        error_log("Error fetching customer data by ID ($customer_id): " . $e->getMessage());
        return null;
    }
}

function getAdminById(int $adminId): ?array {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id_pengguna, nama_pengguna, kata_sandi, nama_lengkap, url_foto FROM pengguna WHERE id_pengguna = ? AND peran = 'admin'");
        $stmt->execute([$adminId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        error_log("Error fetching admin data by ID ($adminId): " . $e->getMessage());
        return null;
    }
}

function updateAdminById(int $adminId, array $data): bool {
    global $pdo;
    
    $setParts = [];
    $params = ['id_pengguna' => $adminId];

    // Map old keys to new schema keys
    $keyMap = [
        'name' => 'nama_lengkap',
        'email' => 'nama_pengguna',
        'avatar' => 'url_foto',
        'password_hash' => 'kata_sandi'
    ];

    foreach ($data as $key => $value) {
        if (isset($keyMap[$key])) {
            $dbKey = $keyMap[$key];
            $setParts[] = "`{$dbKey}` = :{$dbKey}";
            $params[":{$dbKey}"] = $value;
        }
    }

    if (empty($setParts)) {
        return false; // Nothing to update
    }

    $sql = "UPDATE pengguna SET " . implode(', ', $setParts) . " WHERE id_pengguna = :id_pengguna AND peran = 'admin'";

    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Error updating admin (ID: {$adminId}): " . $e->getMessage());
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

function get_construction_skills(): array {
    return [
        'Tukang Bangunan',
        'Tukang Keramik / Lantai',
        'Tukang Cat / Finishing',
        'Tukang Kayu',
        'Tukang Listrik',
        'Tukang Pipa / Plumbing',
        'Tukang Atap / Baja Ringan',
        'Tukang Las / Besi',
        'Tukang Gypsum / Plafon',
        'Kebersihan Pasca Renovasi'
    ];
}

// ==========================================
// 4. JOB MANAGEMENT FUNCTIONS
// ==========================================

function generateJobId(): string {
    global $pdo;
    $stmt = $pdo->query("SELECT id_pekerjaan FROM pekerjaan ORDER BY CAST(SUBSTR(id_pekerjaan, 4) AS UNSIGNED) DESC LIMIT 1");
    $lastJob = $stmt->fetch();
    
    $lastId = 0;
    if ($lastJob) {
        $lastId = intval(substr($lastJob['id_pekerjaan'], 3));
    }
    return 'JOB' . str_pad((string)($lastId + 1), 3, '0', STR_PAD_LEFT);
}

function getJobById(string $jobId): ?array {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT j.*, w.nama as worker_full_name, w.url_foto as worker_photo, w.telepon as worker_phone, w.email as worker_email
                               FROM pekerjaan j
                               LEFT JOIN pekerja w ON j.id_pekerja = w.id_pekerja
                               WHERE j.id_pekerjaan = ?");
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
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pekerjaan WHERE id_pekerjaan = ? AND email_pelanggan = ?");
        $stmt->execute([$jobId, $customerEmail]);
        return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error verifying job ownership ($jobId, $customerEmail): " . $e->getMessage());
        return false;
    }
}

function getJobs(): array {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM pekerjaan ORDER BY dibuat_pada DESC");
    return $stmt->fetchAll();
}

function getCustomerOrders(string $customerEmail): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM pekerjaan WHERE email_pelanggan = ? ORDER BY dibuat_pada DESC");
    $stmt->execute([$customerEmail]);
    return $stmt->fetchAll();
}

function getWorkerJobs(string $workerProfileId): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM pekerjaan WHERE id_pekerja = ? ORDER BY dibuat_pada DESC");
    $stmt->execute([$workerProfileId]);
    return $stmt->fetchAll();
}

function getRecentWorkers(int $limit = 5): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM pekerja ORDER BY tanggal_bergabung DESC LIMIT ?");
    $stmt->execute([$limit]);
    $workers = $stmt->fetchAll();
    foreach ($workers as &$worker) {
        $worker['keahlian'] = json_decode((string)$worker['keahlian'], true) ?: [];
    }
    return $workers;
}

function addWorkerToJob(array $jobData): bool {
    global $pdo;
    
    DatabaseHelper::beginTransaction();

    try {
        $sql = "INSERT INTO pekerjaan (id_pekerjaan, id_lowongan_diposting, id_pekerja, nama_pekerja, jenis_pekerjaan, tanggal_mulai, tanggal_selesai, nama_pelanggan, telepon_pelanggan, email_pelanggan, harga, lokasi, alamat_lokasi, deskripsi, status_pekerjaan, dibuat_pada)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
        $stmt = $pdo->prepare($sql);
        
        $jobId = generateJobId();
        $createdAt = date('Y-m-d H:i:s');
        
        $stmt->execute([
            $jobId,
            $jobData['id_lowongan_diposting'] ?? null,
            $jobData['id_pekerja'] ?? null,
            $jobData['nama_pekerja'] ?? 'Unknown',
            $jobData['jenis_pekerjaan'] ?? '',
            $jobData['tanggal_mulai'] ?? null,
            $jobData['tanggal_selesai'] ?? null,
            $jobData['nama_pelanggan'] ?? '',
            $jobData['telepon_pelanggan'] ?? '',
            $jobData['email_pelanggan'] ?? '',
            $jobData['harga'] ?? 0,
            $jobData['lokasi'] ?? '',
            $jobData['alamat_lokasi'] ?? '',
            $jobData['deskripsi'] ?? '',
            $jobData['status_pekerjaan'] ?? 'pending',
            $createdAt
        ]);
        
        if (isset($jobData['id_pekerja']) && ($jobData['status_pekerjaan'] === 'in-progress' || $jobData['status_pekerjaan'] === 'pending')) {
            updateWorkerStatus((string)$jobData['id_pekerja'], 'Assigned');
        }
        
        DatabaseHelper::commit();
        return true;

    } catch (Exception $e) {
        DatabaseHelper::rollback();
        error_log("Error adding worker to job: " . $e->getMessage());
        return false;
    }
}

function updateJobStatus(string $jobId, string $status): bool {
    global $pdo;
    DatabaseHelper::beginTransaction();
    
    try {
        $stmtJob = $pdo->prepare("SELECT id_pekerja, status_pekerjaan FROM pekerjaan WHERE id_pekerjaan = ? FOR UPDATE");
        $stmtJob->execute([$jobId]);
        $job = $stmtJob->fetch();

        if (!$job) {
            DatabaseHelper::rollback();
            return false;
        }

        $sql = "UPDATE pekerjaan SET status_pekerjaan = ?, diperbarui_pada = NOW() WHERE id_pekerjaan = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $jobId]);
        
        if ($job['id_pekerja'] && in_array($status, ['completed', 'cancelled'])) {
            updateWorkerStatus($job['id_pekerja'], 'Available');
        }
        
        DatabaseHelper::commit();
        return true;

    } catch (Exception $e) {
        DatabaseHelper::rollback();
        error_log("Error updating job status: " . $e->getMessage());
        return false;
    }
}

function deleteWorkerFromJob(string $jobId): bool {
    global $pdo;
    
    DatabaseHelper::beginTransaction();
    
    try {
        $job = null;
        $stmtJob = $pdo->prepare("SELECT id_pekerja, status_pekerjaan FROM pekerjaan WHERE id_pekerjaan = ?");
        $stmtJob->execute([$jobId]);
        $job = $stmtJob->fetch();
        
        if ($job && $job['id_pekerja'] && ($job['status_pekerjaan'] === 'in-progress' || $job['status_pekerjaan'] === 'pending')) {
            updateWorkerStatus($job['id_pekerja'], 'Available');
        }

        $sql = "UPDATE pekerjaan SET id_pekerja = NULL, nama_pekerja = 'Unassigned' WHERE id_pekerjaan = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$jobId]);
        
        DatabaseHelper::commit();
        return true;

    } catch (Exception $e) {
        DatabaseHelper::rollback();
        error_log("Error deleting worker from job: " . $e->getMessage());
        return false;
    }
}

// ==========================================
// 5. WORKER MANAGEMENT FUNCTIONS
// ==========================================

function generateWorkerId(): string {
    global $pdo;
    $stmt = $pdo->query("SELECT id_pekerja FROM pekerja ORDER BY CAST(SUBSTR(id_pekerja, 4) AS UNSIGNED) DESC LIMIT 1");
    $lastWorker = $stmt->fetch();
    
    $lastId = 0;
    if ($lastWorker) {
        $lastId = intval(substr($lastWorker['id_pekerja'], 3));
    }
    return 'TUK' . str_pad((string)($lastId + 1), 3, '0', STR_PAD_LEFT);
}

function getWorkers(): array {
    global $pdo;
    
    $sql = "SELECT w.*, COUNT(r.id_ulasan) as review_count
            FROM pekerja w
            LEFT JOIN ulasan r ON w.id_pekerja = r.id_pekerja
            GROUP BY w.id_pekerja
            ORDER BY w.nama ASC";
            
    $stmt = $pdo->query($sql);
    $workers = $stmt->fetchAll();
    
    foreach ($workers as &$worker) {
        $worker['keahlian'] = json_decode((string)$worker['keahlian'], true) ?: [];
    }
    return $workers;
}

function getAvailableWorkers(): array {
    global $pdo;
    
    $sql = "SELECT w.*, COUNT(r.id_ulasan) as review_count
            FROM pekerja w
            LEFT JOIN ulasan r ON w.id_pekerja = r.id_pekerja
            WHERE w.status_ketersediaan = 'Available'
            GROUP BY w.id_pekerja
            ORDER BY w.nama ASC";
            
    $stmt = $pdo->prepare($sql);
    $workers = $stmt->fetchAll();
    
    foreach ($workers as &$worker) {
        $worker['keahlian'] = json_decode((string)$worker['keahlian'], true) ?: [];
    }
    return $workers;
}

function getTopRatedWorkers(int $limit = 4): array {
    global $pdo;

    $sql = "SELECT w.*, COUNT(r.id_ulasan) as review_count
            FROM pekerja w
            LEFT JOIN ulasan r ON w.id_pekerja = r.id_pekerja
            WHERE w.status_ketersediaan = 'Available'
            GROUP BY w.id_pekerja
            ORDER BY w.rating DESC, review_count DESC
            LIMIT ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$limit]);
    $workers = $stmt->fetchAll();
    
    foreach ($workers as &$worker) {
        $worker['keahlian'] = json_decode((string)$worker['keahlian'], true) ?: [];
    }
    return $workers;
}

function getWorkerById(string $workerId): ?array {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM pekerja WHERE id_pekerja = ?");
        $stmt->execute([$workerId]);
        $worker = $stmt->fetch();
        
        if ($worker) {
            $worker['keahlian'] = json_decode((string)$worker['keahlian'], true) ?: [];
        }
        return $worker ?: null;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return null;
    }
}

function searchWorkers(array $criteria = []): array {
    global $pdo;
    
    $sql = "SELECT w.*, COUNT(r.id_ulasan) as review_count
            FROM pekerja w
            LEFT JOIN ulasan r ON w.id_pekerja = r.id_pekerja
            WHERE w.status_ketersediaan = 'Available'";
    $params = [];
    
    if (!empty($criteria['skill'])) {
        $sql .= " AND JSON_CONTAINS(keahlian, ?)";
        $params[] = '"' . $criteria['skill'] . '"';
    }
    
    if (!empty($criteria['location'])) {
        $sql .= " AND lokasi LIKE ?";
        $params[] = '%' . $criteria['location'] . '%';
    }
    
    if (!empty($criteria['min_price'])) {
        $sql .= " AND tarif_per_jam >= ?";
        $params[] = intval($criteria['min_price']);
    }
    
    if (!empty($criteria['max_price'])) {
        $sql .= " AND tarif_per_jam <= ?";
        $params[] = intval($criteria['max_price']);
    }
    
    if (!empty($criteria['min_rating'])) {
        $sql .= " AND w.rating >= ?";
        $params[] = floatval($criteria['min_rating']);
    }
    
    $sql .= " GROUP BY w.id_pekerja";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $workers = $stmt->fetchAll();
        
        foreach ($workers as &$worker) {
            $worker['keahlian'] = json_decode((string)$worker['keahlian'], true) ?: [];
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
        
        $sqlWorker = "INSERT INTO pekerja (id_pekerja, nama, email, telepon, lokasi, keahlian, status_ketersediaan, tarif_per_jam, pengalaman, deskripsi_diri, url_foto, rating, jumlah_pekerjaan_selesai, tanggal_bergabung)
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
        
        $sqlUser = "INSERT INTO pengguna (nama_pengguna, kata_sandi, peran, nama_lengkap, telepon, id_profil_pekerja)
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
            throw new Exception("Gagal menambah pekerja: Email '" . $workerData['email'] . "' sudah digunakan untuk akun lain.");
        }
        return false;
    }
}

function updateWorker(string $workerId, array $updatedData): bool {
    global $pdo;
    
    DatabaseHelper::beginTransaction(); // Start transaction

    try {
        $params = [
            'nama' => $updatedData['name'] ?? '',
            'email' => $updatedData['email'] ?? '',
            'telepon' => $updatedData['phone'] ?? '',
            'lokasi' => $updatedData['location'] ?? '',
            'keahlian' => json_encode($updatedData['skills'] ?? []),
            'status_ketersediaan' => $updatedData['status'] ?? 'Available',
            'tarif_per_jam' => $updatedData['rate'] ?? 0,
            'pengalaman' => $updatedData['experience'] ?? '',
            'deskripsi_diri' => $updatedData['description'] ?? '',
            'id_pekerja' => $workerId
        ];
        
        $setParts = [];
        foreach ($params as $key => $value) {
            if ($key !== 'id_pekerja') { // id_pekerja is in WHERE clause
                $setParts[] = "`{$key}` = :{$key}";
            }
        }

        if (!empty($updatedData['photo'])) {
            $setParts[] = "`url_foto` = :url_foto";
            $params['url_foto'] = $updatedData['photo'];
        }
        
        $sql = "UPDATE pekerja SET " . implode(', ', $setParts) . " WHERE id_pekerja = :id_pekerja";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Also update the 'pengguna' table if the worker's name or email changed
        if (isset($updatedData['name']) || isset($updatedData['email'])) {
            $updateUserSql = "UPDATE pengguna SET ";
            $userUpdateParams = [];
            
            if (isset($updatedData['name'])) {
                $updateUserSql .= "nama_lengkap = :nama_lengkap_user, ";
                $userUpdateParams['nama_lengkap_user'] = $updatedData['name'];
            }
            if (isset($updatedData['email'])) { 
                $updateUserSql .= "nama_pengguna = :nama_pengguna_user, ";
                $userUpdateParams['nama_pengguna_user'] = $updatedData['email'];
            }
            
            // Remove trailing comma and space
            $updateUserSql = rtrim($updateUserSql, ', ');
            $updateUserSql .= " WHERE id_profil_pekerja = :id_profil_pekerja";
            $userUpdateParams['id_profil_pekerja'] = $workerId;

            $stmtUser = $pdo->prepare($updateUserSql);
            $stmtUser->execute($userUpdateParams);
        }

        // Also update nama_pekerja in the 'pekerjaan' table for consistency
        if (isset($updatedData['name'])) {
            $updateJobsSql = "UPDATE pekerjaan SET nama_pekerja = :new_nama_pekerja WHERE id_pekerja = :id_pekerja_job";
            $stmtJobs = $pdo->prepare($updateJobsSql);
            $stmtJobs->execute([
                'new_nama_pekerja' => $updatedData['name'],
                'id_pekerja_job' => $workerId
            ]);
        }

        DatabaseHelper::commit(); // Commit transaction
        return true;
    } catch (PDOException $e) {
        DatabaseHelper::rollback(); // Rollback on error
        error_log("Error in updateWorker: " . $e->getMessage()); // Keep logging for server-side debug
        throw $e; // Re-throw the exception
    }
}

function updateWorkerStatus(string $workerId, string $status): bool {
    global $pdo;
    try {
        $sql = "UPDATE pekerja SET status_ketersediaan = ? WHERE id_pekerja = ?";
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
        $sql = "DELETE FROM pekerja WHERE id_pekerja = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$workerId]);
        
        $sqlUser = "DELETE FROM pengguna WHERE id_profil_pekerja = ?";
        $pdo->prepare($sqlUser)->execute([$workerId]);
        
        $sqlJobs = "UPDATE pekerjaan SET id_pekerja = NULL, nama_pekerja = 'Deleted Worker' WHERE id_pekerja = ?";
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
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ulasan WHERE id_pekerjaan = ? AND id_pelanggan = ?");
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
        $stmt = $pdo->prepare("SELECT status_pekerjaan, email_pelanggan FROM pekerjaan WHERE id_pekerjaan = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();
        
        if (!$job) {
            return false;
        }
        
        return $job['status_pekerjaan'] === 'completed' && $job['email_pelanggan'] === $customerEmail;
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
                    u.nama_lengkap as customer_name, 
                    u.nama_pengguna as customer_email,
                    w.id as worker_id, 
                    w.name as worker_name
                FROM ulasan r
                LEFT JOIN pengguna u ON r.id_pelanggan = u.id_pengguna
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
        $stmtGetWorker = $pdo->prepare("SELECT id_pekerja FROM ulasan WHERE id_ulasan = ?");
        $stmtGetWorker->execute([$reviewId]);
        $reviewData = $stmtGetWorker->fetch();
        
        if (!$reviewData) {
            $pdo->rollBack();
            return false;
        }
        $workerId = $reviewData['id_pekerja'];

        $sql = "DELETE FROM ulasan WHERE id_ulasan = ?";
        $stmt = $pdo->prepare($sql);
        $deleted = $stmt->execute([$reviewId]);

        if ($deleted && $workerId) {
            $sqlAvg = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM ulasan WHERE id_pekerja = ?");
            $sqlAvg->execute([$workerId]);
            
            $newRating = $sqlAvg->fetch()['avg_rating'] ?? 4.0;

            $sqlUpdateWorker = $pdo->prepare("UPDATE pekerja SET rating = ? WHERE id_pekerja = ?");
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
        
        $stats['total_workers'] = $pdo->query("SELECT COUNT(*) FROM pekerja")->fetchColumn();
        $stats['available_workers'] = $pdo->query("SELECT COUNT(*) FROM pekerja WHERE status_ketersediaan = 'Available'")->fetchColumn();
        $stats['on_job_workers'] = $pdo->query("SELECT COUNT(*) FROM pekerja WHERE status_ketersediaan = 'Assigned'")->fetchColumn();
        $stats['active_jobs'] = $pdo->query("SELECT COUNT(*) FROM pekerjaan WHERE status_pekerjaan IN ('in-progress', 'pending')")->fetchColumn();
        $stats['completed_jobs'] = $pdo->query("SELECT COUNT(*) FROM pekerjaan WHERE status_pekerjaan = 'completed'")->fetchColumn();
        $stats['pending_jobs'] = $pdo->query("SELECT COUNT(*) FROM pekerjaan WHERE status_pekerjaan = 'pending'")->fetchColumn();
        
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