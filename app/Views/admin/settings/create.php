<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="/admin/settings" class="text-neutral-500 hover:text-neutral-700 inline-flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Settings
        </a>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold">Add Custom Setting</h1>
        </div>

        <form method="POST" action="/admin/settings/store" class="p-6 space-y-6">
            <?= csrf_field() ?>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Setting Key *</label>
                <input type="text" name="setting_key" value="<?= e(old('setting_key')) ?>" required
                       pattern="[a-z_]+" placeholder="e.g., custom_setting_name"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <p class="text-neutral-500 text-sm mt-1">Lowercase letters and underscores only</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Type *</label>
                <select name="setting_type" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <?php foreach ($types as $type): ?>
                        <option value="<?= $type ?>"><?= ucfirst($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Value</label>
                <textarea name="setting_value" rows="3"
                          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= e(old('setting_value')) ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Description</label>
                <input type="text" name="description" value="<?= e(old('description')) ?>"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_public" value="1"
                           class="w-4 h-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-neutral-700">Public (accessible via API)</span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="/admin/settings" class="px-4 py-2 text-neutral-700 hover:text-neutral-900">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Create Setting
                </button>
            </div>
        </form>
    </div>
</div>
