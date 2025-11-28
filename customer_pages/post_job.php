<?php
/**
 * Customer Dashboard - Post a Job View
 */
?>
<div class="mb-6">
    <h2 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-6 text-gray-800 flex items-center">
        <i data-feather="plus-circle" class="w-6 h-6 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-blue-600"></i>
        Buat Pekerja Baru
    </h2>
    
    <form method="POST" class="bg-white rounded-xl sm:rounded-2xl shadow-lg p-4 sm:p-8">
        <?php echo csrfInput(); ?>
        <input type="hidden" name="post_new_job" value="1">
        
        <div class="space-y-4 sm:space-y-6">
            <div>
                <label for="job_title" class="block text-sm font-semibold text-gray-700 mb-1 sm:mb-2 flex items-center">
                    <i data-feather="type" class="w-4 h-4 mr-2 text-blue-600"></i>
                    Judul Pekerjaan
                </label>
                <input type="text" id="job_title" name="job_title" required 
                       class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors text-sm sm:text-base"
                       placeholder="Contoh: Perlu tukang untuk renovasi atap">
            </div>
            
            <div>
                <label for="job_type" class="block text-sm font-semibold text-gray-700 mb-1 sm:mb-2 flex items-center">
                    <i data-feather="tool" class="w-4 h-4 mr-2 text-blue-600"></i>
                    Jenis Pekerjaan
                </label>
                <select id="job_type" name="job_type" required class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors text-sm sm:text-base">
                    <option value="">Pilih Jenis Pekerjaan</option>
                    <?php 
                        $skills = get_construction_skills();
                        foreach ($skills as $skill_option) {
                            echo "<option value=\"{$skill_option}\">{$skill_option}</option>";
                        }
                    ?>
                </select>
            </div>

            <div>
                <label for="job_description" class="block text-sm font-semibold text-gray-700 mb-1 sm:mb-2 flex items-center">
                    <i data-feather="file-text" class="w-4 h-4 mr-2 text-blue-600"></i>
                    Deskripsi Detail
                </label>
                <textarea id="job_description" name="job_description" required rows="5"
                          class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors resize-none text-sm sm:text-base"
                          placeholder="Jelaskan detail pekerjaan yang dibutuhkan, material yang diperlukan, dan ekspektasi Anda..."></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label for="job_location" class="block text-sm font-semibold text-gray-700 mb-1 sm:mb-2 flex items-center">
                        <i data-feather="map-pin" class="w-4 h-4 mr-2 text-blue-600"></i>
                        Lokasi
                    </label>
                    <input type="text" id="job_location" name="job_location" required value="<?php echo htmlspecialchars($customer_address); ?>"
                           class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors text-sm sm:text-base"
                           placeholder="Masukkan lokasi pekerjaan">
                </div>
                <div>
                    <label for="job_budget" class="block text-sm font-semibold text-gray-700 mb-1 sm:mb-2 flex items-center">
                        <i data-feather="dollar-sign" class="w-4 h-4 mr-2 text-blue-600"></i>
                        Anggaran (Opsional)
                    </label>
                    <input type="number" id="job_budget" name="job_budget"
                           class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors text-sm sm:text-base"
                           placeholder="Contoh: 500000">
                </div>
            </div>
        </div>
        
        <div class="mt-6 sm:mt-8 pt-4 border-t border-gray-200">
            <button type="submit" 
                    class="w-full sm:w-auto bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-8 py-3 rounded-lg sm:rounded-xl hover:from-blue-700 hover:to-indigo-700 font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all text-base flex items-center justify-center">
                <i data-feather="send" class="w-5 h-5 mr-2"></i>
                Posting Pekerja
            </button>
        </div>
    </form>
</div>
