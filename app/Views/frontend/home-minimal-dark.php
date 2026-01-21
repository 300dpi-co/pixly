<?php
/**
 * Home Page - Minimal Dark Theme
 * Clean, content-focused, distraction-free design with subtle gradients
 * SEO Optimized: Fast loading, semantic HTML5, proper heading structure
 */
$mainFeature = $featuredImages[0] ?? $trendingImages[0] ?? null;
$sideFeatures = array_slice($featuredImages ?: $trendingImages, 1, 2);
?>

<!-- Minimal Hero - Content First -->
<section class="bg-neutral-950 pt-8 pb-16" itemscope itemtype="https://schema.org/WebPage">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Simple Header -->
        <div class="mb-12">
            <h1 class="text-4xl md:text-5xl font-medium text-white tracking-tight" itemprop="name">
                Featured
            </h1>
            <p class="text-neutral-500 mt-2" itemprop="description">Handpicked content, updated daily</p>
        </div>

        <!-- Featured Grid - Asymmetric -->
        <?php if ($mainFeature): ?>
        <div class="grid md:grid-cols-3 gap-4">
            <!-- Main Feature -->
            <a href="<?= $view->url('/image/' . $mainFeature['slug']) ?>"
               class="group md:col-span-2 relative aspect-[16/10] bg-neutral-900 overflow-hidden"
               title="<?= e($mainFeature['title']) ?>">
                <img data-src="<?= e('/uploads/' . ($mainFeature['storage_path'] ?? $mainFeature['thumbnail_path'])) ?>"
                     src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 500'%3E%3Crect fill='%23171717' width='800' height='500'/%3E%3C/svg%3E"
                     alt="<?= e($mainFeature['alt_text'] ?: $mainFeature['title']) ?>"
                     class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6">
                    <span class="text-neutral-400 text-sm">Featured</span>
                    <h2 class="text-white text-2xl font-medium mt-1"><?= e($mainFeature['title']) ?></h2>
                </div>
            </a>

            <!-- Side Features -->
            <div class="flex flex-col gap-4">
                <?php foreach ($sideFeatures as $img): ?>
                <?php $thumbSrc = $img['thumbnail_webp_path'] ?: $img['thumbnail_path']; ?>
                <a href="<?= $view->url('/image/' . $img['slug']) ?>"
                   class="group relative flex-1 bg-neutral-900 overflow-hidden"
                   title="<?= e($img['title']) ?>">
                    <img data-src="<?= e('/uploads/' . $thumbSrc) ?>"
                         src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Crect fill='%23171717' width='400' height='300'/%3E%3C/svg%3E"
                         alt="<?= e($img['alt_text'] ?: $img['title']) ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-4">
                        <h3 class="text-white font-medium truncate"><?= e($img['title']) ?></h3>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Quick Categories - Horizontal Scroll -->
<?php if (!empty($categories)): ?>
<section class="bg-neutral-900 py-8 border-y border-neutral-800">
    <div class="max-w-6xl mx-auto px-4">
        <nav class="flex gap-6 overflow-x-auto pb-2 scrollbar-hide" aria-label="Categories">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= $view->url('/category/' . $cat['slug']) ?>"
               class="flex-shrink-0 text-neutral-400 hover:text-white transition-colors whitespace-nowrap"
               title="<?= e($cat['name']) ?>">
                <?= e($cat['name']) ?>
                <span class="text-neutral-600 ml-1"><?= number_format($cat['image_count'] ?? 0) ?></span>
            </a>
            <?php endforeach; ?>
        </nav>
    </div>
</section>
<?php endif; ?>

<!-- Trending - Clean List -->
<?php if (!empty($trendingImages)): ?>
<section class="bg-neutral-950 py-16">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-baseline justify-between mb-8">
            <h2 class="text-2xl font-medium text-white">Trending</h2>
            <a href="<?= $view->url('/trending') ?>" class="text-neutral-500 hover:text-white transition-colors text-sm">
                View all
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            <?php foreach ($trendingImages as $image): ?>
            <?php $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path']; ?>
            <a href="<?= $view->url('/image/' . $image['slug']) ?>"
               class="group"
               title="<?= e($image['title']) ?>">
                <div class="relative aspect-[4/5] bg-neutral-900 overflow-hidden">
                    <img data-src="<?= e('/uploads/' . $thumbSrc) ?>"
                         src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 500'%3E%3Crect fill='%23171717' width='400' height='500'/%3E%3C/svg%3E"
                         alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
                <div class="mt-3">
                    <h3 class="text-white text-sm font-medium truncate group-hover:text-neutral-300 transition-colors"><?= e($image['title']) ?></h3>
                    <p class="text-neutral-600 text-xs mt-1"><?= number_format($image['view_count'] ?? 0) ?> views</p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Recent - Dense Grid -->
<?php if (!empty($recentImages)): ?>
<section class="bg-neutral-900 py-16">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-baseline justify-between mb-8">
            <h2 class="text-2xl font-medium text-white">Recent</h2>
            <a href="<?= $view->url('/gallery') ?>" class="text-neutral-500 hover:text-white transition-colors text-sm">
                Browse all
            </a>
        </div>

        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
            <?php foreach ($recentImages as $image): ?>
            <?php $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path']; ?>
            <a href="<?= $view->url('/image/' . $image['slug']) ?>"
               class="group relative aspect-square bg-neutral-800 overflow-hidden"
               title="<?= e($image['title']) ?>">
                <img data-src="<?= e('/uploads/' . $thumbSrc) ?>"
                     src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 300'%3E%3Crect fill='%23262626' width='300' height='300'/%3E%3C/svg%3E"
                     alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                     class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Tags - Inline -->
<?php if (!empty($trendingTags)): ?>
<section class="bg-neutral-950 py-12 border-t border-neutral-800">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center flex-wrap gap-x-1 gap-y-2">
            <span class="text-neutral-600 mr-2">Tags:</span>
            <?php foreach ($trendingTags as $i => $tag): ?>
            <a href="<?= $view->url('/tag/' . $tag['slug']) ?>"
               class="text-neutral-500 hover:text-white transition-colors"
               title="<?= e($tag['name']) ?>">
                <?= e($tag['name']) ?><?= $i < count($trendingTags) - 1 ? ',' : '' ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Simple CTA -->
<section class="bg-neutral-900 py-20 border-t border-neutral-800">
    <div class="max-w-2xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-medium text-white mb-4">Start Exploring</h2>
        <p class="text-neutral-500 mb-8">
            Create an account to save favorites and get personalized recommendations.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="<?= $view->url('/register') ?>"
               class="px-8 py-3 bg-white text-black font-medium hover:bg-neutral-200 transition-colors">
                Sign Up Free
            </a>
            <a href="<?= $view->url('/gallery') ?>"
               class="px-8 py-3 border border-neutral-700 text-white hover:bg-neutral-800 transition-colors">
                Browse Gallery
            </a>
        </div>
    </div>
</section>

<style>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.hover\:scale-102:hover { transform: scale(1.02); }
</style>
