<!-- Schema.org JSON-LD -->
<script type="application/ld+json"><?= json_encode($schemaData, JSON_UNESCAPED_SLASHES) ?></script>

<div class="max-w-5xl mx-auto px-4 py-8">
    <!-- Navigation -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <?php if ($prevImage): ?>
            <a href="<?= $view->url('/image/' . $prevImage['slug']) ?>" class="group flex items-center text-neutral-500 hover:text-amber-500 transition-colors" title="<?= e($prevImage['title']) ?>">
                <span class="w-10 h-10 rounded-lg bg-white/3 border border-white/5 group-hover:border-amber-700/30 flex items-center justify-center transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </span>
            </a>
            <?php else: ?>
            <span class="w-10 h-10 rounded-lg bg-white/3 border border-white/5 flex items-center justify-center opacity-30">
                <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </span>
            <?php endif; ?>
        </div>
        <div class="hidden md:flex items-center gap-4 text-xs text-neutral-600">
            <span><kbd class="px-1.5 py-0.5 bg-white/3 border border-white/5 rounded">←</kbd> <kbd class="px-1.5 py-0.5 bg-white/3 border border-white/5 rounded">→</kbd></span>
            <span><kbd class="px-1.5 py-0.5 bg-white/3 border border-white/5 rounded">L</kbd> like</span>
            <span><kbd class="px-1.5 py-0.5 bg-white/3 border border-white/5 rounded">S</kbd> save</span>
            <span><kbd class="px-1.5 py-0.5 bg-white/3 border border-white/5 rounded">E</kbd> embed</span>
        </div>
        <div>
            <?php if ($nextImage): ?>
            <a href="<?= $view->url('/image/' . $nextImage['slug']) ?>" class="group flex items-center text-neutral-500 hover:text-amber-500 transition-colors" title="<?= e($nextImage['title']) ?>">
                <span class="w-10 h-10 rounded-lg bg-white/3 border border-white/5 group-hover:border-amber-700/30 flex items-center justify-center transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </a>
            <?php else: ?>
            <span class="w-10 h-10 rounded-lg bg-white/3 border border-white/5 flex items-center justify-center opacity-30">
                <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Image Container -->
    <div class="flex justify-center mb-6">
        <div class="relative inline-block">
            <?php
            $isAnimated = !empty($image['is_animated']);
            $displayPath = $isAnimated ? $image['storage_path'] : ($image['webp_path'] ?: $image['storage_path']);
            ?>
            <div class="relative overflow-hidden rounded-lg bg-neutral-900 border border-white/5">
                <?php if ($isAnimated): ?>
                <img src="<?= e('/uploads/' . $image['storage_path']) ?>"
                     alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                     class="max-w-full h-auto max-h-[75vh]">
                <?php else: ?>
                <picture>
                    <?php if ($image['webp_path']): ?>
                    <source srcset="<?= e('/uploads/' . $image['webp_path']) ?>" type="image/webp">
                    <?php endif; ?>
                    <img src="<?= e('/uploads/' . $image['storage_path']) ?>"
                         alt="<?= e($image['alt_text'] ?: $image['title']) ?>"
                         class="max-w-full h-auto max-h-[75vh]">
                </picture>
                <?php endif; ?>
            </div>
            <?php if ($isAnimated): ?>
            <span class="absolute top-3 left-3 px-2 py-0.5 bg-black/70 text-white text-xs font-bold rounded">GIF</span>
            <?php endif; ?>
            <button id="flagBtn" class="absolute bottom-3 right-3 flex items-center gap-1.5 px-2.5 py-1.5 bg-black/50 hover:bg-red-900/80 text-white/80 hover:text-white text-xs rounded transition-all" title="Report">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                </svg>
                Report
            </button>
        </div>
    </div>

    <!-- Title & Info Card -->
    <div class="bg-neutral-900/80 border border-white/5 rounded-lg p-5 mb-6">
        <h1 class="text-xl font-medium text-white mb-4"><?= e($image['title']) ?></h1>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2 mb-5">
            <button id="likeBtn" data-image-id="<?= $image['id'] ?>"
                    class="group inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all <?= $isLiked ? 'bg-red-900/30 border border-red-700/30 text-red-400' : 'bg-white/3 border border-white/5 text-neutral-400 hover:bg-red-900/20 hover:border-red-700/30 hover:text-red-400' ?>">
                <svg class="w-4 h-4" fill="<?= $isLiked ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <span id="like-count"><?= number_format($image['like_count'] ?? 0) ?></span>
            </button>

            <button id="favoriteBtn" data-image-id="<?= $image['id'] ?>"
                    class="group inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all <?= $isFavorited ? 'bg-amber-900/30 border border-amber-700/30 text-amber-400' : 'bg-white/3 border border-white/5 text-neutral-400 hover:bg-amber-900/20 hover:border-amber-700/30 hover:text-amber-400' ?>">
                <svg class="w-4 h-4" fill="<?= $isFavorited ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
                <?= $isFavorited ? 'Saved' : 'Save' ?>
            </button>

            <?php if (!empty($isPexelsTheme) && !($isOwnImage ?? false) && setting('appreciate_system_enabled', '1') === '1'): ?>
            <button id="appreciateBtn" data-image-id="<?= $image['id'] ?>"
                    class="group inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all <?= !empty($isAppreciated) ? 'bg-emerald-900/30 border border-emerald-700/30 text-emerald-400' : 'bg-white/3 border border-white/5 text-neutral-400 hover:bg-emerald-900/20 hover:border-emerald-700/30 hover:text-emerald-400' ?>"
                    title="Show appreciation for this content">
                <svg class="w-4 h-4" fill="<?= !empty($isAppreciated) ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                </svg>
                <span id="appreciate-count"><?= number_format($image['appreciate_count'] ?? 0) ?></span>
            </button>
            <?php endif; ?>

            <button id="embedBtn"
                    class="group inline-flex items-center gap-2 px-4 py-2 bg-white/3 border border-white/5 text-neutral-400 hover:bg-white/5 hover:text-neutral-300 rounded-lg text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
                Embed
            </button>

            <a href="<?= e('/uploads/' . $image['storage_path']) ?>" download
               class="group inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-800 to-red-700 hover:from-red-700 hover:to-red-600 text-white rounded-lg text-sm font-medium transition-all border border-red-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download
            </a>
        </div>

        <!-- Stats -->
        <div class="flex items-center gap-6 text-sm text-neutral-500 pb-5 border-b border-white/5">
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <?= number_format($image['view_count']) ?>
            </span>
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <?= number_format($image['like_count'] ?? 0) ?>
            </span>
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
                <?= number_format($image['favorite_count']) ?>
            </span>
            <?php if (!empty($isPexelsTheme) && setting('appreciate_system_enabled', '1') === '1'): ?>
            <span class="flex items-center gap-1.5" title="Appreciations">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                </svg>
                <span id="appreciate-stat"><?= number_format($image['appreciate_count'] ?? 0) ?></span>
            </span>
            <?php endif; ?>
        </div>

        <!-- Details -->
        <div class="pt-5">
            <?php if ($image['description'] || $image['ai_description']): ?>
            <p class="text-neutral-400 mb-4 text-sm leading-relaxed"><?= e($image['description'] ?: $image['ai_description']) ?></p>
            <?php endif; ?>

            <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-neutral-500">
                <?php if (!empty($categories)): ?>
                <div>
                    <span class="text-neutral-600">Category:</span>
                    <?php foreach ($categories as $i => $cat): ?>
                    <a href="<?= $view->url('/category/' . $cat['slug']) ?>" class="text-amber-600/80 hover:text-amber-500 transition-colors"><?= e($cat['name']) ?></a><?= $i < count($categories) - 1 ? ',' : '' ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ($image['width'] && $image['height']): ?>
                <div><?= $image['width'] ?> x <?= $image['height'] ?></div>
                <?php endif; ?>

                <div><?= date('M j, Y', strtotime($image['created_at'])) ?></div>
            </div>

            <?php if (!empty($tags)): ?>
            <div class="flex flex-wrap gap-2 mt-4">
                <?php foreach ($tags as $tag): ?>
                <a href="<?= $view->url('/tag/' . $tag['slug']) ?>" class="px-2.5 py-1 bg-white/3 hover:bg-red-900/20 border border-white/5 hover:border-red-700/30 text-neutral-500 hover:text-red-300 rounded text-xs transition-all">
                    #<?= e($tag['name']) ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related Images -->
    <?php if (!empty($relatedImages)): ?>
    <div class="mb-8">
        <h2 class="text-sm font-medium text-neutral-400 mb-4 uppercase tracking-wider">Related</h2>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
            <?php foreach ($relatedImages as $related): ?>
            <a href="<?= $view->url('/image/' . $related['slug']) ?>" class="group">
                <div class="aspect-square bg-neutral-900 rounded-lg overflow-hidden border border-white/5 group-hover:border-amber-700/20 transition-all">
                    <?php $thumbSrc = $related['thumbnail_webp_path'] ?: $related['thumbnail_path']; ?>
                    <img data-src="<?= e('/uploads/' . $thumbSrc) ?>"
                         src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3C/svg%3E"
                         alt="<?= e($related['title']) ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                         loading="lazy">
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Comments Section -->
    <div class="bg-neutral-900/80 border border-white/5 rounded-lg p-5">
        <h2 class="text-sm font-medium text-neutral-400 mb-5 uppercase tracking-wider">Comments (<?= count($comments) ?>)</h2>

        <?php if (isset($_SESSION['user_id'])): ?>
        <form id="commentForm" class="mb-6">
            <input type="hidden" name="image_id" value="<?= $image['id'] ?>">
            <textarea name="content" rows="3" placeholder="Write a comment..."
                      class="w-full px-4 py-3 text-sm bg-black/50 border border-white/5 text-white placeholder-neutral-600 rounded-lg focus:border-amber-700/30 focus:ring-0 resize-none transition-all"
                      required></textarea>
            <div class="flex justify-end mt-3">
                <button type="submit" class="px-5 py-2 bg-gradient-to-r from-red-800 to-red-700 hover:from-red-700 hover:to-red-600 text-white text-sm font-medium rounded-lg transition-all border border-red-600/30">
                    Post Comment
                </button>
            </div>
        </form>
        <?php else: ?>
        <div class="bg-black/30 border border-white/5 rounded-lg p-4 mb-6 text-center">
            <p class="text-sm text-neutral-500">
                <a href="<?= $view->url('/login') ?>" class="text-amber-600/80 hover:text-amber-500 font-medium transition-colors">Login</a> to comment.
            </p>
        </div>
        <?php endif; ?>

        <?php if (empty($comments)): ?>
        <div class="text-center py-8">
            <p class="text-neutral-600 text-sm">No comments yet.</p>
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($comments as $comment): ?>
            <div class="flex gap-3 p-3 bg-black/30 rounded-lg">
                <div class="w-9 h-9 bg-gradient-to-br from-red-800 to-red-900 rounded-full flex items-center justify-center text-white text-sm font-medium flex-shrink-0">
                    <?= strtoupper(substr($comment['username'], 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-medium text-white text-sm"><?= e($comment['username']) ?></span>
                        <span class="text-xs text-neutral-600"><?= date('M j, Y', strtotime($comment['created_at'])) ?></span>
                    </div>
                    <p class="text-neutral-400 text-sm leading-relaxed"><?= nl2br(e($comment['content'])) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$siteUrl = rtrim(config('app.url'), '/');
$siteName = config('app.name');
$pageUrl = $siteUrl . '/image/' . $image['slug'];
$isAnimatedEmbed = !empty($image['is_animated']);
$fullImageUrl = $isAnimatedEmbed
    ? $siteUrl . '/uploads/' . $image['storage_path']
    : $siteUrl . '/uploads/' . ($image['webp_path'] ?: $image['storage_path']);
$thumbUrl = $siteUrl . '/uploads/' . ($image['thumbnail_path'] ?? $image['storage_path']);
$imageTitle = $image['title'];
$imageAlt = $image['alt_text'] ?: $image['title'];
?>

<!-- Embed Modal -->
<div id="embedModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeEmbedModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="bg-neutral-900 border border-white/10 rounded-xl p-5 m-4">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-medium text-white">Share & Embed</h3>
                <button onclick="closeEmbedModal()" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-white/10 text-neutral-400 hover:text-white flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-neutral-500 mb-1.5">Direct Link</label>
                    <div class="flex gap-2">
                        <input type="text" readonly value="<?= e($fullImageUrl) ?>" class="flex-1 px-3 py-2 text-xs bg-black/50 border border-white/5 rounded-lg text-neutral-300 font-mono">
                        <button onclick="copyEmbed(this)" class="px-3 py-2 bg-red-800 hover:bg-red-700 text-white text-xs rounded-lg font-medium transition-all">Copy</button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-neutral-500 mb-1.5">Page Link</label>
                    <div class="flex gap-2">
                        <input type="text" readonly value="<?= e($pageUrl) ?>" class="flex-1 px-3 py-2 text-xs bg-black/50 border border-white/5 rounded-lg text-neutral-300 font-mono">
                        <button onclick="copyEmbed(this)" class="px-3 py-2 bg-red-800 hover:bg-red-700 text-white text-xs rounded-lg font-medium transition-all">Copy</button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-neutral-500 mb-1.5">BBCode</label>
                    <div class="flex gap-2">
                        <input type="text" readonly value="[url=<?= e($pageUrl) ?>][img]<?= e($fullImageUrl) ?>[/img][/url]" class="flex-1 px-3 py-2 text-xs bg-black/50 border border-white/5 rounded-lg text-neutral-300 font-mono">
                        <button onclick="copyEmbed(this)" class="px-3 py-2 bg-red-800 hover:bg-red-700 text-white text-xs rounded-lg font-medium transition-all">Copy</button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-neutral-500 mb-1.5">BBCode (Thumbnail)</label>
                    <div class="flex gap-2">
                        <input type="text" readonly value="[url=<?= e($pageUrl) ?>][img]<?= e($thumbUrl) ?>[/img][/url]" class="flex-1 px-3 py-2 text-xs bg-black/50 border border-white/5 rounded-lg text-neutral-300 font-mono">
                        <button onclick="copyEmbed(this)" class="px-3 py-2 bg-red-800 hover:bg-red-700 text-white text-xs rounded-lg font-medium transition-all">Copy</button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-neutral-500 mb-1.5">HTML</label>
                    <div class="flex gap-2">
                        <input type="text" readonly value="<a href=&quot;<?= e($pageUrl) ?>&quot; title=&quot;<?= e($imageTitle) ?>&quot;><img src=&quot;<?= e($fullImageUrl) ?>&quot; alt=&quot;<?= e($imageAlt) ?>&quot;></a>" class="flex-1 px-3 py-2 text-xs bg-black/50 border border-white/5 rounded-lg text-neutral-300 font-mono">
                        <button onclick="copyEmbed(this)" class="px-3 py-2 bg-red-800 hover:bg-red-700 text-white text-xs rounded-lg font-medium transition-all">Copy</button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-neutral-500 mb-1.5">Markdown</label>
                    <div class="flex gap-2">
                        <input type="text" readonly value="[![<?= e($imageAlt) ?>](<?= e($fullImageUrl) ?>)](<?= e($pageUrl) ?>)" class="flex-1 px-3 py-2 text-xs bg-black/50 border border-white/5 rounded-lg text-neutral-300 font-mono">
                        <button onclick="copyEmbed(this)" class="px-3 py-2 bg-red-800 hover:bg-red-700 text-white text-xs rounded-lg font-medium transition-all">Copy</button>
                    </div>
                </div>
            </div>

            <p class="text-xs text-neutral-600 mt-4 text-center">
                Links back to <?= e($siteName) ?>
            </p>
        </div>
    </div>
</div>

<!-- Report Modal -->
<div id="flagModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeFlagModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm">
        <div class="bg-neutral-900 border border-white/10 rounded-xl p-5 m-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-medium text-white">Report Image</h3>
                <button onclick="closeFlagModal()" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-white/10 text-neutral-400 hover:text-white flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="flagForm">
                <input type="hidden" name="image_id" value="<?= $image['id'] ?>">
                <select name="reason" required class="w-full px-3 py-2.5 text-sm bg-black/50 border border-white/5 text-white rounded-lg mb-3">
                    <option value="">Select reason...</option>
                    <option value="copyright">Copyright Infringement</option>
                    <option value="dmca">DMCA Takedown</option>
                    <option value="inappropriate">Inappropriate</option>
                    <option value="spam">Spam</option>
                    <option value="other">Other</option>
                </select>
                <textarea name="details" rows="2" placeholder="Details (optional)"
                          class="w-full px-3 py-2.5 text-sm bg-black/50 border border-white/5 text-white placeholder-neutral-600 rounded-lg mb-3 resize-none"></textarea>
                <input type="email" name="email" placeholder="Your email (optional)"
                       class="w-full px-3 py-2.5 text-sm bg-black/50 border border-white/5 text-white placeholder-neutral-600 rounded-lg mb-4">
                <div class="flex gap-2">
                    <button type="button" onclick="closeFlagModal()" class="flex-1 px-4 py-2.5 text-sm bg-white/5 border border-white/5 text-neutral-300 rounded-lg hover:bg-white/10 transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 text-sm bg-red-700 hover:bg-red-600 text-white rounded-lg font-medium transition-colors">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('keydown', function(e) {
    if (e.target.tagName === 'TEXTAREA' || e.target.tagName === 'INPUT') return;
    <?php if ($prevImage): ?>if (e.key === 'ArrowLeft') window.location.href = '<?= $view->url('/image/' . $prevImage['slug']) ?>';<?php endif; ?>
    <?php if ($nextImage): ?>if (e.key === 'ArrowRight') window.location.href = '<?= $view->url('/image/' . $nextImage['slug']) ?>';<?php endif; ?>
    if (e.key === 'l' || e.key === 'L') document.getElementById('likeBtn').click();
    if (e.key === 's' || e.key === 'S') document.getElementById('favoriteBtn').click();
    if (e.key === 'e' || e.key === 'E') openEmbedModal();
    if (e.key === 'g' || e.key === 'G') window.location.href = '<?= $view->url('/gallery') ?>';
    if (e.key === 'r' || e.key === 'R') window.location.href = '<?= $view->url('/random') ?>';
    if (e.key === 'Escape') { closeEmbedModal(); closeFlagModal(); }
});

function openFlagModal() { document.getElementById('flagModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
function closeFlagModal() { document.getElementById('flagModal').classList.add('hidden'); document.body.style.overflow = ''; }
document.getElementById('flagBtn').addEventListener('click', openFlagModal);

function openEmbedModal() { document.getElementById('embedModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
function closeEmbedModal() { document.getElementById('embedModal').classList.add('hidden'); document.body.style.overflow = ''; }
document.getElementById('embedBtn').addEventListener('click', openEmbedModal);

function copyEmbed(btn) {
    const input = btn.parentElement.querySelector('input');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
        const originalText = btn.textContent;
        btn.textContent = 'Copied!';
        btn.classList.add('bg-green-700');
        btn.classList.remove('bg-red-800');
        setTimeout(() => {
            btn.textContent = originalText;
            btn.classList.remove('bg-green-700');
            btn.classList.add('bg-red-800');
        }, 1500);
    });
}

document.getElementById('flagForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd = new FormData(this), btn = this.querySelector('button[type="submit"]');
    btn.disabled = true; btn.textContent = 'Sending...';
    try {
        const res = await fetch('/api/reports', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?= csrf_token() ?>' },
            body: JSON.stringify({ image_id: fd.get('image_id'), reason: fd.get('reason'), details: fd.get('details'), email: fd.get('email') }) });
        if (res.ok) { closeFlagModal(); alert('Report submitted. Thank you.'); this.reset(); }
        else { const d = await res.json(); alert(d.error || 'Failed to submit.'); }
    } catch (err) { alert('Error submitting report.'); }
    finally { btn.disabled = false; btn.textContent = 'Submit'; }
});

document.getElementById('likeBtn').addEventListener('click', async function() {
    const id = this.dataset.imageId, liked = this.classList.contains('bg-red-900/30');
    try {
        const res = await fetch('/api/like', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?= csrf_token() ?>' }, body: JSON.stringify({ type: 'image', id: parseInt(id) }) });
        const data = await res.json();
        if (data.success) {
            document.getElementById('like-count').textContent = data.count.toLocaleString();
            if (data.liked) {
                this.classList.remove('bg-white/3', 'border-white/5', 'text-neutral-400');
                this.classList.add('bg-red-900/30', 'border-red-700/30', 'text-red-400');
            } else {
                this.classList.remove('bg-red-900/30', 'border-red-700/30', 'text-red-400');
                this.classList.add('bg-white/3', 'border-white/5', 'text-neutral-400');
            }
            this.querySelector('svg').setAttribute('fill', data.liked ? 'currentColor' : 'none');
        }
    } catch (e) { console.error(e); }
});

document.getElementById('favoriteBtn').addEventListener('click', async function() {
    <?php if (!isset($_SESSION['user_id'])): ?>window.location.href = '<?= $view->url('/login') ?>'; return;<?php endif; ?>
    const id = this.dataset.imageId, saved = this.classList.contains('bg-amber-900/30');
    try {
        const res = await fetch('/api/favorites/' + id, { method: saved ? 'DELETE' : 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?= csrf_token() ?>' } });
        if (res.ok) {
            if (saved) {
                this.classList.remove('bg-amber-900/30', 'border-amber-700/30', 'text-amber-400');
                this.classList.add('bg-white/3', 'border-white/5', 'text-neutral-400');
            } else {
                this.classList.remove('bg-white/3', 'border-white/5', 'text-neutral-400');
                this.classList.add('bg-amber-900/30', 'border-amber-700/30', 'text-amber-400');
            }
            this.querySelector('svg').setAttribute('fill', saved ? 'none' : 'currentColor');
            this.innerHTML = this.innerHTML.replace(saved ? 'Saved' : 'Save', saved ? 'Save' : 'Saved');
        }
    } catch (e) { console.error(e); }
});

// Appreciate button (Pexels theme only)
const appreciateBtn = document.getElementById('appreciateBtn');
if (appreciateBtn) {
    appreciateBtn.addEventListener('click', async function() {
        <?php if (!isset($_SESSION['user_id'])): ?>
        window.location.href = '<?= $view->url('/login') ?>'; return;
        <?php endif; ?>
        const id = this.dataset.imageId;
        const appreciated = this.classList.contains('bg-emerald-900/30');
        try {
            const res = await fetch('/api/appreciate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?= csrf_token() ?>' },
                body: JSON.stringify({ image_id: parseInt(id) })
            });
            const data = await res.json();
            if (data.success) {
                // Update count displays
                document.getElementById('appreciate-count').textContent = data.count.toLocaleString();
                const statEl = document.getElementById('appreciate-stat');
                if (statEl) statEl.textContent = data.count.toLocaleString();
                // Toggle button style
                if (data.appreciated) {
                    this.classList.remove('bg-white/3', 'border-white/5', 'text-neutral-400');
                    this.classList.add('bg-emerald-900/30', 'border-emerald-700/30', 'text-emerald-400');
                } else {
                    this.classList.remove('bg-emerald-900/30', 'border-emerald-700/30', 'text-emerald-400');
                    this.classList.add('bg-white/3', 'border-white/5', 'text-neutral-400');
                }
                this.querySelector('svg').setAttribute('fill', data.appreciated ? 'currentColor' : 'none');
            } else if (data.error) {
                alert(data.error);
            }
        } catch (e) { console.error(e); }
    });
}

const cf = document.getElementById('commentForm');
if (cf) cf.addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd = new FormData(this), btn = this.querySelector('button[type="submit"]');
    btn.disabled = true; btn.textContent = 'Posting...';
    try {
        const res = await fetch('/api/comments', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?= csrf_token() ?>' },
            body: JSON.stringify({ image_id: fd.get('image_id'), content: fd.get('content') }) });
        if (res.ok) { this.querySelector('textarea').value = ''; alert('Comment submitted for moderation.'); }
        else { const d = await res.json(); alert(d.error || 'Failed.'); }
    } catch (err) { alert('Error posting comment.'); }
    finally { btn.disabled = false; btn.textContent = 'Post Comment'; }
});
</script>
