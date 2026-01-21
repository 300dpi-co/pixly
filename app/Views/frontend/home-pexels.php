<?php
/**
 * Pexels-Inspired Light Theme
 * Clean, bright stock photo gallery design like pexels.com
 */
$siteName = setting('site_name', config('app.name'));
?>

<!-- Hero Section with Search -->
<section class="relative bg-white overflow-hidden">
    <!-- Hero Background Image -->
    <?php if (!empty($featuredImages[0])): ?>
    <div class="absolute inset-0">
        <img src="<?= e('/uploads/' . ($featuredImages[0]['storage_path'] ?? $featuredImages[0]['thumbnail_path'])) ?>"
             class="w-full h-full object-cover" alt="">
        <div class="absolute inset-0 bg-black/40"></div>
    </div>
    <?php else: ?>
    <div class="absolute inset-0 bg-gradient-to-br from-teal-600 to-emerald-700"></div>
    <?php endif; ?>

    <div class="relative max-w-5xl mx-auto px-4 py-20 md:py-28 lg:py-36 text-center">
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-semibold text-white mb-6">
            The best free stock photos, royalty free images & videos shared by creators.
        </h1>

        <!-- Search Bar -->
        <form action="<?= $view->url('/search') ?>" method="GET" class="max-w-2xl mx-auto">
            <div class="relative">
                <div class="flex items-center bg-white rounded-lg shadow-xl overflow-hidden">
                    <div class="pl-5 text-neutral-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="q" placeholder="Search for free photos"
                           class="flex-1 px-4 py-4 text-neutral-800 placeholder-neutral-400 focus:outline-none bg-transparent text-base">
                    <button type="submit" class="px-6 py-4 bg-teal-500 hover:bg-teal-600 text-white font-medium transition-colors">
                        Search
                    </button>
                </div>
            </div>
        </form>

        <!-- Trending Searches -->
        <div class="mt-6 flex flex-wrap justify-center gap-2 text-sm">
            <span class="text-white/70">Trending:</span>
            <?php
            $trendingSearches = ['nature', 'wallpaper', 'background', 'happy', 'love'];
            foreach ($trendingSearches as $search):
            ?>
            <a href="<?= $view->url('/search?q=' . urlencode($search)) ?>"
               class="text-white hover:underline">
                <?= e($search) ?><?= $search !== end($trendingSearches) ? ',' : '' ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Main Content Area -->
<div class="bg-white min-h-screen">
    <!-- Category/Filter Bar -->
    <div class="border-b border-neutral-200 sticky top-0 z-40 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center gap-1 py-2 overflow-x-auto scrollbar-hide">
                <a href="<?= $view->url('/') ?>"
                   class="flex-shrink-0 px-4 py-2 text-sm font-medium text-neutral-900 hover:bg-neutral-100 rounded-md transition-colors">
                    Home
                </a>
                <a href="<?= $view->url('/gallery') ?>"
                   class="flex-shrink-0 px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-neutral-100 rounded-md transition-colors">
                    Discover
                </a>
                <?php if (!empty($categories)): ?>
                <?php foreach (array_slice($categories, 0, 6) as $category): ?>
                <a href="<?= $view->url('/category/' . $category['slug']) ?>"
                   class="flex-shrink-0 px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-neutral-100 rounded-md transition-colors">
                    <?= e($category['name']) ?>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Free Stock Photos Section -->
    <section class="py-8">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-xl font-semibold text-neutral-900 mb-6">Free Stock Photos</h2>

            <?php if (!empty($recentImages)): ?>
            <!-- Masonry Grid -->
            <div class="columns-2 sm:columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4" id="photo-grid">
                <?php foreach ($recentImages as $image): ?>
                <div class="break-inside-avoid">
                    <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="group block relative rounded-lg overflow-hidden bg-neutral-100">
                        <img src="<?= e('/uploads/' . ($image['thumbnail_webp_path'] ?? $image['thumbnail_path'] ?? $image['storage_path'])) ?>"
                             alt="<?= e($image['alt_text'] ?? $image['title']) ?>"
                             class="w-full h-auto object-cover"
                             loading="lazy">

                        <!-- Hover Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <!-- Bottom Actions -->
                            <div class="absolute bottom-0 left-0 right-0 p-4 flex items-end justify-between">
                                <span class="text-white text-sm font-medium truncate max-w-[60%]"><?= e($image['title']) ?></span>
                                <div class="flex items-center gap-2">
                                    <button onclick="event.preventDefault();" class="w-8 h-8 flex items-center justify-center bg-white/90 hover:bg-white rounded-md transition-colors" title="Collect">
                                        <svg class="w-4 h-4 text-neutral-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                        </svg>
                                    </button>
                                    <button onclick="event.preventDefault();" class="w-8 h-8 flex items-center justify-center bg-white/90 hover:bg-white rounded-md transition-colors" title="Like">
                                        <svg class="w-4 h-4 text-neutral-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Download Button -->
                            <div class="absolute top-3 right-3">
                                <span class="px-3 py-1.5 bg-teal-500 hover:bg-teal-600 text-white text-xs font-medium rounded transition-colors">
                                    Download
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Load More -->
            <div class="mt-10 text-center">
                <a href="<?= $view->url('/gallery') ?>"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-neutral-900 hover:bg-neutral-800 text-white font-medium rounded-lg transition-colors">
                    See more
                </a>
            </div>
            <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-20">
                <div class="w-20 h-20 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-neutral-900 mb-2">No photos yet</h3>
                <p class="text-neutral-500 mb-6">Be the first to upload amazing photos!</p>
                <a href="<?= $view->url('/upload') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-teal-500 hover:bg-teal-600 text-white font-medium rounded-lg transition-colors">
                    Upload Photos
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Trending Tags -->
    <?php if (!empty($trendingTags)): ?>
    <section class="py-8 border-t border-neutral-100">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-xl font-semibold text-neutral-900 mb-6">Trending Searches</h2>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($trendingTags as $tag): ?>
                <a href="<?= $view->url('/tag/' . $tag['slug']) ?>"
                   class="px-4 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-full text-sm font-medium transition-colors">
                    <?= e($tag['name']) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Top Contributors -->
    <?php if (!empty($topContributors)): ?>
    <section class="py-8 border-t border-neutral-100">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-xl font-semibold text-neutral-900 mb-6">Top Contributors</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
                <?php foreach ($topContributors as $index => $contributor): ?>
                <a href="<?= $view->url('/user/' . $contributor['username']) ?>"
                   class="group text-center">
                    <div class="relative mx-auto w-16 h-16 mb-3">
                        <?php if (!empty($contributor['avatar_path'])): ?>
                        <img src="<?= $view->url('/uploads/' . $contributor['avatar_path']) ?>"
                             alt="<?= e($contributor['display_name'] ?: $contributor['username']) ?>"
                             class="w-full h-full rounded-full object-cover group-hover:ring-2 group-hover:ring-teal-500 transition-all">
                        <?php else: ?>
                        <div class="w-full h-full rounded-full bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center text-white text-xl font-semibold group-hover:ring-2 group-hover:ring-teal-500 transition-all">
                            <?= strtoupper(substr($contributor['username'], 0, 1)) ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($index < 3): ?>
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shadow <?php
                            echo match($index) {
                                0 => 'bg-yellow-400 text-yellow-900',
                                1 => 'bg-gray-300 text-gray-700',
                                2 => 'bg-orange-400 text-white',
                                default => ''
                            };
                        ?>"><?= $index + 1 ?></div>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-sm font-medium text-neutral-900 truncate group-hover:text-teal-600 transition-colors">
                        <?= e($contributor['display_name'] ?: $contributor['username']) ?>
                    </h3>
                    <p class="text-xs text-neutral-500"><?= number_format($contributor['total_score']) ?> points</p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <section class="py-16 bg-neutral-50 border-t border-neutral-100">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <h2 class="text-2xl font-semibold text-neutral-900 mb-4">
                The best free stock photos from talented creators.
            </h2>
            <p class="text-neutral-600 mb-8">
                Share your own photos and get exposure to millions of users.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="<?= $view->url('/upload') ?>"
                   class="px-6 py-3 bg-teal-500 hover:bg-teal-600 text-white font-medium rounded-lg transition-colors">
                    Upload your photos
                </a>
                <a href="<?= $view->url('/register') ?>"
                   class="px-6 py-3 bg-white hover:bg-neutral-100 text-neutral-700 font-medium rounded-lg border border-neutral-300 transition-colors">
                    Join for free
                </a>
            </div>
        </div>
    </section>

    <!-- Stats Footer -->
    <section class="py-12 border-t border-neutral-100">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-3xl font-bold text-neutral-900"><?= number_format($stats['total_images'] ?? 0) ?>+</div>
                    <div class="text-neutral-500 text-sm mt-1">Free Photos</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-neutral-900"><?= number_format($stats['total_users'] ?? 0) ?>+</div>
                    <div class="text-neutral-500 text-sm mt-1">Creators</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-neutral-900"><?= number_format($stats['total_downloads'] ?? 0) ?>+</div>
                    <div class="text-neutral-500 text-sm mt-1">Downloads</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-neutral-900">100%</div>
                    <div class="text-neutral-500 text-sm mt-1">Free Forever</div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
