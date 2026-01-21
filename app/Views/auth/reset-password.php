<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-neutral-900 dark:text-white">Reset Password</h2>
            <p class="mt-2 text-neutral-600 dark:text-neutral-400">Enter your new password</p>
        </div>

        <?php $errors = $view->errors(); ?>
        <?php if (!empty($errors)): ?>
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="<?= $view->url('/reset-password') ?>" method="POST">
            <?= $view->csrf() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">

            <div class="space-y-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">New Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                           class="mt-1 block w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white dark:placeholder-neutral-400 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Min 12 characters">
                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Must be at least 12 characters long.</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                           class="mt-1 block w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white dark:placeholder-neutral-400 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Confirm your password">
                </div>
            </div>

            <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 font-medium">
                Reset Password
            </button>
        </form>

        <p class="text-center text-neutral-600 dark:text-neutral-400">
            <a href="<?= $view->url('/login') ?>" class="text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300 font-medium">Back to login</a>
        </p>
    </div>
</div>
