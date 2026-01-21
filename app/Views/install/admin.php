<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-6 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Create Admin Account</h1>
                <p class="text-sm text-slate-500">Set up your administrator account</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="<?= url('/install/admin') ?>" method="POST" class="p-6">
        <?= csrf_field() ?>

        <?php if ($view->error('database')): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
            <?= e($view->error('database')) ?>
        </div>
        <?php endif; ?>

        <div class="space-y-5">
            <!-- Username -->
            <div>
                <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                <input type="text" id="username" name="username"
                       value="<?= e(old('username', '')) ?>"
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 <?= $view->error('username') ? 'border-red-300 bg-red-50' : '' ?>"
                       placeholder="admin"
                       autocomplete="username">
                <?php if ($view->error('username')): ?>
                <p class="mt-1 text-sm text-red-600"><?= e($view->error('username')) ?></p>
                <?php else: ?>
                <p class="mt-1 text-sm text-slate-500">Letters, numbers, and underscores only. At least 3 characters.</p>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                <input type="email" id="email" name="email"
                       value="<?= e(old('email', '')) ?>"
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 <?= $view->error('email') ? 'border-red-300 bg-red-50' : '' ?>"
                       placeholder="admin@example.com"
                       autocomplete="email">
                <?php if ($view->error('email')): ?>
                <p class="mt-1 text-sm text-red-600"><?= e($view->error('email')) ?></p>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" id="password" name="password"
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 <?= $view->error('password') ? 'border-red-300 bg-red-50' : '' ?>"
                       placeholder="Enter a strong password"
                       autocomplete="new-password">
                <?php if ($view->error('password')): ?>
                <p class="mt-1 text-sm text-red-600"><?= e($view->error('password')) ?></p>
                <?php else: ?>
                <p class="mt-1 text-sm text-slate-500">At least 12 characters. Use a mix of letters, numbers, and symbols.</p>
                <?php endif; ?>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 <?= $view->error('password_confirmation') ? 'border-red-300 bg-red-50' : '' ?>"
                       placeholder="Confirm your password"
                       autocomplete="new-password">
                <?php if ($view->error('password_confirmation')): ?>
                <p class="mt-1 text-sm text-red-600"><?= e($view->error('password_confirmation')) ?></p>
                <?php endif; ?>
            </div>

            <!-- Security Note -->
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h3 class="font-medium text-amber-800">Security Note</h3>
                        <p class="text-sm text-amber-700 mt-1">Choose a strong, unique password. You'll use these credentials to access the admin panel.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-slate-200 flex justify-between items-center">
            <a href="<?= url('/install/setup') ?>" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-800 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
            <button type="submit" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg font-medium transition">
                Create Account & Continue
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </form>
</div>
