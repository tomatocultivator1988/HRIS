<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Analytics - HRIS MVP</title>
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
                        <h1 class="text-2xl font-bold text-white">Employee Analytics</h1>
                        <p class="text-slate-400 text-sm mt-1">Headcount, demographics, and workforce insights</p>
                    </div>
                </div>
            </header>

            <main class="p-8" id="report-content">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <div class="flex items-center justify-between">
                            <div><p class="text-slate-400 text-sm">Total Employees</p><p class="text-3xl font-bold text-white mt-2"><span id="total-employees" class="skeleton-loading">0</span></p></div>
                            <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
                        </div>
                    </div>
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <div class="flex items-center justify-between">
                            <div><p class="text-slate-400 text-sm">Departments</p><p class="text-3xl font-bold text-green-400 mt-2"><span id="total-departments" class="skeleton-loading">0</span></p></div>
                            <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>
                        </div>
                    </div>
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <div class="flex items-center justify-between">
                            <div><p class="text-slate-400 text-sm">Positions</p><p class="text-3xl font-bold text-purple-400 mt-2"><span id="total-positions" class="skeleton-loading">0</span></p></div>
                            <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></div>
                        </div>
                    </div>
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <div class="flex items-center justify-between">
                            <div><p class="text-slate-400 text-sm">Active Rate</p><p class="text-3xl font-bold text-yellow-400 mt-2"><span id="active-rate" class="skeleton-loading">0%</span></p></div>
                            <div class="w-12 h-12 bg-yellow-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
                        </div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Headcount by Department</h3>
                        <canvas id="departmentChart"></canvas>
                    </div>
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Employment Status</h3>
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Employee Directory</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-700">
                                <tr>
                                    <th class="px-4 py-3 text-slate-300 font-medium">Employee ID</th>
                                    <th class="px-4 py-3 text-slate-300 font-medium">Name</th>
                                    <th class="px-4 py-3 text-slate-300 font-medium">Department</th>
                                    <th class="px-4 py-3 text-slate-300 font-medium">Position</th>
                                    <th class="px-4 py-3 text-slate-300 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody id="records-table" class="divide-y divide-slate-700">
                                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Loading employee data...</td></tr>
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
    <script src="<?= base_url('/assets/js/reports/employee-charts.js') ?>"></script>
</body>
</html>
