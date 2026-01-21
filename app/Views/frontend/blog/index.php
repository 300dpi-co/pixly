<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Featured Posts Hero -->
    <?php if (!empty($featuredPosts) && $page === 1): ?>
    <section class="mb-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <?php $mainFeatured = array_shift($featuredPosts); ?>
            <!-- Main Featured -->
            <a href="/blog/<?= e($mainFeatured->slug) ?>" class="group relative block h-80 lg:h-full rounded-2xl overflow-hidden">
                <?php if ($mainFeatured->featured_image): ?>
                <img src="<?= uploads_url(e($mainFeatured->featured_image)) ?>" alt="<?= e($mainFeatured->featured_image_alt ?: $mainFeatured->title) ?>"
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                <?php else: ?>
                <div class="absolute inset-0 bg-gradient-to-br from-primary-500 to-primary-700"></div>
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                    <?php if ($mainFeatured->category_name): ?>
                    <span class="px-3 py-1 bg-primary-600 text-xs font-medium rounded-full"><?= e($mainFeatured->category_name) ?></span>
                    <?php endif; ?>
                    <h2 class="mt-3 text-2xl lg:text-3xl font-bold group-hover:text-primary-300 transition"><?= e($mainFeatured->title) ?></h2>
                    <p class="mt-2 text-neutral-300 line-clamp-2"><?= e($mainFeatured->excerpt) ?></p>
                    <div class="mt-4 flex items-center gap-4 text-sm text-neutral-400">
                        <span><?= e($mainFeatured->author_name ?? 'Admin') ?></span>
                        <span><?= date('M j, Y', strtotime($mainFeatured->published_at)) ?></span>
                        <span><?= $mainFeatured->read_time_minutes ?> min read</span>
                    </div>
                </div>
            </a>

            <!-- Secondary Featured -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4">
                <?php foreach ($featuredPosts as $featured): ?>
                <a href="/blog/<?= e($featured->slug) ?>" class="group relative block h-36 lg:h-auto rounded-xl overflow-hidden">
                    <?php if ($featured->featured_image): ?>
                    <img src="<?= uploads_url(e($featured->featured_image)) ?>" alt=""
                         class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    <?php else: ?>
                    <div class="absolute inset-0 bg-gradient-to-br from-secondary-500 to-secondary-700"></div>
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                        <h3 class="font-semibold group-hover:text-primary-300 transition line-clamp-2"><?= e($featured->title) ?></h3>
                        <div class="mt-1 text-xs text-neutral-400">
                            <?= date('M j, Y', strtotime($featured->published_at)) ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Main Content -->
        <div class="flex-1">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Latest Posts</h1>
                <form action="/blog/search" method="GET" class="hidden sm:block">
                    <div class="relative">
                        <input type="text" name="q" placeholder="Search articles..."
                               class="pl-10 pr-4 py-2 border border-neutral-200 dark:border-neutral-700 dark:bg-neutral-800 rounded-lg focus:ring-2 focus:ring-primary-500 w-64">
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </form>
            </div>

            <?php if (empty($posts)): ?>
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <p class="text-neutral-500 dark:text-neutral-400">No posts yet. Check back soon!</p>
            </div>
            <?php else: ?>
            <!-- Posts Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($posts as $post): ?>
                <article class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm overflow-hidden hover:shadow-md transition group">
                    <a href="/blog/<?= e($post->slug) ?>" class="block">
                        <?php if ($post->featured_image): ?>
                        <div class="aspect-video overflow-hidden">
                            <img src="<?= uploads_url(e($post->featured_image)) ?>" alt="<?= e($post->featured_image_alt ?: $post->title) ?>"
                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        </div>
                        <?php else: ?>
                        <div class="aspect-video bg-gradient-to-br from-neutral-100 to-neutral-200 dark:from-neutral-700 dark:to-neutral-600 flex items-center justify-center">
                            <svg class="w-12 h-12 text-neutral-300 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                    </a>
                    <div class="p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <?php if ($post->category_name): ?>
                            <a href="/blog/category/<?= e($post->category_slug) ?>" class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700">
                                <?= e($post->category_name) ?>
                            </a>
                            <?php endif; ?>
                            <span class="text-xs text-neutral-400"><?= $post->read_time_minutes ?> min read</span>
                        </div>
                        <a href="/blog/<?= e($post->slug) ?>">
                            <h2 class="text-lg font-bold text-neutral-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition line-clamp-2">
                                <?= e($post->title) ?>
                            </h2>
                        </a>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400 line-clamp-2">
                            <?= e($post->excerpt) ?>
                        </p>
                        <div class="mt-4 flex items-center justify-between text-xs text-neutral-500">
                            <span><?= e($post->author_name ?? 'Admin') ?></span>
                            <span><?= date('M j, Y', strtotime($post->published_at)) ?></span>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-8 flex justify-center">
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="px-4 py-2 border border-neutral-200 dark:border-neutral-700 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800">
                        Previous
                    </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?page=<?= $i ?>"
                       class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-primary-600 text-white' : 'border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="px-4 py-2 border border-neutral-200 dark:border-neutral-700 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800">
                        Next
                    </a>
                    <?php endif; ?>
                </div>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="lg:w-80 space-y-6">
            <!-- Search (Mobile) -->
            <div class="sm:hidden bg-white dark:bg-neutral-800 rounded-xl p-4 shadow-sm">
                <form action="/blog/search" method="GET">
                    <input type="text" name="q" placeholder="Search articles..."
                           class="w-full px-4 py-2 border border-neutral-200 dark:border-neutral-700 dark:bg-neutral-700 rounded-lg">
                </form>
            </div>

            <!-- Categories -->
            <div class="bg-white dark:bg-neutral-800 rounded-xl p-5 shadow-sm">
                <h3 class="font-bold text-neutral-900 dark:text-white mb-4">Categories</h3>
                <ul class="space-y-2">
                    <?php foreach ($categories as $cat): ?>
                    <?php if ($cat->post_count > 0): ?>
                    <li>
                        <a href="/blog/category/<?= e($cat->slug) ?>" class="flex items-center justify-between py-1 text-neutral-600 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400">
                            <span><?= e($cat->name) ?></span>
                            <span class="text-xs bg-neutral-100 dark:bg-neutral-700 px-2 py-0.5 rounded-full"><?= $cat->post_count ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Popular Tags -->
            <div class="bg-white dark:bg-neutral-800 rounded-xl p-5 shadow-sm">
                <h3 class="font-bold text-neutral-900 dark:text-white mb-4">Popular Tags</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($popularTags as $tag): ?>
                    <a href="/blog/tag/<?= e($tag->slug) ?>"
                       class="px-3 py-1 text-sm bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 rounded-full hover:bg-primary-100 dark:hover:bg-primary-900 hover:text-primary-700 dark:hover:text-primary-300 transition">
                        <?= e($tag->name) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Newsletter CTA -->
            <div class="bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl p-5 text-white">
                <h3 class="font-bold mb-2">Stay Updated</h3>
                <p class="text-sm text-primary-100 mb-4">Subscribe to get our latest articles and updates.</p>
                <form class="space-y-2">
                    <input type="email" placeholder="Your email" class="w-full px-4 py-2 rounded-lg text-neutral-900 placeholder-neutral-400">
                    <button type="submit" class="w-full px-4 py-2 bg-white text-primary-600 font-medium rounded-lg hover:bg-primary-50">
                        Subscribe
                    </button>
                </form>
            </div>
        </aside>
    </div>
</div>
