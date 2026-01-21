<!-- Trending Header -->
<div class="bg-gradient-to-r from-orange-500 to-red-500 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold">Trending Images</h1>
        <p class="text-orange-100 text-sm mt-1">Discover what's popular right now</p>

        <!-- Period Filter -->
        <div class="flex gap-2 mt-4">
            <a href="<?= $view->url('/trending') ?>?period=today"
               class="px-3 py-1.5 text-sm rounded-lg <?= $period === 'today' ? 'bg-white text-orange-600 font-medium' : 'bg-white/20 hover:bg-white/30' ?>">
                Today
            </a>
            <a href="<?= $view->url('/trending') ?>?period=week"
               class="px-3 py-1.5 text-sm rounded-lg <?= $period === 'week' ? 'bg-white text-orange-600 font-medium' : 'bg-white/20 hover:bg-white/30' ?>">
                This Week
            </a>
            <a href="<?= $view->url('/trending') ?>?period=month"
               class="px-3 py-1.5 text-sm rounded-lg <?= $period === 'month' ? 'bg-white text-orange-600 font-medium' : 'bg-white/20 hover:bg-white/30' ?>">
                This Month
            </a>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Main Content -->
        <div class="flex-1 min-w-0">
            <?php if (empty($images)): ?>
            <div class="text-center py-16">
                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-neutral-900 dark:text-white">No trending images</h3>
                <p class="mt-2 text-neutral-600 dark:text-neutral-400">Check back later for trending content.</p>
            </div>
            <?php else: ?>
            <!-- Image Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                <?php foreach ($images as $index => $image): ?>
                <?php
                $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path'];
                $isAnimated = !empty($image['is_animated']);
                ?>
                <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="group block">
                    <div class="aspect-square bg-neutral-100 dark:bg-neutral-800 rounded-lg overflow-hidden relative">
                        <img data-src="<?= e('/uploads/' . $thumbSrc) ?>"
                             src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3C/svg%3E"
                             alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             loading="lazy">
                        <?php if ($index < 3): ?>
                        <div class="absolute top-2 left-2 z-10 w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shadow
                            <?= $index === 0 ? 'bg-yellow-400 text-yellow-900' : ($index === 1 ? 'bg-neutral-300 text-neutral-700' : 'bg-orange-400 text-orange-900') ?>">
                            <?= $index + 1 ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($isAnimated): ?>
                        <span class="absolute <?= $index < 3 ? 'top-2 left-11' : 'top-2 left-2' ?> px-1.5 py-0.5 bg-black/70 text-white text-[10px] font-bold rounded">GIF</span>
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </div>
                    <div class="mt-2">
                        <h3 class="text-sm font-medium text-neutral-800 dark:text-neutral-200 truncate"><?= e($image['title']) ?></h3>
                        <div class="flex items-center gap-3 text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
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
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-8 flex justify-center">
                <div class="flex items-center gap-2">
                    <?php if ($page > 1): ?>
                    <a href="<?= $view->url('/trending') ?>?period=<?= $period ?>&page=<?= $page - 1 ?>" class="px-4 py-2 bg-white dark:bg-neutral-800 border dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white">Previous</a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                    <a href="<?= $view->url('/trending') ?>?period=<?= $period ?>&page=<?= $i ?>"
                       class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-primary-600 text-white' : 'bg-white dark:bg-neutral-800 border dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <a href="<?= $view->url('/trending') ?>?period=<?= $period ?>&page=<?= $page + 1 ?>" class="px-4 py-2 bg-white dark:bg-neutral-800 border dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white">Next</a>
                    <?php endif; ?>
                </div>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="lg:w-72 flex-shrink-0">
            <?php if (!empty($trendingTags)): ?>
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm p-6">
                <h3 class="font-semibold text-neutral-900 dark:text-white mb-4">Trending Tags</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($trendingTags as $tag): ?>
                    <a href="<?= $view->url('/tag/' . $tag['slug']) ?>" class="px-3 py-1 bg-gradient-to-r from-orange-100 to-red-100 dark:from-orange-900/50 dark:to-red-900/50 hover:from-orange-200 hover:to-red-200 dark:hover:from-orange-800/50 dark:hover:to-red-800/50 text-orange-700 dark:text-orange-300 rounded-full text-sm">
                        #<?= e($tag['name']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</div>
