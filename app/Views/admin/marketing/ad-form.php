<?php $currentPage = 'marketing'; $currentTab = 'ads'; ?>

<?php include __DIR__ . '/_subnav.php'; ?>

<div class="max-w-3xl">
    <div class="mb-6">
        <a href="/admin/marketing/ads" class="text-sm text-neutral-500 hover:text-neutral-700 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Ads
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-neutral-100 p-6">
        <form action="<?= $ad ? '/admin/marketing/ads/' . $ad['id'] : '/admin/marketing/ads' ?>" method="POST">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">

            <!-- Name -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Name *</label>
                <input type="text" name="name" value="<?= e($ad['name'] ?? '') ?>" required
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="e.g., Header Banner - January Campaign">
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Placement -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Placement *</label>
                    <select name="placement_id" required
                            class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Select placement...</option>
                        <?php foreach ($placements as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($ad['placement_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                            <?= e($p['name']) ?> (<?= e($p['slug']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-sm text-neutral-500">Where this ad will be displayed</p>
                </div>

                <!-- Ad Type -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Ad Type *</label>
                    <select name="ad_type" id="ad_type" required
                            class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            onchange="toggleAdTypeFields()">
                        <option value="custom_html" <?= ($ad['ad_type'] ?? 'custom_html') === 'custom_html' ? 'selected' : '' ?>>Custom HTML</option>
                        <option value="image" <?= ($ad['ad_type'] ?? '') === 'image' ? 'selected' : '' ?>>Image</option>
                        <option value="adsense" <?= ($ad['ad_type'] ?? '') === 'adsense' ? 'selected' : '' ?>>Google AdSense</option>
                        <option value="juicyads" <?= ($ad['ad_type'] ?? '') === 'juicyads' ? 'selected' : '' ?>>JuicyAds</option>
                    </select>
                </div>
            </div>

            <!-- Content (HTML) -->
            <div class="mb-6" id="content_field">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Ad Content (HTML)</label>
                <textarea name="content" rows="8"
                          class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono text-sm"
                          placeholder="<div>Your ad HTML here...</div>"><?= e($ad['content'] ?? '') ?></textarea>
                <p class="mt-1 text-sm text-neutral-500">Paste your ad code, AdSense snippet, or custom HTML</p>
            </div>

            <!-- Image Fields (shown when type is image) -->
            <div class="mb-6 hidden" id="image_fields">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Image Path</label>
                    <input type="text" name="image_path" value="<?= e($ad['image_path'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="/uploads/ads/banner.jpg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Destination URL</label>
                    <input type="url" name="destination_url" value="<?= e($ad['destination_url'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="https://example.com/landing-page">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Device Target -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Device Target</label>
                    <select name="device_target"
                            class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="all" <?= ($ad['device_target'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Devices</option>
                        <option value="desktop" <?= ($ad['device_target'] ?? '') === 'desktop' ? 'selected' : '' ?>>Desktop Only</option>
                        <option value="mobile" <?= ($ad['device_target'] ?? '') === 'mobile' ? 'selected' : '' ?>>Mobile Only</option>
                    </select>
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Priority</label>
                    <input type="number" name="priority" value="<?= $ad['priority'] ?? 0 ?>" min="0"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <p class="mt-1 text-sm text-neutral-500">Higher priority ads shown more often</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Start Date -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Start Date (optional)</label>
                    <input type="datetime-local" name="start_date"
                           value="<?= !empty($ad['start_date']) ? date('Y-m-d\TH:i', strtotime($ad['start_date'])) : '' ?>"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">End Date (optional)</label>
                    <input type="datetime-local" name="end_date"
                           value="<?= !empty($ad['end_date']) ? date('Y-m-d\TH:i', strtotime($ad['end_date'])) : '' ?>"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <!-- Active -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($ad['is_active'] ?? 1) ? 'checked' : '' ?>
                           class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm font-medium text-neutral-700">Active</span>
                </label>
                <p class="mt-1 text-sm text-neutral-500 ml-6">Enable this ad to display on the site</p>
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-4 pt-4 border-t border-neutral-200">
                <button type="submit"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">
                    <?= $ad ? 'Update Ad' : 'Create Ad' ?>
                </button>
                <a href="/admin/marketing/ads" class="px-6 py-2 text-neutral-600 hover:text-neutral-800">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAdTypeFields() {
    const adType = document.getElementById('ad_type').value;
    const contentField = document.getElementById('content_field');
    const imageFields = document.getElementById('image_fields');

    if (adType === 'image') {
        contentField.classList.add('hidden');
        imageFields.classList.remove('hidden');
    } else {
        contentField.classList.remove('hidden');
        imageFields.classList.add('hidden');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', toggleAdTypeFields);
</script>
