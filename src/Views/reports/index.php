<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - HRIS MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
</head>
<body class="h-full bg-slate-900">
    <!-- Loading Screen -->
    <div id="page-loading" class="fixed inset-0 bg-slate-900 z-50 flex items-center justify-center">
        <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-blue-500 mb-4"></div>
            <h2 class="text-2xl font-semibold text-white">Loading Reports...</h2>
            <p class="text-slate-400 mt-2">Please wait</p>
        </div>
    </div>

    <div class="flex h-full">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <!-- Header -->
            <header class="bg-slate-800 border-b border-slate-700">
                <div class="px-8 py-6">
                    <h1 class="text-3xl font-bold text-white">Reports & Analytics</h1>
                    <p class="mt-2 text-slate-400">Comprehensive HR data analysis and insights</p>
                </div>
            </header>

            <!-- Main Content -->
            <main class="p-8">
            <!-- Report Type Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Attendance Reports -->
                <a href="<?= base_url('/reports/attendance-view') ?>" class="group">
                    <div class="bg-slate-800 rounded-xl border-2 border-slate-700 hover:border-blue-500 shadow-xl p-6 transition-all transform hover:scale-105">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mb-4 group-hover:shadow-lg group-hover:shadow-blue-500/50 transition-all">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2 group-hover:text-blue-400 transition-colors">Attendance Reports</h3>
                        <p class="text-slate-400 text-sm mb-4">Daily trends, status distribution, and work hours analysis</p>
                        <div class="flex items-center text-blue-400 text-sm font-medium">
                            View Reports
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Leave Reports -->
                <a href="<?= base_url('/reports/leave-view') ?>" class="group">
                    <div class="bg-slate-800 rounded-xl border-2 border-slate-700 hover:border-green-500 shadow-xl p-6 transition-all transform hover:scale-105">
                        <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mb-4 group-hover:shadow-lg group-hover:shadow-green-500/50 transition-all">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2 group-hover:text-green-400 transition-colors">Leave Analytics</h3>
                        <p class="text-slate-400 text-sm mb-4">Leave requests, balances, and utilization patterns</p>
                        <div class="flex items-center text-green-400 text-sm font-medium">
                            View Reports
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Employee Analytics -->
                <a href="<?= base_url('/reports/employees-view') ?>" class="group">
                    <div class="bg-slate-800 rounded-xl border-2 border-slate-700 hover:border-purple-500 shadow-xl p-6 transition-all transform hover:scale-105">
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mb-4 group-hover:shadow-lg group-hover:shadow-purple-500/50 transition-all">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2 group-hover:text-purple-400 transition-colors">Employee Analytics</h3>
                        <p class="text-slate-400 text-sm mb-4">Headcount, demographics, and workforce insights</p>
                        <div class="flex items-center text-purple-400 text-sm font-medium">
                            View Reports
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Productivity Metrics -->
                <a href="<?= base_url('/reports/productivity-view') ?>" class="group">
                    <div class="bg-slate-800 rounded-xl border-2 border-slate-700 hover:border-orange-500 shadow-xl p-6 transition-all transform hover:scale-105">
                        <div class="w-14 h-14 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center mb-4 group-hover:shadow-lg group-hover:shadow-orange-500/50 transition-all">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2 group-hover:text-orange-400 transition-colors">Productivity Metrics</h3>
                        <p class="text-slate-400 text-sm mb-4">Performance indicators and efficiency analysis</p>
                        <div class="flex items-center text-orange-400 text-sm font-medium">
                            View Reports
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

            </div>

            <!-- Quick Stats -->
            <div class="mt-12">
                <h2 class="text-xl font-semibold text-white mb-6">Quick Overview</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-slate-400 text-sm">Total Employees</p>
                                <p class="text-3xl font-bold text-white mt-2">
                                    <span id="total-employees" class="skeleton-loading">--</span>
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-slate-400 text-sm">Present Today</p>
                                <p class="text-3xl font-bold text-white mt-2">
                                    <span id="present-today" class="skeleton-loading">--</span>
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-slate-400 text-sm">On Leave</p>
                                <p class="text-3xl font-bold text-white mt-2">
                                    <span id="on-leave" class="skeleton-loading">--</span>
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-slate-400 text-sm">Avg Work Hours</p>
                                <p class="text-3xl font-bold text-white mt-2">
                                    <span id="avg-hours" class="skeleton-loading">--</span>
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-orange-500/10 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </main>
        </div>
    </div>

    <script src="<?= base_url('/assets/js/config.js') ?>"></script>
    <script src="<?= base_url('/assets/js/auth.js') ?>"></script>
    <script src="<?= base_url('/assets/js/token-manager.js') ?>"></script>
    <style>
        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }
        
        .skeleton-loading {
            display: inline-block;
            min-width: 60px;
            height: 1em;
            background: linear-gradient(90deg, #334155 25%, #475569 50%, #334155 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 4px;
            color: transparent !important;
        }
        
        .skeleton-loading.loaded {
            animation: none;
            background: none;
            color: inherit !important;
        }
    </style>
    <script>
        // Load quick stats
        async function loadQuickStats() {
            try {
                // Fetch headcount data
                const headcountResponse = await fetch(AppConfig.getApiUrl('/reports/headcount'), {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('hris_token')}` }
                });
                
                if (headcountResponse.ok) {
                    const headcountResult = await headcountResponse.json();
                    if (headcountResult.success) {
                        const element = document.getElementById('total-employees');
                        element.textContent = headcountResult.data.report.summary.total_employees || '0';
                        element.classList.add('loaded');
                    }
                }
                
                // Fetch today's attendance data
                const today = new Date().toISOString().split('T')[0];
                const attendanceResponse = await fetch(AppConfig.getApiUrl(`/reports/attendance?start_date=${today}&end_date=${today}`), {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('hris_token')}` }
                });
                
                if (attendanceResponse.ok) {
                    const attendanceResult = await attendanceResponse.json();
                    if (attendanceResult.success) {
                        const summary = attendanceResult.data.report.summary;
                        
                        const presentElement = document.getElementById('present-today');
                        presentElement.textContent = (summary.present + summary.late) || '0';
                        presentElement.classList.add('loaded');
                        
                        const hoursElement = document.getElementById('avg-hours');
                        hoursElement.textContent = summary.average_hours ? summary.average_hours.toFixed(1) : '0.0';
                        hoursElement.classList.add('loaded');
                    }
                }
                
                // Fetch leave data for today
                const leaveResponse = await fetch(AppConfig.getApiUrl(`/reports/leave?start_date=${today}&end_date=${today}`), {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('hris_token')}` }
                });
                
                if (leaveResponse.ok) {
                    const leaveResult = await leaveResponse.json();
                    if (leaveResult.success) {
                        const leaveElement = document.getElementById('on-leave');
                        leaveElement.textContent = leaveResult.data.report.summary.approved || '0';
                        leaveElement.classList.add('loaded');
                    }
                }
            } catch (error) {
                console.error('Error loading stats:', error);
                // Remove skeleton loading on error
                document.querySelectorAll('.skeleton-loading').forEach(el => {
                    el.classList.add('loaded');
                    if (el.textContent === '--') {
                        el.textContent = '0';
                    }
                });
            } finally {
                // Hide loading screen after data is loaded
                hideLoadingScreen();
            }
        }

        function hideLoadingScreen() {
            const loadingScreen = document.getElementById('page-loading');
            if (loadingScreen) {
                setTimeout(() => {
                    loadingScreen.style.opacity = '0';
                    loadingScreen.style.transition = 'opacity 0.3s ease-out';
                    setTimeout(() => {
                        loadingScreen.style.display = 'none';
                    }, 300);
                }, 300);
            }
        }

        document.addEventListener('DOMContentLoaded', loadQuickStats);
    </script>
</body>
</html>
