<?php
// File ini dipanggil oleh admin_dashboard.php
if (!defined('IS_ADMIN_PAGE')) {
    die('Akses ditolak!');
}
?>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6 worker-card">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                <i data-feather="users"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Kuli</p>
                <h3 class="text-2xl font-bold">
                    <?php echo htmlspecialchars((string)$totalWorkers); ?>
                </h3>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 worker-card">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                <i data-feather="check-circle"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Available</p>
                <h3 class="text-2xl font-bold">
                    <?php echo htmlspecialchars((string)$availableCount); ?>
                </h3>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 worker-card">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                <i data-feather="clock"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">On Job</p>
                <h3 class="text-2xl font-bold">
                    <?php echo htmlspecialchars((string)$onJobCount); ?>
                </h3>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 worker-card">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                <i data-feather="dollar-sign"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Active Jobs</p>
                <h3 class="text-2xl font-bold">
                    <?php echo htmlspecialchars((string)$activeJobs); ?>
                </h3>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Recent Job Assignments -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Recent Job Assignments</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Worker</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $recentJobs = getRecentJobs(5); ?>
                    <?php if (empty($recentJobs)): ?>
                        <tr>
                            <td colspan="3" class="text-center py-4 text-gray-500">Belum ada pekerjaan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentJobs as $job): ?>
                            <?php
                                $statusClass = getStatusClass($job['status'], 'job');
                                $statusText = ucwords(str_replace('-', ' ', $job['status']));
                            ?>
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <?php echo htmlspecialchars($job['workerName']); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <?php echo htmlspecialchars($job['jobType']); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Rated Workers -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Top Rated Workers</h3>
        <div class="space-y-4">
            <?php $topWorkersDisplay = getTopRatedWorkers(5); ?>
            <?php if (empty($topWorkersDisplay)): ?>
                <p class="text-center py-4 text-gray-500">Belum ada data kuli.</p>
            <?php else: ?>
                <?php foreach ($topWorkersDisplay as $worker): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg worker-card">
                        <div class="flex items-center">
                            <img
                                src="<?php echo htmlspecialchars($worker['photo']); ?>"
                                alt="<?php echo htmlspecialchars($worker['name']); ?>"
                                class="w-10 h-10 rounded-full mr-3 object-cover"
                            >
                            <div>
                                <div class="font-medium"><?php echo htmlspecialchars($worker['name']); ?></div>
                                <div class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars(implode(', ', array_slice($worker['skills'], 0, 2))); ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="flex items-center text-yellow-500">
                                <?php echo formatRating($worker['rating']); ?>
                            </div>
                            <div class="text-sm font-medium text-gray-700">
                                <?php echo formatCurrency($worker['rate']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="?tab=add_worker"
           class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-blue-500 hover:text-blue-500 transition-colors text-center">
            <i data-feather="user-plus" class="w-8 h-8 mx-auto mb-2"></i>
            <p class="text-sm font-medium">Tambah Kuli Baru</p>
        </a>
        <a href="?tab=add_job"
           class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-blue-500 hover:text-blue-500 transition-colors text-center">
            <i data-feather="plus-circle" class="w-8 h-8 mx-auto mb-2"></i>
            <p class="text-sm font-medium">Tambah Pekerjaan</p>
        </a>
        <a href="?tab=jobs"
           class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-blue-500 hover:text-blue-500 transition-colors text-center">
            <i data-feather="clipboard" class="w-8 h-8 mx-auto mb-2"></i>
            <p class="text-sm font-medium">Lihat Semua Pekerjaan</p>
        </a>
    </div>
</div>
