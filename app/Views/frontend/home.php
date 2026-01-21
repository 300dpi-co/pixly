<!-- Quick Nav Bar -->
<div class="sticky top-16 z-30 bg-black/95 backdrop-blur-sm border-b border-white/5">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center gap-6 py-3 overflow-x-auto scrollbar-hide">
            <a href="<?= $view->url('/gallery') ?>" class="text-sm text-neutral-400 hover:text-white whitespace-nowrap transition-colors">All</a>
            <a href="<?= $view->url('/trending') ?>" class="text-sm text-red-400 hover:text-red-300 whitespace-nowrap transition-colors flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"/></svg>
                Trending
            </a>
            <?php if (!empty($categories)): ?>
            <span class="text-neutral-700">|</span>
            <?php foreach ($categories as $cat): ?>
            <a href="<?= $view->url('/category/' . $cat['slug']) ?>" class="text-sm text-neutral-400 hover:text-white whitespace-nowrap transition-colors"><?= e($cat['name']) ?></a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex gap-6">
        <!-- Main Content -->
        <div class="flex-1 min-w-0">
            <!-- Trending Section -->
            <?php if (!empty($trendingImages)): ?>
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-medium text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"/></svg>
                        Trending
                    </h2>
                    <a href="<?= $view->url('/trending') ?>" class="text-xs text-amber-600/80 hover:text-amber-500 transition-colors">See all</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                    <?php foreach ($trendingImages as $index => $image): ?>
                    <?php
                    $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path'];
                    $isAnimated = !empty($image['is_animated']);
                    ?>
                    <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="group block">
                        <div class="aspect-[4/5] bg-neutral-900 rounded overflow-hidden relative border border-white/5 group-hover:border-red-800/30 transition-all">
                            <img data-src="<?= e('/uploads/' . $thumbSrc) ?>"
                                 src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3C/svg%3E"
                                 alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 loading="lazy">
                            <?php if ($index < 3): ?>
                            <div class="absolute top-1.5 left-1.5 w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold
                                <?= $index === 0 ? 'bg-amber-500 text-black' : ($index === 1 ? 'bg-neutral-400 text-black' : 'bg-amber-700 text-white') ?>">
                                <?= $index + 1 ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($isAnimated): ?>
                            <span class="absolute <?= $index < 3 ? 'top-1.5 left-8' : 'top-1.5 left-1.5' ?> px-1 py-0.5 bg-black/70 text-white text-[9px] font-bold rounded">GIF</span>
                            <?php endif; ?>
                            <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/80 to-transparent">
                                <p class="text-white text-xs truncate"><?= e($image['title']) ?></p>
                                <div class="flex items-center gap-2 text-[10px] text-neutral-400 mt-0.5">
                                    <span><?= number_format($image['view_count']) ?> views</span>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent/All Images -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-medium text-white">Latest</h2>
                    <a href="<?= $view->url('/gallery') ?>" class="text-xs text-amber-600/80 hover:text-amber-500 transition-colors">See all</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                    <?php if (!empty($recentImages)): ?>
                    <?php foreach ($recentImages as $image): ?>
                    <?php
                    $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path'];
                    $isAnimated = !empty($image['is_animated']);
                    ?>
                    <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="group block">
                        <div class="aspect-[4/5] bg-neutral-900 rounded overflow-hidden relative border border-white/5 group-hover:border-red-800/30 transition-all">
                            <img data-src="<?= e('/uploads/' . $thumbSrc) ?>"
                                 src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3C/svg%3E"
                                 alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 loading="lazy">
                            <?php if ($isAnimated): ?>
                            <span class="absolute top-1.5 left-1.5 px-1 py-0.5 bg-black/70 text-white text-[9px] font-bold rounded">GIF</span>
                            <?php endif; ?>
                            <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/80 to-transparent">
                                <p class="text-white text-xs truncate"><?= e($image['title']) ?></p>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="col-span-full text-center py-12">
                        <p class="text-neutral-500">No images yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Load More -->
            <div class="mt-8 text-center">
                <a href="<?= $view->url('/gallery') ?>" class="inline-block px-8 py-3 bg-red-800 hover:bg-red-700 text-white text-sm font-medium rounded transition-colors">
                    Browse All Images
                </a>
            </div>
        </div>

        <!-- Sidebar -->
        <aside class="hidden lg:block w-56 flex-shrink-0">
            <!-- Search -->
            <form action="<?= $view->url('/search') ?>" method="GET" class="mb-6">
                <div class="relative">
                    <input type="text" name="q" placeholder="Search..."
                           class="w-full px-3 py-2 text-sm bg-neutral-900 border border-white/5 text-white placeholder-neutral-500 rounded focus:border-red-800/50 focus:ring-0 transition-all">
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-neutral-500 hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Categories -->
            <?php if (!empty($categories)): ?>
            <div class="mb-6">
                <h3 class="text-xs font-medium text-neutral-500 uppercase tracking-wider mb-3">Categories</h3>
                <div class="space-y-0.5">
                    <?php foreach ($categories as $cat): ?>
                    <a href="<?= $view->url('/category/' . $cat['slug']) ?>" class="flex items-center justify-between px-2 py-1.5 text-sm text-neutral-400 hover:text-white hover:bg-white/3 rounded transition-all">
                        <span><?= e($cat['name']) ?></span>
                        <span class="text-[10px] text-neutral-600"><?= number_format($cat['image_count'] ?? 0) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Popular Tags -->
            <?php if (!empty($trendingTags)): ?>
            <div class="mb-6">
                <h3 class="text-xs font-medium text-neutral-500 uppercase tracking-wider mb-3">Popular Tags</h3>
                <div class="flex flex-wrap gap-1">
                    <?php foreach (array_slice($trendingTags, 0, 15) as $tag): ?>
                    <a href="<?= $view->url('/tag/' . $tag['slug']) ?>" class="px-2 py-0.5 text-[11px] text-neutral-500 hover:text-red-300 bg-white/3 hover:bg-red-900/20 rounded transition-all">
                        <?= e($tag['name']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Premium (minimal) -->
            <div class="p-3 bg-gradient-to-br from-amber-900/10 to-red-900/10 border border-amber-900/20 rounded">
                <div class="flex items-center gap-1.5 mb-1.5">
                    <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <span class="text-xs font-medium text-amber-400">Premium</span>
                </div>
                <p class="text-[10px] text-neutral-500 mb-2">Ad-free & exclusive</p>
                <a href="<?= $view->url('/premium') ?>" class="block text-center px-2 py-1 bg-amber-600 hover:bg-amber-500 text-black text-[11px] font-medium rounded transition-colors">
                    Upgrade
                </a>
            </div>
        </aside>
    </div>
</div>

<style>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
