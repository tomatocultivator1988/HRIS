/**
 * Loading Skeletons - Reusable skeleton components
 */

const LoadingSkeletons = {
    /**
     * Summary card skeleton (for dashboard cards)
     */
    summaryCard() {
        return `
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="skeleton skeleton-text" style="width: 60%;"></div>
                        <div class="skeleton skeleton-text-lg" style="width: 40%; margin-top: 0.5rem;"></div>
                    </div>
                    <div class="skeleton skeleton-avatar"></div>
                </div>
            </div>
        `;
    },

    /**
     * Chart skeleton
     */
    chart() {
        return `
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <div class="skeleton skeleton-text-lg" style="width: 40%; margin-bottom: 1rem;"></div>
                <div class="skeleton skeleton-chart"></div>
            </div>
        `;
    },

    /**
     * Table skeleton
     */
    table(rows = 5, columns = 6) {
        const headerCells = Array(columns).fill('<th class="px-6 py-4"><div class="skeleton skeleton-text"></div></th>').join('');
        const bodyCells = Array(columns).fill('<td class="px-6 py-4"><div class="skeleton skeleton-text"></div></td>').join('');
        const bodyRows = Array(rows).fill(`<tr class="border-b border-slate-700">${bodyCells}</tr>`).join('');
        
        return `
            <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
                <div class="p-6 border-b border-slate-700">
                    <div class="skeleton skeleton-text-lg" style="width: 30%;"></div>
                    <div class="skeleton skeleton-text" style="width: 50%; margin-top: 0.5rem;"></div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-700/50">
                            <tr>${headerCells}</tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            ${bodyRows}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    },

    /**
     * List item skeleton
     */
    listItem() {
        return `
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-4 mb-3">
                <div class="flex items-center space-x-4">
                    <div class="skeleton skeleton-avatar"></div>
                    <div class="flex-1">
                        <div class="skeleton skeleton-text" style="width: 60%;"></div>
                        <div class="skeleton skeleton-text" style="width: 40%; margin-top: 0.5rem;"></div>
                    </div>
                </div>
            </div>
        `;
    },

    /**
     * Report page skeleton (4 cards + 2 charts + table)
     */
    reportPage() {
        return `
            <!-- Summary Cards Skeleton -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                ${this.summaryCard()}
                ${this.summaryCard()}
                ${this.summaryCard()}
                ${this.summaryCard()}
            </div>

            <!-- Charts Skeleton -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                ${this.chart()}
                ${this.chart()}
            </div>

            <!-- Table Skeleton -->
            ${this.table(8, 6)}
        `;
    },

    /**
     * Dashboard skeleton
     */
    dashboard() {
        return `
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                ${this.summaryCard()}
                ${this.summaryCard()}
                ${this.summaryCard()}
                ${this.summaryCard()}
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                ${this.chart()}
                ${this.chart()}
            </div>

            <!-- Recent Activity -->
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                <div class="skeleton skeleton-text-lg" style="width: 30%; margin-bottom: 1rem;"></div>
                ${this.listItem()}
                ${this.listItem()}
                ${this.listItem()}
            </div>
        `;
    },

    /**
     * Attendance table skeleton
     */
    attendanceTable() {
        return this.table(10, 6);
    },

    /**
     * Leave requests skeleton
     */
    leaveRequests() {
        return `
            <div class="space-y-4">
                ${this.listItem()}
                ${this.listItem()}
                ${this.listItem()}
                ${this.listItem()}
                ${this.listItem()}
            </div>
        `;
    },

    /**
     * Employee list skeleton
     */
    employeeList() {
        return this.table(12, 5);
    },

    /**
     * Show skeleton in element
     */
    show(elementId, type = 'table', ...args) {
        const element = document.getElementById(elementId);
        if (!element) return;

        let skeleton = '';
        switch (type) {
            case 'summaryCard':
                skeleton = this.summaryCard();
                break;
            case 'chart':
                skeleton = this.chart();
                break;
            case 'table':
                skeleton = this.table(...args);
                break;
            case 'listItem':
                skeleton = this.listItem();
                break;
            case 'reportPage':
                skeleton = this.reportPage();
                break;
            case 'dashboard':
                skeleton = this.dashboard();
                break;
            case 'attendanceTable':
                skeleton = this.attendanceTable();
                break;
            case 'leaveRequests':
                skeleton = this.leaveRequests();
                break;
            case 'employeeList':
                skeleton = this.employeeList();
                break;
            default:
                skeleton = this.table();
        }

        element.innerHTML = skeleton;
    },

    /**
     * Hide skeleton and show content
     */
    hide(elementId, content) {
        const element = document.getElementById(elementId);
        if (!element) return;
        element.innerHTML = content;
    }
};

// Make available globally
window.LoadingSkeletons = LoadingSkeletons;
