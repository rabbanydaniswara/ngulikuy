<?php
// File ini dipanggil oleh admin_dashboard.php
if (!defined('IS_ADMIN_PAGE')) {
    die('Akses ditolak!');
}
?>

<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-6">Tambah Pekerja Baru</h3>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_worker" value="1">
        <?php echo csrfInput(); ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Nama -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300
                    focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300
                    focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <!-- Telepon -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                <input type="tel" name="phone"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300
                    focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <!-- Lokasi -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                <input type="text" name="location"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300
                    focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <!-- Skills -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Skills</label>
                <select multiple name="skills[]"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300
                    focus:outline-none focus:ring-2 focus:ring-blue-500 h-24">
                    <?php 
                        $skills = get_construction_skills();
                        foreach ($skills as $skill_option) {
                            echo "<option value=\"{$skill_option}\">{$skill_option}</option>";
                        }
                    ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd untuk memilih beberapa skill</p>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300
                    focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Available">Available</option>
                    <option value="Assigned">Assigned</option>
                    <option value="On Leave">On Leave</option>
                </select>
            </div>

            <!-- Rate -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rate per Hari (Rp)</label>
                <input type="number" name="rate"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300
                    focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <!-- Deskripsi -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300
                    focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Deskripsi keahlian dan pengalaman..."></textarea>
            </div>

            <!-- Pengalaman -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pengalaman</label>
                <input type="text" name="experience"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300
                    focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Contoh: 3 tahun">
            </div>

            <!-- Foto Profil -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-3">Foto Profil</label>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Upload -->
                    <div class="upload-area p-6 text-center cursor-pointer" id="uploadArea">
                        <input type="file" id="photoUpload" name="photo" accept="image/*" class="file-input">
                        <div class="flex flex-col items-center justify-center space-y-3">
                            <i data-feather="upload-cloud" class="w-12 h-12 text-gray-400"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-700">Klik untuk upload foto</p>
                                <p class="text-xs text-gray-500">atau drag & drop file di sini</p>
                            </div>
                            <p class="text-xs text-gray-400">PNG, JPG, JPEG (max. 2MB)</p>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="space-y-4">
                        <div id="photoPreview" class="hidden">
                            <p class="text-sm font-medium text-gray-700 mb-2">Preview:</p>
                            <div class="flex flex-col items-center space-y-3">
                                <img id="previewImage" class="photo-preview">
                                <button type="button" id="removePhoto"
                                    class="text-xs text-red-600 hover:text-red-800 flex items-center">
                                    <i data-feather="x" class="w-3 h-3 mr-1"></i> Hapus Foto
                                </button>
                            </div>
                        </div>

                        <!-- URL Foto -->
                        <div class="border-t pt-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Atau gunakan URL foto:</p>
                            <input type="url" name="photo_url"
                                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300
                                focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="https://example.com/photo.jpg">
                            <p class="text-xs text-gray-500 mt-1">Kosongkan jika menggunakan upload file</p>
                        </div>
                    </div>
                </div>

                <!-- Progress Upload -->
                <div id="uploadStatus" class="mt-3 hidden">
                    <div id="uploadProgress" class="w-full bg-gray-200 rounded-full h-2 mb-2">
                        <div id="progressBar"
                            class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                            style="width: 0%">
                        </div>
                    </div>
                    <p id="statusText" class="text-xs text-gray-600"></p>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex justify-end">
            <a href="?tab=workers"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 mr-2 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" id="saveWorkerBtn"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                <i data-feather="save" class="w-4 h-4 mr-2"></i> Simpan Pekerja
            </button>
        </div>
    </form>
</div>
