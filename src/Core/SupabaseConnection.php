<?php

namespace Core;

/**
 * SupabaseConnection - Simple wrapper for Supabase API calls
 * 
 * This class provides a simple interface for making requests to Supabase
 * that's compatible with the existing Model base class expectations.
 */
class SupabaseConnection
{
    private array $config;
    
    public function __construct(array $config = [])
    {
        // Load Supabase config if not provided
        if (empty($config)) {
            $configFile = dirname(__DIR__, 2) . '/config/supabase.php';
            $this->config = file_exists($configFile) ? require $configFile : [];
        } else {
            $this->config = $config;
        }
    }
    
    /**
     * Select records from Supabase table
     *
     * @param string $table Table name
     * @param array $conditions WHERE conditions
     * @return array Query results
     */
    public function select(string $table, array $conditions = [], array $options = []): array
    {
        $endpoint = $table;

        $queryParts = $this->buildQueryParts($conditions, $options);
        if (!empty($queryParts)) {
            $endpoint .= '?' . implode('&', $queryParts);
        }
        
        // Debug logging for leave requests
        if ($table === TABLE_LEAVE_REQUESTS) {
            error_log("=== SUPABASE SELECT DEBUG (LEAVE) ===");
            error_log("Table: $table");
            error_log("Conditions: " . json_encode($conditions));
            error_log("Endpoint: $endpoint");
        }
        
        $response = $this->makeRequest($endpoint, 'GET');
        
        if ($table === TABLE_LEAVE_REQUESTS) {
            error_log("Response success: " . ($response['success'] ? 'true' : 'false'));
            error_log("Response count: " . count($response['data'] ?? []));
        }
        
        return $response['success'] ? ($response['data'] ?? []) : [];
    }
    
    /**
     * Insert record into Supabase table
     *
     * @param string $table Table name
     * @param array $data Data to insert
     * @return array Insert result
     */
    public function insert(string $table, array $data): array
    {
        error_log("=== SUPABASE INSERT DEBUG ===");
        error_log("Table: $table");
        error_log("Data to insert: " . json_encode($data));
        
        $response = $this->makeRequest($table, 'POST', $data);
        
        error_log("Response success: " . ($response['success'] ? 'true' : 'false'));
        error_log("Response status code: " . ($response['status_code'] ?? 'N/A'));
        error_log("Response data: " . json_encode($response['data'] ?? null));
        error_log("Raw response: " . ($response['raw_response'] ?? 'N/A'));
        
        if (!$response['success']) {
            error_log("Insert failed - response not successful");
            return [];
        }
        
        // Supabase returns an array of records, get the first one
        $responseData = $response['data'] ?? [];
        
        if (is_array($responseData) && !empty($responseData)) {
            // If it's an array of records, return the first one
            if (isset($responseData[0]) && is_array($responseData[0])) {
                error_log("Returning first record from array");
                return $responseData[0];
            }
            // If it's already a single record, return it
            error_log("Returning single record");
            return $responseData;
        }
        
        error_log("Response data is empty or not an array");
        return [];
    }
    
    /**
     * Update records in Supabase table
     *
     * @param string $table Table name
     * @param array $data Data to update
     * @param array $conditions WHERE conditions
     * @return int Number of affected rows (simulated)
     */
    public function update(string $table, array $data, array $conditions): int
    {
        error_log("=== SUPABASE UPDATE DEBUG ===");
        error_log("Table: $table");
        error_log("Data to update: " . json_encode($data));
        error_log("Conditions: " . json_encode($conditions));
        
        $endpoint = $table;

        $matchedRows = $this->select($table, $conditions);
        error_log("Matched rows before update: " . count($matchedRows));
        
        if (empty($matchedRows)) {
            error_log("No rows matched the conditions");
            return 0;
        }

        $queryParts = $this->buildQueryParts($conditions);
        if (!empty($queryParts)) {
            $endpoint .= '?' . implode('&', $queryParts);
        }
        
        error_log("Update endpoint: $endpoint");
        
        $response = $this->makeRequest($endpoint, 'PATCH', $data);
        
        error_log("Update response success: " . ($response['success'] ? 'true' : 'false'));
        error_log("Update response status code: " . ($response['status_code'] ?? 'N/A'));
        error_log("Update response data: " . json_encode($response['data'] ?? null));
        
        if (!$response['success']) {
            error_log("Update failed");
            return 0;
        }

        // Check if response data is a list (PHP 8.0 compatible)
        if (isset($response['data']) && is_array($response['data'])) {
            // Check if array is a list (sequential numeric keys starting from 0)
            $isList = array_keys($response['data']) === range(0, count($response['data']) - 1);
            if ($isList) {
                error_log("Update affected " . count($response['data']) . " rows");
                return count($response['data']);
            }
        }

        error_log("Update affected " . count($matchedRows) . " rows (estimated)");
        return count($matchedRows);
    }
    
    /**
     * Delete records from Supabase table
     *
     * @param string $table Table name
     * @param array $conditions WHERE conditions
     * @return int Number of affected rows (simulated)
     */
    public function delete(string $table, array $conditions): int
    {
        $endpoint = $table;
        $queryParts = $this->buildQueryParts($conditions);
        if (!empty($queryParts)) {
            $endpoint .= '?' . implode('&', $queryParts);
        }
        
        $response = $this->makeRequest($endpoint, 'DELETE');
        return $response['success'] ? 1 : 0; // Simplified return
    }
    
    /**
     * Find record by ID
     *
     * @param string $table Table name
     * @param mixed $id Record ID
     * @param string $primaryKey Primary key column name
     * @return array|null Record data or null if not found
     */
    public function find(string $table, $id, string $primaryKey = 'id'): ?array
    {
        $endpoint = $table . '?' . $primaryKey . '=eq.' . $id;
        $response = $this->makeRequest($endpoint, 'GET');
        
        if ($response['success'] && !empty($response['data'])) {
            return $response['data'][0];
        }
        
        return null;
    }
    
    /**
     * Count records in Supabase table
     *
     * @param string $table Table name
     * @param array $conditions WHERE conditions
     * @return int Record count
     */
    public function count(string $table, array $conditions = []): int
    {
        $queryParts = $this->buildQueryParts($conditions, ['select' => 'count']);
        $endpoint = $table . '?' . implode('&', $queryParts);
        $response = $this->makeRequest($endpoint, 'GET');
        
        if ($response['success'] && !empty($response['data'])) {
            return (int) $response['data'][0]['count'];
        }
        
        return 0;
    }
    
    /**
     * Check if record exists
     *
     * @param string $table Table name
     * @param array $conditions WHERE conditions
     * @return bool True if exists, false otherwise
     */
    public function exists(string $table, array $conditions): bool
    {
        $queryParts = $this->buildQueryParts($conditions, ['select' => 'id', 'limit' => 1]);
        $endpoint = $table . '?' . implode('&', $queryParts);
        $response = $this->makeRequest($endpoint, 'GET');
        
        return $response['success'] && !empty($response['data']);
    }
    
    /**
     * Get configuration
     *
     * @return array Configuration array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    private function buildQueryParts(array $conditions = [], array $options = []): array
    {
        $queryParts = [];

        foreach ($conditions as $key => $value) {
            $queryParts[] = $this->buildCondition($key, $value);
        }

        foreach ($options as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $queryParts[] = $key . '=' . rawurlencode((string) $value);
        }

        return $queryParts;
    }

    private function buildCondition(string $key, $value): string
    {
        if (is_array($value) && array_key_exists('operator', $value)) {
            $operator = strtolower((string) $value['operator']);
            $conditionValue = $value['value'] ?? null;

            // Handle 'in' operator
            if ($operator === 'in') {
                if (is_array($conditionValue)) {
                    $conditionValue = '(' . implode(',', $conditionValue) . ')';
                }

                $conditionValue = trim((string) $conditionValue);
                if ($conditionValue !== '' && $conditionValue[0] !== '(') {
                    $conditionValue = '(' . $conditionValue . ')';
                }

                return "{$key}=in.{$conditionValue}";
            }
            
            // Handle 'between' operator - not directly supported by PostgREST
            // Convert to gte and lte conditions
            if ($operator === 'between' && is_array($conditionValue) && count($conditionValue) === 2) {
                // Return as two separate conditions (will be handled by caller)
                // For now, we'll use gte for the lower bound
                return "{$key}=gte." . $this->formatScalarValue($conditionValue[0]) . "&{$key}=lte." . $this->formatScalarValue($conditionValue[1]);
            }

            // Handle other operators (gte, lte, gt, lt, neq, like, ilike, etc.)
            if (is_array($conditionValue)) {
                // If value is array but not 'in' or 'between', encode as JSON
                return "{$key}={$operator}." . rawurlencode(json_encode($conditionValue));
            }

            return "{$key}={$operator}." . $this->formatScalarValue($conditionValue);
        }

        if (is_bool($value)) {
            return "{$key}=eq." . ($value ? 'true' : 'false');
        }

        if (is_array($value)) {
            return "{$key}=eq." . rawurlencode(json_encode($value));
        }

        return "{$key}=eq." . $this->formatScalarValue($value);
    }

    private function formatScalarValue($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return rawurlencode((string) $value);
    }
    
    /**
     * Make HTTP request to Supabase API
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array|null $data Request data
     * @return array Response
     */
    private function makeRequest(string $endpoint, string $method, ?array $data = null): array
    {
        $url = ($this->config['api_url'] ?? 'https://xtfekjcusnnadfgcrzht.supabase.co/rest/v1/') . $endpoint;
        $apiKey = $this->config['service_key'] ?? $this->config['anon_key'] ?? '';
        
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $apiKey,
            'Authorization: Bearer ' . $apiKey
        ];
        
        // Add Prefer header for POST and PATCH to return the created/updated record
        if ($method === 'POST' || $method === 'PATCH') {
            $headers[] = 'Prefer: return=representation';
        }
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $error,
                'status_code' => 0
            ];
        }
        
        $decodedResponse = json_decode($response, true);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'data' => $decodedResponse,
            'status_code' => $httpCode,
            'raw_response' => $response
        ];
    }
}
