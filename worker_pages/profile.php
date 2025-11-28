<?php
// worker_pages/profile.php
if (!defined('IS_WORKER_PAGE')) {
    die('Akses ditolak!');
}

global $pdo;

$worker_id = $_SESSION['worker_profile_id'];
$worker = getWorkerById($worker_id);

?>
<div class="w-full">
    <div id="profile-notification"></div>

  <div class="flex items-center justify-between mb-6">
    <div>
      <h2 class="text-2xl font-semibold">Profil Saya</h2>
      <p class="text-sm text-gray-600">Perbarui informasi profil Anda.</p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="p-4 bg-white rounded-2xl shadow-sm">
      <div class="flex flex-col items-center gap-3">
        <?php $avatar = !empty($worker['url_foto']) ? $worker['url_foto'] : getDefaultWorkerPhoto(); ?>
        <img id="avatar-preview" src="<?php echo htmlspecialchars($avatar); ?>" alt="avatar" class="w-28 h-28 rounded-full object-cover border" />
        <div class="text-center">
          <p class="font-medium"><?php echo htmlspecialchars($worker['nama']); ?></p>
          <p class="text-sm text-gray-500"><?php echo htmlspecialchars($worker['email']); ?></p>
        </div>
      </div>
    </div>

    <div class="lg:col-span-2 p-4 bg-white rounded-2xl shadow-sm">
      <form id="profile-form" method="post" enctype="multipart/form-data" class="space-y-4">
        <?php echo csrfInput(); ?>
        <input type="hidden" name="action" value="save_worker_profile">

        <div>
          <label class="block text-sm text-gray-600">Nama</label>
          <input name="name" value="<?php echo htmlspecialchars($worker['nama']); ?>" required class="mt-1 block w-full rounded border-gray-200 p-2" />
        </div>

        <div>
          <label class="block text-sm text-gray-600">Email</label>
          <input name="email" type="email" value="<?php echo htmlspecialchars($worker['email']); ?>" required class="mt-1 block w-full rounded border-gray-200 p-2" />
        </div>

        <div>
          <label class="block text-sm text-gray-600">Telepon</label>
          <input name="phone" type="tel" value="<?php echo htmlspecialchars($worker['telepon']); ?>" required class="mt-1 block w-full rounded border-gray-200 p-2" />
        </div>
        
        <div>
          <label class="block text-sm text-gray-600">Lokasi</label>
          <input name="location" type="text" value="<?php echo htmlspecialchars($worker['lokasi']); ?>" required class="mt-1 block w-full rounded border-gray-200 p-2" />
        </div>
        
        <div>
            <label class="block text-sm text-gray-600">Keahlian</label>
            <select multiple name="skills[]"
                class="w-full px-4 py-2 rounded-lg border border-gray-300
                focus:outline-none focus:ring-2 focus:ring-blue-500 h-24">
                <?php 
                    $skills = get_construction_skills();
                    foreach ($skills as $skill_option) {
                        $selected = in_array($skill_option, $worker['keahlian']) ? 'selected' : '';
                        echo "<option value=\"{$skill_option}\" {$selected}>{$skill_option}</option>";
                    }
                ?>
            </select>
            <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd untuk memilih beberapa skill</p>
        </div>

        <div>
            <label class="block text-sm text-gray-600">Deskripsi</label>
            <textarea name="description" rows="3"
                class="w-full px-4 py-2 rounded-lg border border-gray-300
                focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Deskripsi keahlian dan pengalaman..."><?php echo htmlspecialchars($worker['deskripsi_diri']); ?></textarea>
        </div>

        <div>
            <label class="block text-sm text-gray-600">Pengalaman</label>
            <input name="experience" type="text" value="<?php echo htmlspecialchars($worker['pengalaman']); ?>" 
                   class="mt-1 block w-full rounded border-gray-200 p-2" placeholder="Cth: 5 Tahun / 6 Bulan" />
        </div>

        <div>
            <label class="block text-sm text-gray-600">Tarif per Jam (Rp)</label>
            <input name="rate" type="number" value="<?php echo htmlspecialchars($worker['tarif_per_jam']); ?>" 
                   class="mt-1 block w-full rounded border-gray-200 p-2" placeholder="Cth: 50000" min="0" />
        </div>

        <div>
          <label class="block text-sm text-gray-600">Upload Foto Profil (jpg/png, max 2MB)</label>
          <input name="photo" id="photo-input" type="file" accept=".png,.jpg,.jpeg" class="mt-1" />
        </div>

        <div class="pt-2 border-t">
          <h4 class="text-sm font-medium mb-2">Ubah Password</h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <input name="current_password" type="password" placeholder="Password saat ini" class="block w-full rounded border-gray-200 p-2" />
            <input name="new_password" type="password" placeholder="Password baru (kosong = tidak diubah)" class="block w-full rounded border-gray-200 p-2" />
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 mt-4">
          <a href="worker_dashboard.php" class="px-4 py-2 rounded border text-sm">Batal</a>
          <button type="submit" id="save-profile-btn" class="px-4 py-2 rounded bg-blue-600 text-white text-sm">Simpan Perubahan</button>
        </div>
      </form>
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

