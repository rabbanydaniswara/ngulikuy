<?php
// index.php

// Kita butuh functions.php untuk proses login (authenticate)
// Kita TIDAK butuh db.php di sini karena functions.php sudah memanggilnya
require_once 'functions.php'; 

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- VALIDASI CSRF ---
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        // Jika token tidak valid, kita bisa set error umum
        // atau error spesifik tergantung form mana yang disubmit
        if (isset($_POST['admin_login'])) {
            $admin_error = 'Sesi tidak valid. Silakan coba lagi.';
        } else {
            $login_error = 'Sesi tidak valid. Silakan coba lagi.';
        }
    } else {
    // --- AKHIR VALIDASI CSRF ---

        // Cek apakah ini login admin
        if (isset($_POST['admin_login'])) {
            $username = $_POST['admin_username'] ?? '';
            $password = $_POST['admin_password'] ?? '';
            
            if (authenticate($username, $password) && $_SESSION['user_role'] === 'admin') {
                header('Location: admin_dashboard.php');
                session_regenerate_id(true);
                exit();
            } else {
                $admin_error = 'Username atau Password Admin salah';
            }
            
        // Jika bukan, berarti ini login customer
        } else {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (authenticate($email, $password) && $_SESSION['user_role'] === 'customer') {
                header('Location: customer_dashboard.php');
                session_regenerate_id(true);
                exit();
            } else {
                $login_error = 'Email atau Password salah';
            }
        }
    } // <-- Penutup blok 'else' dari validasi CSRF
}

// Logout if requested
if (isset($_GET['logout'])) {
    logout();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NguliKuy - Login</title>
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
        .shake {
            animation: shake 0.5s;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
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
                <h1 class="text-2xl font-bold text-white">NguliKuy</h1>
                <p class="text-blue-100 mt-1">Digital Kuli Ordering Platform</p>
            </div>

            <form method="POST" class="p-8">
                <?php echo csrfInput(); ?> <?php if (isset($login_error)): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
                        <?php echo htmlspecialchars($login_error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-feather="mail" class="text-gray-400"></i>
                        </div>
                        <input type="email" id="email" name="email" class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus" placeholder="email@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-feather="lock" class="text-gray-400"></i>
                        </div>
                        <input type="password" id="password" name="password" class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus" placeholder="••••••••" required>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" id="togglePassword">
                            <i data-feather="eye" class="text-gray-400" id="eyeIcon"></i>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                    </div>
                    <a href="#" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
                </div>

                <button type="submit" class="gradient-bg w-full py-3 px-4 rounded-lg text-white font-medium hover:opacity-90 transition duration-200 flex items-center justify-center">
                    <i data-feather="log-in" class="mr-2"></i>
                    Sign In
                </button>
                
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">Belum punya akun? <a href="register.php" class="text-blue-600 font-medium hover:underline">Register</a></p>
                </div>

                <div class="mt-6 text-center">
                    <button type="button" id="adminLoginToggle" class="text-xs text-gray-500 hover:text-blue-600">Admin Login</button>
                </div>
            </form>
        </div>

        <div id="adminLoginModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                <div class="gradient-bg p-6 text-center">
                    <h2 class="text-xl font-bold text-white">Admin Login</h2>
                </div>
                <form method="POST" class="p-6">
                    <?php echo csrfInput(); ?> <input type="hidden" name="admin_login" value="1">
                    
                    <?php if (isset($admin_error)): ?>
                        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
                            <?php echo htmlspecialchars($admin_error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-6">
                        <label for="adminUsername" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-feather="user" class="text-gray-400"></i>
                            </div>
                            <input type="text" id="adminUsername" name="admin_username" class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus" placeholder="admin" required value="<?php echo isset($_POST['admin_username']) ? htmlspecialchars($_POST['admin_username']) : ''; ?>">
                        </div>
                    </div>
                    <div class="mb-6">
                        <label for="adminPassword" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-feather="key" class="text-gray-400"></i>
                            </div>
                            <input type="password" id="adminPassword" name="admin_password" class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" id="cancelAdminLogin" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="gradient-bg px-4 py-2 rounded-lg text-white hover:opacity-90">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialize feather icons
        feather.replace();
        
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.setAttribute('data-feather', 'eye-off');
            } else {
                passwordInput.type = 'password';
                eyeIcon.setAttribute('data-feather', 'eye');
            }
            feather.replace();
        });

        // Admin login toggle
        document.getElementById('adminLoginToggle').addEventListener('click', function() {
            document.getElementById('adminLoginModal').classList.remove('hidden');
        });

        // Cancel admin login
        document.getElementById('cancelAdminLogin').addEventListener('click', function() {
            document.getElementById('adminLoginModal').classList.add('hidden');
        });

        // Jika ada error admin, langsung tampilkan modalnya
        <?php if (isset($admin_error)): ?>
            document.getElementById('adminLoginModal').classList.remove('hidden');
        <?php endif; ?>
    </script>
</body>
</html>