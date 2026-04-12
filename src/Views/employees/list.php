<?php
/**
 * Employee List View Template
 * 
 * Displays a list of employees with search, filtering, and pagination
 */

$employees = $employees ?? [];
$pagination = $pagination ?? [];
$filters = $filters ?? [];
$departments = $departments ?? [];
?>

<div class="px-4 py-6 sm:px-0">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Employee Management</h1>
            <p class="mt-2 text-sm text-gray-600">Manage employee records and information</p>
        </div>
        <button id="addEmployeeBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
            Add Employee
        </button>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="bg-white shadow rounded-lg p-6 mb-6">
    <form id="filterForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label for="searchInput" class="block text-sm font-medium text-gray-700">Search</label>
            <input type="text" id="searchInput" name="search" placeholder="Search employees..." 
                   value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="departmentFilter" class="block text-sm font-medium text-gray-700">Department</label>
            <select id="departmentFilter" name="department" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Departments</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= htmlspecialchars($dept) ?>" <?= ($filters['department'] ?? '') === $dept ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="statusFilter" class="block text-sm font-medium text-gray-700">Status</label>
            <select id="statusFilter" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="" <?= ($filters['status'] ?? '') === '' ? 'selected' : '' ?>>Active Only</option>
                <option value="all" <?= ($filters['status'] ?? '') === 'all' ? 'selected' : '' ?>>All Employees</option>
                <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
            </select>
        </div>
        <div class="flex items-end space-x-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                Filter
            </button>
            <button type="button" id="clearFiltersBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                Clear
            </button>
        </div>
    </form>
</div>

<!-- Employee Table -->
<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Employees</h3>
        <p class="mt-1 text-sm text-gray-600">
            <?= count($employees) ?> employees found
            <?php if (!empty($pagination['total'])): ?>
                (<?= $pagination['total'] ?> total)
            <?php endif; ?>
        </p>
    </div>
    
    <?php if (empty($employees)): ?>
        <!-- Empty state -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No employees found</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by adding a new employee.</p>
            <div class="mt-6">
                <button id="addEmployeeEmptyBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                    Add Employee
                </button>
            </div>
        </div>
    <?php else: ?>
        <!-- Employee table -->
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Employee ID
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Department
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Position
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($employees as $employee): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($employee['employee_id']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">
                                            <?= strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($employee['full_name']) ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($employee['work_email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= htmlspecialchars($employee['department'] ?? '-') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= htmlspecialchars($employee['position'] ?? '-') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $statusClass = 'bg-gray-100 text-gray-800';
                            $statusText = 'Inactive';
                            
                            if ($employee['is_active']) {
                                $statusText = $employee['employment_status'];
                                switch ($employee['employment_status']) {
                                    case 'Regular':
                                        $statusClass = 'bg-green-100 text-green-800';
                                        break;
                                    case 'Probationary':
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                        break;
                                    case 'Contractual':
                                        $statusClass = 'bg-blue-100 text-blue-800';
                                        break;
                                    case 'Part-time':
                                        $statusClass = 'bg-purple-100 text-purple-800';
                                        break;
                                }
                            }
                            ?>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                                <?= htmlspecialchars($statusText) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="/employees/<?= htmlspecialchars($employee['id']) ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                <a href="/employees/<?= htmlspecialchars($employee['id']) ?>/edit" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <button onclick="view201Files('<?= htmlspecialchars($employee['id']) ?>', '<?= htmlspecialchars($employee['full_name']) ?>')" 
                                        class="text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-2 py-1 rounded">
                                    201 Files
                                </button>
                                <?php if ($employee['is_active']): ?>
                                    <button onclick="deactivateEmployee('<?= htmlspecialchars($employee['id']) ?>')" 
                                            class="text-red-600 hover:text-red-900">Deactivate</button>
                                <?php else: ?>
                                    <button onclick="activateEmployee('<?= htmlspecialchars($employee['id']) ?>')" 
                                            class="text-green-600 hover:text-green-900">Activate</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="?page=<?= $pagination['current_page'] - 1 ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        <?php if ($pagination['has_more']): ?>
                            <a href="?page=<?= $pagination['current_page'] + 1 ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing <span class="font-medium"><?= $pagination['offset'] + 1 ?></span> to 
                                <span class="font-medium"><?= min($pagination['offset'] + $pagination['limit'], $pagination['total']) ?></span> of 
                                <span class="font-medium"><?= $pagination['total'] ?></span> results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <?php if ($i === $pagination['current_page']): ?>
                                        <span class="relative inline-flex items-center px-4 py-2 border border-blue-500 bg-blue-50 text-sm font-medium text-blue-600">
                                            <?= $i ?>
                                        </span>
                                    <?php else: ?>
                                        <a href="?page=<?= $i ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <?= $i ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Confirmation Modal -->
<div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-yellow-100 rounded-full mb-4">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 id="confirm-title" class="text-xl font-bold text-gray-900 text-center mb-2"></h3>
            <p id="confirm-message" class="text-gray-600 text-center mb-6"></p>
        </div>
        <div class="bg-gray-50 px-6 py-4 flex space-x-3 rounded-b-xl">
            <button onclick="closeConfirmModal()" class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg transition-all">
                Cancel
            </button>
            <button id="confirm-action-btn" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all">
                Confirm
            </button>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loading-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mb-4"></div>
        <p id="loading-message" class="text-gray-700 text-lg font-semibold">Processing...</p>
    </div>
</div>

<!-- Employee Creation/Edit Modal -->
<div id="employeeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Add New Employee</h3>
                <button id="closeModalBtn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="employeeForm" class="space-y-4">
                <input type="hidden" id="employeeId" name="id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID *</label>
                        <input type="text" id="employee_id" name="employee_id" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div id="employee_id-error" class="form-error mt-1 text-sm text-red-600 hidden"></div>
                    </div>
                    
                    <div>
                        <label for="employment_status" class="block text-sm font-medium text-gray-700">Employment Status *</label>
                        <select id="employment_status" name="employment_status" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Status</option>
                            <option value="Regular">Regular</option>
                            <option value="Probationary">Probationary</option>
                            <option value="Contractual">Contractual</option>
                            <option value="Part-time">Part-time</option>
                        </select>
                        <div id="employment_status-error" class="form-error mt-1 text-sm text-red-600 hidden"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div id="first_name-error" class="form-error mt-1 text-sm text-red-600 hidden"></div>
                    </div>
                    
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div id="last_name-error" class="form-error mt-1 text-sm text-red-600 hidden"></div>
                    </div>
                </div>

                <div>
                    <label for="work_email" class="block text-sm font-medium text-gray-700">Work Email *</label>
                    <input type="email" id="work_email" name="work_email" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <div id="work_email-error" class="form-error mt-1 text-sm text-red-600 hidden"></div>
                </div>

                <div>
                    <label for="mobile_number" class="block text-sm font-medium text-gray-700">Mobile Number</label>
                    <input type="tel" id="mobile_number" name="mobile_number"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <div id="mobile_number-error" class="form-error mt-1 text-sm text-red-600 hidden"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700">Department *</label>
                        <input type="text" id="department" name="department" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div id="department-error" class="form-error mt-1 text-sm text-red-600 hidden"></div>
                    </div>
                    
                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700">Position *</label>
                        <input type="text" id="position" name="position" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div id="position-error" class="form-error mt-1 text-sm text-red-600 hidden"></div>
                    </div>
                </div>

                <div>
                    <label for="date_hired" class="block text-sm font-medium text-gray-700">Date Hired</label>
                    <input type="date" id="date_hired" name="date_hired"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <div id="date_hired-error" class="form-error mt-1 text-sm text-red-600 hidden"></div>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancelBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md">
                        Cancel
                    </button>
                    <button type="submit" id="saveBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                        Save Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 201 Files Modal -->
<div id="documents-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-slate-800">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-700">
                <div>
                    <h3 class="text-xl font-medium text-white" id="documents-modal-title">Employee 201 Files</h3>
                    <p class="text-sm text-slate-400 mt-1" id="documents-employee-name"></p>
                </div>
                <button id="close-documents-modal" class="text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Filter and Stats Section -->
            <div class="mb-4 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <label for="document-type-filter" class="text-sm font-medium text-slate-300">Filter by Type:</label>
                    <select id="document-type-filter" class="bg-slate-700 border-slate-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        <option value="">All Documents</option>
                        <option value="Resume">Resume</option>
                        <option value="Birth Certificate">Birth Certificate</option>
                        <option value="TIN">TIN</option>
                        <option value="SSS">SSS</option>
                        <option value="PhilHealth">PhilHealth</option>
                        <option value="Pag-IBIG">Pag-IBIG</option>
                        <option value="NBI Clearance">NBI Clearance</option>
                        <option value="Medical Certificate">Medical Certificate</option>
                        <option value="Diploma">Diploma</option>
                        <option value="Transcript">Transcript</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="text-sm text-slate-400">
                    <span id="documents-count">0 documents</span> • 
                    <span id="documents-size">0 MB</span>
                </div>
            </div>

            <!-- Documents Table -->
            <div class="bg-slate-900 rounded-lg overflow-hidden border border-slate-700">
                <div id="documents-list-container" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-700">
                        <thead class="bg-slate-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Filename</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Size</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Upload Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Verified</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="documents-table-body" class="bg-slate-900 divide-y divide-slate-700">
                            <!-- Documents will be inserted here -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty State -->
                <div id="documents-empty-state" class="text-center py-12 hidden">
                    <svg class="mx-auto h-12 w-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-400">No documents found</h3>
                    <p class="mt-1 text-sm text-slate-500">This employee hasn't uploaded any documents yet.</p>
                </div>
            </div>

            <!-- Close Button -->
            <div class="mt-6 flex justify-end">
                <button id="close-documents-modal-btn" class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-2 rounded-md">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/validation.js"></script>
<script>
    // Employee management functionality
    class EmployeeManager {
        constructor() {
            this.isEditing = false;
            this.editingEmployeeId = null;
            this.init();
        }
        
        init() {
            this.setupEventListeners();
            this.setupFormValidation();
        }
        
        setupEventListeners() {
            // Modal events
            document.getElementById('addEmployeeBtn')?.addEventListener('click', () => this.openModal());
            document.getElementById('addEmployeeEmptyBtn')?.addEventListener('click', () => this.openModal());
            document.getElementById('closeModalBtn')?.addEventListener('click', () => this.closeModal());
            document.getElementById('cancelBtn')?.addEventListener('click', () => this.closeModal());
            
            // Form submission
            document.getElementById('employeeForm')?.addEventListener('submit', (e) => this.handleFormSubmit(e));
            
            // Filter form
            document.getElementById('filterForm')?.addEventListener('submit', (e) => this.handleFilterSubmit(e));
            document.getElementById('clearFiltersBtn')?.addEventListener('click', () => this.clearFilters());
            
            // Close modal on outside click
            document.getElementById('employeeModal')?.addEventListener('click', (e) => {
                if (e.target.id === 'employeeModal') {
                    this.closeModal();
                }
            });
        }
        
        setupFormValidation() {
            // Basic validation setup - would use ValidationRules if available
            const form = document.getElementById('employeeForm');
            if (form) {
                // Add validation logic here
            }
        }
        
        openModal(employee = null) {
            this.isEditing = !!employee;
            this.editingEmployeeId = employee?.id || null;
            
            const modal = document.getElementById('employeeModal');
            const title = document.getElementById('modalTitle');
            const form = document.getElementById('employeeForm');
            
            if (this.isEditing) {
                title.textContent = 'Edit Employee';
                this.populateForm(employee);
            } else {
                title.textContent = 'Add New Employee';
                form.reset();
            }
            
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        
        closeModal() {
            const modal = document.getElementById('employeeModal');
            const form = document.getElementById('employeeForm');
            
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            
            form.reset();
            this.clearFormErrors();
            this.isEditing = false;
            this.editingEmployeeId = null;
        }
        
        populateForm(employee) {
            const fields = ['employee_id', 'first_name', 'last_name', 'work_email', 
                          'mobile_number', 'department', 'position', 'employment_status', 'date_hired'];
            
            fields.forEach(field => {
                const input = document.getElementById(field);
                if (input && employee[field] !== undefined) {
                    input.value = employee[field] || '';
                }
            });
            
            document.getElementById('employeeId').value = employee.id;
        }
        
        async handleFormSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            const actionText = this.isEditing ? 'update' : 'create';
            const employeeName = `${data.first_name || ''} ${data.last_name || ''}`.trim() || 'this employee';
            
            showConfirm(
                this.isEditing ? 'Update Employee?' : 'Create Employee?',
                `Are you sure you want to ${actionText} ${employeeName}?`,
                async () => {
                    try {
                        this.showFormLoading();
                        showLoading(this.isEditing ? 'Updating employee...' : 'Creating employee...');
                        
                        let response;
                        if (this.isEditing) {
                            response = await window.API.put(`employees/${this.editingEmployeeId}`, data);
                        } else {
                            response = await window.API.post('employees', data);
                        }
                        
                        hideLoading();
                        
                        if (response.success) {
                            this.showSuccess(this.isEditing ? 'Employee updated successfully' : 'Employee created successfully');
                            this.closeModal();
                            window.location.reload(); // Refresh the page to show updated data
                        } else {
                            this.handleFormErrors(response.errors || {});
                            this.showError(response.message || 'Failed to save employee');
                        }
                    } catch (error) {
                        hideLoading();
                        console.error('Error saving employee:', error);
                        this.showError('Failed to save employee. Please try again.');
                    } finally {
                        this.hideFormLoading();
                    }
                }
            );
        }
        
        handleFilterSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const params = new URLSearchParams();
            
            for (const [key, value] of formData.entries()) {
                if (value) {
                    params.append(key, value);
                }
            }
            
            window.location.href = `${window.location.pathname}?${params.toString()}`;
        }
        
        clearFilters() {
            window.location.href = window.location.pathname;
        }
        
        handleFormErrors(errors) {
            this.clearFormErrors();
            
            Object.keys(errors).forEach(field => {
                const errorElement = document.getElementById(`${field}-error`);
                if (errorElement) {
                    errorElement.textContent = errors[field];
                    errorElement.classList.remove('hidden');
                }
            });
        }
        
        clearFormErrors() {
            document.querySelectorAll('.form-error').forEach(element => {
                element.textContent = '';
                element.classList.add('hidden');
            });
        }
        
        showFormLoading() {
            const saveBtn = document.getElementById('saveBtn');
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
        }
        
        hideFormLoading() {
            const saveBtn = document.getElementById('saveBtn');
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Employee';
        }
        
        showSuccess(message) {
            // Simple alert for now - could be enhanced with toast notifications
            alert(message);
        }
        
        showError(message) {
            // Simple alert for now - could be enhanced with toast notifications
            alert(message);
        }
    }
    
    // Confirmation modal functions
    let confirmCallback = null;
    
    function showConfirm(title, message, callback) {
        document.getElementById('confirm-title').textContent = title;
        document.getElementById('confirm-message').textContent = message;
        confirmCallback = callback;
        document.getElementById('confirm-modal').classList.remove('hidden');
    }
    
    function closeConfirmModal() {
        document.getElementById('confirm-modal').classList.add('hidden');
        confirmCallback = null;
    }
    
    document.getElementById('confirm-action-btn').addEventListener('click', function() {
        if (confirmCallback) {
            confirmCallback();
        }
        closeConfirmModal();
    });
    
    function showLoading(message = 'Processing...') {
        document.getElementById('loading-message').textContent = message;
        document.getElementById('loading-modal').classList.remove('hidden');
    }
    
    function hideLoading() {
        document.getElementById('loading-modal').classList.add('hidden');
    }
    
    // Global functions for action buttons
    async function deactivateEmployee(employeeId) {
        showConfirm(
            'Deactivate Employee?',
            'Are you sure you want to deactivate this employee?',
            async function() {
                showLoading('Deactivating employee...');
                
                try {
                    const response = await window.API.delete(`employees/${employeeId}`);
                    hideLoading();
                    
                    if (response.success) {
                        alert('Employee deactivated successfully');
                        window.location.reload();
                    } else {
                        alert('Failed to deactivate employee: ' + response.message);
                    }
                } catch (error) {
                    hideLoading();
                    console.error('Error deactivating employee:', error);
                    alert('Failed to deactivate employee. Please try again.');
                }
            }
        );
    }
    
    async function activateEmployee(employeeId) {
        showConfirm(
            'Activate Employee?',
            'Are you sure you want to activate this employee?',
            async function() {
                showLoading('Activating employee...');
                
                try {
                    const response = await window.API.put(`employees/${employeeId}`, { is_active: true });
                    hideLoading();
                    
                    if (response.success) {
                        alert('Employee activated successfully');
                        window.location.reload();
                    } else {
                        alert('Failed to activate employee: ' + response.message);
                    }
                } catch (error) {
                    hideLoading();
                    console.error('Error activating employee:', error);
                    alert('Failed to activate employee. Please try again.');
                }
            }
        );
    }
    
    // Initialize employee manager
    document.addEventListener('DOMContentLoaded', function() {
        new EmployeeManager();
    });
    
    // 201 Files Management Functions
    let currentEmployeeId = null;
    let allDocuments = [];
    
    async function view201Files(employeeId, employeeName) {
        currentEmployeeId = employeeId;
        
        // Update modal title
        document.getElementById('documents-employee-name').textContent = employeeName;
        
        // Show modal
        document.getElementById('documents-modal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        
        // Load documents
        await loadDocuments(employeeId);
    }
    
    async function loadDocuments(employeeId) {
        try {
            showLoading('Loading documents...');
            
            const response = await window.API.get(`employees/${employeeId}/documents`);
            
            hideLoading();
            
            if (response.success) {
                allDocuments = response.data.documents || [];
                const storage = response.data.storage || {};
                
                // Update stats
                updateDocumentStats(allDocuments.length, storage.total_size || 0);
                
                // Display documents
                displayDocuments(allDocuments);
            } else {
                alert('Failed to load documents: ' + response.message);
            }
        } catch (error) {
            hideLoading();
            console.error('Error loading documents:', error);
            alert('Failed to load documents. Please try again.');
        }
    }
    
    function displayDocuments(documents) {
        const tbody = document.getElementById('documents-table-body');
        const emptyState = document.getElementById('documents-empty-state');
        
        if (!documents || documents.length === 0) {
            tbody.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }
        
        emptyState.classList.add('hidden');
        
        tbody.innerHTML = documents.map(doc => {
            const uploadDate = new Date(doc.uploaded_at).toLocaleDateString();
            const fileSize = formatFileSize(doc.file_size);
            const fileIcon = getFileIcon(doc.mime_type);
            const verifiedBadge = doc.is_verified 
                ? '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Verified</span>'
                : '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unverified</span>';
            
            return `
                <tr class="hover:bg-slate-800">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center">
                            ${fileIcon}
                            <span class="ml-2 text-sm text-slate-300">${escapeHtml(doc.document_type)}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-300">${escapeHtml(doc.file_name)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-400">${fileSize}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-400">${uploadDate}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   ${doc.is_verified ? 'checked' : ''} 
                                   onchange="toggleVerify('${doc.id}', this.checked)"
                                   class="form-checkbox h-4 w-4 text-purple-600 rounded focus:ring-purple-500 bg-slate-700 border-slate-600">
                            <span class="ml-2 text-xs text-slate-400">${doc.is_verified ? 'Verified' : 'Verify'}</span>
                        </label>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button onclick="downloadDocument('${currentEmployeeId}', '${doc.id}')" 
                                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download
                            </button>
                            <button onclick="deleteDocument('${doc.id}', '${escapeHtml(doc.file_name)}')" 
                                    class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm rounded-md transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }
    
    function filterDocuments() {
        const filterValue = document.getElementById('document-type-filter').value;
        
        if (!filterValue) {
            displayDocuments(allDocuments);
        } else {
            const filtered = allDocuments.filter(doc => doc.document_type === filterValue);
            displayDocuments(filtered);
        }
    }
    
    async function downloadDocument(employeeId, documentId) {
        // Find the document to get its filename
        const doc = allDocuments.find(d => d.id === documentId);
        const fileName = doc ? doc.file_name : 'this document';
        
        showConfirm(
            'Download Document?',
            `Are you sure you want to download "${fileName}"?`,
            async function() {
                try {
                    showLoading('Preparing download...');
                    
                    const url = `/HRIS/api/employees/${employeeId}/documents/${documentId}/download`;
                    
                    // Get token from localStorage (matching the pattern used in this page)
                    const token = localStorage.getItem('hris_token') || localStorage.getItem('access_token');
                    
                    if (!token) {
                        hideLoading();
                        alert('Authentication required. Please log in again.');
                        return;
                    }
                    
                    // Fetch with authentication
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Authorization': `Bearer ${token}`
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error('Download failed');
                    }
                    
                    // Get filename from Content-Disposition header
                    const contentDisposition = response.headers.get('Content-Disposition');
                    let filename = 'download';
                    if (contentDisposition) {
                        const matches = /filename="([^"]+)"/.exec(contentDisposition);
                        if (matches && matches[1]) {
                            filename = matches[1];
                        }
                    }
                    
                    // Create blob and download
                    const blob = await response.blob();
                    const blobUrl = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = blobUrl;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(blobUrl);
                    
                    hideLoading();
                    alert('Document downloaded successfully');
                    
                } catch (error) {
                    hideLoading();
                    console.error('Error downloading document:', error);
                    alert('Failed to download document. Please try again.');
                }
            }
        );
    }
    
    async function toggleVerify(documentId, isVerified) {
        const action = isVerified ? 'verify' : 'unverify';
        const message = isVerified 
            ? 'Are you sure you want to verify this document? This indicates the document has been reviewed and approved.'
            : 'Are you sure you want to unverify this document?';
        
        showConfirm(
            `${isVerified ? 'Verify' : 'Unverify'} Document?`,
            message,
            async function() {
                try {
                    showLoading(isVerified ? 'Verifying document...' : 'Unverifying document...');
                    
                    const response = await window.API.put(
                        `employees/${currentEmployeeId}/documents/${documentId}/verify`,
                        { is_verified: isVerified }
                    );
                    
                    hideLoading();
                    
                    if (response.success) {
                        alert(`Document ${action}d successfully`);
                        await loadDocuments(currentEmployeeId);
                    } else {
                        alert('Failed to update verification: ' + response.message);
                        await loadDocuments(currentEmployeeId);
                    }
                } catch (error) {
                    hideLoading();
                    console.error('Error updating verification:', error);
                    alert('Failed to update verification. Please try again.');
                    await loadDocuments(currentEmployeeId);
                }
            },
            async function() {
                // On cancel, reload to reset checkbox state
                await loadDocuments(currentEmployeeId);
            }
        );
    }
    
    async function deleteDocument(documentId, fileName) {
        showConfirm(
            'Delete Document?',
            `Are you sure you want to delete "${fileName}"? This action cannot be undone.`,
            async function() {
                try {
                    showLoading('Deleting document...');
                    
                    const response = await window.API.delete(
                        `employees/${currentEmployeeId}/documents/${documentId}`
                    );
                    
                    hideLoading();
                    
                    if (response.success) {
                        alert('Document deleted successfully');
                        // Reload documents
                        await loadDocuments(currentEmployeeId);
                    } else {
                        alert('Failed to delete document: ' + response.message);
                    }
                } catch (error) {
                    hideLoading();
                    console.error('Error deleting document:', error);
                    alert('Failed to delete document. Please try again.');
                }
            }
        );
    }
    
    function updateDocumentStats(count, totalSize) {
        document.getElementById('documents-count').textContent = 
            `${count} document${count !== 1 ? 's' : ''}`;
        document.getElementById('documents-size').textContent = formatFileSize(totalSize);
    }
    
    function formatFileSize(bytes) {
        if (bytes < 1024) {
            return bytes + ' B';
        } else if (bytes < 1024 * 1024) {
            return (bytes / 1024).toFixed(1) + ' KB';
        } else {
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }
    }
    
    function getFileIcon(mimeType) {
        if (mimeType.includes('pdf')) {
            return '<svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>';
        } else if (mimeType.includes('image')) {
            return '<svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>';
        } else {
            return '<svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>';
        }
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function closeDocumentsModal() {
        document.getElementById('documents-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        currentEmployeeId = null;
        allDocuments = [];
        
        // Reset filter
        document.getElementById('document-type-filter').value = '';
    }
    
    // Event listeners for modal
    document.getElementById('close-documents-modal')?.addEventListener('click', closeDocumentsModal);
    document.getElementById('close-documents-modal-btn')?.addEventListener('click', closeDocumentsModal);
    document.getElementById('document-type-filter')?.addEventListener('change', filterDocuments);
    
    // Close modal on outside click
    document.getElementById('documents-modal')?.addEventListener('click', function(e) {
        if (e.target.id === 'documents-modal') {
            closeDocumentsModal();
        }
    });
</script>