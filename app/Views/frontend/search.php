<!-- Search Header -->
<div class="bg-white dark:bg-neutral-900 border-b dark:border-neutral-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-4">Search Images</h1>

        <form action="<?= $view->url('/search') ?>" method="GET" class="max-w-2xl">
            <div class="flex gap-4">
                <input type="text" name="q" value="<?= e($query) ?>" placeholder="Search for images, tags, categories..."
                       class="flex-1 px-4 py-3 border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white dark:placeholder-neutral-400 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                    Search
                </button>
            </div>
        </form>

        <?php if ($query): ?>
        <p class="text-neutral-500 dark:text-neutral-400 mt-4"><?= number_format($total) ?> results for "<?= e($query) ?>"</p>
        <?php endif; ?>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Main Content -->
        <div class="flex-1">
            <?php if (!$query): ?>
            <div class="text-center py-16">
                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-neutral-900 dark:text-white">Start searching</h3>
                <p class="mt-2 text-neutral-600 dark:text-neutral-400">Enter keywords to find images.</p>
            </div>
            <?php elseif (empty($images)): ?>
            <div class="text-center py-16">
                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-neutral-900 dark:text-white">No results found</h3>
                <p class="mt-2 text-neutral-600 dark:text-neutral-400">Try different keywords or browse our categories.</p>
                <a href="<?= $view->url('/gallery') ?>" class="mt-4 inline-block text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">Browse Gallery</a>
            </div>
            <?php else: ?>
            <!-- Image Grid -->
            <div class="gallery-grid grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php foreach ($images as $image): ?>
                <?php $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path']; ?>
                <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="group block">
                    <div class="aspect-square bg-neutral-200 dark:bg-neutral-700 rounded-lg overflow-hidden relative">
                        <img data-src="<?= e('/uploads/' . $thumbSrc) ?>"
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
                    <a href="<?= $view->url('/search') ?>?q=<?= urlencode($query) ?>&page=<?= $page - 1 ?>" class="px-4 py-2 bg-white dark:bg-neutral-800 border dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white">Previous</a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                    <a href="<?= $view->url('/search') ?>?q=<?= urlencode($query) ?>&page=<?= $i ?>"
                       class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-primary-600 text-white' : 'bg-white dark:bg-neutral-800 border dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <a href="<?= $view->url('/search') ?>?q=<?= urlencode($query) ?>&page=<?= $page + 1 ?>" class="px-4 py-2 bg-white dark:bg-neutral-800 border dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white">Next</a>
                    <?php endif; ?>
                </div>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="lg:w-72 flex-shrink-0">
            <?php if (!empty($popularSearches)): ?>
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm p-6">
                <h3 class="font-semibold text-neutral-900 dark:text-white mb-4">Popular Searches</h3>
                <ul class="space-y-2">
                    <?php foreach ($popularSearches as $search): ?>
                    <li>
                        <a href="<?= $view->url('/search') ?>?q=<?= urlencode($search['query']) ?>" class="flex justify-between text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400">
                            <span><?= e($search['query']) ?></span>
                            <span class="text-neutral-400 dark:text-neutral-500"><?= number_format($search['search_count']) ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</div>
