<header class="mb-8">
    <h1 class="text-2xl font-bold">Daftar Pekerjaan Anda</h1>
    <p class="text-sm text-gray-500 mt-1">Kelola tawaran, pekerjaan yang sedang berjalan, dan riwayat pekerjaan Anda.</p>
</header>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px overflow-x-auto">
            <a href="?tab=find_jobs" class="flex-shrink-0 <?php echo $active_tab === 'find_jobs' ? 'nav-active' : 'border-transparent text-gray-500 hover:border-gray-300'; ?> px-6 py-4 text-sm font-medium">
                Cari Pekerjaan
            </a>
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
                $jobId = htmlspecialchars($job['id_pekerjaan'] ?? '');
                $jobType = htmlspecialchars($job['jenis_pekerjaan'] ?? '-');
                $location = htmlspecialchars($job['lokasi'] ?? '-');
                $customer = htmlspecialchars($job['nama_pelanggan'] ?? '-');
                $customerPhone = htmlspecialchars($job['telepon_pelanggan'] ?? '-');
                $startDate = !empty($job['tanggal_mulai']) ? date('d M Y', strtotime($job['tanggal_mulai'])) : '-';
                $price = function_exists('formatCurrency') ? formatCurrency($job['harga'] ?? 0) : 'Rp ' . number_format($job['harga'] ?? 0,0,',','.');
                $status = htmlspecialchars($job['status_pekerjaan'] ?? 'pending');
                $notes = htmlspecialchars(($active_tab === 'find_jobs' ? $job['deskripsi_lowongan'] : $job['deskripsi']) ?? '-');
            ?>
            <tr id="job-row-<?php echo $jobId; ?>">
            <td class="px-3 py-3 align-top">
                <div class="font-medium"><?php echo $jobType; ?></div>
                <div class="text-sm text-gray-500"><?php echo $location; ?></div>
            </td>

            <td class="px-3 py-3 align-top">
                <div class="font-medium"><?php echo $customer; ?></div>
                <div class="text-sm text-gray-500"><?php echo $customerPhone; ?></div>
                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($job['alamat_lokasi'] ?? ''); ?></div>
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
                <?php if (($job['status_pekerjaan'] ?? '') === 'pending'): ?>
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

                <?php elseif (($job['status_pekerjaan'] ?? '') === 'in-progress'): ?>

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
                        $jobId = htmlspecialchars($job['id_pekerjaan'] ?? '');
                        $jobType = htmlspecialchars($job['jenis_pekerjaan'] ?? '-');
                        $location = htmlspecialchars($job['lokasi'] ?? '-');
                        $customer = htmlspecialchars($job['nama_pelanggan'] ?? '-');
                        $customerPhone = htmlspecialchars($job['telepon_pelanggan'] ?? '-');
                        $startDate = !empty($job['tanggal_mulai']) ? date('d M Y', strtotime($job['tanggal_mulai'])) : '-';
                        $price = function_exists('formatCurrency') ? formatCurrency($job['harga'] ?? 0) : 'Rp ' . number_format($job['harga'] ?? 0,0,',','.');
                        $status = htmlspecialchars($job['status_pekerjaan'] ?? 'pending');
                        $notes = htmlspecialchars(($active_tab === 'find_jobs' ? $job['deskripsi_lowongan'] : $job['deskripsi']) ?? '-');
                    ?>
                        <div id="job-row-<?php echo $jobId; ?>" class="bg-white border rounded-lg p-4 shadow-sm">
                            <div class="flex items-start justify-between">
                                <div class="min-w-0 pr-3">
                                    <div class="text-base font-semibold truncate"><?php echo $jobType; ?></div>
                                    <div class="text-sm text-gray-700 truncate"><?php echo $location; ?></div>
                                    <div class="text-sm text-gray-700 truncate"><?php echo htmlspecialchars($job['alamat_lokasi'] ?? ''); ?></div>
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
                                        <?php if (($job['status_pekerjaan'] ?? '') === 'pending'): ?>
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
                                        <?php elseif (($job['status_pekerjaan'] ?? '') === 'in-progress'): ?>
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
