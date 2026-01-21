<?php $currentPage = 'marketing'; $currentTab = 'placements'; ?>

<?php include __DIR__ . '/_subnav.php'; ?>

<div class="max-w-3xl">
    <div class="mb-6">
        <a href="/admin/marketing/placements" class="text-sm text-neutral-500 hover:text-neutral-700 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Placements
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-neutral-100 p-6">
        <form action="<?= $placement ? '/admin/marketing/placements/' . $placement['id'] : '/admin/marketing/placements' ?>" method="POST">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">

            <!-- Name -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Name *</label>
                <input type="text" name="name" value="<?= e($placement['name'] ?? '') ?>" required
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="e.g., Header Banner Desktop">
            </div>

            <!-- Slug -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Slug *</label>
                <input type="text" name="slug" value="<?= e($placement['slug'] ?? '') ?>" required
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="e.g., header-banner-desktop">
                <p class="mt-1 text-sm text-neutral-500">Unique identifier used in templates to display ads</p>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Location -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Location *</label>
                    <select name="location" required
                            class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Select location...</option>
                        <option value="header" <?= ($placement['location'] ?? '') === 'header' ? 'selected' : '' ?>>Header</option>
                        <option value="sidebar" <?= ($placement['location'] ?? '') === 'sidebar' ? 'selected' : '' ?>>Sidebar</option>
                        <option value="between_images" <?= ($placement['location'] ?? '') === 'between_images' ? 'selected' : '' ?>>Between Images (Gallery)</option>
                        <option value="footer" <?= ($placement['location'] ?? '') === 'footer' ? 'selected' : '' ?>>Footer</option>
                        <option value="popup" <?= ($placement['location'] ?? '') === 'popup' ? 'selected' : '' ?>>Popup</option>
                        <option value="interstitial" <?= ($placement['location'] ?? '') === 'interstitial' ? 'selected' : '' ?>>Interstitial</option>
                    </select>
                </div>

                <!-- Default Size -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Default Size</label>
                    <select name="default_size"
                            class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Select size...</option>
                        <option value="728x90" <?= ($placement['default_size'] ?? '') === '728x90' ? 'selected' : '' ?>>728x90 (Leaderboard)</option>
                        <option value="300x250" <?= ($placement['default_size'] ?? '') === '300x250' ? 'selected' : '' ?>>300x250 (Medium Rectangle)</option>
                        <option value="336x280" <?= ($placement['default_size'] ?? '') === '336x280' ? 'selected' : '' ?>>336x280 (Large Rectangle)</option>
                        <option value="300x600" <?= ($placement['default_size'] ?? '') === '300x600' ? 'selected' : '' ?>>300x600 (Half Page)</option>
                        <option value="160x600" <?= ($placement['default_size'] ?? '') === '160x600' ? 'selected' : '' ?>>160x600 (Skyscraper)</option>
                        <option value="320x50" <?= ($placement['default_size'] ?? '') === '320x50' ? 'selected' : '' ?>>320x50 (Mobile Banner)</option>
                        <option value="responsive" <?= ($placement['default_size'] ?? '') === 'responsive' ? 'selected' : '' ?>>Responsive</option>
                    </select>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Description</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="Describe where this placement appears..."><?= e($placement['description'] ?? '') ?></textarea>
                <p class="mt-1 text-sm text-neutral-500">Optional description for internal reference</p>
            </div>

            <!-- Sort Order -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Sort Order</label>
                <input type="number" name="sort_order" value="<?= $placement['sort_order'] ?? 0 ?>" min="0"
                       class="w-32 px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <p class="mt-1 text-sm text-neutral-500">Lower numbers appear first</p>
            </div>

            <!-- Active -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($placement['is_active'] ?? 1) ? 'checked' : '' ?>
                           class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm font-medium text-neutral-700">Active</span>
                </label>
                <p class="mt-1 text-sm text-neutral-500 ml-6">Enable this ad placement</p>
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-4 pt-4 border-t border-neutral-200">
                <button type="submit"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">
                    <?= $placement ? 'Update Placement' : 'Create Placement' ?>
                </button>
                <a href="/admin/marketing/placements" class="px-6 py-2 text-neutral-600 hover:text-neutral-800">Cancel</a>
            </div>
        </form>
    </div>
</div>
