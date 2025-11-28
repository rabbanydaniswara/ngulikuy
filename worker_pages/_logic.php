<?php
/**
 * Worker Dashboard - Logic
 * Handles data fetching, and authentication for the worker dashboard.
 */

require_once 'functions.php';

// 1. Amankan halaman
redirectIfNotWorker();

// 2. Ambil ID Pekerja dari session
$worker_profile_id = $_SESSION['worker_profile_id'];
$worker_name = $_SESSION['user_name'];

// 3. Ambil data berdasarkan tab yang aktif
$active_tab = $_GET['tab'] ?? 'pending';
$allJobs = [];
$filteredJobs = [];
$openPostedJobs = [];

// Get worker's skills
$worker = getWorkerById($worker_profile_id);
$worker_skills = $worker['keahlian'] ?? [];

if ($active_tab === 'find_jobs') {
    global $pdo;

    if (!empty($worker_skills)) {
        // Create placeholders for skills: ?,?,?
        $placeholders = implode(',', array_fill(0, count($worker_skills), '?'));

        // Fetch all open jobs that match the worker's skills
        $sql = "SELECT * FROM lowongan_diposting 
                WHERE status_lowongan = 'open' 
                AND id_pekerja IS NULL 
                AND jenis_pekerjaan IN ($placeholders) 
                ORDER BY dibuat_pada DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($worker_skills);
        $openPostedJobs = $stmt->fetchAll();
    } else {
        // If worker has no skills, they cannot see any jobs
        $openPostedJobs = [];
    }
} else {
    // Ambil semua job yang ditugaskan ke pekerja ini (untuk tab pending, active, completed)
    $allJobs = getWorkerJobs($worker_profile_id);

    // Filter job berdasarkan tab
    $filteredJobs = array_filter($allJobs, function($job) use ($active_tab) {
        if ($active_tab === 'pending') {
            return ($job['status_pekerjaan'] ?? '') === 'pending';
        }
        if ($active_tab === 'active') {
            return ($job['status_pekerjaan'] ?? '') === 'in-progress';
        }
        if ($active_tab === 'completed') {
            $s = $job['status_pekerjaan'] ?? '';
            return $s === 'completed' || $s === 'cancelled';
        }
        return false;
    });
}
