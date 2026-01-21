<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Response;
use App\Core\ValidationException;
use App\Models\User;
use App\Models\ContributorRequest;

/**
 * Register Controller
 *
 * Handles user registration.
 */
class RegisterController extends Controller
{
    /**
     * Show registration form
     */
    public function show(): Response
    {
        // Check if registration is enabled
        if (!$this->isRegistrationEnabled()) {
            return $this->redirectWithError('/', 'Registration is currently disabled.');
        }

        $contributorSystemEnabled = setting('contributor_system_enabled', '0') === '1';

        return $this->view('auth/register', [
            'title' => 'Register',
            'contributorSystemEnabled' => $contributorSystemEnabled,
        ]);
    }

    /**
     * Check if registration is enabled
     */
    private function isRegistrationEnabled(): bool
    {
        return setting('registration_enabled', '1') === '1';
    }

    /**
     * Handle registration
     */
    public function register(): Response
    {
        // Check if registration is enabled
        if (!$this->isRegistrationEnabled()) {
            return $this->redirectWithError('/', 'Registration is currently disabled.');
        }

        try {
            $data = $this->validate([
                'username' => 'required|min:3|max:50|alpha_dash|unique:users,username',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|min:12|max:255',
                'password_confirmation' => 'required',
            ]);

            // Check password confirmation
            if ($data['password'] !== $this->request->input('password_confirmation')) {
                $_SESSION['_errors'] = ['password_confirmation' => 'Passwords do not match.'];
                $_SESSION['_old_input'] = $this->request->except(['password', 'password_confirmation']);
                return $this->back();
            }

            // Create user
            $user = User::register([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            // Log registration
            $user->logAuthEvent(
                'register',
                $this->request->ip(),
                $this->request->userAgent()
            );

            // For now, auto-verify and login (in production, send verification email)
            $user->verifyEmail();

            // Log user in
            $_SESSION['user_id'] = $user->id;
            unset($_SESSION['_user_cache']);

            // Check if user wants to be a contributor
            $wantsContributor = $this->request->input('want_contributor') === '1';
            $contributorSystemEnabled = setting('contributor_system_enabled', '0') === '1';

            if ($wantsContributor && $contributorSystemEnabled) {
                // Create contributor request
                $reason = trim($this->request->input('contributor_reason', ''));
                ContributorRequest::createRequest($user->id, $reason ?: null);

                return $this->redirectWithSuccess('/', 'Registration successful! Your contributor request has been submitted for review.');
            }

            return $this->redirectWithSuccess('/', 'Registration successful! Welcome to ' . config('app.name') . '.');

        } catch (ValidationException $e) {
            $_SESSION['_old_input'] = $this->request->except(['password', 'password_confirmation']);
            return $this->back();
        }
    }
}
