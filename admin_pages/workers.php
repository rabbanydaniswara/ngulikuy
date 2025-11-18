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
<<<<<<< HEAD
            <a href="?tab=add_worker"
=======
            <a href="?tab=add_worker" 
>>>>>>> 129876e8c9e2037e93f044a24ed31c5b23d98a28
               class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i data-feather="user-plus" class="w-4 h-4 mr-2"></i>
                Tambah Kuli
            </a>
        </div>
    </div>

<<<<<<< HEAD
    <div class="w-full">
        <!-- TABLE: tampil di md ke atas (fixed layout supaya tidak melebar) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full table-fixed divide-y divide-gray-200">

                <thead>
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-40">Worker</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-40">Contact</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-40">Skills</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-28">Location</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-20">Status</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-28">Rating</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-24">Rate</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-20">Actions</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($workers)): ?>
                        <tr>
                            <td colspan="9" class="px-3 py-4 text-center text-gray-500">Belum ada data kuli.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($workers as $worker): ?>
                            <?php $statusClass = getStatusClass($worker['status'], 'worker'); ?>
                            <tr>

                                <!-- ID -->
                                <td class="px-3 py-3 text-xs font-mono truncate"><?php echo htmlspecialchars($worker['id']); ?></td>

                                <!-- Worker (photo + name + experience) -->
                                <td class="px-3 py-3 align-top">
                                    <div class="flex items-center space-x-2 overflow-hidden">
                                        <img src="<?php echo htmlspecialchars($worker['photo']); ?>"
                                             alt="<?php echo htmlspecialchars($worker['name']); ?>"
                                             class="w-8 h-8 rounded-full object-cover flex-shrink-0">

                                        <div class="min-w-0">
                                            <div class="text-sm font-medium truncate max-w-[120px]" title="<?php echo htmlspecialchars($worker['name']); ?>">
                                                <?php echo htmlspecialchars($worker['name']); ?>
                                            </div>
                                            <div class="text-xs text-gray-500 truncate max-w-[120px]" title="<?php echo htmlspecialchars($worker['experience'] ?? 'No experience'); ?>">
                                                <?php echo htmlspecialchars($worker['experience'] ?? 'No experience'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact (email + phone) -->
                                <td class="px-3 py-3 align-top">
                                    <div class="truncate text-sm max-w-[130px]" title="<?php echo htmlspecialchars($worker['email']); ?>">
                                        <?php echo htmlspecialchars($worker['email']); ?>
                                    </div>
                                    <div class="truncate text-xs text-gray-500 max-w-[130px]" title="<?php echo htmlspecialchars($worker['phone']); ?>">
                                        <?php echo htmlspecialchars($worker['phone']); ?>
                                    </div>
                                </td>

                                <!-- Skills -->
                                <td class="px-3 py-3 align-top">
                                    <div class="flex flex-wrap gap-1 max-w-[130px] overflow-hidden">
                                        <?php foreach ($worker['skills'] as $skill): ?>
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded truncate" title="<?php echo htmlspecialchars($skill); ?>">
                                                <?php echo htmlspecialchars($skill); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>

                                <!-- Location -->
                                <td class="px-3 py-3 text-sm text-gray-700 truncate max-w-[100px]" title="<?php echo htmlspecialchars($worker['location']); ?>">
                                    <?php echo htmlspecialchars($worker['location']); ?>
                                </td>

                                <!-- Status -->
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $statusClass; ?>"><?php echo htmlspecialchars($worker['status']); ?></span>
                                </td>

                                <!-- Rating -->
                                <td class="px-3 py-3 text-sm">
                                    <div class="flex items-center space-x-1 truncate">
                                        <span class="text-yellow-500"><?php echo formatRating($worker['rating']); ?></span>
                                        <span class="text-gray-600"><?php echo number_format($worker['rating'], 1); ?></span>
                                        <span class="text-xs text-gray-500">(<?php echo $worker['review_count']; ?>)</span>
                                    </div>
                                </td>

                                <!-- Rate -->
                                <td class="px-3 py-3 text-sm font-medium truncate max-w-[70px]">
                                    <?php echo formatCurrency($worker['rate']); ?>
                                </td>

                                <!-- Actions -->
                                <td class="px-3 py-3 whitespace-nowrap">
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

        <!-- MOBILE: card list (md:hidden) -->
        <div class="md:hidden space-y-4">
            <?php if (empty($workers)): ?>
                <div class="px-6 py-4 text-center text-gray-500">Belum ada data kuli.</div>
            <?php else: ?>
                <?php foreach ($workers as $worker): ?>
                    <?php $statusClass = getStatusClass($worker['status'], 'worker'); ?>
                    <div class="bg-white border rounded-lg p-4 shadow-sm">
                        <div class="flex items-start space-x-3">
                            <img src="<?php echo htmlspecialchars($worker['photo']); ?>"
                                 alt="<?php echo htmlspecialchars($worker['name']); ?>"
                                 class="w-12 h-12 rounded-full object-cover flex-shrink-0">

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-sm truncate" title="<?php echo htmlspecialchars($worker['name']); ?>"><?php echo htmlspecialchars($worker['name']); ?></div>
                                        <div class="text-xs text-gray-500 truncate" title="<?php echo htmlspecialchars($worker['experience'] ?? 'No experience'); ?>"><?php echo htmlspecialchars($worker['experience'] ?? 'No experience'); ?></div>
                                    </div>

                                    <div class="text-right ml-3">
                                        <div class="text-xs <?php echo $statusClass; ?> inline-block px-2 py-0.5 rounded-full"><?php echo htmlspecialchars($worker['status']); ?></div>
                                        <div class="mt-1 text-sm font-medium"><?php echo formatCurrency($worker['rate']); ?></div>
                                    </div>
                                </div>

                                <div class="mt-2 text-sm text-gray-600 truncate" title="<?php echo htmlspecialchars($worker['email'] . ' • ' . $worker['phone']); ?>">
                                    <?php echo htmlspecialchars($worker['email']); ?> • <?php echo htmlspecialchars($worker['phone']); ?>
                                </div>

                                <div class="mt-2 flex flex-wrap gap-1">
                                    <?php foreach ($worker['skills'] as $skill): ?>
                                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded" title="<?php echo htmlspecialchars($skill); ?>"><?php echo htmlspecialchars($skill); ?></span>
                                    <?php endforeach; ?>
                                </div>

                                <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                    <div class="truncate max-w-xs" title="<?php echo htmlspecialchars($worker['location']); ?>"><?php echo htmlspecialchars($worker['location']); ?></div>
                                    <div>
                                        <span class="text-yellow-500"><?php echo formatRating($worker['rating']); ?></span>
                                        <span class="ml-1"><?php echo number_format($worker['rating'], 1); ?> (<?php echo $worker['review_count']; ?>)</span>
                                    </div>
                                </div>

                                <div class="mt-3 flex space-x-2">
                                    <button
                                        type="button"
                                        class="edit-worker-btn text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-50 transition duration-200"
                                        data-worker-id="<?php echo htmlspecialchars($worker['id']); ?>"
=======
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
                                    <div class="text-xs text-gray-500">
                                    (<?php echo $worker['review_count']; ?> ulasan)
                                </div>
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
>>>>>>> 129876e8c9e2037e93f044a24ed31c5b23d98a28
                                        title="Edit Worker">
                                        <i data-feather="edit" class="w-4 h-4"></i>
                                    </button>

<<<<<<< HEAD
                                    <button
                                        type="button"
                                        class="delete-worker-btn text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 transition duration-200"
                                        data-worker-id="<?php echo htmlspecialchars($worker['id']); ?>"
                                        data-worker-name="<?php echo htmlspecialchars($worker['name']); ?>"
=======
                                    <button 
                                        type="button" 
                                        class="delete-worker-btn text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 transition duration-200" 
                                        data-worker-id="<?php echo htmlspecialchars($worker['id']); ?>" 
                                        data-worker-name="<?php echo htmlspecialchars($worker['name']); ?>" 
>>>>>>> 129876e8c9e2037e93f044a24ed31c5b23d98a28
                                        title="Delete Worker">
                                        <i data-feather="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
<<<<<<< HEAD
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
=======
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
>>>>>>> 129876e8c9e2037e93f044a24ed31c5b23d98a28
    </div>
</div>
