<div class="max-w-4xl">
    <form action="<?= $view->url("/admin/images/{$image['id']}") ?>" method="POST" class="space-y-6">
        <?= $view->csrf() ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Image Preview -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="aspect-square bg-neutral-100 rounded-lg overflow-hidden mb-4">
                        <?php if ($image['thumbnail_path']): ?>
                            <img src="<?= $view->url('/uploads/' . $image['storage_path']) ?>"
                                 alt="<?= e($image['title']) ?>"
                                 class="w-full h-full object-contain">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-neutral-400">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="text-sm text-neutral-600 space-y-1">
                        <p><strong>Dimensions:</strong> <?= $image['width'] ?>x<?= $image['height'] ?>px</p>
                        <p><strong>Size:</strong> <?= number_format($image['file_size'] / 1024, 1) ?> KB</p>
                        <p><strong>Type:</strong> <?= $image['mime_type'] ?></p>
                        <p><strong>Uploaded:</strong> <?= date('M j, Y g:i A', strtotime($image['created_at'])) ?></p>
                        <?php if ($image['dominant_color']): ?>
                        <p class="flex items-center gap-2">
                            <strong>Color:</strong>
                            <span class="inline-block w-6 h-6 rounded border" style="background-color: <?= $image['dominant_color'] ?>"></span>
                            <?= $image['dominant_color'] ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Basic Information</h2>

                    <div class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-neutral-700">Title *</label>
                            <input type="text" name="title" id="title" value="<?= e($image['title']) ?>" required
                                   class="mt-1 block w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>

                        <div>
                            <label for="alt_text" class="block text-sm font-medium text-neutral-700">Alt Text</label>
                            <input type="text" name="alt_text" id="alt_text" value="<?= e($image['alt_text']) ?>"
                                   class="mt-1 block w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <p class="mt-1 text-xs text-neutral-500">Describe the image for accessibility and SEO.</p>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-neutral-700">Description</label>
                            <textarea name="description" id="description" rows="3"
                                      class="mt-1 block w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= e($image['description']) ?></textarea>
                        </div>

                        <div>
                            <label for="slug" class="block text-sm font-medium text-neutral-700">URL Slug</label>
                            <div class="mt-1 flex gap-2">
                                <input type="text" name="slug" id="slug" value="<?= e($image['slug']) ?>"
                                       class="flex-1 px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <button type="button" onclick="regenerateSlug()"
                                        class="px-3 py-2 border border-neutral-300 rounded-lg text-neutral-600 hover:bg-neutral-50 text-sm whitespace-nowrap"
                                        title="Regenerate from title">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-neutral-500">URL: <?= $view->url('/image/') ?><span id="slug-preview"><?= e($image['slug']) ?></span></p>
                            <input type="hidden" name="regenerate_slug" id="regenerate_slug" value="">
                        </div>
                    </div>
                </div>

                <!-- Categories & Tags -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Categories & Tags</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Categories</label>
                            <div class="grid grid-cols-2 gap-2">
                                <?php foreach ($categories as $category): ?>
                                <label class="flex items-center">
                                    <input type="checkbox" name="categories[]" value="<?= $category['id'] ?>"
                                           <?= in_array($category['id'], $imageCategoryIds) ? 'checked' : '' ?>
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 rounded">
                                    <span class="ml-2 text-sm text-neutral-700"><?= e($category['name']) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div>
                            <label for="tags" class="block text-sm font-medium text-neutral-700">Tags</label>
                            <input type="text" name="tags" id="tags"
                                   value="<?= e(implode(', ', array_column($imageTags, 'name'))) ?>"
                                   class="mt-1 block w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <p class="mt-1 text-xs text-neutral-500">Separate tags with commas.</p>
                        </div>
                    </div>
                </div>

                <!-- Status & Moderation -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Status & Moderation</h2>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-neutral-700">Status</label>
                            <select name="status" id="status"
                                    class="mt-1 block w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="draft" <?= $image['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="published" <?= $image['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                                <option value="archived" <?= $image['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>

                        <div>
                            <label for="moderation_status" class="block text-sm font-medium text-neutral-700">Moderation</label>
                            <select name="moderation_status" id="moderation_status"
                                    class="mt-1 block w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="pending" <?= $image['moderation_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="approved" <?= $image['moderation_status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="rejected" <?= $image['moderation_status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="featured" <?= $image['featured'] ? 'checked' : '' ?>
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 rounded">
                            <span class="ml-2 text-sm text-neutral-700">Featured image</span>
                        </label>
                    </div>
                </div>

                <!-- AI Generated Data -->
                <?php if ($image['ai_description'] || $image['ai_tags']): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">AI Generated Data</h2>

                    <?php if ($image['ai_description']): ?>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700">AI Description</label>
                        <p class="mt-1 text-sm text-neutral-600"><?= e($image['ai_description']) ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($image['ai_tags']): ?>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700">AI Suggested Tags</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <?php foreach (json_decode($image['ai_tags'], true) ?? [] as $tag): ?>
                            <span class="px-2 py-1 bg-neutral-100 text-neutral-700 text-sm rounded"><?= e($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="flex justify-between">
                    <button type="button" onclick="deleteImage(<?= $image['id'] ?>)"
                            class="px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50">
                        Delete Image
                    </button>
                    <div class="flex gap-4">
                        <a href="<?= $view->url('/admin/images') ?>" class="px-6 py-2 border border-neutral-300 rounded-lg text-neutral-700 hover:bg-neutral-50">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function deleteImage(id) {
    if (!confirm('Are you sure you want to delete this image? This cannot be undone.')) return;

    fetch(`<?= $view->url('/admin/images') ?>/${id}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': '<?= $_SESSION['_csrf_token'] ?? '' ?>'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?= $view->url('/admin/images') ?>';
        } else {
            alert(data.error || 'Failed to delete image');
        }
    })
    .catch(err => alert('Error deleting image'));
}

function regenerateSlug() {
    const title = document.getElementById('title').value;
    if (!title) {
        alert('Please enter a title first');
        return;
    }
    // Generate slug from title (client-side preview)
    let slug = title.toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
    document.getElementById('slug').value = slug;
    document.getElementById('slug-preview').textContent = slug;
    document.getElementById('regenerate_slug').value = 'on';
}

// Update slug preview on manual edit
document.getElementById('slug').addEventListener('input', function() {
    document.getElementById('slug-preview').textContent = this.value;
    document.getElementById('regenerate_slug').value = '';
});

// Auto-generate slug when title changes (only if slug matches old title pattern)
document.getElementById('title').addEventListener('input', function() {
    const slugField = document.getElementById('slug');
    const oldSlug = slugField.value;
    const newTitle = this.value;

    // Generate expected slug from title
    let expectedSlug = newTitle.toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');

    // If slug is empty or matches expected pattern, auto-update
    if (!oldSlug || oldSlug === expectedSlug.substring(0, oldSlug.length)) {
        slugField.value = expectedSlug;
        document.getElementById('slug-preview').textContent = expectedSlug;
    }
});
</script>
