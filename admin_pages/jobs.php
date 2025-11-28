<?php
// File ini dipanggil oleh admin_dashboard.php
if (!defined('IS_ADMIN_PAGE')) {
    die('Akses ditolak!');
}
?>

<div class="bg-white rounded-lg shadow p-6 mb-8">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold">Status Pekerja</h3>
        <div class="flex space-x-4">
            <a href="?tab=add_job"
               class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i data-feather="plus-circle" class="w-4 h-4 mr-2"></i>
                Tambah Pekerjaan
            </a>
        </div>
    </div>

    <div class="w-full">
        <!-- TABLE: tampil di md ke atas (fixed layout supaya tidak melebar) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job ID</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Worker</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job Type</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alamat</th>

                        <!-- price + status + actions : align right so they don't touch text -->
                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($jobs)): ?>
                        <tr>
                            <td colspan="10" class="px-3 py-4 text-center text-gray-500">Belum ada data pekerja.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jobs as $job): ?>
                            <?php $statusClass = getStatusClass($job['status_pekerjaan'], 'job'); ?>
                            <tr>
                                <!-- Job ID -->
                                <td class="px-3 py-3 text-xs font-mono truncate">
                                    <a href="#" class="job-id-link text-blue-600 hover:underline" data-job-id="<?php echo htmlspecialchars($job['id_pekerjaan']); ?>">
                                        <?php echo htmlspecialchars($job['id_pekerjaan']); ?>
                                    </a>
                                </td>

                                <!-- Worker -->
                                <td class="px-3 py-3 align-top">
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium truncate max-w-[160px]" title="<?php echo htmlspecialchars($job['nama_pekerja']); ?>">
                                            <?php echo htmlspecialchars($job['nama_pekerja']); ?>
                                        </div>
                                    </div>
                                </td>

                                <!-- Job Type -->
                                <td class="px-3 py-3 text-sm truncate max-w-[110px]" title="<?php echo htmlspecialchars($job['jenis_pekerjaan']); ?>">
                                    <?php echo htmlspecialchars($job['jenis_pekerjaan']); ?>
                                </td>

                                <!-- Customer (name + phone/email) -->
                                <td class="px-3 py-3 align-top">
                                    <div class="text-sm truncate max-w-[150px]" title="<?php echo htmlspecialchars($job['nama_pelanggan']); ?>">
                                        <?php echo htmlspecialchars($job['nama_pelanggan']); ?>
                                    </div>
                                    <div class="text-xs text-gray-500 truncate max-w-[150px]" title="<?php echo htmlspecialchars($job['telepon_pelanggan'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($job['teleanggan'] ?? ''); ?>
                                    </div>
                                </td>

                                <!-- Dates -->
                                <td class="px-3 py-3 text-sm align-top">
                                    <div class="truncate max-w-[140px]" title="<?php echo date('d M Y', strtotime($job['tanggal_mulai'])) . ' - ' . date('d M Y', strtotime($job['tanggal_selesai'])); ?>">
                                        <?php echo date('d M Y', strtotime($job['tanggal_mulai'])); ?>
                                        <span class="text-xs text-gray-500"> to </span>
                                        <?php echo date('d M Y', strtotime($job['tanggal_selesai'])); ?>
                                    </div>
                                </td>

                                <!-- Location -->
                                <td class="px-3 py-3 text-sm text-gray-700 truncate max-w-[120px]" title="<?php echo htmlspecialchars($job['lokasi']); ?>">
                                    <?php echo htmlspecialchars($job['lokasi']); ?>
                                </td>

                                <!-- Alamat -->
                                <td class="px-3 py-3 text-sm text-gray-700 truncate max-w-[150px]" title="<?php echo htmlspecialchars($job['alamat_lokasi']); ?>">
                                    <?php echo htmlspecialchars($job['alamat_lokasi']); ?>
                                </td>

                                <!-- Price (right aligned, beri spacing agar tidak nempel) -->
                                <td class="px-3 py-3 text-right align-top">
                                    <div class="text-sm font-medium whitespace-nowrap" title="<?php echo formatCurrency($job['harga']); ?>">
                                        <?php echo formatCurrency($job['harga']); ?>
                                    </div>
                                </td>

                                <!-- Status (right aligned, compact pill select inside relative container with caret) -->
                                <td class="px-3 py-3 text-right align-top">
                                    <div class="inline-block relative">
                                        <!-- custom-styled select: appearance-none with caret -->
                                        <select
                                            name="status"
                                            class="job-status-select appearance-none pr-8 pl-3 py-1 text-sm rounded-full border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-200 <?php echo $statusClass; ?>"
                                            data-job-id="<?php echo htmlspecialchars($job['id_pekerjaan']); ?>">
                                            <option value="pending" <?php echo $job['status_pekerjaan'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="in-progress" <?php echo $job['status_pekerjaan'] === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo $job['status_pekerjaan'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $job['status_pekerjaan'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>

                                        <!-- small caret icon -->
                                        <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </td>

                                <!-- Actions (right aligned) -->
                                <td class="px-3 py-3 text-right align-top whitespace-nowrap">
                                    <div class="flex justify-end items-center space-x-2">
                                        <button
                                            type="button"
                                            class="delete-worker-from-job-btn text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 transition duration-200"
                                            data-job-id="<?php echo htmlspecialchars($job['id_pekerjaan']); ?>"
                                            data-worker-name="<?php echo htmlspecialchars($job['nama_pekerja']); ?>"
                                            title="Delete Worker from Job">
                                            <i data-feather="user-x" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- MOBILE: card list (md:hidden) -->
        <!-- NOTE: Wallet-friendly mobile layout: info first, then bottom bar with price / status / actions -->
        <div class="md:hidden space-y-4">
            <?php if (empty($jobs)): ?>
                <div class="px-4 py-4 text-center text-gray-500">Belum ada data pekerja.</div>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <?php $statusClass = getStatusClass($job['status_pekerjaan'], 'job'); ?>
                    <div class="bg-white border rounded-lg p-4 shadow-sm">
                        <!-- main info (title + small meta) -->
                        <div class="min-w-0">
                            <div class="text-base font-semibold truncate" title="<?php echo htmlspecialchars($job['id_pekerjaan'] . ' — ' . $job['jenis_pekerjaan']); ?>">
                                <a href="#" class="job-id-link text-blue-600 hover:underline" data-job-id="<?php echo htmlspecialchars($job['id_pekerjaan']); ?>">
                                    <?php echo htmlspecialchars($job['id_pekerjaan']); ?>
                                </a> — <?php echo htmlspecialchars($job['jenis_pekerjaan']); ?>
                            </div>
                            <div class="text-sm text-gray-700 truncate mt-1" title="<?php echo htmlspecialchars($job['nama_pekerja']); ?>"><?php echo htmlspecialchars($job['nama_pekerja']); ?></div>

                            <div class="mt-2 text-sm text-gray-600 truncate" title="<?php echo htmlspecialchars($job['nama_pelanggan'] . ' • ' . ($job['telepon_pelanggan'] ?? '')); ?>">
                                <?php echo htmlspecialchars($job['nama_pelanggan']); ?> • <?php echo htmlspecialchars($job['telepon_pelanggan'] ?? ''); ?>
                            </div>

                            <div class="mt-2 text-sm text-gray-500 truncate" title="<?php echo date('d M Y', strtotime($job['tanggal_mulai'])) . ' - ' . date('d M Y', strtotime($job['tanggal_selesai'])); ?>">
                                <?php echo date('d M Y', strtotime($job['tanggal_mulai'])); ?> to <?php echo date('d M Y', strtotime($job['tanggal_selesai'])); ?>
                            </div>

                            <div class="mt-2 text-sm text-gray-500 truncate" title="<?php echo htmlspecialchars($job['lokasi']); ?>">
                                <?php echo htmlspecialchars($job['lokasi']); ?>
                            </div>
                            <div class="mt-2 text-sm text-gray-500 truncate" title="<?php echo htmlspecialchars($job['alamat_lokasi']); ?>">
                                <?php echo htmlspecialchars($job['alamat_lokasi']); ?>
                            </div>
                        </div>

                        <!-- bottom row: price | status | actions -->
                        <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <!-- price -->
                            <div class="text-right sm:text-right">
                                <div class="text-lg font-medium whitespace-nowrap"><?php echo formatCurrency($job['harga']); ?></div>
                            </div>

                            <!-- status (on small screens make full width if needed) -->
                            <div class="flex-1 sm:flex-none flex justify-end">
                                <div class="w-full sm:w-auto">
                                    <div class="inline-block relative w-full sm:w-auto">
                                        <select
                                            name="status"
                                            class="job-status-select appearance-none w-full sm:w-auto pr-8 pl-3 py-1 text-sm rounded-full border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-200 <?php echo $statusClass; ?>"
                                            data-job-id="<?php echo htmlspecialchars($job['id_pekerjaan']); ?>">
                                            <option value="pending" <?php echo $job['status_pekerjaan'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="in-progress" <?php echo $job['status_pekerjaan'] === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo $job['status_pekerjaan'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $job['status_pekerjaan'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>

                                        <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- actions -->
                            <div class="flex justify-end items-center">
                                <button
                                    type="button"
                                    class="delete-worker-from-job-btn text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 transition duration-200"
                                    data-job-id="<?php echo htmlspecialchars($job['id_pekerjaan']); ?>"
                                    data-worker-name="<?php echo htmlspecialchars($job['nama_pekerja']); ?>"
                                    title="Delete Worker from Job">
                                    <i data-feather="user-x" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Job Details Modal (Tailwind CSS based) -->
<div id="jobDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3">
            <h3 class="text-xl leading-6 font-medium text-gray-900" id="modalTitle">Detail Pekerjaan</h3>
            <button class="text-gray-400 hover:text-gray-500 close-modal">
                <span class="sr-only">Close</span>
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="mt-2 px-7 py-3" id="modalBody">
            <!-- Job details will be loaded here via AJAX -->
            <p>Loading job details...</p>
        </div>
        <div class="items-center px-4 py-3">
            <button id="okButton" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300 close-modal">
                OK
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const jobDetailsModal = document.getElementById('jobDetailsModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const closeModalButtons = document.querySelectorAll('.close-modal');

    document.querySelectorAll('.job-id-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const jobId = this.dataset.jobId;
            modalTitle.textContent = `Detail Pekerjaan: ${jobId}`;
            modalBody.innerHTML = `
                <div class="flex items-center justify-center p-8">
                    <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="ml-3 text-gray-600">Memuat detail pekerjaan...</p>
                </div>
            `; // Loading message with spinner
            jobDetailsModal.classList.remove('hidden');

            // Helper function to get status text and class for styling
            function getJobStatusTextAndClass(status) {
                let text = 'Tidak Diketahui';
                let classNames = 'bg-gray-200 text-gray-800'; // Default classes

                switch (status) {
                    case 'pending':
                        text = 'Menunggu Konfirmasi';
                        classNames = 'bg-yellow-100 text-yellow-800';
                        break;
                    case 'in-progress':
                        text = 'Sedang Dikerjakan';
                        classNames = 'bg-blue-100 text-blue-800';
                        break;
                    case 'completed':
                        text = 'Selesai';
                        classNames = 'bg-green-100 text-green-800';
                        break;
                    case 'cancelled':
                        text = 'Dibatalkan';
                        classNames = 'bg-red-100 text-red-800';
                        break;
                }
                return { text, classNames };
            }

            // Function to format date
            function formatDate(dateString) {
                if (!dateString) return '-';
                try {
                    return new Date(dateString).toLocaleDateString('id-ID', {
                        year: 'numeric', month: 'long', day: 'numeric'
                    });
                } catch (e) {
                    return dateString; // Fallback if date is invalid
                }
            }

            // Function to format currency
            function formatCurrency(amount) {
                if (typeof amount !== 'number') amount = parseFloat(amount);
                if (isNaN(amount)) return '-';
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency', currency: 'IDR'
                }).format(amount);
            }

            // Fetch job details via AJAX
            fetch('ajax_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_job_details&job_id=' + encodeURIComponent(jobId) + '&csrf_token=' + encodeURIComponent(CSRF_TOKEN)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.job) {
                    const job = data.job;
                    const statusInfo = getJobStatusTextAndClass(job.status_pekerjaan);

                    let detailsHtml = `
                        <div class="space-y-6">
                            <div class="border-b pb-4">
                                <h4 class="text-lg font-semibold flex items-center text-gray-800">
                                    <i data-feather="briefcase" class="w-5 h-5 mr-2 text-blue-500"></i> Informasi Pekerjaan
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2 mt-3 text-sm">
                                    <div><span class="font-medium text-gray-600">ID Pekerjaan:</span> <span class="font-mono">${job.id_pekerjaan}</span></div>
                                    <div><span class="font-medium text-gray-600">Jenis Pekerjaan:</span> ${job.jenis_pekerjaan}</div>
                                    <div class="md:col-span-2">
                                        <span class="font-medium text-gray-600">Deskripsi:</span> ${job.deskripsi || '-'}
                                    </div>
                                    <div><span class="font-medium text-gray-600">Harga:</span> ${formatCurrency(job.harga)}</div>
                                    <div>
                                        <span class="font-medium text-gray-600">Status:</span>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold ${statusInfo.classNames}">${statusInfo.text}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="border-b pb-4">
                                <h4 class="text-lg font-semibold flex items-center text-gray-800">
                                    <i data-feather="tool" class="w-5 h-5 mr-2 text-green-500"></i> Detail Pekerja
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2 mt-3 text-sm">
                                    <div><span class="font-medium text-gray-600">Nama Pekerja:</span> ${job.nama_pekerja || '-'}</div>
                                    <div><span class="font-medium text-gray-600">Telepon Pekerja:</span> ${job.worker_phone || '-'}</div>
                                    <div class="md:col-span-2"><span class="font-medium text-gray-600">Email Pekerja:</span> ${job.worker_email || '-'}</div>
                                    ${job.worker_photo ? `
                                        <div class="md:col-span-2">
                                            <span class="font-medium text-gray-600">Foto Pekerja:</span>
                                            <img src="${job.worker_photo}" alt="Foto Pekerja" class="w-24 h-24 object-cover rounded-full mt-2 border border-gray-200">
                                        </div>
                                    ` : ''}
                                </div>
                            </div>

                            <div class="border-b pb-4">
                                <h4 class="text-lg font-semibold flex items-center text-gray-800">
                                    <i data-feather="user" class="w-5 h-5 mr-2 text-purple-500"></i> Detail Pelanggan
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2 mt-3 text-sm">
                                    <div><span class="font-medium text-gray-600">Nama Pelanggan:</span> ${job.nama_pelanggan || '-'}</div>
                                    <div><span class="font-medium text-gray-600">Telepon Pelanggan:</span> ${job.telepon_pelanggan || '-'}</div>
                                    <div class="md:col-span-2"><span class="font-medium text-gray-600">Email Pelanggan:</span> ${job.email_pelanggan || '-'}</div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-lg font-semibold flex items-center text-gray-800">
                                    <i data-feather="map-pin" class="w-5 h-5 mr-2 text-red-500"></i> Lokasi & Tanggal
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2 mt-3 text-sm">
                                    <div><span class="font-medium text-gray-600">Lokasi:</span> ${job.lokasi || '-'}</div>
                                    <div class="md:col-span-2"><span class="font-medium text-gray-600">Alamat Lengkap:</span> ${job.alamat_lokasi || '-'}</div>
                                    <div><span class="font-medium text-gray-600">Mulai:</span> ${formatDate(job.tanggal_mulai)}</div>
                                    <div><span class="font-medium text-gray-600">Selesai:</span> ${formatDate(job.tanggal_selesai)}</div>
                                    <div><span class="font-medium text-gray-600">Dibuat Pada:</span> ${formatDate(job.dibuat_pada)}</div>
                                    <div><span class="font-medium text-gray-600">Diperbarui Pada:</span> ${formatDate(job.diperbarui_pada)}</div>
                                </div>
                            </div>
                        </div>
                    `;
                    modalBody.innerHTML = detailsHtml;
                    feather.replace(); // Re-initialize feather icons for newly added HTML
                } else {
                    modalBody.innerHTML = `<p class="text-red-500">${data.message || 'Gagal memuat detail pekerjaan.'}</p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching job details:', error);
                modalBody.innerHTML = '<p class="text-red-500">Terjadi kesalahan saat memuat detail.</p>';
            });
        });
    });

    closeModalButtons.forEach(button => {
        button.addEventListener('click', function() {
            jobDetailsModal.classList.add('hidden');
        });
    });

    // Close modal if clicked outside
    jobDetailsModal.addEventListener('click', function(e) {
        if (e.target === jobDetailsModal) {
            jobDetailsModal.classList.add('hidden');
        }
    });
});
</script>