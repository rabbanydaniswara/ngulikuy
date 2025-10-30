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

// Helper untuk status text (bisa dipindah ke functions.php jika mau)
function getStatusTextAndClass($status) {
    $text = 'Tidak Diketahui';
    $class = 'bg-gray-200 text-gray-800'; // Default

    switch ($status) {
        case 'pending':
            $text = 'Menunggu Konfirmasi';
            $class = 'bg-yellow-100 text-yellow-800';
            break;
        case 'in-progress':
            $text = 'Sedang Dikerjakan';
            $class = 'bg-blue-100 text-blue-800';
            break;
        case 'completed':
            $text = 'Selesai';
            $class = 'bg-green-100 text-green-800';
            break;
        case 'cancelled':
            $text = 'Dibatalkan';
            $class = 'bg-red-100 text-red-800';
            break;
    }
    return ['text' => $text, 'class' => $class];
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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
        }
         .detail-label {
            @apply text-sm font-medium text-gray-500;
        }
        .detail-value {
            @apply mt-1 text-md text-gray-900 sm:mt-0 sm:col-span-2;
        }
        .detail-card {
             @apply bg-white shadow overflow-hidden sm:rounded-lg mb-6;
        }
        .detail-card-header {
             @apply px-4 py-5 sm:px-6 border-b border-gray-200;
        }
        .detail-card-body {
            @apply px-4 py-5 sm:p-6;
        }
        .detail-grid {
             @apply grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-3;
        }
    </style>
</head>
<body class="min-h-screen">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="customer_dashboard.php" class="flex-shrink-0 flex items-center">
                        <i data-feather="tool" class="text-blue-600"></i>
                        <span class="ml-2 font-bold text-xl">NguliKuy</span>
                    </a>
                    </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <a href="customer_dashboard.php?tab=orders" class="text-gray-500 hover:text-blue-600 mr-4 flex items-center">
                        <i data-feather="arrow-left" class="w-4 h-4 mr-1"></i> Kembali ke Pesanan
                    </a>
                     <div class="relative">
                        <i data-feather="bell" class="text-gray-500 hover:text-blue-600 cursor-pointer"></i>
                        </div>
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-2">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="User" class="w-8 h-8 rounded-full">
                            <span class="text-sm font-medium"><?php echo $_SESSION['user_name']; ?></span>
                            <a href="index.php?logout=1" class="text-gray-500 hover:text-blue-600">
                                <i data-feather="log-out" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
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
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Detail Pesanan #<?php echo htmlspecialchars($jobDetails['jobId']); ?></h1>
            <p class="text-sm text-gray-500 mb-6">Dibuat pada: <?php echo date('d M Y, H:i', strtotime($jobDetails['createdAt'])); ?></p>

            <div class="mb-6">
                <span class="px-3 py-1 text-sm font-medium rounded-full <?php echo $statusInfo['class']; ?>">
                    <?php echo $statusInfo['text']; ?>
                </span>
            </div>

            <div class="detail-card">
                 <div class="detail-card-header">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Informasi Pekerjaan</h3>
                </div>
                <div class="detail-card-body">
                     <dl class="detail-grid">
                        <div class="sm:col-span-1">
                            <dt class="detail-label">Jenis Pekerjaan</dt>
                            <dd class="detail-value"><?php echo htmlspecialchars($jobDetails['jobType']); ?></dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="detail-label">Tanggal Mulai</dt>
                            <dd class="detail-value"><?php echo date('d M Y', strtotime($jobDetails['startDate'])); ?></dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="detail-label">Tanggal Selesai</dt>
                            <dd class="detail-value"><?php echo date('d M Y', strtotime($jobDetails['endDate'])); ?></dd>
                        </div>
                         <div class="sm:col-span-3">
                            <dt class="detail-label">Lokasi Pengerjaan</dt>
                            <dd class="detail-value"><?php echo nl2br(htmlspecialchars($jobDetails['address'])); ?></dd>
                        </div>
                        <div class="sm:col-span-3">
                            <dt class="detail-label">Deskripsi / Catatan</dt>
                            <dd class="detail-value"><?php echo nl2br(htmlspecialchars($jobDetails['description'] ?: '-')); ?></dd>
                        </div>
                         <div class="sm:col-span-1">
                            <dt class="detail-label">Total Biaya</dt>
                            <dd class="detail-value font-semibold text-blue-600"><?php echo formatCurrency($jobDetails['price']); ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

             <div class="detail-card">
                 <div class="detail-card-header">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Informasi Tukang</h3>
                </div>
                <div class="detail-card-body">
                    <?php if ($jobDetails['workerId'] && $jobDetails['worker_full_name']): ?>
                    <div class="flex items-center">
                        <img src="<?php echo htmlspecialchars($jobDetails['worker_photo'] ?: getDefaultWorkerPhoto()); ?>" alt="<?php echo htmlspecialchars($jobDetails['worker_full_name']); ?>" class="w-16 h-16 rounded-full mr-4 object-cover">
                        <div>
                            <p class="text-xl font-semibold text-gray-900"><?php echo htmlspecialchars($jobDetails['worker_full_name']); ?></p>
                            <p class="text-sm text-gray-600">
                                <?php if($jobDetails['worker_phone']): ?>
                                <i data-feather="phone" class="inline w-3 h-3 mr-1"></i> <?php echo htmlspecialchars($jobDetails['worker_phone']); ?>
                                <?php endif; ?>
                                <?php if($jobDetails['worker_email']): ?>
                                 | <i data-feather="mail" class="inline w-3 h-3 ml-2 mr-1"></i> <?php echo htmlspecialchars($jobDetails['worker_email']); ?>
                                <?php endif; ?>
                            </p>
                            </div>
                    </div>
                    <?php else: ?>
                    <p class="text-gray-500">Informasi tukang tidak tersedia (mungkin tukang telah dihapus).</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-8">
                <a href="customer_dashboard.php?tab=orders" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i data-feather="arrow-left" class="-ml-1 mr-2 h-5 w-5"></i>
                    Kembali ke Pesanan Saya
                </a>
            </div>

        <?php endif; ?>

    </div>

    <script>
        feather.replace();
    </script>
</body>
</html>