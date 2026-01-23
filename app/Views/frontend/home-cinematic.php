<?php
/**
 * Home Page - Dark Cinematic Theme
 * Clean layout matching trending page style
 */
?>

<!-- Hero Section -->
<section class="bg-gradient-to-b from-neutral-900 to-black border-b border-neutral-800">
    <div class="max-w-7xl mx-auto px-4 py-10 md:py-14">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">
                    Welcome to <span class="text-red-500"><?= e(setting('site_name', 'Gallery')) ?></span>
                </h1>
                <p class="text-neutral-400">
                    Discover trending content and latest uploads
                </p>
            </div>

            <!-- Quick Links -->
            <div class="flex items-center gap-2">
                <a href="<?= $view->url('/trending') ?>" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"/></svg>
                    Trending
                </a>
                <a href="<?= $view->url('/gallery') ?>" class="px-4 py-2 bg-neutral-800 hover:bg-neutral-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Browse All
                </a>
            </div>
        </div>
    </div>
</section>

<div class="bg-black min-h-screen">
    <!-- Featured Trending (Top 3) -->
    <?php if (!empty($trendingImages) && count($trendingImages) >= 3): ?>
    <section class="border-b border-neutral-800">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"/></svg>
                    Trending Now
                </h2>
                <a href="<?= $view->url('/trending') ?>" class="text-sm text-red-400 hover:text-red-300 transition-colors">View all</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php for ($i = 0; $i < 3; $i++):
                    if (!isset($trendingImages[$i])) continue;
                    $img = $trendingImages[$i];
                    $thumb = $img['thumbnail_webp_path'] ?: $img['thumbnail_path'];
                    $badgeColors = [
                        0 => 'bg-amber-500 text-black',
                        1 => 'bg-neutral-400 text-black',
                        2 => 'bg-orange-600 text-white'
                    ];
                ?>
                <a href="<?= $view->url('/image/' . $img['slug']) ?>" class="group block">
                    <div class="relative aspect-[4/5] bg-neutral-900 rounded-xl overflow-hidden">
                        <img data-src="<?= e(uploads_url($thumb)) ?>"
                             src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3C/svg%3E"
                             alt="<?= e($img['alt_text'] ?: $img['title']) ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             loading="lazy">

                        <!-- Gradient Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                        <!-- Rank Badge -->
                        <div class="absolute top-3 left-3 w-8 h-8 <?= $badgeColors[$i] ?> rounded-full flex items-center justify-center text-sm font-bold">
                            <?= $i + 1 ?>
                        </div>

                        <!-- Content -->
                        <div class="absolute bottom-0 left-0 right-0 p-4">
                            <h3 class="text-white font-medium text-sm mb-1 truncate"><?= e($img['title']) ?></h3>
                            <div class="flex items-center gap-3 text-xs text-neutral-300">
                                <span><?= number_format($img['view_count']) ?> views</span>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endfor; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Main Content -->
    <section class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Content Grid -->
            <div class="flex-1 min-w-0">
                <!-- Latest Images -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-white">Latest Uploads</h2>
                    <a href="<?= $view->url('/gallery') ?>" class="text-sm text-red-400 hover:text-red-300 transition-colors">View all</a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php if (!empty($recentImages)): ?>
                    <?php foreach ($recentImages as $image):
                        $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path'];
                        $isAnimated = !empty($image['is_animated']);
                    ?>
                    <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="group block">
                        <div class="relative aspect-[3/4] bg-neutral-900 rounded-lg overflow-hidden border border-neutral-800 group-hover:border-red-600/50 transition-colors">
                            <img data-src="<?= e(uploads_url($thumbSrc)) ?>"
                                 src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3C/svg%3E"
                                 alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                 loading="lazy">

                            <?php if ($isAnimated): ?>
                            <span class="absolute top-2 left-2 px-2 py-0.5 bg-red-600 text-white text-xs font-bold rounded">GIF</span>
                            <?php endif; ?>

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                                <div class="absolute bottom-0 left-0 right-0 p-3">
                                    <p class="text-white text-xs truncate"><?= e($image['title']) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-white text-sm font-medium truncate group-hover:text-red-400 transition-colors"><?= e($image['title']) ?></h3>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="col-span-full text-center py-12">
                        <p class="text-neutral-400">No images yet. Be the first to upload!</p>
                        <a href="<?= $view->url('/upload') ?>" class="inline-block mt-4 px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                            Upload Now
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Browse More -->
                <?php if (!empty($recentImages)): ?>
                <div class="mt-8 text-center">
                    <a href="<?= $view->url('/gallery') ?>" class="inline-block px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                        Browse All Images
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="lg:w-64 flex-shrink-0 space-y-6">
                <!-- Search -->
                <form action="<?= $view->url('/search') ?>" method="GET">
                    <div class="relative">
                        <input type="text" name="q" placeholder="Search..."
                               class="w-full px-4 py-3 text-sm bg-neutral-900 border border-neutral-800 text-white placeholder-neutral-500 rounded-lg focus:border-red-600/50 focus:ring-0 transition-colors">
                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-500 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Categories -->
                <?php if (!empty($categories)): ?>
                <div class="bg-neutral-900 rounded-xl p-5 border border-neutral-800">
                    <h3 class="font-semibold text-white mb-4">Categories</h3>
                    <div class="space-y-1">
                        <?php foreach ($categories as $cat): ?>
                        <a href="<?= $view->url('/category/' . $cat['slug']) ?>" class="flex items-center justify-between px-3 py-2 text-sm text-neutral-300 hover:text-white hover:bg-neutral-800 rounded-lg transition-all">
                            <span><?= e($cat['name']) ?></span>
                            <span class="text-xs text-neutral-500"><?= number_format($cat['image_count'] ?? 0) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Popular Tags -->
                <?php if (!empty($trendingTags)): ?>
                <div class="bg-neutral-900 rounded-xl p-5 border border-neutral-800">
                    <h3 class="font-semibold text-white mb-4">Popular Tags</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach (array_slice($trendingTags, 0, 12) as $tag): ?>
                        <a href="<?= $view->url('/tag/' . $tag['slug']) ?>" class="px-3 py-1.5 bg-neutral-800 hover:bg-red-600/20 border border-neutral-700 hover:border-red-500/30 text-neutral-300 hover:text-red-400 rounded-lg text-sm transition-all">
                            <?= e($tag['name']) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Upload CTA -->
                <div class="bg-neutral-900 rounded-xl p-5 border border-neutral-800 text-center">
                    <div class="w-14 h-14 bg-red-600/20 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-white mb-2">Share Your Work</h3>
                    <p class="text-neutral-400 text-sm mb-4">Upload your best content</p>
                    <a href="<?= $view->url('/upload') ?>"
                       class="block w-full py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                        Upload Now
                    </a>
                </div>
            </aside>
        </div>
    </section>
</div>
