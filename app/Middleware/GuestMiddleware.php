<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

/**
 * Guest Middleware
 *
 * Ensures user is NOT authenticated (for login/register pages).
 */
class GuestMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (isset($_SESSION['user_id'])) {
            // Already logged in, redirect to home
            return Response::redirect(url('/'));
        }

        return $next();
    }
}
