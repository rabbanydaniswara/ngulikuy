<?php
/**
 * Customer Dashboard - Search View
 */
?>
<div class="mb-8">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Cari Pekerja</h2>
        <p class="text-gray-500 mt-1">Temukan pekerja profesional yang sesuai dengan kebutuhan Anda</p>
    </div>
    
    <!-- Search Filter Card -->
    <div class="card mb-8">
        <form method="GET" class="p-6">
            <input type="hidden" name="tab" value="search">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <!-- Skill Filter -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        Keahlian
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-feather="tool" class="h-4 w-4 text-gray-400"></i>
                        </div>
                        <select name="skill" class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm bg-white">
                            <option value="">Semua Keahlian</option>
                            <?php 
                                $skills = get_construction_skills();
                                foreach ($skills as $skill_option) {
                                    $selected = (($searchFilters['skill'] ?? '') === $skill_option) ? 'selected' : '';
                                    echo "<option value=\"{$skill_option}\" {$selected}>{$skill_option}</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                
                <!-- Location Filter -->
                <div class="md:col-span-6">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        Lokasi
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-feather="map-pin" class="h-4 w-4 text-gray-400"></i>
                        </div>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($searchFilters['location'] ?? ''); ?>" 
                                placeholder="Cari berdasarkan kota atau area..." 
                                class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                </div>
                
                <!-- Search Button -->
                <div class="md:col-span-2 flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 font-medium shadow-sm transition-colors text-sm flex items-center justify-center">
                        <i data-feather="search" class="w-4 h-4 mr-2"></i>
                        Cari
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php if (empty($workers)): ?>
        <div class="card p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                <i data-feather="search" class="w-8 h-8 text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">Tidak ada pekerja ditemukan</h3>
            <p class="text-gray-500 mb-6">Coba sesuaikan filter pencarian Anda untuk hasil yang lebih baik.</p>
            <a href="?tab=search" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium text-sm">
                <i data-feather="refresh-cw" class="w-4 h-4 mr-2"></i>
                Reset Filter
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($workers as $worker): ?>
                <div class="card group hover:-translate-y-1 transition-all duration-300 h-full flex flex-col">
                    <!-- Card Header / Image -->
                    <div class="relative h-48 bg-gray-100 overflow-hidden rounded-t-xl">
                        <?php if ($worker['url_foto']): ?>
                            <img src="<?php echo htmlspecialchars($worker['url_foto']); ?>" 
                                 alt="<?php echo htmlspecialchars($worker['nama']); ?>"
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-gray-200 text-gray-400">
                                <i data-feather="user" class="w-12 h-12"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="absolute top-3 right-3">
                            <span class="bg-white/90 backdrop-blur-sm text-green-600 text-xs font-bold px-2.5 py-1 rounded-full shadow-sm flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                Tersedia
                            </span>
                        </div>
                    </div>
                    
                    <!-- Card Body -->
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="mb-4">
                            <h3 class="font-bold text-lg text-gray-900 mb-1 view-worker-btn cursor-pointer hover:text-blue-600 transition-colors" 
                                data-worker-id="<?php echo htmlspecialchars($worker['id_pekerja']); ?>">
                                <?php echo htmlspecialchars($worker['nama']); ?>
                            </h3>
                            
                            <div class="flex items-center gap-1 mb-3">
                                <i data-feather="star" class="w-4 h-4 text-yellow-400 fill-current"></i>
                                <span class="font-bold text-gray-900 text-sm"><?php echo number_format($worker['rating'], 1); ?></span>
                                <span class="text-xs text-gray-400">(<?php echo $worker['review_count']; ?> ulasan)</span>
                            </div>

                            <div class="flex flex-wrap gap-2 mb-3">
                                <?php foreach (array_slice($worker['keahlian'], 0, 3) as $skill): ?>
                                    <span class="bg-blue-50 text-blue-700 text-xs font-medium px-2 py-1 rounded-md border border-blue-100">
                                        <?php echo htmlspecialchars($skill); ?>
                                    </span>
                                <?php endforeach; ?>
                                <?php if (count($worker['keahlian']) > 3): ?>
                                    <span class="text-xs text-gray-400 py-1">+<?php echo count($worker['keahlian']) - 3; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                <i data-feather="map-pin" class="w-3.5 h-3.5 text-gray-400"></i>
                                <span class="truncate"><?php echo htmlspecialchars($worker['lokasi']); ?></span>
                            </p>
                        </div>
                        
                        <div class="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Mulai dari</p>
                                <p class="text-base font-bold text-gray-900">
                                    <?php echo formatCurrency($worker['tarif_per_jam']); ?>
                                    <span class="text-xs font-normal text-gray-400">/hari</span>
                                </p>
                            </div>
                            <button onclick="openBookingModal('<?php echo $worker['id_pekerja']; ?>')" 
                                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium shadow-sm transition-colors text-sm">
                                Pesan
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
