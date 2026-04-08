<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productivity Metrics - HRIS MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/assets/css/loading-skeletons.css') ?>">
</head>
<body class="h-full bg-slate-900">
    <div class="flex h-full">
        <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <div class="flex-1 overflow-y-auto">
            <header class="bg-slate-800 border-b border-slate-700 sticky top-0 z-10">
                <div class="px-8 py-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Productivity Metrics</h1>
                        <p class="text-slate-400 text-sm mt-1">Performance indicators and efficiency analysis</p>
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
                            <div><p class="text-slate-400 text-sm">Attendance Rate</p><p class="text-3xl font-bold text-white mt-2" id="attendance-rate">0%</p></div>
                            <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        </div>
                    </div>
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <div class="flex items-center justify-between">
                            <div><p class="text-slate-400 text-sm">Avg Work Hours</p><p class="text-3xl font-bold text-blue-400 mt-2" id="avg-hours">0</p></div>
                            <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        </div>
                    </div>
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <div class="flex items-center justify-between">
                            <div><p class="text-slate-400 text-sm">Productivity Score</p><p class="text-3xl font-bold text-purple-400 mt-2" id="productivity-score">0</p></div>
                            <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
                        </div>
                    </div>
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <div class="flex items-center justify-between">
                            <div><p class="text-slate-400 text-sm">Efficiency Rate</p><p class="text-3xl font-bold text-yellow-400 mt-2" id="efficiency-rate">0%</p></div>
                            <div class="w-12 h-12 bg-yellow-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
                        </div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Attendance Rate Trend</h3>
                        <canvas id="rateChart"></canvas>
                    </div>
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Avg Work Hours by Department</h3>
                        <canvas id="hoursChart"></canvas>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Department Metrics</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-700">
                                <tr>
                                    <th class="px-4 py-3 text-slate-300 font-medium">Department</th>
                                    <th class="px-4 py-3 text-slate-300 font-medium">Avg Hours</th>
                                    <th class="px-4 py-3 text-slate-300 font-medium">Employees</th>
                                </tr>
                            </thead>
                            <tbody id="records-table" class="divide-y divide-slate-700">
                                <tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">No data available. Select date range and click Generate Report.</td></tr>
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
    <script src="<?= base_url('/assets/js/reports/productivity-charts.js') ?>"></script>
</body>
</html>
