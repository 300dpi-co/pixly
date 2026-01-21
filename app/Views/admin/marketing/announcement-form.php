<?php $currentPage = 'marketing'; $currentTab = 'announcements'; ?>

<?php include __DIR__ . '/_subnav.php'; ?>

<div class="max-w-2xl">
    <div class="mb-6">
        <a href="/admin/marketing/announcements" class="text-sm text-neutral-500 hover:text-neutral-700 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Announcements
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-neutral-100 p-6">
        <form action="<?= $announcement ? '/admin/marketing/announcements/' . $announcement['id'] : '/admin/marketing/announcements' ?>" method="POST">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">

            <!-- Message -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Message *</label>
                <textarea name="message" rows="2" required
                          class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="Your announcement message..."><?= e($announcement['message'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Link URL -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Link URL</label>
                    <input type="url" name="link_url" value="<?= e($announcement['link_url'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="https://...">
                </div>

                <!-- Link Text -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Link Text</label>
                    <input type="text" name="link_text" value="<?= e($announcement['link_text'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Learn more">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Background Color -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Background Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="bg_color" value="<?= e($announcement['bg_color'] ?? '#3b82f6') ?>"
                               class="w-12 h-10 border border-neutral-300 rounded cursor-pointer">
                        <input type="text" value="<?= e($announcement['bg_color'] ?? '#3b82f6') ?>"
                               class="flex-1 px-4 py-2 border border-neutral-300 rounded-lg bg-neutral-50"
                               readonly>
                    </div>
                </div>

                <!-- Text Color -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Text Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="text_color" value="<?= e($announcement['text_color'] ?? '#ffffff') ?>"
                               class="w-12 h-10 border border-neutral-300 rounded cursor-pointer">
                        <input type="text" value="<?= e($announcement['text_color'] ?? '#ffffff') ?>"
                               class="flex-1 px-4 py-2 border border-neutral-300 rounded-lg bg-neutral-50"
                               readonly>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Position -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Position</label>
                    <select name="position" class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="top" <?= ($announcement['position'] ?? 'top') === 'top' ? 'selected' : '' ?>>Top</option>
                        <option value="bottom" <?= ($announcement['position'] ?? '') === 'bottom' ? 'selected' : '' ?>>Bottom</option>
                    </select>
                </div>

                <!-- Cookie Days -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Dismiss Duration (days)</label>
                    <input type="number" name="cookie_days" value="<?= $announcement['cookie_days'] ?? 1 ?>" min="1"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Start Date -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Start Date</label>
                    <input type="datetime-local" name="start_date"
                           value="<?= isset($announcement['start_date']) && $announcement['start_date'] ? date('Y-m-d\TH:i', strtotime($announcement['start_date'])) : '' ?>"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">End Date</label>
                    <input type="datetime-local" name="end_date"
                           value="<?= isset($announcement['end_date']) && $announcement['end_date'] ? date('Y-m-d\TH:i', strtotime($announcement['end_date'])) : '' ?>"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <!-- Options -->
            <div class="mb-6 space-y-3">
                <label class="flex items-center">
                    <input type="checkbox" name="is_dismissible" value="1"
                           <?= ($announcement['is_dismissible'] ?? 1) ? 'checked' : '' ?>
                           class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm text-neutral-700">Allow users to dismiss</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="is_sticky" value="1"
                           <?= ($announcement['is_sticky'] ?? 0) ? 'checked' : '' ?>
                           class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm text-neutral-700">Sticky (stays visible on scroll)</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($announcement['is_active'] ?? 0) ? 'checked' : '' ?>
                           class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm text-neutral-700">Active</span>
                </label>
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-4 pt-4 border-t border-neutral-200">
                <button type="submit"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">
                    <?= $announcement ? 'Update Announcement' : 'Create Announcement' ?>
                </button>
                <a href="/admin/marketing/announcements" class="px-6 py-2 text-neutral-600 hover:text-neutral-800">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Sync color inputs with text display
document.querySelectorAll('input[type="color"]').forEach(input => {
    input.addEventListener('input', function() {
        this.nextElementSibling.value = this.value;
    });
});
</script>
