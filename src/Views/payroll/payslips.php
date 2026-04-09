<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Payslips - HRIS MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
</head>
<body class="h-full bg-slate-900 overflow-hidden">
    <div class="flex h-full bg-slate-900">
        <aside class="w-64 bg-slate-800 border-r border-slate-700 flex flex-col">
            <div class="p-6 border-b border-slate-700">
                <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">HRIS MVP</h1>
                <p class="text-xs text-slate-400 mt-1">Human Resources System</p>
            </div>
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="<?= base_url('/dashboard/employee') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">Dashboard</a>
                <a href="<?= base_url('/attendance') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">My Attendance</a>
                <a href="<?= base_url('/leave') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">Leave Requests</a>
                <a href="<?= base_url('/profile') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">My Profile</a>
                <a href="<?= base_url('/payslips') ?>" class="flex items-center px-4 py-3 text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg shadow-blue-900/50">My Payslips</a>
            </nav>
            <div class="p-4 border-t border-slate-700">
                <button id="logout-btn" class="w-full px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">Logout</button>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto bg-slate-900 p-8 space-y-6">
            <header>
                <h2 class="text-3xl font-bold text-white">My Payslips</h2>
                <p class="text-slate-400 mt-1">View your payroll history and download payslip details</p>
            </header>

            <!-- Filters -->
            <section class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-slate-300 text-sm mb-2">Year</label>
                        <input id="filter-year" type="number" placeholder="2026" class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                    </div>
                    <div>
                        <label class="block text-slate-300 text-sm mb-2">Month</label>
                        <select id="filter-month" class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                            <option value="">All Months</option>
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 flex items-end">
                        <button id="load-payslips-btn" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
                            Search Payslips
                        </button>
                    </div>
                </div>
            </section>

            <!-- Payslips Grid -->
            <section id="payslips-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="col-span-full text-center py-12 text-slate-400">
                    Loading payslips...
                </div>
            </section>
        </main>
    </div>

    <!-- Payslip Detail Modal -->
    <div id="detail-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-700 flex items-center justify-between">
                <h3 class="text-2xl font-bold text-white">Payslip Details</h3>
                <button id="close-modal" class="text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-6" id="modal-content">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <script src="<?= base_url('/assets/js/config.js') ?>"></script>
    <script src="<?= base_url('/assets/js/auth.js') ?>"></script>
    <script src="<?= base_url('/assets/js/token-manager.js') ?>"></script>
    <script>
        const token = localStorage.getItem('hris_token');
        if (!token) {
            window.location.href = window.AppConfig.getBaseUrl('/login');
        }

        const container = document.getElementById('payslips-container');

        const headers = {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        const money = (value) => {
            const num = Number(value || 0);
            return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };

        const formatDate = (dateStr) => {
            if (!dateStr) return 'N/A';
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        };

        const renderPayslips = (payslips) => {
            if (!payslips || payslips.length === 0) {
                container.innerHTML = '<div class="col-span-full text-center py-12 text-slate-400">No payslips found. Try adjusting your filters.</div>';
                return;
            }

            container.innerHTML = payslips.map(item => {
                const line = item.line_item || {};
                const period = item.period || {};
                const run = item.run || {};
                
                return `
                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 hover:border-blue-600 transition-all cursor-pointer" onclick="viewPayslipDetail('${line.id}')">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-white">${period.code || 'N/A'}</h3>
                                <p class="text-xs text-slate-400">${formatDate(period.start_date)} - ${formatDate(period.end_date)}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold ${line.payment_status === 'Paid' ? 'bg-green-600 text-white' : 'bg-yellow-600 text-white'}">
                                ${line.payment_status || 'Unpaid'}
                            </span>
                        </div>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Days Worked:</span>
                                <span class="text-white font-semibold">${Number(line.attendance_days || 0).toFixed(1)}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Basic Pay:</span>
                                <span class="text-white">₱${money(line.basic_pay)}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Overtime:</span>
                                <span class="text-white">₱${money(line.overtime_pay)}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Gross Pay:</span>
                                <span class="text-white font-semibold">₱${money(line.gross_pay)}</span>
                            </div>
                        </div>
                        
                        <div class="pt-4 border-t border-slate-700">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-red-400">Total Deductions:</span>
                                <span class="text-red-400">-₱${money(line.total_deductions)}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-lg font-bold text-white">Net Pay:</span>
                                <span class="text-lg font-bold text-green-400">₱${money(line.net_pay)}</span>
                            </div>
                        </div>
                        
                        <button class="mt-4 w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold">
                            View Details
                        </button>
                    </div>
                `;
            }).join('');
        };

        const loadPayslips = async () => {
            container.innerHTML = '<div class="col-span-full text-center py-12 text-slate-400">Loading payslips...</div>';
            
            const year = document.getElementById('filter-year').value.trim();
            const month = document.getElementById('filter-month').value.trim();
            const params = new URLSearchParams();
            if (year) params.append('year', year);
            if (month) params.append('month', month);
            params.append('limit', '50');

            try {
                const response = await window.AuthManager.authFetch(window.AppConfig.getApiUrl('/payroll/payslips?' + params.toString()), {
                    method: 'GET',
                    headers
                });
                const result = await response.json();
                
                if (result.success && result.data && Array.isArray(result.data.payslips)) {
                    renderPayslips(result.data.payslips);
                } else {
                    renderPayslips([]);
                }
            } catch (error) {
                container.innerHTML = '<div class="col-span-full text-center py-12 text-red-400">Error loading payslips</div>';
            }
        };

        async function viewPayslipDetail(lineItemId) {
            document.getElementById('detail-modal').classList.remove('hidden');
            document.getElementById('modal-content').innerHTML = '<div class="text-center py-8 text-slate-400">Loading details...</div>';
            
            try {
                const response = await window.AuthManager.authFetch(window.AppConfig.getApiUrl('/payroll/payslips/' + encodeURIComponent(lineItemId)), {
                    method: 'GET',
                    headers
                });
                const result = await response.json();
                
                if (result.success && result.data) {
                    showPayslipDetail(result.data);
                } else {
                    document.getElementById('modal-content').innerHTML = '<div class="text-center py-8 text-red-400">Error loading details</div>';
                }
            } catch (error) {
                document.getElementById('modal-content').innerHTML = '<div class="text-center py-8 text-red-400">Error loading details</div>';
            }
        }

        function showPayslipDetail(data) {
            const line = data.line_item || {};
            const period = data.period || {};
            const run = data.run || {};
            
            const content = `
                <div class="space-y-6">
                    <!-- Period Info -->
                    <div class="bg-slate-700 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-white mb-3">Pay Period: ${period.code || 'N/A'}</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-slate-400">Period Start</p>
                                <p class="text-white">${formatDate(period.start_date)}</p>
                            </div>
                            <div>
                                <p class="text-slate-400">Period End</p>
                                <p class="text-white">${formatDate(period.end_date)}</p>
                            </div>
                            <div>
                                <p class="text-slate-400">Pay Date</p>
                                <p class="text-white">${formatDate(period.pay_date)}</p>
                            </div>
                            <div>
                                <p class="text-slate-400">Status</p>
                                <span class="px-2 py-1 rounded text-xs ${line.payment_status === 'Paid' ? 'bg-green-600' : 'bg-yellow-600'} text-white">
                                    ${line.payment_status || 'Unpaid'}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Earnings -->
                    <div class="bg-slate-700 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-white mb-3">Earnings</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-300">Days Worked:</span>
                                <span class="text-white">${Number(line.attendance_days || 0).toFixed(1)} days</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-300">Hours Worked:</span>
                                <span class="text-white">${Number(line.attendance_hours || 0).toFixed(1)} hrs</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-300">Basic Pay:</span>
                                <span class="text-white">₱${money(line.basic_pay)}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-300">Overtime Pay (${Number(line.overtime_hours || 0).toFixed(1)} hrs):</span>
                                <span class="text-white">₱${money(line.overtime_pay)}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-300">Leave Pay:</span>
                                <span class="text-white">₱${money(line.leave_pay)}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-slate-600">
                                <span class="text-white font-semibold">Gross Pay:</span>
                                <span class="text-white font-semibold">₱${money(line.gross_pay)}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Deductions -->
                    <div class="bg-slate-700 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-white mb-3">Deductions</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-300">Withholding Tax:</span>
                                <span class="text-red-400">₱${money(line.tax_amount)}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-300">SSS:</span>
                                <span class="text-red-400">₱${money(line.sss_amount)}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-300">PhilHealth:</span>
                                <span class="text-red-400">₱${money(line.philhealth_amount)}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-300">Pag-IBIG:</span>
                                <span class="text-red-400">₱${money(line.pagibig_amount)}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-slate-600">
                                <span class="text-red-400 font-semibold">Total Deductions:</span>
                                <span class="text-red-400 font-semibold">₱${money(line.total_deductions)}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Net Pay -->
                    <div class="bg-gradient-to-r from-green-900/30 to-emerald-900/30 border border-green-700 rounded-lg p-6">
                        <div class="flex justify-between items-center">
                            <span class="text-2xl font-bold text-white">Net Pay:</span>
                            <span class="text-3xl font-bold text-green-400">₱${money(line.net_pay)}</span>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('modal-content').innerHTML = content;
        }

        document.getElementById('load-payslips-btn').addEventListener('click', loadPayslips);
        
        document.getElementById('close-modal').addEventListener('click', () => {
            document.getElementById('detail-modal').classList.add('hidden');
        });

        document.getElementById('logout-btn').addEventListener('click', () => {
            window.AuthManager.logout();
        });

        // Set current year as default
        document.getElementById('filter-year').value = new Date().getFullYear();

        // Load payslips on page load
        loadPayslips();
    </script>
</body>
</html>
