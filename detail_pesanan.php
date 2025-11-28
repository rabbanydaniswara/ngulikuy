<?php
require_once 'functions.php';

redirectIfNotCustomer(); // Pastikan hanya customer yang login bisa akses

// 1. Ambil ID Job dari URL
$jobId = $_GET['id'] ?? null;
$error_message = null;
$jobDetails = null;

if (!$jobId) {
    $error_message = "ID Pesanan tidak valid.";
} else {
    // 2. Lakukan Verifikasi Keamanan
    if (!verifyCustomerOwnsJob($jobId, $_SESSION['user'])) {
        // Jika customer mencoba akses pesanan orang lain
        $error_message = "Anda tidak memiliki akses untuk melihat detail pesanan ini.";
    } else {
        // 3. Ambil Detail Job dari Database
        $jobDetails = getJobById($jobId);
        if (!$jobDetails) {
            $error_message = "Detail pesanan dengan ID '$jobId' tidak ditemukan.";
        } else {
            $worker = null;
            if (!empty($jobDetails['id_pekerja'])) {
                $worker = getWorkerById($jobDetails['id_pekerja']);
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
    <title>Detail Pekerja - Ngulikuy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>

</head>
<body class="min-h-screen flex flex-col">
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center h-10">
                <div class="flex items-center">
                    <a href="customer_dashboard.php" class="flex-shrink-0 flex items-center">
                        <i data-feather="tool" class="text-blue-600"></i>
                        <span class="ml-2 font-bold text-xl">Ngulikuy</span>
                    </a>
                </div>
                <div class="flex items-center">
                    <div class="relative mr-4">
                        <i data-feather="bell" class="text-gray-500 hover:text-blue-600 cursor-pointer w-5 h-5"></i>
                    </div>
                    <div class="relative">
                        <div class="flex items-center space-x-2">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="User" class="w-8 h-8 rounded-full object-cover">
                            <span class="text-sm font-medium text-gray-700"><?php echo $_SESSION['user_name']; ?></span>
                            <a href="login.php?logout=1" class="text-gray-500 hover:text-blue-600 ml-2">
                                <i data-feather="log-out" class="w-5 h-5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow">
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
            <a href="customer_dashboard.php?tab=orders" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i data-feather="arrow-left" class="-ml-1 mr-2 h-5 w-5"></i>
                Kembali ke Pesanan Saya
            </a>
        <?php elseif ($jobDetails): 
            $statusInfo = getStatusTextAndClass($jobDetails['status_pekerjaan']);
        ?>
            <div class="flex items-center mb-2">
                <h1 class="text-4xl font-extrabold text-gray-900">Detail Pekerja #<?php echo htmlspecialchars($jobDetails['id_pekerjaan']); ?></h1>
                <a href="customer_dashboard.php?tab=orders" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200 ml-4">
                    <i data-feather="arrow-left" class="w-4 h-4 mr-1"></i> Kembali ke Pesanan
                </a>
            </div>
            <p class="text-md text-gray-400 mb-6">Dibuat pada: <?php echo date('d M Y, H:i', strtotime($jobDetails['dibuat_pada'])); ?></p>

            <div class="mb-8 flex items-center">
                <span class="inline-flex items-center gap-1 px-4 py-2 text-sm font-semibold rounded-full shadow-md transition-all duration-200 ease-in-out <?php echo $statusInfo['class']; ?>">
                    <?php if ($jobDetails['status_pekerjaan'] == 'pending'): ?>
                        <i data-feather="clock" class="w-4 h-4 mr-1"></i>
                    <?php elseif ($jobDetails['status_pekerjaan'] == 'completed'): ?>
                        <i data-feather="check-circle" class="w-4 h-4 mr-1"></i>
                    <?php else: ?>
                        <i data-feather="info" class="w-4 h-4 mr-1"></i>
                    <?php endif; ?>
                    <?php echo $statusInfo['text']; ?>
                </span>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                 <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900">Informasi Pekerjaan</h3>
                </div>
                <div class="p-6">
                     <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-4 gap-x-6">
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Jenis Pekerjaan</dt>
                            <dd class="mt-1 text-gray-800"><?php echo htmlspecialchars($jobDetails['jenis_pekerjaan']); ?></dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Tanggal Mulai</dt>
                            <dd class="mt-1 text-gray-800"><?php echo date('d M Y', strtotime($jobDetails['tanggal_mulai'])); ?></dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Tanggal Selesai</dt>
                            <dd class="mt-1 text-gray-800"><?php echo date('d M Y', strtotime($jobDetails['tanggal_selesai'])); ?></dd>
                        </div>
                         <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Lokasi Pengerjaan</dt>
                            <dd class="mt-1 text-gray-800"><?php echo nl2br(htmlspecialchars($jobDetails['alamat_lokasi'])); ?></dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Deskripsi / Catatan</dt>
                            <dd class="mt-1 text-gray-800"><?php echo nl2br(htmlspecialchars($jobDetails['deskripsi'] ?: '-')); ?></dd>
                        </div>
                         <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Total Biaya</dt>
                            <dd class="mt-1 text-xl font-semibold text-blue-600"><?php echo formatCurrency($jobDetails['harga']); ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

             <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                 <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900">Informasi Pekerja</h3>
                </div>
                <div class="p-6">
                    <?php if ($jobDetails['id_pekerja'] && $jobDetails['worker_full_name']): ?>
                    <div class="flex items-center space-x-4">
                        <img src="<?php echo htmlspecialchars($jobDetails['worker_photo'] ?: getDefaultWorkerPhoto()); ?>" alt="<?php echo htmlspecialchars($jobDetails['worker_full_name']); ?>" class="w-20 h-20 rounded-full flex-shrink-0 object-cover border-2 border-blue-500 p-0.5">
                        <div>
                            <p class="text-xl font-bold text-gray-900 mb-1 view-worker-btn cursor-pointer hover:underline" data-worker-id="<?php echo htmlspecialchars($jobDetails['id_pekerja']); ?>"><?php echo htmlspecialchars($jobDetails['worker_full_name']); ?></p>
                            <div class="text-sm text-gray-600 space-y-1">
                                <?php if($jobDetails['worker_phone']): ?>
                                <p class="flex items-center"><i data-feather="phone" class="inline w-4 h-4 mr-2 text-blue-500"></i> <?php echo htmlspecialchars($jobDetails['worker_phone']); ?></p>
                                <?php endif; ?>
                                <?php if($jobDetails['worker_email']): ?>
                                <p class="flex items-center"><i data-feather="mail" class="inline w-4 h-4 mr-2 text-blue-500"></i> <?php echo htmlspecialchars($jobDetails['worker_email']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <p class="text-gray-500 py-2">Informasi pekerja tidak tersedia (mungkin pekerja telah dihapus).</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        feather.replace();
    </script>
    <div id="viewWorkerModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity modal-overlay" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="gradient-bg p-6 text-center text-white rounded-t-lg relative">
                    <h2 class="text-xl font-bold">Detail Data Pekerja</h2>
                    <p id="viewWorkerTitle" class="text-blue-100 mt-1"></p>
                    <button type="button" id="closeViewWorkerModalX" class="absolute top-4 right-4 text-blue-100 hover:text-white transition p-1 rounded-full hover:bg-white/10">
                        <i data-feather="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="p-6 modal-content">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="md:col-span-1 flex justify-center">
                            <img id="viewWorkerPhoto" class="photo-preview rounded-full w-40 h-40 object-cover border-4 border-white shadow-lg">
                        </div>
                        <div class="md:col-span-2">
                            <h3 id="viewWorkerName" class="text-2xl font-bold text-gray-800"></h3>
                            <p id="viewWorkerEmail" class="text-sm text-gray-500"></p>
                            <p id="viewWorkerPhone" class="text-sm text-gray-500"></p>
                            <div class="mt-4">
                                <span id="viewWorkerStatus" class="px-3 py-1 text-sm rounded-full"></span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 border-t pt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Lokasi</label>
                            <p id="viewWorkerLocation" class="text-base text-gray-800"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Rate per Hari</label>
                            <p id="viewWorkerRate" class="text-base text-gray-800"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Pengalaman</label>
                            <p id="viewWorkerExperience" class="text-base text-gray-800"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Bergabung pada</label>
                            <p id="viewWorkerJoinDate" class="text-base text-gray-800"></p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Keahlian</label>
                            <div id="viewWorkerSkills" class="flex flex-wrap gap-2"></div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Deskripsi</label>
                            <p id="viewWorkerDescription" class="text-base text-gray-800"></p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
                    <button type="button" id="closeViewWorkerModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        const workers = <?php echo json_encode($worker ? [$worker['id_pekerja'] => $worker] : []); ?>;
        
        const viewWorkerModal = document.getElementById('viewWorkerModal');
        const viewWorkerBtns = document.querySelectorAll('.view-worker-btn');
        const closeViewWorkerModalXBtn = document.getElementById('closeViewWorkerModalX');
        const closeViewWorkerModalBtn = document.getElementById('closeViewWorkerModal');

        function openViewWorkerModal(workerId) {
            const worker = workers[workerId];
            if (worker) {
                document.getElementById('viewWorkerName').textContent = worker.nama;
                document.getElementById('viewWorkerTitle').textContent = 'ID: ' + worker.id_pekerja;
                document.getElementById('viewWorkerEmail').textContent = worker.email;
                document.getElementById('viewWorkerPhone').textContent = worker.telepon;
                document.getElementById('viewWorkerLocation').textContent = worker.lokasi;
                document.getElementById('viewWorkerRate').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(worker.tarif_per_jam) + '/hari';
                document.getElementById('viewWorkerExperience').textContent = worker.pengalaman || '-';
                document.getElementById('viewWorkerJoinDate').textContent = new Date(worker.tanggal_bergabung).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                document.getElementById('viewWorkerDescription').textContent = worker.deskripsi_diri || '-';
                document.getElementById('viewWorkerPhoto').src = worker.url_foto || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&h=150&fit=crop&crop=face';
                
                const statusSpan = document.getElementById('viewWorkerStatus');
                statusSpan.textContent = worker.status_ketersediaan;
                statusSpan.className = 'px-3 py-1 text-sm rounded-full ' + (worker.status_ketersediaan === 'Available' ? 'status-available' : (worker.status_ketersediaan === 'Assigned' ? 'status-assigned' : 'status-on-leave'));

                const skillsContainer = document.getElementById('viewWorkerSkills');
                skillsContainer.innerHTML = '';
                if (worker.keahlian && worker.keahlian.length > 0) {
                    worker.keahlian.forEach(skill => {
                        const skillBadge = document.createElement('span');
                        skillBadge.className = 'bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full';
                        skillBadge.textContent = skill;
                        skillsContainer.appendChild(skillBadge);
                    });
                } else {
                    skillsContainer.textContent = '-';
                }

                viewWorkerModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeViewWorkerModal() {
            if(viewWorkerModal) viewWorkerModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        viewWorkerBtns.forEach(btn => btn.addEventListener('click', function() {
            const workerId = this.dataset.workerId;
            openViewWorkerModal(workerId);
        }));

        if(closeViewWorkerModalXBtn) closeViewWorkerModalXBtn.addEventListener('click', closeViewWorkerModal);
        if(closeViewWorkerModalBtn) closeViewWorkerModalBtn.addEventListener('click', closeViewWorkerModal);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeViewWorkerModal();
            }
        });
    </script>
</body>
</html>