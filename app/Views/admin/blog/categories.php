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
                <h1 class="text-2xl font-bold text-neutral-900">Blog Categories</h1>
                <p class="text-neutral-500 text-sm">Organize your blog posts</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add Category Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-neutral-900 mb-4">Add Category</h2>
                <form method="POST" action="/admin/blog/categories" class="space-y-4">
                    <?= csrf_field() ?>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Name *</label>
                        <input type="text" name="name" required
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Parent Category</label>
                        <select name="parent_id" class="w-full px-3 py-2 border rounded-lg">
                            <option value="">None (Top Level)</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            Add Category
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Categories List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <?php if (empty($categories)): ?>
                <div class="p-12 text-center">
                    <p class="text-neutral-500">No categories yet.</p>
                </div>
                <?php else: ?>
                <table class="w-full">
                    <thead class="bg-neutral-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase hidden sm:table-cell">Description</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-neutral-500 uppercase">Posts</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-neutral-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-neutral-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" id="categoriesTable">
                        <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-neutral-50" data-id="<?= $cat['id'] ?>">
                            <td class="px-4 py-3">
                                <div class="font-medium text-neutral-900"><?= e($cat['name']) ?></div>
                                <div class="text-xs text-neutral-400">/blog/category/<?= e($cat['slug']) ?></div>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <span class="text-sm text-neutral-600 line-clamp-2"><?= e($cat['description'] ?? '-') ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-sm text-neutral-600"><?= number_format($cat['post_count']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($cat['is_active']): ?>
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">Active</span>
                                <?php else: ?>
                                <span class="px-2 py-1 text-xs bg-neutral-100 text-neutral-600 rounded-full">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" onclick="editCategory(<?= htmlspecialchars(json_encode($cat)) ?>)"
                                            class="p-1 text-neutral-400 hover:text-primary-600" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <?php if ($cat['post_count'] == 0): ?>
                                    <form method="POST" action="/admin/blog/categories/<?= $cat['id'] ?>/delete" class="inline" onsubmit="return confirm('Delete this category?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="p-1 text-neutral-400 hover:text-red-600" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-semibold text-neutral-900 mb-4">Edit Category</h3>
        <form method="POST" id="editForm" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Name *</label>
                <input type="text" name="name" id="editName" required
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Description</label>
                <textarea name="description" id="editDescription" rows="3"
                          class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Meta Title</label>
                <input type="text" name="meta_title" id="editMetaTitle"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Meta Description</label>
                <textarea name="meta_description" id="editMetaDescription" rows="2"
                          class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="editIsActive" value="1" class="rounded">
                <label for="editIsActive" class="text-sm text-neutral-700">Active</label>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-neutral-600 hover:bg-neutral-100 rounded-lg">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editCategory(cat) {
    document.getElementById('editForm').action = '/admin/blog/categories/' + cat.id;
    document.getElementById('editName').value = cat.name;
    document.getElementById('editDescription').value = cat.description || '';
    document.getElementById('editMetaTitle').value = cat.meta_title || '';
    document.getElementById('editMetaDescription').value = cat.meta_description || '';
    document.getElementById('editIsActive').checked = cat.is_active == 1;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>
