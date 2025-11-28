<?php
// admin_pages/save_profile.php
if (!isset($_POST['save_profile'])) {
    header('Location: ../admin_dashboard.php?tab=profile');
    exit;
}
if (!isset($_SESSION)) session_start();

if (file_exists(__DIR__ . '/../functions.php')) {
    require_once __DIR__ . '/../functions.php';
}

$admin_id = (int)($_POST['admin_id'] ?? ($_SESSION['user_id'] ?? 0));
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

$errors = [];
if ($admin_id <= 0) $errors[] = 'Admin tidak ditemukan';
if ($name === '') $errors[] = 'Nama wajib diisi';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid';

$avatar_path = null;

if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
    $f = $_FILES['avatar'];
    $allowed = ['image/jpeg','image/png','image/jpg'];
    if ($f['error'] !== UPLOAD_ERR_OK) $errors[] = 'Gagal upload avatar';
    $mime = mime_content_type($f['tmp_name']);
    if (!in_array($mime, $allowed)) $errors[] = 'Format avatar harus JPG/PNG';
    if ($f['size'] > 1 * 1024 * 1024) $errors[] = 'Avatar maksimal 1MB';

    if (empty($errors)) {
        $dir = __DIR__ . '/../uploads/admins';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
        $filename = 'admin_' . $admin_id . '_' . time() . '.' . $ext;
        $target = $dir . '/' . $filename;

        if (move_uploaded_file($f['tmp_name'], $target)) {
            $avatar_path = 'uploads/admins/' . $filename;
        } else {
            $errors[] = 'Gagal menyimpan avatar';
        }
    }
}

$update_data = ['name' => $name, 'email' => $email];
if ($avatar_path) $update_data['avatar'] = $avatar_path;

if ($new_password !== '') {
    $can_change = true;

    if (function_exists('getAdminById')) {
        $adm = getAdminById($admin_id);
        if ($adm && !empty($adm['kata_sandi'])) { // Changed 'password_hash' to 'kata_sandi'
            if (!password_verify($current_password, $adm['kata_sandi'])) { // Changed 'password_hash' to 'kata_sandi'
                $errors[] = 'Password saat ini salah';
                $can_change = False;
            }
        }
    }

    if ($can_change) {
        $update_data['password_hash'] = password_hash($new_password, PASSWORD_DEFAULT);
    }
}

if (!empty($errors)) {
    $_SESSION['flash'] = implode('. ', $errors);
    header('Location: ../admin_dashboard.php?tab=profile');
    exit;
}

$ok = false;

if (function_exists('updateAdminById')) {
    $ok = updateAdminById($admin_id, $update_data);
} else if (file_exists(__DIR__ . '/../db.php')) { // This fallback should ideally not be hit
    require_once __DIR__ . '/../db.php';
    try {
        $stmt_parts = [];
        $params = [];
        $keyMap = [ // Map update_data keys to actual DB column names
            'name' => 'nama_lengkap',
            'email' => 'nama_pengguna',
            'avatar' => 'url_foto',
            'password_hash' => 'kata_sandi'
        ];
        foreach ($update_data as $k => $v) {
            $dbKey = $keyMap[$k] ?? $k; // Use mapped key or original if not mapped
            $stmt_parts[] = "`$dbKey` = :$dbKey";
            $params[":$dbKey"] = $v;
        }
        $sql = "UPDATE pengguna SET " . implode(', ', $stmt_parts) . " WHERE id_pengguna = :id_pengguna AND peran = 'admin'";
        $params[':id_pengguna'] = $admin_id; // Use id_pengguna here
        $pdo->prepare($sql)->execute($params);
        $ok = true;
    } catch (Exception $e) {
        $_SESSION['flash'] = 'Gagal menyimpan perubahan: ' . $e->getMessage();
        header('Location: ../admin_dashboard.php?tab=profile');
        exit;
    }
}

if ($ok) {
    $_SESSION['flash'] = 'Profil berhasil diperbarui';

    // Update session variables to reflect changes
    // Assuming $_SESSION['user_name'], $_SESSION['user'], $_SESSION['user_photo'] are used for admin
    if (!isset($_SESSION['user_name'])) $_SESSION['user_name'] = '';
    if (!isset($_SESSION['user'])) $_SESSION['user'] = '';
    
    $_SESSION['user_name'] = $update_data['name'] ?? $_SESSION['user_name'];
    $_SESSION['user'] = $update_data['email'] ?? $_SESSION['user'];
    if (!empty($update_data['avatar'])) {
        $_SESSION['user_photo'] = $update_data['avatar'];
    }
} else {
    $_SESSION['flash'] = 'Tidak ada perubahan atau terjadi kesalahan';
}

header('Location: ../admin_dashboard.php?tab=profile');
exit;
