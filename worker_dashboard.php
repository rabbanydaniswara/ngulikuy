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
        return ($job['status'] ?? '') === 'pending';
    }
    if ($active_tab === 'active') {
        return ($job['status'] ?? '') === 'in-progress';
    }
    if ($active_tab === 'completed') {
        $s = $job['status'] ?? '';
        return $s === 'completed' || $s === 'cancelled';
    }
    return false;
});
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kuli - NguliKuy</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Feather icons -->
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        .nav-active { border-bottom: 2px solid #3b82f6; color: #1f2937; }
        .status-completed { background-color: #dcfce7; color: #166534; }
        .status-in-progress { background-color: #fef3c7; color: #92400e; }
        .status-pending { background-color: #dbeafe; color: #1e40af; }
        .status-cancelled { background-color: #fecaca; color: #dc2626; }

        /* Notification box */
        #ajax-notification { position: fixed; bottom: 20px; right: 20px; padding: 10px 20px; border-radius: 6px; color: white; z-index: 1000; display: none; transition: opacity 0.4s ease-in-out; }
        #ajax-notification.success { background-color: #10B981; }
        #ajax-notification.error { background-color: #EF4444; }

        /* Modal transitions */
        #actionModal { transition: opacity 0.25s ease-out; }
        #modal-content { transition: transform 0.25s ease-out, opacity 0.25s ease-out; transform: translateY(12px); opacity: 0; }
        #actionModal:not(.hidden) { opacity: 1; }
        #actionModal:not(.hidden) #modal-content { transform: translateY(0); opacity: 1; }

        /* Small helpers */
        .truncate-multiline { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    
/* ---------- Responsive table fixes ---------- */
.table-wrapper { overflow: visible; } /* allow stable layout */
.responsive-table {
  width: 100%;
  table-layout: fixed; /* force columns widths from colgroup */
  border-collapse: collapse;
  white-space: normal;
}
.responsive-table th, .responsive-table td {
  padding: 0.75rem;
  vertical-align: top;
  font-size: 0.95rem;
}
.responsive-table col.col-job { width: 20%; }
.responsive-table col.col-customer { width: 15%; }
.responsive-table col.col-date { width: 12%; }
.responsive-table col.col-price { width: 12%; }
.responsive-table col.col-status { width: 10%; }
.responsive-table col.col-desc { width: 26%; }
.responsive-table col.col-actions { width: 100px; }
.responsive-table td.desc {
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
@media (max-width: 768px) {
  .responsive-table td.desc { white-space: normal; }
}
.status-badge {
  display: inline-block;
  min-width: 74px;
  text-align: center;
  padding-left: 0.5rem;
  padding-right: 0.5rem;
  padding: .25rem .5rem;
  border-radius: 9999px;
}
.responsive-table td.actions {
  text-align: right;
  white-space: nowrap;
}
.table-outer {
  overflow-x: hidden;
}
.responsive-table th.actions-col,
.responsive-table td.actions {
  position: sticky;
  right: 0;
  background: #fff;
  z-index: 2;
}
td.price { font-weight: 600; }
.table-outer::-webkit-scrollbar { height: 8px; }
.table-outer::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 999px; }

/* Actions vertical stack - desktop stacked, mobile row */
.actions-buttons {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 6px;
}
.actions-buttons button {
    min-width: 90px;
    text-align: center;
}
@media (max-width: 768px) {
    .actions-buttons {
        flex-direction: row !important;
        align-items: center;
        justify-content: flex-end;
    }
    .actions-buttons button { min-width: auto; }
}

</style>
</head>
<body class="min-h-screen">

    <!-- NAV -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i data-feather="tool" class="text-blue-600"></i>
                        <span class="ml-3 font-bold text-xl">NguliKuy (Kuli)</span>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-medium">Halo, <?php echo htmlspecialchars($worker_name); ?></span>
                            <a href="index.php?logout=1" class="text-gray-500 hover:text-blue-600" title="Logout">
                                <i data-feather="log-out" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- AJAX notification -->
    <div id="ajax-notification" role="status" aria-live="polite"></div>

    <!-- CONTENT -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <header class="mb-8">
            <h1 class="text-2xl font-bold">Daftar Pekerjaan Anda</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola tawaran, pekerjaan yang sedang berjalan, dan riwayat pekerjaan Anda.</p>
        </header>

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
                    <!-- Desktop table -->
                    
<div class="table-outer">
  <div class="table-wrapper bg-white rounded-b-lg">
    <table class="responsive-table hidden md:table divide-y divide-gray-200">
      <colgroup>
        <col class="col-job" />
        <col class="col-customer" />
        <col class="col-date" />
        <col class="col-price" />
        <col class="col-status" />
        <col class="col-desc" />
        <col class="col-actions" />
      </colgroup>
      <thead>
        <tr>
          <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job</th>
          <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
          <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
          <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Biaya</th>
          <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
          <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi / Catatan</th>
          <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase actions-col">Aksi</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php if (empty($filteredJobs)): ?>
          <tr>
            <td colspan="7" class="px-3 py-6 text-center text-gray-500">Tidak ada pekerjaan di kategori ini.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($filteredJobs as $job):
              $jobId = htmlspecialchars($job['jobId'] ?? $job['id'] ?? '');
              $jobType = htmlspecialchars($job['jobType'] ?? '-');
              $location = htmlspecialchars($job['location'] ?? '-');
              $customer = htmlspecialchars($job['customer'] ?? ($job['customer_name'] ?? '-'));
              $customerPhone = htmlspecialchars($job['customerPhone'] ?? ($job['customer_phone'] ?? '-'));
              $startDate = !empty($job['startDate']) ? date('d M Y', strtotime($job['startDate'])) : '-';
              $price = function_exists('formatCurrency') ? formatCurrency($job['price'] ?? 0) : 'Rp ' . number_format($job['price'] ?? 0,0,',','.');
              $status = htmlspecialchars($job['status'] ?? 'pending');
              $notes = htmlspecialchars($job['notes'] ?? $job['description'] ?? '-');
          ?>
          <tr id="job-row-<?php echo $jobId; ?>">
            <td class="px-3 py-3 align-top">
              <div class="font-medium"><?php echo $jobType; ?></div>
              <div class="text-sm text-gray-500"><?php echo $location; ?></div>
            </td>

            <td class="px-3 py-3 align-top">
              <div class="font-medium"><?php echo $customer; ?></div>
              <div class="text-sm text-gray-500"><?php echo $customerPhone; ?></div>
              <div class="text-sm text-gray-500"><?php echo htmlspecialchars($job['address'] ?? ''); ?></div>
            </td>

            <td class="px-3 py-3 text-sm whitespace-nowrap align-top"><?php echo $startDate; ?></td>

            <td class="px-3 py-3 price align-top"><?php echo $price; ?></td>

            <td class="px-3 py-3 align-top">
              <span class="status-badge <?php echo getStatusClass($status, 'job'); ?>"><?php echo $status; ?></span>
            </td>

            <td class="px-3 py-3 desc text-sm text-gray-600 align-top" title="<?php echo $notes; ?>">
              <?php echo $notes; ?>
            </td>

            <td class="px-3 py-3 actions align-top">
              <div class="actions-buttons">
                <?php if (($job['status'] ?? '') === 'pending'): ?>
                  <button type="button" class="job-modal-trigger px-4 py-1.5 flex items-center gap-2 rounded-full bg-green-100 text-green-700 text-xs font-medium hover:bg-green-200 transition shadow-sm"
                      data-action="worker_accept_job" data-job-id="<?php echo $jobId; ?>"
                      data-job-type="<?php echo $jobType; ?>" data-job-customer="<?php echo $customer; ?>">
                    <i data-feather="check-circle" class="w-4 h-4"></i>
                    Terima
                  </button>

                  <button type="button" class="job-modal-trigger px-4 py-1.5 flex items-center gap-2 rounded-full bg-red-100 text-red-700 text-xs font-medium hover:bg-red-200 transition shadow-sm"
                      data-action="worker_reject_job" data-job-id="<?php echo $jobId; ?>"
                      data-job-type="<?php echo $jobType; ?>" data-job-customer="<?php echo $customer; ?>">
                    <i data-feather="x-circle" class="w-4 h-4"></i>
                    Tolak
                  </button>

                <?php elseif (($job['status'] ?? '') === 'in-progress'): ?>

                  <button type="button" class="job-modal-trigger px-4 py-1.5 flex items-center gap-2 rounded-full bg-blue-100 text-blue-700 text-xs font-medium hover:bg-blue-200 transition shadow-sm"
                      data-action="worker_complete_job" data-job-id="<?php echo $jobId; ?>"
                      data-job-type="<?php echo $jobType; ?>" data-job-customer="<?php echo $customer; ?>">
                    <i data-feather="check-square" class="w-4 h-4"></i>
                    Selesaikan
                  </button>

                <?php else: ?>
                  <div class="text-xs text-gray-500">-</div>
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


                    <!-- Mobile cards -->
                    <div class="md:hidden space-y-4">
                        <?php if (empty($filteredJobs)): ?>
                            <div class="px-4 py-4 text-center text-gray-500">Tidak ada pekerjaan di kategori ini.</div>
                        <?php else: ?>
                            <?php foreach ($filteredJobs as $job):
                                $jobId = htmlspecialchars($job['jobId'] ?? $job['id'] ?? '');
                                $jobType = htmlspecialchars($job['jobType'] ?? '-');
                                $location = htmlspecialchars($job['location'] ?? '-');
                                $customer = htmlspecialchars($job['customer'] ?? ($job['customer_name'] ?? '-'));
                                $customerPhone = htmlspecialchars($job['customerPhone'] ?? ($job['customer_phone'] ?? '-'));
                                $startDate = !empty($job['startDate']) ? date('d M Y', strtotime($job['startDate'])) : '-';
                                $price = function_exists('formatCurrency') ? formatCurrency($job['price'] ?? 0) : 'Rp ' . number_format($job['price'] ?? 0,0,',','.');
                                $status = htmlspecialchars($job['status'] ?? 'pending');
                                $notes = htmlspecialchars($job['notes'] ?? $job['description'] ?? '-');
                            ?>
                                <div id="job-row-<?php echo $jobId; ?>" class="bg-white border rounded-lg p-4 shadow-sm">
                                    <div class="flex items-start justify-between">
                                        <div class="min-w-0 pr-3">
                                            <div class="text-base font-semibold truncate"><?php echo $jobType; ?></div>
                                            <div class="text-sm text-gray-700 truncate"><?php echo $location; ?></div>
                                            <div class="text-sm text-gray-700 truncate"><?php echo htmlspecialchars($job['address'] ?? ''); ?></div>
                                            <div class="mt-1 text-sm text-gray-600 truncate"><?php echo $customer; ?> • <?php echo $customerPhone; ?></div>
                                            <div class="mt-2 text-sm text-gray-500"><?php echo $startDate; ?></div>
                                            <div class="mt-2 text-sm text-gray-600 break-words"><strong>Catatan:</strong> <?php echo $notes; ?></div>
                                        </div>

                                        <div class="flex-shrink-0 w-36 text-right">
                                            <div class="text-lg font-medium"><?php echo $price; ?></div>

                                            <div class="mt-3 inline-block relative">
                                                <span class="status-badge inline-block px-3 py-1 text-xs rounded-full <?php echo getStatusClass($status, 'job'); ?>"><?php echo $status; ?></span>
                                            </div>

                                            <div class="mt-3 flex justify-end space-x-2">
                                                <?php if (($job['status'] ?? '') === 'pending'): ?>
                                                    <button type="button" class="job-modal-trigger text-green-600 bg-green-50 p-2 rounded-full"
                                                        data-action="worker_accept_job" data-job-id="<?php echo $jobId; ?>"
                                                        data-job-type="<?php echo $jobType; ?>" data-job-customer="<?php echo $customer; ?>">
                                                        <i data-feather="check-circle" class="w-5 h-5"></i>
                                                    </button>
                                                    <button type="button" class="job-modal-trigger text-red-600 bg-red-50 p-2 rounded-full"
                                                        data-action="worker_reject_job" data-job-id="<?php echo $jobId; ?>"
                                                        data-job-type="<?php echo $jobType; ?>" data-job-customer="<?php echo $customer; ?>">
                                                        <i data-feather="x-circle" class="w-5 h-5"></i>
                                                    </button>
                                                <?php elseif (($job['status'] ?? '') === 'in-progress'): ?>
                                                    <button type="button" class="job-modal-trigger text-blue-600 bg-blue-50 p-2 rounded-full"
                                                        data-action="worker_complete_job" data-job-id="<?php echo $jobId; ?>"
                                                        data-job-type="<?php echo $jobType; ?>" data-job-customer="<?php echo $customer; ?>">
                                                        <i data-feather="check-square" class="w-5 h-5"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <div class="text-xs text-gray-500">-</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- ACTION MODAL -->
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

    <!-- SCRIPTS -->
    <script>
        // Render feather icons
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

        // Modal elements
        const actionModal = document.getElementById('actionModal');
        const modalOverlay = document.getElementById('modal-overlay');
        const modalContent = document.getElementById('modal-content');
        const modalTitle = document.getElementById('modal-title');
        const modalDescription = document.getElementById('modal-description');
        const modalConfirmBtn = document.getElementById('modal-confirm-btn');
        const modalCancelBtn = document.getElementById('modal-cancel-btn');
        const modalIcon = document.getElementById('modal-icon');
        const modalIconContainer = document.getElementById('modal-icon-container');

        function closeModal() {
            actionModal.classList.add('hidden');
        }

        // Attach modal listeners to buttons with .job-modal-trigger
        function attachModalListeners() {
            document.querySelectorAll('.job-modal-trigger').forEach(button => {
                // remove old listener (safe)
                button.removeEventListener('click', openModalHandler);
                button.addEventListener('click', openModalHandler);
            });
        }

        function openModalHandler() {
            const action = this.dataset.action;
            const jobId = this.dataset.jobId;
            const jobType = this.dataset.jobType;
            const jobCustomer = this.dataset.jobCustomer;

            let title = 'Konfirmasi Tindakan';
            let description = 'Apakah Anda yakin?';
            let confirmText = 'Konfirmasi';
            let confirmClass = 'bg-blue-600 hover:bg-blue-700';
            let iconName = 'info';
            let iconClass = 'text-blue-600';
            let iconContainerClass = 'bg-blue-100';

            if (action === 'worker_accept_job') {
                title = 'Terima Pekerjaan?';
                description = `Anda akan menerima pekerjaan <strong>${jobType}</strong> dari customer <strong>${jobCustomer}</strong>. Lanjutkan?`;
                confirmText = 'Ya, Terima';
                confirmClass = 'bg-green-600 hover:bg-green-700';
                iconName = 'check-circle';
                iconClass = 'text-green-600';
                iconContainerClass = 'bg-green-100';
            } else if (action === 'worker_reject_job') {
                title = 'Tolak Pekerjaan?';
                description = `Anda akan menolak pekerjaan <strong>${jobType}</strong> dari customer <strong>${jobCustomer}</strong>. Tindakan ini tidak dapat dibatalkan.`;
                confirmText = 'Ya, Tolak';
                confirmClass = 'bg-red-600 hover:bg-red-700';
                iconName = 'x-circle';
                iconClass = 'text-red-600';
                iconContainerClass = 'bg-red-100';
            } else if (action === 'worker_complete_job') {
                title = 'Selesaikan Pekerjaan?';
                description = `Konfirmasi bahwa pekerjaan <strong>${jobType}</strong> untuk <strong>${jobCustomer}</strong> telah selesai.`;
                confirmText = 'Ya, Selesaikan';
                confirmClass = 'bg-blue-600 hover:bg-blue-700';
                iconName = 'check-square';
                iconClass = 'text-blue-600';
                iconContainerClass = 'bg-blue-100';
            }

            // Populate modal
            modalTitle.textContent = title;
            modalDescription.innerHTML = description;

            const btnText = modalConfirmBtn.querySelector('.btn-text');
            if (btnText) btnText.textContent = confirmText;

            // replace bg classes (simple way: remove known patterns)
            modalConfirmBtn.className = modalConfirmBtn.className.replace(/\bbg-\S+\b/g, '').replace(/\bhover:bg-\S+\b/g, '');
            modalConfirmBtn.classList.add(...confirmClass.split(' '));

            // icon
            modalIcon.setAttribute('data-feather', iconName);
            modalIcon.className = `h-6 w-6 ${iconClass}`;
            modalIconContainer.className = `mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10 ${iconContainerClass}`;
            feather.replace();

            modalConfirmBtn.dataset.action = action;
            modalConfirmBtn.dataset.jobId = jobId;

            actionModal.classList.remove('hidden');
        }

        attachModalListeners();

        modalCancelBtn.addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', closeModal);

        modalConfirmBtn.addEventListener('click', async function() {
            const action = this.dataset.action;
            const jobId = this.dataset.jobId;
            const row = document.getElementById('job-row-' + jobId);

            const btnText = this.querySelector('.btn-text');
            const btnLoading = this.querySelector('.btn-loading');

            // Show loading
            if (btnText) btnText.classList.add('hidden');
            if (btnLoading) { btnLoading.classList.remove('hidden'); btnLoading.classList.add('flex'); }
            this.disabled = true;
            modalCancelBtn.disabled = true;

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

                closeModal();

                if (data.success) {
                    showAjaxNotification(data.message, 'success');

                    if (row) {
                        const statusBadge = row.querySelector('.status-badge');
                        const actionCellWrapper = row.querySelector('td:last-child .flex') || row.querySelector('td:last-child');

                        if (action === 'worker_accept_job') {
                            if (statusBadge) {
                                statusBadge.textContent = 'in-progress';
                                statusBadge.className = 'status-badge inline-block px-2 py-1 text-xs rounded-full whitespace-nowrap status-in-progress';
                            }
                            if (actionCellWrapper) {
                                actionCellWrapper.innerHTML = `<button type="button" class="job-modal-trigger flex items-center justify-center w-full sm:w-auto text-center px-3 py-1.5 bg-blue-100 text-blue-700 text-xs rounded-full hover:bg-blue-200 transition"
                                        data-action="worker_complete_job" data-job-id="${jobId}"
                                        data-job-type="${row.querySelector('td:first-child .font-medium') ? row.querySelector('td:first-child .font-medium').textContent.trim() : ''}"
                                        data-job-customer="${row.querySelector('td:nth-child(2) .font-medium') ? row.querySelector('td:nth-child(2) .font-medium').textContent.trim() : ''}">
                                        <i data-feather="check-square" class="w-4 h-4 mr-1"></i>
                                        Selesaikan
                                    </button>`;
                                attachModalListeners();
                                feather.replace();
                            }
                        } else if (action === 'worker_reject_job') {
                            if (statusBadge) {
                                statusBadge.textContent = 'cancelled';
                                statusBadge.className = 'status-badge inline-block px-2 py-1 text-xs rounded-full whitespace-nowrap status-cancelled';
                            }
                            if (actionCellWrapper) actionCellWrapper.innerHTML = '-';
                        } else if (action === 'worker_complete_job') {
                            if (statusBadge) {
                                statusBadge.textContent = 'completed';
                                statusBadge.className = 'status-badge inline-block px-2 py-1 text-xs rounded-full whitespace-nowrap status-completed';
                            }
                            if (actionCellWrapper) actionCellWrapper.innerHTML = '-';
                        }

                        // remove row visually for certain tabs
                        <?php if ($active_tab === 'pending'): ?>
                        if (action === 'worker_accept_job' || action === 'worker_reject_job') {
                            row.style.opacity = 0.5;
                            setTimeout(() => row.remove(), 500);
                        }
                        <?php endif; ?>

                        <?php if ($active_tab === 'active'): ?>
                        if (action === 'worker_complete_job') {
                            row.style.opacity = 0.5;
                            setTimeout(() => row.remove(), 500);
                        }
                        <?php endif; ?>
                    }
                } else {
                    showAjaxNotification(data.message || 'Operasi gagal', 'error');
                }
            } catch (err) {
                closeModal();
                showAjaxNotification('Terjadi error: ' + (err.message || err), 'error');
            } finally {
                // Hide loading
                if (btnText) btnText.classList.remove('hidden');
                if (btnLoading) { btnLoading.classList.add('hidden'); btnLoading.classList.remove('flex'); }
                this.disabled = false;
                modalCancelBtn.disabled = false;
            }
        });

        // Re-attach listeners on load/DOM changes
        document.addEventListener('DOMContentLoaded', attachModalListeners);
        window.addEventListener('load', attachModalListeners);
    </script>

</body>
</html>
