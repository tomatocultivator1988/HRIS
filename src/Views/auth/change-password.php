<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - HRIS MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="h-full bg-slate-900">
    <div class="min-h-full flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 gradient-bg items-center justify-center p-12">
            <div class="max-w-md text-white">
                <h1 class="text-5xl font-bold mb-6">HRIS MVP</h1>
                <p class="text-xl mb-8 text-purple-100">Secure Password Management</p>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 mr-3 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold">Secure Authentication</h3>
                            <p class="text-purple-100 text-sm">Your security is our priority</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-6 h-6 mr-3 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold">Password Protection</h3>
                            <p class="text-purple-100 text-sm">Strong encryption standards</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-6 h-6 mr-3 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold">Account Safety</h3>
                            <p class="text-purple-100 text-sm">Regular password updates recommended</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Change Password Form -->
        <div class="flex-1 flex items-center justify-center p-8 bg-slate-900">
            <div class="max-w-md w-full space-y-8">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
                        HRIS MVP
                    </h1>
                    <p class="text-slate-400 mt-2">Human Resource Information System</p>
                </div>

                <!-- Change Password Card -->
                <div class="bg-slate-800 p-8 rounded-xl shadow-2xl border border-slate-700">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full mb-4">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h2 class="text-3xl font-bold text-white">Change Password</h2>
                        <p class="text-slate-400 mt-2">Update your account password</p>
                        <?php if ($force_change ?? false): ?>
                            <div class="mt-4 bg-yellow-500/10 border border-yellow-500 text-yellow-400 px-4 py-3 rounded-lg text-sm flex items-start">
                                <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span>You are required to change your password before continuing.</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form id="changePasswordForm" class="space-y-6">
                        <div id="alertContainer"></div>
                        
                        <div>
                            <label for="currentPassword" class="block text-sm font-medium text-slate-300 mb-2">
                                Current Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <input 
                                    type="password" 
                                    id="currentPassword" 
                                    name="current_password" 
                                    class="w-full pl-10 pr-12 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                    placeholder="Enter current password"
                                    required
                                >
                                <button type="button" id="toggleCurrentPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="newPassword" class="block text-sm font-medium text-slate-300 mb-2">
                                New Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <input 
                                    type="password" 
                                    id="newPassword" 
                                    name="new_password" 
                                    class="w-full pl-10 pr-12 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                    placeholder="Enter new password"
                                    required
                                >
                                <button type="button" id="toggleNewPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-slate-400 mt-2">
                                Password must be at least 8 characters and contain uppercase, lowercase, and numbers.
                            </p>
                        </div>

                        <div>
                            <label for="confirmPassword" class="block text-sm font-medium text-slate-300 mb-2">
                                Confirm New Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <input 
                                    type="password" 
                                    id="confirmPassword" 
                                    name="confirm_password" 
                                    class="w-full pl-10 pr-12 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                    placeholder="Confirm new password"
                                    required
                                >
                                <button type="button" id="toggleConfirmPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <button 
                                type="submit" 
                                id="submitBtn"
                                class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-800 transition-all shadow-lg shadow-blue-900/50"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span id="submitText">Change Password</span>
                            </button>
                            <?php if (!($force_change ?? false)): ?>
                                <a href="<?= base_url('/dashboard') ?>" class="w-full flex items-center justify-center px-4 py-3 bg-slate-700 text-slate-300 font-semibold rounded-lg hover:bg-slate-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 focus:ring-offset-slate-800 transition-all">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                    Back to Dashboard
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="text-center text-sm text-slate-500">
                    <p>&copy; 2024 HRIS MVP. Built with PHP & Supabase.</p>
                </div>
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

    <script src="<?= base_url('/assets/js/config.js') ?>"></script>
    <script src="<?= base_url('/assets/js/auth.js') ?>"></script>
    <script>
        // Toggle password visibility
        document.getElementById('toggleCurrentPassword').addEventListener('click', function() {
            togglePasswordVisibility('currentPassword', this);
        });

        document.getElementById('toggleNewPassword').addEventListener('click', function() {
            togglePasswordVisibility('newPassword', this);
        });

        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            togglePasswordVisibility('confirmPassword', this);
        });

        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('svg');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            }
        }

        // Handle form submission
        document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const alertContainer = document.getElementById('alertContainer');

            // Clear previous alerts
            alertContainer.innerHTML = '';

            // Validate passwords match
            if (newPassword !== confirmPassword) {
                showAlert('New passwords do not match', 'danger');
                return;
            }

            // Validate password strength
            if (newPassword.length < 8) {
                showAlert('Password must be at least 8 characters long', 'danger');
                return;
            }

            if (!/[A-Z]/.test(newPassword)) {
                showAlert('Password must contain at least one uppercase letter', 'danger');
                return;
            }

            if (!/[a-z]/.test(newPassword)) {
                showAlert('Password must contain at least one lowercase letter', 'danger');
                return;
            }

            if (!/[0-9]/.test(newPassword)) {
                showAlert('Password must contain at least one number', 'danger');
                return;
            }

            // Show confirmation modal
            showConfirm(
                'Change Password?',
                'Are you sure you want to change your password? You will need to use the new password for future logins.',
                async function() {
                    // Disable submit button
                    submitBtn.disabled = true;
                    submitText.innerHTML = '<svg class="animate-spin h-5 w-5 text-white inline mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Changing Password...';
                    
                    showLoading('Changing password...');

                    try {
                        const token = localStorage.getItem('hris_token');
                        
                        if (!token) {
                            console.error('No authentication token found');
                            hideLoading();
                            showAlert('Authentication required. Please log in again.', 'danger');
                            submitBtn.disabled = false;
                            submitText.innerHTML = 'Change Password';
                            setTimeout(() => {
                                window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
                            }, 2000);
                            return;
                        }
                        
                        const apiUrl = window.AppConfig ? window.AppConfig.apiUrl('password/change') : '/HRIS/api/password/change';
                        console.log('Calling API:', apiUrl);
                        
                        const response = await fetch(apiUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${token}`
                            },
                            body: JSON.stringify({
                                current_password: currentPassword,
                                new_password: newPassword,
                                confirm_password: confirmPassword
                            })
                        });

                        const data = await response.json();
                        console.log('API Response:', data);
                        hideLoading();

                        if (data.success) {
                            showAlert('Password changed successfully! Logging you in with new credentials...', 'success');
                            
                            // Get current user email before clearing session
                            const currentUser = JSON.parse(localStorage.getItem('hris_user') || '{}');
                            const userEmail = currentUser.email;
                    
                    // Clear session to force fresh token
                    localStorage.removeItem('hris_token');
                    localStorage.removeItem('hris_user');
                    localStorage.removeItem('hris_refresh_token');
                    
                    // Auto-login with new password to get fresh token with updated user data
                    setTimeout(async () => {
                        try {
                            const loginApiUrl = window.AppConfig ? window.AppConfig.apiUrl('auth/login') : '/HRIS/api/auth/login';
                            const loginResponse = await fetch(loginApiUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    email: userEmail,
                                    password: newPassword
                                })
                            });
                            
                            const loginData = await loginResponse.json();
                            
                            if (loginData.success) {
                                const payload = loginData.data || loginData;
                                localStorage.setItem('hris_token', payload.access_token);
                                localStorage.setItem('hris_user', JSON.stringify(payload.user));
                                
                                if (payload.refresh_token) {
                                    localStorage.setItem('hris_refresh_token', payload.refresh_token);
                                }
                                
                                const dashboardUrl = payload.user.role === 'admin' 
                                    ? (window.AppConfig ? window.AppConfig.url('dashboard/admin') : '/HRIS/dashboard/admin')
                                    : (window.AppConfig ? window.AppConfig.url('dashboard/employee') : '/HRIS/dashboard/employee');
                                window.location.href = dashboardUrl;
                            } else {
                                // If auto-login fails, redirect to login page
                                showAlert('Password changed! Please log in with your new password.', 'success');
                                setTimeout(() => {
                                    window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
                                }, 2000);
                            }
                        } catch (error) {
                            console.error('Auto-login error:', error);
                            showAlert('Password changed! Please log in with your new password.', 'success');
                            setTimeout(() => {
                                window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
                            }, 2000);
                        }
                    }, 1500);
                } else {
                    showAlert(data.message || 'Failed to change password', 'danger');
                    submitBtn.disabled = false;
                    submitText.innerHTML = 'Change Password';
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.', 'danger');
                submitBtn.disabled = false;
                submitText.innerHTML = 'Change Password';
            }
        });

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            
            const bgColor = type === 'success' ? 'bg-green-500/10 border-green-500 text-green-400' : 'bg-red-500/10 border-red-500 text-red-400';
            const icon = type === 'success' 
                ? '<svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
                : '<svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
            
            alert.className = `${bgColor} border px-4 py-3 rounded-lg text-sm flex items-start mb-4`;
            alert.innerHTML = `
                ${icon}
                <span class="flex-1">${message}</span>
                <button type="button" class="ml-2 text-current opacity-70 hover:opacity-100" onclick="this.parentElement.remove()">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            `;
            alertContainer.appendChild(alert);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        // Check if user needs to change password on page load
        (function() {
            const forceChange = <?php echo json_encode($force_change ?? false); ?>;
            if (forceChange) {
                // Prevent navigation away from this page
                window.addEventListener('beforeunload', function(e) {
                    e.preventDefault();
                    e.returnValue = '';
                });
            }
        })();
        
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
        
        // Focus first input on page load
        document.getElementById('currentPassword').focus();
    </script>
</body>
</html>
</body>
</html>
