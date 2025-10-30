<?php
// register.php

// Sertakan file koneksi database Anda
require_once 'db.php';
require_once 'functions.php'; // Kita butuh functions.php untuk validasi CSRF
// session_start() sudah dipanggil di functions.php

$error_message = '';
$success_message = 'Registrasi berhasil!';

// Cek jika form sudah di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- VALIDASI CSRF ---
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $error_message = 'Sesi tidak valid atau telah kedaluwarsa. Silakan coba lagi.';
    } else {
    // --- AKHIR VALIDASI CSRF ---

        // 1. Ambil data dari form
        $name = $_POST['name'] ?? '';
        $username = $_POST['username'] ?? ''; // Ini adalah email
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // 2. Validasi sederhana
        if (empty($name) || empty($username) || empty($password) || empty($confirm_password)) {
            $error_message = 'Semua field harus diisi!';
        } elseif ($password !== $confirm_password) {
            $error_message = 'Password dan Konfirmasi Password tidak cocok!';
        } elseif (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Email tidak valid!';
        } else {
            
            // 3. Cek apakah email (username) sudah ada
            try {
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                $stmtCheck->execute([$username]);
                
                if ($stmtCheck->fetchColumn() > 0) {
                    $error_message = 'Email ini sudah terdaftar. Silakan gunakan email lain.';
                } else {
                    
                    // 4. (INI BAGIAN PENTING) Hash password-nya!
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    // 5. Masukkan ke database
                    $sql = "INSERT INTO users (username, password, role, name) VALUES (?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    
                    // User baru kita set sebagai 'customer' secara default
                    $stmt->execute([$username, $hashedPassword, 'customer', $name]);
                    
                    $success_message = 'Registrasi berhasil! Silakan <a href="index.php" class="font-bold text-green-700 hover:underline">login</a>.';

                }
            } catch (PDOException $e) {
                $error_message = "Registrasi gagal, terjadi error pada database: " . $e->getMessage();
            }
        }
    } // <-- Penutup blok 'else' dari validasi CSRF
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NguliKuy - Registrasi</title>
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
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <div class="absolute inset-0 gradient-bg opacity-10 z-0"></div>
    
    <div class="w-full max-w-md mx-4 z-10">
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="gradient-bg p-6 text-center">
                <div class="flex justify-center mb-4">
                    <div class="bg-white p-3 rounded-full">
                        <i data-feather="tool" class="text-blue-600 w-8 h-8"></i>
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-white">Buat Akun Baru</h1>
                <p class="text-blue-100 mt-1">NguliKuy</p>
            </div>

            <div class="p-8">
                <?php if ($error_message): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
                        <?php echo htmlspecialchars($error_message); // Perbaikan XSS ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
                        <?php echo htmlspecialchars($success_message); // <-- Selalu escape ?>
                        Silakan <a href="index.php" class="font-bold text-green-700 hover:underline">login di sini</a>.
                    </div>
                <?php else: // Sembunyikan form jika registrasi sudah sukses ?>
                
                <form method="POST">
                    <?php echo csrfInput(); ?> <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-feather="user" class="text-gray-400"></i>
                            </div>
                            <input type="text" id="name" name="name" class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus" placeholder="Nama Anda" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-feather="mail" class="text-gray-400"></i>
                            </div>
                            <input type="email" id="email" name="username" class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus" placeholder="email@example.com" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-feather="lock" class="text-gray-400"></i>
                            </div>
                            <input type="password" id="password" name="password" class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus" placeholder="••••••••" required>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-feather="lock" class="text-gray-400"></i>
                            </div>
                            <input type="password" id="confirm_password" name="confirm_password" class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="gradient-bg w-full py-3 px-4 rounded-lg text-white font-medium hover:opacity-90 transition duration-200 flex items-center justify-center">
                        <i data-feather="user-plus" class="mr-2"></i>
                        Daftar
                    </button>
                </form>
                <?php endif; ?>
                
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">Sudah punya akun? <a href="index.php" class="text-blue-600 font-medium hover:underline">Login di sini</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize feather icons
        feather.replace();
    </script>
</body>
</html>