/**
 * Global Configuration for HRIS MVP
 * Provides centralized configuration for paths and URLs
 */

// Detect environment and set base path
const detectBasePath = () => {
    // Production: Render.com or custom domain
    if (window.location.hostname.includes('onrender.com') || 
        window.location.hostname.includes('render.app') ||
        !window.location.hostname.includes('localhost')) {
        return ''; // Root path for production
    }
    
    // Development: localhost with HRIS folder
    const path = window.location.pathname;
    const parts = path.split('/').filter(p => p);
    const hrisIndex = parts.findIndex(p => p.toUpperCase() === 'HRIS');
    
    if (hrisIndex >= 0) {
        return '/' + parts.slice(0, hrisIndex + 1).join('/');
    }
    
    // Check if we're inside HRIS folder
    if (window.location.href.match(/\/HRIS\//i)) {
        return '/HRIS';
    }
    
    // Default for localhost
    if (window.location.hostname === 'localhost') {
        return '/HRIS';
    }
    
    return '';
};

// Global configuration object
window.AppConfig = {
    // Base path (e.g., '/HRIS' for localhost, '' for production)
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
