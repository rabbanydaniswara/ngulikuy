<?php
// admin_pages/diagram.php
// Letakkan file ini di folder admin_pages
// Mengasumsikan project Anda memiliki functions.php dengan getDashboardStats() dan redirectIfNotAdmin()

require_once __DIR__ . '/../functions.php';
redirectIfNotAdmin(); // pastikan akses admin

// Ambil statistik dasar dari fungsi yang ada (fallback ke nol bila tidak tersedia)
$stats = function_exists('getDashboardStats') ? getDashboardStats() : [];
$total_workers     = isset($stats['total_workers'])     ? (int)$stats['total_workers']     : 0;
$available_workers = isset($stats['available_workers']) ? (int)$stats['available_workers'] : 0;
$on_job_workers    = isset($stats['on_job_workers'])    ? (int)$stats['on_job_workers']    : 0;
$active_jobs       = isset($stats['active_jobs'])       ? (int)$stats['active_jobs']       : 0;
$completed_jobs    = isset($stats['completed_jobs'])    ? (int)$stats['completed_jobs']    : 0;
$pending_jobs      = isset($stats['pending_jobs'])      ? (int)$stats['pending_jobs']      : 0;

// Untuk diagram garis, kita buat data synthetic 6 bulan berdasar total pekerjaan
$base_jobs = max(1, $active_jobs + $completed_jobs + $pending_jobs);
$line_values = [];
for ($i = 5; $i >= 0; $i--) {
    // distribusi sinusoida kecil agar terlihat ada tren; tetap integer
    $val = round($base_jobs * (0.4 + 0.6 * sin((6 - $i) / 6 * M_PI)));
    $line_values[] = max(0, $val);
}

// Nama bulan (short) untuk 6 bulan terakhir
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $ts = strtotime("first day of -{$i} months");
    $months[] = date('M Y', $ts);
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Diagram — Admin</title>

  <!-- Tailwind CDN (sesuaikan jika Anda sudah punya bundling sendiri) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Feather icons -->
  <script src="https://unpkg.com/feather-icons"></script>
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

  <style>
    /* Small custom styling to make cards look modern */
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
    /* subtle axis text */
    .axis-muted { color: rgba(55,65,81,0.6); font-size: .92rem; }
  </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-800">
  <div class="max-w-7xl mx-auto p-4">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold">Diagram</h1>
        <p class="text-sm text-gray-600">Visualisasi cepat untuk metrik penting — responsif dan modern.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="../admin_dashboard.php" class="inline-flex items-center gap-2 px-3 py-2 rounded-md border shadow-sm bg-white hover:bg-gray-50">
          <i data-feather="chevrons-left"></i>
          <span class="text-sm">Kembali</span>
        </a>
      </div>
    </div>

    <!-- stats summary -->
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

    <!-- charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Line Chart Card -->
      <div class="card">
        <div class="flex items-start justify-between mb-3">
          <div>
            <h2 class="text-lg font-semibold">Trend Pekerjaan — 6 Bulan</h2>
            <p class="text-sm text-gray-500">Grafik garis menunjukkan estimasi pekerjaan per bulan.</p>
          </div>
          <div class="text-sm axis-muted">
            <div>Aktif: <strong><?= $active_jobs ?></strong></div>
            <div>Selesai: <strong><?= $completed_jobs ?></strong></div>
            <div>Tertunda: <strong><?= $pending_jobs ?></strong></div>
          </div>
        </div>
        <div class="chart-wrap">
          <canvas id="lineChart"></canvas>
        </div>
      </div>

      <!-- Bar Chart Card -->
      <div class="card">
        <div class="flex items-start justify-between mb-3">
          <div>
            <h2 class="text-lg font-semibold">Status Pekerjaan</h2>
            <p class="text-sm text-gray-500">Perbandingan jumlah pekerjaan berdasarkan status saat ini.</p>
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
      Sumber: fungsi <code>getDashboardStats()</code>. Jika Anda ingin grafik berdasar data riil per-bulan dari database, beri tahu — saya tambahkan query dan endpoint kecil untuk mengambilnya.
    </div>
  </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  feather.replace();

  // PHP-supplied data
  const months = <?= json_encode($months, JSON_HEX_TAG) ?>;
  const lineValues = <?= json_encode($line_values, JSON_HEX_TAG) ?>;
  const statusValues = {
    active: <?= (int)$active_jobs ?>,
    completed: <?= (int)$completed_jobs ?>,
    pending: <?= (int)$pending_jobs ?>
  };

  // Utility to create gradient
  function createGradient(ctx, area, colorStart, colorEnd) {
    const gradient = ctx.createLinearGradient(0, 0, 0, area.height);
    gradient.addColorStop(0, colorStart);
    gradient.addColorStop(1, colorEnd);
    return gradient;
  }

  // Line Chart
  const lineCtx = document.getElementById('lineChart').getContext('2d');
  let lineGradient;
  const lineChart = new Chart(lineCtx, {
    type: 'line',
    data: {
      labels: months,
      datasets: [{
        label: 'Pekerjaan per bulan',
        data: lineValues,
        tension: 0.36,
        pointRadius: 4,
        borderWidth: 2.5,
        // backgroundColor set in 'beforeRender' to access chart area for gradient
        backgroundColor: 'rgba(59,130,246,0.12)',
        borderColor: 'rgba(59,130,246,1)',
        fill: true,
        pointHoverRadius: 6,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: { padding: { top: 6, right: 6, bottom: 6, left: 6 } },
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.dataset.label}: ${ctx.formattedValue}`
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: '#475569' }
        },
        y: {
          beginAtZero: true,
          ticks: { color: '#475569', precision: 0 }
        }
      }
    },
    plugins: [{
      id: 'lineGradientPlugin',
      beforeDraw: (chart) => {
        const ctx = chart.ctx;
        const area = chart.chartArea;
        if (!area) return;
        // create gradient based on area height (so it scales)
        const g = ctx.createLinearGradient(0, area.top, 0, area.bottom);
        g.addColorStop(0, 'rgba(59,130,246,0.16)');
        g.addColorStop(1, 'rgba(59,130,246,0.02)');
        chart.data.datasets[0].backgroundColor = g;
      }
    }]
  });

  // Bar Chart
  const barCtx = document.getElementById('barChart').getContext('2d');
  const barChart = new Chart(barCtx, {
    type: 'bar',
    data: {
      labels: ['Active', 'Completed', 'Pending'],
      datasets: [{
        label: 'Jumlah',
        data: [statusValues.active, statusValues.completed, statusValues.pending],
        borderRadius: 8,
        barPercentage: 0.6,
        categoryPercentage: 0.7,
        backgroundColor: [
          'rgba(59,130,246,0.95)',
          'rgba(16,185,129,0.95)',
          'rgba(249,115,22,0.95)'
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: { label: ctx => ` ${ctx.label}: ${ctx.formattedValue}` }
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#475569' } },
        y: { beginAtZero: true, ticks: { color: '#475569', precision: 0 } }
      }
    }
  });

  // Ensure charts resize nicely with container
  function resizeCharts() {
    lineChart.resize();
    barChart.resize();
  }
  window.addEventListener('resize', resizeCharts);
});
</script>
</body>
</html>
