<header class="mb-8">
    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Daftar Pekerjaan</h1>
    <p class="text-slate-500 mt-2">Kelola tawaran, pekerjaan yang sedang berjalan, dan riwayat pekerjaan Anda.</p>
</header>

<div class="card-modern overflow-hidden">
    <div class="border-b border-slate-200 bg-white">
        <nav class="flex -mb-px overflow-x-auto" aria-label="Tabs">
            <a href="?tab=find_jobs" class="nav-link whitespace-nowrap <?php echo $active_tab === 'find_jobs' ? 'nav-active' : ''; ?>">
                Cari Pekerjaan
            </a>
            <a href="?tab=pending" class="nav-link whitespace-nowrap <?php echo $active_tab === 'pending' ? 'nav-active' : ''; ?>">
                Tawaran Baru
                <?php if($active_tab === 'pending'): ?>
                    <span class="ml-2 bg-blue-100 text-blue-600 py-0.5 px-2 rounded-full text-xs">Active</span>
                <?php endif; ?>
            </a>
            <a href="?tab=active" class="nav-link whitespace-nowrap <?php echo $active_tab === 'active' ? 'nav-active' : ''; ?>">
                Sedang Berjalan
            </a>
            <a href="?tab=completed" class="nav-link whitespace-nowrap <?php echo $active_tab === 'completed' ? 'nav-active' : ''; ?>">
                Riwayat
            </a>
        </nav>
    </div>

    <div class="p-0">
        <div class="overflow-x-auto">
            <!-- Desktop table -->
            <div class="table-outer">
                <table class="responsive-table hidden md:table">
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
                            <th>Pekerjaan</th>
                            <th>Customer</th>
                            <th>Tanggal</th>
                            <th>Biaya</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <?php if (empty($filteredJobs)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="bg-slate-50 p-4 rounded-full mb-3">
                                            <i data-feather="inbox" class="w-8 h-8 text-slate-400"></i>
                                        </div>
                                        <p>Tidak ada pekerjaan di kategori ini.</p>
                                    </div>
                                </td>
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
                            <tr id="job-row-<?php echo $jobId; ?>" class="group hover:bg-slate-50 transition-colors">
                                <td class="align-top">
                                    <div class="font-semibold text-slate-900"><?php echo $jobType; ?></div>
                                    <div class="text-sm text-slate-500 flex items-center mt-1">
                                        <i data-feather="map-pin" class="w-3 h-3 mr-1"></i> <?php echo $location; ?>
                                    </div>
                                </td>

                                <td class="align-top">
                                    <div class="font-medium text-slate-900"><?php echo $customer; ?></div>
                                    <div class="text-sm text-slate-500 mt-0.5"><?php echo $customerPhone; ?></div>
                                    <div class="text-xs text-slate-400 mt-1 truncate max-w-[150px]" title="<?php echo htmlspecialchars($job['alamat_lokasi'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($job['alamat_lokasi'] ?? ''); ?>
                                    </div>
                                </td>

                                <td class="text-sm text-slate-600 whitespace-nowrap align-top pt-5">
                                    <?php echo $startDate; ?>
                                </td>

                                <td class="price align-top pt-5">
                                    <?php echo $price; ?>
                                </td>

                                <td class="align-top pt-4">
                                    <span class="status-badge <?php echo getStatusClass($status, 'job'); ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>

                                <td class="desc text-sm text-slate-600 align-top pt-5" title="<?php echo $notes; ?>">
                                    <?php echo $notes; ?>
                                </td>

                                <td class="actions align-top pt-4">
                                    <div class="actions-buttons">
                                        <?php if (($job['status_pekerjaan'] ?? '') === 'pending'): ?>
                                            <button type="button" class="job-modal-trigger px-3 py-1.5 flex items-center gap-2 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-medium hover:bg-emerald-100 transition-colors"
                                                data-action="worker_accept_job" data-job-id="<?php echo $jobId; ?>"
                                                data-job-type="<?php echo $jobType; ?>" data-job-customer="<?php echo $customer; ?>">
                                                <i data-feather="check" class="w-3.5 h-3.5"></i>
                                                Terima
                                            </button>

                                            <button type="button" class="job-modal-trigger px-3 py-1.5 flex items-center gap-2 rounded-lg bg-rose-50 text-rose-700 text-xs font-medium hover:bg-rose-100 transition-colors"
                                                data-action="worker_reject_job" data-job-id="<?php echo $jobId; ?>"
                                                data-job-type="<?php echo $jobType; ?>" data-job-customer="<?php echo $customer; ?>">
                                                <i data-feather="x" class="w-3.5 h-3.5"></i>
                                                Tolak
                                            </button>

                                        <?php elseif (($job['status_pekerjaan'] ?? '') === 'in-progress'): ?>
                                            <button type="button" class="job-modal-trigger px-3 py-1.5 flex items-center gap-2 rounded-lg bg-blue-50 text-blue-700 text-xs font-medium hover:bg-blue-100 transition-colors"
                                                data-action="worker_complete_job" data-job-id="<?php echo $jobId; ?>"
                                                data-job-type="<?php echo $jobType; ?>" data-job-customer="<?php echo $customer; ?>">
                                                <i data-feather="check-square" class="w-3.5 h-3.5"></i>
                                                Selesai
                                            </button>

                                        <?php else: ?>
                                            <div class="text-xs text-slate-400 font-medium py-1">Selesai</div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <div class="md:hidden space-y-4 p-4 bg-slate-50">
                <?php if (empty($filteredJobs)): ?>
                    <div class="text-center py-8 text-slate-500">
                         <div class="bg-white p-4 rounded-full inline-block mb-3 shadow-sm">
                            <i data-feather="inbox" class="w-6 h-6 text-slate-400"></i>
                        </div>
                        <p>Tidak ada pekerjaan di kategori ini.</p>
                    </div>
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
                        <div id="job-row-<?php echo $jobId; ?>" class="card-modern p-5">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="font-semibold text-slate-900 text-lg"><?php echo $jobType; ?></h3>
                                    <div class="text-sm text-slate-500 flex items-center mt-1">
                                        <i data-feather="map-pin" class="w-3.5 h-3.5 mr-1"></i> <?php echo $location; ?>
                                    </div>
                                </div>
                                <span class="status-badge <?php echo getStatusClass($status, 'job'); ?> text-xs">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </div>

                            <div class="space-y-3 mb-4">
                                <div class="flex items-start gap-3 text-sm">
                                    <div class="min-w-[20px] pt-0.5"><i data-feather="user" class="w-4 h-4 text-slate-400"></i></div>
                                    <div>
                                        <div class="font-medium text-slate-700"><?php echo $customer; ?></div>
                                        <div class="text-slate-500 text-xs"><?php echo $customerPhone; ?></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="min-w-[20px]"><i data-feather="calendar" class="w-4 h-4 text-slate-400"></i></div>
                                    <div class="text-slate-600"><?php echo $startDate; ?></div>
                                </div>
                                <div class="flex items-start gap-3 text-sm">
                                    <div class="min-w-[20px] pt-0.5"><i data-feather="file-text" class="w-4 h-4 text-slate-400"></i></div>
                                    <div class="text-slate-600 italic">"<?php echo $notes; ?>"</div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                                <div class="font-bold text-slate-900 text-lg"><?php echo $price; ?></div>
                                
                                <div class="flex gap-2">
                                    <?php if (($job['status_pekerjaan'] ?? '') === 'pending'): ?>
                                        <button type="button" class="job-modal-trigger p-2 rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors"
                                            data-action="worker_accept_job" data-job-id="<?php echo $jobId; ?>"
                                            data-job-type="<?php echo $jobType; ?>" data-job-customer="<?php echo $customer; ?>">
                                            <i data-feather="check" class="w-5 h-5"></i>
                                        </button>
                                        <button type="button" class="job-modal-trigger p-2 rounded-full bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors"
                                            data-action="worker_reject_job" data-job-id="<?php echo $jobId; ?>"
                                            data-job-type="<?php echo $jobType; ?>" data-job-customer="<?php echo $customer; ?>">
                                            <i data-feather="x" class="w-5 h-5"></i>
                                        </button>
                                    <?php elseif (($job['status_pekerjaan'] ?? '') === 'in-progress'): ?>
                                        <button type="button" class="job-modal-trigger px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition-colors shadow-sm flex items-center gap-2"
                                            data-action="worker_complete_job" data-job-id="<?php echo $jobId; ?>"
                                            data-job-type="<?php echo $jobType; ?>" data-job-customer="<?php echo $customer; ?>">
                                            <i data-feather="check-square" class="w-4 h-4"></i>
                                            Selesaikan
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
