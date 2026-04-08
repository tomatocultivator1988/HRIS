<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRIS MVP - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
    <style>
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.95);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .loading-overlay.active {
            display: flex;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="h-full bg-slate-900">
    <!-- Loading Overlay -->
    <div id="login-loading" class="loading-overlay">
        <div class="bg-slate-800 rounded-xl p-8 text-center shadow-2xl border border-slate-700">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-blue-500 mb-4"></div>
            <h2 class="text-xl font-semibold text-white">Signing In...</h2>
            <p class="text-slate-400 mt-2">Please wait</p>
        </div>
    </div>

    <div class="min-h-full flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 gradient-bg items-center justify-center p-12">
            <div class="max-w-md text-white">
                <h1 class="text-5xl font-bold mb-6">HRIS MVP</h1>
                <p class="text-xl mb-8 text-purple-100">Human Resource Information System</p>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 mr-3 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold">Employee Management</h3>
                            <p class="text-purple-100 text-sm">Manage your workforce efficiently</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-6 h-6 mr-3 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold">Attendance Tracking</h3>
                            <p class="text-purple-100 text-sm">Monitor time and attendance</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-6 h-6 mr-3 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold">Leave Management</h3>
                            <p class="text-purple-100 text-sm">Handle leave requests seamlessly</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="flex-1 flex items-center justify-center p-8 bg-slate-900">
            <div class="max-w-md w-full space-y-8">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
                        HRIS MVP
                    </h1>
                    <p class="text-slate-400 mt-2">Human Resource Information System</p>
                </div>

                <!-- Login Card -->
                <div class="bg-slate-800 p-8 rounded-xl shadow-2xl border border-slate-700">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-white">Welcome Back</h2>
                        <p class="text-slate-400 mt-2">Sign in to your account</p>
                    </div>

                    <form id="login-form" class="space-y-6" method="POST">
                        <div id="error-message" class="hidden bg-red-500/10 border border-red-500 text-red-400 px-4 py-3 rounded-lg text-sm"></div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-300 mb-2">
                                Email Address
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                placeholder="Enter your email"
                                required
                                autocomplete="email"
                            >
                            <div id="email-error" class="text-red-400 text-sm mt-1 hidden"></div>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-300 mb-2">
                                Password
                            </label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                placeholder="Enter your password"
                                required
                                autocomplete="current-password"
                            >
                            <div id="password-error" class="text-red-400 text-sm mt-1 hidden"></div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    id="remember-me" 
                                    name="remember-me"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-600 rounded bg-slate-700"
                                >
                                <label for="remember-me" class="ml-2 block text-sm text-slate-300">
                                    Remember me
                                </label>
                            </div>
                            <a href="#" class="text-sm text-blue-400 hover:text-blue-300 transition-colors">
                                Forgot password?
                            </a>
                        </div>

                        <button 
                            type="submit" 
                            id="login-btn"
                            class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-800 transition-all shadow-lg shadow-blue-900/50"
                        >
                            <span id="login-text">Sign In</span>
                            <div id="login-spinner" class="hidden ml-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </button>
                    </form>
                </div>

                <!-- Demo Credentials -->
                <div class="bg-blue-500/10 border border-blue-500/30 p-4 rounded-lg">
                    <h3 class="text-sm font-semibold text-blue-400 mb-2">Demo Credentials</h3>
                    <div class="text-sm text-slate-300 space-y-1">
                        <p class="cursor-pointer hover:text-white transition-colors" data-demo="admin">
                            <span class="font-semibold text-blue-400">Admin:</span> admin@company.com / Admin123!
                        </p>
                        <p class="cursor-pointer hover:text-white transition-colors" data-demo="employee">
                            <span class="font-semibold text-blue-400">Employee:</span> employee@company.com / emp123
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center text-sm text-slate-500">
                    <p>&copy; <span id="current-year"></span> HRIS MVP. Built with PHP & Supabase.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('/assets/js/config.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= base_url('/assets/js/utils.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= base_url('/assets/js/validation.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= base_url('/assets/js/api.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= base_url('/assets/js/auth.js') ?>?v=<?= time() ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set current year in footer
            document.getElementById('current-year').textContent = new Date().getFullYear();
            
            // Initialize form validation
            const validator = new FormValidator('#login-form', {
                email: ValidationRules.email('Email Address'),
                password: {
                    required: true,
                    label: 'Password',
                    minLength: 3 // Relaxed for demo
                }
            });

            const loginForm = document.getElementById('login-form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const loginBtn = document.getElementById('login-btn');
            const loginText = document.getElementById('login-text');
            const loginSpinner = document.getElementById('login-spinner');
            const errorMessage = document.getElementById('error-message');
            const loginLoading = document.getElementById('login-loading');

            // Handle form submission
            loginForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Clear previous errors
                hideError();
                
                // Validate form
                if (!validator.validateAll()) {
                    return;
                }
                
                // Get form data
                const email = emailInput.value.trim();
                const password = passwordInput.value;
                
                // Show loading state
                setLoading(true);
                loginLoading.classList.add('active');
                
                try {
                    // Attempt login
                    const result = await window.AuthManager.login(email, password);
                    
                    console.log('=== LOGIN DEBUG ===');
                    console.log('Login result:', result);
                    console.log('User data:', result.user);
                    console.log('User role:', result.user?.role);
                    console.log('Force password change:', result.user?.force_password_change);
                    console.log('Redirect URL:', result.redirectUrl);
                    console.log('Force change flag in result:', result.force_password_change);
                    console.log('===================');
                    
                    if (result.success) {
                        // OVERRIDE: Check force password change HERE
                        const user = result.user;
                        let finalRedirectUrl = result.redirectUrl;
                        
                        if (user && user.role === 'employee' && user.force_password_change === true) {
                            console.log('🔴 FORCE PASSWORD CHANGE DETECTED!');
                            console.log('Overriding redirect URL to password change page');
                            finalRedirectUrl = window.AppConfig ? window.AppConfig.url('password/change') : '/HRIS/password/change';
                        }
                        
                        console.log('Final redirect URL:', finalRedirectUrl);
                        
                        // Keep loading screen visible during redirect
                        setTimeout(() => {
                            console.log('Redirecting to:', finalRedirectUrl);
                            window.location.href = finalRedirectUrl;
                        }, 500);
                    } else {
                        loginLoading.classList.remove('active');
                        showError(result.message || 'Login failed. Please check your credentials.');
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    loginLoading.classList.remove('active');
                    showError('An error occurred. Please try again.');
                } finally {
                    setLoading(false);
                }
            });

            // Demo credential quick fill
            document.querySelectorAll('[data-demo]').forEach(element => {
                element.addEventListener('click', function() {
                    const demo = this.getAttribute('data-demo');
                    
                    if (demo === 'admin') {
                        emailInput.value = 'admin@company.com';
                        passwordInput.value = 'Admin123!';
                    } else if (demo === 'employee') {
                        emailInput.value = 'employee@company.com';
                        passwordInput.value = 'emp123';
                    }
                    
                    validator.clearAllErrors();
                    emailInput.focus();
                });
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Enter key to submit form
                if (e.key === 'Enter' && (e.target === emailInput || e.target === passwordInput)) {
                    loginForm.dispatchEvent(new Event('submit'));
                }
            });

            function setLoading(loading) {
                loginBtn.disabled = loading;
                if (loading) {
                    loginText.textContent = 'Signing In...';
                    loginSpinner.classList.remove('hidden');
                } else {
                    loginText.textContent = 'Sign In';
                    loginSpinner.classList.add('hidden');
                }
            }

            function showError(message) {
                errorMessage.textContent = message;
                errorMessage.classList.remove('hidden');
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    hideError();
                }, 5000);
            }

            function hideError() {
                errorMessage.classList.add('hidden');
            }

            // Focus email input on page load
            emailInput.focus();
        });
    </script>
</body>
</html>
