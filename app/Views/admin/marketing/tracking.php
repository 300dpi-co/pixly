<?php $currentPage = 'marketing'; $currentTab = 'tracking'; ?>

<?php include __DIR__ . '/_subnav.php'; ?>

<?php if (session_get_flash('success')): ?>
<div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
    <?= session_get_flash('success') ?>
</div>
<?php endif; ?>

<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-neutral-100 p-6">
        <h2 class="text-lg font-semibold text-neutral-900 mb-6">Tracking Codes</h2>

        <form action="/admin/marketing/tracking" method="POST">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">

            <!-- Google Analytics -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Google Analytics 4 ID</label>
                <input type="text" name="google_analytics_id" value="<?= e($settings['google_analytics_id'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="G-XXXXXXXXXX">
                <p class="mt-1 text-sm text-neutral-500">Your GA4 Measurement ID</p>
            </div>

            <!-- Google Tag Manager -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Google Tag Manager ID</label>
                <input type="text" name="gtm_id" value="<?= e($settings['gtm_id'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="GTM-XXXXXXX">
            </div>

            <!-- Facebook Pixel -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Facebook Pixel ID</label>
                <input type="text" name="facebook_pixel_id" value="<?= e($settings['facebook_pixel_id'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="1234567890">
            </div>

            <!-- Custom Head Scripts -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Custom Scripts (Head)</label>
                <textarea name="custom_head_scripts" rows="6"
                          class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono text-sm"
                          placeholder="<!-- Your scripts here -->"><?= e($settings['custom_head_scripts'] ?? '') ?></textarea>
                <p class="mt-1 text-sm text-neutral-500">These scripts will be added to the &lt;head&gt; section</p>
            </div>

            <!-- Custom Body Scripts -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Custom Scripts (Body End)</label>
                <textarea name="custom_body_scripts" rows="6"
                          class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono text-sm"
                          placeholder="<!-- Your scripts here -->"><?= e($settings['custom_body_scripts'] ?? '') ?></textarea>
                <p class="mt-1 text-sm text-neutral-500">These scripts will be added before &lt;/body&gt;</p>
            </div>

            <!-- Submit -->
            <div class="pt-4 border-t border-neutral-200">
                <button type="submit"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
