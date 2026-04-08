/**
 * Chart.js Visualization Components for HRIS MVP
 * Provides reusable chart components for dashboard analytics
 * Requirements: 1.5, 1.6, 1.7, 11.3
 */

class DashboardCharts {
    constructor() {
        this.charts = {};
        this.defaultColors = [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
            '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16'
        ];
        
        // Dark mode chart defaults
        Chart.defaults.color = '#94a3b8'; // slate-400
        Chart.defaults.borderColor = '#334155'; // slate-700
    }

    /**
     * Update department headcount pie chart
     * @param {Object} departmentData - Department headcount data {dept: count}
     */
    updateDepartmentChart(departmentData) {
        const canvas = document.getElementById('department-chart');
        if (!canvas) return;

        // Destroy existing chart
        if (this.charts.department) {
            this.charts.department.destroy();
        }

        // Prepare data
        const labels = Object.keys(departmentData);
        const data = Object.values(departmentData);

        // Create pie chart
        this.charts.department = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: this.defaultColors.slice(0, labels.length),
                    borderWidth: 3,
                    borderColor: '#1e293b' // slate-800
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 10,
                            font: {
                                size: 12,
                                family: "'Inter', sans-serif"
                            },
                            color: '#cbd5e1' // slate-300
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#cbd5e1',
                        borderColor: '#475569',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Update attendance trend line chart
     * @param {Array} trendData - Array of {date, present, late, absent}
     */
    updateAttendanceTrend(trendData) {
        const canvas = document.getElementById('attendance-trend-chart');
        if (!canvas) return;

        // Destroy existing chart
        if (this.charts.attendanceTrend) {
            this.charts.attendanceTrend.destroy();
        }

        // Prepare data
        const labels = trendData.map(d => {
            const date = new Date(d.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });

        const presentData = trendData.map(d => d.present);
        const lateData = trendData.map(d => d.late);
        const absentData = trendData.map(d => d.absent);

        // Create line chart
        this.charts.attendanceTrend = new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Present',
                        data: presentData,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#10B981',
                        pointBorderColor: '#1e293b',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'Late',
                        data: lateData,
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.2)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#F59E0B',
                        pointBorderColor: '#1e293b',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'Absent',
                        data: absentData,
                        borderColor: '#EF4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#EF4444',
                        pointBorderColor: '#1e293b',
                        pointBorderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 10,
                            font: {
                                size: 12,
                                family: "'Inter', sans-serif"
                            },
                            color: '#cbd5e1',
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#cbd5e1',
                        borderColor: '#475569',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#94a3b8',
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: '#334155',
                            drawBorder: false
                        },
                        title: {
                            display: true,
                            text: 'Employees',
                            color: '#cbd5e1',
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    },
                    x: {
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: '#334155',
                            drawBorder: false
                        },
                        title: {
                            display: true,
                            text: 'Date',
                            color: '#cbd5e1',
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Update leave status breakdown chart
     * @param {Object} leaveData - Leave status data {pending, approved, denied}
     */
    updateLeaveStatusChart(leaveData) {
        const canvas = document.getElementById('leave-status-chart');
        if (!canvas) return;

        // Destroy existing chart
        if (this.charts.leaveStatus) {
            this.charts.leaveStatus.destroy();
        }

        // Prepare data
        const data = [
            leaveData.pending || 0,
            leaveData.approved || 0,
            leaveData.denied || 0
        ];

        // Create doughnut chart
        this.charts.leaveStatus = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Approved', 'Denied'],
                datasets: [{
                    data: data,
                    backgroundColor: ['#F59E0B', '#10B981', '#EF4444'],
                    borderWidth: 3,
                    borderColor: '#1e293b'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 13,
                                family: "'Inter', sans-serif"
                            },
                            color: '#cbd5e1'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#cbd5e1',
                        borderColor: '#475569',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Destroy all charts
     */
    destroyAll() {
        Object.values(this.charts).forEach(chart => {
            if (chart) chart.destroy();
        });
        this.charts = {};
    }

    /**
     * Resize all charts (useful for responsive layouts)
     */
    resizeAll() {
        Object.values(this.charts).forEach(chart => {
            if (chart) chart.resize();
        });
    }
}

// Create global instance
window.DashboardCharts = new DashboardCharts();

// Handle window resize
window.addEventListener('resize', () => {
    if (window.DashboardCharts) {
        window.DashboardCharts.resizeAll();
    }
});
