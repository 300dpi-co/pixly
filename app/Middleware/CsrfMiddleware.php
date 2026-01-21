<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

/**
 * CSRF Middleware
 *
 * Validates CSRF tokens on POST/PUT/DELETE requests.
 */
class CsrfMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Only check on state-changing methods
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $next();
        }

        $tokenName = config('security.csrf_token_name', '_token');
        $token = $request->input($tokenName) ?? $request->header('X-CSRF-TOKEN');

        if (!$this->isValidToken($token)) {
            // For AJAX requests, return JSON error
            if ($request->isAjax() || $request->wantsJson()) {
                return Response::json(['error' => 'CSRF token mismatch'], 419);
            }

            // For regular requests, redirect back with error
            session_flash('error', 'Your session has expired. Please try again.');
            return Response::redirect($request->referer() ?? url('/'));
        }

        return $next();
    }

    /**
     * Validate the CSRF token
     */
    private function isValidToken(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        $sessionToken = $_SESSION['_csrf_token'] ?? null;

        if (empty($sessionToken)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }
}
