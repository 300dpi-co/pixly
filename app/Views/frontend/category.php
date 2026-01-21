<!-- Category Header -->
<div class="bg-white dark:bg-neutral-900 border-b dark:border-neutral-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="text-sm text-neutral-500 dark:text-neutral-400 mb-2">
            <a href="<?= $view->url('/gallery') ?>" class="hover:text-primary-600 dark:hover:text-primary-400">Gallery</a>
            <span class="mx-2">/</span>
            <span class="text-neutral-900 dark:text-white"><?= e($category['name']) ?></span>
        </nav>
        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white"><?= e($category['name']) ?></h1>
        <?php if ($category['description']): ?>
        <p class="text-neutral-600 dark:text-neutral-400 mt-2"><?= e($category['description']) ?></p>
        <?php endif; ?>
        <p class="text-neutral-500 dark:text-neutral-400 mt-2"><?= number_format($total) ?> images</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Main Content -->
        <div class="flex-1">
            <?php if (empty($images)): ?>
            <div class="text-center py-16">
                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-neutral-900 dark:text-white">No images in this category</h3>
                <p class="mt-2 text-neutral-600 dark:text-neutral-400">Check back soon for new content.</p>
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
                    <a href="<?= $view->url('/category/' . $category['slug'] . '/page/' . ($page - 1)) ?>" class="px-4 py-2 bg-white dark:bg-neutral-800 border dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white">Previous</a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                    <a href="<?= $view->url('/category/' . $category['slug'] . '/page/' . $i) ?>"
                       class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-primary-600 text-white' : 'bg-white dark:bg-neutral-800 border dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <a href="<?= $view->url('/category/' . $category['slug'] . '/page/' . ($page + 1)) ?>" class="px-4 py-2 bg-white dark:bg-neutral-800 border dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white">Next</a>
                    <?php endif; ?>
                </div>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="lg:w-72 flex-shrink-0">
            <!-- All Categories -->
            <?php if (!empty($categories)): ?>
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm p-6">
                <h3 class="font-semibold text-neutral-900 dark:text-white mb-4">All Categories</h3>
                <ul class="space-y-2">
                    <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="<?= $view->url('/category/' . $cat['slug']) ?>"
                           class="flex justify-between <?= $cat['id'] == $category['id'] ? 'text-primary-600 dark:text-primary-400 font-medium' : 'text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400' ?>">
                            <span><?= e($cat['name']) ?></span>
                            <span class="text-neutral-400 dark:text-neutral-500"><?= number_format($cat['image_count'] ?? 0) ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</div>
