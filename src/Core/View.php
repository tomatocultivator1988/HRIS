<?php

namespace Core;

/**
 * View Class - Handles template rendering
 * 
 * This class provides template rendering functionality for the MVC framework.
 * It supports layouts, data passing, and template inheritance.
 */
class View
{
    private string $viewsPath;
    private string $layoutsPath;
    private array $data = [];
    private ?string $layout = null;
    
    /**
     * Constructor
     *
     * @param string $viewsPath Path to views directory
     */
    public function __construct(string $viewsPath = null)
    {
        $this->viewsPath = $viewsPath ?: dirname(__DIR__) . '/Views';
        $this->layoutsPath = $this->viewsPath . '/layouts';
    }
    
    /**
     * Render a view template
     *
     * @param string $template Template name (e.g., 'employees/list')
     * @param array $data Data to pass to template
     * @param string|null $layout Layout to use (null for no layout)
     * @return string Rendered HTML
     */
    public function render(string $template, array $data = [], ?string $layout = 'base'): string
    {
        $this->data = $data;
        $this->layout = $layout;
        
        // Render the main template
        $content = $this->renderTemplate($template, $data);
        
        // If no layout specified, return content directly
        if (!$layout) {
            return $content;
        }
        
        // Render with layout
        return $this->renderLayout($layout, array_merge($data, ['content' => $content]));
    }
    
    /**
     * Render template without layout
     *
     * @param string $template Template name
     * @param array $data Data to pass to template
     * @return string Rendered HTML
     */
    public function renderPartial(string $template, array $data = []): string
    {
        return $this->renderTemplate($template, $data);
    }
    
    /**
     * Set layout for subsequent renders
     *
     * @param string|null $layout Layout name or null for no layout
     */
    public function setLayout(?string $layout): void
    {
        $this->layout = $layout;
    }
    
    /**
     * Add data to be available in all templates
     *
     * @param array $data Global data
     */
    public function share(array $data): void
    {
        $this->data = array_merge($this->data, $data);
    }
    
    /**
     * Check if template exists
     *
     * @param string $template Template name
     * @return bool True if template exists
     */
    public function exists(string $template): bool
    {
        return file_exists($this->getTemplatePath($template));
    }
    
    /**
     * Render a template file
     *
     * @param string $template Template name
     * @param array $data Data to pass to template
     * @return string Rendered HTML
     * @throws \Exception If template not found
     */
    private function renderTemplate(string $template, array $data = []): string
    {
        $templatePath = $this->getTemplatePath($template);
        
        if (!file_exists($templatePath)) {
            throw new \Exception("Template not found: {$template} (looked in: {$templatePath})");
        }
        
        // Merge with global data
        $data = array_merge($this->data, $data);
        
        // Extract data to variables
        extract($data, EXTR_SKIP);
        
        // Start output buffering
        ob_start();
        
        try {
            // Include the template
            include $templatePath;
            
            // Get the rendered content
            return ob_get_clean();
        } catch (\Exception $e) {
            // Clean the buffer on error
            ob_end_clean();
            throw $e;
        }
    }
    
    /**
     * Render layout template
     *
     * @param string $layout Layout name
     * @param array $data Data to pass to layout
     * @return string Rendered HTML
     * @throws \Exception If layout not found
     */
    private function renderLayout(string $layout, array $data = []): string
    {
        $layoutPath = $this->layoutsPath . '/' . $layout . '.php';
        
        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout not found: {$layout} (looked in: {$layoutPath})");
        }
        
        // Merge with global data
        $data = array_merge($this->data, $data);
        
        // Extract data to variables
        extract($data, EXTR_SKIP);
        
        // Start output buffering
        ob_start();
        
        try {
            // Include the layout
            include $layoutPath;
            
            // Get the rendered content
            return ob_get_clean();
        } catch (\Exception $e) {
            // Clean the buffer on error
            ob_end_clean();
            throw $e;
        }
    }
    
    /**
     * Get full path to template file
     *
     * @param string $template Template name
     * @return string Full path to template
     */
    private function getTemplatePath(string $template): string
    {
        // Convert dot notation to path (e.g., 'employees.list' -> 'employees/list')
        $template = str_replace('.', '/', $template);
        
        // Add .php extension if not present
        if (!str_ends_with($template, '.php')) {
            $template .= '.php';
        }
        
        return $this->viewsPath . '/' . $template;
    }
    
    /**
     * Escape HTML output
     *
     * @param string $value Value to escape
     * @return string Escaped value
     */
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Include a partial template
     *
     * @param string $partial Partial template name
     * @param array $data Data to pass to partial
     * @return void Outputs the partial directly
     */
    public function include(string $partial, array $data = []): void
    {
        echo $this->renderPartial($partial, $data);
    }
    
    /**
     * Create a view instance
     *
     * @param string|null $viewsPath Custom views path
     * @return View View instance
     */
    public static function create(?string $viewsPath = null): View
    {
        return new self($viewsPath);
    }
}