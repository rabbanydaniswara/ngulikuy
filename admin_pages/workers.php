<?php
// File ini dipanggil oleh admin_dashboard.php
if (!defined('IS_ADMIN_PAGE')) {
    die('Akses ditolak!');
}
?>

<div class="bg-white rounded-lg shadow p-6 mb-8">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold">Data Kuli</h3>
        <div class="flex space-x-4">
            <a href="?tab=add_worker" 
               class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i data-feather="user-plus" class="w-4 h-4 mr-2"></i>
                Tambah Kuli
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Worker</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skills</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($workers)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-gray-500">
                            Belum ada data kuli.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($workers as $worker): ?>
                        <?php $statusClass = getStatusClass($worker['status'], 'worker'); ?>

                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-mono">
                                <?php echo htmlspecialchars($worker['id']); ?>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img src="<?php echo htmlspecialchars($worker['photo']); ?>" 
                                         alt="<?php echo htmlspecialchars($worker['name']); ?>" 
                                         class="w-10 h-10 rounded-full mr-3 object-cover">
                                    <div>
                                        <div class="font-medium">
                                            <?php echo htmlspecialchars($worker['name']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($worker['experience'] ?? 'No experience'); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">
                                    <?php echo htmlspecialchars($worker['email']); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($worker['phone']); ?>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    <?php foreach ($worker['skills'] as $skill): ?>
                                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                            <?php echo htmlspecialchars($skill); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo htmlspecialchars($worker['location']); ?>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($worker['status']); ?>
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-yellow-500 mr-1">
                                        <?php echo formatRating($worker['rating']); ?>
                                    </span>
                                    <span class="text-sm text-gray-600">
                                        <?php echo number_format($worker['rating'], 1); ?>
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap font-medium">
                                <?php echo formatCurrency($worker['rate']); ?>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <button 
                                        type="button" 
                                        class="edit-worker-btn text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-50 transition duration-200" 
                                        data-worker-id="<?php echo htmlspecialchars($worker['id']); ?>" 
                                        title="Edit Worker">
                                        <i data-feather="edit" class="w-4 h-4"></i>
                                    </button>

                                    <button 
                                        type="button" 
                                        class="delete-worker-btn text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 transition duration-200" 
                                        data-worker-id="<?php echo htmlspecialchars($worker['id']); ?>" 
                                        data-worker-name="<?php echo htmlspecialchars($worker['name']); ?>" 
                                        title="Delete Worker">
                                        <i data-feather="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
