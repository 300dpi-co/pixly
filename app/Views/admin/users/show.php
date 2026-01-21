<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="/admin/users" class="text-neutral-500 hover:text-neutral-700 inline-flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Users
        </a>
        <a href="/admin/users/<?= $user->id ?>/edit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
            Edit User
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Info Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center mb-6">
                <?php if ($user->avatar_path): ?>
                    <img src="/uploads/<?= e($user->avatar_path) ?>" alt="" class="w-24 h-24 rounded-full mx-auto object-cover">
                <?php else: ?>
                    <div class="w-24 h-24 bg-primary-100 text-primary-600 rounded-full mx-auto flex items-center justify-center text-3xl font-bold">
                        <?= strtoupper(substr($user->username, 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <h2 class="text-xl font-semibold mt-4"><?= e($user->username) ?></h2>
                <p class="text-neutral-500"><?= e($user->email) ?></p>
            </div>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-neutral-500">Role</span>
                    <span class="px-2 py-1 text-xs rounded-full
                        <?php
                        echo match($user->role) {
                            'superadmin' => 'bg-red-100 text-red-800',
                            'admin' => 'bg-purple-100 text-purple-800',
                            'moderator' => 'bg-blue-100 text-blue-800',
                            default => 'bg-neutral-100 text-neutral-600'
                        };
                        ?>">
                        <?= ucfirst($user->role) ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-500">Status</span>
                    <span class="px-2 py-1 text-xs rounded-full
                        <?php
                        echo match($user->status) {
                            'active' => 'bg-green-100 text-green-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'suspended' => 'bg-orange-100 text-orange-800',
                            'banned' => 'bg-red-100 text-red-800',
                            default => 'bg-neutral-100 text-neutral-600'
                        };
                        ?>">
                        <?= ucfirst($user->status) ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-500">Email Verified</span>
                    <span><?= $user->email_verified_at ? 'Yes' : 'No' ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-500">Joined</span>
                    <span><?= date('M j, Y', strtotime($user->created_at)) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-500">Last Login</span>
                    <span><?= $user->last_login_at ? time_ago($user->last_login_at) : 'Never' ?></span>
                </div>
            </div>

            <?php if ($user->bio): ?>
            <div class="mt-6 pt-6 border-t">
                <h3 class="text-sm font-medium text-neutral-700 mb-2">Bio</h3>
                <p class="text-sm text-neutral-600"><?= e($user->bio) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Stats & Activity -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <p class="text-3xl font-bold text-primary-600"><?= number_format($stats['uploads']) ?></p>
                    <p class="text-sm text-neutral-500">Uploads</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <p class="text-3xl font-bold text-red-500"><?= number_format($stats['favorites']) ?></p>
                    <p class="text-sm text-neutral-500">Favorites</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <p class="text-3xl font-bold text-green-600"><?= number_format($stats['comments']) ?></p>
                    <p class="text-sm text-neutral-500">Comments</p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b">
                    <h3 class="font-semibold">Recent Activity</h3>
                </div>
                <div class="p-6">
                    <?php if (empty($recentActivity)): ?>
                        <p class="text-neutral-500 text-center py-4">No recent activity.</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($recentActivity as $activity): ?>
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-full flex items-center justify-center
                                        <?php
                                        echo match($activity['event_type']) {
                                            'login' => 'bg-green-100 text-green-600',
                                            'logout' => 'bg-neutral-100 text-neutral-600',
                                            'failed_login' => 'bg-red-100 text-red-600',
                                            default => 'bg-blue-100 text-blue-600'
                                        };
                                        ?>">
                                        <?php if ($activity['event_type'] === 'login'): ?>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                            </svg>
                                        <?php elseif ($activity['event_type'] === 'logout'): ?>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                        <?php else: ?>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        <?php endif; ?>
                                    </span>
                                    <div>
                                        <p class="font-medium"><?= ucfirst(str_replace('_', ' ', $activity['event_type'])) ?></p>
                                        <?php if ($activity['ip_address']): ?>
                                            <p class="text-neutral-500 text-xs">IP: <?= e($activity['ip_address']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="text-neutral-500"><?= time_ago($activity['created_at']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
