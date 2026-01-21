<?php
/**
 * Pexels-Style Light Trending Page
 * Clean, bright design matching pexels.com
 */
?>

<!-- Hero Header -->
<section class="bg-white border-b border-neutral-200">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h1 class="text-3xl font-semibold text-neutral-900 mb-2">Trending</h1>
                <p class="text-neutral-500">
                    <?= number_format($total) ?> photos trending right now
                </p>
            </div>

            <!-- Period Filter Pills -->
            <div class="flex items-center gap-2">
                <?php
                $periods = [
                    'today' => 'Today',
                    'week' => 'This Week',
                    'month' => 'This Month',
                    'all' => 'All Time',
                ];
                foreach ($periods as $key => $label):
                    $isActive = $period === $key;
                ?>
                <a href="<?= $view->url('/trending') ?>?period=<?= $key ?>"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $isActive ? 'bg-neutral-900 text-white' : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<div class="bg-white min-h-screen">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <?php if (empty($images)): ?>
        <!-- Empty State -->
        <div class="text-center py-20">
            <div class="w-20 h-20 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-neutral-900 mb-2">No trending photos yet</h2>
            <p class="text-neutral-500 mb-6">Be the first to upload and get discovered!</p>
            <a href="<?= $view->url('/upload') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-teal-500 hover:bg-teal-600 text-white font-medium rounded-lg transition-colors">
                Upload Photos
            </a>
        </div>
        <?php else: ?>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Main Content -->
            <div class="flex-1 min-w-0">
                <!-- Featured Top 3 -->
                <?php if ($page === 1 && count($images) >= 3): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <?php foreach (array_slice($images, 0, 3) as $rank => $image):
                        $thumbSrc = $image['medium_webp_path'] ?: $image['medium_path'] ?: $image['thumbnail_webp_path'] ?: $image['thumbnail_path'];
                    ?>
                    <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="group relative block">
                        <div class="aspect-[4/5] bg-neutral-100 rounded-xl overflow-hidden">
                            <img src="<?= e(uploads_url($thumbSrc)) ?>"
                                 alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

                            <!-- Gradient Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                            <!-- Rank Badge -->
                            <div class="absolute top-4 left-4 w-10 h-10 rounded-full flex items-center justify-center text-lg font-bold shadow-lg <?php
                                echo match($rank) {
                                    0 => 'bg-yellow-400 text-yellow-900',
                                    1 => 'bg-gray-300 text-gray-700',
                                    2 => 'bg-orange-400 text-white',
                                    default => 'bg-white text-neutral-900'
                                };
                            ?>">
                                <?= $rank + 1 ?>
                            </div>

                            <!-- Content -->
                            <div class="absolute bottom-0 left-0 right-0 p-5">
                                <h3 class="text-white font-medium text-lg mb-2 line-clamp-2"><?= e($image['title']) ?></h3>

                                <?php if ($image['user']): ?>
                                <div class="flex items-center gap-2 mb-3">
                                    <?php if ($image['user']['avatar_path']): ?>
                                    <img src="<?= $view->url('/uploads/' . $image['user']['avatar_path']) ?>" class="w-6 h-6 rounded-full object-cover" alt="">
                                    <?php else: ?>
                                    <div class="w-6 h-6 rounded-full bg-white/30 flex items-center justify-center text-white text-xs font-bold">
                                        <?= strtoupper(substr($image['user']['username'], 0, 1)) ?>
                                    </div>
                                    <?php endif; ?>
                                    <span class="text-white/80 text-sm"><?= e($image['user']['display_name'] ?: $image['user']['username']) ?></span>
                                </div>
                                <?php endif; ?>

                                <div class="flex items-center gap-4 text-white/70 text-sm">
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
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Masonry Grid -->
                <div class="columns-2 md:columns-3 xl:columns-4 gap-4 space-y-4">
                    <?php
                    $startIndex = ($page === 1) ? 3 : 0;
                    foreach (array_slice($images, $startIndex) as $image):
                        $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path'];
                        $isAnimated = !empty($image['is_animated']);
                    ?>
                    <div class="break-inside-avoid group">
                        <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="block relative rounded-lg overflow-hidden bg-neutral-100">
                            <img src="<?= e(uploads_url($thumbSrc)) ?>"
                                 alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                                 class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-300"
                                 loading="lazy">

                            <?php if ($isAnimated): ?>
                            <span class="absolute top-3 left-3 px-2 py-0.5 bg-black/70 text-white text-xs font-bold rounded">GIF</span>
                            <?php endif; ?>

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <!-- User Info -->
                                <?php if ($image['user']): ?>
                                <div class="absolute bottom-3 left-3 flex items-center gap-2">
                                    <?php if ($image['user']['avatar_path']): ?>
                                    <img src="<?= $view->url('/uploads/' . $image['user']['avatar_path']) ?>" class="w-7 h-7 rounded-full object-cover" alt="">
                                    <?php else: ?>
                                    <div class="w-7 h-7 rounded-full bg-white/30 flex items-center justify-center text-white text-xs font-bold">
                                        <?= strtoupper(substr($image['user']['username'], 0, 1)) ?>
                                    </div>
                                    <?php endif; ?>
                                    <span class="text-white text-sm font-medium"><?= e($image['user']['display_name'] ?: $image['user']['username']) ?></span>
                                </div>
                                <?php endif; ?>

                                <!-- Actions -->
                                <div class="absolute top-3 right-3 flex items-center gap-2">
                                    <span class="flex items-center gap-1 px-2 py-1 bg-white/90 rounded text-neutral-700 text-xs">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                        <?= number_format($image['favorite_count']) ?>
                                    </span>
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
                        <a href="<?= $view->url('/trending') ?>?period=<?= $period ?>&page=<?= $page - 1 ?>"
                           class="px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-white rounded-md transition-colors">
                            Previous
                        </a>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                        <a href="<?= $view->url('/trending') ?>?period=<?= $period ?>&page=<?= $i ?>"
                           class="w-10 h-10 flex items-center justify-center text-sm font-medium rounded-md transition-colors <?= $i === $page ? 'bg-neutral-900 text-white' : 'text-neutral-600 hover:bg-white' ?>">
                            <?= $i ?>
                        </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <a href="<?= $view->url('/trending') ?>?period=<?= $period ?>&page=<?= $page + 1 ?>"
                           class="px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-white rounded-md transition-colors">
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
                <div class="bg-neutral-50 rounded-xl p-6">
                    <h3 class="font-semibold text-neutral-900 mb-4">Trending Searches</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($trendingTags as $tag): ?>
                        <a href="<?= $view->url('/tag/' . $tag['slug']) ?>"
                           class="px-3 py-1.5 bg-white hover:bg-neutral-100 text-neutral-700 rounded-full text-sm font-medium border border-neutral-200 transition-colors">
                            <?= e($tag['name']) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Top Contributors -->
                <?php if (!empty($topContributors)): ?>
                <div class="bg-neutral-50 rounded-xl p-6">
                    <h3 class="font-semibold text-neutral-900 mb-4">Top Contributors</h3>
                    <div class="space-y-3">
                        <?php foreach ($topContributors as $index => $contributor): ?>
                        <a href="<?= $view->url('/user/' . $contributor['username']) ?>"
                           class="flex items-center gap-3 p-2 -mx-2 rounded-lg hover:bg-white transition-colors">
                            <div class="relative">
                                <?php if ($contributor['avatar_path']): ?>
                                <img src="<?= $view->url('/uploads/' . $contributor['avatar_path']) ?>"
                                     class="w-10 h-10 rounded-full object-cover" alt="">
                                <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center text-white font-bold">
                                    <?= strtoupper(substr($contributor['username'], 0, 1)) ?>
                                </div>
                                <?php endif; ?>
                                <?php if ($index < 3): ?>
                                <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold shadow <?php
                                    echo match($index) {
                                        0 => 'bg-yellow-400 text-yellow-900',
                                        1 => 'bg-gray-300 text-gray-700',
                                        2 => 'bg-orange-400 text-white',
                                        default => ''
                                    };
                                ?>"><?= $index + 1 ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-neutral-900 text-sm truncate">
                                    <?= e($contributor['display_name'] ?: $contributor['username']) ?>
                                </div>
                                <div class="text-xs text-neutral-500"><?= number_format($contributor['total_score']) ?> points</div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Upload CTA -->
                <div class="bg-teal-500 rounded-xl p-6 text-white text-center">
                    <h3 class="font-semibold text-lg mb-2">Share Your Work</h3>
                    <p class="text-teal-100 text-sm mb-4">Upload photos and join the trending ranks!</p>
                    <a href="<?= $view->url('/upload') ?>"
                       class="block w-full py-3 bg-white text-teal-600 font-medium rounded-lg hover:bg-neutral-100 transition-colors">
                        Upload Now
                    </a>
                </div>
            </aside>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
