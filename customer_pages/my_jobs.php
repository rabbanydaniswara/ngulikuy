<?php
/**
 * Customer Dashboard - My Posted Jobs View
 */

// This variable will be populated from _logic.php
// $postedJobs = getPostedJobsByCustomer($customer_id); 
?>
<div class="mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
            <i data-feather="briefcase" class="w-6 h-6 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-blue-600"></i>
            Pekerjaan yang Saya Posting
        </h2>
        <a href="?tab=post_job" class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 font-semibold shadow-md hover:shadow-lg transition-all text-sm">
            <i data-feather="plus" class="w-4 h-4 mr-2"></i>
            Buat Pekerjaan Baru
        </a>
    </div>

    <?php 
    // --- Pre-process jobs to filter out deleted ones ---
    $displayable_jobs = [];
    if (!empty($postedJobs)) {
        foreach ($postedJobs as $job) {
            $posted_status = $job['posted_job_status'];
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
                // Add the final status to the job array for use in the view
                $job['final_status'] = $final_status;
                $displayable_jobs[] = $job;
            }
        }
    }
    ?>

    <?php if (empty($displayable_jobs)): ?>
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg p-8 sm:p-12 text-center">
            <div class="inline-block p-4 sm:p-6 bg-gray-100 rounded-full mb-3 sm:mb-4">
                <i data-feather="briefcase" class="w-12 h-12 sm:w-16 sm:h-16 text-gray-400"></i>
            </div>
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-2">Anda belum memposting pekerjaan</h3>
            <p class="text-sm sm:text-base text-gray-500 mb-4 sm:mb-6">Posting pekerjaan baru dan dapatkan penawaran dari para tukang.</p>
            <a href="?tab=post_job" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 font-semibold shadow-md hover:shadow-lg transition-all text-base">
                <i data-feather="plus" class="w-5 h-5 mr-2"></i>
                Posting Pekerjaan Pertama Anda
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-4 sm:space-y-6">
            <?php foreach ($displayable_jobs as $job): 
                $final_status = $job['final_status'];
                
                $statusClass = 'bg-gray-100 text-gray-800';
                if ($final_status === 'open') $statusClass = 'bg-green-100 text-green-800';
                if ($final_status === 'in-progress') $statusClass = 'bg-yellow-100 text-yellow-800';
                if ($final_status === 'completed') $statusClass = 'bg-blue-100 text-blue-800';
                if ($final_status === 'cancelled') $statusClass = 'bg-orange-100 text-orange-800';
            ?>
                <div id="job-row-<?php echo $job['id']; ?>" class="bg-white rounded-xl sm:rounded-2xl shadow-lg hover:shadow-xl transition-all overflow-hidden">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start">
                            <div>
                                <h3 class="text-base sm:text-xl font-bold text-gray-800 hover:text-blue-600 transition-colors">
                                    <a href="detail_posted_job.php?id=<?php echo $job['id']; ?>"><?php echo htmlspecialchars($job['title']); ?></a>
                                </h3>
                                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                                    <span class="font-semibold"><?php echo htmlspecialchars($job['job_type']); ?></span>
                                    <span class="mx-1 sm:mx-2">•</span>
                                    <i data-feather="map-pin" class="w-3 h-3 inline"></i> <?php echo htmlspecialchars($job['location']); ?>
                                    <span class="mx-1 sm:mx-2">•</span>
                                    Posted on <?php echo date('d M Y', strtotime($job['created_at'])); ?>
                                </p>
                            </div>
                            <span class="mt-2 sm:mt-0 px-3 py-1 text-xs sm:text-sm font-bold rounded-full <?php echo $statusClass; ?> inline-block w-fit">
                                <?php echo ucfirst(str_replace('_', ' ', $final_status)); ?>
                            </span>
                        </div>
                        
                        <p class="mt-3 text-sm text-gray-600">
                            <?php echo nl2br(htmlspecialchars(substr($job['description'], 0, 150))); ?>
                            <?php if (strlen($job['description']) > 150): ?>...<?php endif; ?>
                        </p>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pt-4 mt-4 border-t border-gray-100">
                            <div>
                                <?php if (isset($job['budget']) && $job['budget'] > 0): ?>
                                    <p class="text-base sm:text-lg font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                                        <?php echo formatCurrency($job['budget']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500">Anggaran</p>
                                <?php else: ?>
                                    <p class="text-sm font-medium text-gray-500">Anggaran tidak ditentukan</p>
                                <?php endif; ?>
                            </div>
                            <div class="mt-3 sm:mt-0 flex items-center space-x-2">
                                <a href="detail_posted_job.php?id=<?php echo $job['id']; ?>" class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-xs font-semibold transition-all">
                                    <i data-feather="eye" class="w-4 h-4 mr-2"></i>
                                    Lihat Detail
                                </a>
                                <?php if ($final_status === 'open'): ?>
                                <button type="button" class="job-modal-trigger inline-flex items-center px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 text-xs font-semibold transition-all"
                                    data-action="customer_delete_posted_job" data-job-id="<?php echo $job['id']; ?>" data-job-title="<?php echo htmlspecialchars($job['title']); ?>">
                                    <i data-feather="trash-2" class="w-4 h-4 mr-2"></i>
                                    Hapus
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
