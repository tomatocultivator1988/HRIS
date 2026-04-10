<?php

namespace Models;

use Core\Model;
use Core\ValidationResult;

/**
 * ApplicantEvaluation Model - Represents applicant evaluation entities and handles evaluation data operations
 * 
 * This model handles applicant evaluation data access, validation, and business entity operations.
 * Works with the Supabase applicant_evaluations table and provides methods for CRUD operations.
 */
class ApplicantEvaluation extends Model
{
    protected string $table = 'applicant_evaluations';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'applicant_id',
        'stage_name',
        'score',
        'notes',
        'interviewer_name',
        'evaluation_date',
        'pass_fail'
    ];
    
    protected array $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];
    
    protected array $casts = [
        'score' => 'float',
        'pass_fail' => 'boolean'
    ];
    
    /**
     * Get evaluations by applicant
     *
     * @param string $applicantId Applicant ID
     * @return array Array of evaluations
     */
    public function getByApplicant(string $applicantId): array
    {
        try {
            return $this->where(['applicant_id' => $applicantId])->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByApplicant', ['applicant_id' => $applicantId]);
            return [];
        }
    }
    
    /**
     * Get evaluation by applicant and stage
     *
     * @param string $applicantId Applicant ID
     * @param string $stageName Stage name
     * @return array|null Evaluation data or null if not found
     */
    public function getByApplicantAndStage(string $applicantId, string $stageName): ?array
    {
        try {
            return $this->where([
                'applicant_id' => $applicantId,
                'stage_name' => $stageName
            ])->first();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByApplicantAndStage', [
                'applicant_id' => $applicantId,
                'stage_name' => $stageName
            ]);
            return null;
        }
    }
    
    /**
     * Get evaluations by stage name
     *
     * @param string $stageName Stage name
     * @return array Array of evaluations
     */
    public function getByStage(string $stageName): array
    {
        try {
            return $this->where(['stage_name' => $stageName])->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByStage', ['stage_name' => $stageName]);
            return [];
        }
    }
    
    /**
     * Get passing evaluations
     *
     * @param string|null $applicantId Optional applicant ID filter
     * @return array Array of passing evaluations
     */
    public function getPassing(?string $applicantId = null): array
    {
        try {
            $conditions = ['pass_fail' => true];
            
            if ($applicantId !== null) {
                $conditions['applicant_id'] = $applicantId;
            }
            
            return $this->where($conditions)->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getPassing', ['applicant_id' => $applicantId]);
            return [];
        }
    }
    
    /**
     * Get failing evaluations
     *
     * @param string|null $applicantId Optional applicant ID filter
     * @return array Array of failing evaluations
     */
    public function getFailing(?string $applicantId = null): array
    {
        try {
            $conditions = ['pass_fail' => false];
            
            if ($applicantId !== null) {
                $conditions['applicant_id'] = $applicantId;
            }
            
            return $this->where($conditions)->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getFailing', ['applicant_id' => $applicantId]);
            return [];
        }
    }
    
    /**
     * Calculate average score for an applicant
     *
     * @param string $applicantId Applicant ID
     * @return float|null Average score or null if no evaluations
     */
    public function calculateAverageScore(string $applicantId): ?float
    {
        try {
            $evaluations = $this->getByApplicant($applicantId);
            
            if (empty($evaluations)) {
                return null;
            }
            
            $totalScore = 0;
            foreach ($evaluations as $evaluation) {
                $totalScore += floatval($evaluation['score']);
            }
            
            return $totalScore / count($evaluations);
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'calculateAverageScore', ['applicant_id' => $applicantId]);
            return null;
        }
    }
    
    /**
     * Check if all stages are complete for an applicant
     *
     * @param string $applicantId Applicant ID
     * @return bool True if all 4 stages complete, false otherwise
     */
    public function allStagesComplete(string $applicantId): bool
    {
        try {
            $evaluations = $this->getByApplicant($applicantId);
            
            $requiredStages = ['Screening', 'Interview 1', 'Interview 2', 'Final Interview'];
            $completedStages = array_column($evaluations, 'stage_name');
            
            foreach ($requiredStages as $stage) {
                if (!in_array($stage, $completedStages)) {
                    return false;
                }
            }
            
            return true;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'allStagesComplete', ['applicant_id' => $applicantId]);
            return false;
        }
    }
    
    /**
     * Check if all stages are passed for an applicant
     *
     * @param string $applicantId Applicant ID
     * @return bool True if all stages passed, false otherwise
     */
    public function allStagesPassed(string $applicantId): bool
    {
        try {
            $evaluations = $this->getByApplicant($applicantId);
            
            if (empty($evaluations)) {
                return false;
            }
            
            foreach ($evaluations as $evaluation) {
                if (!$evaluation['pass_fail']) {
                    return false;
                }
            }
            
            return true;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'allStagesPassed', ['applicant_id' => $applicantId]);
            return false;
        }
    }
    
    /**
     * Validate evaluation data before database operations
     *
     * @param array $data Evaluation data to validate
     * @param mixed $id Evaluation ID for update operations (null for create)
     * @return ValidationResult Validation result
     */
    protected function validate(array $data, $id = null): ValidationResult
    {
        $errors = [];
        
        // Required field validation for create operations
        if ($id === null) {
            $requiredFields = ['applicant_id', 'stage_name', 'score', 'interviewer_name', 'evaluation_date', 'pass_fail'];
            
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }
        }
        
        // Stage name validation
        if (isset($data['stage_name'])) {
            $validStages = ['Screening', 'Interview 1', 'Interview 2', 'Final Interview'];
            if (!in_array($data['stage_name'], $validStages)) {
                $errors['stage_name'] = 'Invalid stage name. Must be one of: ' . implode(', ', $validStages);
            }
        }
        
        // Score validation
        if (isset($data['score'])) {
            $score = floatval($data['score']);
            if ($score < 0 || $score > 100) {
                $errors['score'] = 'Score must be between 0 and 100';
            }
        }
        
        // Interviewer name validation
        if (isset($data['interviewer_name'])) {
            $interviewerName = trim($data['interviewer_name']);
            if (strlen($interviewerName) < 1 || strlen($interviewerName) > 255) {
                $errors['interviewer_name'] = 'Interviewer name must be between 1 and 255 characters';
            }
        }
        
        // Evaluation date validation
        if (isset($data['evaluation_date'])) {
            $evaluationDate = \DateTime::createFromFormat('Y-m-d', $data['evaluation_date']);
            if (!$evaluationDate || $evaluationDate->format('Y-m-d') !== $data['evaluation_date']) {
                $errors['evaluation_date'] = 'Invalid evaluation date format (Y-m-d required)';
            } elseif ($evaluationDate > new \DateTime()) {
                $errors['evaluation_date'] = 'Evaluation date cannot be in the future';
            }
        }
        
        // Pass/fail validation
        if (isset($data['pass_fail']) && !is_bool($data['pass_fail'])) {
            // Try to convert string to boolean
            if (is_string($data['pass_fail'])) {
                $data['pass_fail'] = filter_var($data['pass_fail'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($data['pass_fail'] === null) {
                    $errors['pass_fail'] = 'Pass/fail must be a boolean value';
                }
            }
        }
        
        // Sanitize data
        $sanitizedData = $this->sanitizeEvaluationData($data);
        
        return new ValidationResult(empty($errors), $errors, $sanitizedData);
    }
    
    /**
     * Sanitize evaluation data
     *
     * @param array $data Raw evaluation data
     * @return array Sanitized data
     */
    private function sanitizeEvaluationData(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
            }
            
            // Cast score to float
            if ($key === 'score' && is_numeric($value)) {
                $value = floatval($value);
            }
            
            // Cast pass_fail to boolean
            if ($key === 'pass_fail' && !is_bool($value)) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
            
            $sanitized[$key] = $value;
        }
        
        return $sanitized;
    }
}
