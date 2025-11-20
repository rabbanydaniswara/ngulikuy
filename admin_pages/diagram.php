<?php
// admin_pages/diagram.php
// Place this file in the admin_pages folder
// This file now uses REAL data from the 'jobs' table

require_once __DIR__ . '/../functions.php';
redirectIfNotAdmin(); // Ensure admin access

// Ensure we access the global PDO object defined in db.php
global $pdo;

// 1. Get basic dashboard stats (for the top cards)
$stats = function_exists('getDashboardStats') ? getDashboardStats() : [];
$total_workers     = isset($stats['total_workers'])     ? (int)$stats['total_workers']     : 0;
$available_workers = isset($stats['available_workers']) ? (int)$stats['available_workers'] : 0;
$on_job_workers    = isset($stats['on_job_workers'])    ? (int)$stats['on_job_workers']    : 0;
$active_jobs       = isset($stats['active_jobs'])       ? (int)$stats['active_jobs']       : 0;
$completed_jobs    = isset($stats['completed_jobs'])    ? (int)$stats['completed_jobs']    : 0;
$pending_jobs      = isset($stats['pending_jobs'])      ? (int)$stats['pending_jobs']      : 0;

// 2. LOGIC FIX: Get REAL data for the Line Chart (Last 6 Months)
$months = [];
$line_values = [];
$db_data = [];

try {
    // Query to count jobs per month for the last 6 months
    // uses DATE_FORMAT to group by 'YYYY-MM'
    $sql = "SELECT 
                DATE_FORMAT(createdAt, '%Y-%m') as month_year, 
                COUNT(*) as total 
            FROM jobs 
            WHERE createdAt >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY month_year";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // Fetch into an associative array ['2025-11' => 5, '2025-10' => 3, etc]
    $db_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $e) {
    // Silent fail or log error, allow page to load with 0s
    error_log("Chart Data Error: " . $e->getMessage());
}

// 3. Build the arrays for Chart.js ensuring we have exactly 6 points
// (filling 0 for months with no jobs)
for ($i = 5; $i >= 0; $i--) {
    $timestamp = strtotime("first day of -$i months");
    $key = date('Y-m', $timestamp); // Key to match DB result
    
    $months[] = date('M Y', $timestamp); // Label: "Nov 2025"
    $line_values[] = isset($db_data[$key]) ? (int)$db_data[$key] : 0;
}

?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Diagram — Admin</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

  <style>
    /* Custom styling for modern cards */
    .card {
      background: linear-gradient(180deg, rgba(255,255,255,0.9), rgba(250,250,250,0.9));
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(22, 28, 45, 0.06);
      padding: 1rem;
    }
    .chart-wrap { position: relative; height: 320px; }
    @media (min-width: 768px) {
      .chart-wrap { height: 360px; }
    }
    .axis-muted { color: rgba(55,65,81,0.6); font-size: .92rem; }
  </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-800">
  <div class="max-w-7xl mx-auto p-4">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold">Diagram Statistik</h1>
        <p class="text-sm text-gray-600">Visualisasi data real-time dari database.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="?tab=dashboard" class="inline-flex items-center gap-2 px-3 py-2 rounded-md border shadow-sm bg-white hover:bg-gray-50">
          <i data-feather="chevrons-left"></i>
          <span class="text-sm">Kembali</span>
        </a>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="card flex flex-col">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-gray-500">Total Pekerja</p>
            <p class="text-xl font-medium"><?= $total_workers ?></p>
          </div>
          <div class="text-green-500">
            <i data-feather="users"></i>
          </div>
        </div>
      </div>

      <div class="card flex flex-col">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-gray-500">Tersedia</p>
            <p class="text-xl font-medium"><?= $available_workers ?></p>
          </div>
          <div class="text-blue-500">
            <i data-feather="check-circle"></i>
          </div>
        </div>
      </div>

      <div class="card flex flex-col">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-gray-500">Sedang Bekerja</p>
            <p class="text-xl font-medium"><?= $on_job_workers ?></p>
          </div>
          <div class="text-orange-500">
            <i data-feather="briefcase"></i>
          </div>
        </div>
      </div>

      <div class="card flex flex-col">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-gray-500">Total Pekerjaan</p>
            <p class="text-xl font-medium"><?= $active_jobs + $completed_jobs + $pending_jobs ?></p>
          </div>
          <div class="text-indigo-500">
            <i data-feather="bar-chart-2"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="card">
        <div class="flex items-start justify-between mb-3">
          <div>
            <h2 class="text-lg font-semibold">Trend Pekerjaan — 6 Bulan Terakhir</h2>
            <p class="text-sm text-gray-500">Grafik jumlah pekerjaan baru berdasarkan tanggal pembuatan.</p>
          </div>
        </div>
        <div class="chart-wrap">
          <canvas id="lineChart"></canvas>
        </div>
      </div>

      <div class="card">
        <div class="flex items-start justify-between mb-3">
          <div>
            <h2 class="text-lg font-semibold">Status Pekerjaan Saat Ini</h2>
            <p class="text-sm text-gray-500">Perbandingan jumlah pekerjaan berdasarkan status.</p>
          </div>
          <div class="text-sm axis-muted">
            <div>Total: <strong><?= $active_jobs + $completed_jobs + $pending_jobs ?></strong></div>
          </div>
        </div>
        <div class="chart-wrap">
          <canvas id="barChart"></canvas>
        </div>
      </div>
    </div>

    <div class="mt-6 text-xs text-gray-500">
      * Data grafik garis diambil dari tabel <code>jobs</code> kolom <code>createdAt</code>. Data status diambil dari fungsi statistik real-time.
    </div>
  </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  feather.replace();

  // Data from PHP (Real Database Data)
  const months = <?= json_encode($months) ?>;
  const lineValues = <?= json_encode($line_values) ?>;
  
  const statusValues = {
    active: <?= (int)$active_jobs ?>,
    completed: <?= (int)$completed_jobs ?>,
    pending: <?= (int)$pending_jobs ?>
  };

  // 1. Line Chart Configuration
  const lineCtx = document.getElementById('lineChart').getContext('2d');
  const lineChart = new Chart(lineCtx, {
    type: 'line',
    data: {
      labels: months,
      datasets: [{
        label: 'Jumlah Pekerjaan',
        data: lineValues,
        tension: 0.3, // curves the line slightly
        pointRadius: 5,
        borderWidth: 3,
        backgroundColor: 'rgba(59,130,246,0.1)', // blue fill
        borderColor: 'rgba(59,130,246,1)',      // blue line
        fill: true,
        pointHoverRadius: 7,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => `Total: ${ctx.formattedValue} Jobs`
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: '#64748b' }
        },
        y: {
          beginAtZero: true,
          ticks: { color: '#64748b', precision: 0 }, // precision 0 for integers
          grid: { borderDash: [2, 4] }
        }
      }
    }
  });

  // 2. Bar Chart Configuration
  const barCtx = document.getElementById('barChart').getContext('2d');
  const barChart = new Chart(barCtx, {
    type: 'bar',
    data: {
      labels: ['Active / In Progress', 'Completed', 'Pending'],
      datasets: [{
        label: 'Jumlah',
        data: [statusValues.active, statusValues.completed, statusValues.pending],
        borderRadius: 6,
        barPercentage: 0.5,
        backgroundColor: [
          'rgba(59,130,246,0.8)',  // blue
          'rgba(16,185,129,0.8)', // green
          'rgba(249,115,22,0.8)'  // orange
        ],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: '#64748b' }
        },
        y: {
          beginAtZero: true,
          ticks: { color: '#64748b', precision: 0 }
        }
      }
    }
  });

  // Resize listener
  window.addEventListener('resize', () => {
    lineChart.resize();
    barChart.resize();
  });
});
</script>
</body>
</html>