<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\Response;
use App\Models\User;
use App\Models\ContributorRequest;

/**
 * Frontend Contributor Controller
 *
 * Handles user-facing contributor request pages.
 */
class ContributorController extends Controller
{
    /**
     * Show contributor request form
     */
    public function request(): Response
    {
        // Check if contributor system is enabled
        if (!$this->isContributorSystemEnabled()) {
            return $this->redirectWithError('/', 'Contributor system is not available.');
        }

        $user = $this->getUser();

        // Check if user is already a contributor or higher
        if ($user->canUpload()) {
            return $this->redirectWithSuccess('/upload', 'You already have upload permissions.');
        }

        // Check if user already has a pending request
        if ($user->hasPendingContributorRequest()) {
            return $this->redirect('/contributor/status');
        }

        return $this->view('frontend/contributor/request', [
            'title' => 'Become a Contributor',
            'meta_description' => 'Apply to become a contributor and share your images with our community.',
        ]);
    }

    /**
     * Submit contributor request
     */
    public function submit(): Response
    {
        // Check if contributor system is enabled
        if (!$this->isContributorSystemEnabled()) {
            return $this->redirectWithError('/', 'Contributor system is not available.');
        }

        $user = $this->getUser();

        // Check if user is already a contributor or higher
        if ($user->canUpload()) {
            return $this->redirectWithSuccess('/upload', 'You already have upload permissions.');
        }

        // Check if user already has a pending request
        if ($user->hasPendingContributorRequest()) {
            return $this->redirectWithError('/contributor/status', 'You already have a pending request.');
        }

        // Create contributor request
        $reason = trim($this->request->input('reason', ''));
        ContributorRequest::createRequest($user->id, $reason ?: null);

        return $this->redirectWithSuccess('/contributor/status', 'Your contributor request has been submitted!');
    }

    /**
     * Show contributor request status
     */
    public function status(): Response
    {
        // Check if contributor system is enabled
        if (!$this->isContributorSystemEnabled()) {
            return $this->redirectWithError('/', 'Contributor system is not available.');
        }

        $user = $this->getUser();

        // Check if user is already a contributor or higher
        if ($user->canUpload()) {
            return $this->redirectWithSuccess('/upload', 'You already have upload permissions. You can upload images now!');
        }

        // Get user's contributor request
        $contributorRequest = $user->getContributorRequest();

        return $this->view('frontend/contributor/status', [
            'title' => 'Contributor Request Status',
            'meta_description' => 'Check the status of your contributor application.',
            'contributorRequest' => $contributorRequest,
        ]);
    }

    /**
     * Check if contributor system is enabled
     */
    private function isContributorSystemEnabled(): bool
    {
        return setting('contributor_system_enabled', '0') === '1';
    }

    /**
     * Get the current user as User model
     */
    private function getUser(): User
    {
        return User::find($_SESSION['user_id']);
    }
}
