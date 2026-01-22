<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Services\MigrationRunner;

/**
 * Admin Middleware
 *
 * Ensures user has admin privileges.
 * Also runs any pending database migrations automatically.
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

        // Run any pending database migrations automatically
        // This ensures updates "just work" without manual SQL
        $this->runPendingMigrations();

        return $next();
    }

    /**
     * Run pending database migrations silently
     */
    private function runPendingMigrations(): void
    {
        static $hasRun = false;

        // Only run once per request
        if ($hasRun) {
            return;
        }
        $hasRun = true;

        try {
            $runner = new MigrationRunner();
            $runner->runPending();
        } catch (\Throwable $e) {
            // Never break the app due to migration issues
            // Just log it in debug mode
            if (function_exists('config') && config('app.debug', false)) {
                error_log('AdminMiddleware migration error: ' . $e->getMessage());
            }
        }
    }
}
