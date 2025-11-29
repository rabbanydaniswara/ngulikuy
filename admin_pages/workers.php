<?php
// File ini dipanggil oleh admin_dashboard.php
if (!defined('IS_ADMIN_PAGE')) {
    die('Akses ditolak!');
}
?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8">
    <!-- Header & Search -->
    <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-gray-800">Data Pekerja</h3>
            <p class="text-sm text-gray-500 mt-1">Kelola informasi mitra pekerja NguliKuy</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <!-- Search Bar -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-feather="search" class="h-4 w-4 text-gray-400"></i>
                </div>
                <input type="text" 
                       id="workerSearchInput" 
                       placeholder="Cari nama, email, atau skill..." 
                       class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full sm:w-64 transition-all">
            </div>

            <a href="?tab=add_worker"
               class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm hover:shadow">
                <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                Tambah Pekerja
            </a>
        </div>
    </div>

    <div class="w-full">
        <!-- TABLE: Desktop View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pekerja</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kontak</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Keahlian</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rating</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tarif</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-50" id="workerTableBody">
                    <?php if (empty($workers)): ?>
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="bg-gray-50 rounded-full p-3 mb-3">
                                        <i data-feather="users" class="w-6 h-6 text-gray-400"></i>
                                    </div>
                                    <p>Belum ada data pekerja.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($workers as $worker): ?>
                            <?php $statusClass = getStatusClass($worker['status_ketersediaan'], 'worker'); ?>
                            <tr class="hover:bg-gray-50/50 transition-colors group worker-row">

                                <!-- ID -->
                                <td class="px-6 py-4 text-xs font-mono text-gray-500">
                                    <?php echo htmlspecialchars($worker['id_pekerja']); ?>
                                </td>

                                <!-- Worker -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <img src="<?php echo htmlspecialchars($worker['url_foto']); ?>"
                                             alt="<?php echo htmlspecialchars($worker['nama']); ?>"
                                             class="w-10 h-10 rounded-full object-cover border border-gray-100 shadow-sm">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900 view-worker-btn cursor-pointer hover:text-blue-600 transition-colors" 
                                                 data-worker-id="<?php echo htmlspecialchars($worker['id_pekerja']); ?>">
                                                <?php echo htmlspecialchars($worker['nama']); ?>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                <?php echo htmlspecialchars($worker['pengalaman'] ?? 'Belum ada pengalaman'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col space-y-1">
                                        <div class="flex items-center text-xs text-gray-600">
                                            <i data-feather="mail" class="w-3 h-3 mr-1.5 text-gray-400"></i>
                                            <?php echo htmlspecialchars($worker['email']); ?>
                                        </div>
                                        <div class="flex items-center text-xs text-gray-600">
                                            <i data-feather="phone" class="w-3 h-3 mr-1.5 text-gray-400"></i>
                                            <?php echo htmlspecialchars($worker['telepon']); ?>
                                        </div>
                                    </div>
                                </td>

                                <!-- Skills -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5 max-w-[200px]">
                                        <?php foreach (array_slice($worker['keahlian'], 0, 3) as $skill): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                                <?php echo htmlspecialchars($skill); ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if (count($worker['keahlian']) > 3): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-50 text-gray-600 border border-gray-100">
                                                +<?php echo count($worker['keahlian']) - 3; ?>
                                            </span>
                                        <?php endif; ?>
                                        <!-- Hidden span for search indexing -->
                                        <span class="hidden"><?php echo htmlspecialchars(implode(' ', $worker['keahlian'])); ?></span>
                                    </div>
                                </td>

                                <!-- Location -->
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($worker['lokasi']); ?>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5 opacity-60"></span>
                                        <?php echo htmlspecialchars($worker['status_ketersediaan']); ?>
                                    </span>
                                </td>

                                <!-- Rating -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <i data-feather="star" class="w-3.5 h-3.5 text-yellow-400 fill-current"></i>
                                        <span class="ml-1.5 text-sm font-medium text-gray-900"><?php echo number_format($worker['rating'], 1); ?></span>
                                        <span class="ml-1 text-xs text-gray-400">(<?php echo $worker['review_count']; ?>)</span>
                                    </div>
                                </td>

                                <!-- Rate -->
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <?php echo formatCurrency($worker['tarif_per_jam']); ?>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            type="button"
                                            class="edit-worker-btn p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            data-worker-id="<?php echo htmlspecialchars($worker['id_pekerja']); ?>"
                                            title="Edit Data">
                                            <i data-feather="edit-2" class="w-4 h-4"></i>
                                        </button>

                                        <button
                                            type="button"
                                            class="delete-worker-btn p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            data-worker-id="<?php echo htmlspecialchars($worker['id_pekerja']); ?>"
                                            data-worker-name="<?php echo htmlspecialchars($worker['nama']); ?>"
                                            title="Hapus Data">
                                            <i data-feather="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- No Results Message (Hidden by default) -->
            <div id="noResultsMessage" class="hidden px-6 py-12 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 mb-3">
                    <i data-feather="search" class="w-5 h-5 text-gray-400"></i>
                </div>
                <h3 class="text-sm font-medium text-gray-900">Tidak ada hasil ditemukan</h3>
                <p class="text-xs text-gray-500 mt-1">Coba kata kunci pencarian lain.</p>
            </div>
        </div>

        <!-- MOBILE: Card List -->
        <div class="md:hidden p-4 space-y-4" id="workerMobileList">
            <?php if (empty($workers)): ?>
                <div class="text-center py-8 text-gray-500">
                    <p>Belum ada data pekerja.</p>
                </div>
            <?php else: ?>
                <?php foreach ($workers as $worker): ?>
                    <?php $statusClass = getStatusClass($worker['status_ketersediaan'], 'worker'); ?>
                    <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm worker-card-mobile">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <img src="<?php echo htmlspecialchars($worker['url_foto']); ?>"
                                     alt="<?php echo htmlspecialchars($worker['nama']); ?>"
                                     class="w-12 h-12 rounded-full object-cover border border-gray-100">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 view-worker-btn" 
                                        data-worker-id="<?php echo htmlspecialchars($worker['id_pekerja']); ?>">
                                        <?php echo htmlspecialchars($worker['nama']); ?>
                                    </h4>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($worker['pengalaman'] ?? 'Belum ada pengalaman'); ?></p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-medium <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($worker['status_ketersediaan']); ?>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="bg-gray-50 rounded-lg p-2">
                                <p class="text-[10px] text-gray-500 uppercase tracking-wide">Tarif</p>
                                <p class="text-xs font-semibold text-gray-900"><?php echo formatCurrency($worker['tarif_per_jam']); ?></p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <p class="text-[10px] text-gray-500 uppercase tracking-wide">Rating</p>
                                <div class="flex items-center">
                                    <i data-feather="star" class="w-3 h-3 text-yellow-400 fill-current mr-1"></i>
                                    <span class="text-xs font-semibold text-gray-900"><?php echo number_format($worker['rating'], 1); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-xs text-gray-600">
                                <i data-feather="map-pin" class="w-3.5 h-3.5 mr-2 text-gray-400"></i>
                                <?php echo htmlspecialchars($worker['lokasi']); ?>
                            </div>
                            <div class="flex items-center text-xs text-gray-600">
                                <i data-feather="mail" class="w-3.5 h-3.5 mr-2 text-gray-400"></i>
                                <?php echo htmlspecialchars($worker['email']); ?>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-1.5 mb-4">
                            <?php foreach (array_slice($worker['keahlian'], 0, 3) as $skill): ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                    <?php echo htmlspecialchars($skill); ?>
                                </span>
                            <?php endforeach; ?>
                            <!-- Hidden span for search indexing -->
                            <span class="hidden"><?php echo htmlspecialchars(implode(' ', $worker['keahlian'])); ?></span>
                        </div>

                        <div class="flex items-center gap-2 pt-3 border-t border-gray-50">
                            <button class="flex-1 flex items-center justify-center px-3 py-2 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors edit-worker-btn"
                                    data-worker-id="<?php echo htmlspecialchars($worker['id_pekerja']); ?>">
                                <i data-feather="edit-2" class="w-3.5 h-3.5 mr-1.5"></i>
                                Edit
                            </button>
                            <button class="flex-1 flex items-center justify-center px-3 py-2 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors delete-worker-btn"
                                    data-worker-id="<?php echo htmlspecialchars($worker['id_pekerja']); ?>"
                                    data-worker-name="<?php echo htmlspecialchars($worker['nama']); ?>">
                                <i data-feather="trash-2" class="w-3.5 h-3.5 mr-1.5"></i>
                                Hapus
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- No Results Message Mobile -->
            <div id="noResultsMessageMobile" class="hidden py-8 text-center">
                <p class="text-sm text-gray-500">Tidak ada hasil ditemukan.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('workerSearchInput');
    const tableRows = document.querySelectorAll('.worker-row');
    const mobileCards = document.querySelectorAll('.worker-card-mobile');
    const noResultsDesktop = document.getElementById('noResultsMessage');
    const noResultsMobile = document.getElementById('noResultsMessageMobile');

    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        let hasVisibleDesktop = false;
        let hasVisibleMobile = false;

        // Filter Desktop Table
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
                hasVisibleDesktop = true;
            } else {
                row.style.display = 'none';
            }
        });

        // Filter Mobile Cards
        mobileCards.forEach(card => {
            const text = card.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                card.style.display = '';
                hasVisibleMobile = true;
            } else {
                card.style.display = 'none';
            }
        });

        // Toggle No Results Messages
        if (noResultsDesktop) {
            noResultsDesktop.classList.toggle('hidden', hasVisibleDesktop);
        }
        if (noResultsMobile) {
            noResultsMobile.classList.toggle('hidden', hasVisibleMobile);
        }
    });
});
</script>
