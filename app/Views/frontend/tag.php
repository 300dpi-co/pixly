<!-- Tag Header -->
<div class="bg-white dark:bg-neutral-900 border-b dark:border-neutral-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="text-sm text-neutral-500 dark:text-neutral-400 mb-2">
            <a href="<?= $view->url('/gallery') ?>" class="hover:text-primary-600 dark:hover:text-primary-400">Gallery</a>
            <span class="mx-2">/</span>
            <span class="text-neutral-900 dark:text-white">Tags</span>
        </nav>
        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">#<?= e($tag['name']) ?></h1>
        <p class="text-neutral-500 dark:text-neutral-400 mt-2"><?= number_format($total) ?> images tagged with "<?= e($tag['name']) ?>"</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Main Content -->
        <div class="flex-1">
            <?php if (empty($images)): ?>
            <div class="text-center py-16">
                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-neutral-900 dark:text-white">No images with this tag</h3>
                <p class="mt-2 text-neutral-600 dark:text-neutral-400">Try browsing other tags.</p>
            </div>
            <?php else: ?>
            <!-- Image Grid -->
            <div class="gallery-grid grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php foreach ($images as $image): ?>
                <?php $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path']; ?>
                <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="group block">
                    <div class="aspect-square bg-neutral-200 dark:bg-neutral-700 rounded-lg overflow-hidden relative">
                        <img data-src="<?= e(uploads_url($thumbSrc)) ?>"
                             src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 300'%3E%3Crect fill='%23e5e7eb' width='300' height='300'/%3E%3C/svg%3E"
                             alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors"></div>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white truncate"><?= e($image['title']) ?></h3>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-8 flex justify-center">
                <div class="flex items-center gap-2">
                    <?php if ($page > 1): ?>
                    <a href="<?= $view->url('/tag/' . $tag['slug'] . '/page/' . ($page - 1)) ?>" class="px-4 py-2 bg-white dark:bg-neutral-800 border dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white">Previous</a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                    <a href="<?= $view->url('/tag/' . $tag['slug'] . '/page/' . $i) ?>"
                       class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-primary-600 text-white' : 'bg-white dark:bg-neutral-800 border dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <a href="<?= $view->url('/tag/' . $tag['slug'] . '/page/' . ($page + 1)) ?>" class="px-4 py-2 bg-white dark:bg-neutral-800 border dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white">Next</a>
                    <?php endif; ?>
                </div>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar - Related Tags -->
        <aside class="lg:w-72 flex-shrink-0">
            <?php if (!empty($relatedTags)): ?>
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm p-6">
                <h3 class="font-semibold text-neutral-900 dark:text-white mb-4">Related Tags</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($relatedTags as $relTag): ?>
                    <a href="<?= $view->url('/tag/' . $relTag['slug']) ?>" class="px-3 py-1 bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-200 rounded-full text-sm">
                        #<?= e($relTag['name']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</div>
