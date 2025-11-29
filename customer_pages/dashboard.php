<?php
/**
 * Customer Dashboard - Home/Dashboard View
 */
?>

<!-- Hero Section -->
<div class="bg-blue-600 rounded-2xl text-white p-8 sm:p-12 mb-8 shadow-xl relative overflow-hidden">
    <!-- Abstract Shapes -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-20 -mt-20 blur-3xl"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full -ml-10 -mb-10 blur-2xl"></div>
    
    <div class="relative z-10 max-w-2xl">
        <h1 class="text-3xl sm:text-4xl font-bold mb-4 leading-tight">Solusi Cepat untuk Kebutuhan Pekerja Harian</h1>
        <p class="text-blue-100 text-lg mb-8 leading-relaxed">Temukan pekerja berpengalaman dengan mudah, transparan, dan terpercaya untuk proyek Anda.</p>
        
        <div class="flex flex-wrap gap-4">
            <a href="?tab=search" class="inline-flex items-center bg-white text-blue-600 px-6 py-3 rounded-xl font-semibold hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <i data-feather="search" class="w-5 h-5 mr-2"></i>
                Cari Pekerja
            </a>
            <a href="?tab=post_job" class="inline-flex items-center bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold hover:bg-blue-800 transition-all border border-blue-500">
                <i data-feather="plus-circle" class="w-5 h-5 mr-2"></i>
                Buat Lowongan
            </a>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
    <!-- Total Workers -->
    <div class="card p-6 flex items-start space-x-4">
        <div class="p-3 bg-blue-50 rounded-xl text-blue-600">
            <i data-feather="users" class="w-6 h-6"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Total Pekerja</p>
            <h3 class="text-2xl font-bold text-gray-900"><?php echo count(getWorkers()); ?></h3>
        </div>
    </div>

    <!-- Completed Orders -->
    <div class="card p-6 flex items-start space-x-4">
        <div class="p-3 bg-green-50 rounded-xl text-green-600">
            <i data-feather="check-circle" class="w-6 h-6"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Pesanan Selesai</p>
            <h3 class="text-2xl font-bold text-gray-900">
                <?php echo count(array_filter(getCustomerOrders($customer_email), function($o) { 
                    return $o['status_pekerjaan'] === 'completed'; 
                })); ?>
            </h3>
        </div>
    </div>

    <!-- Pending Orders -->
    <div class="card p-6 flex items-start space-x-4">
        <div class="p-3 bg-yellow-50 rounded-xl text-yellow-600">
            <i data-feather="clock" class="w-6 h-6"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Menunggu</p>
            <h3 class="text-2xl font-bold text-gray-900"><?php echo $pendingOrderCount; ?></h3>
        </div>
    </div>

    <!-- User Address -->
    <div class="card p-6 flex items-start space-x-4">
        <div class="p-3 bg-purple-50 rounded-xl text-purple-600">
            <i data-feather="map-pin" class="w-6 h-6"></i>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-gray-500 mb-1">Lokasi Anda</p>
            <h3 class="text-sm font-semibold text-gray-900 truncate" title="<?php echo htmlspecialchars($customer_address); ?>">
                <?php echo htmlspecialchars($customer_address); ?>
            </h3>
        </div>
    </div>
</div>

<!-- Top Workers Section -->
<div class="mb-8">
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Pekerja Terbaik</h2>
            <p class="text-gray-500 mt-1">Mitra dengan rating tertinggi bulan ini</p>
        </div>
        <a href="?tab=search" class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center group">
            Lihat Semua
            <i data-feather="arrow-right" class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform"></i>
        </a>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($topWorkers as $worker): ?>
            <div class="card group hover:-translate-y-1 transition-all duration-300">
                <div class="p-6 text-center">
                    <div class="relative inline-block mb-4">
                        <img src="<?php echo htmlspecialchars($worker['url_foto']); ?>" 
                             alt="<?php echo htmlspecialchars($worker['nama']); ?>"
                             class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-md mx-auto">
                        <div class="absolute bottom-0 right-0 bg-white rounded-full p-1 shadow-sm border border-gray-100">
                            <i data-feather="award" class="w-4 h-4 text-blue-600"></i>
                        </div>
                    </div>
                    
                    <h3 class="text-lg font-bold text-gray-900 mb-1 view-worker-btn cursor-pointer hover:text-blue-600 transition-colors" 
                        data-worker-id="<?php echo htmlspecialchars($worker['id_pekerja']); ?>">
                        <?php echo htmlspecialchars($worker['nama']); ?>
                    </h3>
                    
                    <p class="text-sm text-gray-500 mb-3">
                        <?php echo htmlspecialchars($worker['keahlian'][0] ?? 'Umum'); ?>
                    </p>
                    
                    <div class="flex justify-center items-center gap-1 mb-4">
                        <i data-feather="star" class="w-4 h-4 text-yellow-400 fill-current"></i>
                        <span class="font-bold text-gray-900"><?php echo number_format($worker['rating'], 1); ?></span>
                        <span class="text-xs text-gray-400">(<?php echo $worker['review_count']; ?> ulasan)</span>
                    </div>
                    
                    <div class="border-t border-gray-50 pt-4 mt-4">
                        <p class="text-sm font-semibold text-gray-900 mb-3">
                            <?php echo formatCurrency($worker['tarif_per_jam']); ?> <span class="text-gray-400 font-normal">/hari</span>
                        </p>
                        <button onclick="openBookingModal('<?php echo $worker['id_pekerja']; ?>')" 
                                class="w-full py-2.5 rounded-lg bg-blue-50 text-blue-600 font-medium text-sm hover:bg-blue-100 transition-colors">
                            Pesan Jasa
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
