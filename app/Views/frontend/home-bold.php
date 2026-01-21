<?php
/**
 * Home Page - Bold Modern Theme
 * Dramatic, impactful, high-contrast layout
 */
$heroImage = $featuredImages[0] ?? $trendingImages[0] ?? null;
?>

<!-- Full-Screen Hero -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <!-- Background Image with Overlay -->
    <?php if ($heroImage): ?>
    <div class="absolute inset-0">
        <img src="<?= e('/uploads/' . ($heroImage['storage_path'] ?? $heroImage['thumbnail_path'])) ?>"
             alt="" class="w-full h-full object-cover scale-110" id="heroImage">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-900/90 via-indigo-900/80 to-pink-900/90"></div>
    </div>
    <?php else: ?>
    <div class="absolute inset-0 bg-gradient-to-br from-purple-900 via-indigo-900 to-pink-900"></div>
    <?php endif; ?>

    <!-- Animated Background Shapes -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-pink-500/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
    </div>

    <!-- Hero Content -->
    <div class="relative z-10 text-center px-4 max-w-5xl mx-auto">
        <h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-white mb-6 leading-none tracking-tight">
            <span class="block">DISCOVER</span>
            <span class="block bg-gradient-to-r from-pink-400 via-purple-400 to-indigo-400 bg-clip-text text-transparent">AMAZING</span>
            <span class="block">VISUALS</span>
        </h1>
        <p class="text-xl md:text-2xl text-white/80 mb-10 max-w-2xl mx-auto font-light">
            Explore trending images, creative content, and inspiring imagery from around the world.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= $view->url('/gallery') ?>"
               class="group relative px-10 py-5 bg-white text-purple-900 font-bold text-lg rounded-full overflow-hidden transition-transform hover:scale-105">
                <span class="relative z-10">EXPLORE GALLERY</span>
                <div class="absolute inset-0 bg-gradient-to-r from-pink-400 to-purple-400 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <span class="absolute inset-0 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity font-bold">EXPLORE GALLERY</span>
            </a>
            <a href="<?= $view->url('/trending') ?>"
               class="px-10 py-5 border-2 border-white text-white font-bold text-lg rounded-full hover:bg-white hover:text-purple-900 transition-all">
                SEE TRENDING
            </a>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 animate-bounce">
        <svg class="w-8 h-8 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
        </svg>
    </div>
</section>

<!-- Stats Bar -->
<section class="bg-neutral-900 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-4xl md:text-5xl font-black text-white mb-2">10K+</div>
                <div class="text-neutral-400 uppercase tracking-wider text-sm">Images</div>
            </div>
            <div>
                <div class="text-4xl md:text-5xl font-black text-white mb-2">50+</div>
                <div class="text-neutral-400 uppercase tracking-wider text-sm">Categories</div>
            </div>
            <div>
                <div class="text-4xl md:text-5xl font-black text-white mb-2">1M+</div>
                <div class="text-neutral-400 uppercase tracking-wider text-sm">Views</div>
            </div>
            <div>
                <div class="text-4xl md:text-5xl font-black text-white mb-2">24/7</div>
                <div class="text-neutral-400 uppercase tracking-wider text-sm">Updates</div>
            </div>
        </div>
    </div>
</section>

<!-- Trending Section - Large Cards -->
<?php if (!empty($trendingImages)): ?>
<section class="py-20 bg-white dark:bg-neutral-900">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <span class="inline-block px-4 py-2 bg-gradient-to-r from-pink-500 to-purple-500 text-white text-sm font-bold rounded-full mb-4">HOT RIGHT NOW</span>
            <h2 class="text-4xl md:text-6xl font-black dark:text-white">Trending Images</h2>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach (array_slice($trendingImages, 0, 4) as $index => $image): ?>
            <?php $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path']; ?>
            <a href="<?= $view->url('/image/' . $image['slug']) ?>"
               class="group relative rounded-3xl overflow-hidden <?= $index === 0 ? 'md:col-span-2 md:row-span-2' : '' ?>">
                <div class="<?= $index === 0 ? 'aspect-square' : 'aspect-[4/5]' ?> bg-neutral-200 dark:bg-neutral-800">
                    <img data-src="<?= e(uploads_url($thumbSrc)) ?>"
                         src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 500'%3E%3Crect fill='%23374151' width='400' height='500'/%3E%3C/svg%3E"
                         alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                    <h3 class="text-white font-bold text-xl mb-2"><?= e($image['title']) ?></h3>
                    <div class="flex items-center gap-4 text-white/70 text-sm">
                        <span><?= number_format($image['view_count'] ?? 0) ?> views</span>
                    </div>
                </div>
                <?php if ($index < 3): ?>
                <div class="absolute top-4 left-4 w-10 h-10 rounded-full bg-white flex items-center justify-center font-black text-purple-600">
                    <?= $index + 1 ?>
                </div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-12">
            <a href="<?= $view->url('/trending') ?>"
               class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-pink-500 to-purple-500 text-white font-bold rounded-full hover:shadow-lg hover:shadow-purple-500/30 transition-all hover:scale-105">
                VIEW ALL TRENDING
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Categories - Bold Cards -->
<?php if (!empty($categories)): ?>
<section class="py-20 bg-neutral-100 dark:bg-neutral-800">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-6xl font-black dark:text-white mb-4">Browse Categories</h2>
            <p class="text-xl text-neutral-600 dark:text-neutral-400">Find exactly what you're looking for</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php
            $categoryGradients = [
                'from-green-400 to-emerald-600',
                'from-blue-400 to-indigo-600',
                'from-purple-400 to-violet-600',
                'from-orange-400 to-red-600',
                'from-pink-400 to-rose-600',
                'from-yellow-400 to-amber-600',
                'from-cyan-400 to-teal-600',
                'from-fuchsia-400 to-purple-600',
            ];
            foreach ($categories as $i => $cat):
                $gradient = $categoryGradients[$i % count($categoryGradients)];
            ?>
            <a href="<?= $view->url('/category/' . $cat['slug']) ?>"
               class="group relative h-40 md:h-52 rounded-2xl overflow-hidden bg-gradient-to-br <?= $gradient ?> hover:scale-105 transition-transform duration-300 hover:shadow-xl">
                <div class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors"></div>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-white p-4 text-center">
                    <h3 class="text-xl md:text-2xl font-black mb-1"><?= e($cat['name']) ?></h3>
                    <span class="text-white/70 text-sm"><?= number_format($cat['image_count'] ?? 0) ?> images</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Recent Images - Horizontal Scroll -->
<?php if (!empty($recentImages)): ?>
<section class="py-20 bg-white dark:bg-neutral-900 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-end justify-between mb-12">
            <div>
                <span class="text-purple-500 font-bold uppercase tracking-wider text-sm">Fresh Content</span>
                <h2 class="text-4xl md:text-5xl font-black dark:text-white">Just Added</h2>
            </div>
            <a href="<?= $view->url('/gallery') ?>" class="text-purple-500 font-bold hover:text-purple-600 transition hidden md:block">
                View All &rarr;
            </a>
        </div>
    </div>

    <!-- Horizontal Scroll Gallery -->
    <div class="flex gap-6 overflow-x-auto pb-8 px-4 snap-x snap-mandatory scrollbar-hide" style="-webkit-overflow-scrolling: touch;">
        <div class="w-4 flex-shrink-0"></div>
        <?php foreach ($recentImages as $image): ?>
        <?php $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path']; ?>
        <a href="<?= $view->url('/image/' . $image['slug']) ?>"
           class="group flex-shrink-0 w-72 snap-start">
            <div class="aspect-[3/4] rounded-2xl overflow-hidden bg-neutral-200 dark:bg-neutral-800 mb-4">
                <img data-src="<?= e(uploads_url($thumbSrc)) ?>"
                     src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 400'%3E%3Crect fill='%23374151' width='300' height='400'/%3E%3C/svg%3E"
                     alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </div>
            <h3 class="font-bold text-neutral-900 dark:text-white truncate"><?= e($image['title']) ?></h3>
        </a>
        <?php endforeach; ?>
        <div class="w-4 flex-shrink-0"></div>
    </div>
</section>
<?php endif; ?>

<!-- Tags Cloud -->
<?php if (!empty($trendingTags)): ?>
<section class="py-16 bg-neutral-900">
    <div class="max-w-5xl mx-auto px-4 text-center">
        <h3 class="text-2xl font-bold text-white mb-8">Trending Tags</h3>
        <div class="flex flex-wrap justify-center gap-3">
            <?php foreach ($trendingTags as $tag): ?>
            <a href="<?= $view->url('/tag/' . $tag['slug']) ?>"
               class="px-5 py-2 bg-white/10 hover:bg-gradient-to-r hover:from-pink-500 hover:to-purple-500 text-white rounded-full transition-all hover:scale-105 font-medium">
                #<?= e($tag['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="py-24 bg-gradient-to-br from-purple-600 via-indigo-600 to-pink-600 relative overflow-hidden">
    <!-- Decorative Elements -->
    <div class="absolute top-0 left-0 w-72 h-72 bg-white/10 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/10 rounded-full translate-x-1/2 translate-y-1/2"></div>

    <div class="relative z-10 max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-4xl md:text-6xl font-black text-white mb-6">Ready to Dive In?</h2>
        <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
            Join our community of visual enthusiasts. Save favorites, discover trends, and explore unlimited creativity.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= $view->url('/register') ?>"
               class="px-10 py-5 bg-white text-purple-600 font-black text-lg rounded-full hover:shadow-2xl hover:scale-105 transition-all">
                GET STARTED FREE
            </a>
            <a href="<?= $view->url('/gallery') ?>"
               class="px-10 py-5 border-2 border-white text-white font-bold text-lg rounded-full hover:bg-white hover:text-purple-600 transition-all">
                BROWSE GALLERY
            </a>
        </div>
    </div>
</section>

<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
</style>
