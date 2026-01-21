<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

/**
 * Auth Middleware
 *
 * Ensures user is authenticated before accessing protected routes.
 */
class AuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!isset($_SESSION['user_id'])) {
            // Store intended URL for redirect after login
            $_SESSION['_intended_url'] = $request->getUrl();

            // Redirect to login
            return Response::redirect(url('/login'));
        }

        return $next();
    }
}
