<?php
/**
 * Worker Dashboard - Main Controller
 *
 * This file acts as a router to display the correct content based on the 'tab' parameter.
 * It includes the necessary logic, header, content, and footer files.
 */

// Include the main logic file which handles authentication and data fetching.
require_once 'worker_pages/_logic.php';

// Include the header, which contains the HTML head, styles, and top navigation.
require_once 'worker_pages/_header.php';

define('IS_WORKER_PAGE', true);

// Depending on the active tab, include the corresponding content file.
if ($active_tab === 'find_jobs') {
    include 'worker_pages/find_jobs.php';
} elseif ($active_tab === 'profile') {
    include 'worker_pages/profile.php';
} else {
    // Default to the jobs list for pending, active, completed tabs
    include 'worker_pages/jobs.php';
}

// Include the footer, which contains the modal, scripts, and closing HTML tags.
require_once 'worker_pages/_footer.php';