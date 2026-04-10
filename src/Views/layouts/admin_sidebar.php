<?php
/**
 * Standard Admin Sidebar Component
 * Include this in all admin pages for consistency
 * 
 * Usage:
 * $currentPage = 'dashboard'; // or 'employees', 'attendance', etc.
 * include __DIR__ . '/../layouts/admin_sidebar.php';
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
        <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">HRIS MVP</h1>
        <p class="text-xs text-slate-400 mt-1">Human Resources System</p>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
        <a href="<?= base_url('/dashboard/admin') ?>" class="flex items-center px-4 py-3 <?= $isActive('dashboard') ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </a>
        
        <a href="<?= base_url('/employees') ?>" class="flex items-center px-4 py-3 <?= $isActive('employees') ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Employees
        </a>
        
        <a href="<?= base_url('/attendance') ?>" class="flex items-center px-4 py-3 <?= $isActive('attendance') ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            Attendance
        </a>
        
        <a href="<?= base_url('/leave') ?>" class="flex items-center px-4 py-3 <?= $isActive('leave') ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Leave Requests
        </a>
        
        <a href="<?= base_url('/recruitment') ?>" class="flex items-center px-4 py-3 <?= $isActive('recruitment') ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Recruitment
        </a>
        
        <a href="<?= base_url('/compensation') ?>" class="flex items-center px-4 py-3 <?= $isActive('compensation') ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Manage Salaries
        </a>
        
        <a href="<?= base_url('/payroll/simple') ?>" class="flex items-center px-4 py-3 <?= $isActive('payroll') ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 .895-4 2s1.79 2 4 2 4 .895 4 2-1.79 2-4 2m0-10V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Payroll
        </a>
        
        <a href="<?= base_url('/reports') ?>" class="flex items-center px-4 py-3 <?= $isActive('reports') ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Reports
        </a>
    </nav>
    
    <!-- Logout -->
    <div class="p-4 border-t border-slate-700">
        <button id="logout-btn" class="w-full px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">Logout</button>
    </div>
</aside>
