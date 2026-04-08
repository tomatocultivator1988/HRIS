<?php

/**
 * Simple autoloader for the MVC framework
 * 
 * This autoloader handles class loading for the Core, Controllers, Models, and Services namespaces.
 */

spl_autoload_register(function ($className) {
    $sharedCoreFiles = [
        'Core\\HRISException' => __DIR__ . '/Core/ErrorHandler.php',
        'Core\\ValidationException' => __DIR__ . '/Core/ErrorHandler.php',
        'Core\\AuthenticationException' => __DIR__ . '/Core/ErrorHandler.php',
        'Core\\AuthorizationException' => __DIR__ . '/Core/ErrorHandler.php',
        'Core\\DatabaseException' => __DIR__ . '/Core/ErrorHandler.php',
        'Core\\BusinessLogicException' => __DIR__ . '/Core/ErrorHandler.php',
        'Core\\NotFoundException' => __DIR__ . '/Core/ErrorHandler.php',
    ];

    if (isset($sharedCoreFiles[$className])) {
        require_once $sharedCoreFiles[$className];
        return;
    }

    // Define namespace to directory mappings
    $namespaces = [
        'Core\\' => __DIR__ . '/Core/',
        'Controllers\\' => __DIR__ . '/Controllers/',
        'Models\\' => __DIR__ . '/Models/',
        'Services\\' => __DIR__ . '/Services/',
        'Middleware\\' => __DIR__ . '/Middleware/',
        'Config\\' => __DIR__ . '/Config/',
    ];
    
    // Check each namespace
    foreach ($namespaces as $namespace => $directory) {
        if (strpos($className, $namespace) === 0) {
            // Remove namespace prefix and convert to file path
            $relativeClass = substr($className, strlen($namespace));
            $file = $directory . str_replace('\\', '/', $relativeClass) . '.php';
            
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
    
    // Fallback: try to load from src directory directly
    $file = __DIR__ . '/' . str_replace('\\', '/', $className) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load configuration helper functions
require_once __DIR__ . '/Config/helpers.php';
