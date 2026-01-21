<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Blog Posts</h1>
            <p class="text-neutral-500 text-sm mt-1">Manage your blog content</p>
        </div>
        <div class="flex gap-3">
            <a href="/admin/blog/categories" class="px-4 py-2 border border-neutral-300 text-neutral-700 rounded-lg hover:bg-neutral-50 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                Categories
            </a>
            <a href="/admin/blog/comments" class="px-4 py-2 border border-neutral-300 text-neutral-700 rounded-lg hover:bg-neutral-50 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                Comments
            </a>
            <a href="/admin/blog/create" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Post
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg p-4 border">
            <div class="text-2xl font-bold text-neutral-900"><?= number_format($stats['total']) ?></div>
            <div class="text-sm text-neutral-500">Total Posts</div>
        </div>
        <div class="bg-white rounded-lg p-4 border">
            <div class="text-2xl font-bold text-green-600"><?= number_format($stats['published']) ?></div>
            <div class="text-sm text-neutral-500">Published</div>
        </div>
        <div class="bg-white rounded-lg p-4 border">
            <div class="text-2xl font-bold text-amber-600"><?= number_format($stats['drafts']) ?></div>
            <div class="text-sm text-neutral-500">Drafts</div>
        </div>
        <div class="bg-white rounded-lg p-4 border">
            <div class="text-2xl font-bold text-purple-600"><?= number_format($stats['ai_generated']) ?></div>
            <div class="text-sm text-neutral-500">AI Generated</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>"
                       placeholder="Search posts..."
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <select name="status" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    <option value="">All Status</option>
                    <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="scheduled" <?= ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                    <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div>
                <select name="category_id" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat->id ?>" <?= ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' ?>>
                        <?= e($cat->name) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-neutral-100 text-neutral-700 rounded-lg hover:bg-neutral-200">
                Filter
            </button>
            <?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['category_id'])): ?>
            <a href="/admin/blog" class="px-4 py-2 text-neutral-500 hover:text-neutral-700">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Posts Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php if (empty($posts)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
            </svg>
            <p class="text-neutral-500">No blog posts found.</p>
            <a href="/admin/blog/create" class="mt-4 inline-block text-primary-600 hover:text-primary-700">Create your first post</a>
        </div>
        <?php else: ?>
        <table class="w-full">
            <thead class="bg-neutral-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Post</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase hidden md:table-cell">Category</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase hidden lg:table-cell">Author</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-neutral-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-neutral-500 uppercase hidden sm:table-cell">Views</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase hidden lg:table-cell">Date</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-neutral-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($posts as $post): ?>
                <tr class="hover:bg-neutral-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <?php if ($post['featured_image']): ?>
                            <img src="/uploads/<?= e($post['featured_image']) ?>" alt="" class="w-12 h-12 object-cover rounded">
                            <?php else: ?>
                            <div class="w-12 h-12 bg-neutral-100 rounded flex items-center justify-center">
                                <svg class="w-6 h-6 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <?php endif; ?>
                            <div>
                                <a href="/admin/blog/<?= $post['id'] ?>/edit" class="font-medium text-neutral-900 hover:text-primary-600">
                                    <?= e($post['title']) ?>
                                </a>
                                <div class="flex items-center gap-2 text-xs text-neutral-500">
                                    <?php if ($post['is_featured']): ?>
                                    <span class="text-amber-500">Featured</span>
                                    <?php endif; ?>
                                    <?php if ($post['ai_generated']): ?>
                                    <span class="text-purple-500">AI</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <span class="text-sm text-neutral-600"><?= e($post['category_name'] ?? 'Uncategorized') ?></span>
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell">
                        <span class="text-sm text-neutral-600"><?= e($post['author_name'] ?? 'Unknown') ?></span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <?php
                        $statusColors = [
                            'published' => 'bg-green-100 text-green-700',
                            'draft' => 'bg-neutral-100 text-neutral-700',
                            'scheduled' => 'bg-blue-100 text-blue-700',
                            'archived' => 'bg-red-100 text-red-700',
                        ];
                        $color = $statusColors[$post['status']] ?? 'bg-neutral-100 text-neutral-700';
                        ?>
                        <span class="px-2 py-1 text-xs font-medium rounded-full <?= $color ?>">
                            <?= ucfirst($post['status']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center hidden sm:table-cell">
                        <span class="text-sm text-neutral-600"><?= number_format($post['view_count']) ?></span>
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell">
                        <span class="text-sm text-neutral-500"><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <?php if ($post['status'] === 'published'): ?>
                            <a href="/blog/<?= e($post['slug']) ?>" target="_blank" class="p-1 text-neutral-400 hover:text-neutral-600" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                            <?php endif; ?>
                            <a href="/admin/blog/<?= $post['id'] ?>/edit" class="p-1 text-neutral-400 hover:text-primary-600" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="/admin/blog/<?= $post['id'] ?>/delete" class="inline" onsubmit="return confirm('Delete this post?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="p-1 text-neutral-400 hover:text-red-600" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="px-4 py-3 border-t flex items-center justify-between">
            <div class="text-sm text-neutral-500">
                Showing <?= (($page - 1) * 20) + 1 ?> to <?= min($page * 20, $total) ?> of <?= $total ?> posts
            </div>
            <div class="flex gap-1">
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&<?= http_build_query($filters) ?>" class="px-3 py-1 border rounded hover:bg-neutral-50">Prev</a>
                <?php endif; ?>
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?page=<?= $i ?>&<?= http_build_query($filters) ?>"
                   class="px-3 py-1 border rounded <?= $i === $page ? 'bg-primary-600 text-white border-primary-600' : 'hover:bg-neutral-50' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&<?= http_build_query($filters) ?>" class="px-3 py-1 border rounded hover:bg-neutral-50">Next</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
