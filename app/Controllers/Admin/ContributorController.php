<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Models\ContributorRequest;

/**
 * Admin Contributor Controller
 *
 * Manages contributor requests in the admin panel.
 */
class ContributorController extends Controller
{
    /**
     * List all contributor requests
     */
    public function index(): Response
    {
        $page = max(1, (int) ($this->request->input('page', 1)));
        $status = $this->request->input('status');

        // Validate status filter
        if ($status && !in_array($status, ['pending', 'approved', 'rejected'])) {
            $status = null;
        }

        try {
            $requests = ContributorRequest::getAllRequests($page, 20, $status);
            $pendingCount = ContributorRequest::countByStatus('pending');
            $approvedCount = ContributorRequest::countByStatus('approved');
            $rejectedCount = ContributorRequest::countByStatus('rejected');
        } catch (\Exception $e) {
            // Fallback if there's a DB error
            $requests = ['data' => [], 'total' => 0, 'per_page' => 20, 'current_page' => 1, 'last_page' => 1];
            $pendingCount = 0;
            $approvedCount = 0;
            $rejectedCount = 0;
        }

        return $this->view('admin/contributors/index', [
            'title' => 'Contributor Requests',
            'currentPage' => 'contributors',
            'requests' => $requests['data'],
            'pagination' => $requests,
            'currentStatus' => $status,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
        ], 'admin');
    }

    /**
     * Approve a contributor request
     */
    public function approve(int|string $id): Response
    {
        $id = (int) $id;
        $request = ContributorRequest::find($id);

        if (!$request) {
            return $this->redirectWithError('/admin/contributors', 'Contributor request not found.');
        }

        if ($request->status !== 'pending') {
            return $this->redirectWithError('/admin/contributors', 'This request has already been processed.');
        }

        $note = trim($this->request->input('note', ''));
        $adminId = $_SESSION['user_id'];

        $request->approve($adminId, $note ?: null);

        return $this->redirectWithSuccess('/admin/contributors', 'Contributor request approved! User can now upload images.');
    }

    /**
     * Reject a contributor request
     */
    public function reject(int|string $id): Response
    {
        $id = (int) $id;
        $request = ContributorRequest::find($id);

        if (!$request) {
            return $this->redirectWithError('/admin/contributors', 'Contributor request not found.');
        }

        if ($request->status !== 'pending') {
            return $this->redirectWithError('/admin/contributors', 'This request has already been processed.');
        }

        $note = trim($this->request->input('note', ''));
        $adminId = $_SESSION['user_id'];

        $request->reject($adminId, $note ?: null);

        return $this->redirectWithSuccess('/admin/contributors', 'Contributor request rejected.');
    }
}
