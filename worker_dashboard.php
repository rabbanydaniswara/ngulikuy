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
        
        /* Gaya Modal */
        #actionModal { transition: opacity 0.3s ease-out; }
        #modal-content { 
            transition: all 0.3s ease-out; 
            transform: translateY(20px); 
            opacity: 0;
        }
        #actionModal:not(.hidden) { opacity: 1; }
        #actionModal:not(.hidden) #modal-content { 
            transform: translateY(0); 
            opacity: 1;
        }
        
        /* * PERBAIKAN: 
         * Menghapus CSS .btn-loading dan .btn-loading.flex 
         * Kita akan mengandalkan kelas 'hidden' dari Tailwind 
        */
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
                                                        <button type="button" class="job-modal-trigger flex items-center justify-center w-full sm:w-auto text-center px-3 py-1.5 bg-green-100 text-green-700 text-xs rounded-full hover:bg-green-200 transition"
                                                                data-action="worker_accept_job" data-job-id="<?php echo htmlspecialchars($job['jobId']); ?>"
                                                                data-job-type="<?php echo htmlspecialchars($job['jobType']); ?>" data-job-customer="<?php echo htmlspecialchars($job['customer']); ?>">
                                                            <i data-feather="check-circle" class="w-4 h-4 mr-1"></i>
                                                            Terima
                                                        </button>
                                                        <button type="button" class="job-modal-trigger flex items-center justify-center w-full sm:w-auto text-center px-3 py-1.5 bg-red-100 text-red-700 text-xs rounded-full hover:bg-red-200 transition"
                                                                data-action="worker_reject_job" data-job-id="<?php echo htmlspecialchars($job['jobId']); ?>"
                                                                data-job-type="<?php echo htmlspecialchars($job['jobType']); ?>" data-job-customer="<?php echo htmlspecialchars($job['customer']); ?>">
                                                            <i data-feather="x-circle" class="w-4 h-4 mr-1"></i>
                                                            Tolak
                                                        </button>
                                                    <?php elseif ($job['status'] === 'in-progress'): ?>
                                                        <button type="button" class="job-modal-trigger flex items-center justify-center w-full sm:w-auto text-center px-3 py-1.5 bg-blue-100 text-blue-700 text-xs rounded-full hover:bg-blue-200 transition"
                                                                data-action="worker_complete_job" data-job-id="<?php echo htmlspecialchars($job['jobId']); ?>"
                                                                data-job-type="<?php echo htmlspecialchars($job['jobType']); ?>" data-job-customer="<?php echo htmlspecialchars($job['customer']); ?>">
                                                            <i data-feather="check-square" class="w-4 h-4 mr-1"></i>
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

<div id="actionModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        
        <div id="modal-overlay" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div id="modal-content" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div id="modal-icon-container" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i id="modal-icon" data-feather="info" class="h-6 w-6 text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Konfirmasi Tindakan</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="modal-description">Apakah Anda yakin?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="modal-confirm-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                    <span class="btn-text">Konfirmasi</span>
                    <span class="btn-loading hidden items-center">
                        <i data-feather="loader" class="animate-spin -ml-1 mr-2 h-5 w-5"></i>
                        Memproses...
                    </span>
                </button>
                <button type="button" id="modal-cancel-btn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                    Batal
                </button>
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
    
    // --- LOGIKA MODAL BARU ---

    // Ambil elemen-elemen modal
    const actionModal = document.getElementById('actionModal');
    const modalOverlay = document.getElementById('modal-overlay');
    const modalContent = document.getElementById('modal-content');
    const modalTitle = document.getElementById('modal-title');
    const modalDescription = document.getElementById('modal-description');
    const modalConfirmBtn = document.getElementById('modal-confirm-btn');
    const modalCancelBtn = document.getElementById('modal-cancel-btn');
    const modalIcon = document.getElementById('modal-icon');
    const modalIconContainer = document.getElementById('modal-icon-container');

    // Fungsi untuk menutup modal
    function closeModal() {
        actionModal.classList.add('hidden');
    }

    // Listener untuk tombol-tombol pemicu modal
    // Kita buat fungsi ini agar bisa dipanggil ulang
    function attachModalListeners() {
        document.querySelectorAll('.job-modal-trigger').forEach(button => {
            // Hapus listener lama agar tidak duplikat
            button.removeEventListener('click', openModalHandler);
            // Tambah listener baru
            button.addEventListener('click', openModalHandler);
        });
    }
    
    function openModalHandler() {
        const action = this.dataset.action;
        const jobId = this.dataset.jobId;
        const jobType = this.dataset.jobType;
        const jobCustomer = this.dataset.jobCustomer;

        let title, description, confirmText, confirmClass, iconName, iconClass, iconContainerClass;

        switch (action) {
            case 'worker_accept_job':
                title = 'Terima Pekerjaan?';
                description = `Anda akan menerima pekerjaan <strong>${jobType}</strong> dari customer <strong>${jobCustomer}</strong>. Lanjutkan?`;
                confirmText = 'Ya, Terima';
                confirmClass = 'bg-green-600 hover:bg-green-700';
                iconName = 'check-circle';
                iconClass = 'text-green-600';
                iconContainerClass = 'bg-green-100';
                break;
            case 'worker_reject_job':
                title = 'Tolak Pekerjaan?';
                description = `Anda akan menolak pekerjaan <strong>${jobType}</strong> dari customer <strong>${jobCustomer}</strong>. Tindakan ini tidak dapat dibatalkan.`;
                confirmText = 'Ya, Tolak';
                confirmClass = 'bg-red-600 hover:bg-red-700';
                iconName = 'x-circle';
                iconClass = 'text-red-600';
                iconContainerClass = 'bg-red-100';
                break;
            case 'worker_complete_job':
                title = 'Selesaikan Pekerjaan?';
                description = `Konfirmasi bahwa pekerjaan <strong>${jobType}</strong> untuk <strong>${jobCustomer}</strong> telah selesai.`;
                confirmText = 'Ya, Selesaikan';
                confirmClass = 'bg-blue-600 hover:bg-blue-700';
                iconName = 'check-square';
                iconClass = 'text-blue-600';
                iconContainerClass = 'bg-blue-100';
                break;
        }

        // Isi konten modal
        modalTitle.textContent = title;
        modalDescription.innerHTML = description;
        
        // Set tombol konfirmasi
        const btnText = modalConfirmBtn.querySelector('.btn-text');
        if (btnText) btnText.textContent = confirmText;
        
        // Hapus kelas warna lama & tambahkan yang baru
        modalConfirmBtn.className = modalConfirmBtn.className.replace(/bg-\w+-600/g, '').replace(/hover:bg-\w+-700/g, '');
        modalConfirmBtn.classList.add(...confirmClass.split(' '));
        
        // Set ikon
        modalIcon.setAttribute('data-feather', iconName);
        modalIcon.className = `h-6 w-6 ${iconClass}`;
        modalIconContainer.className = `mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10 ${iconContainerClass}`;
        feather.replace(); // Render ikon baru

        // Simpan data di tombol konfirmasi untuk dipakai nanti
        modalConfirmBtn.dataset.action = action;
        modalConfirmBtn.dataset.jobId = jobId;

        // Tampilkan modal
        actionModal.classList.remove('hidden');
    }

    // Panggil fungsi attach listener saat halaman dimuat
    attachModalListeners();


    // Listener untuk tombol Batal di modal
    modalCancelBtn.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', closeModal);

    // Listener untuk tombol Konfirmasi di modal
    modalConfirmBtn.addEventListener('click', async function() {
        const action = this.dataset.action;
        const jobId = this.dataset.jobId;
        const row = document.getElementById('job-row-' + jobId);
        
        const btnText = this.querySelector('.btn-text');
        const btnLoading = this.querySelector('.btn-loading');

        // Tampilkan loading
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden'); // <-- PERBAIKAN 1: Hapus 'hidden'
        btnLoading.classList.add('flex');      // <-- PERBAIKAN 2: Tambah 'flex'
        this.disabled = true;
        modalCancelBtn.disabled = true; // Nonaktifkan tombol batal saat loading

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

            closeModal(); // Tutup modal baik sukses atau gagal

            if (data.success) {
                showAjaxNotification(data.message, 'success');
                
                // Update UI (Logika ini sama seperti sebelumnya)
                const statusBadge = row.querySelector('.status-badge');
                const actionCell = row.querySelector('td:last-child div'); // Ambil wrapper div

                if (action === 'worker_accept_job') {
                    statusBadge.textContent = 'in-progress';
                    statusBadge.className = 'status-badge px-2 py-1 text-xs rounded-full whitespace-nowrap status-in-progress';
                    // Perbarui tombol di dalam wrapper
                    actionCell.innerHTML = `<button type="button" class="job-modal-trigger flex items-center justify-center w-full sm:w-auto text-center px-3 py-1.5 bg-blue-100 text-blue-700 text-xs rounded-full hover:bg-blue-200 transition"
                                                data-action="worker_complete_job" data-job-id="${jobId}"
                                                data-job-type="${row.querySelector('td:first-child div:first-child').textContent}" 
                                                data-job-customer="${row.querySelector('td:nth-child(2) div:first-child').textContent}">
                                            <i data-feather="check-square" class="w-4 h-4 mr-1"></i>
                                            Selesaikan
                                        </button>`;
                    // Re-attach listener ke tombol baru
                    attachModalListeners(); // Panggil fungsi utama lagi
                    feather.replace();
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
            }

        } catch (error) {
            closeModal();
            showAjaxNotification('Terjadi error: ' + error.message, 'error');
        } finally {
            // Sembunyikan loading & aktifkan tombol
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');   // <-- PERBAIKAN 3: Tambah 'hidden'
            btnLoading.classList.remove('flex');  // <-- PERBAIKAN 4: Hapus 'flex'
            this.disabled = false;
            modalCancelBtn.disabled = false;
        }
    });

</script>

</body>
</html>