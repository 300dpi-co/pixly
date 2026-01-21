<?php
/**
 * User Settings / Profile Edit Page
 */
$displayName = $user['display_name'] ?: $user['username'];
$avatarUrl = $user['avatar_path'] ? '/uploads/' . $user['avatar_path'] : null;
$errors = session_get_flash('errors') ?? [];
$old = session_get_flash('old') ?? [];
$success = session_get_flash('success');
$error = session_get_flash('error');
?>

<div class="min-h-screen bg-neutral-50 dark:bg-neutral-950">
    <!-- Header -->
    <div class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800">
        <div class="max-w-4xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Settings</h1>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Manage your profile and account settings</p>
                </div>
                <a href="<?= $view->url('/user/' . $user['username']) ?>"
                   class="flex items-center gap-2 px-4 py-2 bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-300 rounded-lg font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    View Public Profile
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Success/Error Messages -->
        <?php if ($success): ?>
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-green-700 dark:text-green-300"><?= e($success) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-red-700 dark:text-red-300"><?= e($error) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid gap-8">
            <!-- Avatar Section -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Profile Photo</h2>
                <div class="flex items-center gap-6">
                    <!-- Current Avatar -->
                    <div class="relative">
                        <?php if ($avatarUrl): ?>
                        <img src="<?= e($avatarUrl) ?>" alt="<?= e($displayName) ?>"
                             class="w-24 h-24 rounded-full object-cover border-4 border-neutral-200 dark:border-neutral-700">
                        <?php else: ?>
                        <div class="w-24 h-24 rounded-full bg-primary-100 dark:bg-primary-900 border-4 border-neutral-200 dark:border-neutral-700 flex items-center justify-center">
                            <span class="text-3xl font-bold text-primary-600 dark:text-primary-400"><?= strtoupper(substr($user['username'], 0, 1)) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Upload Form -->
                    <form action="<?= $view->url('/profile/avatar') ?>" method="POST" enctype="multipart/form-data" class="flex-1">
                        <?= csrf_field() ?>
                        <div class="flex items-center gap-4">
                            <label class="flex-1">
                                <input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden" id="avatarInput">
                                <div class="px-4 py-2 bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-300 rounded-lg font-medium cursor-pointer transition text-center">
                                    Choose Image
                                </div>
                            </label>
                            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition">
                                Upload
                            </button>
                        </div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">JPG, PNG, GIF or WebP. Max 2MB.</p>
                    </form>
                </div>
            </div>

            <!-- Profile Information -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Profile Information</h2>
                <form action="<?= $view->url('/profile') ?>" method="POST" class="space-y-4">
                    <?= csrf_field() ?>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <!-- Username (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Username</label>
                            <input type="text" value="<?= e($user['username']) ?>" disabled
                                   class="w-full px-4 py-2 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg text-neutral-500 dark:text-neutral-400 cursor-not-allowed">
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Username cannot be changed</p>
                        </div>

                        <!-- Display Name -->
                        <div>
                            <label for="display_name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Display Name</label>
                            <input type="text" name="display_name" id="display_name"
                                   value="<?= e($old['display_name'] ?? $user['display_name'] ?? '') ?>"
                                   maxlength="100"
                                   class="w-full px-4 py-2 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white transition"
                                   placeholder="Your display name">
                            <?php if (isset($errors['display_name'])): ?>
                            <p class="text-xs text-red-500 mt-1"><?= e($errors['display_name']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Bio -->
                    <div>
                        <label for="bio" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Bio</label>
                        <textarea name="bio" id="bio" rows="3" maxlength="500"
                                  class="w-full px-4 py-2 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white transition resize-none"
                                  placeholder="Tell us about yourself..."><?= e($old['bio'] ?? $user['bio'] ?? '') ?></textarea>
                        <?php if (isset($errors['bio'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?= e($errors['bio']) ?></p>
                        <?php endif; ?>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Max 500 characters</p>
                    </div>

                    <!-- Location -->
                    <div>
                        <label for="location" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Location</label>
                        <input type="text" name="location" id="location"
                               value="<?= e($old['location'] ?? $user['location'] ?? '') ?>"
                               maxlength="100"
                               class="w-full px-4 py-2 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white transition"
                               placeholder="City, Country">
                    </div>

                    <!-- Website -->
                    <div>
                        <label for="website" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Website</label>
                        <input type="url" name="website" id="website"
                               value="<?= e($old['website'] ?? $user['website'] ?? '') ?>"
                               class="w-full px-4 py-2 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white transition"
                               placeholder="https://yourwebsite.com">
                        <?php if (isset($errors['website'])): ?>
                        <p class="text-xs text-red-500 mt-1"><?= e($errors['website']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Social Links -->
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="twitter_handle" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Twitter / X</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400">@</span>
                                <input type="text" name="twitter_handle" id="twitter_handle"
                                       value="<?= e($old['twitter_handle'] ?? $user['twitter_handle'] ?? '') ?>"
                                       maxlength="50"
                                       class="w-full pl-8 pr-4 py-2 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white transition"
                                       placeholder="username">
                            </div>
                        </div>
                        <div>
                            <label for="instagram_handle" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Instagram</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400">@</span>
                                <input type="text" name="instagram_handle" id="instagram_handle"
                                       value="<?= e($old['instagram_handle'] ?? $user['instagram_handle'] ?? '') ?>"
                                       maxlength="50"
                                       class="w-full pl-8 pr-4 py-2 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white transition"
                                       placeholder="username">
                            </div>
                        </div>
                    </div>

                    <!-- Privacy Setting -->
                    <div class="flex items-center justify-between p-4 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                        <div>
                            <h3 class="font-medium text-neutral-900 dark:text-white">Public Profile</h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Allow others to see your profile and uploads</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_public" value="1" class="sr-only peer"
                                   <?= ($old['is_public'] ?? $user['is_public'] ?? 1) ? 'checked' : '' ?>>
                            <div class="w-11 h-6 bg-neutral-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-neutral-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                        </label>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Security Section -->
            <div id="security" class="bg-white dark:bg-neutral-900 rounded-xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Change Password</h2>
                <form action="<?= $view->url('/profile/password') ?>" method="POST" class="space-y-4">
                    <?= csrf_field() ?>

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Current Password</label>
                        <input type="password" name="current_password" id="current_password" required
                               class="w-full px-4 py-2 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white transition">
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">New Password</label>
                            <input type="password" name="new_password" id="new_password" required minlength="8"
                                   class="w-full px-4 py-2 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white transition">
                        </div>
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" required minlength="8"
                                   class="w-full px-4 py-2 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white transition">
                        </div>
                    </div>

                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Password must be at least 8 characters</p>

                    <div class="pt-2">
                        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Account Stats -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Account Statistics</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                        <div class="text-2xl font-bold text-neutral-900 dark:text-white"><?= number_format($stats['uploads']) ?></div>
                        <div class="text-sm text-neutral-500 dark:text-neutral-400">Uploads</div>
                    </div>
                    <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                        <div class="text-2xl font-bold text-neutral-900 dark:text-white"><?= number_format($stats['favorites']) ?></div>
                        <div class="text-sm text-neutral-500 dark:text-neutral-400">Favorites</div>
                    </div>
                    <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                        <div class="text-2xl font-bold text-neutral-900 dark:text-white"><?= e($user['role']) ?></div>
                        <div class="text-sm text-neutral-500 dark:text-neutral-400">Role</div>
                    </div>
                    <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                        <div class="text-sm font-bold text-neutral-900 dark:text-white"><?= date('M Y', strtotime($user['created_at'])) ?></div>
                        <div class="text-sm text-neutral-500 dark:text-neutral-400">Joined</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show selected filename
document.getElementById('avatarInput')?.addEventListener('change', function() {
    const fileName = this.files[0]?.name;
    if (fileName) {
        this.closest('label').querySelector('div').textContent = fileName;
    }
});
</script>
