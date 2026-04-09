<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Position Salaries - HRIS MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
</head>
<body class="h-full bg-slate-900 overflow-hidden">
    <div class="flex h-full bg-slate-900">
        <?php $currentPage = 'compensation'; include __DIR__ . '/../layouts/admin_sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto bg-slate-900 p-8 space-y-6">
            <header>
                <h2 class="text-3xl font-bold text-white">Manage Position Salaries</h2>
                <p class="text-slate-400 mt-1">Set salaries for each position - all employees with that position will inherit these settings</p>
            </header>

            <!-- Position List -->
            <section class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-white">Positions</h3>
                    <button id="refresh-btn" class="px-3 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-sm">Refresh</button>
                </div>
                
                <div class="overflow-auto rounded-lg border border-slate-700">
                    <table class="w-full text-sm text-slate-200">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="text-left px-4 py-3">Position</th>
                                <th class="text-left px-4 py-3">Department</th>
                                <th class="text-left px-4 py-3">Payroll Type</th>
                                <th class="text-right px-4 py-3">Base Salary</th>
                                <th class="text-right px-4 py-3">Net Pay (Est.)</th>
                                <th class="text-center px-4 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody id="positions-list" class="divide-y divide-slate-700">
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-400">Loading positions...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <!-- Edit Position Salary Modal -->
    <div id="edit-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-700 flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-white">Edit Position Salary</h3>
                    <p class="text-slate-400 text-sm mt-1" id="modal-position-name"></p>
                </div>
                <button id="close-modal" class="text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-6">
                <form id="salary-form" class="space-y-6">
                    <!-- Basic Salary Info -->
                    <div class="bg-slate-700 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-white mb-4">Salary Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-slate-300 text-sm mb-2">Payroll Type</label>
                                <select id="payroll-type" class="w-full px-3 py-2 rounded-lg bg-slate-600 text-white border border-slate-500">
                                    <option value="Monthly">Monthly</option>
                                    <option value="Daily">Daily</option>
                                    <option value="Hourly">Hourly</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-slate-300 text-sm mb-2">Base Salary (₱)</label>
                                <input id="base-salary" type="number" step="0.01" class="w-full px-3 py-2 rounded-lg bg-slate-600 text-white border border-slate-500">
                            </div>
                            <div>
                                <label class="block text-slate-300 text-sm mb-2">Daily Rate (₱)</label>
                                <input id="daily-rate" type="number" step="0.01" class="w-full px-3 py-2 rounded-lg bg-slate-600 text-white border border-slate-500">
                            </div>
                            <div>
                                <label class="block text-slate-300 text-sm mb-2">Hourly Rate (₱)</label>
                                <input id="hourly-rate" type="number" step="0.01" class="w-full px-3 py-2 rounded-lg bg-slate-600 text-white border border-slate-500">
                            </div>
                        </div>
                    </div>

                    <!-- Deductions -->
                    <div class="bg-slate-700 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-white mb-2">Government Deductions</h4>
                        <p class="text-slate-400 text-xs mb-4">Auto-calculated based on Philippine government contribution tables (2024)</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-slate-300 text-sm mb-2">SSS Employee Share (₱)</label>
                                <input id="sss-amount" type="text" readonly class="w-full px-3 py-2 rounded-lg bg-slate-800 text-slate-300 border border-slate-600 cursor-not-allowed">
                                <p class="text-slate-500 text-xs mt-1">Based on SSS contribution table</p>
                            </div>
                            <div>
                                <label class="block text-slate-300 text-sm mb-2">PhilHealth Employee Share (₱)</label>
                                <input id="philhealth-amount" type="text" readonly class="w-full px-3 py-2 rounded-lg bg-slate-800 text-slate-300 border border-slate-600 cursor-not-allowed">
                                <p class="text-slate-500 text-xs mt-1">2.5% of monthly salary</p>
                            </div>
                            <div>
                                <label class="block text-slate-300 text-sm mb-2">Pag-IBIG Employee Share (₱)</label>
                                <input id="pagibig-amount" type="text" readonly class="w-full px-3 py-2 rounded-lg bg-slate-800 text-slate-300 border border-slate-600 cursor-not-allowed">
                                <p class="text-slate-500 text-xs mt-1">1-2% of monthly salary (max ₱100)</p>
                            </div>
                            <div>
                                <label class="block text-slate-300 text-sm mb-2">Withholding Tax (₱)</label>
                                <input id="tax-amount" type="text" readonly class="w-full px-3 py-2 rounded-lg bg-slate-800 text-slate-300 border border-slate-600 cursor-not-allowed">
                                <p class="text-slate-500 text-xs mt-1">Based on TRAIN Law tax table</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="p-6 border-t border-slate-700 flex gap-3">
                <button id="cancel-btn" class="flex-1 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg">Cancel</button>
                <button id="save-btn" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Success Toast -->
    <div id="success-toast" class="hidden fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        <p id="success-message"></p>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirm-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 w-full max-w-lg p-6">
            <div class="flex items-start gap-3 mb-4">
                <svg class="w-6 h-6 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <h3 class="text-xl font-bold text-white">Confirm Salary Changes</h3>
                </div>
            </div>
            
            <div class="space-y-3 mb-6">
                <p class="text-slate-300">Are you sure you want to save these salary settings?</p>
                
                <div class="bg-yellow-900 bg-opacity-30 border border-yellow-600 rounded-lg p-3">
                    <p class="text-yellow-200 text-sm font-semibold mb-2">Important Notes:</p>
                    <ul class="text-yellow-100 text-sm space-y-1 list-disc list-inside">
                        <li>This will affect all employees with this position</li>
                        <li>Changes apply to <span class="font-semibold">future payroll runs only</span></li>
                        <li>Existing/finalized payroll will NOT be updated</li>
                    </ul>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button id="confirm-cancel-btn" class="flex-1 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg">Cancel</button>
                <button id="confirm-save-btn" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loading-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-8 flex flex-col items-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mb-4"></div>
            <p class="text-white text-lg">Saving changes...</p>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="error-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-slate-800 rounded-xl border border-red-500 w-full max-w-md p-6">
            <h3 class="text-xl font-bold text-red-400 mb-4">Error</h3>
            <p id="error-message" class="text-slate-300 mb-6"></p>
            <button id="error-close-btn" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">Close</button>
        </div>
    </div>

    <script src="<?= base_url('/assets/js/config.js') ?>"></script>
    <script src="<?= base_url('/assets/js/auth.js') ?>"></script>
    <script>
        const token = localStorage.getItem('hris_token');
        if (!token) {
            window.location.href = window.AppConfig.getBaseUrl('/login');
        }

        const authHeaders = () => ({
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        });

        const money = (value) => Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        let currentPositionId = null;
        let currentPosition = null;

        // Auto-calculation logic
        const WORKING_DAYS_PER_MONTH = 22; // Standard working days
        const WORKING_HOURS_PER_DAY = 8;   // Standard hours per day

        // Philippine SSS Contribution Table 2024
        function calculateSSS(monthlySalary) {
            if (monthlySalary < 4250) return 180.00;
            if (monthlySalary < 4750) return 202.50;
            if (monthlySalary < 5250) return 225.00;
            if (monthlySalary < 5750) return 247.50;
            if (monthlySalary < 6250) return 270.00;
            if (monthlySalary < 6750) return 292.50;
            if (monthlySalary < 7250) return 315.00;
            if (monthlySalary < 7750) return 337.50;
            if (monthlySalary < 8250) return 360.00;
            if (monthlySalary < 8750) return 382.50;
            if (monthlySalary < 9250) return 405.00;
            if (monthlySalary < 9750) return 427.50;
            if (monthlySalary < 10250) return 450.00;
            if (monthlySalary < 10750) return 472.50;
            if (monthlySalary < 11250) return 495.00;
            if (monthlySalary < 11750) return 517.50;
            if (monthlySalary < 12250) return 540.00;
            if (monthlySalary < 12750) return 562.50;
            if (monthlySalary < 13250) return 585.00;
            if (monthlySalary < 13750) return 607.50;
            if (monthlySalary < 14250) return 630.00;
            if (monthlySalary < 14750) return 652.50;
            if (monthlySalary < 15250) return 675.00;
            if (monthlySalary < 15750) return 697.50;
            if (monthlySalary < 16250) return 720.00;
            if (monthlySalary < 16750) return 742.50;
            if (monthlySalary < 17250) return 765.00;
            if (monthlySalary < 17750) return 787.50;
            if (monthlySalary < 18250) return 810.00;
            if (monthlySalary < 18750) return 832.50;
            if (monthlySalary < 19250) return 855.00;
            if (monthlySalary < 19750) return 877.50;
            if (monthlySalary < 20250) return 900.00;
            if (monthlySalary < 20750) return 922.50;
            if (monthlySalary < 21250) return 945.00;
            if (monthlySalary < 21750) return 967.50;
            if (monthlySalary < 22250) return 990.00;
            if (monthlySalary < 22750) return 1012.50;
            if (monthlySalary < 23250) return 1035.00;
            if (monthlySalary < 23750) return 1057.50;
            if (monthlySalary < 24250) return 1080.00;
            if (monthlySalary < 24750) return 1102.50;
            return 1125.00; // Maximum contribution (salary >= 24750)
        }

        // PhilHealth Contribution 2024: 5% of monthly salary (2.5% employee share)
        function calculatePhilHealth(monthlySalary) {
            const minSalary = 10000;
            const maxSalary = 100000;
            const rate = 0.05; // 5% total, 2.5% employee share
            
            let baseSalary = monthlySalary;
            if (baseSalary < minSalary) baseSalary = minSalary;
            if (baseSalary > maxSalary) baseSalary = maxSalary;
            
            return (baseSalary * rate) / 2; // Employee share is half
        }

        // Pag-IBIG Contribution 2024
        function calculatePagIBIG(monthlySalary) {
            if (monthlySalary <= 1500) {
                return monthlySalary * 0.01; // 1%
            } else if (monthlySalary <= 5000) {
                return monthlySalary * 0.02; // 2%
            } else {
                return 100.00; // Maximum employee share
            }
        }

        // Withholding Tax 2024 (TRAIN Law - simplified)
        function calculateWithholdingTax(monthlySalary) {
            const annualSalary = monthlySalary * 12;
            let annualTax = 0;

            if (annualSalary <= 250000) {
                annualTax = 0;
            } else if (annualSalary <= 400000) {
                annualTax = (annualSalary - 250000) * 0.15;
            } else if (annualSalary <= 800000) {
                annualTax = 22500 + (annualSalary - 400000) * 0.20;
            } else if (annualSalary <= 2000000) {
                annualTax = 102500 + (annualSalary - 800000) * 0.25;
            } else if (annualSalary <= 8000000) {
                annualTax = 402500 + (annualSalary - 2000000) * 0.30;
            } else {
                annualTax = 2202500 + (annualSalary - 8000000) * 0.35;
            }

            return annualTax / 12; // Monthly tax
        }

        function calculateGovernmentDeductions(baseSalary) {
            if (!baseSalary || baseSalary <= 0) {
                document.getElementById('sss-amount').value = '0.00';
                document.getElementById('philhealth-amount').value = '0.00';
                document.getElementById('pagibig-amount').value = '0.00';
                document.getElementById('tax-amount').value = '0.00';
                return;
            }

            const sss = calculateSSS(baseSalary);
            const philhealth = calculatePhilHealth(baseSalary);
            const pagibig = calculatePagIBIG(baseSalary);
            const tax = calculateWithholdingTax(baseSalary);

            document.getElementById('sss-amount').value = sss.toFixed(2);
            document.getElementById('philhealth-amount').value = philhealth.toFixed(2);
            document.getElementById('pagibig-amount').value = pagibig.toFixed(2);
            document.getElementById('tax-amount').value = tax.toFixed(2);
        }

        function calculateFromBaseSalary() {
            const baseSalary = parseFloat(document.getElementById('base-salary').value) || 0;
            if (baseSalary > 0) {
                const dailyRate = baseSalary / WORKING_DAYS_PER_MONTH;
                const hourlyRate = dailyRate / WORKING_HOURS_PER_DAY;
                
                document.getElementById('daily-rate').value = dailyRate.toFixed(2);
                document.getElementById('hourly-rate').value = hourlyRate.toFixed(2);
                calculateGovernmentDeductions(baseSalary);
            }
        }

        function calculateFromDailyRate() {
            const dailyRate = parseFloat(document.getElementById('daily-rate').value) || 0;
            if (dailyRate > 0) {
                const baseSalary = dailyRate * WORKING_DAYS_PER_MONTH;
                const hourlyRate = dailyRate / WORKING_HOURS_PER_DAY;
                
                document.getElementById('base-salary').value = baseSalary.toFixed(2);
                document.getElementById('hourly-rate').value = hourlyRate.toFixed(2);
                calculateGovernmentDeductions(baseSalary);
            }
        }

        function calculateFromHourlyRate() {
            const hourlyRate = parseFloat(document.getElementById('hourly-rate').value) || 0;
            if (hourlyRate > 0) {
                const dailyRate = hourlyRate * WORKING_HOURS_PER_DAY;
                const baseSalary = dailyRate * WORKING_DAYS_PER_MONTH;
                
                document.getElementById('base-salary').value = baseSalary.toFixed(2);
                document.getElementById('daily-rate').value = dailyRate.toFixed(2);
                calculateGovernmentDeductions(baseSalary);
            }
        }

        // Add event listeners for auto-calculation
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('base-salary').addEventListener('input', calculateFromBaseSalary);
            document.getElementById('daily-rate').addEventListener('input', calculateFromDailyRate);
            document.getElementById('hourly-rate').addEventListener('input', calculateFromHourlyRate);
        });

        function showSuccessToast(message) {
            const toast = document.getElementById('success-toast');
            document.getElementById('success-message').textContent = message;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        function showErrorModal(message) {
            document.getElementById('error-message').textContent = message;
            document.getElementById('error-modal').classList.remove('hidden');
        }

        async function loadPositions() {
            try {
                const response = await window.AuthManager.authFetch(window.AppConfig.getApiUrl('/compensation/positions'), {
                    headers: authHeaders()
                });
                const result = await response.json();

                if (result.success && result.data) {
                    renderPositions(result.data);
                } else {
                    document.getElementById('positions-list').innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-red-400">Error loading positions</td></tr>';
                }
            } catch (error) {
                document.getElementById('positions-list').innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-red-400">Error loading positions</td></tr>';
            }
        }

        function renderPositions(positions) {
            const tbody = document.getElementById('positions-list');
            
            if (!positions || positions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No positions found. Add employees with positions first.</td></tr>';
                return;
            }

            tbody.innerHTML = positions.map(pos => {
                const hasSalary = pos.has_salary || (pos.base_salary && pos.base_salary > 0);
                const netPay = (pos.base_salary || 0) - (pos.sss_employee_share || 0) - (pos.philhealth_employee_share || 0) - (pos.pagibig_employee_share || 0) - (pos.tax_value || 0);
                
                const statusBadge = hasSalary 
                    ? '<span class="inline-block px-2 py-0.5 bg-green-600 text-white text-xs rounded mt-1">Configured</span>'
                    : '<span class="inline-block px-2 py-0.5 bg-yellow-600 text-white text-xs rounded mt-1">Not Set</span>';
                
                return `
                    <tr class="hover:bg-slate-700 ${!hasSalary ? 'bg-slate-750' : ''}">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-white">${pos.position || 'N/A'}</div>
                            ${statusBadge}
                        </td>
                        <td class="px-4 py-3">${pos.department || 'N/A'}</td>
                        <td class="px-4 py-3">${pos.payroll_type || 'Monthly'}</td>
                        <td class="px-4 py-3 text-right ${!hasSalary ? 'text-yellow-400' : ''}">₱${money(pos.base_salary)}</td>
                        <td class="px-4 py-3 text-right ${!hasSalary ? 'text-yellow-400' : 'text-green-400'}">₱${money(netPay)}</td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="editPosition('${pos.id || ''}', '${pos.position}')" class="px-3 py-1 ${hasSalary ? 'bg-blue-600 hover:bg-blue-700' : 'bg-yellow-600 hover:bg-yellow-700'} text-white rounded text-xs">
                                ${hasSalary ? 'Edit' : 'Set Salary'}
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function editPosition(positionId, positionName) {
            currentPositionId = positionId;
            currentPosition = positionName;
            document.getElementById('modal-position-name').textContent = positionName;
            document.getElementById('edit-modal').classList.remove('hidden');

            // Load current salary
            try {
                const response = await window.AuthManager.authFetch(window.AppConfig.getApiUrl(`/compensation/positions/${encodeURIComponent(positionName)}`), {
                    headers: authHeaders()
                });
                const result = await response.json();

                if (result.success && result.data) {
                    const salary = result.data;
                    currentPositionId = salary.id;
                    
                    document.getElementById('payroll-type').value = salary.payroll_type || 'Monthly';
                    document.getElementById('base-salary').value = salary.base_salary || '';
                    document.getElementById('daily-rate').value = salary.daily_rate || '';
                    document.getElementById('hourly-rate').value = salary.hourly_rate || '';
                    
                    // Calculate government deductions based on base salary
                    const baseSalary = parseFloat(salary.base_salary) || 0;
                    if (baseSalary > 0) {
                        calculateGovernmentDeductions(baseSalary);
                    } else {
                        document.getElementById('sss-amount').value = '0.00';
                        document.getElementById('philhealth-amount').value = '0.00';
                        document.getElementById('pagibig-amount').value = '0.00';
                        document.getElementById('tax-amount').value = '0.00';
                    }
                }
            } catch (error) {
                console.error('Error loading position salary:', error);
            }
        }

        document.getElementById('save-btn').addEventListener('click', async () => {
            // Show confirmation modal
            document.getElementById('confirm-modal').classList.remove('hidden');
        });

        document.getElementById('confirm-save-btn').addEventListener('click', async () => {
            // Hide confirmation modal
            document.getElementById('confirm-modal').classList.add('hidden');
            
            // Show loading modal
            document.getElementById('loading-modal').classList.remove('hidden');

            const data = {
                position: currentPosition,
                payroll_type: document.getElementById('payroll-type').value,
                base_salary: parseFloat(document.getElementById('base-salary').value) || 0,
                daily_rate: parseFloat(document.getElementById('daily-rate').value) || 0,
                hourly_rate: parseFloat(document.getElementById('hourly-rate').value) || 0,
                sss_employee_share: parseFloat(document.getElementById('sss-amount').value) || 0,
                philhealth_employee_share: parseFloat(document.getElementById('philhealth-amount').value) || 0,
                pagibig_employee_share: parseFloat(document.getElementById('pagibig-amount').value) || 0,
                tax_value: parseFloat(document.getElementById('tax-amount').value) || 0,
                standard_work_hours_per_day: 8.00
            };

            try {
                const url = currentPositionId 
                    ? `/compensation/positions/${currentPositionId}`
                    : '/compensation/positions';
                
                const response = await window.AuthManager.authFetch(window.AppConfig.getApiUrl(url), {
                    method: currentPositionId ? 'PUT' : 'POST',
                    headers: authHeaders(),
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                // Hide loading modal
                document.getElementById('loading-modal').classList.add('hidden');

                if (result.success) {
                    showSuccessToast('Position salary updated successfully!');
                    document.getElementById('edit-modal').classList.add('hidden');
                    loadPositions();
                } else {
                    showErrorModal(result.message || 'Failed to update position salary');
                }
            } catch (error) {
                // Hide loading modal
                document.getElementById('loading-modal').classList.add('hidden');
                showErrorModal('Network error: ' + error.message);
            }
        });

        document.getElementById('confirm-cancel-btn').addEventListener('click', () => {
            document.getElementById('confirm-modal').classList.add('hidden');
        });

        document.getElementById('close-modal').addEventListener('click', () => {
            document.getElementById('edit-modal').classList.add('hidden');
        });

        document.getElementById('cancel-btn').addEventListener('click', () => {
            document.getElementById('edit-modal').classList.add('hidden');
        });

        document.getElementById('error-close-btn').addEventListener('click', () => {
            document.getElementById('error-modal').classList.add('hidden');
        });

        document.getElementById('refresh-btn').addEventListener('click', loadPositions);

        document.getElementById('logout-btn').addEventListener('click', () => {
            window.AuthManager.logout();
        });

        // Load positions on page load
        loadPositions();
    </script>
</body>
</html>
