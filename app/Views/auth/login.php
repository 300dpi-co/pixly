<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-neutral-900 dark:text-white">Welcome back</h2>
            <p class="mt-2 text-neutral-600 dark:text-neutral-400">Sign in to your account</p>
        </div>

        <?php $errors = $view->errors(); ?>
        <?php if (!empty($errors)): ?>
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($view->hasFlash('info')): ?>
        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 px-4 py-3 rounded-lg text-sm break-all">
            <?= e($view->flash('info')) ?>
        </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="<?= $view->url('/login') ?>" method="POST">
            <?= $view->csrf() ?>

            <div class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           value="<?= e($view->old('email')) ?>"
                           class="mt-1 block w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="mt-1 block w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 rounded">
                    <label for="remember" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">Remember me</label>
                </div>

                <a href="<?= $view->url('/forgot-password') ?>" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300">
                    Forgot password?
                </a>
            </div>

            <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 font-medium">
                Sign in
            </button>
        </form>

        <p class="text-center text-neutral-600 dark:text-neutral-400">
            Don't have an account?
            <a href="<?= $view->url('/register') ?>" class="text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300 font-medium">Register</a>
        </p>
    </div>
</div>
