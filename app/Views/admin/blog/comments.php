<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="/admin/blog" class="p-2 text-neutral-400 hover:text-neutral-600 rounded-lg hover:bg-neutral-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-neutral-900">Blog Comments</h1>
                <p class="text-neutral-500 text-sm">Moderate and manage comments</p>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex gap-2 border-b">
        <a href="?status=pending"
           class="px-4 py-2 border-b-2 <?= $status === 'pending' ? 'border-primary-600 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700' ?>">
            Pending
            <?php if ($counts['pending'] > 0): ?>
            <span class="ml-1 px-2 py-0.5 text-xs bg-amber-100 text-amber-700 rounded-full"><?= $counts['pending'] ?></span>
            <?php endif; ?>
        </a>
        <a href="?status=approved"
           class="px-4 py-2 border-b-2 <?= $status === 'approved' ? 'border-primary-600 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700' ?>">
            Approved
            <span class="ml-1 text-xs text-neutral-400"><?= $counts['approved'] ?></span>
        </a>
        <a href="?status=spam"
           class="px-4 py-2 border-b-2 <?= $status === 'spam' ? 'border-primary-600 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700' ?>">
            Spam
            <span class="ml-1 text-xs text-neutral-400"><?= $counts['spam'] ?></span>
        </a>
    </div>

    <!-- Comments List -->
    <div class="bg-white rounded-lg shadow">
        <?php if (empty($comments)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p class="text-neutral-500">No <?= $status ?> comments.</p>
        </div>
        <?php else: ?>
        <div class="divide-y">
            <?php foreach ($comments as $comment): ?>
            <div class="p-4 hover:bg-neutral-50">
                <div class="flex gap-4">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <?php
                        $email = $comment['user_email'] ?? $comment['guest_email'] ?? '';
                        $hash = md5(strtolower(trim($email)));
                        ?>
                        <img src="https://www.gravatar.com/avatar/<?= $hash ?>?s=48&d=mp" alt=""
                             class="w-10 h-10 rounded-full">
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="font-medium text-neutral-900">
                                    <?= e($comment['username'] ?? $comment['guest_name'] ?? 'Anonymous') ?>
                                </span>
                                <?php if ($comment['guest_email']): ?>
                                <span class="text-neutral-400 text-sm">&lt;<?= e($comment['guest_email']) ?>&gt;</span>
                                <?php endif; ?>
                                <span class="text-neutral-400 text-sm mx-2">on</span>
                                <a href="/blog/<?= e($comment['post_slug']) ?>" class="text-primary-600 hover:underline text-sm">
                                    <?= e($comment['post_title']) ?>
                                </a>
                            </div>
                            <span class="text-xs text-neutral-400 whitespace-nowrap">
                                <?= date('M j, Y g:i A', strtotime($comment['created_at'])) ?>
                            </span>
                        </div>

                        <div class="mt-2 text-neutral-700">
                            <?= nl2br(e($comment['content'])) ?>
                        </div>

                        <div class="mt-3 flex items-center gap-4">
                            <?php if ($status === 'pending'): ?>
                            <form method="POST" action="/admin/blog/comments/<?= $comment['id'] ?>/approve" class="inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-sm text-green-600 hover:text-green-700 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Approve
                                </button>
                            </form>
                            <form method="POST" action="/admin/blog/comments/<?= $comment['id'] ?>/spam" class="inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-sm text-amber-600 hover:text-amber-700 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    Spam
                                </button>
                            </form>
                            <?php elseif ($status === 'spam'): ?>
                            <form method="POST" action="/admin/blog/comments/<?= $comment['id'] ?>/approve" class="inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-sm text-green-600 hover:text-green-700">Not Spam</button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="/admin/blog/comments/<?= $comment['id'] ?>/delete" class="inline"
                                  onsubmit="return confirm('Delete this comment?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-sm text-red-600 hover:text-red-700 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Delete
                                </button>
                            </form>
                            <span class="text-xs text-neutral-400">IP: <?= e($comment['ip_address']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
