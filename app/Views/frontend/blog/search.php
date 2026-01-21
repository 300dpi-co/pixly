<div class="max-w-7xl mx-auto px-4 py-8">
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Search Results</h1>
        <p class="mt-2 text-neutral-600 dark:text-neutral-400">
            <?= number_format($total) ?> results for "<span class="font-medium"><?= e($query) ?></span>"
        </p>
    </header>

    <!-- Search Form -->
    <form action="/blog/search" method="GET" class="mb-8">
        <div class="relative max-w-xl">
            <input type="text" name="q" value="<?= e($query) ?>"
                   class="w-full pl-12 pr-4 py-3 border border-neutral-200 dark:border-neutral-700 dark:bg-neutral-800 rounded-xl focus:ring-2 focus:ring-primary-500"
                   placeholder="Search articles...">
            <svg class="absolute left-4 top-3.5 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </form>

    <div class="flex flex-col lg:flex-row gap-8">
        <div class="flex-1">
            <?php if (empty($posts)): ?>
            <div class="text-center py-12 bg-neutral-50 dark:bg-neutral-800 rounded-xl">
                <svg class="w-16 h-16 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p class="text-neutral-500 dark:text-neutral-400">No articles found for your search.</p>
                <p class="mt-2 text-sm text-neutral-400">Try different keywords or browse our categories.</p>
            </div>
            <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($posts as $post): ?>
                <article class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm overflow-hidden hover:shadow-md transition group flex flex-col sm:flex-row">
                    <a href="/blog/<?= e($post->slug) ?>" class="sm:w-48 flex-shrink-0">
                        <?php if ($post->featured_image): ?>
                        <div class="aspect-video sm:h-full overflow-hidden">
                            <img src="/uploads/<?= e($post->featured_image) ?>" alt="" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        </div>
                        <?php else: ?>
                        <div class="aspect-video sm:h-full bg-gradient-to-br from-neutral-100 to-neutral-200 dark:from-neutral-700 dark:to-neutral-600"></div>
                        <?php endif; ?>
                    </a>
                    <div class="p-5 flex-1">
                        <div class="flex items-center gap-3 mb-2 text-xs">
                            <?php if ($post->category_name): ?>
                            <a href="/blog/category/<?= e($post->category_slug) ?>" class="font-medium text-primary-600 hover:text-primary-700"><?= e($post->category_name) ?></a>
                            <?php endif; ?>
                            <span class="text-neutral-400"><?= date('M j, Y', strtotime($post->published_at)) ?></span>
                        </div>
                        <a href="/blog/<?= e($post->slug) ?>">
                            <h2 class="text-lg font-bold text-neutral-900 dark:text-white group-hover:text-primary-600 transition"><?= e($post->title) ?></h2>
                        </a>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400 line-clamp-2"><?= e($post->excerpt) ?></p>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav class="mt-8 flex justify-center">
                <div class="flex gap-2">
                    <?php if ($page > 1): ?><a href="?q=<?= urlencode($query) ?>&page=<?= $page - 1 ?>" class="px-4 py-2 border rounded-lg hover:bg-neutral-100">Previous</a><?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?q=<?= urlencode($query) ?>&page=<?= $i ?>" class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-primary-600 text-white' : 'border hover:bg-neutral-100' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?><a href="?q=<?= urlencode($query) ?>&page=<?= $page + 1 ?>" class="px-4 py-2 border rounded-lg hover:bg-neutral-100">Next</a><?php endif; ?>
                </div>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <aside class="lg:w-80 space-y-6">
            <div class="bg-white dark:bg-neutral-800 rounded-xl p-5 shadow-sm">
                <h3 class="font-bold text-neutral-900 dark:text-white mb-4">Categories</h3>
                <ul class="space-y-2">
                    <?php foreach ($categories as $cat): ?>
                    <?php if ($cat->post_count > 0): ?>
                    <li><a href="/blog/category/<?= e($cat->slug) ?>" class="flex items-center justify-between py-1 text-neutral-600 dark:text-neutral-400 hover:text-primary-600"><span><?= e($cat->name) ?></span><span class="text-xs bg-neutral-100 dark:bg-neutral-700 px-2 py-0.5 rounded-full"><?= $cat->post_count ?></span></a></li>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="bg-white dark:bg-neutral-800 rounded-xl p-5 shadow-sm">
                <h3 class="font-bold text-neutral-900 dark:text-white mb-4">Popular Tags</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($popularTags as $tag): ?>
                    <a href="/blog/tag/<?= e($tag->slug) ?>" class="px-3 py-1 text-sm bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 rounded-full hover:bg-primary-100 hover:text-primary-700 transition"><?= e($tag->name) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>
    </div>
</div>
