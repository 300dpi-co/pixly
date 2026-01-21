<div class="space-y-6">
    <!-- Google Analytics Connection -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-orange-500" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.545 10.239v3.821h5.445c-.712 2.315-2.647 3.972-5.445 3.972a6.033 6.033 0 110-12.064c1.498 0 2.866.549 3.921 1.453l2.814-2.814A9.969 9.969 0 0012.545 2C7.021 2 2.543 6.477 2.543 12s4.478 10 10.002 10c8.396 0 10.249-7.85 9.426-11.748l-9.426-.013z"/>
                </svg>
                <h2 class="text-lg font-semibold">Google Analytics</h2>
            </div>
            <?php if ($gaSettings['enabled']): ?>
                <span class="flex items-center gap-2 text-green-600 text-sm">
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                    Connected
                </span>
            <?php else: ?>
                <span class="flex items-center gap-2 text-neutral-400 text-sm">
                    <span class="w-2 h-2 bg-neutral-300 rounded-full"></span>
                    Not Connected
                </span>
            <?php endif; ?>
        </div>
        <div class="p-6">
            <form method="POST" action="/admin/analytics/google" class="flex flex-col sm:flex-row gap-4">
                <?= csrf_field() ?>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Measurement ID</label>
                    <input type="text" name="google_analytics_id"
                           value="<?= e($gaSettings['measurement_id']) ?>"
                           placeholder="G-XXXXXXXXXX"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <p class="text-xs text-neutral-500 mt-1">
                        Find this in Google Analytics > Admin > Data Streams > Your Stream
                    </p>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                        <?= $gaSettings['enabled'] ? 'Update' : 'Connect' ?>
                    </button>
                    <?php if ($gaSettings['enabled']): ?>
                        <button type="submit" name="google_analytics_id" value=""
                                class="px-4 py-2 text-red-600 hover:text-red-700"
                                onclick="return confirm('Disconnect Google Analytics?')">
                            Disconnect
                        </button>
                    <?php endif; ?>
                </div>
            </form>

            <?php if ($gaSettings['enabled']): ?>
            <div class="mt-4 pt-4 border-t">
                <p class="text-sm text-neutral-600 mb-2">
                    <strong>Tracking Active:</strong> Google Analytics is now collecting data from your site.
                </p>
                <a href="https://analytics.google.com/" target="_blank"
                   class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700">
                    Open Google Analytics Dashboard
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-neutral-500">Total Images</p>
                    <p class="text-2xl font-semibold"><?= number_format($stats['total_images']) ?></p>
                    <p class="text-xs text-green-600"><?= number_format($stats['published_images']) ?> published</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-neutral-500">Views This Week</p>
                    <p class="text-2xl font-semibold"><?= number_format($stats['views_this_week']) ?></p>
                    <?php if ($stats['views_growth'] != 0): ?>
                        <p class="text-xs <?= $stats['views_growth'] >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $stats['views_growth'] >= 0 ? '+' : '' ?><?= $stats['views_growth'] ?>% vs last week
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-neutral-500">Total Favorites</p>
                    <p class="text-2xl font-semibold"><?= number_format($stats['total_favorites']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-neutral-500">Total Users</p>
                    <p class="text-2xl font-semibold"><?= number_format($stats['total_users']) ?></p>
                    <p class="text-xs text-green-600"><?= number_format($stats['active_users']) ?> active</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-neutral-500">All-Time Views</p>
                    <p class="text-2xl font-semibold"><?= number_format($stats['total_views']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-neutral-500">Comments</p>
                    <p class="text-2xl font-semibold"><?= number_format($stats['total_comments']) ?></p>
                </div>
            </div>
        </div>

        <?php if ($apiStats): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-cyan-100 text-cyan-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-neutral-500">API Calls (30d)</p>
                    <p class="text-2xl font-semibold"><?= number_format($apiStats['total_calls'] ?? 0) ?></p>
                    <?php if (($apiStats['errors'] ?? 0) > 0): ?>
                        <p class="text-xs text-red-600"><?= number_format($apiStats['errors']) ?> errors</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Images -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Top Images by Views</h2>
            </div>
            <div class="p-6">
                <?php if (empty($topImages)): ?>
                    <p class="text-neutral-500 text-sm">No images yet.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($topImages as $index => $image): ?>
                            <div class="flex items-center gap-3">
                                <span class="text-neutral-400 text-sm w-5"><?= $index + 1 ?></span>
                                <?php if ($image['thumbnail_path']): ?>
                                    <img src="/uploads/<?= e($image['thumbnail_path']) ?>"
                                         alt="" class="w-10 h-10 object-cover rounded">
                                <?php else: ?>
                                    <div class="w-10 h-10 bg-neutral-200 rounded flex items-center justify-center">
                                        <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1 min-w-0">
                                    <a href="/admin/images/<?= $image['id'] ?>/edit"
                                       class="text-sm font-medium hover:text-blue-600 truncate block">
                                        <?= e($image['title']) ?>
                                    </a>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-medium"><?= number_format($image['view_count']) ?></span>
                                    <span class="text-xs text-neutral-500">views</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Categories -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Top Categories</h2>
            </div>
            <div class="p-6">
                <?php if (empty($topCategories)): ?>
                    <p class="text-neutral-500 text-sm">No categories yet.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($topCategories as $category): ?>
                            <div class="flex items-center justify-between">
                                <div>
                                    <a href="/category/<?= e($category['slug']) ?>"
                                       class="font-medium hover:text-blue-600" target="_blank">
                                        <?= e($category['name']) ?>
                                    </a>
                                    <span class="text-sm text-neutral-500 ml-2">
                                        <?= number_format($category['image_count']) ?> images
                                    </span>
                                </div>
                                <span class="text-sm">
                                    <?= number_format($category['total_views'] ?? 0) ?> views
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Searches -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Top Searches</h2>
            </div>
            <div class="p-6">
                <?php if (empty($topSearches)): ?>
                    <p class="text-neutral-500 text-sm">No search data yet.</p>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach ($topSearches as $search): ?>
                            <div class="flex items-center justify-between py-1">
                                <span class="text-sm"><?= e($search['query']) ?></span>
                                <div class="text-right">
                                    <span class="text-sm font-medium"><?= number_format($search['search_count']) ?></span>
                                    <span class="text-xs text-neutral-500 ml-1">
                                        (<?= number_format($search['result_count']) ?> results)
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Tags -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Top Tags</h2>
            </div>
            <div class="p-6">
                <?php if (empty($topTags)): ?>
                    <p class="text-neutral-500 text-sm">No tags yet.</p>
                <?php else: ?>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($topTags as $tag): ?>
                            <a href="/tag/<?= e($tag['slug']) ?>"
                               class="inline-flex items-center gap-1 px-3 py-1 bg-neutral-100 hover:bg-neutral-200 rounded-full text-sm"
                               target="_blank">
                                <?= e($tag['name']) ?>
                                <span class="text-neutral-500"><?= number_format($tag['usage_count']) ?></span>
                                <?php if (($tag['trend_score'] ?? 0) > 20): ?>
                                    <span class="text-green-600 text-xs" title="Trending">^</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Views Chart -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Views (Last 14 Days)</h2>
            </div>
            <div class="p-6">
                <?php if (empty($dailyViews)): ?>
                    <p class="text-neutral-500 text-sm">No view data yet.</p>
                <?php else: ?>
                    <div class="h-48 flex items-end gap-1">
                        <?php
                        $maxViews = max(array_column($dailyViews, 'views'));
                        foreach ($dailyViews as $day):
                            $height = $maxViews > 0 ? ($day['views'] / $maxViews) * 100 : 0;
                        ?>
                            <div class="flex-1 flex flex-col items-center gap-1">
                                <div class="w-full bg-blue-500 rounded-t transition-all hover:bg-blue-600"
                                     style="height: <?= max(4, $height) ?>%"
                                     title="<?= e($day['date']) ?>: <?= number_format($day['views']) ?> views"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex justify-between text-xs text-neutral-500 mt-2">
                        <span><?= date('M j', strtotime($dailyViews[0]['date'])) ?></span>
                        <span><?= date('M j', strtotime(end($dailyViews)['date'])) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Uploads -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Recent Uploads</h2>
            </div>
            <div class="p-6">
                <?php if (empty($recentUploads)): ?>
                    <p class="text-neutral-500 text-sm">No uploads yet.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($recentUploads as $image): ?>
                            <div class="flex items-center gap-3">
                                <?php if ($image['thumbnail_path']): ?>
                                    <img src="/uploads/<?= e($image['thumbnail_path']) ?>"
                                         alt="" class="w-12 h-12 object-cover rounded">
                                <?php else: ?>
                                    <div class="w-12 h-12 bg-neutral-200 rounded flex items-center justify-center">
                                        <svg class="w-6 h-6 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1 min-w-0">
                                    <a href="/admin/images/<?= $image['id'] ?>/edit"
                                       class="text-sm font-medium hover:text-blue-600 truncate block">
                                        <?= e($image['title']) ?>
                                    </a>
                                    <p class="text-xs text-neutral-500">
                                        <?= time_ago($image['created_at']) ?>
                                    </p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full <?= $image['status'] === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                                    <?= e($image['status']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($apiStats && ($apiStats['total_tokens'] ?? 0) > 0): ?>
    <!-- API Usage -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">AI API Usage (Last 30 Days)</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="text-center">
                    <p class="text-3xl font-bold text-blue-600"><?= number_format($apiStats['total_calls'] ?? 0) ?></p>
                    <p class="text-sm text-neutral-500">API Calls</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-green-600"><?= number_format($apiStats['total_tokens'] ?? 0) ?></p>
                    <p class="text-sm text-neutral-500">Tokens Used</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold <?= ($apiStats['errors'] ?? 0) > 0 ? 'text-red-600' : 'text-neutral-400' ?>">
                        <?= number_format($apiStats['errors'] ?? 0) ?>
                    </p>
                    <p class="text-sm text-neutral-500">Errors</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
