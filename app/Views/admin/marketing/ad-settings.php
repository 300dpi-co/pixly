<?php $currentPage = 'marketing'; $currentTab = 'ad-settings'; ?>

<?php include __DIR__ . '/_subnav.php'; ?>

<?php if (session_get_flash('success')): ?>
<div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
    <?= session_get_flash('success') ?>
</div>
<?php endif; ?>

<div class="max-w-3xl space-y-6">
    <!-- Global Ad Settings -->
    <div class="bg-white rounded-xl shadow-sm border border-neutral-100 p-6">
        <h2 class="text-lg font-semibold text-neutral-900 mb-6">Global Ad Settings</h2>

        <form action="/admin/marketing/ad-settings" method="POST">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">

            <!-- Ads Enabled -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="ads_enabled" value="1"
                           <?= ($settings['ads_enabled'] ?? '1') === '1' ? 'checked' : '' ?>
                           class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm font-medium text-neutral-700">Enable Ads Globally</span>
                </label>
                <p class="mt-1 ml-6 text-sm text-neutral-500">Turn off to disable all ads site-wide</p>
            </div>

            <!-- Hide for Logged In -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="hide_ads_for_users" value="1"
                           <?= ($settings['hide_ads_for_users'] ?? '0') === '1' ? 'checked' : '' ?>
                           class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm font-medium text-neutral-700">Hide Ads for Logged-In Users</span>
                </label>
                <p class="mt-1 ml-6 text-sm text-neutral-500">Premium feature for registered members</p>
            </div>

            <!-- Ad Refresh -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Ad Refresh Interval (seconds)</label>
                <input type="number" name="ad_refresh_interval" value="<?= e($settings['ad_refresh_interval'] ?? '0') ?>" min="0"
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <p class="mt-1 text-sm text-neutral-500">0 = no refresh. Recommended: 30-60 seconds for max revenue</p>
            </div>

            <!-- Lazy Load Ads -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="lazy_load_ads" value="1"
                           <?= ($settings['lazy_load_ads'] ?? '1') === '1' ? 'checked' : '' ?>
                           class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm font-medium text-neutral-700">Lazy Load Ads</span>
                </label>
                <p class="mt-1 ml-6 text-sm text-neutral-500">Load ads only when they enter the viewport</p>
            </div>

            <!-- Submit -->
            <div class="pt-4 border-t border-neutral-200">
                <button type="submit"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Ad Network Credentials -->
    <div class="bg-white rounded-xl shadow-sm border border-neutral-100 p-6">
        <h2 class="text-lg font-semibold text-neutral-900 mb-6">Ad Network Credentials</h2>

        <form action="/admin/marketing/ad-settings/networks" method="POST">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">

            <!-- JuicyAds -->
            <div class="mb-6 p-4 border border-neutral-200 rounded-lg">
                <h3 class="font-medium text-neutral-900 mb-4 flex items-center">
                    <span class="w-3 h-3 bg-orange-500 rounded-full mr-2"></span>
                    JuicyAds
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Publisher ID</label>
                        <input type="text" name="juicyads_publisher_id" value="<?= e($settings['juicyads_publisher_id'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Site ID</label>
                        <input type="text" name="juicyads_site_id" value="<?= e($settings['juicyads_site_id'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                    </div>
                </div>
            </div>

            <!-- ExoClick -->
            <div class="mb-6 p-4 border border-neutral-200 rounded-lg">
                <h3 class="font-medium text-neutral-900 mb-4 flex items-center">
                    <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                    ExoClick
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Site ID</label>
                        <input type="text" name="exoclick_site_id" value="<?= e($settings['exoclick_site_id'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Zone ID</label>
                        <input type="text" name="exoclick_zone_id" value="<?= e($settings['exoclick_zone_id'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                    </div>
                </div>
            </div>

            <!-- TrafficJunky -->
            <div class="mb-6 p-4 border border-neutral-200 rounded-lg">
                <h3 class="font-medium text-neutral-900 mb-4 flex items-center">
                    <span class="w-3 h-3 bg-purple-500 rounded-full mr-2"></span>
                    TrafficJunky
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Publisher ID</label>
                        <input type="text" name="trafficjunky_publisher_id" value="<?= e($settings['trafficjunky_publisher_id'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Site ID</label>
                        <input type="text" name="trafficjunky_site_id" value="<?= e($settings['trafficjunky_site_id'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                    </div>
                </div>
            </div>

            <!-- Google AdSense -->
            <div class="mb-6 p-4 border border-neutral-200 rounded-lg">
                <h3 class="font-medium text-neutral-900 mb-4 flex items-center">
                    <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                    Google AdSense
                </h3>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Publisher ID (ca-pub-xxxxx)</label>
                    <input type="text" name="adsense_publisher_id" value="<?= e($settings['adsense_publisher_id'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"
                           placeholder="ca-pub-1234567890">
                </div>
            </div>

            <!-- Submit -->
            <div class="pt-4 border-t border-neutral-200">
                <button type="submit"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">
                    Save Network Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Blocked Pages -->
    <div class="bg-white rounded-xl shadow-sm border border-neutral-100 p-6">
        <h2 class="text-lg font-semibold text-neutral-900 mb-6">Blocked Pages</h2>

        <form action="/admin/marketing/ad-settings/blocked" method="POST">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">

            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Pages to Exclude from Ads</label>
                <textarea name="blocked_pages" rows="6"
                          class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono text-sm"
                          placeholder="/admin/*&#10;/login&#10;/register&#10;/legal/*"><?= e($settings['blocked_pages'] ?? "/admin/*\n/login\n/register") ?></textarea>
                <p class="mt-1 text-sm text-neutral-500">One path per line. Supports wildcards (*). Ads won't display on these pages.</p>
            </div>

            <!-- Submit -->
            <div class="pt-4 border-t border-neutral-200">
                <button type="submit"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">
                    Save Blocked Pages
                </button>
            </div>
        </form>
    </div>

    <!-- GDPR/Consent Settings -->
    <div class="bg-white rounded-xl shadow-sm border border-neutral-100 p-6">
        <h2 class="text-lg font-semibold text-neutral-900 mb-6">GDPR & Consent</h2>

        <form action="/admin/marketing/ad-settings/consent" method="POST">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">

            <!-- Cookie Consent -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="show_cookie_consent" value="1"
                           <?= ($settings['show_cookie_consent'] ?? '1') === '1' ? 'checked' : '' ?>
                           class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm font-medium text-neutral-700">Show Cookie Consent Banner</span>
                </label>
            </div>

            <!-- Consent Message -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Consent Message</label>
                <textarea name="cookie_consent_message" rows="3"
                          class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= e($settings['cookie_consent_message'] ?? 'We use cookies to enhance your experience and show personalized ads. By continuing, you agree to our use of cookies.') ?></textarea>
            </div>

            <!-- Privacy Policy Link -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Privacy Policy URL</label>
                <input type="url" name="privacy_policy_url" value="<?= e($settings['privacy_policy_url'] ?? '/privacy') ?>"
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <!-- Submit -->
            <div class="pt-4 border-t border-neutral-200">
                <button type="submit"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">
                    Save Consent Settings
                </button>
            </div>
        </form>
    </div>
</div>
