<?php
require_once 'functions.php';

redirectIfNotCustomer(); // Pastikan hanya customer yang login bisa akses

// 1. Ambil ID Job dari URL
$jobId = $_GET['id'] ?? null;
$error_message = null;
$jobDetails = null;

if (!$jobId) {
    $error_message = "ID Pesanan tidak valid.";
} else {
    // 2. Lakukan Verifikasi Keamanan
    if (!verifyCustomerOwnsJob($jobId, $_SESSION['user'])) {
        // Jika customer mencoba akses pesanan orang lain
        $error_message = "Anda tidak memiliki akses untuk melihat detail pesanan ini.";
    } else {
        // 3. Ambil Detail Job dari Database
        $jobDetails = getJobById($jobId);
        if (!$jobDetails) {
            $error_message = "Detail pesanan dengan ID '$jobId' tidak ditemukan.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - NguliKuy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>

</head>
<body class="min-h-screen flex flex-col">
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center h-10">
                <div class="flex items-center">
                    <a href="customer_dashboard.php" class="flex-shrink-0 flex items-center">
                        <i data-feather="tool" class="text-blue-600"></i>
                        <span class="ml-2 font-bold text-xl">NguliKuy</span>
                    </a>
                </div>
                <div class="flex items-center">
                    <div class="relative mr-4">
                        <i data-feather="bell" class="text-gray-500 hover:text-blue-600 cursor-pointer w-5 h-5"></i>
                    </div>
                    <div class="relative">
                        <div class="flex items-center space-x-2">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="User" class="w-8 h-8 rounded-full object-cover">
                            <span class="text-sm font-medium text-gray-700"><?php echo $_SESSION['user_name']; ?></span>
                            <a href="login.php?logout=1" class="text-gray-500 hover:text-blue-600 ml-2">
                                <i data-feather="log-out" class="w-5 h-5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow">
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
            <a href="customer_dashboard.php?tab=orders" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i data-feather="arrow-left" class="-ml-1 mr-2 h-5 w-5"></i>
                Kembali ke Pesanan Saya
            </a>
        <?php elseif ($jobDetails): 
            $statusInfo = getStatusTextAndClass($jobDetails['status']);
        ?>
            <div class="flex items-center mb-2">
                <h1 class="text-4xl font-extrabold text-gray-900">Detail Pesanan #<?php echo htmlspecialchars($jobDetails['jobId']); ?></h1>
                <a href="customer_dashboard.php?tab=orders" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200 ml-4">
                    <i data-feather="arrow-left" class="w-4 h-4 mr-1"></i> Kembali ke Pesanan
                </a>
            </div>
            <p class="text-md text-gray-400 mb-6">Dibuat pada: <?php echo date('d M Y, H:i', strtotime($jobDetails['createdAt'])); ?></p>

            <div class="mb-8 flex items-center">
                <span class="inline-flex items-center gap-1 px-4 py-2 text-sm font-semibold rounded-full shadow-md transition-all duration-200 ease-in-out <?php echo $statusInfo['class']; ?>">
                    <?php if ($jobDetails['status'] == 'pending'): ?>
                        <i data-feather="clock" class="w-4 h-4 mr-1"></i>
                    <?php elseif ($jobDetails['status'] == 'completed'): ?>
                        <i data-feather="check-circle" class="w-4 h-4 mr-1"></i>
                    <?php else: ?>
                        <i data-feather="info" class="w-4 h-4 mr-1"></i>
                    <?php endif; ?>
                    <?php echo $statusInfo['text']; ?>
                </span>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                 <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900">Informasi Pekerjaan</h3>
                </div>
                <div class="p-6">
                     <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-4 gap-x-6">
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Jenis Pekerjaan</dt>
                            <dd class="mt-1 text-gray-800"><?php echo htmlspecialchars($jobDetails['jobType']); ?></dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Tanggal Mulai</dt>
                            <dd class="mt-1 text-gray-800"><?php echo date('d M Y', strtotime($jobDetails['startDate'])); ?></dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Tanggal Selesai</dt>
                            <dd class="mt-1 text-gray-800"><?php echo date('d M Y', strtotime($jobDetails['endDate'])); ?></dd>
                        </div>
                         <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Lokasi Pengerjaan</dt>
                            <dd class="mt-1 text-gray-800"><?php echo nl2br(htmlspecialchars($jobDetails['address'])); ?></dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Deskripsi / Catatan</dt>
                            <dd class="mt-1 text-gray-800"><?php echo nl2br(htmlspecialchars($jobDetails['description'] ?: '-')); ?></dd>
                        </div>
                         <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Total Biaya</dt>
                            <dd class="mt-1 text-xl font-semibold text-blue-600"><?php echo formatCurrency($jobDetails['price']); ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

             <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                 <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900">Informasi Tukang</h3>
                </div>
                <div class="p-6">
                    <?php if ($jobDetails['workerId'] && $jobDetails['worker_full_name']): ?>
                    <div class="flex items-center space-x-4">
                        <img src="<?php echo htmlspecialchars($jobDetails['worker_photo'] ?: getDefaultWorkerPhoto()); ?>" alt="<?php echo htmlspecialchars($jobDetails['worker_full_name']); ?>" class="w-20 h-20 rounded-full flex-shrink-0 object-cover border-2 border-blue-500 p-0.5">
                        <div>
                            <p class="text-xl font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($jobDetails['worker_full_name']); ?></p>
                            <div class="text-sm text-gray-600 space-y-1">
                                <?php if($jobDetails['worker_phone']): ?>
                                <p class="flex items-center"><i data-feather="phone" class="inline w-4 h-4 mr-2 text-blue-500"></i> <?php echo htmlspecialchars($jobDetails['worker_phone']); ?></p>
                                <?php endif; ?>
                                <?php if($jobDetails['worker_email']): ?>
                                <p class="flex items-center"><i data-feather="mail" class="inline w-4 h-4 mr-2 text-blue-500"></i> <?php echo htmlspecialchars($jobDetails['worker_email']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <p class="text-gray-500 py-2">Informasi tukang tidak tersedia (mungkin tukang telah dihapus).</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        feather.replace();
    </script>
</body>
</html>