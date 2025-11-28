<?php
ob_start(); // Start output buffering
// ajax_handler.php
require_once 'functions.php'; // functions.php sudah include db.php dan session_start()

// Set header ke JSON (kecuali jika kita mengharapkan output lain nanti)
// Kita pindahkan header('Content-Type: application/json'); ke dalam blok if/case
// karena add/edit worker mungkin mengembalikan pesan error non-JSON saat upload gagal

// Keamanan: Pastikan hanya pengguna yang login yang bisa akses (admin, worker, atau customer)
if (!isAdmin() && !isWorker() && !isCustomer()) { // <-- FIX: Izinkan customer
    header('Content-Type: application/json');
    ob_clean(); // Discard any buffered output before sending JSON
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Anda harus login.']);
    exit;
}

// --- VALIDASI CSRF UNTUK AJAX ---
// Untuk AJAX, kita terima token dari $_POST karena kita akan pakai FormData
if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
    header('Content-Type: application/json'); // Set header JSON untuk pesan error
    ob_clean(); // Discard any buffered output before sending JSON
    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid atau sesi kedaluwarsa.']);
    exit;
}
// --- AKHIR VALIDASI CSRF ---

// Baca aksi dari $_POST (karena pakai FormData)
$action = $_POST['action'] ?? null;

// Default response
$response = ['success' => false, 'message' => 'Aksi tidak dikenal atau data tidak lengkap.'];

// Pastikan aksi ada
if (!$action) {
    header('Content-Type: application/json');
    ob_clean(); // Discard any buffered output before sending JSON
    echo json_encode($response);
    exit;
}

// --- Handle Aksi ---
header('Content-Type: application/json'); // Set header JSON default untuk semua aksi
try {

if ($action === 'update_job_status') {
    // Logika ini sekarang perlu membaca dari $_POST, bukan $input JSON
    if (!isAdmin()) {
        $response['message'] = 'Aksi ini hanya untuk admin.';
        ob_clean(); // Discard any buffered output before sending JSON
        echo json_encode($response);
        exit;
    }
    $jobId = $_POST['job_id'] ?? null;
    $status = $_POST['status'] ?? null;

    $validStatuses = ['pending', 'in-progress', 'completed', 'cancelled'];
    if (!$jobId || !$status || !in_array($status, $validStatuses)) {
        $response['message'] = 'Data Job ID atau Status tidak valid.';
    } else {
        if (updateJobStatus($jobId, $status)) {
            $response['success'] = true;
            $response['message'] = "Status Job #{$jobId} berhasil diupdate menjadi '{$status}'.";
            if (function_exists('getStatusClass')) {
                 $response['newClass'] = getStatusClass($status, 'job');
            } else {
                 $response['newClass'] = 'status-pending'; // fallback
            }
        } else {
            $response['message'] = "Gagal mengupdate status Job #{$jobId}.";
        }
    }
} elseif ($action === 'add_worker') {
    // Ambil data dari $_POST
    $workerData = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'location' => $_POST['location'] ?? '',
        'skills' => $_POST['skills'] ?? [], // Skills dikirim sebagai array
        'status' => $_POST['status'] ?? 'Available',
        'rate' => intval($_POST['rate'] ?? 0),
        'experience' => $_POST['experience'] ?? '',
        'description' => $_POST['description'] ?? '',
        'rating' => 4.0 // Default rating
    ];
    
    // Validasi dasar (bisa ditambahkan lebih detail)
    if (empty($workerData['name']) || empty($workerData['email']) || empty($workerData['phone'])) {
         $response['message'] = 'Nama, Email, dan Telepon wajib diisi.';
         ob_clean(); // Discard any buffered output before sending JSON
         echo json_encode($response);
         exit;
    }

    $photoUrl = getDefaultWorkerPhoto();

    // Handle file upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handlePhotoUpload($_FILES['photo']);
        if ($uploadResult['success']) {
            $photoUrl = $uploadResult['file_path'];
        } else {
            // Jika upload gagal, kirim pesan error spesifik
            $response['message'] = 'Gagal upload foto: ' . $uploadResult['error'];
            ob_clean(); // Discard any buffered output before sending JSON
            echo json_encode($response);
            exit; // Hentikan proses
        }
    } elseif (!empty($_POST['photo_url'])) { // Handle URL jika tidak ada file upload
        $photoUrl = filter_var($_POST['photo_url'], FILTER_VALIDATE_URL) ? $_POST['photo_url'] : getDefaultWorkerPhoto();
    }
    
    $workerData['photo'] = $photoUrl;

    // Panggil fungsi addWorker
    if (addWorker($workerData)) {
        $response['success'] = true;
        $response['message'] = 'Worker berhasil ditambahkan!';
        // Kirim ID worker baru jika diperlukan oleh frontend (opsional)
        // $response['new_worker_id'] = generateWorkerId(); // Perlu modif generateWorkerId agar return ID yg baru dibuat
    } else {
        $response['message'] = 'Gagal menambah worker ke database!';
    }

} elseif ($action === 'edit_worker') {
    $workerId = $_POST['worker_id'] ?? null;

    if (!$workerId) {
        $response['message'] = 'Worker ID tidak valid.';
        ob_clean(); // Discard any buffered output before sending JSON
        echo json_encode($response);
        exit;
    }

    // Ambil data dari $_POST untuk update
    $updatedData = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'location' => $_POST['location'] ?? '',
        'skills' => $_POST['skills'] ?? [],
        'status' => $_POST['status'] ?? 'Available',
        'rate' => intval($_POST['rate'] ?? 0),
        'experience' => $_POST['experience'] ?? '',
        'description' => $_POST['description'] ?? ''
        // Rating tidak diupdate dari sini, biarkan dihitung otomatis
    ];
    
    // Validasi dasar
    if (empty($updatedData['name']) || empty($updatedData['email']) || empty($updatedData['phone'])) {
         $response['message'] = 'Nama, Email, dan Telepon wajib diisi.';
         ob_clean(); // Discard any buffered output before sending JSON
         echo json_encode($response);
         exit;
    }

    // Handle file upload (jika ada file baru)
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handlePhotoUpload($_FILES['photo']);
        if ($uploadResult['success']) {
            $updatedData['photo'] = $uploadResult['file_path'];
            // Opsional: Hapus foto lama jika perlu
            // $oldWorker = getWorkerById($workerId);
            // if ($oldWorker && $oldWorker['photo'] && file_exists($oldWorker['photo']) && $oldWorker['photo'] != getDefaultWorkerPhoto()) {
            //     unlink($oldWorker['photo']);
            // }
        } else {
            $response['message'] = 'Gagal upload foto baru: ' . $uploadResult['error'];
            ob_clean(); // Discard any buffered output before sending JSON
            echo json_encode($response);
            exit;
        }
    } elseif (!empty($_POST['photo_url'])) { // Handle URL jika tidak ada file upload
         $updatedData['photo'] = filter_var($_POST['photo_url'], FILTER_VALIDATE_URL) ? $_POST['photo_url'] : null;
         // Jika URL baru diberikan, kita set kolom foto. Jika tidak, fungsi updateWorker tidak akan mengubah foto.
    }
    // Jika tidak ada file baru atau URL baru, 'photo' tidak akan ada di $updatedData,
    // dan fungsi updateWorker tidak akan mengubah foto yang sudah ada.

    // Panggil fungsi updateWorker
    if (updateWorker($workerId, $updatedData)) {
        $response['success'] = true;
        $response['message'] = 'Data worker berhasil diupdate!';
        // Kirim data worker yg terupdate jika perlu (opsional)
        // $response['updated_worker'] = getWorkerById($workerId);
    } else {
        $response['message'] = 'Gagal mengupdate data worker di database!';
    }
} elseif ($action === 'save_worker_profile') {
    if (!isWorker()) {
        $response['message'] = 'Aksi ini hanya untuk pekerja.';
        ob_clean(); // Discard any buffered output before sending JSON
        echo json_encode($response);
        exit;
    }

    $workerId = $_SESSION['worker_profile_id'];
    $updatedData = [
        'name' => InputValidator::sanitizeString($_POST['name'] ?? ''),
        'email' => InputValidator::sanitizeString($_POST['email'] ?? ''),
        'phone' => InputValidator::sanitizeString($_POST['phone'] ?? ''),
        'location' => InputValidator::sanitizeString($_POST['location'] ?? ''),
        'skills' => $_POST['skills'] ?? [],
        'description' => InputValidator::sanitizeString($_POST['description'] ?? ''),
        'experience' => InputValidator::sanitizeString($_POST['experience'] ?? ''),
        'rate' => InputValidator::validateIntRange($_POST['rate'] ?? 0)
    ];

    // Handle file upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handlePhotoUpload($_FILES['photo']);
        if ($uploadResult['success']) {
            $updatedData['photo'] = $uploadResult['file_path'];
        } else {
            $response['message'] = 'Gagal upload foto: ' . $uploadResult['error'];
            echo json_encode($response);
            exit;
        }
    } elseif (!empty($_POST['photo_url'])) {
        $updatedData['photo'] = filter_var($_POST['photo_url'], FILTER_VALIDATE_URL) ?: null;
    }

    try {
        if (updateWorker($workerId, $updatedData)) {
            // also update password if provided
            if (!empty($_POST['new_password'])) {
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];

                $user_stmt = $pdo->prepare("SELECT kata_sandi FROM pengguna WHERE id_pengguna = ?");
                $user_stmt->execute([$_SESSION['user_id']]);
                $user = $user_stmt->fetch();

                if ($user && password_verify($current_password, $user['kata_sandi'])) {
                    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_pass_stmt = $pdo->prepare("UPDATE pengguna SET kata_sandi = ? WHERE id_pengguna = ?");
                    $update_pass_stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                    $response['success'] = true;
                    $response['message'] = 'Profil dan password berhasil diperbarui!';
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Password saat ini salah.';
                }
            } else {
                $response['success'] = true;
                $response['message'] = 'Profil berhasil diperbarui!';
            }
        } else {
            // This 'else' path is for cases where updateWorker returns false
            // without throwing an exception, which shouldn't happen after re-throwing.
            // But it's good to keep it as a fallback if updateWorker is ever refactored
            // to return false for non-PDO errors.
            $response['message'] = 'Gagal memperbarui profil (non-exception).';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database Error: ' . $e->getMessage();
        SecurityLogger::logError('Error updating worker profile: ' . $e->getMessage());
    } catch (Exception $e) { // Catch any other unexpected exceptions
        $response['message'] = 'Error: ' . $e->getMessage();
        SecurityLogger::logError('Unexpected error updating worker profile: ' . $e->getMessage());
    }
} elseif ($action === 'add_job') {
    // Logika ini diambil dari admin_dashboard.php
    $workerId = $_POST['worker_id'] ?? '';
    $worker = getWorkerById($workerId);
    
    $jobData = [
        'id_pekerja' => $workerId,
        'nama_pekerja' => $worker ? $worker['nama'] : 'Unknown Worker',
        'jenis_pekerjaan' => $_POST['job_type'] ?? '',
        'tanggal_mulai' => $_POST['start_date'] ?? '',
        'tanggal_selesai' => $_POST['end_date'] ?? '',
        'nama_pelanggan' => $_POST['customer'] ?? '',
        'telepon_pelanggan' => $_POST['customer_phone'] ?? '',
        'email_pelanggan' => $_POST['customer_email'] ?? '',
        'harga' => intval($_POST['price'] ?? 0),
        'lokasi' => $_POST['location'] ?? '',
        'alamat_lokasi' => $_POST['address'] ?? '',
        'deskripsi' => $_POST['description'] ?? '',
        'status_pekerjaan' => $_POST['status'] ?? 'pending'
    ];
    
    if (addWorkerToJob($jobData)) {
        $response['success'] = true;
        $response['message'] = 'Pekerjaan berhasil ditambahkan!';
    } else {
        $response['message'] = 'Gagal menambah pekerjaan ke database!';
    }

} elseif ($action === 'delete_worker') {
    $workerId = $_POST['worker_id_to_delete'] ?? null;
    if ($workerId && deleteWorker((string)$workerId)) {
        $response['success'] = true;
        $response['message'] = 'Worker berhasil dihapus!';
    } else {
        $response['message'] = 'Gagal menghapus worker!';
    }

} elseif ($action === 'delete_worker_from_job') {
    $jobId = $_POST['job_id_to_delete'] ?? null;
    if ($jobId && deleteWorkerFromJob((string)$jobId)) {
        $response['success'] = true;
        $response['message'] = 'Pekerja berhasil dihapus dari pekerjaan!';
    } else {
        $response['message'] = 'Gagal menghapus pekerja dari pekerjaan!';
    }

} elseif ($action === 'delete_review') {
    $reviewId = $_POST['review_id_to_delete'] ?? null;
    if ($reviewId && deleteReview((int)$reviewId)) {
        $response['success'] = true;
        $response['message'] = 'Ulasan berhasil dihapus!';
    } else {
        $response['message'] = 'Gagal menghapus ulasan!';
    }
} elseif ($action === 'customer_delete_posted_job') {
    if (!isCustomer()) {
        $response['message'] = 'Aksi ini hanya untuk customer.';
        ob_clean(); // Discard any buffered output before sending JSON
        echo json_encode($response);
        exit;
    }

    $jobId = $_POST['job_id'] ?? null;
    $customerId = $_SESSION['user_id'] ?? null; // Use the correct session variable

    if (!$jobId || !$customerId) {
        $response['message'] = 'Data tidak valid untuk menghapus pekerjaan.';
        ob_clean(); // Discard any buffered output before sending JSON
        echo json_encode($response);
        exit;
    }

    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM lowongan_diposting WHERE id_lowongan = ? AND id_pelanggan = ? AND id_pekerja IS NULL");
        $stmt->execute([$jobId, $customerId]);

        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
            $response['message'] = 'Pekerjaan yang diposting berhasil dihapus.';
        } else {
            $response['message'] = 'Gagal menghapus pekerjaan. Mungkin sudah diambil oleh pekerja atau tidak ditemukan.';
        }
    } catch (PDOException $e) {
        SecurityLogger::logError('Error deleting posted job: ' . $e->getMessage());
        $response['message'] = 'Terjadi error di database saat menghapus pekerjaan.';
    }

} elseif ($action === 'worker_accept_job' || $action === 'worker_reject_job' || $action === 'worker_complete_job') {
    
    // Pastikan hanya kuli yang bisa melakukan ini
    if (!isWorker()) {
        $response['message'] = 'Aksi ini hanya untuk pekerja.';
        ob_clean(); // Discard any buffered output before sending JSON
        echo json_encode($response);
        exit;
    }
    
    $jobId = $_POST['job_id'] ?? null;
    $workerProfileId = $_SESSION['worker_profile_id'];

    if (!$jobId) {
        $response['message'] = 'Job ID tidak ada.';
        ob_clean(); // Discard any buffered output before sending JSON
        echo json_encode($response);
        exit;
    }
    
    // Verifikasi ekstra: pastikan kuli ini pemilik job-nya
    $jobDetails = getJobById($jobId);
    if ($jobDetails['id_pekerja'] !== $workerProfileId) {
        $response['message'] = 'Anda tidak berhak mengubah job ini.';
        ob_clean(); // Discard any buffered output before sending JSON
        echo json_encode($response);
        exit;
    }

    // Tentukan status baru berdasarkan aksi
    $newStatus = '';
    if ($action === 'worker_accept_job') {
        $newStatus = 'in-progress';
    } elseif ($action === 'worker_reject_job') {
        $newStatus = 'cancelled';
    } elseif ($action === 'worker_complete_job') {
        $newStatus = 'completed';
    }

    // Panggil fungsi updateJobStatus yang sudah ada (aman & pakai transaksi)
    if (updateJobStatus($jobId, $newStatus)) {
        $response['success'] = true;
        $response['message'] = 'Status job berhasil diupdate!';
    } else {
        $response['message'] = 'Gagal mengupdate status job.';
    }
} elseif ($action === 'worker_take_posted_job') {
    if (!isWorker()) {
        $response['message'] = 'Aksi ini hanya untuk pekerja.';
        ob_clean(); // Discard any buffered output before sending JSON
        echo json_encode($response);
        exit;
    }

    $jobId = $_POST['job_id'] ?? null;
    $workerProfileId = $_SESSION['worker_profile_id'];

    if (!$jobId) {
        $response['message'] = 'Job ID tidak valid.';
        ob_clean(); // Discard any buffered output before sending JSON
        echo json_encode($response);
        exit;
    }

    global $pdo;
    DatabaseHelper::beginTransaction();

    try {
        // Atomic update to claim the job
        $sql = "UPDATE lowongan_diposting SET id_pekerja = ?, status_lowongan = 'assigned' WHERE id_lowongan = ? AND status_lowongan = 'open'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$workerProfileId, $jobId]);

        if ($stmt->rowCount() > 0) {
            // Successfully claimed the job, now get its details
            $stmtSelect = $pdo->prepare("
                SELECT pj.*, u.nama_pengguna as customer_email, u.nama_lengkap as customer_name, u.telepon as customer_phone, u.alamat_lengkap as alamat 
                FROM lowongan_diposting pj 
                JOIN pengguna u ON pj.id_pelanggan = u.id_pengguna 
                WHERE pj.id_lowongan = ?
            ");
            $stmtSelect->execute([$jobId]);
            $postedJob = $stmtSelect->fetch();

            if ($postedJob) {
                // Now, create a new job in the main `jobs` table
                $worker = getWorkerById($workerProfileId);

                // Create a synthetic date range (e.g., 1 day) as this is not in posted_jobs
                $startDate = date('Y-m-d');
                $endDate = date('Y-m-d');

                $jobData = [
                    'id_lowongan_diposting' => $jobId,
                    'id_pekerja' => $workerProfileId,
                    'nama_pekerja' => $worker['nama'],
                    'jenis_pekerjaan' => $postedJob['jenis_pekerjaan'],
                    'tanggal_mulai' => $startDate,
                    'tanggal_selesai' => $endDate,
                    'nama_pelanggan' => $postedJob['customer_name'],
                    'telepon_pelanggan' => $postedJob['customer_phone'],
                    'email_pelanggan' => $postedJob['customer_email'],
                    'harga' => $postedJob['anggaran'] ?? 0,
                    'lokasi' => $postedJob['lokasi'],
                    'alamat_lokasi' => $postedJob['alamat'],
                    'deskripsi' => $postedJob['deskripsi_lowongan'],
                    'status_pekerjaan' => 'in-progress'
                ];

                if (addWorkerToJob($jobData)) {
                    DatabaseHelper::commit();
                    $response['success'] = true;
                    $response['message'] = 'Pekerjaan berhasil diambil dan telah dipindahkan ke tab "Sedang Berjalan".';
                } else {
                    DatabaseHelper::rollback();
                    $response['message'] = 'Gagal membuat penugasan pekerjaan. Silakan coba lagi.';
                }
            } else {
                DatabaseHelper::rollback();
                $response['message'] = 'Tidak dapat menemukan detail pekerjaan setelah diklaim.';
            }
        } else {
            // Job was already taken
            DatabaseHelper::rollback();
            $response['message'] = 'Pekerjaan ini sudah diambil oleh orang lain.';
        }
    } catch (PDOException $e) {
        DatabaseHelper::rollback();
        SecurityLogger::logError('Error taking posted job: ' . $e->getMessage());
        $response['message'] = 'Terjadi error di database. Silakan coba lagi.';
    }
}

} catch (Throwable $e) {
    // Catch any unexpected PHP errors or exceptions
    SecurityLogger::logError('Fatal error in ajax_handler: ' . $e->getMessage() . ' on line ' . $e->getLine() . ' in ' . $e->getFile());
    $response = [
        'success' => false,
        'message' => 'Internal Server Error: ' . $e->getMessage() . ' (See logs for details)',
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ];
}

// Kirim response JSON final
ob_clean(); // Discard any buffered output
echo json_encode($response);
exit;