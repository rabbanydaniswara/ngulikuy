<?php
/**
 * Customer Dashboard - Main Controller
 *
 * This file acts as a router to display the correct content based on the 'tab' parameter.
 * It includes the necessary logic, header, content, and footer files.
 */

// Include the main logic file which handles authentication, data fetching, and form processing.
// The path is relative to this file.
require_once 'customer_pages/_logic.php';

// Include the header, which contains the HTML head, styles, and top navigation.
// The path is relative to this file.
require_once 'customer_pages/_header.php';

// Depending on the active tab, include the corresponding content file.
// The active tab is determined in _logic.php.
$tab_file = "customer_pages/{$active_tab}.php";

if (file_exists($tab_file)) {
    include $tab_file;
} else {
    // If the tab is invalid, default to the main dashboard view.
    include 'customer_pages/dashboard.php';
}

// Include the footer, which contains the mobile navigation, modals, scripts, and closing HTML tags.
// The path is relative to this file. The included file will handle its own sub-includes.
require_once 'customer_pages/_footer.php';
