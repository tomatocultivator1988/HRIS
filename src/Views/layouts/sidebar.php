<?php
/**
 * Smart Sidebar Loader - CLEAN VERSION
 * Loads ONLY the correct sidebar based on user role
 * Uses inline script to determine role, then includes the right sidebar
 * 
 * Usage in shared pages (attendance, leave):
 * $currentPage = 'attendance';
 * include __DIR__ . '/../layouts/sidebar.php';
 */

$currentPage = $currentPage ?? '';
?>
<script>
// Check user role immediately
(function() {
    const user = JSON.parse(localStorage.getItem('hris_user') || '{}');
    
    if (!user || !user.role) {
        window.location.href = window.AppConfig ? window.AppConfig.getBaseUrl('/login') : '/HRIS/login';
        return;
    }
    
    // Store role in a global variable for PHP to check
    window.__userRole = user.role;
})();
</script>

<!-- Load the appropriate sidebar based on role -->
<div id="employee-sidebar-container">
    <?php include __DIR__ . '/employee_sidebar.php'; ?>
</div>

<div id="admin-sidebar-container" style="display: none;">
    <?php include __DIR__ . '/admin_sidebar.php'; ?>
</div>

<script>
// Show/hide the correct sidebar based on user role
(function() {
    const user = JSON.parse(localStorage.getItem('hris_user') || '{}');
    
    const employeeContainer = document.getElementById('employee-sidebar-container');
    const adminContainer = document.getElementById('admin-sidebar-container');
    
    if (user.role === 'admin') {
        employeeContainer.style.display = 'none';
        adminContainer.style.display = 'block';
    } else {
        employeeContainer.style.display = 'block';
        adminContainer.style.display = 'none';
    }
})();
</script>
