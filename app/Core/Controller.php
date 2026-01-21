<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base Controller
 *
 * Provides common functionality for all controllers.
 */
abstract class Controller
{
    protected Request $request;
    protected View $view;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->view = new View();
    }

    /**
     * Render a view
     */
    protected function view(string $template, array $data = [], ?string $layout = 'main'): Response
    {
        $content = $this->view->render($template, $data, $layout);
        return Response::html($content);
    }

    /**
     * Return JSON response
     */
    protected function json(mixed $data, int $statusCode = 200): Response
    {
        return Response::json($data, $statusCode);
    }

    /**
     * Redirect to a URL
     */
    protected function redirect(string $url, int $statusCode = 302): Response
    {
        return Response::redirect($url, $statusCode);
    }

    /**
     * Redirect back to previous page
     */
    protected function back(): Response
    {
        $referer = $this->request->referer() ?? url('/');
        return Response::redirect($referer);
    }

    /**
     * Redirect back with errors
     */
    protected function backWithErrors(array $errors, array $old = []): Response
    {
        $_SESSION['_errors'] = $errors;
        $_SESSION['_old_input'] = $old ?: $this->request->all();
        return $this->back();
    }

    /**
     * Redirect with success message
     */
    protected function redirectWithSuccess(string $url, string $message): Response
    {
        session_flash('success', $message);
        return $this->redirect($url);
    }

    /**
     * Redirect with error message
     */
    protected function redirectWithError(string $url, string $message): Response
    {
        session_flash('error', $message);
        return $this->redirect($url);
    }

    /**
     * Get validation errors from session
     */
    protected function getErrors(): array
    {
        $errors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_errors']);
        return $errors;
    }

    /**
     * Validate request data
     *
     * Two usage patterns:
     * 1. validate($rules) - throws ValidationException on failure, returns validated data
     * 2. validate($data, $rules) - returns errors array (empty if valid)
     */
    protected function validate(array $data, array $rules = []): array
    {
        // If only one argument passed, treat it as rules (old pattern)
        if (empty($rules)) {
            $rules = $data;
            $data = $this->request->all();

            $validator = new Validator($data, $rules);

            if (!$validator->validate()) {
                $_SESSION['_errors'] = $validator->getErrors();
                $_SESSION['_old_input'] = $data;
                throw new ValidationException($validator->getErrors());
            }

            return $validator->getValidated();
        }

        // Two arguments passed - return errors array
        $validator = new Validator($data, $rules);

        if (!$validator->validate()) {
            return $validator->getErrors();
        }

        return [];
    }

    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get authenticated user ID
     */
    protected function userId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    /**
     * Get authenticated user
     */
    protected function user(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        // Cache user in session to avoid repeated DB queries
        if (!isset($_SESSION['_user_cache'])) {
            $db = app()->getDatabase();
            $_SESSION['_user_cache'] = $db->fetch(
                'SELECT * FROM users WHERE id = :id',
                ['id' => $this->userId()]
            );
        }

        return $_SESSION['_user_cache'];
    }

    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $_SESSION['_intended_url'] = $this->request->getUrl();
            redirect(url('/login'));
        }
    }

    /**
     * Require specific role
     */
    protected function requireRole(string $role): void
    {
        $this->requireAuth();
        $user = $this->user();

        if ($user['role'] !== $role && $user['role'] !== 'admin') {
            throw new \RuntimeException('Unauthorized', 403);
        }
    }

    /**
     * Get database instance
     */
    protected function db(): Database
    {
        return app()->getDatabase();
    }

    /**
     * Return 404 not found response
     */
    protected function notFound(string $message = 'Page not found'): Response
    {
        $content = $this->view->render('errors/404', ['message' => $message], 'main');
        return Response::html($content, 404);
    }
}

/**
 * Validation Exception
 */
class ValidationException extends \Exception
{
    private array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct('Validation failed');
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
