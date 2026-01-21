<!-- Saved Posts Header -->
<div class="bg-white dark:bg-neutral-900 border-b dark:border-neutral-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Saved Posts</h1>
        <p class="text-neutral-600 dark:text-neutral-400 mt-2"><?= number_format($total) ?> saved blog posts</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if (empty($posts)): ?>
    <div class="text-center py-16">
        <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-neutral-900 dark:text-white">No saved posts yet</h3>
        <p class="mt-2 text-neutral-600 dark:text-neutral-400">Browse the blog and save posts to read later.</p>
        <a href="<?= $view->url('/blog') ?>" class="mt-4 inline-block bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg">
            Browse Blog
        </a>
    </div>
    <?php else: ?>
    <!-- Post Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($posts as $post): ?>
        <article class="group bg-white dark:bg-neutral-800 rounded-xl overflow-hidden border border-neutral-200 dark:border-neutral-700 hover:shadow-lg transition">
            <a href="<?= $view->url('/blog/' . $post['slug']) ?>" class="block">
                <?php if ($post['featured_image']): ?>
                <div class="aspect-video bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                    <img src="<?= uploads_url(e($post['featured_image'])) ?>"
                         alt="<?= e($post['title']) ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
                <?php else: ?>
                <div class="aspect-video bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                    <svg class="w-12 h-12 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                </div>
                <?php endif; ?>

                <div class="p-5">
                    <?php if ($post['category_name']): ?>
                    <span class="inline-block px-2 py-1 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 text-xs font-medium rounded-full mb-3">
                        <?= e($post['category_name']) ?>
                    </span>
                    <?php endif; ?>

                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2 line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">
                        <?= e($post['title']) ?>
                    </h2>

                    <?php if ($post['excerpt']): ?>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 line-clamp-2 mb-4">
                        <?= e($post['excerpt']) ?>
                    </p>
                    <?php endif; ?>

                    <div class="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400">
                        <div class="flex items-center gap-3">
                            <span><?= $post['read_time_minutes'] ?> min read</span>
                            <span><?= number_format($post['view_count']) ?> views</span>
                        </div>
                        <span>Saved <?= date('M j', strtotime($post['saved_at'])) ?></span>
                    </div>
                </div>
            </a>

            <div class="px-5 pb-4 pt-0 border-t border-neutral-100 dark:border-neutral-700">
                <button onclick="removeSave(<?= $post['id'] ?>, this)"
                        class="flex items-center gap-2 text-xs text-neutral-500 hover:text-red-500 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Remove from saved
                </button>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-8 flex justify-center">
        <div class="flex items-center gap-2">
            <?php if ($page > 1): ?>
            <a href="<?= $view->url('/my-saves') ?>?page=<?= $page - 1 ?>" class="px-4 py-2 bg-white dark:bg-neutral-800 border dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white">Previous</a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
            <a href="<?= $view->url('/my-saves') ?>?page=<?= $i ?>"
               class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-primary-600 text-white' : 'bg-white dark:bg-neutral-800 border dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
            <a href="<?= $view->url('/my-saves') ?>?page=<?= $page + 1 ?>" class="px-4 py-2 bg-white dark:bg-neutral-800 border dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white">Next</a>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
async function removeSave(postId, button) {
    if (!confirm('Remove from saved?')) return;

    try {
        const response = await fetch('/api/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?= csrf_token() ?>'
            },
            body: JSON.stringify({ type: 'blog_post', id: postId })
        });

        if (response.ok) {
            const card = button.closest('article');
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            setTimeout(() => card.remove(), 300);
        }
    } catch (e) {
        console.error('Error removing save:', e);
    }
}
</script>
