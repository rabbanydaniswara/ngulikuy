<?php
// worker_pages/profile.php
if (!defined('IS_WORKER_PAGE')) {
    die('Akses ditolak!');
}

global $pdo;

$worker_id = $_SESSION['worker_profile_id'];
$worker = getWorkerById($worker_id);

?>
<div class="w-full max-w-5xl mx-auto">
    <div id="profile-notification"></div>

  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-3xl font-bold text-slate-900 tracking-tight">Profil Saya</h2>
      <p class="text-slate-500 mt-2">Perbarui informasi profil dan keahlian Anda.</p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Sidebar / Avatar -->
    <div class="lg:col-span-1">
        <div class="card-modern p-6 sticky top-24">
            <div class="flex flex-col items-center gap-4">
                <?php $avatar = !empty($worker['url_foto']) ? $worker['url_foto'] : getDefaultWorkerPhoto(); ?>
                <div class="relative group">
                    <img id="avatar-preview" src="<?php echo htmlspecialchars($avatar); ?>" alt="avatar" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-md group-hover:shadow-lg transition-shadow" />
                    <div class="absolute inset-0 rounded-full bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                        <i data-feather="camera" class="text-white opacity-0 group-hover:opacity-100 w-8 h-8 drop-shadow-md transition-opacity"></i>
                    </div>
                    <input type="file" id="photo-input-trigger" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".png,.jpg,.jpeg" />
                </div>
                
                <div class="text-center">
                    <h3 class="font-bold text-lg text-slate-900"><?php echo htmlspecialchars($worker['nama']); ?></h3>
                    <p class="text-sm text-slate-500"><?php echo htmlspecialchars($worker['email']); ?></p>
                </div>

                <div class="w-full border-t border-slate-100 pt-4 mt-2">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-xs text-slate-400 uppercase font-semibold tracking-wider">Tarif/Jam</div>
                            <div class="font-medium text-slate-700 mt-1">
                                <?php echo number_format($worker['tarif_per_jam'] ?? 0, 0, ',', '.'); ?>
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400 uppercase font-semibold tracking-wider">Pengalaman</div>
                            <div class="font-medium text-slate-700 mt-1">
                                <?php echo htmlspecialchars($worker['pengalaman'] ?? '-'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Form -->
    <div class="lg:col-span-2">
        <div class="card-modern p-6 sm:p-8">
            <form id="profile-form" method="post" enctype="multipart/form-data" class="space-y-6">
                <?php echo csrfInput(); ?>
                <input type="hidden" name="action" value="save_worker_profile">
                <!-- Hidden real file input linked to trigger above -->
                <input name="photo" id="photo-input" type="file" accept=".png,.jpg,.jpeg" class="hidden" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-1 md:col-span-2">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                            <i data-feather="user" class="w-5 h-5 text-blue-500"></i> Informasi Dasar
                        </h3>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                        <input name="name" value="<?php echo htmlspecialchars($worker['nama']); ?>" required 
                            class="block w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500/20 transition-all p-2.5" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input name="email" type="email" value="<?php echo htmlspecialchars($worker['email']); ?>" required 
                            class="block w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500/20 transition-all p-2.5" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nomor Telepon</label>
                        <input name="phone" type="tel" value="<?php echo htmlspecialchars($worker['telepon']); ?>" required 
                            class="block w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500/20 transition-all p-2.5" />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Lokasi / Kota</label>
                        <input name="location" type="text" value="<?php echo htmlspecialchars($worker['lokasi']); ?>" required 
                            class="block w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500/20 transition-all p-2.5" />
                    </div>
                </div>

                <div class="border-t border-slate-100 my-6"></div>

                <div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <i data-feather="briefcase" class="w-5 h-5 text-blue-500"></i> Keahlian & Pengalaman
                    </h3>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Keahlian (Pilih Banyak)</label>
                            <select multiple name="skills[]"
                                class="block w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500/20 transition-all p-2.5 min-h-[120px]">
                                <?php 
                                    $skills = get_construction_skills();
                                    foreach ($skills as $skill_option) {
                                        $selected = in_array($skill_option, $worker['keahlian']) ? 'selected' : '';
                                        echo "<option value=\"{$skill_option}\" {$selected}>{$skill_option}</option>";
                                    }
                                ?>
                            </select>
                            <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-1">
                                <i data-feather="info" class="w-3 h-3"></i> Tahan tombol Ctrl (Windows) atau Cmd (Mac) untuk memilih lebih dari satu.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi Diri</label>
                            <textarea name="description" rows="4"
                                class="block w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500/20 transition-all p-2.5"
                                placeholder="Jelaskan pengalaman kerja dan keahlian spesifik Anda..."><?php echo htmlspecialchars($worker['deskripsi_diri']); ?></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Pengalaman Kerja</label>
                                <input name="experience" type="text" value="<?php echo htmlspecialchars($worker['pengalaman']); ?>" 
                                    class="block w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500/20 transition-all p-2.5" 
                                    placeholder="Cth: 5 Tahun" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tarif per Jam (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-slate-500 text-sm">Rp</span>
                                    <input name="rate" type="number" value="<?php echo htmlspecialchars($worker['tarif_per_jam']); ?>" 
                                        class="block w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500/20 transition-all p-2.5 pl-10" 
                                        placeholder="0" min="0" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100 my-6"></div>

                <div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <i data-feather="lock" class="w-5 h-5 text-blue-500"></i> Keamanan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Password Saat Ini</label>
                            <input name="current_password" type="password" placeholder="••••••••" class="block w-full rounded-lg border-slate-200 bg-white focus:border-blue-500 focus:ring-blue-500/20 transition-all p-2.5" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
                            <input name="new_password" type="password" placeholder="••••••••" class="block w-full rounded-lg border-slate-200 bg-white focus:border-blue-500 focus:ring-blue-500/20 transition-all p-2.5" />
                            <p class="text-xs text-slate-500 mt-1">Kosongkan jika tidak ingin mengubah password.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                    <a href="worker_dashboard.php" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-colors">Batal</a>
                    <button type="submit" id="save-profile-btn" class="px-5 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition-colors shadow-sm flex items-center gap-2">
                        <i data-feather="save" class="w-4 h-4"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profile-form');
    const notification = document.getElementById('profile-notification');
    const avatarPreview = document.getElementById('avatar-preview');
    const photoInput = document.getElementById('photo-input');

    photoInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(event) {
                avatarPreview.src = event.target.result;
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        const saveBtn = document.getElementById('save-profile-btn');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Menyimpan...';

        fetch('ajax_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Log the raw response text before trying to parse as JSON
            return response.text().then(text => {
                console.log('Raw server response:', text);
                try {
                    return JSON.parse(text); // Try to parse it as JSON
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    throw new Error('Server did not return valid JSON: ' + text); // Re-throw with raw text
                }
            });
        })
        .then(data => {
            notification.innerHTML = '';
            const alert = document.createElement('div');
            alert.className = 'mb-4 p-3 rounded';
            if (data.success) {
                alert.classList.add('bg-green-50', 'text-green-800');
                alert.textContent = data.message;
                // Optionally, update fields if they changed, e.g., name in header
            } else {
                alert.classList.add('bg-red-50', 'text-red-700');
                alert.textContent = data.message;
            }
            notification.appendChild(alert);
            window.scrollTo(0, 0);
        })
        .catch(error => {
            console.error('Error:', error);
            notification.innerHTML = '';
            const alert = document.createElement('div');
            alert.className = 'mb-4 p-3 rounded bg-red-50 text-red-700';
            alert.textContent = 'Terjadi error. Silakan coba lagi.';
            notification.appendChild(alert);
            window.scrollTo(0, 0);
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Simpan Perubahan';
        });
    });
});
</script>

