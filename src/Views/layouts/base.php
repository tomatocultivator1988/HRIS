<?php
/**
 * Base Layout Template
 * 
 * This is the main layout template that other views extend.
 * It provides the common HTML structure, navigation, and includes.
 */

$title = $title ?? 'HRIS MVP';
$user = $user ?? null;
$content = $content ?? '';
$scripts = $scripts ?? [];
$styles = $styles ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
    
    <?php foreach ($styles as $style): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>">
    <?php endforeach; ?>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="<?= base_url('/dashboard') ?>" class="text-xl font-semibold text-indigo-600">HRIS MVP</a>
                    
                    <div id="nav-links" class="ml-10 flex items-baseline space-x-4" style="display: none;">
                        <!-- Navigation links will be populated by JavaScript based on user role -->
                    </div>
                </div>
                
                <div id="user-info" class="flex items-center space-x-4" style="display: none;">
                    <span id="user-name" class="text-sm text-gray-700"></span>
                    <button id="logoutBtn" class="text-gray-500 hover:text-gray-700 text-sm">Logout</button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">
                © <?= date('Y') ?> HRIS MVP. All rights reserved.
            </p>
        </div>
    </footer>

    <!-- Common Scripts -->
    <script src="<?= base_url('/assets/js/config.js') ?>"></script>
    <script src="<?= base_url('/assets/js/utils.js') ?>"></script>
    <script src="<?= base_url('/assets/js/auth.js') ?>"></script>
    <script src="<?= base_url('/assets/js/api.js') ?>"></script>
    
    <?php foreach ($scripts as $script): ?>
        <script src="<?= htmlspecialchars($script) ?>"></script>
    <?php endforeach; ?>
    
    <script>
        // Populate navigation based on user from localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const user = JSON.parse(localStorage.getItem('hris_user') || 'null');
            
            if (user) {
                // Show user info
                const userInfo = document.getElementById('user-info');
                const userName = document.getElementById('user-name');
                const navLinks = document.getElementById('nav-links');
                
                if (userInfo && userName) {
                    userName.textContent = 'Welcome, ' + (user.name || user.email);
                    userInfo.style.display = 'flex';
                }
                
                // Populate navigation links based on role
                if (navLinks) {
                    if (user.role === 'admin') {
                        navLinks.innerHTML = `
                            <a href="<?= base_url('/dashboard/admin') ?>" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                            <a href="<?= base_url('/employees') ?>" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">Employees</a>
                            <a href="<?= base_url('/reports') ?>" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">Reports</a>
                        `;
                    } else {
                        navLinks.innerHTML = `
                            <a href="<?= base_url('/dashboard/employee') ?>" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                            <a href="<?= base_url('/profile') ?>" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">My Profile</a>
                        `;
                    }
                    navLinks.style.display = 'flex';
                }
                
                // Initialize logout functionality
                const logoutBtn = document.getElementById('logoutBtn');
                if (logoutBtn) {
                    logoutBtn.addEventListener('click', function() {
                        if (window.AuthManager) {
                            window.AuthManager.logout();
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>
