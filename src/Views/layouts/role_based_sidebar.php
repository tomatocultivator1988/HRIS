<?php
/**
 * Role-Based Sidebar Component
 * Dynamically shows appropriate navigation based on user role
 * Loaded via JavaScript to access localStorage
 * 
 * Usage:
 * $currentPage = 'attendance';
 * include __DIR__ . '/../layouts/role_based_sidebar.php';
 */

$currentPage = $currentPage ?? '';
?>
<aside id="app-sidebar" class="w-64 bg-slate-800 border-r border-slate-700 flex flex-col">
    <!-- Logo -->
    <div class="p-6 border-b border-slate-700">
        <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
            HRIS MVP
        </h1>
        <p class="text-xs text-slate-400 mt-1">Human Resources System</p>
    </div>
    
    <!-- Navigation (will be populated by JavaScript) -->
    <nav id="sidebar-nav" class="flex-1 p-4 space-y-2 overflow-y-auto">
        <!-- Loading state -->
        <div class="flex items-center justify-center py-8">
            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
        </div>
    </nav>
    
    <!-- User Profile Section (Employee only) -->
    <div id="sidebar-user-profile" class="p-4 border-t border-slate-700 hidden">
        <div class="flex items-center space-x-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                <span id="sidebar-user-initials">E</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate" id="sidebar-user-name">Employee User</p>
                <p class="text-xs text-slate-400 truncate" id="sidebar-user-email">employee@company.com</p>
            </div>
        </div>
        <button id="sidebar-logout-btn" class="w-full flex items-center justify-center px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Logout
        </button>
    </div>
    
    <!-- Logout Only (Admin) -->
    <div id="sidebar-logout-only" class="p-4 border-t border-slate-700 hidden">
        <button id="sidebar-logout-btn-admin" class="w-full px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">
            Logout
        </button>
    </div>
</aside>

<script>
(function() {
    const currentPage = '<?= $currentPage ?>';
    const user = JSON.parse(localStorage.getItem('hris_user') || '{}');
    
    if (!user || !user.role) {
        window.location.href = window.AppConfig ? window.AppConfig.getBaseUrl('/login') : '/HRIS/login';
        return;
    }
    
    const baseUrl = window.AppConfig ? window.AppConfig.getBaseUrl : (path) => '/HRIS' + path;
    
    // Helper function to create active class
    const isActive = (page) => {
        return page === currentPage 
            ? 'text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg shadow-blue-900/50'
            : 'text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all';
    };
    
    // Navigation items based on role
    const adminNav = [
        { page: 'dashboard', href: '/dashboard/admin', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', label: 'Dashboard' },
        { page: 'employees', href: '/employees', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', label: 'Employees' },
        { page: 'attendance', href: '/attendance', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', label: 'Attendance' },
        { page: 'leave', href: '/leave', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', label: 'Leave Requests' },
        { page: 'recruitment', href: '/recruitment', icon: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', label: 'Recruitment' },
        { page: 'compensation', href: '/compensation', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', label: 'Manage Salaries' },
        { page: 'payroll', href: '/payroll/simple', icon: 'M12 8c-2.21 0-4 .895-4 2s1.79 2 4 2 4 .895 4 2-1.79 2-4 2m0-10V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z', label: 'Payroll' },
        { page: 'reports', href: '/reports', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', label: 'Reports' }
    ];
    
    const employeeNav = [
        { page: 'dashboard', href: '/dashboard/employee', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', label: 'Dashboard' },
        { page: 'attendance', href: '/attendance', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', label: 'My Attendance' },
        { page: 'leave', href: '/leave', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', label: 'Leave Requests' },
        { page: 'profile', href: '/profile', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', label: 'My Profile' },
        { page: 'payslips', href: '/payslips', icon: 'M9 14h6m-6 4h6m2 2H7a2 2 0 01-2-2V6a2 2 0 012-2h5l5 5v9a2 2 0 01-2 2z', label: 'My Payslips' }
    ];
    
    // Select navigation based on role
    const navItems = user.role === 'admin' ? adminNav : employeeNav;
    
    // Build navigation HTML
    const navHTML = navItems.map(item => `
        <a href="${baseUrl(item.href)}" class="flex items-center px-4 py-3 ${isActive(item.page)}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${item.icon}" />
            </svg>
            ${item.label}
        </a>
    `).join('');
    
    // Update navigation
    document.getElementById('sidebar-nav').innerHTML = navHTML;
    
    // Show appropriate footer section
    if (user.role === 'admin') {
        document.getElementById('sidebar-logout-only').classList.remove('hidden');
        document.getElementById('sidebar-logout-btn-admin').addEventListener('click', () => {
            if (window.AuthManager) {
                window.AuthManager.logout();
            } else {
                localStorage.clear();
                window.location.href = baseUrl('/login');
            }
        });
    } else {
        // Employee - show user profile section
        document.getElementById('sidebar-user-profile').classList.remove('hidden');
        
        // Update user info
        if (user.first_name && user.last_name) {
            const initials = (user.first_name[0] + user.last_name[0]).toUpperCase();
            const fullName = `${user.first_name} ${user.last_name}`;
            
            document.getElementById('sidebar-user-initials').textContent = initials;
            document.getElementById('sidebar-user-name').textContent = fullName;
            document.getElementById('sidebar-user-email').textContent = user.email || user.work_email || '';
        }
        
        document.getElementById('sidebar-logout-btn').addEventListener('click', () => {
            if (window.AuthManager) {
                window.AuthManager.logout();
            } else {
                localStorage.clear();
                window.location.href = baseUrl('/login');
            }
        });
    }
})();
</script>
