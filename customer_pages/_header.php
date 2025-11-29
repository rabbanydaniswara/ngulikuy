<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NguliKuy - Platform booking tukang harian terpercaya">
    <meta name="theme-color" content="#ffffff">
    <title><?php echo $active_tab === 'home' ? 'Dashboard' : ucfirst($active_tab); ?> - NguliKuy</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            -webkit-tap-highlight-color: transparent;
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f9fafb; /* Gray-50 */
            color: #111827; /* Gray-900 */
        }
        
        /* Minimalist Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-completed { 
            background-color: #ecfdf5; /* Green-50 */
            color: #059669; /* Green-600 */
            border: 1px solid #d1fae5;
        }
        
        .status-in-progress { 
            background-color: #eff6ff; /* Blue-50 */
            color: #2563eb; /* Blue-600 */
            border: 1px solid #dbeafe;
        }
        
        .status-pending { 
            background-color: #fffbeb; /* Amber-50 */
            color: #d97706; /* Amber-600 */
            border: 1px solid #fef3c7;
        }
        
        .status-cancelled { 
            background-color: #fef2f2; /* Red-50 */
            color: #dc2626; /* Red-600 */
            border: 1px solid #fee2e2;
        }
        
        /* Card Styles */
        .card {
            background-color: white;
            border-radius: 0.75rem; /* rounded-xl */
            border: 1px solid #f3f4f6; /* gray-100 */
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.2s ease-in-out;
        }
        
        .card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        /* Navigation Active State */
        .nav-link {
            transition: all 0.2s;
        }
        .nav-link.active {
            background-color: #eff6ff; /* Blue-50 */
            color: #2563eb; /* Blue-600 */
        }
        .nav-link:not(.active):hover {
            background-color: #f9fafb; /* Gray-50 */
            color: #111827; /* Gray-900 */
        }

        /* Modal Transitions */
        .modal-enter {
            animation: modalEnter 0.2s ease-out forwards;
        }
        
        @keyframes modalEnter {
            from {
                opacity: 0;
                transform: scale(0.98) translateY(10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        /* Mobile Navigation */
        .mobile-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #f3f4f6;
            z-index: 40;
            padding-bottom: env(safe-area-inset-bottom);
        }
        
        .mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 0;
            color: #6b7280;
            transition: color 0.2s;
        }
        
        .mobile-nav-item.active {
            color: #2563eb;
        }
        
        .mobile-nav-item svg {
            width: 1.5rem;
            height: 1.5rem;
            margin-bottom: 0.25rem;
            stroke-width: 2px;
        }
        
        .mobile-nav-item span {
            font-size: 0.7rem;
            font-weight: 500;
        }

        /* Rating Stars */
        .rating-star-input { display: none; }
        .rating-star-label { cursor: pointer; transition: transform 0.1s; }
        .rating-star-label:hover { transform: scale(1.1); }
        .rating-star-label svg {
            width: 2rem; height: 2rem; color: #e5e7eb; transition: color 0.2s;
        }
        .rating-star-label:hover svg,
        .rating-star-label:hover ~ .rating-star-label svg,
        .rating-star-input:checked ~ .rating-star-label svg {
            color: #fbbf24; fill: #fbbf24;
        }
        .rating-stars-container {
            display: flex; flex-direction: row-reverse; justify-content: center; gap: 0.5rem;
        }

        /* Toast Notification */
        .toast-notification {
            position: fixed; top: 1.5rem; right: 1.5rem; z-index: 100;
            animation: slideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideIn {
            from { transform: translateY(-1rem); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Loading Button */
        .btn-loading {
            position: relative;
            color: transparent !important;
            pointer-events: none;
        }
        .btn-loading::after {
            content: "";
            position: absolute;
            width: 1.25em;
            height: 1.25em;
            top: 50%;
            left: 50%;
            margin-top: -0.625em;
            margin-left: -0.625em;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spin 0.75s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="min-h-screen pb-20 md:pb-0">
    
    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 backdrop-blur-xl bg-white/80 supports-[backdrop-filter]:bg-white/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="?tab=home" class="flex-shrink-0 flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white shadow-sm">
                            <i data-feather="tool" class="w-4 h-4"></i>
                        </div>
                        <span class="font-bold text-xl tracking-tight text-gray-900">NguliKuy</span>
                    </a>
                    
                    <!-- Desktop Nav -->
                    <div class="hidden md:ml-10 md:flex md:space-x-2">
                        <a href="?tab=home" class="nav-link <?php echo $active_tab === 'home' ? 'active' : 'text-gray-500'; ?> px-3 py-2 rounded-lg text-sm font-medium inline-flex items-center">
                            Home
                        </a>
                        <a href="?tab=search" class="nav-link <?php echo $active_tab === 'search' ? 'active' : 'text-gray-500'; ?> px-3 py-2 rounded-lg text-sm font-medium inline-flex items-center">
                            Cari Pekerja
                        </a>
                        <a href="?tab=orders" class="nav-link <?php echo $active_tab === 'orders' ? 'active' : 'text-gray-500'; ?> px-3 py-2 rounded-lg text-sm font-medium inline-flex items-center">
                            Pesanan Saya
                        </a>
                        <a href="?tab=my_jobs" class="nav-link <?php echo $active_tab === 'my_jobs' ? 'active' : 'text-gray-500'; ?> px-3 py-2 rounded-lg text-sm font-medium inline-flex items-center">
                            Pekerja Saya
                        </a>
                        <a href="?tab=post_job" class="nav-link <?php echo $active_tab === 'post_job' ? 'active' : 'text-gray-500'; ?> px-3 py-2 rounded-lg text-sm font-medium inline-flex items-center">
                            Buat Lowongan
                        </a>
                    </div>
                </div>
                
                <!-- Right Side Actions -->
                <div class="flex items-center gap-3">
                    <!-- Notifications -->
                    <button class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors relative">
                        <i data-feather="bell" class="w-5 h-5"></i>
                        <?php if ($pendingOrderCount > 0): ?>
                        <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                        <?php endif; ?>
                    </button>

                    <!-- User Menu -->
                    <div class="hidden sm:flex items-center gap-3 pl-3 border-l border-gray-200">
                        <div class="text-right hidden lg:block">
                            <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></p>
                            <p class="text-xs text-gray-500">Pelanggan</p>
                        </div>
                        <div class="h-9 w-9 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-semibold border border-gray-200">
                            <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
                        </div>
                        <a href="login.php?logout=1" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Keluar">
                            <i data-feather="log-out" class="w-5 h-5"></i>
                        </a>
                    </div>

                    <!-- Mobile Logout (Icon only) -->
                    <a href="login.php?logout=1" class="sm:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                        <i data-feather="log-out" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (!empty($success_message)): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="alert-notification flex items-center p-4 bg-green-50 border border-green-100 rounded-xl shadow-sm text-green-700">
                <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <i data-feather="check" class="w-5 h-5 text-green-600"></i>
                </div>
                <span class="font-medium text-sm"><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
             <div class="alert-notification flex items-center p-4 bg-red-50 border border-red-100 rounded-xl shadow-sm text-red-700">
                <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                    <i data-feather="alert-circle" class="w-5 h-5 text-red-600"></i>
                </div>
                <span class="font-medium text-sm"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Main Content Wrapper -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
