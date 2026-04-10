<?php
/**
 * Employee Sidebar Component
 * Include this in all employee-accessible pages
 * 
 * Usage:
 * $currentPage = 'dashboard'; // or 'attendance', 'leave', etc.
 * include __DIR__ . '/../layouts/employee_sidebar.php';
 */

$currentPage = $currentPage ?? '';
$isActive = function($page) use ($currentPage) {
    return $page === $currentPage 
        ? 'text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg shadow-blue-900/50'
        : 'text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all';
};
?>
<aside class="w-64 bg-slate-800 border-r border-slate-700 flex flex-col">
    <!-- Logo -->
    <div class="p-6 border-b border-slate-700">
        <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
            HRIS MVP
        </h1>
        <p class="text-xs text-slate-400 mt-1">Human Resources System</p>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
        <a href="<?= base_url('/dashboard/employee') ?>" class="flex items-center px-4 py-3 <?= $isActive('dashboard') ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </a>
        
        <a href="<?= base_url('/attendance') ?>" class="flex items-center px-4 py-3 <?= $isActive('attendance') ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            My Attendance
        </a>
        
        <a href="<?= base_url('/leave') ?>" class="flex items-center px-4 py-3 <?= $isActive('leave') ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Leave Requests
        </a>
        
        <a href="<?= base_url('/profile') ?>" class="flex items-center px-4 py-3 <?= $isActive('profile') ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            My Profile
        </a>
        
        <a href="<?= base_url('/payslips') ?>" class="flex items-center px-4 py-3 <?= $isActive('payslips') ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14h6m-6 4h6m2 2H7a2 2 0 01-2-2V6a2 2 0 012-2h5l5 5v9a2 2 0 01-2 2z" />
            </svg>
            My Payslips
        </a>
    </nav>
    
    <!-- User Profile -->
    <div class="p-4 border-t border-slate-700">
        <div class="flex items-center space-x-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                <span id="user-initials">E</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate" id="user-name">Employee User</p>
                <p class="text-xs text-slate-400 truncate" id="user-email">employee@company.com</p>
            </div>
        </div>
        <button id="logout-btn" class="w-full flex items-center justify-center px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Logout
        </button>
    </div>
</aside>

<script>
// Load user info for sidebar
(function() {
    const user = JSON.parse(localStorage.getItem('hris_user') || '{}');
    if (user.first_name && user.last_name) {
        const initials = (user.first_name[0] + user.last_name[0]).toUpperCase();
        const fullName = `${user.first_name} ${user.last_name}`;
        
        const initialsEl = document.getElementById('user-initials');
        const nameEl = document.getElementById('user-name');
        const emailEl = document.getElementById('user-email');
        
        if (initialsEl) initialsEl.textContent = initials;
        if (nameEl) nameEl.textContent = fullName;
        if (emailEl) emailEl.textContent = user.email || user.work_email || '';
    }
})();
</script>
