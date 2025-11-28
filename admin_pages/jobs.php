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
                            <?php $statusClass = getStatusClass($job['status'], 'job'); ?>
                            <tr>
                                <!-- Job ID -->
                                <td class="px-3 py-3 text-xs font-mono truncate"><?php echo htmlspecialchars($job['jobId']); ?></td>

                                <!-- Worker -->
                                <td class="px-3 py-3 align-top">
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium truncate max-w-[160px]" title="<?php echo htmlspecialchars($job['workerName']); ?>">
                                            <?php echo htmlspecialchars($job['workerName']); ?>
                                        </div>
                                    </div>
                                </td>

                                <!-- Job Type -->
                                <td class="px-3 py-3 text-sm truncate max-w-[110px]" title="<?php echo htmlspecialchars($job['jobType']); ?>">
                                    <?php echo htmlspecialchars($job['jobType']); ?>
                                </td>

                                <!-- Customer (name + phone/email) -->
                                <td class="px-3 py-3 align-top">
                                    <div class="text-sm truncate max-w-[150px]" title="<?php echo htmlspecialchars($job['customer']); ?>">
                                        <?php echo htmlspecialchars($job['customer']); ?>
                                    </div>
                                    <div class="text-xs text-gray-500 truncate max-w-[150px]" title="<?php echo htmlspecialchars($job['customerPhone'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($job['customerPhone'] ?? ''); ?>
                                    </div>
                                </td>

                                <!-- Dates -->
                                <td class="px-3 py-3 text-sm align-top">
                                    <div class="truncate max-w-[140px]" title="<?php echo date('d M Y', strtotime($job['startDate'])) . ' - ' . date('d M Y', strtotime($job['endDate'])); ?>">
                                        <?php echo date('d M Y', strtotime($job['startDate'])); ?>
                                        <span class="text-xs text-gray-500"> to </span>
                                        <?php echo date('d M Y', strtotime($job['endDate'])); ?>
                                    </div>
                                </td>

                                <!-- Location -->
                                <td class="px-3 py-3 text-sm text-gray-700 truncate max-w-[120px]" title="<?php echo htmlspecialchars($job['location']); ?>">
                                    <?php echo htmlspecialchars($job['location']); ?>
                                </td>

                                <!-- Alamat -->
                                <td class="px-3 py-3 text-sm text-gray-700 truncate max-w-[150px]" title="<?php echo htmlspecialchars($job['address']); ?>">
                                    <?php echo htmlspecialchars($job['address']); ?>
                                </td>

                                <!-- Price (right aligned, beri spacing agar tidak nempel) -->
                                <td class="px-3 py-3 text-right align-top">
                                    <div class="text-sm font-medium whitespace-nowrap" title="<?php echo formatCurrency($job['price']); ?>">
                                        <?php echo formatCurrency($job['price']); ?>
                                    </div>
                                </td>

                                <!-- Status (right aligned, compact pill select inside relative container with caret) -->
                                <td class="px-3 py-3 text-right align-top">
                                    <div class="inline-block relative">
                                        <!-- custom-styled select: appearance-none with caret -->
                                        <select
                                            name="status"
                                            class="job-status-select appearance-none pr-8 pl-3 py-1 text-sm rounded-full border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-200 <?php echo $statusClass; ?>"
                                            data-job-id="<?php echo htmlspecialchars($job['jobId']); ?>">
                                            <option value="pending" <?php echo $job['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="in-progress" <?php echo $job['status'] === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo $job['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $job['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
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
                                            data-job-id="<?php echo htmlspecialchars($job['jobId']); ?>"
                                            data-worker-name="<?php echo htmlspecialchars($job['workerName']); ?>"
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
                    <?php $statusClass = getStatusClass($job['status'], 'job'); ?>
                    <div class="bg-white border rounded-lg p-4 shadow-sm">
                        <!-- main info (title + small meta) -->
                        <div class="min-w-0">
                            <div class="text-base font-semibold truncate" title="<?php echo htmlspecialchars($job['jobId'] . ' — ' . $job['jobType']); ?>">
                                <?php echo htmlspecialchars($job['jobId']); ?> — <?php echo htmlspecialchars($job['jobType']); ?>
                            </div>
                            <div class="text-sm text-gray-700 truncate mt-1" title="<?php echo htmlspecialchars($job['workerName']); ?>"><?php echo htmlspecialchars($job['workerName']); ?></div>

                            <div class="mt-2 text-sm text-gray-600 truncate" title="<?php echo htmlspecialchars($job['customer'] . ' • ' . ($job['customerPhone'] ?? '')); ?>">
                                <?php echo htmlspecialchars($job['customer']); ?> • <?php echo htmlspecialchars($job['customerPhone'] ?? ''); ?>
                            </div>

                            <div class="mt-2 text-sm text-gray-500 truncate" title="<?php echo date('d M Y', strtotime($job['startDate'])) . ' - ' . date('d M Y', strtotime($job['endDate'])); ?>">
                                <?php echo date('d M Y', strtotime($job['startDate'])); ?> to <?php echo date('d M Y', strtotime($job['endDate'])); ?>
                            </div>

                            <div class="mt-2 text-sm text-gray-500 truncate" title="<?php echo htmlspecialchars($job['location']); ?>">
                                <?php echo htmlspecialchars($job['location']); ?>
                            </div>
                            <div class="mt-2 text-sm text-gray-500 truncate" title="<?php echo htmlspecialchars($job['address']); ?>">
                                <?php echo htmlspecialchars($job['address']); ?>
                            </div>
                        </div>

                        <!-- bottom row: price | status | actions -->
                        <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <!-- price -->
                            <div class="text-right sm:text-right">
                                <div class="text-lg font-medium whitespace-nowrap"><?php echo formatCurrency($job['price']); ?></div>
                            </div>

                            <!-- status (on small screens make full width if needed) -->
                            <div class="flex-1 sm:flex-none flex justify-end">
                                <div class="w-full sm:w-auto">
                                    <div class="inline-block relative w-full sm:w-auto">
                                        <select
                                            name="status"
                                            class="job-status-select appearance-none w-full sm:w-auto pr-8 pl-3 py-1 text-sm rounded-full border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-200 <?php echo $statusClass; ?>"
                                            data-job-id="<?php echo htmlspecialchars($job['jobId']); ?>">
                                            <option value="pending" <?php echo $job['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="in-progress" <?php echo $job['status'] === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo $job['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $job['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
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
                                    data-job-id="<?php echo htmlspecialchars($job['jobId']); ?>"
                                    data-worker-name="<?php echo htmlspecialchars($job['workerName']); ?>"
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
