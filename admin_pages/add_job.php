<?php
// File ini dipanggil oleh admin_dashboard.php
if (!defined('IS_ADMIN_PAGE')) {
    die('Akses ditolak!');
}
?>

<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-6">Tambah Pekerjaan Baru</h3>

    <form method="POST">
        <input type="hidden" name="add_job" value="1">
        <?php echo csrfInput(); ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Pilih Kuli -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kuli</label>
                <select name="worker_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 
                       focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Pilih Kuli</option>
                    <?php foreach ($availableWorkers as $worker): ?>
                        <option value="<?php echo htmlspecialchars($worker['id']); ?>">
                            <?php echo htmlspecialchars(
                                $worker['name'] . ' - ' .
                                implode(', ', $worker['skills']) . ' - ' .
                                formatCurrency($worker['rate'])
                            ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Jenis Pekerjaan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Pekerjaan</label>
                <select name="job_type" class="w-full px-4 py-2 rounded-lg border border-gray-300 
                       focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Pilih Jenis Pekerjaan</option>
                    <option value="Construction">Construction</option>
                    <option value="Moving">Moving</option>
                    <option value="Cleaning">Cleaning</option>
                    <option value="Gardening">Gardening</option>
                    <option value="Plumbing">Plumbing</option>
                    <option value="Electrical">Electrical</option>
                    <option value="Painting">Painting</option>
                </select>
            </div>

            <!-- Tanggal -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" class="w-full px-4 py-2 rounded-lg border border-gray-300 
                       focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                <input type="date" name="end_date" class="w-full px-4 py-2 rounded-lg border border-gray-300 
                       focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required min="<?php echo date('Y-m-d'); ?>">
            </div>

            <!-- Data Customer -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Customer</label>
                <input type="text" name="customer" class="w-full px-4 py-2 rounded-lg border border-gray-300 
                       focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Customer</label>
                <input type="email" name="customer_email" class="w-full px-4 py-2 rounded-lg border border-gray-300 
                       focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telepon Customer</label>
                <input type="tel" name="customer_phone" class="w-full px-4 py-2 rounded-lg border border-gray-300 
                       focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp)</label>
                <input type="number" name="price" class="w-full px-4 py-2 rounded-lg border border-gray-300 
                       focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <!-- Lokasi dan Alamat -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                <input type="text" name="location" class="w-full px-4 py-2 rounded-lg border border-gray-300 
                       focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                <textarea name="address" rows="2" class="w-full px-4 py-2 rounded-lg border border-gray-300 
                          focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
            </div>

            <!-- Deskripsi -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Pekerjaan</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-300 
                          focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 rounded-lg border border-gray-300 
                       focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="pending">Pending</option>
                    <option value="in-progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end">
            <a href="?tab=jobs" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 mr-2 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Simpan Pekerjaan
            </button>
        </div>
    </form>
</div>
