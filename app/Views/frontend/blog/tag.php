<div class="max-w-7xl mx-auto px-4 py-8">
    <header class="mb-8">
        <nav class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">
            <a href="/" class="hover:text-primary-600">Home</a>
            <span class="mx-2">/</span>
            <a href="/blog" class="hover:text-primary-600">Blog</a>
            <span class="mx-2">/</span>
            <span class="text-neutral-900 dark:text-white">Tag: <?= e($tag->name) ?></span>
        </nav>
        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">
            <span class="text-neutral-400">#</span><?= e($tag->name) ?>
        </h1>
        <p class="mt-2 text-sm text-neutral-500"><?= number_format($total) ?> articles</p>
    </header>

    <div class="flex flex-col lg:flex-row gap-8">
        <div class="flex-1">
            <?php if (empty($posts)): ?>
            <div class="text-center py-12">
                <p class="text-neutral-500 dark:text-neutral-400">No posts with this tag yet.</p>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($posts as $post): ?>
                <article class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm overflow-hidden hover:shadow-md transition group">
                    <a href="/blog/<?= e($post->slug) ?>" class="block">
                        <?php if ($post->featured_image): ?>
                        <div class="aspect-video overflow-hidden">
                            <img src="/uploads/<?= e($post->featured_image) ?>" alt="" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        </div>
                        <?php else: ?>
                        <div class="aspect-video bg-gradient-to-br from-neutral-100 to-neutral-200 dark:from-neutral-700 dark:to-neutral-600"></div>
                        <?php endif; ?>
                    </a>
                    <div class="p-5">
                        <?php if ($post->category_name): ?>
                        <a href="/blog/category/<?= e($post->category_slug) ?>" class="text-xs font-medium text-primary-600 hover:text-primary-700"><?= e($post->category_name) ?></a>
                        <?php endif; ?>
                        <a href="/blog/<?= e($post->slug) ?>">
                            <h2 class="mt-2 text-lg font-bold text-neutral-900 dark:text-white group-hover:text-primary-600 transition line-clamp-2"><?= e($post->title) ?></h2>
                        </a>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400 line-clamp-2"><?= e($post->excerpt) ?></p>
                        <div class="mt-3 text-xs text-neutral-500"><?= date('M j, Y', strtotime($post->published_at)) ?></div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav class="mt-8 flex justify-center">
                <div class="flex gap-2">
                    <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>" class="px-4 py-2 border rounded-lg hover:bg-neutral-100">Previous</a><?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?page=<?= $i ?>" class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-primary-600 text-white' : 'border hover:bg-neutral-100' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?><a href="?page=<?= $page + 1 ?>" class="px-4 py-2 border rounded-lg hover:bg-neutral-100">Next</a><?php endif; ?>
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
                    <?php foreach ($popularTags as $t): ?>
                    <a href="/blog/tag/<?= e($t->slug) ?>" class="px-3 py-1 text-sm rounded-full transition <?= $t->id === $tag->id ? 'bg-primary-600 text-white' : 'bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 hover:bg-primary-100 hover:text-primary-700' ?>"><?= e($t->name) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>
    </div>
</div>
