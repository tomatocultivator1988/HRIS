<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use Services\AnnouncementService;

/**
 * AnnouncementController - Handles announcement-related requests
 * 
 * This controller provides announcement management functionality using
 * the AnnouncementService for business logic.
 */
class AnnouncementController extends Controller
{
    private AnnouncementService $announcementService;
    
    /**
     * Constructor - Initialize with AnnouncementService dependency
     */
    public function __construct(\Core\Container $container)
    {
        parent::__construct($container);
        $this->announcementService = $container->resolve(AnnouncementService::class);
    }
    
    /**
     * List all announcements (GET /api/announcements or /api/announcements/list.php)
     */
    public function index(Request $request): Response
    {
        return $this->list($request);
    }
    
    /**
     * List announcements - backward compatible method
     */
    public function list(Request $request): Response
    {
        try {
            // Get user from request
            $user = $request->getUser();
            $isAdmin = $user && ($user['role'] === 'admin' || $user['role'] === 'superadmin');
            
            // Get announcements
            if ($isAdmin) {
                $result = $this->announcementService->getAllAnnouncements();
            } else {
                $result = $this->announcementService->getActiveAnnouncements();
            }
            
            return $this->json($result);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'ANNOUNCEMENT_LIST_ERROR'
            ], 500);
        }
    }
    
    /**
     * Show a single announcement (GET /api/announcements/{id})
     */
    public function show(Request $request): Response
    {
        try {
            $id = $request->getRouteParameter('id');
            
            if (!$id) {
                return $this->json([
                    'success' => false,
                    'message' => 'Announcement ID is required',
                    'error' => 'MISSING_ID'
                ], 400);
            }
            
            $result = $this->announcementService->getAnnouncement($id);
            
            return $this->json($result);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'ANNOUNCEMENT_NOT_FOUND'
            ], 404);
        }
    }
    
    /**
     * Create a new announcement (POST /api/announcements or /api/announcements/create.php)
     */
    public function create(Request $request): Response
    {
        try {
            // Get request data
            $data = $request->isJson() ? $request->getJsonData() : $request->getPostData();
            
            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';
            
            // Get user from request
            $user = $request->getUser();
            $authorId = $user['id'] ?? null;
            
            if (!$authorId) {
                return $this->json([
                    'success' => false,
                    'message' => 'User authentication required',
                    'error' => 'AUTH_REQUIRED'
                ], 401);
            }
            
            $result = $this->announcementService->createAnnouncement($title, $content, $authorId);
            
            return $this->json($result, 201);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'ANNOUNCEMENT_CREATE_ERROR'
            ], 400);
        }
    }
    
    /**
     * Update an announcement (PUT /api/announcements/{id} or POST /api/announcements/update.php)
     */
    public function update(Request $request): Response
    {
        try {
            // Get announcement ID
            $id = $request->getRouteParameter('id');
            if (!$id) {
                // Try to get from POST data for legacy endpoint
                $data = $request->isJson() ? $request->getJsonData() : $request->getPostData();
                $id = $data['id'] ?? null;
            }
            
            if (!$id) {
                return $this->json([
                    'success' => false,
                    'message' => 'Announcement ID is required',
                    'error' => 'MISSING_ID'
                ], 400);
            }
            
            // Get request data
            $data = $request->isJson() ? $request->getJsonData() : $request->getPostData();
            
            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';
            
            // Get user from request
            $user = $request->getUser();
            $editorId = $user['id'] ?? null;
            
            if (!$editorId) {
                return $this->json([
                    'success' => false,
                    'message' => 'User authentication required',
                    'error' => 'AUTH_REQUIRED'
                ], 401);
            }
            
            $result = $this->announcementService->updateAnnouncement($id, $title, $content, $editorId);
            
            return $this->json($result);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'ANNOUNCEMENT_UPDATE_ERROR'
            ], 400);
        }
    }
    
    /**
     * Deactivate an announcement (POST /api/announcements/{id}/deactivate or /api/announcements/deactivate.php)
     */
    public function deactivate(Request $request): Response
    {
        try {
            // Get announcement ID
            $id = $request->getRouteParameter('id');
            if (!$id) {
                // Try to get from POST data for legacy endpoint
                $data = $request->isJson() ? $request->getJsonData() : $request->getPostData();
                $id = $data['id'] ?? null;
            }
            
            if (!$id) {
                return $this->json([
                    'success' => false,
                    'message' => 'Announcement ID is required',
                    'error' => 'MISSING_ID'
                ], 400);
            }
            
            $result = $this->announcementService->deactivateAnnouncement($id);
            
            return $this->json($result);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'ANNOUNCEMENT_DEACTIVATE_ERROR'
            ], 400);
        }
    }
}
