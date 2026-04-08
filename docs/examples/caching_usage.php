<?php

/**
 * Caching Usage Examples
 * 
 * This file demonstrates how to use the caching features in controllers and services.
 */

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Traits\Cacheable;

/**
 * Example 1: Using Cacheable trait in a controller
 */
class DashboardController extends Controller
{
    use Cacheable;
    
    /**
     * Get dashboard metrics with caching
     */
    public function metrics(Request $request): Response
    {
        // Cache dashboard metrics for 5 minutes
        $metrics = $this->remember('dashboard:metrics', function() {
            return [
                'total_employees' => $this->employeeService->count(),
                'present_today' => $this->attendanceService->getPresentCount(),
                'on_leave' => $this->leaveService->getOnLeaveCount(),
                'pending_requests' => $this->leaveService->getPendingCount()
            ];
        }, 300);
        
        return $this->json($metrics);
    }
    
    /**
     * Get employee list with query caching
     */
    public function employees(Request $request): Response
    {
        $department = $request->getQueryParameter('department');
        
        // Generate cache key based on parameters
        $cacheKey = $this->cacheKey('employees:list', [
            'department' => $department
        ]);
        
        // Cache query results for 10 minutes
        $employees = $this->cachedQuery($cacheKey, function() use ($department) {
            if ($department) {
                return $this->employeeService->getByDepartment($department);
            }
            return $this->employeeService->getAll();
        }, 600);
        
        return $this->json($employees);
    }
    
    /**
     * Update employee and invalidate cache
     */
    public function updateEmployee(Request $request): Response
    {
        $id = $request->getRouteParameter('id');
        $data = $request->getJsonData();
        
        $employee = $this->employeeService->update($id, $data);
        
        // Invalidate related caches
        $this->invalidateCache("employee:{$id}");
        $this->invalidateQueryCache('employees:list');
        
        return $this->json($employee);
    }
}

/**
 * Example 2: Using HTTP caching headers
 */
class EmployeeController extends Controller
{
    /**
     * Get employee with ETag support
     */
    public function show(Request $request): Response
    {
        $id = $request->getRouteParameter('id');
        $employee = $this->employeeService->findById($id);
        
        if (!$employee) {
            return $this->json(['error' => 'Employee not found'], 404);
        }
        
        $response = new Response();
        $response->json($employee)
            ->etag()  // Auto-generate ETag from content
            ->lastModified($employee['updated_at'])
            ->cache(3600);  // Cache for 1 hour
        
        // Return 304 if not modified
        if ($response->isNotModified($request)) {
            return $response->notModified();
        }
        
        return $response;
    }
    
    /**
     * Get employee list with public caching
     */
    public function index(Request $request): Response
    {
        $employees = $this->employeeService->getAll();
        
        return $this->json($employees)
            ->cache(1800, true)  // Cache for 30 minutes (public)
            ->etag();
    }
    
    /**
     * Get sensitive data with private caching
     */
    public function profile(Request $request): Response
    {
        $user = $request->getUser();
        $profile = $this->employeeService->getProfile($user['id']);
        
        return $this->json($profile)
            ->cache(600, false)  // Cache for 10 minutes (private)
            ->noCache();  // Or disable caching for sensitive data
    }
}

/**
 * Example 3: Using caching in services
 */
class EmployeeService
{
    use Cacheable;
    
    /**
     * Get employee by ID with caching
     */
    public function findById(int $id): ?array
    {
        return $this->remember("employee:{$id}", function() use ($id) {
            return $this->employeeModel->find($id);
        }, 3600);
    }
    
    /**
     * Get employees by department with query caching
     */
    public function getByDepartment(string $department): array
    {
        return $this->cachedQuery(
            "employees:department:{$department}",
            function() use ($department) {
                return $this->employeeModel->where(['department' => $department]);
            },
            1800
        );
    }
    
    /**
     * Update employee and invalidate caches
     */
    public function update(int $id, array $data): array
    {
        $employee = $this->employeeModel->update($id, $data);
        
        // Invalidate specific employee cache
        $this->invalidateCache("employee:{$id}");
        
        // Invalidate department cache if department changed
        if (isset($data['department'])) {
            $this->invalidateQueryCache("employees:department:{$data['department']}");
        }
        
        // Invalidate list caches
        $this->invalidateQueryCache('employees:list');
        
        return $employee;
    }
}

/**
 * Example 4: Static resource caching
 */
class AssetController extends Controller
{
    /**
     * Serve static assets with long-term caching
     */
    public function serve(Request $request): Response
    {
        $file = $request->getRouteParameter('file');
        $filePath = __DIR__ . '/../../public/assets/' . $file;
        
        if (!file_exists($filePath)) {
            return $this->json(['error' => 'File not found'], 404);
        }
        
        $content = file_get_contents($filePath);
        $mimeType = mime_content_type($filePath);
        
        return (new Response())
            ->setContent($content)
            ->setHeader('Content-Type', $mimeType)
            ->cache(31536000, true)  // Cache for 1 year
            ->etag()
            ->lastModified(filemtime($filePath));
    }
}

/**
 * Example 5: Conditional caching based on user role
 */
class ReportController extends Controller
{
    use Cacheable;
    
    /**
     * Generate report with role-based caching
     */
    public function generate(Request $request): Response
    {
        $user = $request->getUser();
        $type = $request->getQueryParameter('type');
        
        // Admin reports are cached longer
        $ttl = $user['role'] === 'admin' ? 3600 : 600;
        
        $cacheKey = $this->cacheKey("report:{$type}", [
            'role' => $user['role']
        ]);
        
        $report = $this->remember($cacheKey, function() use ($type) {
            return $this->reportService->generate($type);
        }, $ttl);
        
        return $this->json($report);
    }
}

/**
 * Example 6: Cache warming
 */
class CacheWarmer
{
    use Cacheable;
    
    /**
     * Warm up frequently accessed caches
     */
    public function warmUp(): void
    {
        // Warm up dashboard metrics
        $this->remember('dashboard:metrics', function() {
            return $this->calculateMetrics();
        }, 300);
        
        // Warm up employee list
        $this->cachedQuery('employees:active', function() {
            return $this->getActiveEmployees();
        }, 600);
        
        // Warm up department data
        foreach ($this->getDepartments() as $dept) {
            $this->cachedQuery("employees:department:{$dept}", function() use ($dept) {
                return $this->getEmployeesByDepartment($dept);
            }, 1800);
        }
    }
    
    private function calculateMetrics(): array
    {
        // Calculate metrics...
        return [];
    }
    
    private function getActiveEmployees(): array
    {
        // Get active employees...
        return [];
    }
    
    private function getDepartments(): array
    {
        return ['IT', 'HR', 'Finance', 'Operations'];
    }
    
    private function getEmployeesByDepartment(string $dept): array
    {
        // Get employees by department...
        return [];
    }
}
