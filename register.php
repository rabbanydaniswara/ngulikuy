<?php
// register.php

// Sertakan file koneksi database Anda
require_once 'db.php';
require_once 'functions.php'; // Kita butuh functions.php untuk validasi CSRF
// session_start() sudah dipanggil di functions.php

$error_message = '';
$success_message = '';

// Cek jika form sudah di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $error_message = 'Sesi tidak valid atau telah kedaluwarsa. Silakan coba lagi.';
    } else {
        
        // Ambil dan validasi input
        $name = InputValidator::sanitizeString($_POST['name'] ?? '');
        $username = $_POST['username'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $alamat = InputValidator::sanitizeString($_POST['alamat'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validasi dasar
        if (empty($name) || empty($username) || empty($phone) || empty($alamat) || empty($password)) {
            $error_message = 'Semua field harus diisi!';
        } 
        // Validasi email
        elseif (!InputValidator::validateEmail($username)) {
            $error_message = 'Format email tidak valid!';
        }
        // Validasi phone
        elseif (!InputValidator::validatePhone($phone)) {
            $error_message = 'Format nomor telepon tidak valid!';
        }
        // Validasi password
        elseif ($password !== $confirm_password) {
            $error_message = 'Password dan Konfirmasi Password tidak cocok!';
        } else {
            $passwordCheck = InputValidator::validatePassword($password);
            if (!$passwordCheck['valid']) {
                $error_message = $passwordCheck['message'];
            } else {
                // Proses registrasi
                try {
                    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                    $stmtCheck->execute([$username]);
                    
                    if ($stmtCheck->fetchColumn() > 0) {
                        $error_message = 'Email ini sudah terdaftar.';
                    } else {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        
                        $sql = "INSERT INTO users (username, password, role, name, phone, alamat) VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$username, $hashedPassword, 'customer', $name, $phone, $alamat]);
                        
                        SecurityLogger::log('INFO', 'New user registered: ' . $username);
                        $success_message = 'Registrasi berhasil!';
                    }
                } catch (PDOException $e) {
                    SecurityLogger::logError('Registration error: ' . $e->getMessage());
                    $error_message = 'Registrasi gagal. Silakan coba lagi.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ngulikuy - Registrasi</title>
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
        input::-ms-reveal,
        input::-ms-clear {
            display: none !important;
        }
        input[type="password"]::-webkit-textfield-decoration-container {
            display: none !important;
        }
        input[type="password"]::-webkit-credentials-auto-fill-button {
            display: none !important;
            -webkit-appearance: none;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <div class="absolute inset-0 gradient-bg opacity-10 z-0"></div>
    
    <div class="w-full max-w-md mx-4 z-10">
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="p-6 text-center" style="background-image: linear-gradient(135deg, rgba(59,130,246,0.85), rgba(99,102,241,0.85)), url('images/header-bg.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
                <div class="flex justify-center mb-4">
                    <div class="bg-white p-3 rounded-full">
                        <i data-feather="tool" class="text-blue-600 w-8 h-8"></i>
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-white">Buat Akun Baru</h1>
                <p class="text-blue-100 mt-1">Ngulikuy</p>
            </div>

            <div class="p-8">
                <?php if ($error_message): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
                        <?php echo htmlspecialchars($success_message); ?>
                        Silakan <a href="login.php" class="font-bold text-green-700 hover:underline">login di sini</a>.
                    </div>
                <?php else: ?>
                
                <form method="POST">
                    <?php echo csrfInput(); ?>
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-feather="user" class="text-gray-400 w-5 h-5"></i>
                            </div>
                            <input type="text" id="name" name="name" class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus" placeholder="Nama Anda" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-feather="mail" class="text-gray-400 w-5 h-5"></i>
                            </div>
                            <input type="email" id="email" name="username" class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus" placeholder="email@example.com" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-feather="phone" class="text-gray-400 w-5 h-5"></i>
                            </div>
                            <input type="tel" id="phone" name="phone" class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus" placeholder="08123456789" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 pt-3 flex items-start pointer-events-none">
                                <i data-feather="home" class="text-gray-400 w-5 h-5"></i>
                            </div>
                            <textarea id="alamat" name="alamat" rows="3" class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus" placeholder="Alamat lengkap Anda" required></textarea>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-feather="lock" class="text-gray-400 w-5 h-5"></i>
                            </div>
                            <input type="password" id="password" name="password" class="pl-10 pr-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus" placeholder="••••••••" required>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePassword('password', 'eyeIconPassword')">
                                <i data-feather="eye" id="eyeIconPassword" class="text-gray-400 w-5 h-5"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-feather="lock" class="text-gray-400 w-5 h-5"></i>
                            </div>
                            <input type="password" id="confirm_password" name="confirm_password" class="pl-10 pr-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus" placeholder="••••••••" required>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePassword('confirm_password', 'eyeIconConfirmPassword')">
                                <i data-feather="eye" id="eyeIconConfirmPassword" class="text-gray-400 w-5 h-5"></i>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="gradient-bg w-full py-3 px-4 rounded-lg text-white font-medium hover:opacity-90 transition duration-200 flex items-center justify-center">
                        <i data-feather="user-plus" class="mr-2"></i>
                        Register
                    </button>
                </form>
                <?php endif; ?>
                
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">Sudah punya akun? <a href="login.php" class="text-blue-600 font-medium hover:underline">Login di sini</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        feather.replace();

        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.setAttribute('data-feather', 'eye-off');
            } else {
                passwordInput.type = 'password';
                eyeIcon.setAttribute('data-feather', 'eye');
            }
            feather.replace();
        }
    </script>
</body>
</html>
