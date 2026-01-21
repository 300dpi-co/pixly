<div class="max-w-2xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="/admin/categories" class="text-neutral-500 hover:text-neutral-700 inline-flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Categories
        </a>
        <a href="/category/<?= e($category->slug) ?>" target="_blank"
           class="text-sm text-primary-600 hover:text-primary-700 inline-flex items-center gap-1">
            View on site
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold">Edit Category</h1>
        </div>

        <form method="POST" action="/admin/categories/<?= $category->id ?>" class="p-6 space-y-6">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Name *</label>
                    <input type="text" name="name" value="<?= e(old('name') ?: $category->name) ?>" required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Slug</label>
                    <input type="text" name="slug" value="<?= e(old('slug') ?: $category->slug) ?>"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Parent Category</label>
                <select name="parent_id"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">None (Top Level)</option>
                    <?php foreach ($parentCategories as $id => $name): ?>
                        <option value="<?= $id ?>" <?= $category->parent_id == $id ? 'selected' : '' ?>>
                            <?= e($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Description</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= e(old('description') ?: $category->description) ?></textarea>
            </div>

            <div class="border-t pt-6">
                <h3 class="text-sm font-medium text-neutral-700 mb-4">SEO Settings</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Meta Title</label>
                        <input type="text" name="meta_title" value="<?= e(old('meta_title') ?: $category->meta_title) ?>" maxlength="70"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-neutral-500 text-sm mt-1">Max 70 characters</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Meta Description</label>
                        <textarea name="meta_description" rows="2" maxlength="160"
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= e(old('meta_description') ?: $category->meta_description) ?></textarea>
                        <p class="text-neutral-500 text-sm mt-1">Max 160 characters</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="<?= e(old('sort_order') ?: $category->sort_order) ?>" min="0"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div class="flex items-center">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1"
                               <?= $category->is_active ? 'checked' : '' ?>
                               class="w-4 h-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm text-neutral-700">Active</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-between items-center pt-4 border-t">
                <div>
                    <form method="POST" action="/admin/categories/<?= $category->id ?>/delete" class="inline"
                          onsubmit="return confirm('Are you sure you want to delete this category? This cannot be undone.')">
                        <?= csrf_field() ?>
                        <button type="submit" class="text-red-600 hover:text-red-700">Delete Category</button>
                    </form>
                </div>
                <div class="flex gap-3">
                    <a href="/admin/categories" class="px-4 py-2 text-neutral-700 hover:text-neutral-900">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Category Stats -->
    <div class="mt-6 bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold mb-4">Statistics</h3>
        <div class="grid grid-cols-2 gap-4 text-center">
            <div>
                <p class="text-2xl font-bold text-primary-600"><?= number_format($category->image_count) ?></p>
                <p class="text-sm text-neutral-500">Images</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-neutral-600"><?= date('M j, Y', strtotime($category->created_at)) ?></p>
                <p class="text-sm text-neutral-500">Created</p>
            </div>
        </div>
    </div>
</div>
