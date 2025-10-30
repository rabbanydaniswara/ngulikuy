<?php
// ajax_handler.php
require_once 'functions.php'; // functions.php sudah include db.php dan session_start()

// Set header ke JSON (kecuali jika kita mengharapkan output lain nanti)
// Kita pindahkan header('Content-Type: application/json'); ke dalam blok if/case
// karena add/edit worker mungkin mengembalikan pesan error non-JSON saat upload gagal

// Keamanan: Pastikan hanya admin yang login bisa akses
if (!isAdmin()) {
    header('Content-Type: application/json'); // Set header JSON untuk pesan error
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya admin.']);
    exit;
}

// --- VALIDASI CSRF UNTUK AJAX ---
// Untuk AJAX, kita terima token dari $_POST karena kita akan pakai FormData
if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
    header('Content-Type: application/json'); // Set header JSON untuk pesan error
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
    echo json_encode($response);
    exit;
}

// --- Handle Aksi ---
header('Content-Type: application/json'); // Set header JSON default untuk semua aksi

if ($action === 'update_job_status') {
    // Logika ini sekarang perlu membaca dari $_POST, bukan $input JSON
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
}

// Kirim response JSON final
echo json_encode($response);
exit;