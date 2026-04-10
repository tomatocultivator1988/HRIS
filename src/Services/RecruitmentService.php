<?php

namespace Services;

use Core\ValidationException;
use Core\NotFoundException;
use Models\JobPosting;
use Models\Applicant;
use Models\ApplicantEvaluation;
use Services\EmployeeService;
use Exception;

/**
 * RecruitmentService - Handles recruitment business logic
 * 
 * This service encapsulates all recruitment-related business logic including
 * job posting management, applicant tracking, evaluation scoring, and hiring workflow.
 */
class RecruitmentService
{
    private JobPosting $jobPostingModel;
    private Applicant $applicantModel;
    private ApplicantEvaluation $evaluationModel;
    private EmployeeService $employeeService;
    
    public function __construct(
        JobPosting $jobPostingModel,
        Applicant $applicantModel,
        ApplicantEvaluation $evaluationModel,
        EmployeeService $employeeService
    ) {
        $this->jobPostingModel = $jobPostingModel;
        $this->applicantModel = $applicantModel;
        $this->evaluationModel = $evaluationModel;
        $this->employeeService = $employeeService;
    }
    
    // ==================== JOB POSTING METHODS (Task 3) ====================
    
    /**
     * Create a new job posting
     * 
     * @param array $data Job posting data
     * @return array Created job posting
     * @throws ValidationException
     */
    public function createJobPosting(array $data): array
    {
        try {
            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = 'Open';
            }
            
            // Create job posting (validation happens in model)
            $jobPosting = $this->jobPostingModel->create($data);
            
            // Handle array wrapping from model
            if (isset($jobPosting[0]) && is_array($jobPosting[0])) {
                $jobPosting = $jobPosting[0];
            }
            
            return $jobPosting;
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('RecruitmentService::createJobPosting Error: ' . $e->getMessage());
            throw new Exception('Failed to create job posting: ' . $e->getMessage());
        }
    }
    
    /**
     * Update existing job posting
     * 
     * @param string $id Job posting ID
     * @param array $data Update data
     * @return array Updated job posting
     * @throws NotFoundException, ValidationException
     */
    public function updateJobPosting(string $id, array $data): array
    {
        try {
            // Check if job posting exists
            $existingJobPosting = $this->jobPostingModel->find($id);
            
            if (!$existingJobPosting) {
                throw new NotFoundException('Job posting not found');
            }
            
            // Update job posting (validation happens in model)
            $success = $this->jobPostingModel->update($id, $data);
            
            if (!$success) {
                throw new Exception('Failed to update job posting');
            }
            
            // Get updated job posting
            $updatedJobPosting = $this->jobPostingModel->find($id);
            
            return $updatedJobPosting;
            
        } catch (NotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('RecruitmentService::updateJobPosting Error: ' . $e->getMessage());
            throw new Exception('Failed to update job posting: ' . $e->getMessage());
        }
    }
    
    /**
     * Get all job postings with filtering
     * 
     * @param array $filters Filter parameters (status, department)
     * @return array Job postings list
     */
    public function getJobPostings(array $filters = []): array
    {
        try {
            $conditions = [];
            
            // Status filter
            if (!empty($filters['status'])) {
                $conditions['status'] = $filters['status'];
            }
            
            // Department filter
            if (!empty($filters['department'])) {
                $conditions['department'] = $filters['department'];
            }
            
            // Get job postings
            if (empty($conditions)) {
                $jobPostings = $this->jobPostingModel->all();
            } else {
                $jobPostings = $this->jobPostingModel->where($conditions)->get();
            }
            
            return $jobPostings;
            
        } catch (Exception $e) {
            error_log('RecruitmentService::getJobPostings Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get job posting by ID
     * 
     * @param string $id Job posting ID
     * @return array Job posting data
     * @throws NotFoundException
     */
    public function getJobPostingById(string $id): array
    {
        try {
            $jobPosting = $this->jobPostingModel->find($id);
            
            if (!$jobPosting) {
                throw new NotFoundException('Job posting not found');
            }
            
            return $jobPosting;
            
        } catch (NotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('RecruitmentService::getJobPostingById Error: ' . $e->getMessage());
            throw new Exception('Failed to fetch job posting: ' . $e->getMessage());
        }
    }
    
    // ==================== APPLICANT METHODS (Task 4) ====================
    
    /**
     * Create a new applicant
     * 
     * @param array $data Applicant data
     * @return array Created applicant
     * @throws ValidationException
     */
    public function createApplicant(array $data): array
    {
        try {
            // Validate job posting exists
            if (!empty($data['job_posting_id'])) {
                $jobPosting = $this->jobPostingModel->find($data['job_posting_id']);
                if (!$jobPosting) {
                    throw new ValidationException('Invalid job posting', ['job_posting_id' => 'Job posting not found']);
                }
            }
            
            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = 'Applied';
            }
            
            // Set default is_active if not provided
            if (!isset($data['is_active'])) {
                $data['is_active'] = true;
            }
            
            // Create applicant (validation happens in model)
            $applicant = $this->applicantModel->create($data);
            
            // Handle array wrapping from model
            if (isset($applicant[0]) && is_array($applicant[0])) {
                $applicant = $applicant[0];
            }
            
            return $applicant;
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('RecruitmentService::createApplicant Error: ' . $e->getMessage());
            throw new Exception('Failed to create applicant: ' . $e->getMessage());
        }
    }
    
    /**
     * Update existing applicant
     * 
     * @param string $id Applicant ID
     * @param array $data Update data
     * @return array Updated applicant
     * @throws NotFoundException, ValidationException
     */
    public function updateApplicant(string $id, array $data): array
    {
        try {
            // Check if applicant exists
            $existingApplicant = $this->applicantModel->find($id);
            
            if (!$existingApplicant) {
                throw new NotFoundException('Applicant not found');
            }
            
            // Validate job posting if being updated
            if (!empty($data['job_posting_id']) && $data['job_posting_id'] !== $existingApplicant['job_posting_id']) {
                $jobPosting = $this->jobPostingModel->find($data['job_posting_id']);
                if (!$jobPosting) {
                    throw new ValidationException('Invalid job posting', ['job_posting_id' => 'Job posting not found']);
                }
            }
            
            // Update applicant (validation happens in model)
            $success = $this->applicantModel->update($id, $data);
            
            if (!$success) {
                throw new Exception('Failed to update applicant');
            }
            
            // Get updated applicant
            $updatedApplicant = $this->applicantModel->find($id);
            
            return $updatedApplicant;
            
        } catch (NotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('RecruitmentService::updateApplicant Error: ' . $e->getMessage());
            throw new Exception('Failed to update applicant: ' . $e->getMessage());
        }
    }
    
    /**
     * Get all applicants with filtering
     * 
     * @param array $filters Filter parameters (job_posting_id, status)
     * @return array Applicants list
     */
    public function getApplicants(array $filters = []): array
    {
        try {
            $conditions = [];
            
            // Job posting filter
            if (!empty($filters['job_posting_id'])) {
                $conditions['job_posting_id'] = $filters['job_posting_id'];
            }
            
            // Status filter
            if (!empty($filters['status'])) {
                $conditions['status'] = $filters['status'];
            }
            
            // Get applicants
            if (empty($conditions)) {
                $applicants = $this->applicantModel->all();
            } else {
                $applicants = $this->applicantModel->where($conditions)->get();
            }
            
            // Add final_score to each applicant
            foreach ($applicants as &$applicant) {
                $applicant['final_score'] = $this->calculateFinalScore($applicant['id']);
            }
            
            return $applicants;
            
        } catch (Exception $e) {
            error_log('RecruitmentService::getApplicants Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get applicant by ID with evaluations
     * 
     * @param string $id Applicant ID
     * @return array Applicant data with evaluations and final score
     * @throws NotFoundException
     */
    public function getApplicantById(string $id): array
    {
        try {
            $applicant = $this->applicantModel->find($id);
            
            if (!$applicant) {
                throw new NotFoundException('Applicant not found');
            }
            
            // Get evaluations
            $evaluations = $this->evaluationModel->getByApplicant($id);
            
            // Calculate final score
            $finalScore = $this->calculateFinalScore($id);
            
            // Add evaluations and final score to applicant data
            $applicant['evaluations'] = $evaluations;
            $applicant['final_score'] = $finalScore;
            
            return $applicant;
            
        } catch (NotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('RecruitmentService::getApplicantById Error: ' . $e->getMessage());
            throw new Exception('Failed to fetch applicant: ' . $e->getMessage());
        }
    }
    
    // ==================== EVALUATION METHODS (Task 5) ====================
    
    /**
     * Create or update evaluation for an applicant stage
     * 
     * @param string $applicantId Applicant ID
     * @param array $data Evaluation data
     * @return array Created/updated evaluation
     * @throws ValidationException, NotFoundException
     */
    public function saveEvaluation(string $applicantId, array $data): array
    {
        try {
            // Log incoming data for debugging
            error_log('RecruitmentService::saveEvaluation - Applicant ID: ' . $applicantId);
            error_log('RecruitmentService::saveEvaluation - Data: ' . json_encode($data));
            
            // Validate applicant exists
            $applicant = $this->applicantModel->find($applicantId);
            if (!$applicant) {
                throw new NotFoundException('Applicant not found');
            }
            
            // Set applicant_id in data
            $data['applicant_id'] = $applicantId;
            
            // Check if evaluation already exists for this stage
            $existingEvaluation = null;
            if (!empty($data['stage_name'])) {
                $existingEvaluation = $this->evaluationModel->getByApplicantAndStage($applicantId, $data['stage_name']);
                error_log('RecruitmentService::saveEvaluation - Existing evaluation: ' . json_encode($existingEvaluation));
            }
            
            if ($existingEvaluation) {
                // Update existing evaluation
                error_log('RecruitmentService::saveEvaluation - Updating existing evaluation');
                $success = $this->evaluationModel->update($existingEvaluation['id'], $data);
                
                if (!$success) {
                    throw new Exception('Failed to update evaluation');
                }
                
                $evaluation = $this->evaluationModel->find($existingEvaluation['id']);
            } else {
                // Create new evaluation (validation happens in model)
                error_log('RecruitmentService::saveEvaluation - Creating new evaluation');
                $evaluation = $this->evaluationModel->create($data);
                error_log('RecruitmentService::saveEvaluation - Created evaluation: ' . json_encode($evaluation));
                
                // Handle array wrapping from model
                if (isset($evaluation[0]) && is_array($evaluation[0])) {
                    $evaluation = $evaluation[0];
                }
            }
            
            error_log('RecruitmentService::saveEvaluation - Final evaluation: ' . json_encode($evaluation));
            return $evaluation;
            
        } catch (NotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            error_log('RecruitmentService::saveEvaluation - Validation error: ' . json_encode($e->getErrors()));
            throw $e;
        } catch (Exception $e) {
            error_log('RecruitmentService::saveEvaluation Error: ' . $e->getMessage());
            throw new Exception('Failed to save evaluation: ' . $e->getMessage());
        }
    }
    
    /**
     * Get all evaluations for an applicant
     * 
     * @param string $applicantId Applicant ID
     * @return array Evaluations list
     * @throws NotFoundException
     */
    public function getEvaluations(string $applicantId): array
    {
        try {
            // Validate applicant exists
            $applicant = $this->applicantModel->find($applicantId);
            if (!$applicant) {
                throw new NotFoundException('Applicant not found');
            }
            
            // Get evaluations
            $evaluations = $this->evaluationModel->getByApplicant($applicantId);
            
            return $evaluations;
            
        } catch (NotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('RecruitmentService::getEvaluations Error: ' . $e->getMessage());
            throw new Exception('Failed to fetch evaluations: ' . $e->getMessage());
        }
    }
    
    /**
     * Calculate final score for an applicant
     * 
     * @param string $applicantId Applicant ID
     * @return float|null Final score (average of all stages) or null if no evaluations
     */
    public function calculateFinalScore(string $applicantId): ?float
    {
        try {
            return $this->evaluationModel->calculateAverageScore($applicantId);
        } catch (Exception $e) {
            error_log('RecruitmentService::calculateFinalScore Error: ' . $e->getMessage());
            return null;
        }
    }
    
    // ==================== HIRING METHOD (Task 6) ====================
    
    /**
     * Hire an applicant by creating an employee record
     * 
     * This method:
     * 1. Validates hiring eligibility (all stages complete, passing scores)
     * 2. Calls EmployeeService.createEmployee() with applicant data
     * 3. Updates applicant status to 'Hired' and links to employee
     * 4. Decrements job posting openings
     * 5. Auto-closes job posting if openings reach zero
     * 
     * @param string $applicantId Applicant ID
     * @param float $minimumPassingScore Minimum required final score (default: 70.0)
     * @return array Hiring result with employee and applicant data
     * @throws ValidationException, NotFoundException, Exception
     */
    public function hireApplicant(string $applicantId, float $minimumPassingScore = 70.0): array
    {
        try {
            // Get applicant
            $applicant = $this->applicantModel->find($applicantId);
            if (!$applicant) {
                throw new NotFoundException('Applicant not found');
            }
            
            // Check if already hired
            if ($applicant['status'] === 'Hired') {
                throw new ValidationException('Applicant is already hired', ['status' => 'Applicant is already hired']);
            }
            
            // Get job posting
            $jobPosting = $this->jobPostingModel->find($applicant['job_posting_id']);
            if (!$jobPosting) {
                throw new NotFoundException('Job posting not found');
            }
            
            // Validate hiring eligibility
            $this->validateHiringEligibility($applicantId, $minimumPassingScore);
            
            // Check job posting has available openings
            if ($jobPosting['num_openings'] <= 0) {
                throw new ValidationException('No available openings for this position', ['num_openings' => 'No available openings']);
            }
            
            // Prepare employee data from applicant
            $employeeData = [
                'first_name' => $applicant['first_name'],
                'last_name' => $applicant['last_name'],
                'work_email' => $applicant['work_email'],
                'mobile_number' => $applicant['mobile_number'],
                'department' => $applicant['department'],
                'position' => $applicant['position'],
                'employment_status' => $applicant['employment_status'],
                'date_hired' => date('Y-m-d')
            ];
            
            // Call EmployeeService.createEmployee()
            // Note: EmployeeService handles its own transaction for employee creation
            $employee = $this->employeeService->createEmployee($employeeData);
            
            // Update applicant status to 'Hired' and link to employee
            $applicantUpdateData = [
                'status' => 'Hired',
                'employee_id' => $employee['id']
            ];
            
            $success = $this->applicantModel->update($applicantId, $applicantUpdateData);
            if (!$success) {
                error_log('RecruitmentService::hireApplicant Warning: Failed to update applicant status after employee creation');
            }
            
            // Decrement job posting openings
            $newOpenings = $jobPosting['num_openings'] - 1;
            $jobPostingUpdateData = ['num_openings' => $newOpenings];
            
            // Auto-close job posting if openings reach zero
            if ($newOpenings === 0) {
                $jobPostingUpdateData['status'] = 'Closed';
            }
            
            $success = $this->jobPostingModel->update($jobPosting['id'], $jobPostingUpdateData);
            if (!$success) {
                error_log('RecruitmentService::hireApplicant Warning: Failed to update job posting openings');
            }
            
            // Get updated data
            $updatedApplicant = $this->applicantModel->find($applicantId);
            $updatedJobPosting = $this->jobPostingModel->find($jobPosting['id']);
            $finalScore = $this->calculateFinalScore($applicantId);
            
            // Add final score to applicant data
            $updatedApplicant['final_score'] = $finalScore;
            
            return [
                'employee' => $employee,
                'applicant' => $updatedApplicant,
                'job_posting' => $updatedJobPosting
            ];
            
        } catch (NotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('RecruitmentService::hireApplicant Error: ' . $e->getMessage());
            throw new Exception('Failed to hire applicant: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate hiring eligibility for an applicant
     * 
     * @param string $applicantId Applicant ID
     * @param float $minimumPassingScore Minimum required final score
     * @throws ValidationException
     */
    private function validateHiringEligibility(string $applicantId, float $minimumPassingScore): void
    {
        $errors = [];
        
        // Get all evaluations
        $evaluations = $this->evaluationModel->getByApplicant($applicantId);
        
        // Check all 4 stages have evaluations
        $requiredStages = ['Screening', 'Interview 1', 'Interview 2', 'Final Interview'];
        $completedStages = array_column($evaluations, 'stage_name');
        
        foreach ($requiredStages as $stage) {
            if (!in_array($stage, $completedStages)) {
                $errors['evaluations'] = 'All 4 evaluation stages must be completed';
                break;
            }
        }
        
        // Check each evaluation has all required fields
        foreach ($evaluations as $evaluation) {
            if (empty($evaluation['score']) && $evaluation['score'] !== 0) {
                $errors['evaluations'] = 'All evaluations must have a score';
                break;
            }
            if (empty($evaluation['interviewer_name'])) {
                $errors['evaluations'] = 'All evaluations must have an interviewer name';
                break;
            }
            if (empty($evaluation['evaluation_date'])) {
                $errors['evaluations'] = 'All evaluations must have an evaluation date';
                break;
            }
            if (!isset($evaluation['pass_fail'])) {
                $errors['evaluations'] = 'All evaluations must have a pass/fail status';
                break;
            }
        }
        
        // Check all stages marked as Pass
        foreach ($evaluations as $evaluation) {
            if (!$evaluation['pass_fail']) {
                $errors['pass_fail'] = 'All evaluation stages must be marked as Pass';
                break;
            }
        }
        
        // Calculate and check final score
        $finalScore = $this->calculateFinalScore($applicantId);
        
        if ($finalScore === null) {
            $errors['final_score'] = 'Unable to calculate final score';
        } elseif ($finalScore < $minimumPassingScore) {
            $errors['final_score'] = "Final score ({$finalScore}) is below minimum passing score ({$minimumPassingScore})";
        }
        
        // Throw validation exception if any errors
        if (!empty($errors)) {
            throw new ValidationException('Hiring eligibility validation failed', $errors);
        }
    }
}
