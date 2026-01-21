<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="/admin/categories" class="text-neutral-500 hover:text-neutral-700 inline-flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Categories
        </a>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold">Create Category</h1>
        </div>

        <form method="POST" action="/admin/categories" class="p-6 space-y-6">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Name *</label>
                    <input type="text" name="name" value="<?= e(old('name')) ?>" required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Slug</label>
                    <input type="text" name="slug" value="<?= e(old('slug')) ?>" placeholder="auto-generated"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <p class="text-neutral-500 text-sm mt-1">Leave blank to auto-generate</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Parent Category</label>
                <select name="parent_id"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">None (Top Level)</option>
                    <?php foreach ($parentCategories as $id => $name): ?>
                        <option value="<?= $id ?>"><?= e($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Description</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= e(old('description')) ?></textarea>
            </div>

            <div class="border-t pt-6">
                <h3 class="text-sm font-medium text-neutral-700 mb-4">SEO Settings</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Meta Title</label>
                        <input type="text" name="meta_title" value="<?= e(old('meta_title')) ?>" maxlength="70"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-neutral-500 text-sm mt-1">Max 70 characters</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Meta Description</label>
                        <textarea name="meta_description" rows="2" maxlength="160"
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= e(old('meta_description')) ?></textarea>
                        <p class="text-neutral-500 text-sm mt-1">Max 160 characters</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="<?= e(old('sort_order') ?: 0) ?>" min="0"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div class="flex items-center">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" checked
                               class="w-4 h-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm text-neutral-700">Active</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="/admin/categories" class="px-4 py-2 text-neutral-700 hover:text-neutral-900">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Create Category
                </button>
            </div>
        </form>
    </div>
</div>
