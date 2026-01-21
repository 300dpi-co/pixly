<div class="min-h-[60vh] py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Contributor Request Status</h1>
        </div>

        <?php if (!$contributorRequest): ?>
            <!-- No Request Found -->
            <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-lg p-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-neutral-100 dark:bg-neutral-700 mb-4">
                    <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-neutral-900 dark:text-white mb-2">No Request Found</h2>
                <p class="text-neutral-600 dark:text-neutral-400 mb-6">You haven't submitted a contributor request yet.</p>
                <a href="<?= $view->url('/contributor/request') ?>"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Apply Now
                </a>
            </div>

        <?php elseif ($contributorRequest['status'] === 'pending'): ?>
            <!-- Pending Request -->
            <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-lg p-8">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-100 dark:bg-yellow-900/30 mb-4">
                        <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                        Pending Review
                    </span>
                </div>

                <h2 class="text-xl font-semibold text-neutral-900 dark:text-white text-center mb-2">Your Request is Under Review</h2>
                <p class="text-neutral-600 dark:text-neutral-400 text-center mb-6">
                    Our team is reviewing your contributor request. You'll be notified once a decision is made.
                </p>

                <div class="border-t border-neutral-200 dark:border-neutral-700 pt-6">
                    <dl class="space-y-4">
                        <div class="flex justify-between">
                            <dt class="text-sm text-neutral-500 dark:text-neutral-400">Submitted</dt>
                            <dd class="text-sm font-medium text-neutral-900 dark:text-white">
                                <?= date('M j, Y \a\t g:i A', strtotime($contributorRequest['created_at'])) ?>
                            </dd>
                        </div>
                        <?php if (!empty($contributorRequest['request_reason'])): ?>
                        <div>
                            <dt class="text-sm text-neutral-500 dark:text-neutral-400 mb-1">Your Message</dt>
                            <dd class="text-sm text-neutral-700 dark:text-neutral-300 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg p-3">
                                <?= nl2br(e($contributorRequest['request_reason'])) ?>
                            </dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

        <?php elseif ($contributorRequest['status'] === 'approved'): ?>
            <!-- Approved Request -->
            <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-lg p-8">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 mb-4">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                        Approved
                    </span>
                </div>

                <h2 class="text-xl font-semibold text-neutral-900 dark:text-white text-center mb-2">Congratulations!</h2>
                <p class="text-neutral-600 dark:text-neutral-400 text-center mb-6">
                    Your contributor request has been approved. You can now upload images!
                </p>

                <?php if (!empty($contributorRequest['admin_note'])): ?>
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
                    <p class="text-sm text-green-800 dark:text-green-300">
                        <strong>Admin note:</strong> <?= e($contributorRequest['admin_note']) ?>
                    </p>
                </div>
                <?php endif; ?>

                <div class="text-center">
                    <a href="<?= $view->url('/upload') ?>"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Start Uploading
                    </a>
                </div>
            </div>

        <?php elseif ($contributorRequest['status'] === 'rejected'): ?>
            <!-- Rejected Request -->
            <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-lg p-8">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                        <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                        Not Approved
                    </span>
                </div>

                <h2 class="text-xl font-semibold text-neutral-900 dark:text-white text-center mb-2">Request Not Approved</h2>
                <p class="text-neutral-600 dark:text-neutral-400 text-center mb-6">
                    Unfortunately, your contributor request was not approved at this time.
                </p>

                <?php if (!empty($contributorRequest['admin_note'])): ?>
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                    <p class="text-sm text-red-800 dark:text-red-300">
                        <strong>Reason:</strong> <?= e($contributorRequest['admin_note']) ?>
                    </p>
                </div>
                <?php endif; ?>

                <div class="border-t border-neutral-200 dark:border-neutral-700 pt-6">
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 text-center">
                        You may apply again after some time. Make sure to follow our community guidelines.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-6 text-center">
            <a href="<?= $view->url('/') ?>" class="text-primary-600 dark:text-primary-400 hover:underline">
                Back to Home
            </a>
        </div>
    </div>
</div>
