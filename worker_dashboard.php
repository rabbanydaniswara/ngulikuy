<?php
require_once 'functions.php';

// 1. Amankan halaman
redirectIfNotWorker();

// 2. Ambil ID Kuli dari session (yang kita set di Langkah 2A)
$worker_profile_id = $_SESSION['worker_profile_id'];
$worker_name = $_SESSION['user_name'];

// 3. Ambil semua job yang ditugaskan ke kuli ini
$allJobs = getWorkerJobs($worker_profile_id);

// 4. Filter job berdasarkan tab
$active_tab = $_GET['tab'] ?? 'pending';

$filteredJobs = array_filter($allJobs, function($job) use ($active_tab) {
    if ($active_tab === 'pending') {
        return $job['status'] === 'pending';
    }
    if ($active_tab === 'active') {
        return $job['status'] === 'in-progress';
    }
    if ($active_tab === 'completed') {
        return $job['status'] === 'completed' || $job['status'] === 'cancelled';
    }
    return false;
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kuli - NguliKuy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        .gradient-bg { background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%); }
        .nav-active { border-bottom: 2px solid #3b82f6; color: #1f2937; }
        .status-completed { background-color: #dcfce7; color: #166534; }
        .status-in-progress { background-color: #fef3c7; color: #92400e; }
        .status-pending { background-color: #dbeafe; color: #1e40af; }
        .status-cancelled { background-color: #fecaca; color: #dc2626; }
        #ajax-notification { position: fixed; bottom: 20px; right: 20px; padding: 10px 20px; border-radius: 5px; color: white; z-index: 1000; display: none; transition: opacity 0.5s ease-in-out; }
        #ajax-notification.success { background-color: #10B981; }
        #ajax-notification.error { background-color: #EF4444; }
    </style>
</head>
<body class="min-h-screen">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i data-feather="tool" class="text-blue-600"></i>
                        <span class="ml-2 font-bold text-xl">NguliKuy (Kuli)</span>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium">Halo, <?php echo htmlspecialchars($worker_name); ?></span>
                            <a href="index.php?logout=1" class="text-gray-500 hover:text-blue-600">
                                <i data-feather="log-out" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div id="ajax-notification"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-6">Daftar Pekerjaan Anda</h2>
            
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px overflow-x-auto">
                        <a href="?tab=pending" class="flex-shrink-0 <?php echo $active_tab === 'pending' ? 'nav-active' : 'border-transparent text-gray-500 hover:border-gray-300'; ?> px-6 py-4 text-sm font-medium">
                            Tawaran Baru (Pending)
                        </a>
                        <a href="?tab=active" class="flex-shrink-0 <?php echo $active_tab === 'active' ? 'nav-active' : 'border-transparent text-gray-500 hover:border-gray-300'; ?> px-6 py-4 text-sm font-medium">
                            Sedang Berjalan
                        </a>
                        <a href="?tab=completed" class="flex-shrink-0 <?php echo $active_tab === 'completed' ? 'nav-active' : 'border-transparent text-gray-500 hover:border-gray-300'; ?> px-6 py-4 text-sm font-medium">
                            Riwayat (Selesai/Batal)
                        </a>
                    </nav>
                </div>
                
                <div class="p-0 sm:p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Biaya</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($filteredJobs)): ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            Tidak ada pekerjaan di kategori ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($filteredJobs as $job): ?>
                                        <tr id="job-row-<?php echo htmlspecialchars($job['jobId']); ?>">
                                            <td class="px-6 py-4">
                                                <div class="font-medium"><?php echo htmlspecialchars($job['jobType']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($job['location']); ?></div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="font-medium"><?php echo htmlspecialchars($job['customer']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($job['customerPhone']); ?></div>
                                            </td>
                                            <td class="px-6 py-4 text-sm whitespace-nowrap"><?php echo date('d M Y', strtotime($job['startDate'])); ?></td>
                                            <td class="px-6 py-4 font-medium whitespace-nowrap"><?php echo formatCurrency($job['price']); ?></td>
                                            <td class="px-6 py-4">
                                                <span class="status-badge px-2 py-1 text-xs rounded-full whitespace-nowrap <?php echo getStatusClass($job['status'], 'job'); ?>">
                                                    <?php echo htmlspecialchars($job['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex flex-col sm:flex-row gap-2">
                                                    <?php if ($job['status'] === 'pending'): ?>
                                                        <button type="button" class="job-action-btn w-full sm:w-auto text-center px-3 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600" data-action="worker_accept_job" data-job-id="<?php echo htmlspecialchars($job['jobId']); ?>">
                                                            Terima
                                                        </button>
                                                        <button type="button" class="job-action-btn w-full sm:w-auto text-center px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600" data-action="worker_reject_job" data-job-id="<?php echo htmlspecialchars($job['jobId']); ?>">
                                                            Tolak
                                                        </button>
                                                    <?php elseif ($job['status'] === 'in-progress'): ?>
                                                        <button type="button" class="job-action-btn w-full sm:w-auto text-center px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600" data-action="worker_complete_job" data-job-id="<?php echo htmlspecialchars($job['jobId']); ?>">
                                                            Selesaikan
                                                        </button>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    feather.replace();
    
    // CSRF Token
    const CSRF_TOKEN = '<?php echo getCsrfToken(); ?>';

    // Notifikasi AJAX
    const ajaxNotification = document.getElementById('ajax-notification');
    function showAjaxNotification(message, type = 'success') {
        ajaxNotification.textContent = message;
        ajaxNotification.className = '';
        ajaxNotification.classList.add(type === 'success' ? 'success' : 'error');
        ajaxNotification.style.display = 'block';
        ajaxNotification.style.opacity = 1;
        setTimeout(() => {
            ajaxNotification.style.opacity = 0;
            setTimeout(() => { ajaxNotification.style.display = 'none'; }, 500);
        }, 3000);
    }

    // Event listener untuk tombol aksi
    document.querySelectorAll('.job-action-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const action = this.dataset.action;
            const jobId = this.dataset.jobId;
            const row = document.getElementById('job-row-' + jobId);
            
            if (!confirm('Apakah Anda yakin?')) {
                return;
            }

            button.disabled = true;
            button.textContent = 'Memproses...';

            const formData = new FormData();
            formData.append('action', action);
            formData.append('job_id', jobId);
            formData.append('csrf_token', CSRF_TOKEN);

            try {
                const response = await fetch('ajax_handler.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    showAjaxNotification(data.message, 'success');
                    
                    // Update UI
                    const statusBadge = row.querySelector('.status-badge');
                    const actionCell = button.closest('div'); // Ambil wrapper div

                    if (action === 'worker_accept_job') {
                        statusBadge.textContent = 'in-progress';
                        statusBadge.className = 'status-badge px-2 py-1 text-xs rounded-full whitespace-nowrap status-in-progress';
                        // Perbarui tombol di dalam wrapper
                        actionCell.innerHTML = `<button type="button" class="job-action-btn w-full sm:w-auto text-center px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600" data-action="worker_complete_job" data-job-id="${jobId}">Selesaikan</button>`;
                        // Re-attach listener ke tombol baru
                        actionCell.querySelector('.job-action-btn').addEventListener('click', arguments.callee);
                    } else if (action === 'worker_reject_job') {
                        statusBadge.textContent = 'cancelled';
                        statusBadge.className = 'status-badge px-2 py-1 text-xs rounded-full whitespace-nowrap status-cancelled';
                        actionCell.innerHTML = '-';
                    } else if (action === 'worker_complete_job') {
                        statusBadge.textContent = 'completed';
                        statusBadge.className = 'status-badge px-2 py-1 text-xs rounded-full whitespace-nowrap status-completed';
                        actionCell.innerHTML = '-';
                    }
                    
                    // Jika tab-nya 'pending', hapus barisnya setelah diterima/ditolak
                    <?php if ($active_tab === 'pending'): ?>
                    if (action === 'worker_accept_job' || action === 'worker_reject_job') {
                         row.style.opacity = 0.5;
                         setTimeout(() => row.remove(), 500);
                    }
                    <?php endif; ?>
                    
                    // Jika tab-nya 'active', hapus barisnya setelah selesai
                    <?php if ($active_tab === 'active'): ?>
                    if (action === 'worker_complete_job') {
                         row.style.opacity = 0.5;
                         setTimeout(() => row.remove(), 500);
                    }
                    <?php endif; ?>

                } else {
                    showAjaxNotification(data.message, 'error');
                    button.disabled = false;
                    button.textContent = this.textContent; // Kembalikan teks tombol
                }

            } catch (error) {
                showAjaxNotification('Terjadi error: ' + error.message, 'error');
                button.disabled = false;
                button.textContent = this.textContent;
            }
        });
    });
</script>

</body>
</html>