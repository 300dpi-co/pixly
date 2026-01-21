<!-- Gallery Header -->
<div class="bg-white dark:bg-neutral-900 border-b dark:border-neutral-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Image Gallery</h1>
        <p class="text-neutral-500 dark:text-neutral-400 text-sm mt-1"><?= number_format($total) ?> images</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Main Content -->
        <div class="flex-1 min-w-0">
            <?php if (empty($images)): ?>
            <div class="text-center py-16">
                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-neutral-900 dark:text-white">No images yet</h3>
                <p class="mt-2 text-neutral-600 dark:text-neutral-400">Check back soon for new content.</p>
            </div>
            <?php else: ?>
            <!-- Image Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                <?php foreach ($images as $image): ?>
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
                        <?php if ($isAnimated): ?>
                        <span class="absolute top-2 left-2 px-1.5 py-0.5 bg-black/70 text-white text-[10px] font-bold rounded">GIF</span>
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
                    <a href="<?= $view->url('/gallery/page/' . ($page - 1)) ?>" class="px-4 py-2 bg-white dark:bg-neutral-800 border dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white">Previous</a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                    <a href="<?= $view->url('/gallery/page/' . $i) ?>"
                       class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-primary-600 text-white' : 'bg-white dark:bg-neutral-800 border dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <a href="<?= $view->url('/gallery/page/' . ($page + 1)) ?>" class="px-4 py-2 bg-white dark:bg-neutral-800 border dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white">Next</a>
                    <?php endif; ?>
                </div>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="lg:w-72 flex-shrink-0">
            <!-- Categories -->
            <?php if (!empty($categories)): ?>
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm p-6 mb-6">
                <h3 class="font-semibold text-neutral-900 dark:text-white mb-4">Categories</h3>
                <ul class="space-y-2">
                    <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="<?= $view->url('/category/' . $cat['slug']) ?>" class="flex justify-between text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400">
                            <span><?= e($cat['name']) ?></span>
                            <span class="text-neutral-400 dark:text-neutral-500"><?= number_format($cat['image_count'] ?? 0) ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Popular Tags -->
            <?php if (!empty($popularTags)): ?>
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm p-6">
                <h3 class="font-semibold text-neutral-900 dark:text-white mb-4">Popular Tags</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($popularTags as $tag): ?>
                    <a href="<?= $view->url('/tag/' . $tag['slug']) ?>" class="px-3 py-1 bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-200 rounded-full text-sm">
                        #<?= e($tag['name']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</div>
