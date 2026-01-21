<?php
/**
 * Pexels-Style Light Favorites Page
 * Clean, bright design matching pexels.com
 */
?>

<!-- Favorites Header -->
<div class="bg-white border-b border-neutral-200">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-semibold text-neutral-900">My Favorites</h1>
        <p class="text-neutral-500 mt-2"><?= number_format($total) ?> saved photos</p>
    </div>
</div>

<div class="bg-white min-h-screen">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <?php if (empty($images)): ?>
        <div class="text-center py-16">
            <div class="w-16 h-16 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-neutral-900">No favorites yet</h3>
            <p class="mt-2 text-neutral-500">Browse the gallery and save photos you love.</p>
            <a href="<?= $view->url('/gallery') ?>" class="mt-4 inline-block bg-teal-500 hover:bg-teal-600 text-white px-6 py-2.5 rounded-lg font-medium transition-colors">
                Browse Gallery
            </a>
        </div>
        <?php else: ?>
        <!-- Masonry Grid -->
        <div class="columns-2 md:columns-3 lg:columns-4 xl:columns-5 gap-4 space-y-4">
            <?php foreach ($images as $image): ?>
            <?php $thumbSrc = $image['thumbnail_webp_path'] ?: $image['thumbnail_path']; ?>
            <div class="break-inside-avoid group relative">
                <a href="<?= $view->url('/image/' . $image['slug']) ?>" class="block relative rounded-lg overflow-hidden bg-neutral-100">
                    <img src="<?= e(uploads_url($thumbSrc)) ?>"
                         alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                         class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-300"
                         loading="lazy">

                    <!-- Hover Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <div class="absolute bottom-3 left-3 right-12">
                            <span class="text-white text-sm font-medium truncate block"><?= e($image['title']) ?></span>
                            <span class="text-white/70 text-xs">Saved <?= date('M j, Y', strtotime($image['favorited_at'])) ?></span>
                        </div>
                    </div>
                </a>

                <!-- Remove Button -->
                <button onclick="removeFavorite(<?= $image['id'] ?>, this)"
                        class="absolute top-3 right-3 w-8 h-8 bg-white/90 hover:bg-red-50 rounded-full flex items-center justify-center text-neutral-500 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all border border-neutral-200 hover:border-red-200"
                        title="Remove from favorites">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-12 flex justify-center">
            <div class="inline-flex items-center gap-1 bg-neutral-100 rounded-lg p-1">
                <?php if ($page > 1): ?>
                <a href="<?= $view->url('/favorites') ?>?page=<?= $page - 1 ?>"
                   class="px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-white rounded-md transition-colors">
                    Previous
                </a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                for ($i = $start; $i <= $end; $i++):
                ?>
                <a href="<?= $view->url('/favorites') ?>?page=<?= $i ?>"
                   class="w-10 h-10 flex items-center justify-center text-sm font-medium rounded-md transition-colors <?= $i === $page ? 'bg-neutral-900 text-white' : 'text-neutral-600 hover:bg-white' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                <a href="<?= $view->url('/favorites') ?>?page=<?= $page + 1 ?>"
                   class="px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-white rounded-md transition-colors">
                    Next
                </a>
                <?php endif; ?>
            </div>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
async function removeFavorite(imageId, button) {
    if (!confirm('Remove from favorites?')) return;

    try {
        const response = await fetch('/api/favorites/' + imageId, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?= csrf_token() ?>'
            }
        });

        if (response.ok) {
            const card = button.closest('.group');
            card.style.opacity = '0';
            card.style.transform = 'scale(0.95)';
            setTimeout(() => card.remove(), 300);
        }
    } catch (e) {
        console.error('Error removing favorite:', e);
    }
}
</script>
