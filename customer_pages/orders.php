<?php
/**
 * Customer Dashboard - Orders View
 */
?>
<div class="mb-6">
    <h2 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-6 text-gray-800 flex items-center">
        <i data-feather="clipboard" class="w-6 h-6 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-blue-600"></i>
        Pesanan Saya
    </h2>
    
    <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px overflow-x-auto">
                <a href="?tab=orders&status=all" 
                    class="flex-shrink-0 <?php echo $order_status_filter === 'all' ? 'nav-active bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50'; ?> px-4 sm:px-8 py-3 sm:py-4 text-xs sm:text-sm font-semibold transition-colors whitespace-nowrap">
                    <i data-feather="list" class="w-3 h-3 sm:w-4 sm:h-4 inline mr-1 sm:mr-2"></i>
                    Semua
                </a>
                <a href="?tab=orders&status=pending" 
                    class="flex-shrink-0 <?php echo $order_status_filter === 'pending' ? 'nav-active bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50'; ?> px-4 sm:px-8 py-3 sm:py-4 text-xs sm:text-sm font-semibold transition-colors whitespace-nowrap">
                    <i data-feather="clock" class="w-3 h-3 sm:w-4 sm:h-4 inline mr-1 sm:mr-2"></i>
                    Pending
                </a>
                <a href="?tab=orders&status=in-progress" 
                    class="flex-shrink-0 <?php echo $order_status_filter === 'in-progress' ? 'nav-active bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50'; ?> px-4 sm:px-8 py-3 sm:py-4 text-xs sm:text-sm font-semibold transition-colors whitespace-nowrap">
                    <i data-feather="loader" class="w-3 h-3 sm:w-4 sm:h-4 inline mr-1 sm:mr-2"></i>
                    Berjalan
                </a>
                <a href="?tab=orders&status=completed" 
                    class="flex-shrink-0 <?php echo $order_status_filter === 'completed' ? 'nav-active bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50'; ?> px-4 sm:px-8 py-3 sm:py-4 text-xs sm:text-sm font-semibold transition-colors whitespace-nowrap">
                    <i data-feather="check-circle" class="w-3 h-3 sm:w-4 sm:h-4 inline mr-1 sm:mr-2"></i>
                    Selesai
                </a>
            </nav>
        </div>
    </div>
</div>

<?php if (empty($customerOrders)): ?>
    <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg p-8 sm:p-12 text-center">
        <div class="inline-block p-4 sm:p-6 bg-gray-100 rounded-full mb-3 sm:mb-4">
            <i data-feather="clipboard" class="w-12 h-12 sm:w-16 sm:h-16 text-gray-400"></i>
        </div>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-2">Belum ada pesanan</h3>
        <p class="text-sm sm:text-base text-gray-500 mb-4 sm:mb-6">Mulai pesan tukang untuk pekerjaan Anda</p>
        <a href="?tab=search" class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg sm:rounded-xl hover:from-blue-700 hover:to-indigo-700 font-semibold shadow-md hover:shadow-lg transition-all text-sm sm:text-base">
            <i data-feather="plus" class="w-4 h-4 sm:w-5 sm:h-5 mr-2"></i>
            Buat Pesanan
        </a>
    </div>
<?php else: ?>
    <div class="space-y-4 sm:space-y-6">
        <?php foreach ($customerOrders as $order): 
            $statusInfo = getStatusTextAndClass($order['status']);
        ?>
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg hover:shadow-xl transition-all overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 sm:px-6 py-3 sm:py-4 border-b flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0">
                    <div>
                        <h3 class="text-base sm:text-xl font-bold text-gray-800 flex items-center">
                            <i data-feather="briefcase" class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-blue-600"></i>
                            <?php echo htmlspecialchars($order['jobType']); ?>
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">
                            #<?php echo htmlspecialchars($order['jobId']); ?>
                            <span class="mx-1 sm:mx-2">•</span>
                            <i data-feather="calendar" class="w-3 h-3 inline"></i>
                            <?php echo date('d M Y', strtotime($order['createdAt'])); ?>
                        </p>
                    </div>
                    <span class="px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-bold rounded-lg sm:rounded-xl <?php echo $statusInfo['class']; ?> inline-block shadow-sm w-fit">
                        <?php echo $statusInfo['text']; ?>
                    </span>
                </div>
                
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6 mb-4 sm:mb-6">
                        <div class="space-y-2 sm:space-y-3">
                            <div class="flex items-start">
                                <i data-feather="user" class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 mr-2 sm:mr-3 mt-0.5 flex-shrink-0"></i>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Tukang</p>
                                    <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($order['workerName']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i data-feather="calendar" class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 mr-2 sm:mr-3 mt-0.5 flex-shrink-0"></i>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Periode</p>
                                    <p class="text-sm font-semibold text-gray-800">
                                        <?php echo date('d M', strtotime($order['startDate'])); ?> - 
                                        <?php echo date('d M Y', strtotime($order['endDate'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2 sm:space-y-3">
                            <div class="flex items-start">
                                <i data-feather="map-pin" class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 mr-2 sm:mr-3 mt-0.5 flex-shrink-0"></i>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Lokasi</p>
                                    <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($order['location']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i data-feather="dollar-sign" class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 mr-2 sm:mr-3 mt-0.5 flex-shrink-0"></i>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Total Biaya</p>
                                    <p class="text-base sm:text-lg font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                                        <?php echo formatCurrency($order['price']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row flex-wrap gap-2 sm:gap-3 pt-3 sm:pt-4 border-t">
                        <a href="detail_pesanan.php?id=<?php echo $order['jobId']; ?>" 
                            class="inline-flex items-center justify-center px-4 sm:px-5 py-2 sm:py-2.5 bg-gray-100 text-gray-700 rounded-lg sm:rounded-xl hover:bg-gray-200 text-xs sm:text-sm font-semibold transition-all">
                            <i data-feather="eye" class="w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                            Lihat Detail
                        </a>
                        <?php if ($order['status'] === 'completed' && !$order['has_review']): ?>
                            <button onclick="openReviewModal('<?php echo $order['jobId']; ?>', '<?php echo $order['workerId']; ?>')" 
                                    class="inline-flex items-center justify-center px-4 sm:px-5 py-2 sm:py-2.5 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-lg sm:rounded-xl hover:from-yellow-600 hover:to-orange-600 text-xs sm:text-sm font-semibold shadow-md hover:shadow-lg transition-all">
                                <i data-feather="star" class="w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                                Beri Ulasan
                            </button>
                        <?php elseif ($order['status'] === 'completed' && $order['has_review']): ?>
                            <span class="inline-flex items-center justify-center px-4 sm:px-5 py-2 sm:py-2.5 bg-green-100 text-green-700 rounded-lg sm:rounded-xl text-xs sm:text-sm font-semibold">
                                <i data-feather="check" class="w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>
                                Sudah Diulas
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
