<?php
/**
 * Private Profile View
 * Shown when a user's profile is not public and viewer is not the owner
 */
?>

<div class="min-h-screen bg-neutral-50 dark:bg-neutral-950 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
        <!-- Lock Icon -->
        <div class="w-24 h-24 bg-neutral-200 dark:bg-neutral-800 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-neutral-400 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <!-- Message -->
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mb-2">
            This Profile is Private
        </h1>
        <p class="text-neutral-600 dark:text-neutral-400 mb-6">
            <span class="font-medium">@<?= e($username) ?></span> has chosen to keep their profile private.
        </p>

        <!-- Back Button -->
        <a href="<?= $view->url('/') ?>"
           class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Gallery
        </a>
    </div>
</div>
