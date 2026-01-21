<!-- Likes Header -->
<div class="bg-white dark:bg-neutral-900 border-b dark:border-neutral-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">My Likes</h1>
        <p class="text-neutral-600 dark:text-neutral-400 mt-2"><?= number_format($total) ?> liked images</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if (empty($images)): ?>
    <div class="text-center py-16">
        <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-neutral-900 dark:text-white">No likes yet</h3>
        <p class="mt-2 text-neutral-600 dark:text-neutral-400">Browse the gallery and like images you love.</p>
        <a href="<?= $view->url('/gallery') ?>" class="mt-4 inline-block bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg">
            Browse Gallery
        </a>
    </div>
    <?php else: ?>
    <!-- Image Grid -->
    <div class="gallery-grid grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        <?php foreach ($images as $image): ?>
        <?php $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path']; ?>
        <div class="group relative">
            <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="block">
                <div class="aspect-square bg-neutral-200 dark:bg-neutral-700 rounded-lg overflow-hidden">
                    <img data-src="<?= e('/uploads/' . $thumbSrc) ?>"
                         src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 300'%3E%3Crect fill='%23e5e7eb' width='300' height='300'/%3E%3C/svg%3E"
                         alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
                <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white truncate"><?= e($image['title']) ?></h3>
            </a>
            <button onclick="removeLike(<?= $image['id'] ?>, this)"
                    class="absolute top-2 right-2 w-8 h-8 bg-white/80 dark:bg-neutral-800/80 hover:bg-white dark:hover:bg-neutral-800 rounded-full flex items-center justify-center text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                </svg>
            </button>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Liked <?= date('M j, Y', strtotime($image['liked_at'])) ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-8 flex justify-center">
        <div class="flex items-center gap-2">
            <?php if ($page > 1): ?>
            <a href="<?= $view->url('/my-likes') ?>?page=<?= $page - 1 ?>" class="px-4 py-2 bg-white dark:bg-neutral-800 border dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white">Previous</a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
            <a href="<?= $view->url('/my-likes') ?>?page=<?= $i ?>"
               class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-primary-600 text-white' : 'bg-white dark:bg-neutral-800 border dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
            <a href="<?= $view->url('/my-likes') ?>?page=<?= $page + 1 ?>" class="px-4 py-2 bg-white dark:bg-neutral-800 border dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 dark:text-white">Next</a>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
async function removeLike(imageId, button) {
    if (!confirm('Remove like?')) return;

    try {
        const response = await fetch('/api/like', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?= csrf_token() ?>'
            },
            body: JSON.stringify({ type: 'image', id: imageId })
        });

        if (response.ok) {
            const card = button.closest('.group');
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            setTimeout(() => card.remove(), 300);
        }
    } catch (e) {
        console.error('Error removing like:', e);
    }
}
</script>
