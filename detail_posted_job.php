<?php
require_once 'functions.php';

// Must be logged in as a customer or worker
if (!isCustomer() && !isWorker()) {
    redirectIfNotLoggedIn();
}

$job_id = $_GET['id'] ?? null;
if (!$job_id) {
    header("Location: login.php");
    exit;
}

global $pdo;
$stmt = $pdo->prepare("
    SELECT 
        pj.*, 
        u.name as customer_name, 
        u.photo as customer_photo,
        w.name as worker_name,
        w.photo as worker_photo,
        w.rating as worker_rating,
        pj.status as posted_job_status,
        j.status as job_status
    FROM posted_jobs pj
    JOIN users u ON pj.customer_id = u.id
    LEFT JOIN workers w ON pj.worker_id = w.id
    LEFT JOIN jobs j ON pj.id = j.posted_job_id
    WHERE pj.id = ?
");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if (!$job) {
    // Or show a 'not found' page
    header("Location: login.php");
    exit;
}

// Derive final status and check if job was deleted
$posted_status = $job['posted_job_status'];
$job_status = $job['job_status'];
$final_status = 'unknown';

if ($job_status !== null) {
    $final_status = $job_status;
} else {
    if ($posted_status === 'assigned') {
        $final_status = 'dihapus';
    } else {
        $final_status = 'open';
    }
}

if ($final_status === 'dihapus') {
    // Redirect if the job is effectively deleted
    header("Location: customer_dashboard.php?tab=my_jobs");
    exit;
}

// Determine back link
$back_link = 'index.html';
if (isCustomer()) {
    $back_link = 'customer_dashboard.php?tab=my_jobs';
} elseif (isWorker()) {
    $back_link = 'worker_dashboard.php?tab=find_jobs';
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pekerjaan - <?php echo htmlspecialchars($job['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <div class="mb-6">
            <a href="<?php echo $back_link; ?>" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold">
                <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
                Kembali ke Daftar
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Job Header -->
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight"><?php echo htmlspecialchars($job['title']); ?></h1>
                    <div class="mt-4 flex flex-wrap items-center text-sm text-gray-600 gap-x-4 gap-y-2">
                        <span class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 rounded-full font-medium text-xs">
                            <i data-feather="briefcase" class="w-3 h-3 mr-1.5"></i> <?php echo htmlspecialchars($job['job_type']); ?>
                        </span>
                        <span class="inline-flex items-center px-3 py-1 bg-purple-50 text-purple-700 rounded-full font-medium text-xs">
                            <i data-feather="map-pin" class="w-3 h-3 mr-1.5"></i> <?php echo htmlspecialchars($job['location']); ?>
                        </span>
                        <span class="inline-flex items-center px-3 py-1 bg-gray-50 text-gray-700 rounded-full font-medium text-xs">
                            <i data-feather="calendar" class="w-3 h-3 mr-1.5"></i> Diposting pada <?php echo date('d M Y', strtotime($job['created_at'])); ?>
                        </span>
                    </div>
                </div>

                <!-- Job Description -->
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Deskripsi Pekerjaan</h2>
                    <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div class="space-y-6">
                <!-- Status & Action Card -->
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Status Pekerjaan</h3>
                    <?php 
                        $statusClass = 'bg-gray-100 text-gray-800';
                        if ($final_status === 'open') $statusClass = 'bg-green-100 text-green-700';
                        if ($final_status === 'in-progress') $statusClass = 'bg-yellow-100 text-yellow-700';
                        if ($final_status === 'completed') $statusClass = 'bg-blue-100 text-blue-700';
                        if ($final_status === 'cancelled' || $final_status === 'dihapus') $statusClass = 'bg-red-100 text-red-700';
                    ?>
                    <div class="px-4 py-2 text-base font-bold rounded-lg <?php echo $statusClass; ?> inline-block w-full text-center mb-6">
                        <?php echo ucfirst(str_replace('_', ' ', $final_status)); ?>
                    </div>
                    
                    <?php if (isWorker() && $final_status === 'open'): ?>
                        <button type="button" class="job-modal-trigger w-full text-center px-6 py-3 flex items-center justify-center gap-2 rounded-lg bg-green-600 text-white text-base font-semibold hover:bg-green-700 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                            data-action="worker_take_posted_job" data-job-id="<?php echo $job['id']; ?>"
                            data-job-title="<?php echo htmlspecialchars($job['title']); ?>">
                            <i data-feather="plus-circle" class="w-5 h-5"></i>
                            Ambil Pekerjaan Ini
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Budget Card -->
                <?php if (isset($job['budget']) && $job['budget'] > 0): ?>
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Anggaran</h3>
                    <p class="text-3xl font-extrabold text-green-600"><?php echo formatCurrency($job['budget']); ?></p>
                </div>
                <?php endif; ?>

                <!-- Customer Card -->
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Diposting Oleh</h3>
                    <div class="flex items-center">
                        <img src="<?php echo htmlspecialchars($job['customer_photo'] ?? 'https://via.placeholder.com/150'); ?>" alt="Customer" class="w-14 h-14 rounded-full object-cover mr-4 border border-gray-200">
                        <div>
                            <p class="font-semibold text-gray-900 text-lg"><?php echo htmlspecialchars($job['customer_name']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Worker Card -->
                <?php if ($job['worker_id']): ?>
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Diambil Oleh</h3>
                    <div class="flex items-center">
                        <img src="<?php echo htmlspecialchars($job['worker_photo'] ?? 'https://via.placeholder.com/150'); ?>" alt="Worker" class="w-14 h-14 rounded-full object-cover mr-4 border border-gray-200">
                        <div>
                            <p class="font-semibold text-gray-900 text-lg"><?php echo htmlspecialchars($job['worker_name']); ?></p>
                            <div class="flex items-center mt-1">
                                <i data-feather="star" class="w-4 h-4 text-yellow-400 fill-current"></i>
                                <span class="text-sm text-gray-600 ml-1 font-semibold"><?php echo number_format((float)($job['worker_rating'] ?? 0), 1); ?>/5.0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- The modals and JS needed for the 'Ambil Pekerjaan' button -->
    <?php if(isWorker()): ?>
        <?php include 'worker_pages/_footer.php'; ?>
    <?php else: ?>
        <script>feather.replace();</script>
    <?php endif; ?>
</body>
</html>
