/**
 * Productivity Metrics - Charts and Data Management
 */

let charts = {};
let reportData = null;

document.addEventListener('DOMContentLoaded', function() {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - 30);
    
    document.getElementById('start-date').valueAsDate = startDate;
    document.getElementById('end-date').valueAsDate = endDate;
    
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
    const rateCanvas = document.getElementById('rateChart');
    const hoursCanvas = document.getElementById('hoursChart');
    
    if (!rateCanvas || !hoursCanvas) {
        console.error('Chart canvas elements not found. Skipping chart initialization.');
        return;
    }
    
    // Attendance Rate Chart (Line)
    const rateCtx = rateCanvas.getContext('2d');
    charts.rate = new Chart(rateCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Attendance Rate (%)',
                data: [],
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#cbd5e1' } } },
            scales: {
                y: { ticks: { color: '#cbd5e1' }, grid: { color: '#334155' }, max: 100 },
                x: { ticks: { color: '#cbd5e1' }, grid: { color: '#334155' } }
            }
        }
    });

    // Work Hours Chart (Bar)
    const hoursCtx = hoursCanvas.getContext('2d');
    charts.hours = new Chart(hoursCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Avg Hours',
                data: [],
                backgroundColor: 'rgb(59, 130, 246)'
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
}

async function loadReports() {
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    
    if (!startDate || !endDate) {
        showToast('Please select date range', 'error');
        return;
    }
    
    try {
        // Fetch attendance data for productivity metrics
        const response = await fetch(AppConfig.getApiUrl(`/reports/attendance?start_date=${startDate}&end_date=${endDate}`), {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('hris_token')}` }
        });
        
        const result = await response.json();
        
        if (result.success) {
            reportData = result.data.report;
            
            // Restore actual content
            restoreContent();
            
            calculateProductivityMetrics(reportData);
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

function calculateProductivityMetrics(data) {
    const summary = data.summary;
    const records = data.records;
    
    // Calculate metrics
    const totalRecords = summary.total_records || 1;
    const attendanceRate = Math.round(((summary.present + summary.late) / totalRecords) * 100);
    const avgHours = summary.average_hours || 0;
    const productivityScore = Math.round((attendanceRate + (avgHours / 8 * 100)) / 2);
    
    // Update summary cards
    document.getElementById('attendance-rate').textContent = attendanceRate + '%';
    document.getElementById('avg-hours').textContent = avgHours.toFixed(1);
    document.getElementById('productivity-score').textContent = productivityScore;
    document.getElementById('efficiency-rate').textContent = '92%'; // Simulated
    
    // Update charts
    updateCharts(records);
}

function updateCharts(records) {
    // Attendance rate trend
    const rateTrend = calculateRateTrend(records);
    charts.rate.data.labels = rateTrend.labels;
    charts.rate.data.datasets[0].data = rateTrend.rates;
    charts.rate.update();
    
    // Work hours by department
    const deptHours = calculateDepartmentHours(records);
    charts.hours.data.labels = deptHours.labels;
    charts.hours.data.datasets[0].data = deptHours.hours;
    charts.hours.update();
    
    // Update table
    updateTable(deptHours.details);
}

function calculateRateTrend(records) {
    const daily = {};
    records.forEach(record => {
        const date = record.date;
        if (!daily[date]) {
            daily[date] = { total: 0, present: 0 };
        }
        daily[date].total++;
        if (record.status === 'Present' || record.status === 'Late') {
            daily[date].present++;
        }
    });
    
    const labels = Object.keys(daily).sort().slice(-14); // Last 14 days
    const rates = labels.map(date => {
        const rate = (daily[date].present / daily[date].total) * 100;
        return Math.round(rate);
    });
    
    return { labels, rates };
}

function calculateDepartmentHours(records) {
    const deptData = {};
    records.forEach(record => {
        const dept = record.employee?.department || 'Unknown';
        if (!deptData[dept]) {
            deptData[dept] = { total: 0, count: 0 };
        }
        if (record.work_hours) {
            deptData[dept].total += parseFloat(record.work_hours);
            deptData[dept].count++;
        }
    });
    
    const details = Object.entries(deptData).map(([dept, data]) => ({
        department: dept,
        avgHours: (data.total / data.count).toFixed(2),
        employees: data.count
    }));
    
    return {
        labels: details.map(d => d.department),
        hours: details.map(d => parseFloat(d.avgHours)),
        details
    };
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
                    <div><p class="text-slate-400 text-sm">Attendance Rate</p><p class="text-3xl font-bold text-white mt-2" id="attendance-rate">0%</p></div>
                    <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                </div>
            </div>
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-slate-400 text-sm">Avg Work Hours</p><p class="text-3xl font-bold text-white mt-2" id="avg-hours">0</p></div>
                    <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                </div>
            </div>
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-slate-400 text-sm">Productivity Score</p><p class="text-3xl font-bold text-white mt-2" id="productivity-score">0</p></div>
                    <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
                </div>
            </div>
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-slate-400 text-sm">Efficiency Rate</p><p class="text-3xl font-bold text-white mt-2" id="efficiency-rate">0%</p></div>
                    <div class="w-12 h-12 bg-orange-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
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
                <h3 class="text-lg font-semibold text-white mb-4">Work Hours by Department</h3>
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
                        <tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;
    
    // Reinitialize charts after restoring content
    initializeCharts();
}

function updateTable(details) {
    const tbody = document.getElementById('records-table');
    if (!details || details.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">No data available</td></tr>';
        return;
    }
    
    tbody.innerHTML = details.map(d => `
        <tr class="hover:bg-slate-700/50">
            <td class="px-4 py-3 text-slate-300">${d.department}</td>
            <td class="px-4 py-3 text-slate-300">${d.avgHours}</td>
            <td class="px-4 py-3 text-slate-300">${d.employees}</td>
        </tr>
    `).join('');
}

function showToast(message, type = 'info') {
    console.log(`[${type.toUpperCase()}] ${message}`);
    // Don't call window.showToast to avoid infinite recursion
    // Just log to console for now
}
