<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kuli - NguliKuy</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Feather icons -->
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        .nav-active { border-bottom: 2px solid #3b82f6; color: #1f2937; }
        .status-completed { background-color: #dcfce7; color: #166534; }
        .status-in-progress { background-color: #fef3c7; color: #92400e; }
        .status-pending { background-color: #dbeafe; color: #1e40af; }
        .status-cancelled { background-color: #fecaca; color: #dc2626; }
        .status-open { background-color: #dcfce7; color: #166534; }


        /* Notification box */
        #ajax-notification { position: fixed; bottom: 20px; right: 20px; padding: 10px 20px; border-radius: 6px; color: white; z-index: 1000; display: none; transition: opacity 0.4s ease-in-out; }
        #ajax-notification.success { background-color: #10B981; }
        #ajax-notification.error { background-color: #EF4444; }

        /* Modal transitions */
        #actionModal { transition: opacity 0.25s ease-out; }
        #modal-content { transition: transform 0.25s ease-out, opacity 0.25s ease-out; transform: translateY(12px); opacity: 0; }
        #actionModal:not(.hidden) { opacity: 1; }
        #actionModal:not(.hidden) #modal-content { transform: translateY(0); opacity: 1; }

        /* Small helpers */
        .truncate-multiline { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    
        /* ---------- Responsive table fixes ---------- */
        .table-wrapper { overflow: visible; } /* allow stable layout */
        .responsive-table {
        width: 100%;
        table-layout: fixed; /* force columns widths from colgroup */
        border-collapse: collapse;
        white-space: normal;
        }
        .responsive-table th, .responsive-table td {
        padding: 0.75rem;
        vertical-align: top;
        font-size: 0.95rem;
        }
        .responsive-table col.col-job { width: 20%; }
        .responsive-table col.col-customer { width: 15%; }
        .responsive-table col.col-date { width: 12%; }
        .responsive-table col.col-price { width: 12%; }
        .responsive-table col.col-status { width: 10%; }
        .responsive-table col.col-desc { width: 26%; }
        .responsive-table col.col-actions { width: 100px; }
        .responsive-table td.desc {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        }
        @media (max-width: 768px) {
        .responsive-table td.desc { white-space: normal; }
        }
        .status-badge {
        display: inline-block;
        min-width: 74px;
        text-align: center;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        padding: .25rem .5rem;
        border-radius: 9999px;
        }
        .responsive-table td.actions {
        text-align: right;
        white-space: nowrap;
        }
        .table-outer {
        overflow-x: hidden;
        }
        .responsive-table th.actions-col,
        .responsive-table td.actions {
        position: sticky;
        right: 0;
        background: #fff;
        z-index: 2;
        }
        td.price { font-weight: 600; }
        .table-outer::-webkit-scrollbar { height: 8px; }
        .table-outer::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 999px; }

        /* Actions vertical stack - desktop stacked, mobile row */
        .actions-buttons {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
        }
        .actions-buttons button {
            min-width: 90px;
            text-align: center;
        }
        @media (max-width: 768px) {
            .actions-buttons {
                flex-direction: row !important;
                align-items: center;
                justify-content: flex-end;
            }
            .actions-buttons button { min-width: auto; }
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- NAV -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <a href="?tab=pending" class="flex-shrink-0 flex items-center">
                        <i data-feather="tool" class="text-blue-600"></i>
                        <span class="ml-3 font-bold text-xl">NguliKuy (Kuli)</span>
                    </a>
                </div>
                <div class="flex items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-medium">Halo, <?php echo htmlspecialchars($worker_name); ?></span>
                            <a href="index.php?logout=1" class="text-gray-500 hover:text-blue-600" title="Logout">
                                <i data-feather="log-out" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- AJAX notification -->
    <div id="ajax-notification" role="status" aria-live="polite"></div>

    <!-- CONTENT -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
