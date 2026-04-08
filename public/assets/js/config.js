/**
 * Global Configuration for HRIS MVP
 * Provides centralized configuration for paths and URLs
 */

// Detect base path automatically
const detectBasePath = () => {
    const path = window.location.pathname;
    
    // Split and filter empty parts
    const parts = path.split('/').filter(p => p);
    
    // Look for HRIS folder (case-insensitive)
    const hrisIndex = parts.findIndex(p => p.toUpperCase() === 'HRIS');
    
    if (hrisIndex >= 0) {
        // Found HRIS, return path up to and including HRIS
        return '/' + parts.slice(0, hrisIndex + 1).join('/');
    }
    
    // If not found, check if we're already inside HRIS by looking at the URL
    // This handles cases where the path is like /dashboard/admin
    const fullPath = window.location.href;
    const hrisMatch = fullPath.match(/\/HRIS\//i);
    
    if (hrisMatch) {
        return '/HRIS';
    }
    
    // Last resort: check if current directory contains typical HRIS folders
    // by trying to detect from the current path structure
    if (parts.includes('dashboard') || parts.includes('modules') || parts.includes('api')) {
        // We're likely inside HRIS, assume HRIS is the parent
        return '/HRIS';
    }
    
    // Absolute fallback: empty string (root)
    console.warn('Could not detect HRIS base path, using root');
    return '';
};

// Global configuration object
window.AppConfig = {
    // Base path (e.g., '/HRIS' or '')
    basePath: detectBasePath(),
    
    // Full base URL
    baseUrl: window.location.origin + detectBasePath(),
    
    // API base path
    apiPath: detectBasePath() + '/api',
    
    // Assets path
    assetsPath: detectBasePath() + '/assets',
    
    // Get full URL for a path
    url: function(path) {
        // Remove leading slash
        path = path.replace(/^\/+/, '');
        return this.basePath + '/' + path;
    },
    
    // Get API URL
    apiUrl: function(endpoint) {
        endpoint = endpoint.replace(/^\/+/, '');
        return this.apiPath + '/' + endpoint;
    },
    
    // Alias methods for consistency
    getBaseUrl: function(path) {
        return this.url(path);
    },
    
    getApiUrl: function(endpoint) {
        return this.apiUrl(endpoint);
    },
    
    // Navigate to a page
    navigate: function(path) {
        window.location.href = this.url(path);
    },
    
    // Get asset URL
    asset: function(path) {
        path = path.replace(/^\/+/, '');
        return this.assetsPath + '/' + path;
    }
};

// Make it available globally
window.config = window.AppConfig;

// Debug info (remove in production)
if (window.location.hostname === 'localhost') {
    console.log('=== AppConfig Debug Info ===');
    console.log('Current URL:', window.location.href);
    console.log('Pathname:', window.location.pathname);
    console.log('Detected basePath:', window.AppConfig.basePath);
    console.log('Base URL:', window.AppConfig.baseUrl);
    console.log('API Path:', window.AppConfig.apiPath);
    console.log('Test URLs:');
    console.log('  - Dashboard:', window.AppConfig.url('dashboard/admin'));
    console.log('  - Employees:', window.AppConfig.url('employees'));
    console.log('  - API Login:', window.AppConfig.apiUrl('auth/login'));
    console.log('===========================');
}
