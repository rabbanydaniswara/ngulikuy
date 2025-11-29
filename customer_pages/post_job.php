<?php
/**
 * Customer Dashboard - Post a Job View
 */
?>
<div class="max-w-3xl mx-auto">
    <div class="mb-8 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Buat Lowongan Baru</h2>
        <p class="text-gray-500 mt-2">Isi formulir di bawah ini untuk menemukan pekerja yang tepat</p>
    </div>
    
    <div class="card overflow-hidden">
        <form method="POST" class="p-6 sm:p-8">
            <?php echo csrfInput(); ?>
            <input type="hidden" name="post_new_job" value="1">
            
            <div class="space-y-6">
                <!-- Job Title -->
                <div>
                    <label for="job_title" class="block text-sm font-medium text-gray-700 mb-2">
                        Judul Pekerjaan
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-feather="type" class="h-5 w-5 text-gray-400"></i>
                        </div>
                        <input type="text" id="job_title" name="job_title" required 
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               placeholder="Contoh: Renovasi Atap Rumah Type 36">
                    </div>
                </div>
                
                <!-- Job Type -->
                <div>
                    <label for="job_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Pekerjaan
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-feather="briefcase" class="h-5 w-5 text-gray-400"></i>
                        </div>
                        <select id="job_type" name="job_type" required 
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                            <option value="">Pilih Kategori</option>
                            <?php 
                                $skills = get_construction_skills();
                                foreach ($skills as $skill_option) {
                                    echo "<option value=\"{$skill_option}\">{$skill_option}</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="job_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Deskripsi Detail
                    </label>
                    <div class="relative">
                        <div class="absolute top-3 left-3 pointer-events-none">
                            <i data-feather="align-left" class="h-5 w-5 text-gray-400"></i>
                        </div>
                        <textarea id="job_description" name="job_description" required rows="6"
                                  class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                                  placeholder="Jelaskan detail pekerjaan, material yang dibutuhkan, dan ekspektasi hasil..."></textarea>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Berikan deskripsi selengkap mungkin agar pekerja dapat memahami kebutuhan Anda.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Location -->
                    <div>
                        <label for="job_location" class="block text-sm font-medium text-gray-700 mb-2">
                            Lokasi Proyek
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-feather="map-pin" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="text" id="job_location" name="job_location" required value="<?php echo htmlspecialchars($customer_address); ?>"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   placeholder="Alamat lengkap">
                        </div>
                    </div>
                    
                    <!-- Budget -->
                    <div>
                        <label for="job_budget" class="block text-sm font-medium text-gray-700 mb-2">
                            Anggaran (Opsional)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 font-bold text-sm">Rp</span>
                            </div>
                            <input type="number" id="job_budget" name="job_budget"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   placeholder="0">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" 
                        class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold shadow-sm hover:shadow transition-all transform hover:-translate-y-0.5">
                    <i data-feather="send" class="w-5 h-5 mr-2"></i>
                    Posting Lowongan
                </button>
            </div>
        </form>
    </div>
</div>
