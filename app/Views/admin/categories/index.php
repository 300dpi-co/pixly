<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Categories</h1>
            <p class="text-neutral-500"><?= count($categories) ?> categories</p>
        </div>
        <a href="/admin/categories/create" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Category
        </a>
    </div>

    <!-- Categories Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php if (empty($categories)): ?>
            <div class="p-12 text-center">
                <svg class="w-12 h-12 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <p class="text-neutral-500">No categories found.</p>
                <a href="/admin/categories/create" class="text-primary-600 hover:text-primary-700 mt-2 inline-block">Create your first category</a>
            </div>
        <?php else: ?>
            <table class="w-full">
                <thead class="bg-neutral-50">
                    <tr class="text-left text-sm text-neutral-500">
                        <th class="px-6 py-3 font-medium">Name</th>
                        <th class="px-6 py-3 font-medium">Slug</th>
                        <th class="px-6 py-3 font-medium">Parent</th>
                        <th class="px-6 py-3 font-medium">Images</th>
                        <th class="px-6 py-3 font-medium">Order</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($categories as $cat): ?>
                    <tr class="hover:bg-neutral-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <?php if ($cat['cover_image_id']): ?>
                                    <div class="w-10 h-10 bg-neutral-200 rounded"></div>
                                <?php endif; ?>
                                <div>
                                    <span class="font-medium"><?= e($cat['name']) ?></span>
                                    <?php if ($cat['description']): ?>
                                        <p class="text-sm text-neutral-500 truncate max-w-xs"><?= e($cat['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <code class="text-sm text-neutral-500 bg-neutral-100 px-2 py-1 rounded"><?= e($cat['slug']) ?></code>
                        </td>
                        <td class="px-6 py-4 text-sm text-neutral-500">
                            <?= $cat['parent_name'] ? e($cat['parent_name']) : '—' ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm"><?= number_format($cat['image_count']) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-neutral-500"><?= $cat['sort_order'] ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="toggleStatus(<?= $cat['id'] ?>, this)"
                                    class="px-2 py-1 text-xs rounded-full <?= $cat['is_active'] ? 'bg-green-100 text-green-800' : 'bg-neutral-100 text-neutral-600' ?>">
                                <?= $cat['is_active'] ? 'Active' : 'Inactive' ?>
                            </button>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="/category/<?= e($cat['slug']) ?>" target="_blank"
                                   class="p-2 text-neutral-400 hover:text-neutral-600" title="View">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                                <a href="/admin/categories/<?= $cat['id'] ?>/edit"
                                   class="p-2 text-neutral-400 hover:text-blue-600" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="/admin/categories/<?= $cat['id'] ?>/delete" class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this category?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="p-2 text-neutral-400 hover:text-red-600" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <?php endif; ?>
    </div>
</div>

<script>
async function toggleStatus(id, btn) {
    try {
        const response = await fetch(`/admin/categories/${id}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: '_token=<?= csrf_token() ?>'
        });
        const data = await response.json();
        if (data.success) {
            btn.textContent = data.is_active ? 'Active' : 'Inactive';
            btn.className = 'px-2 py-1 text-xs rounded-full ' +
                (data.is_active ? 'bg-green-100 text-green-800' : 'bg-neutral-100 text-neutral-600');
        }
    } catch (e) {
        console.error(e);
    }
}
</script>
