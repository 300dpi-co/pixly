<?php
/**
 * Pexels-Style Light Gallery Page
 * Clean, bright design matching pexels.com
 */
?>

<!-- Sticky Search Bar -->
<div class="sticky top-0 z-40 bg-white border-b border-neutral-200">
    <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <!-- Search -->
            <form action="<?= $view->url('/search') ?>" method="GET" class="flex-1 max-w-2xl">
                <div class="relative">
                    <input type="text" name="q" placeholder="Search photos..."
                           class="w-full px-5 py-3 pl-12 bg-neutral-100 rounded-lg
                                  text-neutral-900 placeholder-neutral-500
                                  focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white border border-transparent focus:border-neutral-200">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </form>

            <!-- View Toggle -->
            <div class="hidden md:flex items-center gap-2 border border-neutral-200 rounded-lg p-1">
                <button class="p-2 rounded bg-neutral-100" title="Grid View">
                    <svg class="w-5 h-5 text-neutral-700" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </button>
                <button class="p-2 rounded hover:bg-neutral-100" title="List View">
                    <svg class="w-5 h-5 text-neutral-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Category Filters -->
<?php if (!empty($categories)): ?>
<div class="bg-white border-b border-neutral-200">
    <div class="max-w-7xl mx-auto px-4 py-3">
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
            <a href="<?= $view->url('/gallery') ?>" class="flex-shrink-0 px-4 py-2 text-sm font-medium bg-neutral-900 text-white rounded-full">
                All Photos
            </a>
            <?php foreach ($categories as $cat): ?>
            <a href="<?= $view->url('/category/' . $cat['slug']) ?>"
               class="flex-shrink-0 px-4 py-2 text-sm font-medium bg-neutral-100 text-neutral-600 hover:bg-neutral-200 rounded-full transition-colors">
                <?= e($cat['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Main Content -->
<div class="bg-white min-h-screen">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-semibold text-neutral-900">Free Stock Photos</h1>
                <p class="text-neutral-500 mt-1"><?= number_format($total) ?> high-quality photos available for free</p>
            </div>

            <!-- Sort Options -->
            <div class="hidden md:flex items-center gap-4">
                <span class="text-sm text-neutral-500">Sort by:</span>
                <select class="px-4 py-2 bg-white border border-neutral-200 rounded-lg text-sm text-neutral-700 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option>Newest</option>
                    <option>Popular</option>
                    <option>Trending</option>
                </select>
            </div>
        </div>

        <?php if (empty($images)): ?>
        <!-- Empty State -->
        <div class="text-center py-20">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-neutral-100 mb-6">
                <svg class="w-10 h-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-neutral-900 mb-2">No photos yet</h3>
            <p class="text-neutral-500 mb-6">Check back soon for amazing free stock photos.</p>
            <a href="<?= $view->url('/upload') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-teal-500 hover:bg-teal-600 text-white font-medium rounded-lg transition-colors">
                Upload Photos
            </a>
        </div>
        <?php else: ?>

        <!-- Masonry Grid -->
        <div class="columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4">
            <?php foreach ($images as $image): ?>
            <?php
            $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path'];
            $isAnimated = !empty($image['is_animated']);
            ?>
            <div class="break-inside-avoid group">
                <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="block relative rounded-lg overflow-hidden bg-neutral-100">
                    <img src="<?= e('/uploads/' . $thumbSrc) ?>"
                         alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                         class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-300"
                         loading="lazy">

                    <?php if ($isAnimated): ?>
                    <span class="absolute top-3 left-3 px-2 py-0.5 bg-black/70 text-white text-xs font-bold rounded">GIF</span>
                    <?php endif; ?>

                    <!-- Hover Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <!-- Quick Actions -->
                        <div class="absolute top-3 right-3 flex items-center gap-2">
                            <button onclick="event.preventDefault();" class="w-9 h-9 flex items-center justify-center bg-white/90 hover:bg-white rounded-md transition-colors" title="Save to collection">
                                <svg class="w-4 h-4 text-neutral-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                </svg>
                            </button>
                            <button onclick="event.preventDefault();" class="w-9 h-9 flex items-center justify-center bg-white/90 hover:bg-white rounded-md transition-colors" title="Like">
                                <svg class="w-4 h-4 text-neutral-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Info -->
                        <div class="absolute bottom-0 left-0 right-0 p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-white text-sm font-medium truncate max-w-[60%]"><?= e($image['title']) ?></span>
                                <div class="flex items-center gap-3 text-white/80 text-sm">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <?= number_format($image['view_count']) ?>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                        <?= number_format($image['favorite_count']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Download Button -->
                        <div class="absolute bottom-3 right-3">
                            <span class="px-3 py-1.5 bg-teal-500 hover:bg-teal-600 text-white text-xs font-medium rounded transition-colors">
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
                <a href="<?= $view->url('/gallery/page/' . ($page - 1)) ?>"
                   class="px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-white rounded-md transition-colors">
                    Previous
                </a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                for ($i = $start; $i <= $end; $i++):
                ?>
                <a href="<?= $view->url('/gallery/page/' . $i) ?>"
                   class="w-10 h-10 flex items-center justify-center text-sm font-medium rounded-md transition-colors <?= $i === $page ? 'bg-neutral-900 text-white' : 'text-neutral-600 hover:bg-white' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                <a href="<?= $view->url('/gallery/page/' . ($page + 1)) ?>"
                   class="px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-white rounded-md transition-colors">
                    Next
                </a>
                <?php endif; ?>
            </div>
        </nav>

        <p class="text-center text-sm text-neutral-500 mt-4">
            Page <?= $page ?> of <?= $totalPages ?> (<?= number_format($total) ?> photos)
        </p>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<!-- Popular Tags Section -->
<?php if (!empty($popularTags)): ?>
<section class="bg-neutral-50 py-12 border-t border-neutral-100">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-xl font-semibold text-neutral-900 mb-6">Popular Searches</h2>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($popularTags as $tag): ?>
            <a href="<?= $view->url('/tag/' . $tag['slug']) ?>"
               class="px-4 py-2 bg-white hover:bg-neutral-100 text-neutral-700 rounded-full text-sm font-medium border border-neutral-200 transition-colors">
                <?= e($tag['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
