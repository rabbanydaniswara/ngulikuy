<header class="mb-8">
    <h1 class="text-2xl font-bold">Cari Pekerjaan Baru</h1>
    <p class="text-sm text-gray-500 mt-1">Lihat dan ambil pekerjaan yang diposting oleh customer.</p>
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
            <a href="?tab=find_jobs" class="flex-shrink-0 <?php echo $active_tab === 'find_jobs' ? 'nav-active' : 'border-transparent text-gray-500 hover:border-gray-300'; ?> px-6 py-4 text-sm font-medium">
                Cari Pekerjaan
            </a>
        </nav>
    </div>

    <div class="p-4 sm:p-6">
        <?php if (empty($openPostedJobs)): ?>
            <div class="text-center py-12">
                <i data-feather="search" class="w-16 h-16 mx-auto text-gray-300"></i>
                <h3 class="mt-4 text-lg font-medium text-gray-800">Tidak Ada Pekerjaan Terbuka</h3>
                <p class="mt-1 text-sm text-gray-500">Saat ini tidak ada pekerjaan yang tersedia untuk diambil. Silakan cek kembali nanti.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($openPostedJobs as $job): ?>
                    <div id="job-row-<?php echo $job['id']; ?>" class="bg-white border rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex flex-col md:flex-row items-start justify-between">
                            <div class="min-w-0 pr-4 mb-4 md:mb-0">
                                <h4 class="text-lg font-semibold truncate text-blue-700 hover:underline">
                                    <a href="detail_posted_job.php?id=<?php echo $job['id']; ?>"><?php echo htmlspecialchars($job['title']); ?></a>
                                </h4>
                                <div class="mt-1 flex items-center text-sm text-gray-600">
                                    <span class="font-semibold mr-2"><?php echo htmlspecialchars($job['job_type']); ?></span>
                                    <span class="mr-3 flex items-center"><i data-feather="map-pin" class="w-4 h-4 mr-1 text-gray-400"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                                    <span class="flex items-center"><i data-feather="calendar" class="w-4 h-4 mr-1 text-gray-400"></i> Diposting <?php echo date('d M Y', strtotime($job['created_at'])); ?></span>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 truncate-multiline">
                                    <?php echo htmlspecialchars($job['description']); ?>
                                </p>
                            </div>

                            <div class="flex-shrink-0 w-full md:w-52 text-left md:text-right">
                                <?php if (isset($job['budget']) && $job['budget'] > 0): ?>
                                    <div class="text-lg font-bold text-green-600"><?php echo formatCurrency($job['budget']); ?></div>
                                    <div class="text-xs text-gray-500">Anggaran</div>
                                <?php else: ?>
                                    <div class="text-sm font-medium text-gray-500">Anggaran tidak ditentukan</div>
                                <?php endif; ?>
                                
                                <div class="mt-3">
                                    <button type="button" class="job-modal-trigger w-full text-center px-4 py-2 flex items-center justify-center gap-2 rounded-lg bg-green-500 text-white text-sm font-semibold hover:bg-green-600 transition shadow"
                                        data-action="worker_take_posted_job" data-job-id="<?php echo $job['id']; ?>"
                                        data-job-title="<?php echo htmlspecialchars($job['title']); ?>">
                                        <i data-feather="plus-circle" class="w-4 h-4"></i>
                                        Ambil Pekerjaan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
