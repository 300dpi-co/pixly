<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-neutral-900 dark:text-white">Create an account</h2>
            <p class="mt-2 text-neutral-600 dark:text-neutral-400">Join <?= config('app.name') ?> today</p>
        </div>

        <?php $errors = $view->errors(); ?>
        <?php if (!empty($errors)): ?>
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="<?= $view->url('/register') ?>" method="POST">
            <?= $view->csrf() ?>

            <div class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Username</label>
                    <input id="username" name="username" type="text" autocomplete="username" required
                           value="<?= e($view->old('username')) ?>"
                           class="mt-1 block w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white dark:placeholder-neutral-400 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Choose a username">
                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Letters, numbers, dashes, and underscores only. Min 3 characters.</p>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           value="<?= e($view->old('email')) ?>"
                           class="mt-1 block w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white dark:placeholder-neutral-400 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="you@example.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                           class="mt-1 block w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white dark:placeholder-neutral-400 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Min 12 characters">
                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Must be at least 12 characters long.</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                           class="mt-1 block w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white dark:placeholder-neutral-400 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Confirm your password">
                </div>
            </div>

            <div class="flex items-start">
                <input id="terms" name="terms" type="checkbox" required
                       class="h-4 w-4 mt-1 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 rounded">
                <label for="terms" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                    I agree to the
                    <a href="<?= $view->url('/terms') ?>" class="text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300">Terms of Service</a>
                    and
                    <a href="<?= $view->url('/privacy') ?>" class="text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300">Privacy Policy</a>
                </label>
            </div>

            <?php if (!empty($contributorSystemEnabled)): ?>
            <div class="border-t border-neutral-200 dark:border-neutral-700 pt-4 mt-4">
                <div class="flex items-start" x-data="{ wantContributor: false }">
                    <input id="want_contributor" name="want_contributor" type="checkbox" value="1"
                           x-model="wantContributor"
                           class="h-4 w-4 mt-1 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 rounded">
                    <div class="ml-2 flex-1">
                        <label for="want_contributor" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                            I want to be a contributor
                        </label>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            Contributors can upload images to the gallery. Your request will be reviewed by our team.
                        </p>
                        <div x-show="wantContributor" x-transition class="mt-3">
                            <label for="contributor_reason" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                Why do you want to be a contributor? (optional)
                            </label>
                            <textarea id="contributor_reason" name="contributor_reason" rows="2"
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"
                                      placeholder="Tell us about yourself and your photography..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 font-medium">
                Create Account
            </button>
        </form>

        <p class="text-center text-neutral-600 dark:text-neutral-400">
            Already have an account?
            <a href="<?= $view->url('/login') ?>" class="text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300 font-medium">Sign in</a>
        </p>
    </div>
</div>
