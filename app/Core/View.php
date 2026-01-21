<?php

declare(strict_types=1);

namespace App\Core;

/**
 * View/Template Engine
 *
 * Simple PHP-based template engine with layout support.
 */
class View
{
    private string $viewsPath;
    private array $sharedData = [];

    public function __construct()
    {
        $this->viewsPath = VIEWS_PATH;
    }

    /**
     * Share data with all views
     */
    public function share(string $key, mixed $value): void
    {
        $this->sharedData[$key] = $value;
    }

    /**
     * Render a view
     */
    public function render(string $template, array $data = [], ?string $layout = 'main'): string
    {
        // Merge shared data with local data
        $data = array_merge($this->sharedData, $data);

        // Render the template
        $content = $this->renderTemplate($template, $data);

        // Wrap in layout if specified
        if ($layout !== null) {
            $data['content'] = $content;
            $content = $this->renderTemplate("layouts/{$layout}", $data);
        }

        return $content;
    }

    /**
     * Render a partial (no layout)
     */
    public function partial(string $template, array $data = []): string
    {
        return $this->renderTemplate("partials/{$template}", array_merge($this->sharedData, $data));
    }

    /**
     * Render a component
     */
    public function component(string $name, array $data = []): string
    {
        return $this->renderTemplate("partials/components/{$name}", array_merge($this->sharedData, $data));
    }

    /**
     * Render a template file
     */
    private function renderTemplate(string $template, array $data): string
    {
        $file = $this->viewsPath . '/' . str_replace('.', '/', $template) . '.php';

        if (!file_exists($file)) {
            throw new \RuntimeException("View not found: {$template} ({$file})");
        }

        // Extract data to local variables
        extract($data, EXTR_SKIP);

        // Create view helper variable
        $view = $this;

        // Start output buffering
        ob_start();

        try {
            include $file;
            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Escape HTML
     */
    public function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Include another view
     */
    public function include(string $template, array $data = []): void
    {
        echo $this->renderTemplate($template, array_merge($this->sharedData, $data));
    }

    /**
     * Check if view exists
     */
    public function exists(string $template): bool
    {
        $file = $this->viewsPath . '/' . str_replace('.', '/', $template) . '.php';
        return file_exists($file);
    }

    /**
     * Get asset URL
     */
    public function asset(string $path): string
    {
        return asset($path);
    }

    /**
     * Get URL
     */
    public function url(string $path = ''): string
    {
        return url($path);
    }

    /**
     * Output CSRF field
     */
    public function csrf(): string
    {
        return csrf_field();
    }

    /**
     * Get old input value
     */
    public function old(string $key, mixed $default = ''): mixed
    {
        return old($key, $default);
    }

    /**
     * Check for flash message
     */
    public function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    /**
     * Get flash message
     */
    public function flash(string $key, mixed $default = null): mixed
    {
        return session_get_flash($key, $default);
    }

    /**
     * Get validation errors
     */
    public function errors(): array
    {
        $errors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_errors']);
        return $errors;
    }

    /**
     * Check if there's an error for a field
     */
    public function hasError(string $field): bool
    {
        return isset($_SESSION['_errors'][$field]);
    }

    /**
     * Get error for a field
     */
    public function error(string $field): ?string
    {
        return $_SESSION['_errors'][$field] ?? null;
    }

    /**
     * Generate pagination HTML
     */
    public function pagination(array $pagination, string $baseUrl): string
    {
        if ($pagination['last_page'] <= 1) {
            return '';
        }

        $html = '<nav class="flex justify-center mt-8"><ul class="flex gap-2">';

        // Previous
        if ($pagination['current_page'] > 1) {
            $prevUrl = $baseUrl . '/page/' . ($pagination['current_page'] - 1);
            $html .= '<li><a href="' . $this->e($prevUrl) . '" class="px-4 py-2 bg-neutral-100 hover:bg-neutral-200 rounded-lg">&laquo;</a></li>';
        }

        // Page numbers
        $start = max(1, $pagination['current_page'] - 2);
        $end = min($pagination['last_page'], $pagination['current_page'] + 2);

        if ($start > 1) {
            $html .= '<li><a href="' . $this->e($baseUrl . '/page/1') . '" class="px-4 py-2 bg-neutral-100 hover:bg-neutral-200 rounded-lg">1</a></li>';
            if ($start > 2) {
                $html .= '<li><span class="px-4 py-2">...</span></li>';
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            $url = $i === 1 ? $baseUrl : $baseUrl . '/page/' . $i;
            $active = $i === $pagination['current_page'] ? 'bg-primary-600 text-white' : 'bg-neutral-100 hover:bg-neutral-200';
            $html .= '<li><a href="' . $this->e($url) . '" class="px-4 py-2 ' . $active . ' rounded-lg">' . $i . '</a></li>';
        }

        if ($end < $pagination['last_page']) {
            if ($end < $pagination['last_page'] - 1) {
                $html .= '<li><span class="px-4 py-2">...</span></li>';
            }
            $html .= '<li><a href="' . $this->e($baseUrl . '/page/' . $pagination['last_page']) . '" class="px-4 py-2 bg-neutral-100 hover:bg-neutral-200 rounded-lg">' . $pagination['last_page'] . '</a></li>';
        }

        // Next
        if ($pagination['current_page'] < $pagination['last_page']) {
            $nextUrl = $baseUrl . '/page/' . ($pagination['current_page'] + 1);
            $html .= '<li><a href="' . $this->e($nextUrl) . '" class="px-4 py-2 bg-neutral-100 hover:bg-neutral-200 rounded-lg">&raquo;</a></li>';
        }

        $html .= '</ul></nav>';

        return $html;
    }
}
