<?php
/**
 * Customer Dashboard - Orders View
 */
?>
<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Pesanan Saya</h2>
            <p class="text-gray-500 mt-1">Kelola dan pantau status pesanan Anda</p>
        </div>
    </div>
    
    <!-- Filter Tabs -->
    <div class="bg-white border-b border-gray-200 mb-6">
        <nav class="flex -mb-px overflow-x-auto space-x-8">
            <a href="?tab=orders&status=all" 
                class="<?php echo $order_status_filter === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                Semua
            </a>
            <a href="?tab=orders&status=pending" 
                class="<?php echo $order_status_filter === 'pending' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                Menunggu
            </a>
            <a href="?tab=orders&status=in-progress" 
                class="<?php echo $order_status_filter === 'in-progress' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                Berjalan
            </a>
            <a href="?tab=orders&status=completed" 
                class="<?php echo $order_status_filter === 'completed' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                Selesai
            </a>
        </nav>
    </div>

    <?php if (empty($customerOrders)): ?>
        <div class="card p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                <i data-feather="clipboard" class="w-8 h-8 text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">Belum ada pesanan</h3>
            <p class="text-gray-500 mb-6">Mulai pesan pekerja untuk proyek Anda sekarang.</p>
            <a href="?tab=search" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors shadow-sm">
                <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                Buat Pesanan Baru
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($customerOrders as $order): 
                $statusInfo = getStatusTextAndClass($order['status_pekerjaan']);
                // Map status classes to new design system
                $badgeClass = 'status-badge';
                if (strpos($statusInfo['class'], 'green') !== false) $badgeClass .= ' status-completed';
                elseif (strpos($statusInfo['class'], 'yellow') !== false) $badgeClass .= ' status-pending';
                elseif (strpos($statusInfo['class'], 'blue') !== false) $badgeClass .= ' status-in-progress';
                else $badgeClass .= ' status-cancelled';
            ?>
                <div class="card overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <h3 class="text-lg font-bold text-gray-900">
                                        <?php echo htmlspecialchars($order['jenis_pekerjaan']); ?>
                                    </h3>
                                    <span class="<?php echo $badgeClass; ?>">
                                        <?php echo $statusInfo['text']; ?>
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 flex items-center gap-2">
                                    <span>#<?php echo htmlspecialchars($order['id_pekerjaan']); ?></span>
                                    <span>&bull;</span>
                                    <span>Dibuat pada <?php echo date('d M Y', strtotime($order['dibuat_pada'])); ?></span>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500 mb-1">Total Biaya</p>
                                <p class="text-xl font-bold text-gray-900">
                                    <?php echo formatCurrency($order['harga']); ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-t border-b border-gray-50">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-blue-50 rounded-lg text-blue-600 mt-0.5">
                                    <i data-feather="user" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Pekerja</p>
                                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($order['nama_pekerja']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-purple-50 rounded-lg text-purple-600 mt-0.5">
                                    <i data-feather="calendar" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Periode</p>
                                    <p class="font-medium text-gray-900">
                                        <?php echo date('d M', strtotime($order['tanggal_mulai'])); ?> - 
                                        <?php echo date('d M Y', strtotime($order['tanggal_selesai'])); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-3 sm:col-span-2">
                                <div class="p-2 bg-gray-50 rounded-lg text-gray-600 mt-0.5">
                                    <i data-feather="map-pin" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Lokasi</p>
                                    <p class="text-gray-700"><?php echo htmlspecialchars($order['lokasi']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-4">
                            <a href="detail_pesanan.php?id=<?php echo $order['id_pekerjaan']; ?>" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <i data-feather="eye" class="w-4 h-4 mr-2"></i>
                                Detail
                            </a>
                            
                            <?php if ($order['status_pekerjaan'] === 'completed' && !$order['has_review']): ?>
                                <button onclick="openReviewModal('<?php echo $order['id_pekerjaan']; ?>', '<?php echo $order['id_pekerja']; ?>')" 
                                        class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-lg text-sm font-medium hover:bg-yellow-600 shadow-sm transition-colors">
                                    <i data-feather="star" class="w-4 h-4 mr-2"></i>
                                    Beri Ulasan
                                </button>
                            <?php elseif ($order['status_pekerjaan'] === 'completed' && $order['has_review']): ?>
                                <span class="inline-flex items-center px-4 py-2 bg-green-50 text-green-700 rounded-lg text-sm font-medium border border-green-100">
                                    <i data-feather="check" class="w-4 h-4 mr-2"></i>
                                    Sudah Diulas
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
