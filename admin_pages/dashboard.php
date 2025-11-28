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
                <p class="text-sm text-gray-500">Total Pekerja</p>
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
    <!-- Pekerja Baru -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Pekerja Baru</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keahlian</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bergabung</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $recentWorkers = getRecentWorkers(5); ?>
                    <?php if (empty($recentWorkers)): ?>
                        <tr>
                            <td colspan="3" class="text-center py-4 text-gray-500">Belum ada pekerja baru.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentWorkers as $worker): ?>
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full object-cover" src="<?php echo htmlspecialchars($worker['url_foto']); ?>" alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($worker['nama']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($worker['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars(implode(', ', $worker['keahlian'])); ?></div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d M Y', strtotime($worker['tanggal_bergabung'])); ?>
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
        <h3 class="text-lg font-semibold mb-4">Pekerja Terbaik</h3>
        <div class="space-y-4">
            <?php $topWorkersDisplay = getTopRatedWorkers(5); ?>
            <?php if (empty($topWorkersDisplay)): ?>
                <p class="text-center py-4 text-gray-500">Belum ada data pekerja.</p>
            <?php else: ?>
                <?php foreach ($topWorkersDisplay as $worker): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg worker-card">
                        <div class="flex items-center">
                            <img
                                src="<?php echo htmlspecialchars($worker['url_foto']); ?>"
                                alt="<?php echo htmlspecialchars($worker['nama']); ?>"
                                class="w-10 h-10 rounded-full mr-3 object-cover"
                            >
                            <div>
                                <div class="font-medium"><?php echo htmlspecialchars($worker['nama']); ?></div>
                                <div class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars(implode(', ', array_slice($worker['keahlian'], 0, 2))); ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="flex items-center text-yellow-500">
                                <?php echo formatRating($worker['rating']); ?>
                            </div>
                            <div class="text-sm font-medium text-gray-700">
                                <?php echo formatCurrency($worker['tarif_per_jam']); ?>
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
            <p class="text-sm font-medium">Tambah Pekerja Baru</p>
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
