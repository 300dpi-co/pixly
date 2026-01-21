<?php
/**
 * Home Page - Magazine Grid Theme
 * Editorial, elegant, Pinterest-style layout
 */
$featured = $featuredImages[0] ?? $trendingImages[0] ?? null;
$secondaryFeatured = array_slice($featuredImages ?: $trendingImages, 1, 2);
?>

<!-- Magazine Hero - Featured Story Style -->
<section class="bg-neutral-900">
    <div class="max-w-7xl mx-auto">
        <div class="grid lg:grid-cols-3 min-h-[70vh]">
            <!-- Main Featured -->
            <?php if ($featured): ?>
            <a href="<?= $view->url('/image/' . $featured['slug']) ?>" class="lg:col-span-2 relative group overflow-hidden">
                <img src="<?= e('/uploads/' . ($featured['storage_path'] ?? $featured['thumbnail_path'])) ?>"
                     alt="<?= e($featured['alt_text'] ?: $featured['title']) ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-8 lg:p-12">
                    <span class="inline-block px-3 py-1 bg-white text-neutral-900 text-xs font-medium tracking-wider uppercase mb-4">Featured</span>
                    <h1 class="text-3xl lg:text-5xl text-white font-serif leading-tight mb-4"><?= e($featured['title']) ?></h1>
                    <p class="text-neutral-300 text-lg font-serif italic">Discover this stunning visual masterpiece</p>
                </div>
            </a>
            <?php endif; ?>

            <!-- Secondary Featured -->
            <div class="flex flex-col">
                <?php foreach ($secondaryFeatured as $img): ?>
                <?php $thumbSrc = $img['thumbnail_webp_path'] ?: $img['thumbnail_path']; ?>
                <a href="<?= $view->url('/image/' . $img['slug']) ?>" class="relative flex-1 group overflow-hidden">
                    <img src="<?= e(uploads_url($thumbSrc)) ?>"
                         alt="<?= e($img['alt_text'] ?: $img['title']) ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-6">
                        <h2 class="text-xl text-white font-serif"><?= e($img['title']) ?></h2>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php if (count($secondaryFeatured) < 2): ?>
                <div class="flex-1 bg-neutral-800 flex items-center justify-center">
                    <a href="<?= $view->url('/gallery') ?>" class="text-neutral-400 hover:text-white transition font-serif italic">
                        Explore Gallery &rarr;
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Editorial Navigation Bar -->
<nav class="bg-white dark:bg-neutral-900 border-y border-neutral-200 dark:border-neutral-700 sticky top-16 z-40">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-12 overflow-x-auto">
            <div class="flex items-center gap-8 text-sm font-medium tracking-wide">
                <?php if (!empty($categories)): ?>
                <?php foreach (array_slice($categories, 0, 6) as $cat): ?>
                <a href="<?= $view->url('/category/' . $cat['slug']) ?>" class="text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition whitespace-nowrap uppercase text-xs tracking-widest">
                    <?= e($cat['name']) ?>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <form action="<?= $view->url('/search') ?>" method="GET" class="hidden md:flex items-center">
                <input type="text" name="q" placeholder="Search..." class="w-40 px-3 py-1 text-sm bg-transparent border-b border-neutral-300 dark:border-neutral-600 focus:outline-none focus:border-neutral-900 dark:focus:border-white">
            </form>
        </div>
    </div>
</nav>

<!-- Main Content with Sidebar -->
<div class="max-w-7xl mx-auto px-4 py-16">
    <div class="grid lg:grid-cols-12 gap-12">
        <!-- Main Content -->
        <main class="lg:col-span-8">
            <!-- Trending Section -->
            <?php if (!empty($trendingImages)): ?>
            <section class="mb-16">
                <div class="flex items-center gap-4 mb-8">
                    <h2 class="text-2xl font-serif dark:text-white">Trending This Week</h2>
                    <div class="flex-1 h-px bg-neutral-200 dark:bg-neutral-700"></div>
                </div>

                <!-- Masonry Grid -->
                <div class="columns-2 md:columns-3 gap-4 space-y-4">
                    <?php foreach ($trendingImages as $image): ?>
                    <?php $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path']; ?>
                    <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="block break-inside-avoid group mb-4">
                        <article class="bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="overflow-hidden">
                                <img data-src="<?= e(uploads_url($thumbSrc)) ?>"
                                     src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 400'%3E%3Crect fill='%23e5e7eb' width='300' height='400'/%3E%3C/svg%3E"
                                     alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                                     class="w-full group-hover:scale-105 transition-transform duration-500">
                            </div>
                            <div class="p-4">
                                <h3 class="font-serif text-neutral-900 dark:text-white leading-snug"><?= e($image['title']) ?></h3>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2 uppercase tracking-wide">
                                    <?= number_format($image['view_count'] ?? 0) ?> views
                                </p>
                            </div>
                        </article>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Recent Section -->
            <?php if (!empty($recentImages)): ?>
            <section>
                <div class="flex items-center gap-4 mb-8">
                    <h2 class="text-2xl font-serif dark:text-white">Latest Additions</h2>
                    <div class="flex-1 h-px bg-neutral-200 dark:bg-neutral-700"></div>
                    <a href="<?= $view->url('/gallery') ?>" class="text-sm text-neutral-500 hover:text-neutral-900 dark:hover:text-white uppercase tracking-wide">View All</a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <?php foreach ($recentImages as $image): ?>
                    <?php $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path']; ?>
                    <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="group">
                        <div class="aspect-square overflow-hidden bg-neutral-100 dark:bg-neutral-800">
                            <img data-src="<?= e(uploads_url($thumbSrc)) ?>"
                                 src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 300'%3E%3Crect fill='%23e5e7eb' width='300' height='300'/%3E%3C/svg%3E"
                                 alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                        <h3 class="mt-2 text-sm font-serif text-neutral-700 dark:text-neutral-300 truncate"><?= e($image['title']) ?></h3>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </main>

        <!-- Sidebar -->
        <aside class="lg:col-span-4">
            <!-- Search Widget -->
            <div class="mb-10 p-6 bg-neutral-100 dark:bg-neutral-800">
                <h3 class="text-lg font-serif mb-4 dark:text-white">Search</h3>
                <form action="<?= $view->url('/search') ?>" method="GET">
                    <input type="text" name="q" placeholder="Find images..."
                           class="w-full px-4 py-3 bg-white dark:bg-neutral-700 border border-neutral-200 dark:border-neutral-600 dark:text-white focus:outline-none focus:border-neutral-400">
                    <button type="submit" class="w-full mt-2 px-4 py-3 bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-sm uppercase tracking-widest hover:bg-neutral-700 dark:hover:bg-neutral-200 transition">
                        Search
                    </button>
                </form>
            </div>

            <!-- Categories Widget -->
            <?php if (!empty($categories)): ?>
            <div class="mb-10">
                <h3 class="text-lg font-serif mb-6 pb-2 border-b border-neutral-200 dark:border-neutral-700 dark:text-white">Categories</h3>
                <ul class="space-y-3">
                    <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="<?= $view->url('/category/' . $cat['slug']) ?>" class="flex items-center justify-between text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition">
                            <span><?= e($cat['name']) ?></span>
                            <span class="text-sm text-neutral-400"><?= number_format($cat['image_count'] ?? 0) ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Tags Widget -->
            <?php if (!empty($trendingTags)): ?>
            <div class="mb-10">
                <h3 class="text-lg font-serif mb-6 pb-2 border-b border-neutral-200 dark:border-neutral-700 dark:text-white">Popular Tags</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($trendingTags as $tag): ?>
                    <a href="<?= $view->url('/tag/' . $tag['slug']) ?>"
                       class="px-3 py-1 border border-neutral-300 dark:border-neutral-600 text-neutral-600 dark:text-neutral-400 text-sm hover:bg-neutral-900 hover:text-white hover:border-neutral-900 dark:hover:bg-white dark:hover:text-neutral-900 transition">
                        <?= e($tag['name']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Newsletter Widget -->
            <div class="p-6 bg-neutral-900 dark:bg-neutral-800 text-white">
                <h3 class="text-lg font-serif mb-2">Stay Updated</h3>
                <p class="text-neutral-400 text-sm mb-4">Get the latest images delivered to your inbox.</p>
                <form class="space-y-2">
                    <input type="email" placeholder="Your email"
                           class="w-full px-4 py-3 bg-neutral-800 dark:bg-neutral-700 border border-neutral-700 dark:border-neutral-600 text-white placeholder-neutral-500 focus:outline-none focus:border-neutral-500">
                    <button type="submit" class="w-full px-4 py-3 bg-white text-neutral-900 text-sm uppercase tracking-widest hover:bg-neutral-200 transition">
                        Subscribe
                    </button>
                </form>
            </div>
        </aside>
    </div>
</div>

<!-- Footer CTA -->
<section class="bg-neutral-100 dark:bg-neutral-800 py-16">
    <div class="max-w-3xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-serif mb-4 dark:text-white">Join Our Community</h2>
        <p class="text-neutral-600 dark:text-neutral-400 mb-8 font-serif italic">
            Create an account to save favorites, follow trends, and discover curated collections.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= $view->url('/register') ?>" class="px-8 py-3 bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-sm uppercase tracking-widest hover:bg-neutral-700 dark:hover:bg-neutral-200 transition">
                Create Account
            </a>
            <a href="<?= $view->url('/gallery') ?>" class="px-8 py-3 border border-neutral-900 dark:border-white text-neutral-900 dark:text-white text-sm uppercase tracking-widest hover:bg-neutral-900 hover:text-white dark:hover:bg-white dark:hover:text-neutral-900 transition">
                Browse Gallery
            </a>
        </div>
    </div>
</section>
