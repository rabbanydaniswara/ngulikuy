<header class="mb-8">
    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Cari Pekerjaan Baru</h1>
    <p class="text-slate-500 mt-2">Lihat dan ambil pekerjaan yang diposting oleh customer.</p>
</header>

<div class="card-modern overflow-hidden">
    <div class="border-b border-slate-200 bg-white">
        <nav class="flex -mb-px overflow-x-auto" aria-label="Tabs">
            <a href="?tab=find_jobs" class="nav-link whitespace-nowrap <?php echo $active_tab === 'find_jobs' ? 'nav-active' : ''; ?>">
                Cari Pekerjaan
            </a>
            <a href="?tab=pending" class="nav-link whitespace-nowrap <?php echo $active_tab === 'pending' ? 'nav-active' : ''; ?>">
                Tawaran Baru
            </a>
            <a href="?tab=active" class="nav-link whitespace-nowrap <?php echo $active_tab === 'active' ? 'nav-active' : ''; ?>">
                Sedang Berjalan
            </a>
            <a href="?tab=completed" class="nav-link whitespace-nowrap <?php echo $active_tab === 'completed' ? 'nav-active' : ''; ?>">
                Riwayat
            </a>
        </nav>
    </div>

    <div class="p-4 sm:p-6 bg-slate-50/50 min-h-[400px]">
        <?php if (empty($worker_skills)): ?>
            <div class="text-center py-16">
                <div class="bg-amber-50 p-4 rounded-full inline-block mb-4">
                    <i data-feather="alert-triangle" class="w-8 h-8 text-amber-500"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-900">Lengkapi Keahlian Anda</h3>
                <p class="mt-2 text-slate-500 max-w-md mx-auto">Anda belum memiliki keahlian. Tambahkan keahlian di profil Anda untuk melihat pekerjaan yang sesuai.</p>
                <a href="?tab=profile" class="mt-6 inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                    Lengkapi Profil
                </a>
            </div>
        <?php elseif (empty($openPostedJobs)): ?>
            <div class="text-center py-16">
                <div class="bg-slate-100 p-4 rounded-full inline-block mb-4">
                    <i data-feather="search" class="w-8 h-8 text-slate-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-900">Tidak Ada Pekerjaan yang Sesuai</h3>
                <p class="mt-2 text-slate-500 max-w-md mx-auto">Saat ini tidak ada pekerjaan yang cocok dengan keahlian Anda. Silakan cek kembali nanti.</p>
            </div>
        <?php else: ?>
            <div class="grid gap-4">
                <?php foreach ($openPostedJobs as $job): ?>
                    <div id="job-row-<?php echo $job['id_lowongan']; ?>" class="card-modern p-5 hover:shadow-md transition-shadow duration-200">
                        <div class="flex flex-col md:flex-row items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between md:justify-start gap-3 mb-1">
                                    <h4 class="text-lg font-semibold text-slate-900 truncate group">
                                        <a href="detail_posted_job.php?id=<?php echo $job['id_lowongan']; ?>" class="hover:text-blue-600 transition-colors">
                                            <?php echo htmlspecialchars($job['judul_lowongan']); ?>
                                        </a>
                                    </h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                        <?php echo htmlspecialchars($job['jenis_pekerjaan']); ?>
                                    </span>
                                </div>
                                
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500 mt-2">
                                    <span class="flex items-center"><i data-feather="map-pin" class="w-3.5 h-3.5 mr-1.5 text-slate-400"></i> <?php echo htmlspecialchars($job['lokasi']); ?></span>
                                    <span class="flex items-center"><i data-feather="calendar" class="w-3.5 h-3.5 mr-1.5 text-slate-400"></i> Diposting <?php echo date('d M Y', strtotime($job['dibuat_pada'])); ?></span>
                                </div>
                                
                                <p class="mt-3 text-sm text-slate-600 leading-relaxed truncate-multiline">
                                    <?php echo htmlspecialchars($job['deskripsi_lowongan']); ?>
                                </p>
                            </div>

                            <div class="flex-shrink-0 w-full md:w-auto flex flex-row md:flex-col items-center md:items-end justify-between md:justify-start gap-4 md:gap-1 mt-2 md:mt-0 border-t md:border-t-0 border-slate-100 pt-4 md:pt-0">
                                <div class="text-left md:text-right">
                                    <?php if (isset($job['anggaran']) && $job['anggaran'] > 0): ?>
                                        <div class="text-lg font-bold text-slate-900"><?php echo formatCurrency($job['anggaran']); ?></div>
                                        <div class="text-xs text-slate-500 font-medium">Anggaran</div>
                                    <?php else: ?>
                                        <div class="text-sm font-medium text-slate-500">Anggaran tidak ditentukan</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mt-1 md:mt-4 w-auto md:w-full">
                                    <button type="button" class="job-modal-trigger w-full inline-flex items-center justify-center px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 transition-colors shadow-sm gap-2"
                                        data-action="worker_take_posted_job" data-job-id="<?php echo $job['id_lowongan']; ?>"
                                        data-job-title="<?php echo htmlspecialchars($job['judul_lowongan']); ?>">
                                        <i data-feather="plus-circle" class="w-4 h-4"></i>
                                        <span>Ambil</span>
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
