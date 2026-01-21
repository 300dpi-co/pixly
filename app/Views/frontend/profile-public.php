<?php
$displayName = $user['display_name'] ?: $user['username'];
$avatarUrl = $user['avatar_path'] ? uploads_url($user['avatar_path']) : null;
$joinDate = date('F Y', strtotime($user['created_at']));
?>

<div class="min-h-screen bg-neutral-50 dark:bg-neutral-950">
    <!-- Profile Header -->
    <div class="bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 dark:from-primary-900 dark:via-primary-800 dark:to-neutral-900">
        <div class="max-w-5xl mx-auto px-4 py-12 sm:py-16">
            <div class="flex flex-col sm:flex-row items-center sm:items-end gap-6">
                <!-- Avatar -->
                <div class="relative">
                    <?php if ($avatarUrl): ?>
                    <img src="<?= e($avatarUrl) ?>" alt="<?= e($displayName) ?>"
                         class="w-32 h-32 sm:w-40 sm:h-40 rounded-full object-cover border-4 border-white/20 shadow-xl">
                    <?php else: ?>
                    <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-full bg-white/20 border-4 border-white/20 shadow-xl flex items-center justify-center">
                        <span class="text-5xl sm:text-6xl font-bold text-white/90"><?= strtoupper(substr($user['username'], 0, 1)) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (in_array($user['role'], ['admin', 'moderator'])): ?>
                    <div class="absolute -bottom-1 -right-1 bg-amber-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow">
                        <?= $user['role'] === 'admin' ? 'Admin' : 'Mod' ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- User Info -->
                <div class="text-center sm:text-left flex-1">
                    <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2"><?= e($displayName) ?></h1>
                    <p class="text-white/70 text-lg mb-3">@<?= e($user['username']) ?></p>

                    <?php if ($user['location']): ?>
                    <p class="text-white/60 flex items-center justify-center sm:justify-start gap-2 mb-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <?= e($user['location']) ?>
                    </p>
                    <?php endif; ?>

                    <!-- Social Links -->
                    <div class="flex items-center justify-center sm:justify-start gap-3">
                        <?php if ($user['website']): ?>
                        <a href="<?= e($user['website']) ?>" target="_blank" rel="noopener"
                           class="p-2 bg-white/10 hover:bg-white/20 rounded-lg text-white transition" title="Website">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                        </a>
                        <?php endif; ?>

                        <?php if ($user['twitter_handle']): ?>
                        <a href="https://twitter.com/<?= e($user['twitter_handle']) ?>" target="_blank" rel="noopener"
                           class="p-2 bg-white/10 hover:bg-white/20 rounded-lg text-white transition" title="Twitter">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                        <?php endif; ?>

                        <?php if ($user['instagram_handle']): ?>
                        <a href="https://instagram.com/<?= e($user['instagram_handle']) ?>" target="_blank" rel="noopener"
                           class="p-2 bg-white/10 hover:bg-white/20 rounded-lg text-white transition" title="Instagram">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Edit Button (if owner) -->
                <?php if ($isOwner): ?>
                <a href="<?= $view->url('/profile') ?>" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg font-medium transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Profile
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800">
        <div class="max-w-5xl mx-auto px-4">
            <div class="flex items-center justify-center sm:justify-start gap-8 py-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-white"><?= number_format($stats['uploads']) ?></div>
                    <div class="text-sm text-neutral-500 dark:text-neutral-400">Uploads</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-white"><?= number_format($stats['total_views']) ?></div>
                    <div class="text-sm text-neutral-500 dark:text-neutral-400">Views</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-white"><?= number_format($stats['total_favorites']) ?></div>
                    <div class="text-sm text-neutral-500 dark:text-neutral-400">Favorites</div>
                </div>
                <div class="text-center">
                    <div class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Joined</div>
                    <div class="text-sm text-neutral-500 dark:text-neutral-400"><?= e($joinDate) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bio Section -->
    <?php if ($user['bio']): ?>
    <div class="max-w-5xl mx-auto px-4 py-6">
        <div class="bg-white dark:bg-neutral-900 rounded-xl p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-3">About</h2>
            <p class="text-neutral-700 dark:text-neutral-300 whitespace-pre-line"><?= e($user['bio']) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- User's Images -->
    <div class="max-w-5xl mx-auto px-4 py-8">
        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-6">
            <?= $isOwner ? 'Your Uploads' : 'Uploads' ?>
            <span class="text-neutral-400 font-normal">(<?= number_format($stats['uploads']) ?>)</span>
        </h2>

        <?php if (empty($images)): ?>
        <div class="text-center py-16 bg-white dark:bg-neutral-900 rounded-xl">
            <div class="w-16 h-16 bg-neutral-100 dark:bg-neutral-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">No uploads yet</h3>
            <p class="text-neutral-500 dark:text-neutral-400">
                <?= $isOwner ? 'Start sharing your images with the world!' : 'This user hasn\'t uploaded any images yet.' ?>
            </p>
            <?php if ($isOwner): ?>
            <a href="<?= $view->url('/upload') ?>" class="inline-flex items-center gap-2 mt-4 px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Upload Now
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            <?php foreach ($images as $image):
                $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path'];
            ?>
            <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="group block">
                <div class="aspect-square bg-neutral-200 dark:bg-neutral-800 rounded-lg overflow-hidden relative">
                    <img data-src="<?= e(uploads_url($thumbSrc)) ?>"
                         src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3C/svg%3E"
                         alt="<?= e($image['title']) ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 lazy-image"
                         loading="lazy">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                        <div class="flex items-center gap-3 text-white text-xs">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <?= number_format($image['view_count']) ?>
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <?= number_format($image['favorite_count']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
