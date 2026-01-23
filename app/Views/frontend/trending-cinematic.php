<?php
/**
 * Dark Cinematic Trending Page
 * Clean, well-contrasted design for adult themes
 */
?>

<!-- Hero Section -->
<section class="bg-gradient-to-b from-neutral-900 to-black border-b border-neutral-800">
    <div class="max-w-7xl mx-auto px-4 py-10 md:py-14">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-600/20 border border-red-500/30 rounded-full mb-4">
                    <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                    <span class="text-red-400 text-sm font-medium uppercase">Trending</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">
                    What's <span class="text-red-500">Hot</span> Right Now
                </h1>
                <p class="text-neutral-400">
                    <?= number_format($total) ?> photos trending
                </p>
            </div>

            <!-- Period Selector -->
            <div class="flex items-center gap-1 p-1 bg-neutral-800 rounded-lg">
                <?php
                $periods = ['today' => 'Today', 'week' => 'Week', 'month' => 'Month', 'all' => 'All'];
                foreach ($periods as $key => $label):
                    $isActive = $period === $key;
                ?>
                <a href="<?= $view->url('/trending') ?>?period=<?= $key ?>"
                   class="px-4 py-2 rounded-md text-sm font-medium transition-all <?= $isActive ? 'bg-red-600 text-white' : 'text-neutral-300 hover:text-white hover:bg-neutral-700' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<div class="bg-black min-h-screen">
    <?php if (empty($images)): ?>
    <!-- Empty State -->
    <div class="max-w-7xl mx-auto px-4 py-20 text-center">
        <div class="w-20 h-20 bg-neutral-800 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-white mb-2">No trending content yet</h2>
        <p class="text-neutral-400 mb-6">Be the first to upload and start trending!</p>
        <a href="<?= $view->url('/upload') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Upload Now
        </a>
    </div>
    <?php else: ?>

    <!-- Featured Top 3 (First Page Only) -->
    <?php if ($page === 1 && count($images) >= 3): ?>
    <section class="border-b border-neutral-800">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php for ($i = 0; $i < 3; $i++):
                    if (!isset($images[$i])) continue;
                    $img = $images[$i];
                    $thumb = $img['medium_webp_path'] ?: $img['medium_path'] ?: $img['thumbnail_webp_path'] ?: $img['thumbnail_path'];
                    $badgeColors = [
                        0 => 'bg-amber-500 text-black',
                        1 => 'bg-neutral-400 text-black',
                        2 => 'bg-orange-600 text-white'
                    ];
                ?>
                <a href="<?= $view->url('/image/' . $img['slug']) ?>" class="group block">
                    <div class="relative aspect-[4/5] bg-neutral-900 rounded-xl overflow-hidden">
                        <img src="<?= e('/uploads/' . $thumb) ?>"
                             alt="<?= e($img['alt_text'] ?: $img['title']) ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             fetchpriority="high"
                             decoding="async">

                        <!-- Gradient Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                        <!-- Rank Badge -->
                        <div class="absolute top-3 left-3 w-10 h-10 <?= $badgeColors[$i] ?> rounded-full flex items-center justify-center text-lg font-bold">
                            <?= $i + 1 ?>
                        </div>

                        <!-- Content -->
                        <div class="absolute bottom-0 left-0 right-0 p-4">
                            <h3 class="text-white font-semibold text-lg mb-2 line-clamp-2"><?= e($img['title']) ?></h3>
                            <div class="flex items-center gap-4 text-sm text-neutral-300">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    <?= number_format($img['favorite_count']) ?>
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <?= number_format($img['view_count']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endfor; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Main Grid -->
    <section class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Content Grid -->
            <div class="flex-1 min-w-0">
                <?php if ($page === 1 && count($images) > 3): ?>
                <h2 class="text-lg font-semibold text-white mb-6">More Trending</h2>
                <?php elseif ($page > 1): ?>
                <h2 class="text-lg font-semibold text-white mb-6">Page <?= $page ?></h2>
                <?php endif; ?>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php
                    $startIndex = ($page === 1) ? 3 : 0;
                    foreach (array_slice($images, $startIndex) as $image):
                        $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path'];
                        $isAnimated = !empty($image['is_animated']);
                    ?>
                    <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="group block">
                        <div class="relative aspect-[3/4] bg-neutral-900 rounded-lg overflow-hidden border border-neutral-800 group-hover:border-red-600/50 transition-colors">
                            <img src="<?= e(uploads_url($thumbSrc)) ?>"
                                 alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                 loading="lazy">

                            <?php if ($isAnimated): ?>
                            <span class="absolute top-2 left-2 px-2 py-0.5 bg-red-600 text-white text-xs font-bold rounded">GIF</span>
                            <?php endif; ?>

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                                <div class="absolute bottom-0 left-0 right-0 p-3">
                                    <div class="flex items-center justify-between text-white text-sm">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                            </svg>
                                            <?= number_format($image['favorite_count']) ?>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <?= number_format($image['view_count']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-white text-sm font-medium truncate group-hover:text-red-400 transition-colors"><?= e($image['title']) ?></h3>
                            <?php if (!empty($image['user'])): ?>
                            <p class="text-neutral-500 text-xs mt-0.5">by <?= e($image['user']['display_name'] ?: $image['user']['username']) ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-10 flex justify-center">
                    <div class="inline-flex items-center gap-1 p-1 bg-neutral-900 rounded-lg border border-neutral-800">
                        <?php if ($page > 1): ?>
                        <a href="<?= $view->url('/trending') ?>?period=<?= $period ?>&page=<?= $page - 1 ?>"
                           class="px-4 py-2 text-sm font-medium text-neutral-300 hover:text-white hover:bg-neutral-800 rounded-md transition-colors">
                            Prev
                        </a>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                        <a href="<?= $view->url('/trending') ?>?period=<?= $period ?>&page=<?= $i ?>"
                           class="w-10 h-10 flex items-center justify-center text-sm font-medium rounded-md transition-colors <?= $i === $page ? 'bg-red-600 text-white' : 'text-neutral-300 hover:text-white hover:bg-neutral-800' ?>">
                            <?= $i ?>
                        </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <a href="<?= $view->url('/trending') ?>?period=<?= $period ?>&page=<?= $page + 1 ?>"
                           class="px-4 py-2 text-sm font-medium text-neutral-300 hover:text-white hover:bg-neutral-800 rounded-md transition-colors">
                            Next
                        </a>
                        <?php endif; ?>
                    </div>
                </nav>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="lg:w-64 flex-shrink-0 space-y-6">
                <!-- Trending Tags -->
                <?php if (!empty($trendingTags)): ?>
                <div class="bg-neutral-900 rounded-xl p-5 border border-neutral-800">
                    <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        </svg>
                        Hot Tags
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($trendingTags as $tag): ?>
                        <a href="<?= $view->url('/tag/' . $tag['slug']) ?>"
                           class="px-3 py-1.5 bg-neutral-800 hover:bg-red-600/20 border border-neutral-700 hover:border-red-500/30 text-neutral-300 hover:text-red-400 rounded-lg text-sm transition-all">
                            #<?= e($tag['name']) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="bg-neutral-900 rounded-xl p-5 border border-neutral-800">
                    <h3 class="font-semibold text-white mb-4">Stats</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-neutral-400 text-sm">Total Photos</span>
                            <span class="text-white font-medium"><?= number_format($total) ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-neutral-400 text-sm">Page</span>
                            <span class="text-white font-medium"><?= $page ?> / <?= $totalPages ?></span>
                        </div>
                    </div>
                </div>

                <!-- Upload CTA -->
                <div class="bg-neutral-900 rounded-xl p-5 border border-neutral-800 text-center">
                    <div class="w-14 h-14 bg-red-600/20 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-white mb-2">Join the Ranks</h3>
                    <p class="text-neutral-400 text-sm mb-4">Upload your best work</p>
                    <a href="<?= $view->url('/upload') ?>"
                       class="block w-full py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                        Upload Now
                    </a>
                </div>
            </aside>
        </div>
    </section>
    <?php endif; ?>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
