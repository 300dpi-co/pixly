<?php
/**
 * Pexels-Style Light Search Page
 * Clean, bright design matching pexels.com
 */
?>

<!-- Search Header -->
<div class="bg-white border-b border-neutral-200">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-semibold text-neutral-900 mb-4">Search Photos</h1>

        <form action="<?= $view->url('/search') ?>" method="GET" class="max-w-2xl">
            <div class="flex gap-3">
                <div class="flex-1 relative">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="q" value="<?= e($query) ?>" placeholder="Search for photos..."
                           class="w-full pl-12 pr-4 py-3 bg-neutral-100 border border-transparent rounded-lg text-neutral-900 placeholder-neutral-500 focus:bg-white focus:border-neutral-300 focus:ring-2 focus:ring-teal-100 focus:outline-none">
                </div>
                <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    Search
                </button>
            </div>
        </form>

        <?php if ($query): ?>
        <p class="text-neutral-500 mt-4"><?= number_format($total) ?> results for "<?= e($query) ?>"</p>
        <?php endif; ?>
    </div>
</div>

<div class="bg-white min-h-screen">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Main Content -->
            <div class="flex-1 min-w-0">
                <?php if (!$query): ?>
                <div class="text-center py-16">
                    <div class="w-16 h-16 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-neutral-900">Start searching</h3>
                    <p class="mt-2 text-neutral-500">Enter keywords to find photos.</p>
                </div>
                <?php elseif (empty($images)): ?>
                <div class="text-center py-16">
                    <div class="w-16 h-16 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-neutral-900">No results found</h3>
                    <p class="mt-2 text-neutral-500">Try different keywords or browse our gallery.</p>
                    <a href="<?= $view->url('/gallery') ?>" class="mt-4 inline-block text-teal-600 hover:text-teal-700 font-medium">Browse Gallery</a>
                </div>
                <?php else: ?>
                <!-- Masonry Grid -->
                <div class="columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4">
                    <?php foreach ($images as $image): ?>
                    <?php $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path']; ?>
                    <div class="break-inside-avoid group">
                        <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="block relative rounded-lg overflow-hidden bg-neutral-100">
                            <img src="<?= e('/uploads/' . $thumbSrc) ?>"
                                 alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                                 class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-300"
                                 loading="lazy">

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <div class="absolute bottom-3 left-3 right-3">
                                    <span class="text-white text-sm font-medium truncate block"><?= e($image['title']) ?></span>
                                </div>
                                <div class="absolute top-3 right-3">
                                    <span class="flex items-center gap-1 px-2 py-1 bg-white/90 rounded text-neutral-700 text-xs">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                        <?= number_format($image['favorite_count']) ?>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-12 flex justify-center">
                    <div class="inline-flex items-center gap-1 bg-neutral-100 rounded-lg p-1">
                        <?php if ($page > 1): ?>
                        <a href="<?= $view->url('/search') ?>?q=<?= urlencode($query) ?>&page=<?= $page - 1 ?>"
                           class="px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-white rounded-md transition-colors">
                            Previous
                        </a>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                        <a href="<?= $view->url('/search') ?>?q=<?= urlencode($query) ?>&page=<?= $i ?>"
                           class="w-10 h-10 flex items-center justify-center text-sm font-medium rounded-md transition-colors <?= $i === $page ? 'bg-neutral-900 text-white' : 'text-neutral-600 hover:bg-white' ?>">
                            <?= $i ?>
                        </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <a href="<?= $view->url('/search') ?>?q=<?= urlencode($query) ?>&page=<?= $page + 1 ?>"
                           class="px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-white rounded-md transition-colors">
                            Next
                        </a>
                        <?php endif; ?>
                    </div>
                </nav>
                <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="lg:w-72 flex-shrink-0">
                <?php if (!empty($popularSearches)): ?>
                <div class="bg-neutral-50 rounded-xl p-6">
                    <h3 class="font-semibold text-neutral-900 mb-4">Popular Searches</h3>
                    <ul class="space-y-2">
                        <?php foreach ($popularSearches as $search): ?>
                        <li>
                            <a href="<?= $view->url('/search') ?>?q=<?= urlencode($search['query']) ?>"
                               class="flex justify-between text-neutral-600 hover:text-teal-600 transition-colors">
                                <span><?= e($search['query']) ?></span>
                                <span class="text-neutral-400"><?= number_format($search['search_count']) ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</div>
