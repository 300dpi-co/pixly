<?php
$currentPage = 'pages';
$isEdit = isset($page) && $page;
$old = session_get_flash('old') ?? [];
$errors = session_get_flash('errors') ?? [];
?>

<!-- TinyMCE Editor -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<div class="max-w-4xl">
    <div class="mb-6">
        <a href="/admin/pages" class="text-sm text-neutral-500 hover:text-neutral-700 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Pages
        </a>
    </div>

    <?php if (session_get_flash('error')): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
        <?= session_get_flash('error') ?>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-neutral-100 p-6">
        <h1 class="text-xl font-semibold text-neutral-900 mb-6"><?= $isEdit ? 'Edit Page' : 'Create Page' ?></h1>

        <form action="<?= $isEdit ? '/admin/pages/' . $page['id'] : '/admin/pages' ?>" method="POST">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Title *</label>
                        <input type="text" name="title" id="title"
                               value="<?= e($old['title'] ?? $page['title'] ?? '') ?>" required
                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 <?= isset($errors['title']) ? 'border-red-500' : '' ?>"
                               placeholder="e.g., About Us">
                        <?php if (isset($errors['title'])): ?>
                        <p class="mt-1 text-sm text-red-500"><?= e($errors['title']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Slug -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            URL Slug
                            <?php if ($isEdit && $page['is_system']): ?>
                            <span class="text-neutral-400 font-normal">(locked for system pages)</span>
                            <?php endif; ?>
                        </label>
                        <div class="flex items-center">
                            <span class="text-neutral-500 mr-1">/</span>
                            <input type="text" name="slug" id="slug"
                                   value="<?= e($old['slug'] ?? $page['slug'] ?? '') ?>"
                                   <?= ($isEdit && $page['is_system']) ? 'readonly' : '' ?>
                                   class="flex-1 px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 <?= ($isEdit && $page['is_system']) ? 'bg-neutral-100' : '' ?> <?= isset($errors['slug']) ? 'border-red-500' : '' ?>"
                                   placeholder="about-us">
                        </div>
                        <p class="mt-1 text-sm text-neutral-500">Leave empty to auto-generate from title</p>
                        <?php if (isset($errors['slug'])): ?>
                        <p class="mt-1 text-sm text-red-500"><?= e($errors['slug']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Content -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Content</label>
                        <textarea name="content" id="content" rows="20"
                                  class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= e($old['content'] ?? $page['content'] ?? '') ?></textarea>
                        <p class="mt-2 text-sm text-neutral-500">
                            Available variables:
                            <code class="bg-neutral-100 px-1 rounded">{{site_name}}</code>,
                            <code class="bg-neutral-100 px-1 rounded">{{current_date}}</code>,
                            <code class="bg-neutral-100 px-1 rounded">{{current_year}}</code>
                        </p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Meta Description -->
                    <div class="bg-neutral-50 rounded-lg p-4">
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Meta Description</label>
                        <textarea name="meta_description" rows="3"
                                  class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm <?= isset($errors['meta_description']) ? 'border-red-500' : '' ?>"
                                  placeholder="Brief description for search engines..."><?= e($old['meta_description'] ?? $page['meta_description'] ?? '') ?></textarea>
                        <p class="mt-1 text-xs text-neutral-500">Recommended: 150-160 characters</p>
                        <?php if (isset($errors['meta_description'])): ?>
                        <p class="mt-1 text-sm text-red-500"><?= e($errors['meta_description']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Options -->
                    <div class="bg-neutral-50 rounded-lg p-4 space-y-4">
                        <h3 class="font-medium text-neutral-900">Options</h3>

                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1"
                                   <?= ($old['is_active'] ?? $page['is_active'] ?? 1) ? 'checked' : '' ?>
                                   class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                            <span class="ml-2 text-sm text-neutral-700">Active</span>
                        </label>
                        <p class="text-xs text-neutral-500 ml-6">Page is visible to visitors</p>

                        <label class="flex items-center">
                            <input type="checkbox" name="show_in_footer" value="1"
                                   <?= ($old['show_in_footer'] ?? $page['show_in_footer'] ?? 1) ? 'checked' : '' ?>
                                   class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                            <span class="ml-2 text-sm text-neutral-700">Show in Footer</span>
                        </label>
                        <p class="text-xs text-neutral-500 ml-6">Display link in footer navigation</p>
                    </div>

                    <!-- Sort Order -->
                    <div class="bg-neutral-50 rounded-lg p-4">
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Sort Order</label>
                        <input type="number" name="sort_order" min="0"
                               value="<?= $old['sort_order'] ?? $page['sort_order'] ?? 0 ?>"
                               class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <p class="mt-1 text-xs text-neutral-500">Lower numbers appear first in footer</p>
                    </div>

                    <!-- Submit -->
                    <div class="flex gap-3">
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">
                            <?= $isEdit ? 'Update Page' : 'Create Page' ?>
                        </button>
                        <a href="/admin/pages"
                           class="px-4 py-2 border border-neutral-300 text-neutral-700 font-medium rounded-lg hover:bg-neutral-50 transition">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Initialize TinyMCE
tinymce.init({
    selector: '#content',
    height: 500,
    menubar: false,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'table', 'code', 'help', 'wordcount'
    ],
    toolbar: 'undo redo | blocks | ' +
        'bold italic underline strikethrough | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist | ' +
        'link | removeformat | code | help',
    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; line-height: 1.6; }',
    branding: false,
    promotion: false,
    license_key: 'gpl'
});

// Auto-generate slug from title
document.getElementById('title').addEventListener('blur', function() {
    const slugInput = document.getElementById('slug');
    if (!slugInput.value && !slugInput.readOnly) {
        slugInput.value = this.value
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }
});
</script>
