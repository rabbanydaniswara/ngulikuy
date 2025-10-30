<?php
// File ini dipanggil oleh admin_dashboard.php
if (!defined('IS_ADMIN_PAGE')) {
    die('Akses ditolak!');
}
?>

<div class="bg-white rounded-lg shadow p-6 mb-8">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold">Status Pengerjaan</h3>
        <div class="flex space-x-4">
            <a href="?tab=add_job" 
               class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i data-feather="plus-circle" class="w-4 h-4 mr-2"></i>
                Tambah Pekerjaan
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Worker</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($jobs)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-gray-500">
                            Belum ada data pekerjaan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <?php $statusClass = getStatusClass($job['status'], 'job'); ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-mono">
                                <?php echo htmlspecialchars($job['jobId']); ?>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="font-medium">
                                        <?php echo htmlspecialchars($job['workerName']); ?>
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo htmlspecialchars($job['jobType']); ?>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium">
                                    <?php echo htmlspecialchars($job['customer']); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($job['customerPhone']); ?>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">
                                    <?php echo date('d M Y', strtotime($job['startDate'])); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    to <?php echo date('d M Y', strtotime($job['endDate'])); ?>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo htmlspecialchars($job['location']); ?>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap font-medium">
                                <?php echo formatCurrency($job['price']); ?>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <select 
                                    name="status" 
                                    class="job-status-select px-2 py-1 text-xs rounded-full border-none focus:ring-2 focus:ring-blue-500 <?php echo $statusClass; ?>"
                                    data-job-id="<?php echo htmlspecialchars($job['jobId']); ?>">
                                    <option value="pending" <?php echo $job['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="in-progress" <?php echo $job['status'] === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo $job['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $job['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <button 
                                    type="button" 
                                    class="delete-job-btn text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 transition duration-200"
                                    data-job-id="<?php echo htmlspecialchars($job['jobId']); ?>"
                                    data-job-type="<?php echo htmlspecialchars($job['jobType']); ?>"
                                    data-job-customer="<?php echo htmlspecialchars($job['customer']); ?>"
                                    title="Delete Job">
                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
