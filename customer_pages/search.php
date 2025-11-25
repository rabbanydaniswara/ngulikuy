<?php
/**
 * Customer Dashboard - Search View
 */
?>
<div class="mb-6">
    <h2 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-6 text-gray-800 flex items-center">
        <i data-feather="search" class="w-6 h-6 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-blue-600"></i>
        Cari Tukang
    </h2>
    
    <form method="GET" class="bg-white rounded-xl sm:rounded-2xl shadow-lg p-4 sm:p-6">
        <input type="hidden" name="tab" value="search">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">
                    <i data-feather="tool" class="w-3 h-3 sm:w-4 sm:h-4 inline mr-1"></i>
                    Keahlian
                </label>
                <select name="skill" class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors text-sm sm:text-base">
                    <option value="">Semua Keahlian</option>
                    <option value="Construction" <?php echo ($searchFilters['skill'] ?? '') === 'Construction' ? 'selected' : ''; ?>>Construction</option>
                    <option value="Moving" <?php echo ($searchFilters['skill'] ?? '') === 'Moving' ? 'selected' : ''; ?>>Moving</option>
                    <option value="Cleaning" <?php echo ($searchFilters['skill'] ?? '') === 'Cleaning' ? 'selected' : ''; ?>>Cleaning</option>
                    <option value="Gardening" <?php echo ($searchFilters['skill'] ?? '') === 'Gardening' ? 'selected' : ''; ?>>Gardening</option>
                    <option value="Plumbing" <?php echo ($searchFilters['skill'] ?? '') === 'Plumbing' ? 'selected' : ''; ?>>Plumbing</option>
                    <option value="Electrical" <?php echo ($searchFilters['skill'] ?? '') === 'Electrical' ? 'selected' : ''; ?>>Electrical</option>
                    <option value="Painting" <?php echo ($searchFilters['skill'] ?? '') === 'Painting' ? 'selected' : ''; ?>>Painting</option>
                </select>
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">
                    <i data-feather="map-pin" class="w-3 h-3 sm:w-4 sm:h-4 inline mr-1"></i>
                    Lokasi
                </label>
                <input type="text" name="location" value="<?php echo htmlspecialchars($searchFilters['location'] ?? ''); ?>" 
                        placeholder="Masukkan lokasi..." 
                        class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors text-sm sm:text-base">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg sm:rounded-xl hover:from-blue-700 hover:to-indigo-700 font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all text-sm sm:text-base">
                    <i data-feather="search" class="w-4 h-4 sm:w-5 sm:h-5 inline mr-2"></i>
                    Cari
                </button>
            </div>
        </div>
    </form>
</div>

<?php if (empty($workers)): ?>
    <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg p-8 sm:p-12 text-center">
        <div class="inline-block p-4 sm:p-6 bg-gray-100 rounded-full mb-3 sm:mb-4">
            <i data-feather="search" class="w-12 h-12 sm:w-16 sm:h-16 text-gray-400"></i>
        </div>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-2">Tidak ada tukang ditemukan</h3>
        <p class="text-sm sm:text-base text-gray-500 mb-4 sm:mb-6">Coba ubah filter pencarian Anda atau lihat semua tukang</p>
        <a href="?tab=search" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold text-sm sm:text-base">
            Reset Filter
            <i data-feather="refresh-cw" class="w-3 h-3 sm:w-4 sm:h-4 ml-2"></i>
        </a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <?php foreach ($workers as $worker): ?>
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg hover:shadow-2xl transition-all overflow-hidden worker-card">
                <div class="relative">
                    <img src="<?php echo htmlspecialchars($worker['photo']); ?>" 
                            alt="<?php echo htmlspecialchars($worker['name']); ?>"
                            class="w-full h-40 sm:h-56 object-cover">
                    <div class="absolute top-3 sm:top-4 right-3 sm:right-4">
                        <span class="bg-green-500 text-white text-xs font-bold px-2 sm:px-3 py-1 rounded-full shadow-lg">
                            Tersedia
                        </span>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <h3 class="font-bold text-lg sm:text-xl mb-2 text-gray-800"><?php echo htmlspecialchars($worker['name']); ?></h3>
                    <div class="flex flex-wrap gap-1 sm:gap-2 mb-2 sm:mb-3">
                        <?php foreach (array_slice($worker['skills'], 0, 2) as $skill): ?>
                            <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2 sm:px-3 py-1 rounded-full">
                                <?php echo htmlspecialchars($skill); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex items-center mb-2 sm:mb-3">
                        <div class="flex text-yellow-400 text-sm sm:text-base mr-2">
                            <?php echo formatRating($worker['rating']); ?>
                        </div>
                        <span class="text-xs sm:text-sm text-gray-600 font-medium">
                            <?php echo number_format($worker['rating'], 1); ?>
                        </span>
                        <span class="text-xs text-gray-400 ml-1">
                            (<?php echo $worker['review_count']; ?>)
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-600 mb-3 sm:mb-4 flex items-center">
                        <i data-feather="map-pin" class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2 text-gray-400"></i>
                        <?php echo htmlspecialchars($worker['location']); ?>
                    </p>
                    <div class="flex items-center justify-between pt-3 sm:pt-4 border-t">
                        <span class="text-base sm:text-lg font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            <?php echo formatCurrency($worker['rate']); ?>/hari
                        </span>
                        <button onclick="openBookingModal('<?php echo $worker['id']; ?>')" 
                                class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg sm:rounded-xl hover:from-blue-700 hover:to-indigo-700 font-semibold shadow-md hover:shadow-lg transition-all text-xs sm:text-sm">
                            Pesan
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
