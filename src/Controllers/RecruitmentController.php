<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\ValidationException;
use Core\AuthenticationException;
use Core\AuthorizationException;
use Core\NotFoundException;
use Core\View;
use Services\RecruitmentService;

/**
 * RecruitmentController - Handles HTTP requests for recruitment management
 * 
 * This controller coordinates recruitment-related operations, handling HTTP requests
 * and delegating business logic to the RecruitmentService. Supports both web views
 * and API responses with consistent error handling.
 */
class RecruitmentController extends Controller
{
    private RecruitmentService $recruitmentService;
    private View $view;
    
    /**
     * Constructor - Initialize with RecruitmentService dependency
     */
    public function __construct(\Core\Container $container)
    {
        parent::__construct($container);
        $this->recruitmentService = $container->resolve(RecruitmentService::class);
        $this->view = new View();
    }
    
    // ==================== JOB POSTING ENDPOINTS (Task 7) ====================
    
    /**
     * List all job postings
     * GET /api/recruitment/jobs
     * Query params: status, department, limit, offset
     */
    public function listJobs(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            // Get query parameters
            $filters = [
                'status' => $this->getQueryParam('status', ''),
                'department' => $this->getQueryParam('department', ''),
                'limit' => min(max(intval($this->getQueryParam('limit', 50)), 1), 100),
                'offset' => max(intval($this->getQueryParam('offset', 0)), 0)
            ];
            
            // Validate status if provided
            if (!empty($filters['status'])) {
                $validStatuses = ['Open', 'Closed', 'On Hold'];
                if (!in_array($filters['status'], $validStatuses)) {
                    return $this->error('Invalid status. Must be one of: ' . implode(', ', $validStatuses), 400);
                }
            }
            
            // Get job postings from service
            $jobPostings = $this->recruitmentService->getJobPostings($filters);
            
            // Log activity
            $this->logActivity('VIEW_JOB_POSTINGS', [
                'filters' => $filters,
                'results_count' => count($jobPostings)
            ]);
            
            return $this->success([
                'job_postings' => $jobPostings,
                'count' => count($jobPostings)
            ], 'Job postings retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get specific job posting
     * GET /api/recruitment/jobs/{id}
     */
    public function getJob(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $jobId = $this->getRouteParam('id');
            
            if (empty($jobId)) {
                return $this->error('Job posting ID is required', 400);
            }
            
            // Validate UUID format
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $jobId)) {
                return $this->error('Invalid job posting ID format', 400);
            }
            
            $jobPosting = $this->recruitmentService->getJobPostingById($jobId);
            
            // Log activity
            $this->logActivity('VIEW_JOB_POSTING', ['job_posting_id' => $jobId]);
            
            return $this->success(['job_posting' => $jobPosting], 'Job posting retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Create new job posting
     * POST /api/recruitment/jobs
     * Body: {job_title, department, position, num_openings, description, status}
     */
    public function createJob(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $data = $this->getJsonData();
            
            if (empty($data)) {
                return $this->error('Request body is required', 400);
            }
            
            // Create job posting through service
            $jobPosting = $this->recruitmentService->createJobPosting($data);
            
            // Log activity
            $this->logActivity('CREATE_JOB_POSTING', [
                'job_posting_id' => $jobPosting['id'],
                'job_title' => $jobPosting['job_title']
            ]);
            
            return $this->success(['job_posting' => $jobPosting], 'Job posting created successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (ValidationException $e) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Update job posting
     * PUT /api/recruitment/jobs/{id}
     * Body: {job_title?, department?, position?, num_openings?, description?, status?}
     */
    public function updateJob(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $jobId = $this->getRouteParam('id');
            $data = $this->getJsonData();
            
            if (empty($jobId)) {
                return $this->error('Job posting ID is required', 400);
            }
            
            // Validate UUID format
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $jobId)) {
                return $this->error('Invalid job posting ID format', 400);
            }
            
            if (empty($data)) {
                return $this->error('Request body is required', 400);
            }
            
            // Update job posting through service
            $jobPosting = $this->recruitmentService->updateJobPosting($jobId, $data);
            
            // Log activity
            $this->logActivity('UPDATE_JOB_POSTING', [
                'job_posting_id' => $jobId,
                'fields_updated' => array_keys($data)
            ]);
            
            return $this->success(['job_posting' => $jobPosting], 'Job posting updated successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (ValidationException $e) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    // ==================== APPLICANT ENDPOINTS (Task 8) ====================
    
    /**
     * List all applicants
     * GET /api/recruitment/applicants
     * Query params: job_posting_id, status, limit, offset
     */
    public function listApplicants(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            // Get query parameters
            $filters = [
                'job_posting_id' => $this->getQueryParam('job_posting_id', ''),
                'status' => $this->getQueryParam('status', ''),
                'limit' => min(max(intval($this->getQueryParam('limit', 50)), 1), 100),
                'offset' => max(intval($this->getQueryParam('offset', 0)), 0)
            ];
            
            // Validate status if provided
            if (!empty($filters['status'])) {
                $validStatuses = ['Applied', 'In Progress', 'Passed', 'Failed', 'Hired'];
                if (!in_array($filters['status'], $validStatuses)) {
                    return $this->error('Invalid status. Must be one of: ' . implode(', ', $validStatuses), 400);
                }
            }
            
            // Get applicants from service
            $applicants = $this->recruitmentService->getApplicants($filters);
            
            // Log activity
            $this->logActivity('VIEW_APPLICANTS', [
                'filters' => $filters,
                'results_count' => count($applicants)
            ]);
            
            return $this->success([
                'applicants' => $applicants,
                'count' => count($applicants)
            ], 'Applicants retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get specific applicant with evaluations
     * GET /api/recruitment/applicants/{id}
     */
    public function getApplicant(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $applicantId = $this->getRouteParam('id');
            
            if (empty($applicantId)) {
                return $this->error('Applicant ID is required', 400);
            }
            
            // Validate UUID format
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $applicantId)) {
                return $this->error('Invalid applicant ID format', 400);
            }
            
            $applicant = $this->recruitmentService->getApplicantById($applicantId);
            
            // Log activity
            $this->logActivity('VIEW_APPLICANT', ['applicant_id' => $applicantId]);
            
            return $this->success(['applicant' => $applicant], 'Applicant retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Create new applicant
     * POST /api/recruitment/applicants
     * Body: {job_posting_id, first_name, last_name, work_email, mobile_number, 
     *        department, position, employment_status}
     */
    public function createApplicant(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $data = $this->getJsonData();
            
            if (empty($data)) {
                return $this->error('Request body is required', 400);
            }
            
            // Create applicant through service
            $applicant = $this->recruitmentService->createApplicant($data);
            
            // Log activity
            $this->logActivity('CREATE_APPLICANT', [
                'applicant_id' => $applicant['id'],
                'applicant_name' => $applicant['first_name'] . ' ' . $applicant['last_name']
            ]);
            
            return $this->success(['applicant' => $applicant], 'Applicant created successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (ValidationException $e) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Update applicant
     * PUT /api/recruitment/applicants/{id}
     * Body: {first_name?, last_name?, work_email?, mobile_number?, 
     *        department?, position?, employment_status?}
     */
    public function updateApplicant(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $applicantId = $this->getRouteParam('id');
            $data = $this->getJsonData();
            
            if (empty($applicantId)) {
                return $this->error('Applicant ID is required', 400);
            }
            
            // Validate UUID format
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $applicantId)) {
                return $this->error('Invalid applicant ID format', 400);
            }
            
            if (empty($data)) {
                return $this->error('Request body is required', 400);
            }
            
            // Update applicant through service
            $applicant = $this->recruitmentService->updateApplicant($applicantId, $data);
            
            // Log activity
            $this->logActivity('UPDATE_APPLICANT', [
                'applicant_id' => $applicantId,
                'fields_updated' => array_keys($data)
            ]);
            
            return $this->success(['applicant' => $applicant], 'Applicant updated successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (ValidationException $e) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    // ==================== EVALUATION AND HIRING ENDPOINTS (Task 9) ====================
    
    /**
     * Get evaluations for an applicant
     * GET /api/recruitment/applicants/{id}/evaluations
     */
    public function getEvaluations(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $applicantId = $this->getRouteParam('id');
            
            if (empty($applicantId)) {
                return $this->error('Applicant ID is required', 400);
            }
            
            // Validate UUID format
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $applicantId)) {
                return $this->error('Invalid applicant ID format', 400);
            }
            
            $evaluations = $this->recruitmentService->getEvaluations($applicantId);
            
            // Log activity
            $this->logActivity('VIEW_EVALUATIONS', ['applicant_id' => $applicantId]);
            
            return $this->success([
                'evaluations' => $evaluations,
                'count' => count($evaluations)
            ], 'Evaluations retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Create or update evaluation
     * POST /api/recruitment/evaluations
     * Body: {applicant_id, stage_name, score, notes, interviewer_name, 
     *        evaluation_date, pass_fail}
     */
    public function saveEvaluation(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $data = $this->getJsonData();
            
            if (empty($data)) {
                return $this->error('Request body is required', 400);
            }
            
            if (empty($data['applicant_id'])) {
                return $this->error('Applicant ID is required', 400);
            }
            
            $applicantId = $data['applicant_id'];
            
            // Validate UUID format
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $applicantId)) {
                return $this->error('Invalid applicant ID format', 400);
            }
            
            // Save evaluation through service
            $evaluation = $this->recruitmentService->saveEvaluation($applicantId, $data);
            
            // Log activity
            $this->logActivity('SAVE_EVALUATION', [
                'applicant_id' => $applicantId,
                'stage_name' => $data['stage_name'] ?? 'unknown'
            ]);
            
            return $this->success(['evaluation' => $evaluation], 'Evaluation saved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (ValidationException $e) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Hire an applicant
     * POST /api/recruitment/applicants/{id}/hire
     * Body: {minimum_passing_score?} (optional, defaults to 70.0)
     */
    public function hireApplicant(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $applicantId = $this->getRouteParam('id');
            
            if (empty($applicantId)) {
                return $this->error('Applicant ID is required', 400);
            }
            
            // Validate UUID format
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $applicantId)) {
                return $this->error('Invalid applicant ID format', 400);
            }
            
            // Get optional minimum passing score from request body
            $data = $this->getJsonData();
            $minimumPassingScore = isset($data['minimum_passing_score']) ? floatval($data['minimum_passing_score']) : 70.0;
            
            // Validate minimum passing score
            if ($minimumPassingScore < 0 || $minimumPassingScore > 100) {
                return $this->error('Minimum passing score must be between 0 and 100', 400);
            }
            
            // Hire applicant through service
            $result = $this->recruitmentService->hireApplicant($applicantId, $minimumPassingScore);
            
            // Log activity
            $this->logActivity('HIRE_APPLICANT', [
                'applicant_id' => $applicantId,
                'employee_id' => $result['employee']['id'],
                'employee_number' => $result['employee']['employee_id']
            ]);
            
            return $this->success($result, 'Applicant hired successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (ValidationException $e) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    // ==================== VIEW ENDPOINT (Task 10) ====================
    
    /**
     * Display recruitment management interface
     * GET /recruitment
     */
    public function indexView(Request $request): Response
    {
        try {
            // Authentication is handled by JavaScript on the client side
            // No backend auth required for web pages
            
            // Render the recruitment index view directly without base layout
            ob_start();
            include __DIR__ . '/../Views/recruitment/index.php';
            $html = ob_get_clean();
            
            return new Response($html, 200, ['Content-Type' => 'text/html']);
            
        } catch (AuthenticationException $e) {
            return $this->redirectToLogin();
        } catch (AuthorizationException $e) {
            return $this->accessDenied();
        } catch (\Exception $e) {
            return $this->handleViewException($e);
        }
    }
    
    // ==================== HELPER METHODS ====================
    
    /**
     * Redirect to login page
     */
    private function redirectToLogin(): Response
    {
        return $this->redirect('/login');
    }
    
    /**
     * Return access denied page
     */
    private function accessDenied(): Response
    {
        $html = $this->view->render('errors/403', [
            'title' => 'Access Denied - HRIS MVP',
            'message' => 'You do not have permission to access this resource.'
        ]);
        
        return new Response($html, 403, ['Content-Type' => 'text/html']);
    }
    
    /**
     * Handle view exceptions
     */
    private function handleViewException(\Exception $e): Response
    {
        error_log('View Exception: ' . $e->getMessage());
        
        $html = $this->view->render('errors/500', [
            'title' => 'Server Error - HRIS MVP',
            'message' => 'An internal server error occurred.'
        ]);
        
        return new Response($html, 500, ['Content-Type' => 'text/html']);
    }
}
