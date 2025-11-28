<?php
// admin_pages/profile.php
if (!isset($_SESSION)) session_start();
if (empty($_SESSION['admin_id'])) {
    echo '<div class="p-4 bg-red-50 text-red-700 rounded">Akses ditolak. Silakan login sebagai admin.</div>';
    return;
}
$admin = $_SESSION['admin'] ?? [
    'id_pengguna'=>$_SESSION['user_id'],
    'nama_lengkap'=>$_SESSION['user_name'],
    'nama_pengguna'=>$_SESSION['user'],
    'url_foto'=>$_SESSION['user_photo'] ?? 'assets/default-avatar.png'
];

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<div class="w-full">
  <?php if ($flash): ?>
    <div class="mb-4 p-3 rounded bg-green-50 text-green-800"><?= htmlspecialchars($flash) ?></div>
  <?php endif; ?>

  <div class="flex items-center justify-between mb-6">
    <div>
      <h2 class="text-2xl font-semibold">Profil Admin</h2>
      <p class="text-sm text-gray-600">Perbarui informasi akun admin Anda.</p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="p-4 bg-white rounded-2xl shadow-sm">
      <div class="flex flex-col items-center gap-3">
        <?php $avatar = !empty($admin['url_foto']) ? $admin['url_foto'] : 'assets/default-avatar.png'; ?>
        <img src="<?= htmlspecialchars($avatar) ?>" alt="avatar" class="w-28 h-28 rounded-full object-cover border" />
        <div class="text-center">
          <p class="font-medium"><?= htmlspecialchars($admin['nama_lengkap']) ?></p>
          <p class="text-sm text-gray-500"><?= htmlspecialchars($admin['nama_pengguna']) ?></p>
        </div>
      </div>
    </div>

    <div class="lg:col-span-2 p-4 bg-white rounded-2xl shadow-sm">
      <form action="admin_pages/save_profile.php" method="post" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="admin_id" value="<?= htmlspecialchars($admin['id_pengguna']) ?>" />

        <div>
          <label class="block text-sm text-gray-600">Nama</label>
          <input name="name" value="<?= htmlspecialchars($admin['nama_lengkap']) ?>" required class="mt-1 block w-full rounded border-gray-200 p-2" />
        </div>

        <div>
          <label class="block text-sm text-gray-600">Email</label>
          <input name="email" type="email" value="<?= htmlspecialchars($admin['nama_pengguna']) ?>" required class="mt-1 block w-full rounded border-gray-200 p-2" />
        </div>

        <div>
          <label class="block text-sm text-gray-600">Upload Avatar (jpg/png, max 1MB)</label>
          <input name="avatar" type="file" accept=".png,.jpg,.jpeg" class="mt-1" />
        </div>

        <div class="pt-2 border-t">
          <h4 class="text-sm font-medium mb-2">Ubah Password</h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <input name="current_password" type="password" placeholder="Password saat ini" class="block w-full rounded border-gray-200 p-2" />
            <input name="new_password" type="password" placeholder="Password baru (kosong = tidak diubah)" class="block w-full rounded border-gray-200 p-2" />
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 mt-4">
          <a href="admin_dashboard.php" class="px-4 py-2 rounded border text-sm">Batal</a>
          <button type="submit" name="save_profile" class="px-4 py-2 rounded bg-blue-600 text-white text-sm">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>
