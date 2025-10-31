<?php
require_once 'functions.php';

redirectIfNotAdmin();

// == LOGIKA PHP BAGIAN ATAS SEKARANG LEBIH SEDERHANA ==

// Handle form submissions (HANYA Add Job dan Delete actions)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- VALIDASI CSRF ---
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $error_message = 'Sesi tidak valid atau telah kedaluwarsa. Silakan coba lagi.';
    } else {
    // --- AKHIR VALIDASI CSRF ---

    }
}


// Menampilkan pesan sukses/error dari redirect setelah hapus review atau dari AJAX (jika di-set di session/GET)
if (isset($_GET['success_msg'])) $success_message = urldecode($_GET['success_msg']);
if (isset($_GET['error_msg'])) $error_message = urldecode($_GET['error_msg']);
// Menampilkan pesan sukses/error dari form Add Job (non-AJAX)
if (isset($_GET['success']) && $_GET['success'] == 1 && $active_tab == 'reviews') $success_message = 'Ulasan berhasil dihapus!';
if (isset($_GET['error']) && $_GET['error'] == 1 && $active_tab == 'reviews') $error_message = 'Gagal menghapus ulasan!';


// Get data for display (Sama seperti sebelumnya)
$workers = getWorkers();
$jobs = getJobs();
$availableWorkers = getAvailableWorkers();
$stats = getDashboardStats();
$totalWorkers = $stats['total_workers'];
$availableCount = $stats['available_workers'];
$onJobCount = $stats['on_job_workers'];
$activeJobs = $stats['active_jobs'];
$active_tab = $_GET['tab'] ?? 'dashboard';

// Ambil data review jika tab aktif adalah 'reviews'
$reviews = [];
if ($active_tab === 'reviews') {
    $reviews = getAllReviews();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NguliKuy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <style>
        /* ... (SEMUA STYLE CSS ANDA TETAP SAMA) ... */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        .gradient-bg { background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%); }
        /* KUNCI RESPONSIVE #2: Mengubah 'sidebar' agar transisi lebih mulus */
        .sidebar { transition: all 0.3s; }
        .active-tab { border-left: 4px solid #3b82f6; background-color: #eff6ff; }
        .status-available { background-color: #dcfce7 !important; color: #166534 !important; }
        .status-assigned { background-color: #dbeafe !important; color: #1e40af !important; }
        .status-on-leave { background-color: #fef3c7 !important; color: #92400e !important; }
        .status-completed { background-color: #dcfce7 !important; color: #166534 !important; }
        .status-in-progress { background-color: #fef3c7 !important; color: #92400e !important; }
        .status-pending { background-color: #dbeafe !important; color: #1e40af !important; }
        .status-cancelled { background-color: #fecaca !important; color: #dc2626 !important; }
        .worker-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); }
        .photo-preview { max-width: 150px; max-height: 150px; object-fit: cover; border-radius: 0.5rem; border: 2px solid #e5e7eb; }
        .upload-area { border: 2px dashed #d1d5db; border-radius: 0.5rem; transition: all 0.3s ease; }
        .upload-area:hover { border-color: #3b82f6; background-color: #f8fafc; }
        .upload-area.dragover { border-color: #3b82f6; background-color: #eff6ff; }
        .file-input { display: none; }
        .modal-overlay { background-color: rgba(0, 0, 0, 0.5); }
        .modal-content { max-height: 90vh; overflow-y: auto; }
        .job-status-select { appearance: none; -webkit-appearance: none; -moz-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2220%22%20height%3D%2220%22%20fill%3D%22none%22%20stroke%3D%22currentColor%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%222%22%20class%3D%22feather%20feather-chevron-down%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 0.5rem center; background-size: 1em; padding-right: 2rem; }
        #ajax-notification { position: fixed; bottom: 20px; right: 20px; padding: 10px 20px; border-radius: 5px; color: white; z-index: 1000; display: none; transition: opacity 0.5s ease-in-out; }
        #ajax-notification.success { background-color: #10B981; }
        #ajax-notification.error { background-color: #EF4444; }
        /* Style for Rating Stars in Table */
        .rating-stars { display: flex; color: #f59e0b; /* amber-500 */ }
    </style>
</head>
<body class="min-h-screen flex">

    <div id="sidebar" class="sidebar w-64 bg-white shadow-lg fixed h-screen z-30 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
        <div class="p-4 gradient-bg text-white flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold">NguliKuy</h1>
                <p class="text-sm opacity-80">Admin Dashboard</p>
            </div>
            <button id="closeSidebarBtn" class="md:hidden p-1 rounded-full hover:bg-white/20">
                <i data-feather="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="mt-6">
             <a href="?tab=dashboard" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?php echo $active_tab === 'dashboard' ? 'active-tab' : ''; ?>"><i data-feather="home" class="mr-3"></i>Dashboard</a>
            <a href="?tab=workers" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?php echo $active_tab === 'workers' ? 'active-tab' : ''; ?>"><i data-feather="users" class="mr-3"></i>Data Kuli</a>
            <a href="?tab=jobs" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?php echo $active_tab === 'jobs' ? 'active-tab' : ''; ?>"><i data-feather="clipboard" class="mr-3"></i>Status Pengerjaan</a>
            <a href="?tab=reviews" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?php echo $active_tab === 'reviews' ? 'active-tab' : ''; ?>">
                <i data-feather="message-square" class="mr-3"></i>
                Ulasan Pelanggan
            </a>
            <a href="?tab=add_worker" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?php echo $active_tab === 'add_worker' ? 'active-tab' : ''; ?>"><i data-feather="user-plus" class="mr-3"></i>Tambah Kuli</a>
            <a href="?tab=add_job" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?php echo $active_tab === 'add_job' ? 'active-tab' : ''; ?>"><i data-feather="plus-circle" class="mr-3"></i>Tambah Pekerjaan</a>
        </div>
        <div class="absolute bottom-0 w-full p-4 border-t">
            <a href="index.php?logout=1" class="flex items-center text-gray-700 hover:text-blue-600"><i data-feather="log-out" class="mr-2"></i>Logout</a></div>
    </div>

    <div class="flex-1 ml-0 md:ml-64 transition-all duration-300 ease-in-out">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <div class="flex items-center">
                <button id="openSidebarBtn" class="md:hidden mr-4 text-gray-600 hover:text-blue-600">
                    <i data-feather="menu" class="w-6 h-6"></i>
                </button>
                <h2 class="text-xl font-semibold text-gray-800"><?php $titles = ['dashboard' => 'Dashboard', 'workers' => 'Data Kuli', 'jobs' => 'Status Pengerjaan', 'reviews' => 'Ulasan Pelanggan', 'add_worker' => 'Tambah Kuli', 'add_job' => 'Tambah Pekerjaan']; echo $titles[$active_tab] ?? 'Dashboard'; // Tambahkan title 'reviews' ?></h2>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative"><i data-feather="bell" class="text-gray-500 hover:text-blue-600 cursor-pointer"></i><span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center"><?php echo $stats['pending_jobs'] ?? 0; ?></span></div>
                <div class="flex items-center">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="Admin" class="w-8 h-8 rounded-full mr-2">
                    <span class="text-sm font-medium"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </div>
            </div>
        </header>

        <?php if (isset($success_message)): ?><div class="m-4 p-4 bg-green-100 text-green-700 rounded-lg"><?php echo htmlspecialchars($success_message); ?></div><?php endif; ?>
        <?php if (isset($error_message)): ?><div class="m-4 p-4 bg-red-100 text-red-700 rounded-lg"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>
        <div id="ajax-notification"></div>

        <main class="p-6">
            <?php
            define('IS_ADMIN_PAGE', true);
            $allowed_tabs = ['dashboard', 'workers', 'jobs', 'reviews', 'add_worker', 'add_job'];
            $tab_to_load = $active_tab;
            if (!in_array($tab_to_load, $allowed_tabs)) $tab_to_load = 'dashboard';
            $page_file = "admin_pages/{$tab_to_load}.php";
            if (file_exists($page_file)) include $page_file;
            else echo '<div class="m-4 p-4 bg-red-100 text-red-700 rounded-lg"><strong>Error:</strong> Tampilan untuk tab "' . htmlspecialchars($tab_to_load) . '" tidak ditemukan.</div>';
            ?>
        </main>
    </div>

    <div id="sidebarOverlay" class="hidden md:hidden fixed inset-0 bg-black/50 z-20"></div>


    <div id="editWorkerModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity modal-overlay" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="gradient-bg p-6 text-center text-white rounded-t-lg">
                    <h2 class="text-xl font-bold">Edit Data Kuli</h2>
                    <p id="editWorkerTitle" class="text-blue-100 mt-1"></p>
                </div>
                <form id="editWorkerForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="edit_worker" value="1">
                    <input type="hidden" id="editWorkerId" name="worker_id">
                    <?php echo csrfInput(); ?>
                    <div class="p-6 modal-content">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" id="editWorkerName" name="name" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" id="editWorkerEmail" name="email" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                                <input type="tel" id="editWorkerPhone" name="phone" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                                <input type="text" id="editWorkerLocation" name="location" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Skills</label>
                                <select multiple id="editWorkerSkills" name="skills[]" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 h-24">
                                    <option value="Construction">Construction</option>
                                    <option value="Moving">Moving</option>
                                    <option value="Cleaning">Cleaning</option>
                                    <option value="Gardening">Gardening</option>
                                    <option value="Plumbing">Plumbing</option>
                                    <option value="Electrical">Electrical</option>
                                    <option value="Painting">Painting</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple skills</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select id="editWorkerStatus" name="status" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="Available">Available</option>
                                    <option value="Assigned">Assigned</option>
                                    <option value="On Leave">On Leave</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Rate per Hari (Rp)</label>
                                <input type="number" id="editWorkerRate" name="rate" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                                <textarea id="editWorkerDescription" name="description" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pengalaman</label>
                                <input type="text" id="editWorkerExperience" name="experience" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Foto Profil</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 mb-2">Foto Saat Ini:</p>
                                        <img id="currentWorkerPhoto" class="photo-preview mb-2">
                                        <p class="text-xs text-gray-500">URL: <span id="currentPhotoUrl" class="break-all"></span></p>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 mb-2">Update Foto:</p>
                                            <input type="file" name="photo" accept="image/*" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah foto</p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 mb-2">Atau gunakan URL baru:</p>
                                            <input type="url" name="photo_url" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="https://example.com/new-photo.jpg">
                                            <p class="text-xs text-gray-500 mt-1">Kosongkan jika menggunakan upload file</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 gradient-bg text-base font-medium text-white hover:opacity-90 sm:ml-3 sm:w-auto sm:text-sm">
                            <span class="btn-text">Update Data Kuli</span>
                            <span class="btn-loading hidden"><i data-feather="loader" class="animate-spin mr-2"></i>Updating...</span>
                        </button>
                        <button type="button" id="cancelEditWorker" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="deleteWorkerModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity modal-overlay" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i data-feather="alert-triangle" class="h-6 w-6 text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Hapus Kuli</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Apakah Anda yakin ingin menghapus kuli bernama <strong id="deleteWorkerName" class="font-medium text-gray-700"></strong>?</p>
                                <p class="text-sm text-gray-500 mt-1">Tindakan ini tidak dapat dibatalkan.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form id="deleteWorkerForm" class="inline"> <?php echo csrfInput(); ?>
                        <input type="hidden" name="worker_id_to_delete" id="deleteWorkerIdInput">
                        <button type="submit" name="delete_worker_post" value="1" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Hapus
                        </button>
                    </form>
                    <button type="button" id="cancelDeleteWorker" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <div id="deleteJobModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity modal-overlay" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i data-feather="alert-triangle" class="h-6 w-6 text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Hapus Pekerjaan</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Apakah Anda yakin ingin menghapus pekerjaan <strong id="deleteJobType" class="font-medium text-gray-700"></strong> untuk customer <strong id="deleteJobCustomer" class="font-medium text-gray-700"></strong>?</p>
                                <p class="text-sm text-gray-500 mt-1">Tindakan ini tidak dapat dibatalkan.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form id="deleteJobForm" class="inline"> <?php echo csrfInput(); ?>
                        <input type="hidden" name="job_id_to_delete" id="deleteJobIdInput">
                        <button type="submit" name="delete_job_post" value="1" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Hapus
                        </button>
                    </form>
                    <button type="button" id="cancelDeleteJob" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <div id="deleteReviewModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity modal-overlay" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i data-feather="alert-triangle" class="h-6 w-6 text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Hapus Ulasan</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Apakah Anda yakin ingin menghapus ulasan dari <strong id="deleteReviewCustomer" class="font-medium text-gray-700"></strong> (ID: <span id="deleteReviewId" class="font-mono text-xs"></span>)?</p>
                                <p class="text-sm text-gray-500 mt-1">Tindakan ini tidak dapat dibatalkan dan akan mempengaruhi rating worker terkait.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form id="deleteReviewForm" class="inline"> <?php echo csrfInput(); ?>
                        <input type="hidden" name="review_id_to_delete" id="deleteReviewIdInput">
                        <button type="submit" name="delete_review_post" value="1" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Hapus Ulasan
                        </button>
                    </form>
                    <button type="button" id="cancelDeleteReview" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize feather icons
        feather.replace();
        
        // --- KUNCI RESPONSIVE #8: JavaScript untuk Toggle Sidebar ---
        const sidebar = document.getElementById('sidebar');
        const openSidebarBtn = document.getElementById('openSidebarBtn');
        const closeSidebarBtn = document.getElementById('closeSidebarBtn');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (openSidebarBtn) {
            openSidebarBtn.addEventListener('click', () => {
                if (sidebar) sidebar.classList.remove('-translate-x-full');
                if (sidebarOverlay) sidebarOverlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden'; // Mencegah scroll body saat sidebar mobile terbuka
            });
        }
        
        function closeMobileSidebar() {
             if (sidebar) sidebar.classList.add('-translate-x-full');
             if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
             document.body.style.overflow = 'auto'; // Kembalikan scroll body
        }

        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', closeMobileSidebar);
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeMobileSidebar);
        }
        // --- Akhir Script Responsive Sidebar ---


        // CSRF Token
        const CSRF_TOKEN = '<?php echo getCsrfToken(); ?>';

        // Notifikasi AJAX (Sama)
        const ajaxNotification = document.getElementById('ajax-notification'); 
        function showAjaxNotification(message, type = 'success') { 
            ajaxNotification.textContent = message; 
            ajaxNotification.className = ''; // Clear existing classes
            ajaxNotification.classList.add(type === 'success' ? 'success' : 'error');
            ajaxNotification.style.display = 'block'; 
            ajaxNotification.style.opacity = 1; 
            setTimeout(() => { 
                ajaxNotification.style.opacity = 0; 
                setTimeout(() => { ajaxNotification.style.display = 'none'; }, 500); 
            }, 3000);
        }

        // Photo Upload... (Sama, tapi pastikan variabel sudah dideklarasi di scope global jika perlu)
        const uploadArea = document.getElementById('uploadArea'); 
        const photoUpload = document.getElementById('photoUpload'); 
        const photoPreview = document.getElementById('photoPreview'); 
        const previewImage = document.getElementById('previewImage'); 
        const removePhoto = document.getElementById('removePhoto'); 
        const uploadStatus = document.getElementById('uploadStatus'); 
        const progressBar = document.getElementById('progressBar'); 
        const statusText = document.getElementById('statusText');
        
        function validateFile(file) { const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']; const maxSize = 2 * 1024 * 1024; if (!validTypes.includes(file.type)) return { valid: false, error: 'Hanya file gambar (JPEG, PNG, GIF) yang diizinkan' }; if (file.size > maxSize) return { valid: false, error: 'Ukuran file maksimal 2MB' }; return { valid: true };}
        function showPreview(file) { const reader = new FileReader(); reader.onload = function(e) { if(previewImage) previewImage.src = e.target.result; if(photoPreview) photoPreview.classList.remove('hidden'); if(uploadStatus) uploadStatus.classList.add('hidden'); }; reader.readAsDataURL(file);}
        function simulateUpload() { /* ... (fungsi simulateUpload tetap sama) ... */ }
        
        if (photoUpload) photoUpload.addEventListener('change', function(e) { const file = e.target.files[0]; if (file) { const validation = validateFile(file); if (validation.valid) { showPreview(file); /* simulateUpload(); -> Tidak perlu simulasi lagi */ } else { alert(validation.error); this.value = ''; } } });
        if (uploadArea) { ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => uploadArea.addEventListener(eventName, preventDefaults, false)); function preventDefaults (e) { e.preventDefault(); e.stopPropagation(); } ['dragenter', 'dragover'].forEach(eventName => uploadArea.addEventListener(eventName, highlight, false)); ['dragleave', 'drop'].forEach(eventName => uploadArea.addEventListener(eventName, unhighlight, false)); function highlight(e) { uploadArea.classList.add('dragover'); } function unhighlight(e) { uploadArea.classList.remove('dragover'); } uploadArea.addEventListener('drop', handleDrop, false); function handleDrop(e) { e.preventDefault(); e.stopPropagation(); const dt = e.dataTransfer; const file = dt.files[0]; if (file) { const validation = validateFile(file); if (validation.valid) { photoUpload.files = dt.files; showPreview(file); } else { alert(validation.error); } } } uploadArea.addEventListener('click', () => { if(photoUpload) photoUpload.click(); });}
        if (removePhoto) removePhoto.addEventListener('click', function(e) { e.preventDefault(); if(photoUpload) photoUpload.value = ''; if(photoPreview) photoPreview.classList.add('hidden'); if(previewImage) previewImage.src = ''; const pElement = uploadArea?.querySelector('p.text-sm.font-medium.text-gray-700'); if(pElement) pElement.textContent = 'Klik untuk upload foto'; if(uploadArea) uploadArea.classList.remove('bg-green-50', 'border-green-300');});
        
        // --- LOGIKA BARU: AJAX FORM SUBMIT ---
        
        // Fungsi generik untuk handle submit AJAX
            async function handleFormSubmit(event, action) {
            event.preventDefault(); // Mencegah submit biasa
            const form = event.target;
            
            // Temukan tombol submit di dalam form yang di-submit
            const submitButton = form.querySelector('button[type="submit"]');
            let btnText = null;
            let btnLoading = null;
            
            if (submitButton) {
                 btnText = submitButton.querySelector('.btn-text');
                 btnLoading = submitButton.querySelector('.btn-loading');

                // Tampilkan loading
                if (btnText) btnText.classList.add('hidden');
                if (btnLoading) btnLoading.classList.remove('hidden');
                submitButton.disabled = true;
                feather.replace(); // Update ikon loader
            }

            const formData = new FormData(form);
            formData.append('action', action);
            // CSRF token sudah ada di form via `csrfInput()` jadi tidak perlu append manual lagi

            try {
                const response = await fetch('ajax_handler.php', {
                    method: 'POST',
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    showAjaxNotification(data.message || 'Operasi berhasil!', 'success');
                    
                    // Reset form & tutup modal (jika modal)
                    form.reset(); 
                    
                    // Tentukan tab tujuan berdasarkan aksi
                    let redirectTab = 'dashboard';
                    if (action === 'add_worker' || action === 'edit_worker' || action === 'delete_worker') {
                        redirectTab = 'workers';
                        closeEditWorkerModal(); // Tutup modal edit
                        closeDeleteWorkerModal(); // Tutup modal delete
                    } else if (action === 'add_job' || action === 'delete_job') {
                        redirectTab = 'jobs';
                        closeDeleteJobModal(); // Tutup modal delete
                    } else if (action === 'delete_review') {
                        redirectTab = 'reviews';
                        closeDeleteReviewModal(); // Tutup modal delete
                    }
                    
                    if (action === 'add_worker' && photoPreview) { // Reset preview foto
                         photoPreview.classList.add('hidden');
                         if(previewImage) previewImage.src = '';
                         const pElement = uploadArea?.querySelector('p.text-sm.font-medium.text-gray-700');
                         if(pElement) pElement.textContent = 'Klik untuk upload foto';
                         if(uploadArea) uploadArea.classList.remove('bg-green-50', 'border-green-300');
                    }

                    // Redirect ke halaman tab yang sesuai
                    window.location.href = `?tab=${redirectTab}&success_msg=${encodeURIComponent(data.message || 'Operasi berhasil!')}`;
                    
                } else {
                    showAjaxNotification(data.message || 'Operasi gagal.', 'error');
                }

            } catch (error) {
                console.error('Fetch Error:', error);
                showAjaxNotification('Terjadi error: ' + error.message, 'error');
            } finally {
                // Sembunyikan loading
                if (submitButton) {
                    if (btnText) btnText.classList.remove('hidden');
                    if (btnLoading) btnLoading.classList.add('hidden');
                    submitButton.disabled = false;
                }
            }
        }

        // Terapkan ke form Add Worker
        const addWorkerForm = document.getElementById('saveWorkerBtn')?.closest('form');
        if (addWorkerForm) {
            addWorkerForm.addEventListener('submit', (event) => handleFormSubmit(event, 'add_worker'));
            const addWorkerBtn = document.getElementById('saveWorkerBtn');
            if(addWorkerBtn) {
                 addWorkerBtn.innerHTML = `<span class="btn-text flex items-center"><i data-feather="save" class="w-4 h-4 mr-2"></i> Simpan Kuli</span><span class="btn-loading hidden flex items-center"><i data-feather="loader" class="animate-spin mr-2"></i>Menyimpan...</span>`;
                 feather.replace();
            }
        }
        
        // *** BARU: Terapkan ke form Add Job ***
        const addJobForm = document.getElementById('saveJobBtn')?.closest('form');
        if (addJobForm) {
            addJobForm.addEventListener('submit', (event) => handleFormSubmit(event, 'add_job'));
            // Kita sudah menambahkan HTML (btn-text/btn-loading) di file add_job.php
        }


        // Terapkan ke form Edit Worker (di dalam modal)
        const editWorkerForm = document.getElementById('editWorkerForm');
        if (editWorkerForm) {
            editWorkerForm.addEventListener('submit', (event) => handleFormSubmit(event, 'edit_worker'));
             const editWorkerSubmitBtn = editWorkerForm.querySelector('button[type="submit"]');
             if(editWorkerSubmitBtn) {
                 editWorkerSubmitBtn.innerHTML = `<span class="btn-text">Update Data Kuli</span><span class="btn-loading hidden"><i data-feather="loader" class="animate-spin mr-2"></i>Updating...</span>`;
                 feather.replace();
             }
        }
        
        // *** BARU: Terapkan ke form Delete ***
        const deleteWorkerForm = document.getElementById('deleteWorkerForm');
        if (deleteWorkerForm) {
            deleteWorkerForm.addEventListener('submit', (event) => handleFormSubmit(event, 'delete_worker'));
        }
        
        const deleteJobForm = document.getElementById('deleteJobForm');
        if (deleteJobForm) {
            deleteJobForm.addEventListener('submit', (event) => handleFormSubmit(event, 'delete_job'));
        }
        
        const deleteReviewForm = document.getElementById('deleteReviewForm');
        if (deleteReviewForm) {
            deleteReviewForm.addEventListener('submit', (event) => handleFormSubmit(event, 'delete_review'));
        }
        
        // --- AKHIR LOGIKA AJAX FORM SUBMIT ---

        // --- SCRIPT MODAL EDIT & DELETE (SAMA SEPERTI TAHAP 3) ---
        const editWorkerModal = document.getElementById('editWorkerModal'); const editWorkerBtns = document.querySelectorAll('.edit-worker-btn'); const cancelEditWorkerBtn = document.getElementById('cancelEditWorker');
        const deleteWorkerModal = document.getElementById('deleteWorkerModal'); const deleteWorkerBtns = document.querySelectorAll('.delete-worker-btn'); const cancelDeleteWorkerBtn = document.getElementById('cancelDeleteWorker');
        const deleteWorkerNameSpan = document.getElementById('deleteWorkerName');
        
        const deleteJobModal = document.getElementById('deleteJobModal'); const deleteJobBtns = document.querySelectorAll('.delete-job-btn'); const cancelDeleteJobBtn = document.getElementById('cancelDeleteJob');
        const deleteJobTypeSpan = document.getElementById('deleteJobType'); const deleteJobCustomerSpan = document.getElementById('deleteJobCustomer');
        
        const deleteReviewModal = document.getElementById('deleteReviewModal');
        const deleteReviewBtns = document.querySelectorAll('.delete-review-btn');
        const cancelDeleteReviewBtn = document.getElementById('cancelDeleteReview');
        const deleteReviewIdSpan = document.getElementById('deleteReviewId');
        const deleteReviewCustomerSpan = document.getElementById('deleteReviewCustomer');

        // Edit Worker
        editWorkerBtns.forEach(btn => btn.addEventListener('click', function() { /* ... (Logika sama) ... */ const workerId = this.dataset.workerId; const workers = <?php echo json_encode($workers); ?>; const worker = workers.find(w => w.id === workerId); if (worker) { document.getElementById('editWorkerId').value = worker.id; document.getElementById('editWorkerName').value = worker.name; document.getElementById('editWorkerEmail').value = worker.email; document.getElementById('editWorkerPhone').value = worker.phone; document.getElementById('editWorkerLocation').value = worker.location; document.getElementById('editWorkerRate').value = worker.rate; document.getElementById('editWorkerExperience').value = worker.experience || ''; document.getElementById('editWorkerDescription').value = worker.description || ''; document.getElementById('editWorkerStatus').value = worker.status; document.getElementById('currentWorkerPhoto').src = worker.photo; document.getElementById('currentPhotoUrl').textContent = worker.photo; document.getElementById('editWorkerTitle').textContent = 'Edit: ' + worker.name; const skillsSelect = document.getElementById('editWorkerSkills'); Array.from(skillsSelect.options).forEach(option => { option.selected = Array.isArray(worker.skills) && worker.skills.includes(option.value); }); editWorkerModal.classList.remove('hidden'); document.body.style.overflow = 'hidden'; } }));
        
        // Delete Worker (Sama)
        deleteWorkerBtns.forEach(btn => btn.addEventListener('click', function() { const workerId = this.dataset.workerId; const workerName = this.dataset.workerName; deleteWorkerNameSpan.textContent = workerName; document.getElementById('deleteWorkerIdInput').value = workerId; deleteWorkerModal.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }));
        
        // Delete Job (Sama)
        deleteJobBtns.forEach(btn => btn.addEventListener('click', function() { const jobId = this.dataset.jobId; const jobType = this.dataset.jobType; const jobCustomer = this.dataset.jobCustomer; deleteJobTypeSpan.textContent = jobType; deleteJobCustomerSpan.textContent = jobCustomer; document.getElementById('deleteJobIdInput').value = jobId; deleteJobModal.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }));

        // Delete Review (Sama)
        deleteReviewBtns.forEach(btn => { btn.addEventListener('click', function() { const reviewId = this.dataset.reviewId; const customerName = this.dataset.customerName; deleteReviewIdSpan.textContent = reviewId; deleteReviewCustomerSpan.textContent = customerName; document.getElementById('deleteReviewIdInput').value = reviewId; deleteReviewModal.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }); });

        // Close Modals (Sama)
        function closeEditWorkerModal() { if(editWorkerModal) editWorkerModal.classList.add('hidden'); document.body.style.overflow = 'auto'; }
        if(cancelEditWorkerBtn) cancelEditWorkerBtn.addEventListener('click', closeEditWorkerModal);
        function closeDeleteWorkerModal() { if(deleteWorkerModal) deleteWorkerModal.classList.add('hidden'); document.body.style.overflow = 'auto'; }
        if(cancelDeleteWorkerBtn) cancelDeleteWorkerBtn.addEventListener('click', closeDeleteWorkerModal);
        function closeDeleteJobModal() { if(deleteJobModal) deleteJobModal.classList.add('hidden'); document.body.style.overflow = 'auto'; }
        if(cancelDeleteJobBtn) cancelDeleteJobBtn.addEventListener('click', closeDeleteJobModal);
        function closeDeleteReviewModal() { if(deleteReviewModal) deleteReviewModal.classList.add('hidden'); document.body.style.overflow = 'auto'; }
        if(cancelDeleteReviewBtn) cancelDeleteReviewBtn.addEventListener('click', closeDeleteReviewModal);

        // Close modal on overlay click (Sama)
        document.addEventListener('click', function(e) { if (e.target.classList.contains('modal-overlay')) { closeEditWorkerModal(); closeDeleteWorkerModal(); closeDeleteJobModal(); closeDeleteReviewModal(); } });
        // Close modal on Escape key (Sama)
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') { closeEditWorkerModal(); closeDeleteWorkerModal(); closeDeleteJobModal(); closeDeleteReviewModal(); } });

        // AJAX Update Status Job... (DIUBAH UNTUK PAKAI FormData)
        document.querySelectorAll('.job-status-select').forEach(selectElement => { 
            selectElement.addEventListener('change', async function() { // Gunakan async
                const jobId = this.dataset.jobId; 
                const newStatus = this.value; 
                const select = this; 
                const oldClasses = Array.from(select.classList).filter(cls => cls.startsWith('status-')); 
                select.disabled = true; 

                // Buat FormData untuk konsistensi dengan form lain
                const formData = new FormData();
                formData.append('action', 'update_job_status');
                formData.append('job_id', jobId);
                formData.append('status', newStatus);
                formData.append('csrf_token', CSRF_TOKEN); // Kirim token

                try {
                    const response = await fetch('ajax_handler.php', { 
                        method: 'POST', 
                        body: formData // Kirim FormData
                    });

                    if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`); } 
                    
                    const data = await response.json();

                    if (data.success) { 
                        oldClasses.forEach(cls => select.classList.remove(cls)); 
                        select.classList.add(data.newClass); 
                        showAjaxNotification(data.message || 'Status berhasil diupdate!', 'success'); 
                    } else { 
                        showAjaxNotification(data.message || 'Gagal mengupdate status.', 'error'); 
                    } 
                } catch (error) {
                    console.error('Fetch Error:', error); 
                    showAjaxNotification(error.message || 'Terjadi error saat komunikasi dengan server.', 'error'); 
                } finally {
                     select.disabled = false; 
                }
            });
        });

        // Initialize feather icons again
        feather.replace();
    </script>
</body>
</html>