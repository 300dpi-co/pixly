<?php
/**
 * Dark Cinematic Trending Page
 * Moody, film-inspired design for adult/artistic themes
 */
?>

<!-- Cinematic Hero -->
<section class="relative bg-black overflow-hidden">
    <!-- Animated Background Gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-red-950/50 via-black to-purple-950/30"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-red-900/20 via-transparent to-transparent"></div>

    <!-- Film Grain Overlay -->
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=\"0 0 256 256\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cfilter id=\"noise\"%3E%3CfeTurbulence type=\"fractalNoise\" baseFrequency=\"0.9\" numOctaves=\"4\" stitchTiles=\"stitch\"/%3E%3C/filter%3E%3Crect width=\"100%25\" height=\"100%25\" filter=\"url(%23noise)\"/%3E%3C/svg%3E');"></div>

    <div class="relative max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-8">
            <div>
                <!-- Trending Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-red-600/20 border border-red-500/30 rounded-full mb-6">
                    <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                    <span class="text-red-400 text-sm font-medium tracking-wide uppercase">Live Trending</span>
                </div>

                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4 tracking-tight">
                    What's <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-pink-500">Hot</span> Right Now
                </h1>
                <p class="text-neutral-400 text-lg max-w-xl">
                    <?= number_format($total) ?> photos trending • Curated by engagement
                </p>
            </div>

            <!-- Period Selector -->
            <div class="flex items-center gap-1 p-1 bg-white/5 rounded-xl backdrop-blur-sm border border-white/10">
                <?php
                $periods = ['today' => 'Today', 'week' => 'Week', 'month' => 'Month', 'all' => 'All'];
                foreach ($periods as $key => $label):
                    $isActive = $period === $key;
                ?>
                <a href="<?= $view->url('/trending') ?>?period=<?= $key ?>"
                   class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all <?= $isActive ? 'bg-red-600 text-white shadow-lg shadow-red-600/30' : 'text-neutral-400 hover:text-white hover:bg-white/5' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<div class="bg-neutral-950 min-h-screen">
    <?php if (empty($images)): ?>
    <!-- Empty State -->
    <div class="max-w-7xl mx-auto px-4 py-24 text-center">
        <div class="w-24 h-24 bg-neutral-900 rounded-full flex items-center justify-center mx-auto mb-6 border border-neutral-800">
            <svg class="w-12 h-12 text-neutral-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-white mb-3">No trending content</h2>
        <p class="text-neutral-500 mb-8 max-w-md mx-auto">The spotlight awaits. Be the first to upload and claim your place.</p>
        <a href="<?= $view->url('/upload') ?>" class="inline-flex items-center gap-2 px-8 py-4 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Start Uploading
        </a>
    </div>
    <?php else: ?>

    <!-- Featured Hero Section (Top 3) -->
    <?php if ($page === 1 && count($images) >= 3): ?>
    <section class="relative border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-12 gap-4 h-[500px] md:h-[600px]">
                <!-- #1 - Large Feature -->
                <?php
                $topImage = $images[0];
                $topThumb = $topImage['medium_webp_path'] ?: $topImage['medium_path'] ?: $topImage['storage_path'];
                ?>
                <div class="col-span-12 md:col-span-7 relative group">
                    <a href="<?= $view->url('/image/' . $topImage['slug']) ?>" class="block h-full">
                        <div class="relative h-full rounded-2xl overflow-hidden bg-neutral-900">
                            <img src="<?= e('/uploads/' . $topThumb) ?>"
                                 alt="<?= e($topImage['alt_text'] ?: $topImage['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">

                            <!-- Gradient -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent"></div>

                            <!-- #1 Badge -->
                            <div class="absolute top-6 left-6 flex items-center gap-3">
                                <div class="w-14 h-14 bg-gradient-to-br from-yellow-400 to-amber-600 rounded-2xl flex items-center justify-center text-2xl font-black text-black shadow-xl shadow-amber-500/30">
                                    1
                                </div>
                                <div class="px-4 py-2 bg-black/60 backdrop-blur-sm rounded-xl border border-white/10">
                                    <span class="text-amber-400 text-sm font-bold">#1 TRENDING</span>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="absolute bottom-0 left-0 right-0 p-8">
                                <h2 class="text-3xl font-bold text-white mb-3"><?= e($topImage['title']) ?></h2>

                                <?php if ($topImage['user']): ?>
                                <div class="flex items-center gap-3 mb-4">
                                    <?php if ($topImage['user']['avatar_path']): ?>
                                    <img src="<?= $view->url('/uploads/' . $topImage['user']['avatar_path']) ?>" class="w-10 h-10 rounded-full object-cover ring-2 ring-white/20" alt="">
                                    <?php else: ?>
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-600 to-pink-600 flex items-center justify-center text-white font-bold ring-2 ring-white/20">
                                        <?= strtoupper(substr($topImage['user']['username'], 0, 1)) ?>
                                    </div>
                                    <?php endif; ?>
                                    <span class="text-white font-medium"><?= e($topImage['user']['display_name'] ?: $topImage['user']['username']) ?></span>
                                </div>
                                <?php endif; ?>

                                <div class="flex items-center gap-6 text-neutral-300">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                        <?= number_format($topImage['favorite_count']) ?>
                                    </span>
                                    <span class="flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <?= number_format($topImage['view_count']) ?>
                                    </span>
                                    <span class="flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        <?= number_format($topImage['download_count'] ?? 0) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- #2 and #3 -->
                <div class="col-span-12 md:col-span-5 flex flex-col gap-4">
                    <?php foreach ([1, 2] as $rank):
                        if (!isset($images[$rank])) continue;
                        $img = $images[$rank];
                        $thumb = $img['medium_webp_path'] ?: $img['medium_path'] ?: $img['thumbnail_webp_path'] ?: $img['thumbnail_path'];
                        $badgeColors = [1 => 'from-gray-300 to-gray-400 text-gray-800', 2 => 'from-orange-500 to-orange-700 text-white'];
                    ?>
                    <a href="<?= $view->url('/image/' . $img['slug']) ?>" class="flex-1 group relative">
                        <div class="relative h-full rounded-2xl overflow-hidden bg-neutral-900">
                            <img src="<?= e('/uploads/' . $thumb) ?>"
                                 alt="<?= e($img['alt_text'] ?: $img['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">

                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent"></div>

                            <!-- Badge -->
                            <div class="absolute top-4 left-4 w-10 h-10 bg-gradient-to-br <?= $badgeColors[$rank] ?> rounded-xl flex items-center justify-center text-lg font-black shadow-lg">
                                <?= $rank + 1 ?>
                            </div>

                            <!-- Content -->
                            <div class="absolute bottom-0 left-0 right-0 p-5">
                                <h3 class="text-lg font-bold text-white mb-2 line-clamp-1"><?= e($img['title']) ?></h3>
                                <div class="flex items-center gap-4 text-sm text-neutral-400">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                        <?= number_format($img['favorite_count']) ?>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <?= number_format($img['view_count']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Main Grid -->
    <section class="max-w-7xl mx-auto px-4 py-12">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Content Grid -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-bold text-white">
                        <?php if ($page === 1): ?>
                        More Trending
                        <?php else: ?>
                        Page <?= $page ?>
                        <?php endif; ?>
                    </h2>
                    <span class="text-neutral-500 text-sm"><?= number_format($total) ?> total</span>
                </div>

                <!-- Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                    <?php
                    $startIndex = ($page === 1) ? 3 : 0;
                    foreach (array_slice($images, $startIndex) as $image):
                        $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path'];
                        $isAnimated = !empty($image['is_animated']);
                    ?>
                    <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="group block">
                        <div class="relative aspect-[3/4] bg-neutral-900 rounded-xl overflow-hidden ring-1 ring-white/5 group-hover:ring-red-500/50 transition-all">
                            <img src="<?= e(uploads_url($thumbSrc)) ?>"
                                 alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                 loading="lazy">

                            <?php if ($isAnimated): ?>
                            <span class="absolute top-2 left-2 px-2 py-0.5 bg-red-600 text-white text-xs font-bold rounded">GIF</span>
                            <?php endif; ?>

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                                <div class="absolute bottom-0 left-0 right-0 p-4">
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
                        <div class="mt-3">
                            <h3 class="text-white font-medium text-sm truncate group-hover:text-red-400 transition-colors"><?= e($image['title']) ?></h3>
                            <?php if ($image['user']): ?>
                            <p class="text-neutral-500 text-xs mt-1">by <?= e($image['user']['display_name'] ?: $image['user']['username']) ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-12 flex justify-center">
                    <div class="inline-flex items-center gap-1 p-1 bg-neutral-900 rounded-xl border border-white/5">
                        <?php if ($page > 1): ?>
                        <a href="<?= $view->url('/trending') ?>?period=<?= $period ?>&page=<?= $page - 1 ?>"
                           class="px-4 py-2 text-sm font-medium text-neutral-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">
                            Prev
                        </a>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                        <a href="<?= $view->url('/trending') ?>?period=<?= $period ?>&page=<?= $i ?>"
                           class="w-10 h-10 flex items-center justify-center text-sm font-medium rounded-lg transition-colors <?= $i === $page ? 'bg-red-600 text-white' : 'text-neutral-400 hover:text-white hover:bg-white/5' ?>">
                            <?= $i ?>
                        </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <a href="<?= $view->url('/trending') ?>?period=<?= $period ?>&page=<?= $page + 1 ?>"
                           class="px-4 py-2 text-sm font-medium text-neutral-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">
                            Next
                        </a>
                        <?php endif; ?>
                    </div>
                </nav>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="lg:w-72 flex-shrink-0 space-y-6">
                <!-- Trending Tags -->
                <?php if (!empty($trendingTags)): ?>
                <div class="bg-neutral-900 rounded-2xl p-6 border border-white/5">
                    <h3 class="font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                        </svg>
                        Hot Tags
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($trendingTags as $tag): ?>
                        <a href="<?= $view->url('/tag/' . $tag['slug']) ?>"
                           class="px-3 py-1.5 bg-white/5 hover:bg-red-600/20 border border-white/10 hover:border-red-500/30 text-neutral-300 hover:text-red-400 rounded-lg text-sm transition-all">
                            #<?= e($tag['name']) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick Stats -->
                <div class="bg-gradient-to-br from-red-950/50 to-neutral-900 rounded-2xl p-6 border border-red-500/20">
                    <h3 class="font-bold text-white mb-4">This <?= ucfirst($period === 'all' ? 'Period' : $period) ?></h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-neutral-400 text-sm">Total Photos</span>
                            <span class="text-white font-bold"><?= number_format($total) ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-neutral-400 text-sm">Current Page</span>
                            <span class="text-white font-bold"><?= $page ?> / <?= $totalPages ?></span>
                        </div>
                    </div>
                </div>

                <!-- Upload CTA -->
                <div class="bg-neutral-900 rounded-2xl p-6 border border-white/5 text-center">
                    <div class="w-16 h-16 bg-red-600/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-white mb-2">Join the Ranks</h3>
                    <p class="text-neutral-500 text-sm mb-4">Upload your best work and get trending</p>
                    <a href="<?= $view->url('/upload') ?>"
                       class="block w-full py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition-colors">
                        Upload Now
                    </a>
                </div>
            </aside>
        </div>
    </section>
    <?php endif; ?>
</div>

<style>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
