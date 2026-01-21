<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Response;
use App\Models\User;

/**
 * Password Controller
 *
 * Handles password reset flow.
 */
class PasswordController extends Controller
{
    /**
     * Show forgot password form
     */
    public function forgot(): Response
    {
        return $this->view('auth/forgot-password', [
            'title' => 'Forgot Password',
        ]);
    }

    /**
     * Send password reset email
     */
    public function sendReset(): Response
    {
        $email = $this->request->input('email');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['_errors'] = ['email' => 'Please enter a valid email address.'];
            return $this->back();
        }

        $user = User::findByEmail($email);

        if ($user) {
            // Generate reset token
            $token = $user->createPasswordResetToken();

            // In production, send email here
            // For now, we'll just flash the reset link (DEV ONLY)
            $resetUrl = url('/reset-password/' . $token);

            if (config('app.debug')) {
                session_flash('info', 'DEV: Reset link: ' . $resetUrl);
            }

            // Log the event
            $user->logAuthEvent(
                'password_reset',
                $this->request->ip(),
                $this->request->userAgent(),
                ['action' => 'requested']
            );
        }

        // Always show success message to prevent email enumeration
        return $this->redirectWithSuccess(
            '/login',
            'If an account exists with that email, we have sent password reset instructions.'
        );
    }

    /**
     * Show reset password form
     */
    public function reset(string $token): Response
    {
        $user = User::findByResetToken($token);

        if (!$user) {
            return $this->redirectWithError('/forgot-password', 'Invalid or expired reset link.');
        }

        return $this->view('auth/reset-password', [
            'title' => 'Reset Password',
            'token' => $token,
        ]);
    }

    /**
     * Update password
     */
    public function update(): Response
    {
        $token = $this->request->input('token');
        $password = $this->request->input('password');
        $passwordConfirmation = $this->request->input('password_confirmation');

        // Validate
        if (empty($password) || strlen($password) < 12) {
            $_SESSION['_errors'] = ['password' => 'Password must be at least 12 characters.'];
            return $this->redirect('/reset-password/' . $token);
        }

        if ($password !== $passwordConfirmation) {
            $_SESSION['_errors'] = ['password_confirmation' => 'Passwords do not match.'];
            return $this->redirect('/reset-password/' . $token);
        }

        $user = User::findByResetToken($token);

        if (!$user) {
            return $this->redirectWithError('/forgot-password', 'Invalid or expired reset link.');
        }

        // Reset password
        $user->resetPassword($password, $token);

        // Log the event
        $user->logAuthEvent(
            'password_reset',
            $this->request->ip(),
            $this->request->userAgent(),
            ['action' => 'completed']
        );

        return $this->redirectWithSuccess('/login', 'Password has been reset. You can now login.');
    }
}
