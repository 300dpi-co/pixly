<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;

/**
 * Admin Middleware
 *
 * Ensures user has admin privileges.
 */
class AdminMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!isset($_SESSION['user_id'])) {
            return Response::redirect(url('/login'));
        }

        $user = User::find($_SESSION['user_id']);

        if (!$user || !$user->isAdmin()) {
            // Not authorized
            http_response_code(403);
            return new Response('Forbidden', 403);
        }

        return $next();
    }
}
