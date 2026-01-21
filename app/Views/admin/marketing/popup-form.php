<?php $currentPage = 'marketing'; $currentTab = 'popups'; ?>

<?php include __DIR__ . '/_subnav.php'; ?>

<div class="max-w-3xl">
    <div class="mb-6">
        <a href="/admin/marketing/popups" class="text-sm text-neutral-500 hover:text-neutral-700 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Popups
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-neutral-100 p-6">
        <form action="<?= $popup ? '/admin/marketing/popups/' . $popup['id'] : '/admin/marketing/popups' ?>" method="POST">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">

            <!-- Name -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Name *</label>
                <input type="text" name="name" value="<?= e($popup['name'] ?? '') ?>" required
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <!-- Content -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Content (HTML) *</label>
                <textarea name="content" rows="10" required
                          class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono text-sm"><?= e($popup['content'] ?? '') ?></textarea>
                <p class="mt-1 text-sm text-neutral-500">HTML content of the popup. Can include ad codes, forms, or custom content.</p>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Trigger Type -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Trigger</label>
                    <select name="trigger_type" class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="page_load" <?= ($popup['trigger_type'] ?? '') === 'page_load' ? 'selected' : '' ?>>Page Load</option>
                        <option value="exit_intent" <?= ($popup['trigger_type'] ?? '') === 'exit_intent' ? 'selected' : '' ?>>Exit Intent</option>
                        <option value="scroll" <?= ($popup['trigger_type'] ?? '') === 'scroll' ? 'selected' : '' ?>>Scroll Percentage</option>
                        <option value="timed" <?= ($popup['trigger_type'] ?? '') === 'timed' ? 'selected' : '' ?>>Timed Delay</option>
                    </select>
                </div>

                <!-- Trigger Delay -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Delay (seconds)</label>
                    <input type="number" name="trigger_delay" value="<?= $popup['trigger_delay'] ?? 0 ?>" min="0"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Scroll Percent -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Scroll Trigger (%)</label>
                    <input type="number" name="trigger_scroll_percent" value="<?= $popup['trigger_scroll_percent'] ?? 50 ?>" min="0" max="100"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- Frequency -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Frequency</label>
                    <select name="frequency" class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="every_visit" <?= ($popup['frequency'] ?? '') === 'every_visit' ? 'selected' : '' ?>>Every Visit</option>
                        <option value="once_session" <?= ($popup['frequency'] ?? 'once_session') === 'once_session' ? 'selected' : '' ?>>Once Per Session</option>
                        <option value="once_day" <?= ($popup['frequency'] ?? '') === 'once_day' ? 'selected' : '' ?>>Once Per Day</option>
                        <option value="once_week" <?= ($popup['frequency'] ?? '') === 'once_week' ? 'selected' : '' ?>>Once Per Week</option>
                        <option value="once_ever" <?= ($popup['frequency'] ?? '') === 'once_ever' ? 'selected' : '' ?>>Once Ever</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-6 mb-6">
                <!-- Position -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Position</label>
                    <select name="position" class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="center" <?= ($popup['position'] ?? 'center') === 'center' ? 'selected' : '' ?>>Center</option>
                        <option value="top" <?= ($popup['position'] ?? '') === 'top' ? 'selected' : '' ?>>Top</option>
                        <option value="bottom" <?= ($popup['position'] ?? '') === 'bottom' ? 'selected' : '' ?>>Bottom</option>
                        <option value="bottom_right" <?= ($popup['position'] ?? '') === 'bottom_right' ? 'selected' : '' ?>>Bottom Right</option>
                        <option value="bottom_left" <?= ($popup['position'] ?? '') === 'bottom_left' ? 'selected' : '' ?>>Bottom Left</option>
                    </select>
                </div>

                <!-- Animation -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Animation</label>
                    <select name="animation" class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="fade" <?= ($popup['animation'] ?? 'fade') === 'fade' ? 'selected' : '' ?>>Fade</option>
                        <option value="slide" <?= ($popup['animation'] ?? '') === 'slide' ? 'selected' : '' ?>>Slide</option>
                        <option value="zoom" <?= ($popup['animation'] ?? '') === 'zoom' ? 'selected' : '' ?>>Zoom</option>
                        <option value="none" <?= ($popup['animation'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                    </select>
                </div>

                <!-- Width -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Width</label>
                    <input type="text" name="width" value="<?= e($popup['width'] ?? '500px') ?>"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Overlay Opacity -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Overlay Opacity (%)</label>
                    <input type="number" name="overlay_opacity" value="<?= $popup['overlay_opacity'] ?? 50 ?>" min="0" max="100"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- Cookie Days -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Cookie Duration (days)</label>
                    <input type="number" name="cookie_days" value="<?= $popup['cookie_days'] ?? 7 ?>" min="1"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <!-- Pages Exclude -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Exclude Pages</label>
                <?php $excludes = $popup ? json_decode($popup['pages_exclude'] ?? '[]', true) : []; ?>
                <textarea name="pages_exclude" rows="3"
                          class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono text-sm"
                          placeholder="/admin/*&#10;/login&#10;/register"><?= e(implode("\n", $excludes ?? [])) ?></textarea>
                <p class="mt-1 text-sm text-neutral-500">One path per line. Supports wildcards (*).</p>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Start Date -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Start Date</label>
                    <input type="datetime-local" name="start_date"
                           value="<?= !empty($popup['start_date']) ? date('Y-m-d\TH:i', strtotime($popup['start_date'])) : '' ?>"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">End Date</label>
                    <input type="datetime-local" name="end_date"
                           value="<?= !empty($popup['end_date']) ? date('Y-m-d\TH:i', strtotime($popup['end_date'])) : '' ?>"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <!-- Options -->
            <div class="mb-6 space-y-3">
                <label class="flex items-center">
                    <input type="checkbox" name="show_on_mobile" value="1"
                           <?= ($popup['show_on_mobile'] ?? 1) ? 'checked' : '' ?>
                           class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm text-neutral-700">Show on Mobile</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($popup['is_active'] ?? 0) ? 'checked' : '' ?>
                           class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm text-neutral-700">Active</span>
                </label>
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-4 pt-4 border-t border-neutral-200">
                <button type="submit"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">
                    <?= $popup ? 'Update Popup' : 'Create Popup' ?>
                </button>
                <a href="/admin/marketing/popups" class="px-6 py-2 text-neutral-600 hover:text-neutral-800">Cancel</a>
            </div>
        </form>
    </div>
</div>
