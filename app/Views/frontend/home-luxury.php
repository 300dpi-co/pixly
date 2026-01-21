<?php
/**
 * Home Page - Premium Luxury Theme
 * Elegant, exclusive feel with gold accents, refined typography, sophisticated layout
 * SEO Optimized: Schema.org markup, semantic HTML5, strong heading hierarchy
 */
$heroImage = $featuredImages[0] ?? $trendingImages[0] ?? null;
$premiumPicks = array_slice($featuredImages ?: $trendingImages, 0, 4);
?>

<!-- Luxury Hero - Elegant Showcase -->
<section class="relative min-h-screen bg-neutral-950 overflow-hidden" itemscope itemtype="https://schema.org/WebPage">
    <!-- Subtle Gold Gradient Overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-amber-900/20 via-transparent to-amber-900/10"></div>

    <!-- Decorative Elements -->
    <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-amber-500/50 to-transparent"></div>
    <div class="absolute bottom-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-amber-500/50 to-transparent"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 py-24 min-h-screen flex flex-col justify-center">
        <!-- Luxury Brand Header -->
        <div class="text-center mb-16">
            <div class="inline-flex items-center gap-3 mb-6">
                <span class="w-12 h-px bg-amber-500"></span>
                <span class="text-amber-500 text-sm tracking-[0.3em] uppercase font-light">Premium Collection</span>
                <span class="w-12 h-px bg-amber-500"></span>
            </div>
            <h1 class="text-5xl md:text-7xl font-extralight text-white mb-6 tracking-wide" itemprop="name">
                Curated <span class="font-normal italic text-amber-400">Excellence</span>
            </h1>
            <p class="text-lg text-neutral-400 max-w-xl mx-auto font-light leading-relaxed" itemprop="description">
                Discover our handpicked selection of extraordinary visuals. Every image tells a story of elegance.
            </p>
        </div>

        <!-- Premium Grid -->
        <?php if (!empty($premiumPicks)): ?>
        <div class="grid md:grid-cols-4 gap-4 mb-16">
            <?php foreach ($premiumPicks as $i => $img): ?>
            <?php $thumbSrc = $img['thumbnail_webp_path'] ?: $img['thumbnail_path']; ?>
            <a href="<?= $view->url('/image/' . $img['slug']) ?>"
               class="group relative <?= $i === 0 ? 'md:col-span-2 md:row-span-2' : '' ?>"
               title="<?= e($img['title']) ?>">
                <div class="relative <?= $i === 0 ? 'aspect-square' : 'aspect-[3/4]' ?> overflow-hidden">
                    <img data-src="<?= e('/uploads/' . $thumbSrc) ?>"
                         src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 500'%3E%3Crect fill='%23171717' width='400' height='500'/%3E%3C/svg%3E"
                         alt="<?= e($img['alt_text'] ?: $img['title']) ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-1000">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <!-- Gold Border on Hover -->
                    <div class="absolute inset-0 border border-amber-500/0 group-hover:border-amber-500/50 transition-colors duration-500"></div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 p-6">
                    <h2 class="text-white font-light text-lg tracking-wide group-hover:text-amber-400 transition-colors"><?= e($img['title']) ?></h2>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Elegant CTA -->
        <div class="flex justify-center gap-6">
            <a href="<?= $view->url('/gallery') ?>"
               class="group px-10 py-4 bg-amber-500 text-black font-medium tracking-wider uppercase text-sm hover:bg-amber-400 transition-colors">
                <span>View Collection</span>
            </a>
            <a href="<?= $view->url('/trending') ?>"
               class="px-10 py-4 border border-neutral-600 text-neutral-300 font-light tracking-wider uppercase text-sm hover:border-amber-500 hover:text-amber-500 transition-colors">
                Trending Now
            </a>
        </div>
    </div>
</section>

<!-- Categories - Refined Grid -->
<?php if (!empty($categories)): ?>
<section class="bg-neutral-900 py-20 border-t border-neutral-800">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <span class="text-amber-500 text-sm tracking-[0.2em] uppercase">Browse By</span>
            <h2 class="text-3xl font-extralight text-white mt-2 tracking-wide">Categories</h2>
        </div>

        <nav class="grid grid-cols-2 md:grid-cols-4 gap-px bg-neutral-800" aria-label="Browse Categories">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= $view->url('/category/' . $cat['slug']) ?>"
               class="group relative bg-neutral-900 p-8 hover:bg-neutral-800 transition-colors"
               title="<?= e($cat['name']) ?> - <?= number_format($cat['image_count'] ?? 0) ?> images">
                <h3 class="text-white font-light text-xl tracking-wide group-hover:text-amber-400 transition-colors mb-2">
                    <?= e($cat['name']) ?>
                </h3>
                <span class="text-neutral-500 text-sm"><?= number_format($cat['image_count'] ?? 0) ?> pieces</span>
                <div class="absolute bottom-0 left-0 w-0 h-px bg-amber-500 group-hover:w-full transition-all duration-500"></div>
            </a>
            <?php endforeach; ?>
        </nav>
    </div>
</section>
<?php endif; ?>

<!-- Trending - Editorial Style -->
<?php if (!empty($trendingImages)): ?>
<section class="bg-neutral-950 py-20">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-end justify-between mb-12">
            <div>
                <span class="text-amber-500 text-sm tracking-[0.2em] uppercase">What's Hot</span>
                <h2 class="text-3xl font-extralight text-white mt-2 tracking-wide">Trending Now</h2>
            </div>
            <a href="<?= $view->url('/trending') ?>" class="text-neutral-400 hover:text-amber-500 transition-colors text-sm tracking-wider uppercase">
                View All
            </a>
        </div>

        <div class="grid md:grid-cols-4 gap-6">
            <?php foreach ($trendingImages as $image): ?>
            <?php $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path']; ?>
            <a href="<?= $view->url('/image/' . $image['slug']) ?>"
               class="group"
               title="<?= e($image['title']) ?>">
                <div class="relative aspect-[3/4] overflow-hidden bg-neutral-900 mb-4">
                    <img data-src="<?= e('/uploads/' . $thumbSrc) ?>"
                         src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 400'%3E%3Crect fill='%23171717' width='300' height='400'/%3E%3C/svg%3E"
                         alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    <div class="absolute inset-0 border border-transparent group-hover:border-amber-500/30 transition-colors"></div>
                </div>
                <h3 class="text-white font-light tracking-wide truncate group-hover:text-amber-400 transition-colors"><?= e($image['title']) ?></h3>
                <p class="text-neutral-500 text-sm mt-1"><?= number_format($image['view_count'] ?? 0) ?> views</p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Recent Uploads - Refined Gallery -->
<?php if (!empty($recentImages)): ?>
<section class="bg-neutral-900 py-20 border-t border-neutral-800">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-end justify-between mb-12">
            <div>
                <span class="text-amber-500 text-sm tracking-[0.2em] uppercase">Latest Additions</span>
                <h2 class="text-3xl font-extralight text-white mt-2 tracking-wide">New Arrivals</h2>
            </div>
            <a href="<?= $view->url('/gallery') ?>" class="text-neutral-400 hover:text-amber-500 transition-colors text-sm tracking-wider uppercase">
                Full Gallery
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach ($recentImages as $image): ?>
            <?php $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path']; ?>
            <a href="<?= $view->url('/image/' . $image['slug']) ?>"
               class="group"
               title="<?= e($image['title']) ?>">
                <div class="relative aspect-square overflow-hidden bg-neutral-800">
                    <img data-src="<?= e('/uploads/' . $thumbSrc) ?>"
                         src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 300'%3E%3Crect fill='%23262626' width='300' height='300'/%3E%3C/svg%3E"
                         alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 border border-transparent group-hover:border-amber-500/40 transition-colors"></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Tags - Elegant Pills -->
<?php if (!empty($trendingTags)): ?>
<section class="bg-neutral-950 py-16 border-t border-neutral-800">
    <div class="max-w-5xl mx-auto px-4 text-center">
        <span class="text-amber-500 text-sm tracking-[0.2em] uppercase">Explore By</span>
        <h2 class="text-2xl font-extralight text-white mt-2 mb-8 tracking-wide">Popular Tags</h2>
        <div class="flex flex-wrap justify-center gap-3">
            <?php foreach ($trendingTags as $tag): ?>
            <a href="<?= $view->url('/tag/' . $tag['slug']) ?>"
               class="px-5 py-2 border border-neutral-700 text-neutral-400 text-sm tracking-wide hover:border-amber-500 hover:text-amber-500 transition-colors"
               title="<?= e($tag['name']) ?>">
                <?= e($tag['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Luxury CTA -->
<section class="relative bg-neutral-900 py-24 overflow-hidden">
    <!-- Decorative Lines -->
    <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-amber-500/30 to-transparent"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-amber-900/10 via-transparent to-transparent"></div>

    <div class="relative z-10 max-w-3xl mx-auto px-4 text-center">
        <div class="inline-flex items-center gap-3 mb-6">
            <span class="w-8 h-px bg-amber-500"></span>
            <span class="text-amber-500 text-sm tracking-[0.2em] uppercase">Join Us</span>
            <span class="w-8 h-px bg-amber-500"></span>
        </div>
        <h2 class="text-4xl md:text-5xl font-extralight text-white mb-6 tracking-wide">
            Experience <span class="italic text-amber-400">Premium</span>
        </h2>
        <p class="text-neutral-400 text-lg font-light mb-10 leading-relaxed">
            Create your account to unlock exclusive features. Save favorites, follow collections, and enjoy an elevated browsing experience.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= $view->url('/register') ?>"
               class="px-12 py-4 bg-amber-500 text-black font-medium tracking-wider uppercase text-sm hover:bg-amber-400 transition-colors">
                Create Account
            </a>
            <a href="<?= $view->url('/gallery') ?>"
               class="px-12 py-4 border border-neutral-600 text-neutral-300 font-light tracking-wider uppercase text-sm hover:border-amber-500 hover:text-amber-500 transition-colors">
                Browse Gallery
            </a>
        </div>
    </div>
</section>
