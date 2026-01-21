<?php if (!empty($schemaData)): ?>
<script type="application/ld+json"><?= json_encode($schemaData, JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>

<article class="max-w-4xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-neutral-500 dark:text-neutral-400">
            <li><a href="/" class="hover:text-primary-600">Home</a></li>
            <li><span>/</span></li>
            <li><a href="/blog" class="hover:text-primary-600">Blog</a></li>
            <?php if ($category): ?>
            <li><span>/</span></li>
            <li><a href="/blog/category/<?= e($category->slug) ?>" class="hover:text-primary-600"><?= e($category->name) ?></a></li>
            <?php endif; ?>
        </ol>
    </nav>

    <!-- Header -->
    <header class="mb-8">
        <?php if ($category): ?>
        <a href="/blog/category/<?= e($category->slug) ?>" class="inline-block px-3 py-1 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 text-sm font-medium rounded-full mb-4">
            <?= e($category->name) ?>
        </a>
        <?php endif; ?>

        <h1 class="text-3xl md:text-4xl font-bold text-neutral-900 dark:text-white leading-tight">
            <?= e($post->title) ?>
        </h1>

        <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-neutral-500 dark:text-neutral-400">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
                    <span class="text-primary-700 dark:text-primary-300 font-medium">
                        <?= strtoupper(substr($post->author_name ?? 'A', 0, 1)) ?>
                    </span>
                </div>
                <div>
                    <div class="font-medium text-neutral-900 dark:text-white"><?= e($post->author_name ?? 'Admin') ?></div>
                </div>
            </div>
            <span class="hidden sm:inline">·</span>
            <time datetime="<?= $post->published_at ?>"><?= date('F j, Y', strtotime($post->published_at)) ?></time>
            <span class="hidden sm:inline">·</span>
            <span><?= $post->read_time_minutes ?> min read</span>
            <span class="hidden sm:inline">·</span>
            <span><?= number_format($post->view_count) ?> views</span>
        </div>
    </header>

    <!-- Featured Image -->
    <?php if ($post->featured_image): ?>
    <figure class="mb-8 -mx-4 md:mx-0">
        <img src="/uploads/<?= e($post->featured_image) ?>"
             alt="<?= e($post->featured_image_alt ?: $post->title) ?>"
             class="w-full rounded-xl md:rounded-2xl object-cover max-h-[500px]">
    </figure>
    <?php endif; ?>

    <!-- Content -->
    <div class="prose prose-lg prose-neutral dark:prose-invert max-w-none
                prose-headings:font-bold prose-headings:text-neutral-900 dark:prose-headings:text-white
                prose-a:text-primary-600 dark:prose-a:text-primary-400 prose-a:no-underline hover:prose-a:underline
                prose-img:rounded-xl prose-img:mx-auto">
        <?= $post->content ?>
    </div>

    <!-- Tags -->
    <?php if (!empty($tags)): ?>
    <div class="mt-8 pt-6 border-t border-neutral-200 dark:border-neutral-700">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-sm text-neutral-500 dark:text-neutral-400">Tags:</span>
            <?php foreach ($tags as $tag): ?>
            <a href="/blog/tag/<?= e($tag->slug) ?>"
               class="px-3 py-1 text-sm bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 rounded-full hover:bg-primary-100 dark:hover:bg-primary-900 hover:text-primary-700 dark:hover:text-primary-300 transition">
                <?= e($tag->name) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Like, Save & Share -->
    <div class="mt-6 flex items-center justify-between flex-wrap gap-4">
        <!-- Like & Save Buttons -->
        <div class="flex items-center gap-2">
            <button onclick="toggleLike(<?= $post->id ?>)" id="like-btn"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg border transition <?= $isLiked ? 'bg-red-50 border-red-200 text-red-600 dark:bg-red-900/20 dark:border-red-800' : 'border-neutral-200 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:border-red-300 hover:text-red-500' ?>">
                <svg class="w-5 h-5" fill="<?= $isLiked ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <span id="like-count"><?= number_format($post->like_count ?? 0) ?></span>
            </button>
            <button onclick="toggleSave(<?= $post->id ?>)" id="save-btn"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg border transition <?= $isSaved ? 'bg-primary-50 border-primary-200 text-primary-600 dark:bg-primary-900/20 dark:border-primary-800' : 'border-neutral-200 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:border-primary-300 hover:text-primary-500' ?>">
                <svg class="w-5 h-5" fill="<?= $isSaved ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
                <span><?= $isSaved ? 'Saved' : 'Save' ?></span>
            </button>
        </div>

        <!-- Share Buttons -->
        <div class="flex items-center gap-2">
            <span class="text-sm text-neutral-500 dark:text-neutral-400">Share:</span>
            <a href="https://twitter.com/intent/tweet?url=<?= urlencode(config('app.url') . '/blog/' . $post->slug) ?>&text=<?= urlencode($post->title) ?>"
               target="_blank" rel="noopener"
               class="p-2 text-neutral-400 hover:text-[#1DA1F2] transition" title="Share on Twitter">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(config('app.url') . '/blog/' . $post->slug) ?>"
               target="_blank" rel="noopener"
               class="p-2 text-neutral-400 hover:text-[#4267B2] transition" title="Share on Facebook">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode(config('app.url') . '/blog/' . $post->slug) ?>&title=<?= urlencode($post->title) ?>"
               target="_blank" rel="noopener"
               class="p-2 text-neutral-400 hover:text-[#0A66C2] transition" title="Share on LinkedIn">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
            </a>
            <button onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied!')"
                    class="p-2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition" title="Copy link">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Author Bio -->
    <div class="mt-8 p-6 bg-neutral-50 dark:bg-neutral-800 rounded-xl">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-2xl text-primary-700 dark:text-primary-300 font-bold">
                    <?= strtoupper(substr($post->author_name ?? 'A', 0, 1)) ?>
                </span>
            </div>
            <div>
                <h3 class="font-bold text-neutral-900 dark:text-white">Written by <?= e($post->author_name ?? 'Admin') ?></h3>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                    Thank you for reading this article. We hope you found it helpful and informative.
                </p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="mt-8 grid grid-cols-2 gap-4">
        <?php if ($prevPost): ?>
        <a href="/blog/<?= e($prevPost['slug']) ?>" class="p-4 border border-neutral-200 dark:border-neutral-700 rounded-xl hover:border-primary-300 dark:hover:border-primary-700 transition group">
            <span class="text-xs text-neutral-400 uppercase">Previous</span>
            <div class="mt-1 font-medium text-neutral-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 line-clamp-2">
                <?= e($prevPost['title']) ?>
            </div>
        </a>
        <?php else: ?>
        <div></div>
        <?php endif; ?>

        <?php if ($nextPost): ?>
        <a href="/blog/<?= e($nextPost['slug']) ?>" class="p-4 border border-neutral-200 dark:border-neutral-700 rounded-xl hover:border-primary-300 dark:hover:border-primary-700 transition group text-right">
            <span class="text-xs text-neutral-400 uppercase">Next</span>
            <div class="mt-1 font-medium text-neutral-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 line-clamp-2">
                <?= e($nextPost['title']) ?>
            </div>
        </a>
        <?php endif; ?>
    </nav>
</article>

<!-- Related Posts -->
<?php if (!empty($relatedPosts)): ?>
<section class="bg-neutral-50 dark:bg-neutral-800/50 py-12 mt-12">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">Related Articles</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($relatedPosts as $related): ?>
            <a href="/blog/<?= e($related->slug) ?>" class="group">
                <div class="aspect-video rounded-lg overflow-hidden bg-neutral-200 dark:bg-neutral-700 mb-3">
                    <?php if ($related->featured_image): ?>
                    <img src="/uploads/<?= e($related->featured_image) ?>" alt=""
                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    <?php endif; ?>
                </div>
                <h3 class="font-semibold text-neutral-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition line-clamp-2">
                    <?= e($related->title) ?>
                </h3>
                <div class="mt-1 text-sm text-neutral-500">
                    <?= date('M j, Y', strtotime($related->published_at)) ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Comments -->
<?php if ($post->allow_comments): ?>
<section class="max-w-4xl mx-auto px-4 py-12" id="comments">
    <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">
        Comments (<?= $post->comment_count ?>)
    </h2>

    <!-- Comment Form -->
    <div class="bg-white dark:bg-neutral-800 rounded-xl p-6 shadow-sm mb-8">
        <h3 class="font-semibold text-neutral-900 dark:text-white mb-4">Leave a Comment</h3>
        <form method="POST" action="/blog/<?= e($post->slug) ?>/comment" class="space-y-4">
            <?= csrf_field() ?>

            <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name *</label>
                    <input type="text" name="guest_name" required
                           value="<?= e($_SESSION['old']['guest_name'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-neutral-200 dark:border-neutral-700 dark:bg-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Email *</label>
                    <input type="email" name="guest_email" required
                           value="<?= e($_SESSION['old']['guest_email'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-neutral-200 dark:border-neutral-700 dark:bg-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
            <?php endif; ?>

            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Comment *</label>
                <textarea name="content" rows="4" required
                          class="w-full px-3 py-2 border border-neutral-200 dark:border-neutral-700 dark:bg-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500"
                          placeholder="Share your thoughts..."><?= e($_SESSION['old']['content'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">
                Post Comment
            </button>
        </form>
    </div>

    <!-- Comments List -->
    <?php if (!empty($comments)): ?>
    <div class="space-y-6">
        <?php foreach ($comments as $comment): ?>
        <div class="flex gap-4" id="comment-<?= $comment['id'] ?>">
            <img src="https://www.gravatar.com/avatar/<?= md5(strtolower(trim($comment['user_email'] ?? $comment['guest_email'] ?? ''))) ?>?s=48&d=mp"
                 alt="" class="w-10 h-10 rounded-full flex-shrink-0">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-medium text-neutral-900 dark:text-white">
                        <?= e($comment['username'] ?? $comment['guest_name'] ?? 'Anonymous') ?>
                    </span>
                    <span class="text-xs text-neutral-400">
                        <?= date('M j, Y \a\t g:i A', strtotime($comment['created_at'])) ?>
                    </span>
                </div>
                <div class="text-neutral-700 dark:text-neutral-300">
                    <?= nl2br(e($comment['content'])) ?>
                </div>

                <!-- Replies -->
                <?php if (!empty($comment['replies'])): ?>
                <div class="mt-4 pl-4 border-l-2 border-neutral-200 dark:border-neutral-700 space-y-4">
                    <?php foreach ($comment['replies'] as $reply): ?>
                    <div class="flex gap-3" id="comment-<?= $reply['id'] ?>">
                        <img src="https://www.gravatar.com/avatar/<?= md5(strtolower(trim($reply['user_email'] ?? $reply['guest_email'] ?? ''))) ?>?s=32&d=mp"
                             alt="" class="w-8 h-8 rounded-full flex-shrink-0">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-medium text-neutral-900 dark:text-white text-sm">
                                    <?= e($reply['username'] ?? $reply['guest_name'] ?? 'Anonymous') ?>
                                </span>
                                <span class="text-xs text-neutral-400">
                                    <?= date('M j, Y', strtotime($reply['created_at'])) ?>
                                </span>
                            </div>
                            <div class="text-sm text-neutral-700 dark:text-neutral-300">
                                <?= nl2br(e($reply['content'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-8 text-neutral-500 dark:text-neutral-400">
        <p>No comments yet. Be the first to share your thoughts!</p>
    </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<!-- Like/Save JavaScript -->
<script>
const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
const csrfToken = '<?= csrf_token() ?>';

async function toggleLike(postId) {
    // Likes work for guests too - no login required
    const btn = document.getElementById('like-btn');
    const countEl = document.getElementById('like-count');
    const svg = btn.querySelector('svg');

    try {
        const response = await fetch('/api/like', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ type: 'blog_post', id: postId })
        });

        const data = await response.json();

        if (data.success) {
            countEl.textContent = data.count.toLocaleString();

            if (data.liked) {
                btn.className = 'flex items-center gap-2 px-4 py-2 rounded-lg border transition bg-red-50 border-red-200 text-red-600 dark:bg-red-900/20 dark:border-red-800';
                svg.setAttribute('fill', 'currentColor');
            } else {
                btn.className = 'flex items-center gap-2 px-4 py-2 rounded-lg border transition border-neutral-200 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:border-red-300 hover:text-red-500';
                svg.setAttribute('fill', 'none');
            }
        }
    } catch (error) {
        console.error('Error toggling like:', error);
    }
}

async function toggleSave(postId) {
    if (!isLoggedIn) {
        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
        return;
    }

    const btn = document.getElementById('save-btn');
    const svg = btn.querySelector('svg');
    const textEl = btn.querySelector('span');

    try {
        const response = await fetch('/api/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ type: 'blog_post', id: postId })
        });

        const data = await response.json();

        if (data.success) {
            if (data.saved) {
                btn.className = 'flex items-center gap-2 px-4 py-2 rounded-lg border transition bg-primary-50 border-primary-200 text-primary-600 dark:bg-primary-900/20 dark:border-primary-800';
                svg.setAttribute('fill', 'currentColor');
                textEl.textContent = 'Saved';
            } else {
                btn.className = 'flex items-center gap-2 px-4 py-2 rounded-lg border transition border-neutral-200 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:border-primary-300 hover:text-primary-500';
                svg.setAttribute('fill', 'none');
                textEl.textContent = 'Save';
            }
        }
    } catch (error) {
        console.error('Error toggling save:', error);
    }
}
</script>
