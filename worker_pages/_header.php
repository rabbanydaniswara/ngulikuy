<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pekerja - NguliKuy</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Feather icons -->
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #334155; }
        
        /* Modern Utilities */
        .card-modern {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem; /* 12px */
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        
        /* Navigation */
        .nav-link {
            color: #64748b;
            font-weight: 500;
            padding: 1rem 1.5rem;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        .nav-link:hover {
            color: #0f172a;
            border-bottom-color: #cbd5e1;
        }
        .nav-active {
            color: #2563eb;
            border-bottom: 2px solid #2563eb;
        }

        /* Status Badges - Pastel Modern */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.025em;
        }
        .status-completed { background-color: #ecfdf5; color: #059669; } /* Emerald */
        .status-in-progress { background-color: #eff6ff; color: #2563eb; } /* Blue */
        .status-pending { background-color: #fefce8; color: #ca8a04; } /* Yellow/Amber */
        .status-cancelled { background-color: #fef2f2; color: #dc2626; } /* Red */
        .status-open { background-color: #f0fdf4; color: #16a34a; } /* Green */

        /* Notification box */
        #ajax-notification { position: fixed; bottom: 24px; right: 24px; padding: 12px 24px; border-radius: 8px; color: white; z-index: 1000; display: none; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); font-weight: 500; }
        #ajax-notification.success { background-color: #10B981; }
        #ajax-notification.error { background-color: #EF4444; }

        /* Modal transitions */
        #actionModal { transition: opacity 0.2s ease-out; }
        #modal-content { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); transform: scale(0.95); opacity: 0; }
        #actionModal:not(.hidden) { opacity: 1; }
        #actionModal:not(.hidden) #modal-content { transform: scale(1); opacity: 1; }

        /* Small helpers */
        .truncate-multiline { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    
        /* ---------- Responsive table fixes ---------- */
        .table-wrapper { overflow: visible; border: 1px solid #e2e8f0; border-radius: 0.75rem; overflow: hidden; } 
        .responsive-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
            white-space: normal;
        }
        .responsive-table th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .responsive-table td {
            padding: 1rem 1.5rem;
            vertical-align: top;
            font-size: 0.875rem;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }
        .responsive-table tr:last-child td { border-bottom: none; }
        
        .responsive-table col.col-job { width: 22%; }
        .responsive-table col.col-customer { width: 18%; }
        .responsive-table col.col-date { width: 12%; }
        .responsive-table col.col-price { width: 15%; }
        .responsive-table col.col-status { width: 12%; }
        .responsive-table col.col-desc { width: 21%; }
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
        
        .responsive-table td.actions {
            text-align: right;
            white-space: nowrap;
        }
        .table-outer {
            overflow-x: auto;
            border-radius: 0.75rem;
        }
        
        td.price { font-weight: 600; color: #0f172a; }

        /* Actions vertical stack */
        .actions-buttons {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }
        .actions-buttons button {
            width: 100%;
            justify-content: center;
        }
        @media (max-width: 768px) {
            .actions-buttons {
                flex-direction: row !important;
                align-items: center;
                justify-content: flex-end;
            }
            .actions-buttons button { width: auto; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50">
    <!-- NAV -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-600 p-1.5 rounded-lg">
                        <i data-feather="tool" class="text-white w-5 h-5"></i>
                    </div>
                    <span class="font-bold text-lg tracking-tight text-slate-900">NguliKuy <span class="text-slate-400 font-normal">| Pekerja</span></span>
                </div>
                <div class="flex items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-4">
                            <div class="hidden md:flex flex-col items-end mr-2">
                                <span class="text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($worker_name); ?></span>
                                <span class="text-xs text-slate-500">Worker Account</span>
                            </div>
                            <a href="?tab=profile" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-full transition-colors" title="Profile">
                                <i data-feather="user" class="w-5 h-5"></i>
                            </a>
                            <div class="h-6 w-px bg-slate-200 mx-2"></div>
                            <a href="login.php?logout=1" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-colors" title="Logout">
                                <i data-feather="log-out" class="w-5 h-5"></i>
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
