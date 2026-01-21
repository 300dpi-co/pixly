<div class="min-h-[80vh] py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white dark:bg-neutral-800 shadow rounded-lg overflow-hidden">
            <!-- Profile Header -->
            <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-8">
                <div class="flex items-center space-x-4">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center text-primary-600 text-3xl font-bold">
                        <?= strtoupper(substr($user->username, 0, 1)) ?>
                    </div>
                    <div class="text-white">
                        <h1 class="text-2xl font-bold"><?= e($user->username) ?></h1>
                        <p class="opacity-90"><?= e($user->email) ?></p>
                    </div>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="p-6 space-y-6">
                <?php if ($view->hasFlash('success')): ?>
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
                    <?= e($view->flash('success')) ?>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Username</h3>
                        <p class="mt-1 text-neutral-900 dark:text-white"><?= e($user->username) ?></p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Email</h3>
                        <p class="mt-1 text-neutral-900 dark:text-white"><?= e($user->email) ?></p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Role</h3>
                        <p class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?php if ($user->role === 'admin' || $user->role === 'superadmin'): ?>
                                    bg-purple-100 dark:bg-purple-900/50 text-purple-800 dark:text-purple-300
                                <?php elseif ($user->role === 'moderator'): ?>
                                    bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300
                                <?php else: ?>
                                    bg-neutral-100 dark:bg-neutral-700 text-neutral-800 dark:text-neutral-200
                                <?php endif; ?>
                            ">
                                <?= ucfirst($user->role) ?>
                            </span>
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Member Since</h3>
                        <p class="mt-1 text-neutral-900 dark:text-white"><?= date('F j, Y', strtotime($user->created_at)) ?></p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Status</h3>
                        <p class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?php if ($user->status === 'active'): ?>
                                    bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300
                                <?php else: ?>
                                    bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300
                                <?php endif; ?>
                            ">
                                <?= ucfirst($user->status) ?>
                            </span>
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Last Login</h3>
                        <p class="mt-1 text-neutral-900 dark:text-white">
                            <?= $user->last_login_at ? date('F j, Y g:i A', strtotime($user->last_login_at)) : 'Never' ?>
                        </p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="pt-6 border-t border-neutral-200 dark:border-neutral-700">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Quick Links</h2>
                    <div class="flex flex-wrap gap-3">
                        <a href="<?= $view->url('/favorites') ?>" class="inline-flex items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg text-sm font-medium text-neutral-700 dark:text-neutral-200 bg-white dark:bg-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            My Favorites
                        </a>
                        <a href="<?= $view->url('/gallery') ?>" class="inline-flex items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg text-sm font-medium text-neutral-700 dark:text-neutral-200 bg-white dark:bg-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Browse Gallery
                        </a>
                        <?php if (in_array($user->role, ['admin', 'superadmin', 'moderator'])): ?>
                        <a href="<?= $view->url('/admin') ?>" class="inline-flex items-center px-4 py-2 border border-primary-300 dark:border-primary-700 rounded-lg text-sm font-medium text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/30 hover:bg-primary-100 dark:hover:bg-primary-900/50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Admin Panel
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Logout -->
                <div class="pt-6 border-t border-neutral-200 dark:border-neutral-700">
                    <a href="<?= $view->url('/logout') ?>" class="inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-700 rounded-lg text-sm font-medium text-red-700 dark:text-red-400 bg-white dark:bg-neutral-800 hover:bg-red-50 dark:hover:bg-red-900/20">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
