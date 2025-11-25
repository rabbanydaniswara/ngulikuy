<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NguliKuy - Platform booking tukang harian terpercaya">
    <meta name="theme-color" content="#3b82f6">
    <title><?php echo $active_tab === 'home' ? 'Dashboard' : ucfirst($active_tab); ?> - NguliKuy</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            -webkit-tap-highlight-color: transparent;
        }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            background: linear-gradient(to bottom right, #f8fafc 0%, #e2e8f0 100%);
        }
        
        .gradient-bg { 
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%); 
        }
        
        .nav-active { 
            border-bottom: 3px solid #3b82f6; 
            color: #1f2937; 
            font-weight: 600;
        }
        
        .status-completed { 
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534; 
            border: 1px solid #86efac;
        }
        
        .status-in-progress { 
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e; 
            border: 1px solid #fcd34d;
        }
        
        .status-pending { 
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af; 
            border: 1px solid #93c5fd;
        }
        
        .status-cancelled { 
            background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
            color: #dc2626; 
            border: 1px solid #f87171;
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .worker-card {
            position: relative;
            overflow: hidden;
        }
        
        .worker-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        
        .worker-card:hover::before {
            left: 100%;
        }
        
        .badge-new {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .7; }
        }
        
        /* IMPROVED: Fixed rating star hover effect */
        .rating-star-input {
            display: none;
        }
        
        .rating-star-label {
            cursor: pointer;
            display: inline-block;
            transition: all 0.2s ease;
        }
        
        .rating-star-label svg {
            width: 2.5rem;
            height: 2.5rem;
            color: #d1d5db; /* Default empty color */
            transition: all 0.2s ease;
        }
        
        /* When hovering, fill the hovered star and all preceding stars (due to row-reverse) */
        .rating-star-label:hover svg,
        .rating-star-label:hover ~ .rating-star-label svg {
            color: #fbbf24;
            fill: #fbbf24;
        }
        
        /* When an input is checked, fill its star and all preceding stars. */
        /* This rule persists even when not hovering. */
        .rating-star-input:checked ~ .rating-star-label svg {
            color: #fbbf24;
            fill: #fbbf24;
        }
        
        .rating-stars-container {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .modal-enter {
            animation: modalEnter 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        @keyframes modalEnter {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* Mobile Navigation */
        .mobile-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            z-index: 40;
            padding: 0.5rem 0;
        }
        
        .mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            color: #6b7280;
            transition: all 0.2s;
        }
        
        .mobile-nav-item.active {
            color: #3b82f6;
        }
        
        .mobile-nav-item svg {
            width: 1.5rem;
            height: 1.5rem;
            margin-bottom: 0.25rem;
        }
        
        .mobile-nav-item span {
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        /* Responsive adjustments */
        @media (max-width: 640px) {
            body {
                padding-bottom: 5rem; /* Space for mobile nav */
            }
            
            .hero-section {
                padding: 2rem 1rem !important;
            }
            
            .card-hover:hover {
                transform: none;
            }
            
            .modal-content {
                max-height: 85vh;
                overflow-y: auto;
            }
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Loading state */
        .btn-loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        /* Toast notification */
        .toast-notification {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Mobile menu toggle */
        .mobile-menu {
            display: none;
        }
        
        @media (max-width: 640px) {
            .mobile-menu.active {
                display: block;
            }
        }
    </style>
</head>
<body class="min-h-screen">
    
    <nav class="bg-white shadow-md sticky top-0 z-50 backdrop-blur-sm bg-white/90">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="?tab=home" class="flex-shrink-0 flex items-center group">
                        <div class="p-2 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition-colors">
                            <i data-feather="tool" class="text-blue-600 w-5 h-5 sm:w-6 sm:h-6"></i>
                        </div>
                        <span class="ml-2 sm:ml-3 font-bold text-lg sm:text-xl bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">NguliKuy</span>
                    </a>
                    
                    <div class="hidden md:ml-8 md:flex md:space-x-4">
                        <a href="?tab=home" class="<?php echo $active_tab === 'home' ? 'nav-active' : 'text-gray-600 hover:text-gray-900'; ?> inline-flex items-center px-3 pt-1 pb-1 text-sm font-medium transition-colors">
                            <i data-feather="home" class="w-4 h-4 mr-2"></i>
                            Home
                        </a>
                        <a href="?tab=search" class="<?php echo $active_tab === 'search' ? 'nav-active' : 'text-gray-600 hover:text-gray-900'; ?> inline-flex items-center px-3 pt-1 pb-1 text-sm font-medium transition-colors">
                            <i data-feather="search" class="w-4 h-4 mr-2"></i>
                            Cari Tukang
                        </a>
                        <a href="?tab=orders" class="<?php echo $active_tab === 'orders' ? 'nav-active' : 'text-gray-600 hover:text-gray-900'; ?> inline-flex items-center px-3 pt-1 pb-1 text-sm font-medium transition-colors">
                            <i data-feather="clipboard" class="w-4 h-4 mr-2"></i>
                            Pesanan Saya
                        </a>
                        <a href="?tab=my_jobs" class="<?php echo $active_tab === 'my_jobs' ? 'nav-active' : 'text-gray-600 hover:text-gray-900'; ?> inline-flex items-center px-3 pt-1 pb-1 text-sm font-medium transition-colors">
                            <i data-feather="briefcase" class="w-4 h-4 mr-2"></i>
                            Pekerjaan Saya
                        </a>
                        <a href="?tab=post_job" class="<?php echo $active_tab === 'post_job' ? 'nav-active' : 'text-gray-600 hover:text-gray-900'; ?> inline-flex items-center px-3 pt-1 pb-1 text-sm font-medium transition-colors">
                            <i data-feather="plus-circle" class="w-4 h-4 mr-2"></i>
                            Buat Pekerjaan
                        </a>
                    </div>
                </div>
                
                <div class="hidden sm:ml-6 sm:flex sm:items-center space-x-3">
                    <div class="relative">
                        <button class="p-2 rounded-lg hover:bg-gray-100 transition-colors relative">
                            <i data-feather="bell" class="text-gray-600 w-5 h-5"></i>
                            <?php if ($pendingOrderCount > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold badge-new">
                                <?php echo $pendingOrderCount; ?>
                            </span>
                            <?php endif; ?>
                        </button>
                    </div>
                    <div class="flex items-center space-x-2 pl-3 border-l">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="User" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full ring-2 ring-blue-100">
                        <span class="text-sm font-semibold text-gray-700 hidden lg:inline"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                        <a href="login.php?logout=1" class="p-2 rounded-lg hover:bg-red-50 text-gray-600 hover:text-red-600 transition-colors" title="Logout"> title="Logout">
                            <i data-feather="log-out" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
                
                <div class="flex sm:hidden items-center space-x-2">
                    <div class="relative">
                        <button class="p-2 rounded-lg hover:bg-gray-100 transition-colors relative">
                            <i data-feather="bell" class="text-gray-600 w-5 h-5"></i>
                            <?php if ($pendingOrderCount > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center font-bold">
                                <?php echo $pendingOrderCount; ?>
                            </span>
                            <?php endif; ?>
                        </button>
                    </div>
                    <a href="login.php?logout=1" class="p-2 rounded-lg hover:bg-red-50 text-gray-600 hover:text-red-600 transition-colors" title="Logout">>
                        <i data-feather="log-out" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <?php if (!empty($success_message)): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="alert-notification p-3 sm:p-4 bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-500 text-green-800 rounded-lg shadow-md flex items-start">
                <i data-feather="check-circle" class="w-5 h-5 mr-2 sm:mr-3 text-green-600 flex-shrink-0 mt-0.5"></i>
                <span class="font-medium text-sm sm:text-base"><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
             <div class="alert-notification p-3 sm:p-4 bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 text-red-800 rounded-lg shadow-md flex items-start">
                <i data-feather="alert-circle" class="w-5 h-5 mr-2 sm:mr-3 text-red-600 flex-shrink-0 mt-0.5"></i>
                <span class="font-medium text-sm sm:text-base"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
