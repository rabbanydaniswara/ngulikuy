<?php
// File ini dipanggil oleh admin_dashboard.php
if (!defined('IS_ADMIN_PAGE')) {
    die('Akses ditolak!');
}
?>

<div class="bg-white rounded-lg shadow p-6 mb-8">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold">Ulasan Pelanggan</h3>
<<<<<<< HEAD
    </div>

    <div class="w-full">
        <!-- TABLE: tampil di md ke atas -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full table-fixed divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-16">ID</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-28">Tanggal</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-40">Customer</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-40">Worker</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-36">Job</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-28">Rating</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Komentar</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-20">Aksi</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($reviews)): ?>
                        <tr>
                            <td colspan="8" class="px-3 py-4 text-center text-gray-500">Belum ada ulasan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <?php
                                $reviewId = htmlspecialchars((string)$review['review_id']);
                                $date = !empty($review['review_date']) ? date('d M Y', strtotime($review['review_date'])) : '-';
                                $customer = htmlspecialchars($review['customer_name'] ?: $review['customer_email']);
                                $workerName = htmlspecialchars($review['worker_name'] ?: 'N/A');
                                $jobId = htmlspecialchars($review['jobId'] ?? '');
                                $jobType = htmlspecialchars($review['jobType'] ?? '');
                                $rating = floatval($review['rating'] ?? 0);
                                $comment = htmlspecialchars($review['comment'] ?: '-');
                            ?>
                            <tr>
                                <td class="px-3 py-3 text-xs font-mono truncate" title="<?php echo $reviewId; ?>"><?php echo $reviewId; ?></td>
                                <td class="px-3 py-3 text-sm text-gray-500 truncate" title="<?php echo $date; ?>"><?php echo $date; ?></td>
                                <td class="px-3 py-3 text-sm font-medium text-gray-900 truncate max-w-[150px]" title="<?php echo $customer; ?>"><?php echo $customer; ?></td>
                                <td class="px-3 py-3 text-sm text-gray-700 truncate max-w-[140px]" title="<?php echo $workerName; ?>"><?php echo $workerName; ?></td>
                                <td class="px-3 py-3 text-sm text-gray-500">
                                    <div class="font-mono text-xs truncate" title="<?php echo $jobId; ?>"><?php echo $jobId; ?></div>
                                    <div class="truncate max-w-[180px]" title="<?php echo $jobType; ?>"><?php echo $jobType; ?></div>
                                </td>

                                <td class="px-3 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php
                                            $full = intval(round($rating));
                                            for ($i = 1; $i <= 5; $i++):
                                                $fillClass = ($i <= $full) ? 'text-yellow-500' : 'text-gray-300';
                                        ?>
                                            <i data-feather="star" class="w-4 h-4 <?php echo $fillClass; ?>" <?php echo ($i <= $full) ? 'fill="currentColor"' : ''; ?>></i>
                                        <?php endfor; ?>
                                        <span class="ml-2 text-sm text-gray-600"><?php echo number_format($rating, 1); ?></span>
                                    </div>
                                </td>

                                <td class="px-3 py-3 text-sm text-gray-600 break-words truncate max-w-[300px]" title="<?php echo $comment; ?>"><?php echo $comment; ?></td>

                                <td class="px-3 py-3 whitespace-nowrap">
                                    <button type="button"
                                            class="delete-review-btn text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 transition duration-200"
                                            data-review-id="<?php echo $reviewId; ?>"
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

        <!-- MOBILE: card list -->
        <div class="md:hidden space-y-4">
            <?php if (empty($reviews)): ?>
                <div class="px-4 py-4 text-center text-gray-500">Belum ada ulasan.</div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <?php
                        $reviewId = htmlspecialchars((string)$review['review_id']);
                        $date = !empty($review['review_date']) ? date('d M Y', strtotime($review['review_date'])) : '-';
                        $customer = htmlspecialchars($review['customer_name'] ?: $review['customer_email']);
                        $workerName = htmlspecialchars($review['worker_name'] ?: 'N/A');
                        $jobId = htmlspecialchars($review['jobId'] ?? '');
                        $jobType = htmlspecialchars($review['jobType'] ?? '');
                        $rating = floatval($review['rating'] ?? 0);
                        $comment = htmlspecialchars($review['comment'] ?: '-');
                    ?>
                    <div class="bg-white border rounded-lg p-4 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div class="min-w-0 pr-3">
                                <div class="flex items-baseline justify-start space-x-3">
                                    <div class="text-sm font-medium truncate" title="<?php echo $reviewId . ' — ' . $date; ?>"><?php echo $reviewId; ?></div>
                                    <div class="text-xs text-gray-500 truncate" title="<?php echo $date; ?>"><?php echo $date; ?></div>
                                </div>

                                <div class="mt-2 text-sm text-gray-900 truncate" title="<?php echo $customer; ?>"><?php echo $customer; ?></div>
                                <div class="text-xs text-gray-600 truncate" title="<?php echo $workerName; ?>"><?php echo $workerName; ?></div>

                                <div class="mt-2 text-xs text-gray-500 truncate" title="<?php echo $jobId . ' • ' . $jobType; ?>">
                                    <span class="font-mono"><?php echo $jobId; ?></span> • <?php echo $jobType; ?>
                                </div>

                                <div class="mt-3 text-sm text-gray-700 break-words" title="<?php echo $comment; ?>"><?php echo $comment; ?></div>
                            </div>

                            <div class="flex-shrink-0 text-right ml-3">
                                <div class="flex items-center justify-end">
                                    <?php
                                        $full = intval(round($rating));
                                        for ($i = 1; $i <= 5; $i++):
                                            $fillClass = ($i <= $full) ? 'text-yellow-500' : 'text-gray-300';
                                    ?>
                                        <i data-feather="star" class="w-4 h-4 <?php echo $fillClass; ?>" <?php echo ($i <= $full) ? 'fill="currentColor"' : ''; ?>></i>
                                    <?php endfor; ?>
                                </div>

                                <div class="mt-3 flex justify-end">
                                    <button type="button"
                                            class="delete-review-btn text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 transition duration-200"
                                            data-review-id="<?php echo $reviewId; ?>"
                                            title="Delete Review">
                                        <i data-feather="trash-2" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
=======
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
>>>>>>> 129876e8c9e2037e93f044a24ed31c5b23d98a28
