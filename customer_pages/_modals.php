<?php
/**
 * Customer Dashboard - Modals
 * Contains the HTML for the booking and review modals.
 */
?>
<div id="bookingModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 overflow-y-auto backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-2xl max-w-md w-full modal-enter modal-content">
            <div class="gradient-bg p-4 sm:p-6 rounded-t-xl sm:rounded-t-2xl relative">
                <div class="absolute top-0 right-0 w-24 sm:w-32 h-24 sm:h-32 bg-white/10 rounded-full -mr-12 sm:-mr-16 -mt-12 sm:-mt-16"></div>
                <div class="relative z-10">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl sm:text-2xl font-bold text-white">Buat Pesanan</h3>
                            <p class="text-blue-100 text-xs sm:text-sm mt-1">Isi detail pekerjaan Anda</p>
                        </div>
                        <button onclick="closeBookingModal()" class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white/10 rounded-lg">
                            <i data-feather="x" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <form method="POST" class="p-4 sm:p-6">
                <?php echo csrfInput(); ?>
                <input type="hidden" name="book_worker" value="1">
                <input type="hidden" id="modal_worker_id" name="worker_id">
                
                <div class="mb-3 sm:mb-4">
                    <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2 flex items-center">
                        <i data-feather="briefcase" class="w-3 h-3 sm:w-4 sm:h-4 mr-2 text-blue-600"></i>
                        Jenis Pekerjaan
                    </label>
                    <select name="job_type" required class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors text-sm sm:text-base">
                        <option value="">Pilih Jenis Pekerjaan</option>
                        <?php 
                            $skills = get_construction_skills();
                            foreach ($skills as $skill_option) {
                                echo "<option value=\"{$skill_option}\">{$skill_option}</option>";
                            }
                        ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-3 sm:gap-4 mb-3 sm:mb-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2 flex items-center">
                            <i data-feather="calendar" class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2 text-blue-600"></i>
                            Mulai
                        </label>
                        <input type="date" name="start_date" required min="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-2 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors text-sm sm:text-base">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2 flex items-center">
                            <i data-feather="calendar" class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2 text-blue-600"></i>
                            Selesai
                        </label>
                        <input type="date" name="end_date" required min="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-2 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors text-sm sm:text-base">
                    </div>
                </div>
                
                <div class="mb-3 sm:mb-4">
                    <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2 flex items-center">
                        <i data-feather="map-pin" class="w-3 h-3 sm:w-4 sm:h-4 mr-2 text-blue-600"></i>
                        Lokasi Pekerjaan
                    </label>
                    <textarea name="job_location" required rows="3"
                              class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors resize-none text-sm sm:text-base"
                              placeholder="Masukkan alamat lengkap..."></textarea>
                </div>
                
                <div class="mb-4 sm:mb-6">
                    <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2 flex items-center">
                        <i data-feather="file-text" class="w-3 h-3 sm:w-4 sm:h-4 mr-2 text-blue-600"></i>
                        Catatan (Opsional)
                    </label>
                    <textarea name="job_notes" rows="2"
                              class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors resize-none text-sm sm:text-base"
                              placeholder="Tambahkan catatan..."></textarea>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                    <button type="button" onclick="closeBookingModal()" 
                            class="flex-1 px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg sm:rounded-xl text-gray-700 hover:bg-gray-50 font-semibold transition-all text-sm sm:text-base">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2.5 sm:py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg sm:rounded-xl hover:from-blue-700 hover:to-indigo-700 font-semibold shadow-lg hover:shadow-xl transition-all text-sm sm:text-base">
                        Pesan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ACTION MODAL (for generic confirmations) -->
<div id="actionModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div id="modal-overlay" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div id="modal-content" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div id="modal-icon-container" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i id="modal-icon" data-feather="info" class="h-6 w-6 text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Konfirmasi Tindakan</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="modal-description">Apakah Anda yakin?</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="modal-confirm-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                    <span class="btn-text">Konfirmasi</span>
                    <span class="btn-loading hidden items-center">
                        <i data-feather="loader" class="animate-spin -ml-1 mr-2 h-5 w-5"></i>
                        Memproses...
                    </span>
                </button>
                <button type="button" id="modal-cancel-btn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>
<div id="reviewModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 overflow-y-auto backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-2xl max-w-md w-full modal-enter modal-content">
            <div class="gradient-bg p-4 sm:p-6 rounded-t-xl sm:rounded-t-2xl relative">
                <div class="absolute top-0 right-0 w-24 sm:w-32 h-24 sm:h-32 bg-white/10 rounded-full -mr-12 sm:-mr-16 -mt-12 sm:-mt-16"></div>
                <div class="relative z-10">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl sm:text-2xl font-bold text-white">Beri Ulasan</h3>
                            <p class="text-blue-100 text-xs sm:text-sm mt-1">Bagikan pengalaman Anda</p>
                        </div>
                        <button onclick="closeReviewModal()" class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white/10 rounded-lg">
                            <i data-feather="x" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <form method="POST" class="p-4 sm:p-6">
                <?php echo csrfInput(); ?>
                <input type="hidden" name="submit_review" value="1">
                <input type="hidden" id="review_job_id" name="job_id">
                <input type="hidden" id="review_worker_id" name="worker_id">
                
                <div class="mb-4 sm:mb-6">
                    <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-3 sm:mb-4 text-center">
                        Berikan Rating Anda
                    </label>
                    <div class="rating-stars-container">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required class="rating-star-input">
                            <label for="star<?php echo $i; ?>" class="rating-star-label">
                                <i data-feather="star"></i>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="mb-4 sm:mb-6">
                    <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2 flex items-center">
                        <i data-feather="message-square" class="w-3 h-3 sm:w-4 sm:h-4 mr-2 text-blue-600"></i>
                        Komentar
                    </label>
                    <textarea name="comment" required rows="4"
                              class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors resize-none text-sm sm:text-base"
                              placeholder="Bagaimana pengalaman Anda?"></textarea>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                    <button type="button" onclick="closeReviewModal()" 
                            class="flex-1 px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg sm:rounded-xl text-gray-700 hover:bg-gray-50 font-semibold transition-all text-sm sm:text-base">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2.5 sm:py-3 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-lg sm:rounded-xl hover:from-yellow-600 hover:to-orange-600 font-semibold shadow-lg hover:shadow-xl transition-all text-sm sm:text-base">
                        Kirim Ulasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
