<?php
/**
 * Worker Dashboard - Logic
 * Handles data fetching, and authentication for the worker dashboard.
 */

require_once 'functions.php';

// 1. Amankan halaman
redirectIfNotWorker();

// 2. Ambil ID Kuli dari session
$worker_profile_id = $_SESSION['worker_profile_id'];
$worker_name = $_SESSION['user_name'];

// 3. Ambil data berdasarkan tab yang aktif
$active_tab = $_GET['tab'] ?? 'pending';
$allJobs = [];
$filteredJobs = [];
$openPostedJobs = [];

if ($active_tab === 'find_jobs') {
    global $pdo;
    // Fetch all open jobs that haven't been taken by any worker
    $stmt = $pdo->prepare("SELECT * FROM posted_jobs WHERE status = 'open' AND worker_id IS NULL ORDER BY created_at DESC");
    $stmt->execute();
    $openPostedJobs = $stmt->fetchAll();
} else {
    // Ambil semua job yang ditugaskan ke kuli ini (untuk tab pending, active, completed)
    $allJobs = getWorkerJobs($worker_profile_id);

    // Filter job berdasarkan tab
    $filteredJobs = array_filter($allJobs, function($job) use ($active_tab) {
        if ($active_tab === 'pending') {
            return ($job['status'] ?? '') === 'pending';
        }
        if ($active_tab === 'active') {
            return ($job['status'] ?? '') === 'in-progress';
        }
        if ($active_tab === 'completed') {
            $s = $job['status'] ?? '';
            return $s === 'completed' || $s === 'cancelled';
        }
        return false;
    });
}
