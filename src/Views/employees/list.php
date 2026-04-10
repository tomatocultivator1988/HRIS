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
</script>