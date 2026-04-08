/**
 * Employee Analytics - Charts and Data Management
 */

let charts = {};
let reportData = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadReports();
});

function showLoadingSkeleton() {
    const content = document.getElementById('report-content');
    if (content && window.LoadingSkeletons) {
        content.innerHTML = window.LoadingSkeletons.reportPage();
    }
}

function initializeCharts() {
    // Check if chart elements exist
    const deptCanvas = document.getElementById('departmentChart');
    const statusCanvas = document.getElementById('statusChart');
    
    if (!deptCanvas || !statusCanvas) {
        console.error('Chart canvas elements not found. Skipping chart initialization.');
        return;
    }
    
    // Department Chart (Bar)
    const deptCtx = deptCanvas.getContext('2d');
    charts.department = new Chart(deptCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Employees',
                data: [],
                backgroundColor: 'rgb(139, 92, 246)'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#cbd5e1' } } },
            scales: {
                y: { ticks: { color: '#cbd5e1' }, grid: { color: '#334155' } },
                x: { ticks: { color: '#cbd5e1' }, grid: { color: '#334155' } }
            }
        }
    });

    // Status Chart (Pie)
    const statusCtx = statusCanvas.getContext('2d');
    charts.status = new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: ['rgb(34, 197, 94)', 'rgb(234, 179, 8)', 'rgb(239, 68, 68)', 'rgb(59, 130, 246)']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#cbd5e1' } } }
        }
    });
}

async function loadReports() {
    try {
        const response = await fetch(AppConfig.getApiUrl('/reports/headcount'), {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('hris_token')}` }
        });
        
        const result = await response.json();
        
        if (result.success) {
            reportData = result.data.report;
            
            // Restore actual content
            restoreContent();
            
            updateSummaryCards(reportData.summary);
            updateCharts(reportData);
            updateTable(reportData.employees);
        } else {
            restoreContent();
            showToast(result.message || 'Failed to load report', 'error');
        }
    } catch (error) {
        console.error('Error loading report:', error);
        restoreContent();
        showToast('Error loading report data', 'error');
    }
}

// Restore actual content structure
function restoreContent() {
    const content = document.getElementById('report-content');
    if (!content) return;
    
    content.innerHTML = `
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-slate-400 text-sm">Total Employees</p><p class="text-3xl font-bold text-white mt-2" id="total-employees">0</p></div>
                    <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
                </div>
            </div>
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-slate-400 text-sm">Departments</p><p class="text-3xl font-bold text-white mt-2" id="total-departments">0</p></div>
                    <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>
                </div>
            </div>
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-slate-400 text-sm">Positions</p><p class="text-3xl font-bold text-white mt-2" id="total-positions">0</p></div>
                    <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></div>
                </div>
            </div>
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-slate-400 text-sm">Active Rate</p><p class="text-3xl font-bold text-white mt-2" id="active-rate">0%</p></div>
                    <div class="w-12 h-12 bg-orange-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Employees by Department</h3>
                <canvas id="departmentChart"></canvas>
            </div>
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Employment Status</h3>
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Employee List</h3>
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
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;
    
    // Reinitialize charts after restoring content
    initializeCharts();
}

function updateSummaryCards(summary) {
    const totalEmployees = document.getElementById('total-employees');
    totalEmployees.textContent = summary.total_employees || 0;
    totalEmployees.classList.add('loaded');
    
    const deptCount = Object.keys(summary.by_department || {}).length;
    const totalDepartments = document.getElementById('total-departments');
    totalDepartments.textContent = deptCount;
    totalDepartments.classList.add('loaded');
    
    const posCount = Object.keys(summary.by_position || {}).length;
    const totalPositions = document.getElementById('total-positions');
    totalPositions.textContent = posCount;
    totalPositions.classList.add('loaded');
    
    // Calculate active percentage
    const activeCount = summary.total_employees || 0;
    const activeRate = document.getElementById('active-rate');
    activeRate.textContent = activeCount > 0 ? '100%' : '0%';
    activeRate.classList.add('loaded');
}

function updateCharts(data) {
    // Department chart
    const deptData = data.summary.by_department || {};
    charts.department.data.labels = Object.keys(deptData);
    charts.department.data.datasets[0].data = Object.values(deptData);
    charts.department.update();
    
    // Status chart
    const statusData = data.summary.by_employment_status || {};
    charts.status.data.labels = Object.keys(statusData);
    charts.status.data.datasets[0].data = Object.values(statusData);
    charts.status.update();
}

function updateTable(employees) {
    const tbody = document.getElementById('records-table');
    if (!employees || employees.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No employee records found</td></tr>';
        return;
    }
    
    tbody.innerHTML = employees.slice(0, 100).map(emp => `
        <tr class="hover:bg-slate-700/50">
            <td class="px-4 py-3 text-slate-300">${emp.employee_id || 'N/A'}</td>
            <td class="px-4 py-3 text-slate-300">${emp.first_name} ${emp.last_name}</td>
            <td class="px-4 py-3 text-slate-300">${emp.department || 'N/A'}</td>
            <td class="px-4 py-3 text-slate-300">${emp.position || 'N/A'}</td>
            <td class="px-4 py-3"><span class="px-2 py-1 rounded text-xs ${getStatusClass(emp.employment_status)}">${emp.employment_status || 'N/A'}</span></td>
        </tr>
    `).join('');
}

function getStatusClass(status) {
    const statusClasses = {
        'Regular': 'bg-green-500/20 text-green-400 border-green-500/30',
        'Probationary': 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
        'Contractual': 'bg-blue-500/20 text-blue-400 border-blue-500/30',
        'Part-time': 'bg-purple-500/20 text-purple-400 border-purple-500/30'
    };
    return statusClasses[status] || 'bg-slate-500/20 text-slate-400 border-slate-500/30';
}

function showToast(message, type = 'info') {
    console.log(`[${type.toUpperCase()}] ${message}`);
    // Don't call window.showToast to avoid infinite recursion
    // Just log to console for now
}
