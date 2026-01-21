<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Response;
use App\Models\User;

/**
 * Login Controller
 *
 * Handles user authentication.
 */
class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function show(): Response
    {
        return $this->view('auth/login', [
            'title' => 'Login',
        ]);
    }

    /**
     * Handle login
     */
    public function login(): Response
    {
        $email = $this->request->input('email');
        $password = $this->request->input('password');
        $remember = $this->request->input('remember') === 'on';

        // Validate input
        if (empty($email) || empty($password)) {
            $_SESSION['_errors'] = ['email' => 'Email and password are required.'];
            $_SESSION['_old_input'] = ['email' => $email];
            return $this->back();
        }

        // Find user
        $user = User::findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            // Log failed attempt
            $this->logFailedLogin($email);

            $_SESSION['_errors'] = ['email' => 'Invalid email or password.'];
            $_SESSION['_old_input'] = ['email' => $email];
            return $this->back();
        }

        // Check if user is active
        if (!$user->isActive()) {
            if ($user->status === 'pending') {
                $_SESSION['_errors'] = ['email' => 'Please verify your email address first.'];
            } else {
                $_SESSION['_errors'] = ['email' => 'Your account has been suspended.'];
            }
            $_SESSION['_old_input'] = ['email' => $email];
            return $this->back();
        }

        // Login successful
        $this->loginUser($user, $remember);

        // Log successful login
        $user->logAuthEvent(
            'login',
            $this->request->ip(),
            $this->request->userAgent()
        );

        // Redirect to intended URL or home
        $intended = $_SESSION['_intended_url'] ?? url('/');
        unset($_SESSION['_intended_url']);

        return $this->redirectWithSuccess($intended, 'Welcome back, ' . $user->username . '!');
    }

    /**
     * Log in the user
     */
    private function loginUser(User $user, bool $remember = false): void
    {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Set session data
        $_SESSION['user_id'] = $user->id;
        unset($_SESSION['_user_cache']);

        // Update last login
        $user->updateLastLogin();

        // Set remember cookie if requested
        if ($remember) {
            // Simple signed token approach (no DB storage needed)
            $data = $user->id . '|' . strtotime('+30 days');
            $signature = hash_hmac('sha256', $data, config('app.key', 'default-key'));
            $token = base64_encode($data . '|' . $signature);

            setcookie('remember_token', $token, [
                'expires' => strtotime('+30 days'),
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    /**
     * Log failed login attempt
     */
    private function logFailedLogin(string $email): void
    {
        $db = app()->getDatabase();
        $db->insert('auth_logs', [
            'user_id' => null,
            'event_type' => 'failed_login',
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'details' => json_encode(['email' => $email]),
        ]);
    }
}
