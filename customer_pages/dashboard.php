<?php
/**
 * Customer Dashboard - Home/Dashboard View
 */
?>
<div class="gradient-bg rounded-xl sm:rounded-2xl text-white p-6 sm:p-10 mb-6 sm:mb-8 shadow-2xl relative overflow-hidden hero-section">
    <div class="absolute top-0 right-0 w-32 sm:w-64 h-32 sm:h-64 bg-white/10 rounded-full -mr-16 sm:-mr-32 -mt-16 sm:-mt-32"></div>
    <div class="absolute bottom-0 left-0 w-24 sm:w-48 h-24 sm:h-48 bg-white/10 rounded-full -ml-12 sm:-ml-24 -mb-12 sm:-mb-24"></div>
    <div class="relative z-10">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-3 sm:mb-4">Solusi Cepat untuk Kebutuhan Tukang Harian</h1>
        <p class="text-base sm:text-lg mb-4 sm:mb-6 text-blue-100">Temukan tukang berpengalaman dengan mudah dan transparan</p>
        <a href="?tab=search" class="inline-flex items-center bg-white text-blue-600 px-6 sm:px-8 py-2.5 sm:py-3 rounded-lg sm:rounded-xl font-semibold hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1 text-sm sm:text-base">
            <i data-feather="search" class="w-4 h-4 sm:w-5 sm:h-5 mr-2"></i>
            Cari Tukang Sekarang
        </a>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
    <div class="bg-white rounded-xl shadow-lg p-5 sm:p-6 card-hover border-t-4 border-blue-500">
        <div class="flex items-center">
            <div class="p-3 sm:p-4 rounded-xl bg-gradient-to-br from-blue-100 to-blue-200 text-blue-600 mr-3 sm:mr-4">
                <i data-feather="users" class="w-5 h-5 sm:w-6 sm:h-6"></i>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-gray-500 font-medium">Total Tukang</p>
                <h3 class="text-2xl sm:text-3xl font-bold text-gray-800"><?php echo count(getWorkers()); ?></h3>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-5 sm:p-6 card-hover border-t-4 border-green-500">
        <div class="flex items-center">
            <div class="p-3 sm:p-4 rounded-xl bg-gradient-to-br from-green-100 to-green-200 text-green-600 mr-3 sm:mr-4">
                <i data-feather="check-circle" class="w-5 h-5 sm:w-6 sm:h-6"></i>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-gray-500 font-medium">Pesanan Selesai</p>
                <h3 class="text-2xl sm:text-3xl font-bold text-gray-800">
                    <?php echo count(array_filter(getCustomerOrders($customer_email), function($o) { 
                        return $o['status'] === 'completed'; 
                    })); ?>
                </h3>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-5 sm:p-6 card-hover border-t-4 border-yellow-500">
        <div class="flex items-center">
            <div class="p-3 sm:p-4 rounded-xl bg-gradient-to-br from-yellow-100 to-yellow-200 text-yellow-600 mr-3 sm:mr-4">
                <i data-feather="clock" class="w-5 h-5 sm:w-6 sm:h-6"></i>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-gray-500 font-medium">Pesanan Pending</p>
                <h3 class="text-2xl sm:text-3xl font-bold text-gray-800"><?php echo $pendingOrderCount; ?></h3>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-5 sm:p-6 card-hover border-t-4 border-purple-500">
        <div class="flex items-center">
            <div class="p-3 sm:p-4 rounded-xl bg-gradient-to-br from-purple-100 to-purple-200 text-purple-600 mr-3 sm:mr-4">
                <i data-feather="home" class="w-5 h-5 sm:w-6 sm:h-6"></i>
            </div>
            <div class="w-full overflow-hidden">
                <p class="text-xs sm:text-sm text-gray-500 font-medium">Alamat Anda</p>
                <h3 class="text-base sm:text-lg font-bold text-gray-800 truncate"><?php echo htmlspecialchars($customer_address); ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="mb-8 sm:mb-12">
    <div class="flex justify-between items-center mb-4 sm:mb-6">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
            <i data-feather="star" class="w-6 h-6 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-yellow-500"></i>
            Tukang Terbaik
        </h2>
        <a href="?tab=search" class="text-blue-600 hover:text-blue-700 font-semibold flex items-center group text-sm sm:text-base">
            Lihat Semua
            <i data-feather="arrow-right" class="w-3 h-3 sm:w-4 sm:h-4 ml-1 sm:ml-2 group-hover:translate-x-1 transition-transform"></i>
        </a>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <?php foreach ($topWorkers as $worker): ?>
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg overflow-hidden worker-card card-hover">
                <div class="relative h-24 sm:h-32 bg-gradient-to-br from-blue-400 to-indigo-500">
                    <div class="absolute -bottom-10 sm:-bottom-12 left-1/2 transform -translate-x-1/2">
                        <img src="<?php echo htmlspecialchars($worker['photo']); ?>" 
                                alt="<?php echo htmlspecialchars($worker['name']); ?>"
                                class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover border-4 border-white shadow-lg">
                    </div>
                </div>
                <div class="pt-12 sm:pt-16 pb-4 sm:pb-6 px-4 sm:px-6 text-center">
                    <h3 class="font-bold text-base sm:text-lg text-gray-800 mb-1"><?php echo htmlspecialchars($worker['name']); ?></h3>
                    <p class="text-xs sm:text-sm text-gray-600 mb-2 sm:mb-3">
                        <?php echo htmlspecialchars($worker['skills'][0] ?? ''); ?>
                    </p>
                    <div class="flex justify-center items-center mb-2 sm:mb-3">
                        <div class="flex text-yellow-400 text-sm sm:text-base">
                            <?php echo formatRating($worker['rating']); ?>
                        </div>
                        <span class="text-xs text-gray-500 ml-1 sm:ml-2 font-medium">
                            (<?php echo $worker['review_count']; ?>)
                        </span>
                    </div>
                    <p class="text-sm sm:text-base font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-3 sm:mb-4">
                        <?php echo formatCurrency($worker['rate']); ?>/hari
                    </p>
                    <button onclick="openBookingModal('<?php echo $worker['id']; ?>')" 
                            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl hover:from-blue-700 hover:to-indigo-700 text-xs sm:text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all">
                        Pesan Sekarang
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
