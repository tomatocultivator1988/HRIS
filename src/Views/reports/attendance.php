<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Reports - HRIS MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/assets/css/loading-skeletons.css') ?>">
    <style>
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .skeleton-loading {
            display: inline-block;
            min-width: 40px;
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
</head>
<body class="h-full bg-slate-900">
    <div class="flex h-full">
        <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <div class="flex-1 overflow-y-auto">
            <header class="bg-slate-800 border-b border-slate-700 sticky top-0 z-10">
                <div class="px-8 py-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Attendance Reports</h1>
                        <p class="text-slate-400 text-sm mt-1">Analyze attendance patterns and trends</p>
                    </div>
                    <div class="mt-4 flex items-center space-x-4">
                        <input type="date" id="start-date" class="px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white">
                        <span class="text-slate-400">to</span>
                        <input type="date" id="end-date" class="px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white">
                        <button onclick="loadReports()" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">Generate Report</button>
                    </div>
                </div>
            </header>

            <main class="p-8" id="report-content">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <div class="flex items-center justify-between">
                            <div><p class="text-slate-400 text-sm">Total Records</p><p class="text-3xl font-bold text-white mt-2"><span id="total-records" class="skeleton-loading">0</span></p></div>
                            <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
                        </div>
                    </div>
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <div class="flex items-center justify-between">
                            <div><p class="text-slate-400 text-sm">Present</p><p class="text-3xl font-bold text-green-400 mt-2"><span id="total-present" class="skeleton-loading">0</span></p></div>
                            <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        </div>
                    </div>
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <div class="flex items-center justify-between">
                            <div><p class="text-slate-400 text-sm">Late</p><p class="text-3xl font-bold text-yellow-400 mt-2"><span id="total-late" class="skeleton-loading">0</span></p></div>
                            <div class="w-12 h-12 bg-yellow-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        </div>
                    </div>
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <div class="flex items-center justify-between">
                            <div><p class="text-slate-400 text-sm">Absent</p><p class="text-3xl font-bold text-red-400 mt-2"><span id="total-absent" class="skeleton-loading">0</span></p></div>
                            <div class="w-12 h-12 bg-red-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        </div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Daily Attendance Trend</h3>
                        <canvas id="trendChart"></canvas>
                    </div>
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Status Distribution</h3>
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Detailed Records</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-700">
                                <tr>
                                    <th class="px-4 py-3 text-slate-300 font-medium">Date</th>
                                    <th class="px-4 py-3 text-slate-300 font-medium">Employee</th>
                                    <th class="px-4 py-3 text-slate-300 font-medium">Department</th>
                                    <th class="px-4 py-3 text-slate-300 font-medium">Time In</th>
                                    <th class="px-4 py-3 text-slate-300 font-medium">Time Out</th>
                                    <th class="px-4 py-3 text-slate-300 font-medium">Hours</th>
                                    <th class="px-4 py-3 text-slate-300 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody id="records-table" class="divide-y divide-slate-700">
                                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">No data available. Select date range and click Generate Report.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="<?= base_url('/assets/js/config.js') ?>"></script>
    <script src="<?= base_url('/assets/js/auth.js') ?>"></script>
    <script src="<?= base_url('/assets/js/token-manager.js') ?>"></script>
    <script src="<?= base_url('/assets/js/loading-skeletons.js') ?>"></script>
    <script src="<?= base_url('/assets/js/reports/attendance-charts.js') ?>"></script>
</body>
</html>
