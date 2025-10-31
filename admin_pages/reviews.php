<?php
// File ini dipanggil oleh admin_dashboard.php
if (!defined('IS_ADMIN_PAGE')) {
    die('Akses ditolak!');
}
?>

<div class="bg-white rounded-lg shadow p-6 mb-8">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold">Ulasan Pelanggan</h3>
        </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Worker</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Komentar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($reviews)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">Belum ada ulasan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-xs"><?php echo htmlspecialchars((string)$review['review_id']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d M Y', strtotime($review['review_date'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($review['customer_name'] ?: $review['customer_email']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($review['worker_name'] ?: 'N/A'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="font-mono text-xs"><?php echo htmlspecialchars($review['jobId']); ?></span><br>
                                <span><?php echo htmlspecialchars($review['jobType']); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php
                                    $rating = intval($review['rating']);
                                    for ($i = 1; $i <= 5; $i++):
                                        $fillColor = ($i <= $rating) ? 'text-yellow-500' : 'text-gray-300';
                                    ?>
                                        <i data-feather="star" class="w-4 h-4 <?php echo $fillColor; ?>" <?php echo $i <= $rating ? 'fill="currentColor"' : ''; ?>></i>
                                    <?php endfor; ?>
                                    <span class="ml-2 text-sm text-gray-600"><?php echo number_format($review['rating'], 1); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-sm break-words"><?php echo htmlspecialchars($review['comment'] ?: '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                 <button type="button" 
                                        class="delete-review-btn text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 transition duration-200"
                                        data-review-id="<?php echo htmlspecialchars((string)$review['review_id']); ?>"
                                        data-customer-name="<?php echo htmlspecialchars($review['customer_name'] ?: $review['customer_email']); ?>"
                                        title="Delete Review">
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