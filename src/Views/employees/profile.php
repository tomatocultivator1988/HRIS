<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - HRIS MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/assets/css/loading-skeletons.css') ?>">
</head>
<body class="h-full bg-slate-900 overflow-hidden">
    <!-- Main Container -->
    <div class="flex h-full bg-slate-900">
        
        <!-- Toast Notification Container -->
        <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>
        
        <!-- Edit Profile Modal -->
        <div id="edit-profile-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
            <div class="bg-slate-800 rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <h3 class="text-xl font-bold text-white">Edit Profile</h3>
                    </div>
                    <button onclick="closeEditModal()" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form id="edit-profile-form" class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">First Name</label>
                                <input type="text" id="edit-first-name" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500" readonly>
                                <p class="text-xs text-slate-500 mt-1">Contact HR to change</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Last Name</label>
                                <input type="text" id="edit-last-name" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500" readonly>
                                <p class="text-xs text-slate-500 mt-1">Contact HR to change</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Email</label>
                            <input type="email" id="edit-email" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500" readonly>
                            <p class="text-xs text-slate-500 mt-1">Contact HR to change</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Phone Number</label>
                            <input type="tel" id="edit-phone" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Address</label>
                            <textarea id="edit-address" rows="3" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500" placeholder="Enter your address..."></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Emergency Contact</label>
                            <input type="text" id="edit-emergency-contact" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500" placeholder="Name and phone number">
                        </div>
                    </div>
                </form>
                <div class="bg-slate-700 px-6 py-4 flex justify-end space-x-3">
                    <button onclick="closeEditModal()" class="px-6 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-lg transition-all">
                        Cancel
                    </button>
                    <button onclick="saveProfile()" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Confirmation Modal -->
        <div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
            <div class="bg-slate-800 rounded-xl shadow-2xl max-w-md w-full mx-4 border border-slate-700">
                <div class="p-6">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-yellow-500/20 rounded-full mb-4">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 id="confirm-title" class="text-xl font-bold text-white text-center mb-2"></h3>
                    <p id="confirm-message" class="text-slate-300 text-center mb-6"></p>
                </div>
                <div class="bg-slate-700 px-6 py-4 flex space-x-3 rounded-b-xl">
                    <button onclick="closeConfirmModal()" class="flex-1 px-4 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-lg transition-all">
                        Cancel
                    </button>
                    <button id="confirm-action-btn" class="flex-1 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Loading Modal -->
        <div id="loading-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
            <div class="bg-slate-800 rounded-xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center border border-slate-700">
                <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-blue-500 mb-4"></div>
                <p id="loading-message" class="text-white text-lg font-semibold">Processing...</p>
            </div>
        </div>
        
        <!-- Sidebar -->
        <?php $currentPage = 'profile'; include __DIR__ . '/../layouts/employee_sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-slate-900">
            <!-- Header -->
            <header class="bg-slate-800 border-b border-slate-700 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-bold text-white">My Profile</h2>
                        <p class="text-slate-400 mt-1">View and manage your personal information</p>
                    </div>
                    <button onclick="openEditModal()" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg shadow-blue-900/50">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Profile
                    </button>
                </div>
            </header>
            
            <!-- Content -->
            <div class="p-8 space-y-6">
                
                <!-- Profile Header Card -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 shadow-xl p-8">
                    <div class="flex items-center space-x-6">
                        <div class="flex-shrink-0">
                            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold">
                                <span id="profile-initials">EU</span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-white" id="profile-name">Employee User</h3>
                            <p class="text-lg text-slate-300 mt-1" id="profile-position">Position • Department</p>
                            <p class="text-slate-400 mt-2" id="profile-email">employee@company.com</p>
                            <div class="flex items-center mt-3 space-x-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                                    Active
                                </span>
                                <span class="text-sm text-slate-400" id="profile-employee-id">Employee ID: --</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Information Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Personal Information -->
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
                        <div class="p-6 border-b border-slate-700">
                            <h4 class="text-xl font-semibold text-white">Personal Information</h4>
                            <p class="text-slate-400 text-sm mt-1">Your basic personal details</p>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="profile-field">
                                    <label class="block text-sm font-medium text-slate-400 mb-1">First Name</label>
                                    <p class="text-white" id="display-first-name">--</p>
                                </div>
                                <div class="profile-field">
                                    <label class="block text-sm font-medium text-slate-400 mb-1">Last Name</label>
                                    <p class="text-white" id="display-last-name">--</p>
                                </div>
                            </div>
                            <div class="profile-field">
                                <label class="block text-sm font-medium text-slate-400 mb-1">Email Address</label>
                                <p class="text-white" id="display-email">--</p>
                            </div>
                            <div class="profile-field">
                                <label class="block text-sm font-medium text-slate-400 mb-1">Phone Number</label>
                                <p class="text-white" id="display-phone">--</p>
                            </div>
                            <div class="profile-field">
                                <label class="block text-sm font-medium text-slate-400 mb-1">Address</label>
                                <p class="text-white" id="display-address">--</p>
                            </div>
                            <div class="profile-field">
                                <label class="block text-sm font-medium text-slate-400 mb-1">Emergency Contact</label>
                                <p class="text-white" id="display-emergency-contact">--</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Employment Information -->
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
                        <div class="p-6 border-b border-slate-700">
                            <h4 class="text-xl font-semibold text-white">Employment Information</h4>
                            <p class="text-slate-400 text-sm mt-1">Your job and company details</p>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="profile-field">
                                <label class="block text-sm font-medium text-slate-400 mb-1">Employee ID</label>
                                <p class="text-white" id="display-employee-id">--</p>
                            </div>
                            <div class="profile-field">
                                <label class="block text-sm font-medium text-slate-400 mb-1">Department</label>
                                <p class="text-white" id="display-department">--</p>
                            </div>
                            <div class="profile-field">
                                <label class="block text-sm font-medium text-slate-400 mb-1">Position</label>
                                <p class="text-white" id="display-position">--</p>
                            </div>
                            <div class="profile-field">
                                <label class="block text-sm font-medium text-slate-400 mb-1">Employment Status</label>
                                <p class="text-white" id="display-employment-status">--</p>
                            </div>
                            <div class="profile-field">
                                <label class="block text-sm font-medium text-slate-400 mb-1">Date Hired</label>
                                <p class="text-white" id="display-date-hired">--</p>
                            </div>
                            <div class="profile-field">
                                <label class="block text-sm font-medium text-slate-400 mb-1">Manager</label>
                                <p class="text-white" id="display-manager">--</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-lg p-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-slate-400">Leave Balance</p>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-white" id="stats-leave-balance">--</p>
                                    <p class="ml-2 text-sm text-slate-400">days</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl p-6" id="years-service-card">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-lg p-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-slate-400">Attendance Rate</p>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-white" id="stats-attendance-rate">--</p>
                                    <p class="ml-2 text-sm text-slate-400">%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-lg p-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-slate-400">Years of Service</p>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-white" id="stats-years-service">--</p>
                                    <p class="ml-2 text-sm text-slate-400">years</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Scripts -->
    <script src="<?= base_url('/assets/js/config.js') ?>"></script>
    <script src="<?= base_url('/assets/js/auth.js') ?>"></script>
    <script src="<?= base_url('/assets/js/token-manager.js') ?>"></script>
    <script src="<?= base_url('/assets/js/loading-skeletons.js') ?>"></script>
    <script src="<?= base_url('/assets/js/utils.js') ?>"></script>
    <script src="<?= base_url('/assets/js/profile-utils.js') ?>"></script>
    <script>
        let currentUser = null;
        let employeeData = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                // Get current user
                currentUser = await getCurrentUser();
                
                if (!currentUser) {
                    console.error('No user found');
                    hideLoading();
                    return;
                }
                
                // Update user info and navigation
                updateUserInfo(currentUser);
                updateNavigationForRole(currentUser.role);
                
                // Load employee profile data
                await loadEmployeeProfile();
                
                // Hide loading screen
                hideLoading();
            } catch (error) {
                console.error('Initialization error:', error);
                showError('Failed to load profile: ' + error.message);
                hideLoading();
            }
        });

        // Update user info in sidebar
        function updateUserInfo(user) {
            const initial = user.name ? user.name.charAt(0).toUpperCase() : 'E';
            document.querySelector('.w-10.h-10').textContent = initial;
            document.getElementById('user-name').textContent = user.name || 'Employee User';
            document.getElementById('user-email').textContent = user.email || '';
        }
        
        // Update navigation based on role (handled by employee_sidebar.php component)
        function updateNavigationForRole(role) {
            // Navigation is now automatically handled by the employee_sidebar.php component
            // This function is kept for backward compatibility but does nothing
        }
        
        // Load employee profile data
        async function loadEmployeeProfile() {
            try {
                const response = await fetch(AppConfig.getApiUrl('/employees/profile'), {
                    headers: { 'Authorization': `Bearer ${getAccessToken()}` }
                });
                
                const result = await response.json();
                
                if (result.success && result.data.employee) {
                    employeeData = result.data.employee;
                    displayEmployeeProfile(employeeData);
                    await loadProfileStats();
                } else {
                    // Check if user is admin (admins don't have employee profiles)
                    if (currentUser && currentUser.role === 'admin') {
                        showError('Admin users do not have employee profiles. Redirecting to dashboard...');
                        setTimeout(() => {
                            window.location.href = AppConfig.getBaseUrl('/dashboard/admin');
                        }, 2000);
                    } else {
                        showError('Failed to load profile data');
                    }
                }
            } catch (error) {
                console.error('Error loading profile:', error);
                showError('Failed to load profile data');
            }
        }
        
        // Display employee profile data
        function displayEmployeeProfile(employee) {
            const initials = (employee.first_name?.charAt(0) || '') + (employee.last_name?.charAt(0) || '');
            document.getElementById('profile-initials').textContent = initials.toUpperCase();
            document.getElementById('profile-name').textContent = `${employee.first_name || ''} ${employee.last_name || ''}`.trim();
            const headerPositionParts = [employee.position, employee.department].filter(value => ProfileUtils.hasDisplayValue(value));
            document.getElementById('profile-position').textContent = headerPositionParts.length > 0 ? headerPositionParts.join(' • ') : '';
            document.getElementById('profile-email').textContent = ProfileUtils.getFirstDisplayValue(employee.work_email, employee.email) || '';
            document.getElementById('profile-employee-id').textContent = ProfileUtils.hasDisplayValue(employee.employee_id) ? `Employee ID: ${employee.employee_id}` : '';

            setProfileFieldValue('display-first-name', employee.first_name);
            setProfileFieldValue('display-last-name', employee.last_name);
            setProfileFieldValue('display-email', ProfileUtils.getFirstDisplayValue(employee.work_email, employee.email));
            setProfileFieldValue('display-phone', ProfileUtils.getFirstDisplayValue(employee.mobile_number, employee.phone));
            setProfileFieldValue('display-address', employee.address);
            setProfileFieldValue('display-emergency-contact', employee.emergency_contact);
            setProfileFieldValue('display-employee-id', employee.employee_id);
            setProfileFieldValue('display-department', employee.department);
            setProfileFieldValue('display-position', employee.position);
            setProfileFieldValue('display-employment-status', employee.employment_status);
            setProfileFieldValue('display-date-hired', employee.date_hired, ProfileUtils.formatEmployeeDate(employee.date_hired, navigator.language));
            setProfileFieldValue('display-manager', employee.manager_name);

            document.getElementById('edit-first-name').value = employee.first_name || '';
            document.getElementById('edit-last-name').value = employee.last_name || '';
            document.getElementById('edit-email').value = employee.work_email || employee.email || '';
            document.getElementById('edit-phone').value = employee.mobile_number || employee.phone || '';
            document.getElementById('edit-address').value = employee.address || '';
            document.getElementById('edit-emergency-contact').value = employee.emergency_contact || '';
        }
        
        // Load profile statistics
        async function loadProfileStats() {
            try {
                // Load leave balance
                const leaveResponse = await fetch(AppConfig.getApiUrl('/leave/balance'), {
                    headers: { 'Authorization': `Bearer ${getAccessToken()}` }
                });
                
                if (leaveResponse.ok) {
                    const leaveResult = await leaveResponse.json();
                    if (leaveResult.success && leaveResult.data.balance) {
                        const totalBalance = leaveResult.data.balance.reduce((sum, item) => sum + (item.remaining_credits || 0), 0);
                        document.getElementById('stats-leave-balance').textContent = totalBalance;
                    }
                } else {
                    document.getElementById('stats-leave-balance').textContent = '15'; // Default
                }
                
                // Load attendance rate
                const startDate = new Date();
                startDate.setDate(1); // First day of current month
                const endDate = new Date();
                
                const attendanceResponse = await fetch(AppConfig.getApiUrl(`/attendance/history?start_date=${startDate.toISOString().split('T')[0]}&end_date=${endDate.toISOString().split('T')[0]}`), {
                    headers: { 'Authorization': `Bearer ${getAccessToken()}` }
                });
                
                if (attendanceResponse.ok) {
                    const attendanceResult = await attendanceResponse.json();
                    if (attendanceResult.success && attendanceResult.data.records) {
                        const records = attendanceResult.data.records;
                        const presentDays = records.filter(r => r.status === 'Present' || r.status === 'Late').length;
                        const totalDays = records.length;
                        const rate = totalDays > 0 ? Math.round((presentDays / totalDays) * 100) : 0;
                        document.getElementById('stats-attendance-rate').textContent = rate;
                    }
                } else {
                    document.getElementById('stats-attendance-rate').textContent = '0';
                }
                
                const years = ProfileUtils.calculateServiceYears(employeeData?.date_hired);
                const yearsCard = document.getElementById('years-service-card');
                if (years === null) {
                    document.getElementById('stats-years-service').textContent = '--';
                    yearsCard.classList.add('hidden');
                } else {
                    document.getElementById('stats-years-service').textContent = years;
                    yearsCard.classList.remove('hidden');
                }
                
            } catch (error) {
                console.error('Error loading profile stats:', error);
            }
        }
        
        // Modal functions
        function openEditModal() {
            document.getElementById('edit-profile-modal').classList.remove('hidden');
        }
        
        function closeEditModal() {
            document.getElementById('edit-profile-modal').classList.add('hidden');
        }
        
        // Save profile changes
        async function saveProfile() {
            showConfirm(
                'Save Profile Changes?',
                'Are you sure you want to save these changes to your profile?',
                async function() {
                    showLoading('Saving profile...');
                    
                    try {
                        const formData = {
                            mobile_number: document.getElementById('edit-phone').value,
                            address: document.getElementById('edit-address').value,
                            emergency_contact: document.getElementById('edit-emergency-contact').value
                        };
                        
                        const response = await fetch(AppConfig.getApiUrl('/employees/profile'), {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${getAccessToken()}`
                            },
                            body: JSON.stringify(formData)
                        });
                        
                        const result = await response.json();
                        hideLoading();
                        
                        if (result.success) {
                            showSuccess('Profile updated successfully!');
                            closeEditModal();
                            await loadEmployeeProfile(); // Reload profile data
                        } else {
                            showError(result.message || 'Failed to update profile');
                        }
                    } catch (error) {
                        hideLoading();
                        console.error('Save profile error:', error);
                        showError('Failed to update profile');
                    }
                }
            );
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
            // Check if loading modal exists before trying to hide it
            const loadingModal = document.getElementById('loading-modal');
            if (loadingModal) {
                loadingModal.classList.add('hidden');
            }
            
            // Check if page-loading exists (old full-screen loading)
            const pageLoading = document.getElementById('page-loading');
            if (pageLoading) {
                setTimeout(() => {
                    pageLoading.style.opacity = '0';
                    pageLoading.style.transition = 'opacity 0.3s ease-out';
                    setTimeout(() => pageLoading.style.display = 'none', 300);
                }, 500);
            }
        }
        
        function setProfileFieldValue(elementId, value, formattedValue = null) {
            const fieldElement = document.getElementById(elementId);
            if (!fieldElement) {
                return;
            }

            const fieldContainer = fieldElement.closest('.profile-field');
            const hasValue = ProfileUtils.hasDisplayValue(value) || ProfileUtils.hasDisplayValue(formattedValue);

            if (!hasValue) {
                fieldElement.textContent = '';
                if (fieldContainer) {
                    fieldContainer.classList.add('hidden');
                }
                return;
            }

            if (fieldContainer) {
                fieldContainer.classList.remove('hidden');
            }

            fieldElement.textContent = ProfileUtils.hasDisplayValue(formattedValue) ? formattedValue : String(value).trim();
        }
        
        function showSuccess(message) {
            showToast(message, 'success');
        }
        
        function showError(message) {
            showToast(message, 'error');
        }
        
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600';
            const icon = type === 'success' 
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'
                : type === 'error'
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
            
            toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 transform transition-all duration-300 translate-x-full opacity-0`;
            toast.innerHTML = `
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${icon}
                </svg>
                <span class="font-medium">${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;
            
            document.getElementById('toast-container').appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 10);
            
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }
        
        async function getCurrentUser() {
            try {
                let userStr = localStorage.getItem('user') || localStorage.getItem('hris_user');
                if (!userStr) {
                    window.location.href = AppConfig.getBaseUrl('/login');
                    return null;
                }
                return JSON.parse(userStr);
            } catch (error) {
                console.error('Error getting current user:', error);
                window.location.href = AppConfig.getBaseUrl('/login');
                return null;
            }
        }
        
        function getAccessToken() {
            const token = localStorage.getItem('hris_token') || localStorage.getItem('access_token');
            if (!token) {
                console.error('No access token found');
                window.location.href = AppConfig.getBaseUrl('/login');
                return null;
            }
            return token;
        }
    </script>
</body>
</html>
