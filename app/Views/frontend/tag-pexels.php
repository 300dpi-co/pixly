<?php
/**
 * Pexels-Style Light Tag Page
 * Clean, bright design matching pexels.com
 */
?>

<!-- Tag Header -->
<div class="bg-white border-b border-neutral-200">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <nav class="text-sm text-neutral-500 mb-2">
            <a href="<?= $view->url('/gallery') ?>" class="hover:text-teal-600 transition-colors">Gallery</a>
            <span class="mx-2">/</span>
            <span class="text-neutral-900">Tags</span>
        </nav>
        <h1 class="text-2xl font-semibold text-neutral-900">#<?= e($tag['name']) ?></h1>
        <p class="text-neutral-500 mt-2"><?= number_format($total) ?> photos tagged with "<?= e($tag['name']) ?>"</p>
    </div>
</div>

<div class="bg-white min-h-screen">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Main Content -->
            <div class="flex-1 min-w-0">
                <?php if (empty($images)): ?>
                <div class="text-center py-16">
                    <div class="w-16 h-16 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-neutral-900">No photos with this tag</h3>
                    <p class="mt-2 text-neutral-500">Try browsing other tags.</p>
                </div>
                <?php else: ?>
                <!-- Masonry Grid -->
                <div class="columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4">
                    <?php foreach ($images as $image): ?>
                    <?php $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path']; ?>
                    <div class="break-inside-avoid group">
                        <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="block relative rounded-lg overflow-hidden bg-neutral-100">
                            <img src="<?= e(uploads_url($thumbSrc)) ?>"
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
                                <div class="absolute bottom-3 right-3">
                                    <span class="px-3 py-1.5 bg-teal-500 text-white text-xs font-medium rounded">
                                        Download
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
                        <a href="<?= $view->url('/tag/' . $tag['slug'] . '/page/' . ($page - 1)) ?>"
                           class="px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-white rounded-md transition-colors">
                            Previous
                        </a>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                        <a href="<?= $view->url('/tag/' . $tag['slug'] . '/page/' . $i) ?>"
                           class="w-10 h-10 flex items-center justify-center text-sm font-medium rounded-md transition-colors <?= $i === $page ? 'bg-neutral-900 text-white' : 'text-neutral-600 hover:bg-white' ?>">
                            <?= $i ?>
                        </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <a href="<?= $view->url('/tag/' . $tag['slug'] . '/page/' . ($page + 1)) ?>"
                           class="px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-white rounded-md transition-colors">
                            Next
                        </a>
                        <?php endif; ?>
                    </div>
                </nav>
                <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar - Related Tags -->
            <aside class="lg:w-72 flex-shrink-0">
                <?php if (!empty($relatedTags)): ?>
                <div class="bg-neutral-50 rounded-xl p-6">
                    <h3 class="font-semibold text-neutral-900 mb-4">Related Tags</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($relatedTags as $relTag): ?>
                        <a href="<?= $view->url('/tag/' . $relTag['slug']) ?>"
                           class="px-3 py-1.5 bg-white hover:bg-neutral-100 border border-neutral-200 text-neutral-700 rounded-full text-sm transition-colors">
                            #<?= e($relTag['name']) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</div>
