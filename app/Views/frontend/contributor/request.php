<div class="min-h-[60vh] py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/30 mb-4">
                <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Become a Contributor</h1>
            <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                Share your amazing images with our community
            </p>
        </div>

        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-lg p-6 sm:p-8">
            <!-- Benefits Section -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Contributor Benefits</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-neutral-900 dark:text-white">Upload Images</p>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Share your photos with thousands of users</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-neutral-900 dark:text-white">Build Your Portfolio</p>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Showcase your work on your public profile</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-neutral-900 dark:text-white">Get Recognized</p>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Earn appreciation from the community</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-neutral-900 dark:text-white">Contributor Badge</p>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Stand out with a special contributor badge</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-neutral-200 dark:border-neutral-700 pt-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Submit Your Request</h2>

                <form action="<?= $view->url('/contributor/request') ?>" method="POST">
                    <?= $view->csrf() ?>

                    <div class="mb-6">
                        <label for="reason" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Why do you want to be a contributor? (optional)
                        </label>
                        <textarea id="reason" name="reason" rows="4"
                                  class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                  placeholder="Tell us about yourself, your photography experience, or what kind of images you'd like to share..."></textarea>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-2">
                            Providing details can help speed up the review process.
                        </p>
                    </div>

                    <div class="bg-neutral-50 dark:bg-neutral-700/50 rounded-lg p-4 mb-6">
                        <p class="text-sm text-neutral-600 dark:text-neutral-300">
                            <strong>Note:</strong> Your request will be reviewed by our team. You'll be notified once a decision is made.
                            Approved contributors must follow our community guidelines when uploading images.
                        </p>
                    </div>

                    <button type="submit"
                            class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Submit Request
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
