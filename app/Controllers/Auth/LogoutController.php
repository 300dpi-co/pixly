<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Response;
use App\Models\User;

/**
 * Logout Controller
 *
 * Handles user logout.
 */
class LogoutController extends Controller
{
    /**
     * Handle logout
     */
    public function logout(): Response
    {
        if (isset($_SESSION['user_id'])) {
            $user = User::find($_SESSION['user_id']);

            if ($user) {
                $user->logAuthEvent(
                    'logout',
                    $this->request->ip(),
                    $this->request->userAgent()
                );
            }
        }

        // Clear session data
        $_SESSION = [];

        // Destroy session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy session
        session_destroy();

        // Start new session for flash message
        session_start();

        return $this->redirectWithSuccess('/', 'You have been logged out.');
    }
}
