<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HRIS MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/assets/css/loading-skeletons.css') ?>">
</head>
<body class="h-full bg-slate-900 overflow-hidden">
    <!-- Loading Screen -->
    <div id="dashboard-loading" class="fixed inset-0 bg-slate-900 z-50 flex items-center justify-center">
        <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-blue-500 mb-4"></div>
            <h2 class="text-2xl font-semibold text-white">Loading Dashboard...</h2>
            <p class="text-slate-400 mt-2">Please wait while we fetch your data</p>
        </div>
    </div>

    <!-- Main Dashboard Container -->
    <div class="flex h-full bg-slate-900">
        
        <!-- Sidebar -->
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
                <a href="<?= base_url('/dashboard/admin') ?>" class="flex items-center px-4 py-3 text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg shadow-blue-900/50">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>
                
                <a href="<?= base_url('/employees') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Employees
                </a>
                
                <a href="<?= base_url('/attendance') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Attendance
                </a>
                
                <a href="<?= base_url('/leave') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Leave Requests
                </a>
                
                <a href="<?= base_url('/reports') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Reports
                </a>
            </nav>
            
            <!-- User Profile -->
            <div class="p-4 border-t border-slate-700">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                        A
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">Admin User</p>
                        <p class="text-xs text-slate-400 truncate">admin@company.com</p>
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
        
        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-slate-900">
            <!-- Header -->
            <header class="bg-slate-800 border-b border-slate-700 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-bold text-white">Dashboard</h2>
                        <p class="text-slate-400 mt-1">Welcome back, Admin! Here's what's happening today.</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm text-slate-400">Today</p>
                            <p class="text-lg font-semibold text-white" id="current-date"></p>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Dashboard Content -->
            <div class="p-8">
                <!-- Metrics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Employees Card -->
                    <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl p-6 shadow-xl shadow-blue-900/50 transform hover:scale-105 transition-all cursor-pointer">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <span class="text-blue-200 text-sm font-medium">+12%</span>
                        </div>
                        <h3 class="text-blue-100 text-sm font-medium mb-1">Total Employees</h3>
                        <p class="text-4xl font-bold text-white" data-metric="totalEmployees">0</p>
                    </div>
                    
                    <!-- Present Today Card -->
                    <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-xl p-6 shadow-xl shadow-green-900/50 transform hover:scale-105 transition-all cursor-pointer">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-green-200 text-sm font-medium">Today</span>
                        </div>
                        <h3 class="text-green-100 text-sm font-medium mb-1">Present Today</h3>
                        <p class="text-4xl font-bold text-white" data-metric="presentToday">0</p>
                    </div>
                    
                    <!-- Late Today Card -->
                    <div class="bg-gradient-to-br from-yellow-600 to-orange-600 rounded-xl p-6 shadow-xl shadow-orange-900/50 transform hover:scale-105 transition-all cursor-pointer">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-orange-200 text-sm font-medium">Alert</span>
                        </div>
                        <h3 class="text-orange-100 text-sm font-medium mb-1">Late Today</h3>
                        <p class="text-4xl font-bold text-white" data-metric="lateToday">0</p>
                    </div>
                    
                    <!-- Absent Today Card -->
                    <div class="bg-gradient-to-br from-red-600 to-red-700 rounded-xl p-6 shadow-xl shadow-red-900/50 transform hover:scale-105 transition-all cursor-pointer">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <span class="text-red-200 text-sm font-medium">Today</span>
                        </div>
                        <h3 class="text-red-100 text-sm font-medium mb-1">Absent Today</h3>
                        <p class="text-4xl font-bold text-white" data-metric="absentToday">0</p>
                    </div>
                </div>
                
                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Department Distribution Chart -->
                    <div class="bg-slate-800 rounded-xl p-6 border border-slate-700 shadow-xl">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-white">Department Distribution</h3>
                            <button class="text-slate-400 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>
                        </div>
                        <div class="relative" style="height: 280px;">
                            <canvas id="department-chart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Attendance Trend Chart -->
                    <div class="bg-slate-800 rounded-xl p-6 border border-slate-700 shadow-xl">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-white">Attendance Trend (7 Days)</h3>
                            <button class="text-slate-400 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>
                        </div>
                        <div class="relative" style="height: 280px;">
                            <canvas id="attendance-trend-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url('/assets/js/config.js') ?>"></script>
    <script src="<?= base_url('/assets/js/auth.js') ?>"></script>
    <script src="<?= base_url('/assets/js/token-manager.js') ?>"></script>
    <script src="<?= base_url('/assets/js/loading-skeletons.js') ?>"></script>
    <script src="<?= base_url('/assets/js/charts.js') ?>"></script>
    <script>
        // Display current date
        const dateElement = document.getElementById('current-date');
        if (dateElement) {
            const today = new Date();
            const options = { month: 'short', day: 'numeric', year: 'numeric' };
            dateElement.textContent = today.toLocaleDateString('en-US', options);
        }
        
        // Load dashboard metrics from API
        document.addEventListener('DOMContentLoaded', async function() {
            const loadingScreen = document.getElementById('dashboard-loading');
            
            try {
                const token = localStorage.getItem('hris_token');
                if (!token) {
                    console.error('No auth token found');
                    loadingScreen.style.display = 'none';
                    alert('Authentication required. Please log in again.');
                    window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
                    return;
                }

                console.log('Loading metrics with token:', token.substring(0, 20) + '...');

                // Load metrics from API
                const apiUrl = window.AppConfig ? window.AppConfig.apiUrl('dashboard/metrics') : '/HRIS/api/dashboard/metrics';
                console.log('API URL:', apiUrl);
                
                const authHeader = 'Bearer ' + token;
                console.log('Authorization header:', authHeader.substring(0, 30) + '...');
                
                const response = await fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Authorization': authHeader,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                console.log('Response status:', response.status);
                
                const data = await response.json();
                console.log('Dashboard API response:', data);
                
                if (data.success && data.data) {
                    // Update metric cards
                    const metrics = data.data.metrics || {};
                    document.querySelector('[data-metric="totalEmployees"]').textContent = metrics.totalEmployees || 0;
                    document.querySelector('[data-metric="presentToday"]').textContent = metrics.presentToday || 0;
                    document.querySelector('[data-metric="lateToday"]').textContent = metrics.lateToday || 0;
                    document.querySelector('[data-metric="absentToday"]').textContent = metrics.absentToday || 0;
                    
                    console.log('Metrics updated successfully');
                    
                    // Update charts if data available
                    if (data.data.charts) {
                        if (data.data.charts.departments && window.DashboardCharts) {
                            console.log('Updating department chart');
                            window.DashboardCharts.updateDepartmentChart(data.data.charts.departments);
                        }
                        if (data.data.charts.attendanceTrend && window.DashboardCharts) {
                            console.log('Updating attendance trend chart');
                            window.DashboardCharts.updateAttendanceTrend(data.data.charts.attendanceTrend);
                        }
                    }
                    
                    // Hide loading screen after successful load
                    setTimeout(() => {
                        loadingScreen.style.opacity = '0';
                        loadingScreen.style.transition = 'opacity 0.3s ease-out';
                        setTimeout(() => {
                            loadingScreen.style.display = 'none';
                        }, 300);
                    }, 500);
                    
                } else {
                    console.error('Failed to load metrics:', data.message);
                    loadingScreen.style.display = 'none';
                    alert('Failed to load dashboard metrics: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error loading dashboard metrics:', error);
                loadingScreen.style.display = 'none';
                alert('Error loading dashboard metrics. Please refresh the page.');
            }
        });
    </script>
</body>
</html>
