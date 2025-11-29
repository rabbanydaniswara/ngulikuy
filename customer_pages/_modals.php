<?php
/**
 * Customer Dashboard - Modals
 * Contains the HTML for the booking and review modals.
 */
?>

<!-- BOOKING MODAL -->
<div id="bookingModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" onclick="closeBookingModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <!-- Header -->
            <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Buat Pesanan</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Isi detail pekerjaan Anda</p>
                </div>
                <button onclick="closeBookingModal()" class="text-gray-400 hover:text-gray-500 p-2 rounded-full hover:bg-gray-100 transition-colors">
                    <i data-feather="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form method="POST" class="p-6">
                <?php echo csrfInput(); ?>
                <input type="hidden" name="book_worker" value="1">
                <input type="hidden" id="modal_worker_id" name="worker_id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Pekerjaan</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-feather="briefcase" class="h-4 w-4 text-gray-400"></i>
                        </div>
                        <select name="job_type" required class="block w-full pl-10 pr-3 py-2.5 text-sm border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg bg-gray-50 border">
                            <option value="">Pilih Jenis Pekerjaan</option>
                            <?php 
                                $skills = get_construction_skills();
                                foreach ($skills as $skill_option) {
                                    echo "<option value=\"{$skill_option}\">{$skill_option}</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Mulai</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-feather="calendar" class="h-4 w-4 text-gray-400"></i>
                            </div>
                            <input type="date" name="start_date" required min="<?php echo date('Y-m-d'); ?>"
                                   class="block w-full pl-10 pr-3 py-2.5 text-sm border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg bg-gray-50 border">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Selesai</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-feather="calendar" class="h-4 w-4 text-gray-400"></i>
                            </div>
                            <input type="date" name="end_date" required min="<?php echo date('Y-m-d'); ?>"
                                   class="block w-full pl-10 pr-3 py-2.5 text-sm border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg bg-gray-50 border">
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Lokasi Pekerjaan</label>
                    <div class="relative">
                        <div class="absolute top-3 left-3 pointer-events-none">
                            <i data-feather="map-pin" class="h-4 w-4 text-gray-400"></i>
                        </div>
                        <textarea name="job_location" required rows="3"
                                  class="block w-full pl-10 pr-3 py-2.5 text-sm border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg bg-gray-50 border resize-none"
                                  placeholder="Masukkan alamat lengkap..."></textarea>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Catatan (Opsional)</label>
                    <div class="relative">
                        <div class="absolute top-3 left-3 pointer-events-none">
                            <i data-feather="file-text" class="h-4 w-4 text-gray-400"></i>
                        </div>
                        <textarea name="job_notes" rows="2"
                                  class="block w-full pl-10 pr-3 py-2.5 text-sm border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg bg-gray-50 border resize-none"
                                  placeholder="Tambahkan catatan khusus..."></textarea>
                    </div>
                </div>
                
                <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2 border-t border-gray-50">
                    <button type="button" onclick="closeBookingModal()" 
                            class="w-full sm:w-auto px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="w-full sm:w-auto flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 shadow-sm hover:shadow transition-all">
                        Pesan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ACTION MODAL (Generic) -->
<div id="actionModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div id="modal-overlay" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div id="modal-content" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
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

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                <button type="button" id="modal-confirm-btn" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:w-auto sm:text-sm">
                    <span class="btn-text">Konfirmasi</span>
                    <span class="btn-loading hidden items-center">
                        <i data-feather="loader" class="animate-spin -ml-1 mr-2 h-4 w-4"></i>
                        Memproses...
                    </span>
                </button>
                <button type="button" id="modal-cancel-btn" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<!-- REVIEW MODAL -->
<div id="reviewModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" onclick="closeReviewModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <!-- Header -->
            <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Beri Ulasan</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Bagikan pengalaman Anda</p>
                </div>
                <button onclick="closeReviewModal()" class="text-gray-400 hover:text-gray-500 p-2 rounded-full hover:bg-gray-100 transition-colors">
                    <i data-feather="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form method="POST" class="p-6">
                <?php echo csrfInput(); ?>
                <input type="hidden" name="submit_review" value="1">
                <input type="hidden" id="review_job_id" name="job_id">
                <input type="hidden" id="review_worker_id" name="worker_id">
                
                <div class="mb-6 text-center">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Rating Anda</label>
                    <div class="rating-stars-container">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required class="rating-star-input">
                            <label for="star<?php echo $i; ?>" class="rating-star-label p-1">
                                <i data-feather="star"></i>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Komentar</label>
                    <div class="relative">
                        <div class="absolute top-3 left-3 pointer-events-none">
                            <i data-feather="message-square" class="h-4 w-4 text-gray-400"></i>
                        </div>
                        <textarea name="comment" required rows="4"
                                  class="block w-full pl-10 pr-3 py-2.5 text-sm border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg bg-gray-50 border resize-none"
                                  placeholder="Ceritakan pengalaman Anda bekerja dengan mitra ini..."></textarea>
                    </div>
                </div>
                
                <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2 border-t border-gray-50">
                    <button type="button" onclick="closeReviewModal()" 
                            class="w-full sm:w-auto px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="w-full sm:w-auto flex-1 px-4 py-2.5 bg-yellow-500 text-white rounded-lg text-sm font-medium hover:bg-yellow-600 shadow-sm hover:shadow transition-all">
                        Kirim Ulasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
