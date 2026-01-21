<?php
/**
 * Home Page - Neon Nights Theme
 * Cyberpunk aesthetic with neon colors, glowing effects, futuristic grid
 * SEO Optimized: Semantic HTML5, proper heading structure, rich internal linking
 */
$heroImages = array_slice($featuredImages ?: $trendingImages, 0, 3);
?>

<!-- Neon Hero - Glitch Style -->
<section class="relative min-h-screen bg-[#0a0a1a] overflow-hidden">
    <!-- Animated Grid Background -->
    <div class="absolute inset-0 opacity-20">
        <div class="absolute inset-0" style="background-image: linear-gradient(#ff00ff22 1px, transparent 1px), linear-gradient(90deg, #00ffff22 1px, transparent 1px); background-size: 50px 50px;"></div>
    </div>

    <!-- Glow Effects -->
    <div class="absolute top-20 left-10 w-96 h-96 bg-pink-500/20 rounded-full blur-[100px]"></div>
    <div class="absolute bottom-20 right-10 w-96 h-96 bg-cyan-500/20 rounded-full blur-[100px]"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 py-20">
        <!-- Glitch Title -->
        <div class="text-center mb-16">
            <h1 class="text-6xl md:text-8xl font-black text-white mb-4 tracking-tighter relative inline-block">
                <span class="relative z-10">EXPLORE</span>
                <span class="absolute top-0 left-1 text-cyan-400 opacity-70 z-0" aria-hidden="true">EXPLORE</span>
                <span class="absolute top-0 -left-1 text-pink-500 opacity-70 z-0" aria-hidden="true">EXPLORE</span>
            </h1>
            <p class="text-xl text-neutral-400 max-w-2xl mx-auto">
                Dive into a world of stunning visuals. Curated content updated every hour.
            </p>
        </div>

        <!-- Hero Cards Grid -->
        <?php if (!empty($heroImages)): ?>
        <div class="grid md:grid-cols-3 gap-6 mb-12">
            <?php foreach ($heroImages as $i => $img): ?>
            <?php $thumbSrc = $img['thumbnail_webp_path'] ?: $img['thumbnail_path']; ?>
            <a href="<?= $view->url('/image/' . $img['slug']) ?>"
               class="group relative aspect-[3/4] rounded-lg overflow-hidden <?= $i === 1 ? 'md:-mt-8' : '' ?>"
               title="<?= e($img['title']) ?>">
                <img data-src="<?= e('/uploads/' . $thumbSrc) ?>"
                     src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 400'%3E%3Crect fill='%230a0a1a' width='300' height='400'/%3E%3C/svg%3E"
                     alt="<?= e($img['alt_text'] ?: $img['title']) ?>"
                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a1a] via-transparent to-transparent"></div>
                <div class="absolute inset-0 border-2 border-transparent group-hover:border-cyan-400 transition-colors rounded-lg"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6">
                    <span class="inline-block px-2 py-1 bg-pink-500/80 text-white text-xs font-bold mb-2 rounded">#<?= $i + 1 ?> HOT</span>
                    <h2 class="text-white font-bold text-lg drop-shadow-lg"><?= e($img['title']) ?></h2>
                </div>
                <!-- Corner Accents -->
                <div class="absolute top-2 left-2 w-4 h-4 border-l-2 border-t-2 border-cyan-400 opacity-0 group-hover:opacity-100 transition"></div>
                <div class="absolute top-2 right-2 w-4 h-4 border-r-2 border-t-2 border-pink-500 opacity-0 group-hover:opacity-100 transition"></div>
                <div class="absolute bottom-2 left-2 w-4 h-4 border-l-2 border-b-2 border-pink-500 opacity-0 group-hover:opacity-100 transition"></div>
                <div class="absolute bottom-2 right-2 w-4 h-4 border-r-2 border-b-2 border-cyan-400 opacity-0 group-hover:opacity-100 transition"></div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="flex flex-wrap justify-center gap-4">
            <a href="<?= $view->url('/gallery') ?>"
               class="px-8 py-4 bg-gradient-to-r from-pink-500 to-purple-600 text-white font-bold rounded-lg hover:shadow-lg hover:shadow-pink-500/50 transition-all transform hover:scale-105">
                ENTER GALLERY
            </a>
            <a href="<?= $view->url('/trending') ?>"
               class="px-8 py-4 bg-transparent border-2 border-cyan-400 text-cyan-400 font-bold rounded-lg hover:bg-cyan-400 hover:text-black transition-all">
                VIEW TRENDING
            </a>
        </div>
    </div>
</section>

<!-- Categories - Neon Cards -->
<?php if (!empty($categories)): ?>
<section class="bg-[#0f0f24] py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-1 h-8 bg-gradient-to-b from-cyan-400 to-pink-500"></div>
            <h2 class="text-3xl font-bold text-white">Categories</h2>
        </div>

        <nav class="grid grid-cols-2 md:grid-cols-4 gap-4" aria-label="Browse Categories">
            <?php
            $neonColors = [
                ['from-cyan-400', 'to-blue-500', 'shadow-cyan-500/30'],
                ['from-pink-500', 'to-purple-600', 'shadow-pink-500/30'],
                ['from-yellow-400', 'to-orange-500', 'shadow-yellow-500/30'],
                ['from-green-400', 'to-emerald-600', 'shadow-green-500/30'],
            ];
            foreach ($categories as $i => $cat):
                $color = $neonColors[$i % count($neonColors)];
            ?>
            <a href="<?= $view->url('/category/' . $cat['slug']) ?>"
               class="group relative p-6 bg-[#1a1a2e] rounded-xl border border-neutral-800 hover:border-transparent overflow-hidden transition-all hover:shadow-xl <?= $color[2] ?>"
               title="<?= e($cat['name']) ?> - <?= number_format($cat['image_count'] ?? 0) ?> images">
                <div class="absolute inset-0 bg-gradient-to-br <?= $color[0] ?> <?= $color[1] ?> opacity-0 group-hover:opacity-10 transition-opacity"></div>
                <h3 class="text-white font-bold text-xl mb-1 group-hover:text-transparent group-hover:bg-gradient-to-r group-hover:<?= $color[0] ?> group-hover:<?= $color[1] ?> group-hover:bg-clip-text transition">
                    <?= e($cat['name']) ?>
                </h3>
                <span class="text-neutral-500 text-sm"><?= number_format($cat['image_count'] ?? 0) ?> items</span>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r <?= $color[0] ?> <?= $color[1] ?> transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
            </a>
            <?php endforeach; ?>
        </nav>
    </div>
</section>
<?php endif; ?>

<!-- Trending Section - Horizontal Scroll -->
<?php if (!empty($trendingImages)): ?>
<section class="bg-[#0a0a1a] py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="w-1 h-8 bg-gradient-to-b from-pink-500 to-cyan-400"></div>
                <h2 class="text-3xl font-bold text-white">Trending</h2>
                <span class="px-3 py-1 bg-pink-500/20 text-pink-400 text-xs font-bold rounded-full animate-pulse">LIVE</span>
            </div>
            <a href="<?= $view->url('/trending') ?>" class="text-cyan-400 hover:text-cyan-300 transition font-medium">
                View All &rarr;
            </a>
        </div>

        <div class="flex gap-6 overflow-x-auto pb-6 snap-x" style="-webkit-overflow-scrolling: touch;">
            <?php foreach ($trendingImages as $image): ?>
            <?php $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path']; ?>
            <a href="<?= $view->url('/image/' . $image['slug']) ?>"
               class="flex-shrink-0 w-64 group snap-start"
               title="<?= e($image['title']) ?>">
                <div class="relative aspect-[3/4] rounded-xl overflow-hidden bg-[#1a1a2e] mb-3">
                    <img data-src="<?= e('/uploads/' . $thumbSrc) ?>"
                         src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 400'%3E%3Crect fill='%231a1a2e' width='300' height='400'/%3E%3C/svg%3E"
                         alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a1a] via-transparent to-transparent opacity-60"></div>
                    <div class="absolute top-3 right-3 px-2 py-1 bg-black/70 backdrop-blur-sm text-cyan-400 text-xs font-mono rounded">
                        <?= number_format($image['view_count'] ?? 0) ?> views
                    </div>
                </div>
                <h3 class="text-white font-medium truncate group-hover:text-cyan-400 transition"><?= e($image['title']) ?></h3>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Recent Grid -->
<?php if (!empty($recentImages)): ?>
<section class="bg-[#0f0f24] py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="w-1 h-8 bg-gradient-to-b from-cyan-400 to-green-400"></div>
                <h2 class="text-3xl font-bold text-white">New Uploads</h2>
            </div>
            <a href="<?= $view->url('/gallery') ?>" class="text-cyan-400 hover:text-cyan-300 transition font-medium">
                See All &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach ($recentImages as $image): ?>
            <?php $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path']; ?>
            <a href="<?= $view->url('/image/' . $image['slug']) ?>"
               class="group relative aspect-square rounded-lg overflow-hidden bg-[#1a1a2e]"
               title="<?= e($image['title']) ?>">
                <img data-src="<?= e('/uploads/' . $thumbSrc) ?>"
                     src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 300'%3E%3Crect fill='%231a1a2e' width='300' height='300'/%3E%3C/svg%3E"
                     alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                <div class="absolute inset-0 border border-transparent group-hover:border-cyan-400/50 transition rounded-lg"></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Tags Cloud -->
<?php if (!empty($trendingTags)): ?>
<section class="bg-[#0a0a1a] py-16 border-t border-neutral-800">
    <div class="max-w-5xl mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold text-white mb-8">Trending Tags</h2>
        <div class="flex flex-wrap justify-center gap-3">
            <?php foreach ($trendingTags as $tag): ?>
            <a href="<?= $view->url('/tag/' . $tag['slug']) ?>"
               class="px-4 py-2 bg-[#1a1a2e] border border-neutral-700 text-neutral-300 rounded-lg hover:border-pink-500 hover:text-pink-400 hover:shadow-lg hover:shadow-pink-500/20 transition-all"
               title="<?= e($tag['name']) ?>">
                #<?= e($tag['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="relative bg-[#0f0f24] py-20 overflow-hidden">
    <div class="absolute inset-0 opacity-30">
        <div class="absolute inset-0" style="background-image: linear-gradient(#ff00ff22 1px, transparent 1px), linear-gradient(90deg, #00ffff22 1px, transparent 1px); background-size: 30px 30px;"></div>
    </div>
    <div class="absolute top-0 left-1/4 w-64 h-64 bg-pink-500/10 rounded-full blur-[80px]"></div>
    <div class="absolute bottom-0 right-1/4 w-64 h-64 bg-cyan-500/10 rounded-full blur-[80px]"></div>

    <div class="relative z-10 max-w-3xl mx-auto px-4 text-center">
        <h2 class="text-4xl md:text-5xl font-black text-white mb-6">
            Ready to <span class="text-transparent bg-gradient-to-r from-pink-500 to-cyan-400 bg-clip-text">Explore</span>?
        </h2>
        <p class="text-neutral-400 text-lg mb-8">
            Join our community. Save favorites, follow trends, discover exclusive content daily.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= $view->url('/register') ?>"
               class="px-10 py-4 bg-gradient-to-r from-pink-500 via-purple-500 to-cyan-500 text-white font-bold rounded-lg hover:shadow-xl hover:shadow-pink-500/30 transition-all transform hover:scale-105">
                GET STARTED
            </a>
            <a href="<?= $view->url('/gallery') ?>"
               class="px-10 py-4 border-2 border-neutral-600 text-neutral-300 font-bold rounded-lg hover:border-cyan-400 hover:text-cyan-400 transition-all">
                BROWSE GALLERY
            </a>
        </div>
    </div>
</section>
