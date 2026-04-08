<?php

namespace Core;

/**
 * ValidationResult Class - Represents the result of a validation operation
 */
class ValidationResult
{
    public bool $isValid;
    public array $errors;
    public array $sanitizedData;
    
    public function __construct(bool $isValid, array $errors = [], array $sanitizedData = [])
    {
        $this->isValid = $isValid;
        $this->errors = $errors;
        $this->sanitizedData = $sanitizedData;
    }
    
    public function hasErrors(): bool
    {
        return !$this->isValid;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function getSanitizedData(): array
    {
        return $this->sanitizedData;
    }
}

/**
 * Container Class - Dependency Injection Container
 * 
 * This class provides dependency injection functionality for the MVC framework,
 * supporting automatic dependency resolution, interface binding, and singleton management.
 */
class Container
{
    private static ?Container $instance = null;
    private array $bindings = [];
    private array $instances = [];
    private array $singletons = [];
    
    /**
     * Get singleton instance of container
     *
     * @return Container Container instance
     */
    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct() {}
    
    /**
     * Bind a class or interface to a concrete implementation
     *
     * @param string $abstract Class or interface name
     * @param string|callable|null $concrete Concrete implementation or factory
     * @param bool $singleton Whether to treat as singleton
     */
    public function bind(string $abstract, $concrete = null, bool $singleton = false): void
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }
        
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton
        ];
        
        if ($singleton) {
            $this->singletons[$abstract] = true;
        }
    }
    
    /**
     * Bind a class or interface as singleton
     *
     * @param string $abstract Class or interface name
     * @param string|callable|null $concrete Concrete implementation or factory
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }
    
    /**
     * Bind an existing instance as singleton
     *
     * @param string $abstract Class or interface name
     * @param mixed $instance Instance to bind (object or other value)
     */
    public function instance(string $abstract, $instance): void
    {
        $this->instances[$abstract] = $instance;
        $this->singletons[$abstract] = true;
    }
    
    /**
     * Resolve a class from the container
     *
     * @param string $abstract Class or interface name
     * @param array $parameters Additional parameters for constructor
     * @return object Resolved instance
     * @throws ContainerException If unable to resolve
     */
    public function resolve(string $abstract, array $parameters = []): object
    {
        // Return existing instance if singleton
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        
        // Get concrete implementation
        $concrete = $this->getConcrete($abstract);
        
        // Build the instance
        $instance = $this->build($concrete, $parameters);
        
        // Store as singleton if configured
        if (isset($this->singletons[$abstract])) {
            $this->instances[$abstract] = $instance;
        }
        
        return $instance;
    }
    
    /**
     * Check if abstract is bound in container
     *
     * @param string $abstract Class or interface name
     * @return bool True if bound, false otherwise
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }
    
    /**
     * Get concrete implementation for abstract
     *
     * @param string $abstract Class or interface name
     * @return string|callable Concrete implementation
     */
    private function getConcrete(string $abstract)
    {
        // Return binding if exists
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }
        
        // Return abstract itself if no binding (auto-resolution)
        return $abstract;
    }
    
    /**
     * Build an instance of the concrete class
     *
     * @param string|callable $concrete Concrete class or factory
     * @param array $parameters Additional constructor parameters
     * @return object Built instance
     * @throws ContainerException If unable to build
     */
    private function build($concrete, array $parameters = []): object
    {
        // Handle factory functions
        if (is_callable($concrete)) {
            return $concrete($this, $parameters);
        }
        
        // Handle class instantiation
        if (is_string($concrete)) {
            return $this->buildClass($concrete, $parameters);
        }
        
        throw new ContainerException("Invalid concrete type for dependency injection");
    }
    
    /**
     * Build a class instance with dependency injection
     *
     * @param string $className Class name to build
     * @param array $parameters Additional constructor parameters
     * @return object Built instance
     * @throws ContainerException If unable to build class
     */
    private function buildClass(string $className, array $parameters = []): object
    {
        try {
            $reflection = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new ContainerException("Class {$className} not found: " . $e->getMessage());
        }
        
        // Check if class is instantiable
        if (!$reflection->isInstantiable()) {
            throw new ContainerException("Class {$className} is not instantiable");
        }
        
        $constructor = $reflection->getConstructor();
        
        // If no constructor, create instance directly
        if ($constructor === null) {
            return new $className();
        }
        
        // Resolve constructor dependencies
        $dependencies = $this->resolveDependencies($constructor, $parameters);
        
        return $reflection->newInstanceArgs($dependencies);
    }
    
    /**
     * Resolve method dependencies
     *
     * @param \ReflectionMethod $method Method to analyze
     * @param array $parameters Additional parameters
     * @return array Resolved dependencies
     * @throws ContainerException If unable to resolve dependencies
     */
    private function resolveDependencies(\ReflectionMethod $method, array $parameters = []): array
    {
        $dependencies = [];
        $methodParameters = $method->getParameters();
        
        foreach ($methodParameters as $index => $parameter) {
            // Use provided parameter if available
            if (isset($parameters[$index])) {
                $dependencies[] = $parameters[$index];
                continue;
            }
            
            // Use named parameter if available
            if (isset($parameters[$parameter->getName()])) {
                $dependencies[] = $parameters[$parameter->getName()];
                continue;
            }
            
            // Try to resolve by type hint
            $type = $parameter->getType();
            
            if ($type && !$type->isBuiltin()) {
                $typeName = $type->getName();
                
                try {
                    $dependencies[] = $this->resolve($typeName);
                    continue;
                } catch (ContainerException $e) {
                    // Fall through to default value or error
                }
            }
            
            // Use default value if available
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }
            
            // Check if parameter is nullable
            if ($parameter->allowsNull()) {
                $dependencies[] = null;
                continue;
            }
            
            throw new ContainerException(
                "Unable to resolve parameter '{$parameter->getName()}' for {$method->getDeclaringClass()->getName()}::{$method->getName()}"
            );
        }
        
        return $dependencies;
    }
    
    /**
     * Call a method with dependency injection
     *
     * @param object|string $class Class instance or class name
     * @param string $method Method name
     * @param array $parameters Additional parameters
     * @return mixed Method return value
     * @throws ContainerException If unable to call method
     */
    public function call($class, string $method, array $parameters = [])
    {
        // Resolve class if string provided
        if (is_string($class)) {
            $class = $this->resolve($class);
        }
        
        try {
            $reflection = new \ReflectionMethod($class, $method);
        } catch (\ReflectionException $e) {
            throw new ContainerException("Method {$method} not found: " . $e->getMessage());
        }
        
        $dependencies = $this->resolveDependencies($reflection, $parameters);
        
        return $reflection->invokeArgs($class, $dependencies);
    }
    
    /**
     * Register default framework bindings
     */
    public function registerDefaultBindings(): void
    {
        // Register container itself
        $this->instance(Container::class, $this);
        $this->instance('Container', $this);
        
        // Register database connection (Supabase)
        $this->singleton('DatabaseConnection', function (Container $container) {
            return new SupabaseConnection();
        });
        
        // Register SupabaseConnection as an alias for DatabaseConnection
        $this->singleton('SupabaseConnection', function (Container $container) {
            return $container->resolve('DatabaseConnection');
        });
        
        // Register logger
        $this->singleton('Logger', function (Container $container) {
            return new Logger(['file' => 'logs/app.log']);
        });
        
        // Register validator
        $this->singleton('Validator', function (Container $container) {
            return new Validator();
        });
        
        // Register configuration manager (simplified)
        $this->singleton('ConfigManager', function () {
            return new class {
                public function get($key, $default = null) {
                    return $default;
                }
                public function getSupabaseConfig() {
                    return require dirname(__DIR__, 2) . '/config/supabase.php';
                }
            };
        });
    }
    
    /**
     * Clear all bindings and instances (useful for testing)
     */
    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
        $this->singletons = [];
    }
    
    /**
     * Get all registered bindings
     *
     * @return array All bindings
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
    
    /**
     * Get all singleton instances
     *
     * @return array All instances
     */
    public function getInstances(): array
    {
        return $this->instances;
    }
}

/**
 * ContainerException Class - Custom exception for container errors
 */
class ContainerException extends \Exception {}

/**
 * DatabaseConnection Class - Database connection wrapper
 */
class DatabaseConnection
{
    private \PDO $pdo;
    private array $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }
    
    private function connect(): void
    {
        $dsn = "mysql:host={$this->config['host']};dbname={$this->config['database']};charset=utf8mb4";
        
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $this->pdo = new \PDO($dsn, $this->config['username'], $this->config['password'], $options);
    }
    
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    public function getLastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }
    
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }
    
    public function commit(): bool
    {
        return $this->pdo->commit();
    }
    
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }
}

/**
 * Logger Class - Simple logging implementation
 */
class Logger
{
    private array $config;
    
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'level' => 'info',
            'file' => 'logs/app.log',
            'max_files' => 5
        ], $config);
    }
    
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }
    
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }
    
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }
    
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }
    
    private function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] {$level}: {$message}{$contextStr}\n";
        
        $logDir = dirname($this->config['file']);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($this->config['file'], $logEntry, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Validator Class - Input validation
 */
class Validator
{
    public function validate(array $data, array $rules): ValidationResult
    {
        $errors = [];
        $sanitized = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $fieldErrors = [];
            
            foreach ($fieldRules as $rule) {
                if ($rule === 'required' && empty($value)) {
                    $fieldErrors[] = "The {$field} field is required";
                } elseif ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $fieldErrors[] = "The {$field} field must be a valid email";
                } elseif (substr($rule, 0, 4) === 'min:') {
                    $min = (int) substr($rule, 4);
                    if (strlen($value) < $min) {
                        $fieldErrors[] = "The {$field} field must be at least {$min} characters";
                    }
                } elseif (substr($rule, 0, 4) === 'max:') {
                    $max = (int) substr($rule, 4);
                    if (strlen($value) > $max) {
                        $fieldErrors[] = "The {$field} field must not exceed {$max} characters";
                    }
                }
            }
            
            if (!empty($fieldErrors)) {
                $errors[$field] = $fieldErrors;
            } else {
                $sanitized[$field] = $value;
            }
        }
        
        return new ValidationResult(empty($errors), $errors, $sanitized);
    }
}
