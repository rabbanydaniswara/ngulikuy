<?php
/**
 * Customer Dashboard - My Posted Jobs View
 */

// This variable will be populated from _logic.php
// $postedJobs = getPostedJobsByCustomer($customer_id); 
?>
<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Lowongan Saya</h2>
            <p class="text-gray-500 mt-1">Kelola lowongan pekerjaan yang Anda buat</p>
        </div>
        <a href="?tab=post_job" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors shadow-sm">
            <i data-feather="plus" class="w-4 h-4 mr-2"></i>
            Buat Lowongan Baru
        </a>
    </div>

    <?php 
    // --- Pre-process jobs to filter out deleted ones ---
    $displayable_jobs = [];
    if (!empty($postedJobs)) {
        foreach ($postedJobs as $job) {
            $posted_status = $job['status_lowongan'];
            $job_status = $job['job_status'];

            $final_status = 'unknown';
            if ($job_status !== null) {
                $final_status = $job_status;
            } else {
                if ($posted_status === 'assigned') {
                    $final_status = 'dihapus'; 
                } else {
                    $final_status = 'open';
                }
            }

            if ($final_status !== 'dihapus') {
                $job['final_status'] = $final_status;
                $displayable_jobs[] = $job;
            }
        }
    }
    ?>

    <?php if (empty($displayable_jobs)): ?>
        <div class="card p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                <i data-feather="briefcase" class="w-8 h-8 text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">Belum ada lowongan</h3>
            <p class="text-gray-500 mb-6">Buat lowongan baru untuk menemukan pekerja yang tepat.</p>
            <a href="?tab=post_job" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors shadow-sm">
                <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                Buat Lowongan Pertama
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($displayable_jobs as $job): 
                $final_status = $job['final_status'];
                
                $badgeClass = 'status-badge';
                if ($final_status === 'open') $badgeClass .= ' status-completed'; // Greenish for open
                elseif ($final_status === 'in-progress') $badgeClass .= ' status-in-progress';
                elseif ($final_status === 'completed') $badgeClass .= ' status-completed';
                else $badgeClass .= ' status-cancelled';
            ?>
                <div id="job-row-<?php echo $job['id_lowongan']; ?>" class="card overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <h3 class="text-lg font-bold text-gray-900 hover:text-blue-600 transition-colors">
                                        <a href="detail_posted_job.php?id=<?php echo $job['id_lowongan']; ?>">
                                            <?php echo htmlspecialchars($job['judul_lowongan']); ?>
                                        </a>
                                    </h3>
                                    <span class="<?php echo $badgeClass; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $final_status)); ?>
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 flex items-center gap-2">
                                    <span class="font-medium text-gray-700"><?php echo htmlspecialchars($job['jenis_pekerjaan']); ?></span>
                                    <span>&bull;</span>
                                    <span><?php echo htmlspecialchars($job['lokasi']); ?></span>
                                    <span>&bull;</span>
                                    <span>Posted <?php echo date('d M Y', strtotime($job['dibuat_pada'])); ?></span>
                                </p>
                            </div>
                            
                            <?php if (isset($job['anggaran']) && $job['anggaran'] > 0): ?>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500 mb-1">Anggaran</p>
                                    <p class="text-lg font-bold text-gray-900">
                                        <?php echo formatCurrency($job['anggaran']); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <p class="text-gray-600 mb-6 line-clamp-2">
                            <?php echo nl2br(htmlspecialchars(substr($job['deskripsi_lowongan'], 0, 200))); ?>
                        </p>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50">
                            <a href="detail_posted_job.php?id=<?php echo $job['id_lowongan']; ?>" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <i data-feather="eye" class="w-4 h-4 mr-2"></i>
                                Detail
                            </a>
                            
                            <?php if ($final_status === 'open'): ?>
                                <button type="button" 
                                    class="job-modal-trigger inline-flex items-center px-4 py-2 bg-red-50 text-red-600 border border-red-100 rounded-lg text-sm font-medium hover:bg-red-100 transition-colors"
                                    data-action="customer_delete_posted_job" 
                                    data-job-id="<?php echo $job['id_lowongan']; ?>" 
                                    data-job-title="<?php echo htmlspecialchars($job['judul_lowongan']); ?>">
                                    <i data-feather="trash-2" class="w-4 h-4 mr-2"></i>
                                    Hapus
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
