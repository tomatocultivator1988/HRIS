<?php

namespace Core;

// Ensure ValidationResult is loaded
if (!class_exists('Core\ValidationResult')) {
    require_once __DIR__ . '/ValidationResult.php';
}

/**
 * Model Base Class - Provides common database operations and business entity functionality
 * 
 * This abstract class serves as the foundation for all models in the MVC framework,
 * providing CRUD operations, query building, validation, and data transformation.
 * Updated to work with Supabase REST API instead of MySQL PDO.
 */
abstract class Model
{
    protected SupabaseConnection $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $guarded = ['id', 'created_at', 'updated_at'];
    protected array $casts = [];
    protected bool $timestamps = true;
    
    /**
     * Constructor - Initialize model with Supabase connection
     *
     * @param SupabaseConnection $db Supabase connection
     */
    public function __construct(SupabaseConnection $db)
    {
        $this->db = $db;
        
        if (empty($this->table)) {
            // Auto-generate table name from class name if not specified
            $className = (new \ReflectionClass($this))->getShortName();
            $this->table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)) . 's';
        }
    }
    
    /**
     * Find a record by primary key
     *
     * @param mixed $id Primary key value
     * @return array|null Record data or null if not found
     */
    public function find($id): ?array
    {
        try {
            $result = $this->db->find($this->table, $id, $this->primaryKey);
            
            if ($result === null) {
                return null;
            }
            
            return $this->castAttributes($result);
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'find', ['id' => $id]);
            throw new DatabaseException("Failed to find record: " . $e->getMessage());
        }
    }
    
    /**
     * Find multiple records by primary keys
     *
     * @param array $ids Array of primary key values
     * @return array Array of records
     */
    public function findMany(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        
        try {
            $results = $this->db->select($this->table, [
                $this->primaryKey => ['operator' => 'in', 'value' => '(' . implode(',', $ids) . ')']
            ]);
            
            return array_map([$this, 'castAttributes'], $results);
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'findMany', ['ids' => $ids]);
            throw new DatabaseException("Failed to find records: " . $e->getMessage());
        }
    }
    
    /**
     * Create a new record
     *
     * @param array $data Record data
     * @return array Created record with ID
     */
    public function create(array $data): array
    {
        $data = $this->sanitizeData($data);
        $validationResult = $this->validate($data);
        
        if (!$validationResult->isValid()) {
            throw new ValidationException('Validation failed', $validationResult->getErrors());
        }
        
        $data = $validationResult->getSanitizedData();
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        try {
            $result = $this->db->insert($this->table, $data);
            
            // If insert returns empty array, try to fetch the created record
            if (empty($result) || !isset($result['id'])) {
                error_log('Model::create - Insert returned empty or no ID, attempting to fetch created record');
                error_log('Insert result: ' . json_encode($result));
                
                // Try to find the record we just created using unique fields
                // This is a workaround for Supabase not returning the created record
                if (!empty($data['employee_id']) && !empty($data['start_date'])) {
                    // For leave requests, try to find by employee_id and start_date
                    $records = $this->where([
                        'employee_id' => $data['employee_id'],
                        'start_date' => $data['start_date']
                    ])->orderBy('created_at', 'DESC')->limit(1)->get();
                    
                    if (!empty($records)) {
                        error_log('Found created record: ' . json_encode($records[0]));
                        return $this->castAttributes($records[0]);
                    }
                }
                
                error_log('Could not fetch created record, returning data with generated ID');
                // Last resort: return the data we tried to insert
                // Note: This won't have the actual database ID
                return $this->castAttributes($data);
            }
            
            return $this->castAttributes($result);
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'create', ['data' => $data]);
            throw new DatabaseException("Failed to create record: " . $e->getMessage());
        }
    }
    
    /**
     * Update an existing record
     *
     * @param mixed $id Primary key value
     * @param array $data Updated data
     * @return bool True if updated successfully
     */
    public function update($id, array $data): bool
    {
        $data = $this->sanitizeData($data);
        $validationResult = $this->validate($data, $id);
        
        if (!$validationResult->isValid()) {
            throw new ValidationException('Validation failed', $validationResult->getErrors());
        }
        
        $data = $validationResult->getSanitizedData();
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        if (empty($data)) {
            return true; // Nothing to update
        }
        
        try {
            error_log("Model::update - Table: {$this->table}, ID: {$id}");
            error_log("Model::update - Data: " . json_encode($data));
            
            $affectedRows = $this->db->update($this->table, $data, [$this->primaryKey => $id]);
            
            error_log("Model::update - Affected rows: {$affectedRows}");
            
            return $affectedRows > 0;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'update', ['id' => $id, 'data' => $data]);
            throw new DatabaseException("Failed to update record: " . $e->getMessage());
        }
    }
    
    /**
     * Delete a record by primary key
     *
     * @param mixed $id Primary key value
     * @return bool True if deleted successfully
     */
    public function delete($id): bool
    {
        try {
            $affectedRows = $this->db->delete($this->table, [$this->primaryKey => $id]);
            return $affectedRows > 0;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'delete', ['id' => $id]);
            throw new DatabaseException("Failed to delete record: " . $e->getMessage());
        }
    }
    
    /**
     * Create a query builder for complex queries
     *
     * @param array $conditions WHERE conditions
     * @return SupabaseQueryBuilder Query builder instance
     */
    public function where(array $conditions): SupabaseQueryBuilder
    {
        return (new SupabaseQueryBuilder($this->db, $this->table, $this->primaryKey))->where($conditions);
    }
    
    /**
     * Get all records with optional conditions
     *
     * @param array $conditions WHERE conditions
     * @param array $orderBy ORDER BY clauses
     * @param int|null $limit LIMIT clause
     * @param int $offset OFFSET clause
     * @return array Array of records
     */
    public function all(array $conditions = [], array $orderBy = [], ?int $limit = null, int $offset = 0): array
    {
        try {
            $options = [];
            
            if (!empty($orderBy)) {
                $orderClauses = [];
                foreach ($orderBy as $column => $direction) {
                    $orderClauses[] = $column . '.' . strtolower($direction);
                }
                $options['order'] = implode(',', $orderClauses);
            }
            
            if ($limit !== null) {
                $options['limit'] = $limit;
            }
            
            if ($offset > 0) {
                $options['offset'] = $offset;
            }
            
            $results = $this->db->select($this->table, $conditions, $options);
            return array_map([$this, 'castAttributes'], $results);
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'all', ['conditions' => $conditions]);
            throw new DatabaseException("Failed to fetch records: " . $e->getMessage());
        }
    }
    
    /**
     * Count records with optional conditions
     *
     * @param array $conditions WHERE conditions
     * @return int Record count
     */
    public function count(array $conditions = []): int
    {
        try {
            return $this->db->count($this->table, $conditions);
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'count', ['conditions' => $conditions]);
            throw new DatabaseException("Failed to count records: " . $e->getMessage());
        }
    }
    
    /**
     * Check if record exists
     *
     * @param array $conditions WHERE conditions
     * @return bool True if exists, false otherwise
     */
    public function exists(array $conditions): bool
    {
        try {
            return $this->db->exists($this->table, $conditions);
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'exists', ['conditions' => $conditions]);
            throw new DatabaseException("Failed to check record existence: " . $e->getMessage());
        }
    }
    
    /**
     * Validate data before database operations
     *
     * @param array $data Data to validate
     * @param mixed $id Record ID for update operations (null for create)
     * @return ValidationResult Validation result
     */
    protected function validate(array $data, $id = null): ValidationResult
    {
        // Override in child classes to implement specific validation rules
        return new ValidationResult(true, [], $data);
    }
    
    /**
     * Sanitize input data
     *
     * @param array $data Raw input data
     * @return array Sanitized data
     */
    protected function sanitizeData(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove potentially dangerous characters
                $value = trim($value);
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            
            $sanitized[$key] = $value;
        }
        
        return $sanitized;
    }
    
    /**
     * Filter data to only include fillable fields
     *
     * @param array $data Input data
     * @return array Filtered data
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            // If no fillable fields specified, exclude guarded fields
            return array_diff_key($data, array_flip($this->guarded));
        }
        
        // Only include fillable fields
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Cast attributes to appropriate types
     *
     * @param array $attributes Raw attributes from database
     * @return array Casted attributes
     */
    protected function castAttributes(array $attributes): array
    {
        foreach ($this->casts as $key => $type) {
            if (!isset($attributes[$key])) {
                continue;
            }
            
            $value = $attributes[$key];
            
            switch ($type) {
                case 'int':
                case 'integer':
                    $attributes[$key] = (int) $value;
                    break;
                case 'float':
                case 'double':
                    $attributes[$key] = (float) $value;
                    break;
                case 'bool':
                case 'boolean':
                    $attributes[$key] = (bool) $value;
                    break;
                case 'array':
                case 'json':
                    $attributes[$key] = json_decode($value, true);
                    break;
                case 'datetime':
                    $attributes[$key] = new \DateTime($value);
                    break;
            }
        }
        
        return $attributes;
    }
    
    /**
     * Handle database errors with logging
     *
     * @param \Exception $e Database exception
     * @param string $operation Operation that failed
     * @param array $context Additional context
     */
    protected function handleDatabaseError(\Exception $e, string $operation, array $context = []): void
    {
        $logger = Container::getInstance()->resolve('Logger');
        
        $logger->error("Database error in {$operation}", [
            'table' => $this->table,
            'error' => $e->getMessage(),
            'context' => $context,
            'trace' => $e->getTraceAsString()
        ]);
    }
    
    /**
     * Get the table name
     *
     * @return string Table name
     */
    public function getTable(): string
    {
        return $this->table;
    }
    
    /**
     * Get the primary key column name
     *
     * @return string Primary key column name
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }
}

/**
 * SupabaseQueryBuilder Class - Provides fluent interface for building Supabase queries
 */
class SupabaseQueryBuilder
{
    private SupabaseConnection $db;
    private string $table;
    private string $primaryKey;
    private array $wheres = [];
    private array $orderBys = [];
    private ?int $limitCount = null;
    private int $offsetCount = 0;
    private array $selects = ['*'];
    
    public function __construct(SupabaseConnection $db, string $table, string $primaryKey = 'id')
    {
        $this->db = $db;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
    }
    
    /**
     * Add WHERE conditions
     *
     * @param array $conditions Conditions array
     * @return self
     */
    public function where(array $conditions): self
    {
        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                // Handle operator format: ['operator' => 'eq', 'value' => 'test']
                if (isset($value['operator'])) {
                    $this->wheres[$column] = $value;
                } else {
                    // Handle IN operator with array values
                    $this->wheres[$column] = ['operator' => 'in', 'value' => '(' . implode(',', $value) . ')'];
                }
            } else {
                $this->wheres[$column] = ['operator' => 'eq', 'value' => $value];
            }
        }
        
        return $this;
    }
    
    /**
     * Add WHERE condition with custom operator
     *
     * @param string $column Column name
     * @param string $operator Comparison operator
     * @param mixed $value Value to compare
     * @return self
     */
    public function whereOperator(string $column, string $operator, $value): self
    {
        $this->wheres[$column] = ['operator' => $operator, 'value' => $value];
        return $this;
    }
    
    /**
     * Add ORDER BY clause
     *
     * @param string $column Column name
     * @param string $direction Sort direction (ASC or DESC)
     * @return self
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBys[] = $column . '.' . strtolower($direction);
        return $this;
    }
    
    /**
     * Add LIMIT and OFFSET clauses
     *
     * @param int $limit Number of records to limit
     * @param int $offset Number of records to skip
     * @return self
     */
    public function limit(int $limit, int $offset = 0): self
    {
        $this->limitCount = $limit;
        $this->offsetCount = $offset;
        return $this;
    }
    
    /**
     * Execute query and get results
     *
     * @return array Query results
     */
    public function get(): array
    {
        try {
            $options = [];
            
            if (!empty($this->orderBys)) {
                $options['order'] = implode(',', $this->orderBys);
            }
            
            if ($this->limitCount !== null) {
                $options['limit'] = $this->limitCount;
            }
            
            if ($this->offsetCount > 0) {
                $options['offset'] = $this->offsetCount;
            }
            
            return $this->db->select($this->table, $this->wheres, $options);
        } catch (\Exception $e) {
            throw new DatabaseException("Query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get first result
     *
     * @return array|null First result or null
     */
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        
        return $results[0] ?? null;
    }
    
    /**
     * Count matching records
     *
     * @return int Record count
     */
    public function count(): int
    {
        try {
            return $this->db->count($this->table, $this->wheres);
        } catch (\Exception $e) {
            throw new DatabaseException("Count query failed: " . $e->getMessage());
        }
    }
}

/**
 * QueryBuilder Class - Provides fluent interface for building database queries (Legacy MySQL support)
 */
class QueryBuilder
{
    private DatabaseConnection $db;
    private string $table;
    private string $primaryKey;
    private array $wheres = [];
    private array $orderBys = [];
    private ?int $limitCount = null;
    private int $offsetCount = 0;
    private array $joins = [];
    private array $selects = ['*'];
    
    public function __construct(DatabaseConnection $db, string $table, string $primaryKey = 'id')
    {
        $this->db = $db;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
    }
    
    /**
     * Add WHERE conditions
     *
     * @param array $conditions Conditions array
     * @return self
     */
    public function where(array $conditions): self
    {
        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                $this->wheres[] = ['column' => $column, 'operator' => 'IN', 'value' => $value];
            } else {
                $this->wheres[] = ['column' => $column, 'operator' => '=', 'value' => $value];
            }
        }
        
        return $this;
    }
    
    /**
     * Add WHERE condition with custom operator
     *
     * @param string $column Column name
     * @param string $operator Comparison operator
     * @param mixed $value Value to compare
     * @return self
     */
    public function whereOperator(string $column, string $operator, $value): self
    {
        $this->wheres[] = ['column' => $column, 'operator' => $operator, 'value' => $value];
        return $this;
    }
    
    /**
     * Add ORDER BY clause
     *
     * @param string $column Column name
     * @param string $direction Sort direction (ASC or DESC)
     * @return self
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBys[] = ['column' => $column, 'direction' => strtoupper($direction)];
        return $this;
    }
    
    /**
     * Add LIMIT and OFFSET clauses
     *
     * @param int $limit Number of records to limit
     * @param int $offset Number of records to skip
     * @return self
     */
    public function limit(int $limit, int $offset = 0): self
    {
        $this->limitCount = $limit;
        $this->offsetCount = $offset;
        return $this;
    }
    
    /**
     * Execute query and get results
     *
     * @return array Query results
     */
    public function get(): array
    {
        $sql = $this->buildSelectSql();
        $params = $this->buildParameters();
        
        try {
            return $this->db->query($sql, $params);
        } catch (\Exception $e) {
            throw new DatabaseException("Query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get first result
     *
     * @return array|null First result or null
     */
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        
        return $results[0] ?? null;
    }
    
    /**
     * Count matching records
     *
     * @return int Record count
     */
    public function count(): int
    {
        $sql = $this->buildCountSql();
        $params = $this->buildParameters();
        
        try {
            $result = $this->db->query($sql, $params);
            return (int) $result[0]['count'];
        } catch (\Exception $e) {
            throw new DatabaseException("Count query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Build SELECT SQL query
     *
     * @return string SQL query
     */
    private function buildSelectSql(): string
    {
        $sql = "SELECT " . implode(', ', $this->selects) . " FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWhereClause();
        }
        
        if (!empty($this->orderBys)) {
            $orderClauses = array_map(
                function($order) { return "{$order['column']} {$order['direction']}"; },
                $this->orderBys
            );
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        }
        
        if ($this->limitCount !== null) {
            $sql .= " LIMIT {$this->limitCount}";
            if ($this->offsetCount > 0) {
                $sql .= " OFFSET {$this->offsetCount}";
            }
        }
        
        return $sql;
    }
    
    /**
     * Build COUNT SQL query
     *
     * @return string SQL query
     */
    private function buildCountSql(): string
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWhereClause();
        }
        
        return $sql;
    }
    
    /**
     * Build WHERE clause
     *
     * @return string WHERE clause
     */
    private function buildWhereClause(): string
    {
        $clauses = [];
        
        foreach ($this->wheres as $where) {
            if ($where['operator'] === 'IN') {
                $placeholders = str_repeat('?,', count($where['value']) - 1) . '?';
                $clauses[] = "{$where['column']} IN ({$placeholders})";
            } else {
                $clauses[] = "{$where['column']} {$where['operator']} ?";
            }
        }
        
        return implode(' AND ', $clauses);
    }
    
    /**
     * Build parameter array for prepared statement
     *
     * @return array Parameters
     */
    private function buildParameters(): array
    {
        $params = [];
        
        foreach ($this->wheres as $where) {
            if ($where['operator'] === 'IN') {
                $params = array_merge($params, $where['value']);
            } else {
                $params[] = $where['value'];
            }
        }
        
        return $params;
    }
}

// DatabaseException is now defined in ErrorHandler.php
// Removed duplicate declaration to avoid "class already declared" error
