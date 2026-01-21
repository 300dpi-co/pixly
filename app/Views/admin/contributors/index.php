<?php
$contributorSystemEnabled = setting('contributor_system_enabled', '0') === '1';
?>

<?php if (!$contributorSystemEnabled): ?>
<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <p class="font-medium text-yellow-800">Contributor System is Disabled</p>
            <p class="text-sm text-yellow-700 mt-1">
                Enable the contributor system in <a href="/admin/settings?group=features" class="underline">Settings &gt; Features</a> to allow users to apply as contributors.
            </p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-neutral-500">Pending</p>
                <p class="text-2xl font-bold text-yellow-600"><?= $pendingCount ?></p>
            </div>
            <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-neutral-500">Approved</p>
                <p class="text-2xl font-bold text-green-600"><?= $approvedCount ?></p>
            </div>
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-neutral-500">Rejected</p>
                <p class="text-2xl font-bold text-red-600"><?= $rejectedCount ?></p>
            </div>
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="bg-white rounded-lg shadow-sm mb-6">
    <div class="border-b">
        <nav class="flex -mb-px">
            <a href="?<?= http_build_query(array_filter(['page' => null])) ?>"
               class="px-6 py-3 text-sm font-medium <?= empty($currentStatus) ? 'border-b-2 border-primary-500 text-primary-600' : 'text-neutral-500 hover:text-neutral-700' ?>">
                All
            </a>
            <a href="?status=pending"
               class="px-6 py-3 text-sm font-medium <?= $currentStatus === 'pending' ? 'border-b-2 border-primary-500 text-primary-600' : 'text-neutral-500 hover:text-neutral-700' ?>">
                Pending
                <?php if ($pendingCount > 0): ?>
                <span class="ml-1 px-2 py-0.5 text-xs bg-yellow-100 text-yellow-700 rounded-full"><?= $pendingCount ?></span>
                <?php endif; ?>
            </a>
            <a href="?status=approved"
               class="px-6 py-3 text-sm font-medium <?= $currentStatus === 'approved' ? 'border-b-2 border-primary-500 text-primary-600' : 'text-neutral-500 hover:text-neutral-700' ?>">
                Approved
            </a>
            <a href="?status=rejected"
               class="px-6 py-3 text-sm font-medium <?= $currentStatus === 'rejected' ? 'border-b-2 border-primary-500 text-primary-600' : 'text-neutral-500 hover:text-neutral-700' ?>">
                Rejected
            </a>
        </nav>
    </div>

    <!-- Requests List -->
    <div class="divide-y">
        <?php if (empty($requests)): ?>
        <div class="p-8 text-center text-neutral-500">
            <svg class="w-12 h-12 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p>No contributor requests found.</p>
        </div>
        <?php else: ?>
            <?php foreach ($requests as $request): ?>
            <div class="p-4 hover:bg-neutral-50" x-data="{ showActions: false, note: '' }">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-1">
                            <a href="/admin/users?search=<?= urlencode($request['username']) ?>" class="font-medium text-neutral-900 hover:text-primary-600">
                                <?= e($request['username']) ?>
                            </a>
                            <span class="text-sm text-neutral-500"><?= e($request['email']) ?></span>
                            <?php
                            $statusClasses = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                            ];
                            ?>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full <?= $statusClasses[$request['status']] ?>">
                                <?= ucfirst($request['status']) ?>
                            </span>
                        </div>

                        <?php if (!empty($request['request_reason'])): ?>
                        <p class="text-sm text-neutral-600 mb-2 line-clamp-2">
                            <?= e($request['request_reason']) ?>
                        </p>
                        <?php endif; ?>

                        <div class="flex items-center gap-4 text-xs text-neutral-500">
                            <span>Submitted: <?= date('M j, Y', strtotime($request['created_at'])) ?></span>
                            <span>Member since: <?= date('M j, Y', strtotime($request['user_created_at'])) ?></span>
                            <?php if ($request['reviewed_by']): ?>
                            <span>Reviewed by: <?= e($request['reviewer_username'] ?? 'Unknown') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($request['status'] === 'pending'): ?>
                    <div class="flex items-center gap-2">
                        <button @click="showActions = !showActions"
                                class="px-3 py-1.5 text-sm font-medium text-neutral-600 bg-neutral-100 hover:bg-neutral-200 rounded-lg transition">
                            Review
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Expandable Actions -->
                <?php if ($request['status'] === 'pending'): ?>
                <div x-show="showActions" x-transition class="mt-4 pt-4 border-t">
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Admin Note (optional)</label>
                        <textarea x-model="note" rows="2"
                                  class="w-full px-3 py-2 border border-neutral-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                  placeholder="Add a note for the user..."></textarea>
                    </div>
                    <div class="flex items-center gap-3">
                        <form method="POST" action="/admin/contributors/<?= $request['id'] ?>/approve" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="note" :value="note">
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition">
                                Approve
                            </button>
                        </form>
                        <form method="POST" action="/admin/contributors/<?= $request['id'] ?>/reject" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="note" :value="note">
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                                Reject
                            </button>
                        </form>
                        <button @click="showActions = false"
                                class="px-4 py-2 text-sm font-medium text-neutral-600 hover:text-neutral-800 transition">
                            Cancel
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($request['admin_note']) && $request['status'] !== 'pending'): ?>
                <div class="mt-3 p-3 bg-neutral-50 rounded-lg">
                    <p class="text-sm text-neutral-600">
                        <strong>Admin note:</strong> <?= e($request['admin_note']) ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['last_page'] > 1): ?>
    <div class="px-4 py-3 border-t bg-neutral-50 flex items-center justify-between">
        <div class="text-sm text-neutral-600">
            Showing <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?> to
            <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> of
            <?= $pagination['total'] ?> requests
        </div>
        <div class="flex gap-1">
            <?php if ($pagination['current_page'] > 1): ?>
            <a href="?<?= http_build_query(array_filter(['status' => $currentStatus, 'page' => $pagination['current_page'] - 1])) ?>"
               class="px-3 py-1 text-sm border rounded hover:bg-neutral-100">Previous</a>
            <?php endif; ?>

            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
            <a href="?<?= http_build_query(array_filter(['status' => $currentStatus, 'page' => $pagination['current_page'] + 1])) ?>"
               class="px-3 py-1 text-sm border rounded hover:bg-neutral-100">Next</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
