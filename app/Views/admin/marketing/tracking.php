<?php $currentPage = 'marketing'; $currentTab = 'tracking'; ?>

<?php include __DIR__ . '/_subnav.php'; ?>

<?php if (session_get_flash('success')): ?>
<div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
    <?= session_get_flash('success') ?>
</div>
<?php endif; ?>

<div class="max-w-3xl space-y-6">
    <!-- Site Verification -->
    <div class="bg-white rounded-xl shadow-sm border border-neutral-100 p-6">
        <h2 class="text-lg font-semibold text-neutral-900 mb-2">Site Verification</h2>
        <p class="text-sm text-neutral-500 mb-6">Add verification meta tags for search engines and social platforms. These will be added to the &lt;head&gt; section of every page.</p>

        <form action="/admin/marketing/tracking" method="POST">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="_section" value="verification">

            <!-- Google Search Console -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-neutral-700 mb-2">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        Google Search Console
                    </span>
                </label>
                <input type="text" name="verify_google" value="<?= e($settings['verify_google'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Enter verification code only (e.g., abc123def456)">
                <p class="mt-1 text-sm text-neutral-500">From: &lt;meta name="google-site-verification" content="<strong>THIS_CODE</strong>"&gt;</p>
            </div>

            <!-- Bing Webmaster -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-neutral-700 mb-2">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-600" viewBox="0 0 24 24" fill="currentColor"><path d="M5 3v16.5l4 2.5v-7l6 3.5 4-2.5V9L9 4.5V1L5 3z"/></svg>
                        Bing Webmaster Tools
                    </span>
                </label>
                <input type="text" name="verify_bing" value="<?= e($settings['verify_bing'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Enter verification code only">
                <p class="mt-1 text-sm text-neutral-500">From: &lt;meta name="msvalidate.01" content="<strong>THIS_CODE</strong>"&gt;</p>
            </div>

            <!-- Facebook Domain Verification -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-neutral-700 mb-2">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        Facebook Domain Verification
                    </span>
                </label>
                <input type="text" name="verify_facebook" value="<?= e($settings['verify_facebook'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Enter verification code only">
                <p class="mt-1 text-sm text-neutral-500">From: &lt;meta name="facebook-domain-verification" content="<strong>THIS_CODE</strong>"&gt;</p>
            </div>

            <!-- Pinterest -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-neutral-700 mb-2">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.372 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                        Pinterest
                    </span>
                </label>
                <input type="text" name="verify_pinterest" value="<?= e($settings['verify_pinterest'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Enter verification code only">
                <p class="mt-1 text-sm text-neutral-500">From: &lt;meta name="p:domain_verify" content="<strong>THIS_CODE</strong>"&gt;</p>
            </div>

            <!-- Yandex -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-neutral-700 mb-2">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-500" viewBox="0 0 24 24" fill="currentColor"><path d="M2 0h20v24H2V0zm11.5 5.5h-2.3v6.5H9.5L6 5.5H3.5l5 9v4h3v-4l5-9H14l-2.5 5-2.5-5z"/></svg>
                        Yandex Webmaster
                    </span>
                </label>
                <input type="text" name="verify_yandex" value="<?= e($settings['verify_yandex'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Enter verification code only">
                <p class="mt-1 text-sm text-neutral-500">From: &lt;meta name="yandex-verification" content="<strong>THIS_CODE</strong>"&gt;</p>
            </div>

            <!-- Submit -->
            <div class="pt-4 border-t border-neutral-200">
                <button type="submit"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">
                    Save Verification Tags
                </button>
            </div>
        </form>
    </div>

    <!-- Tracking Codes -->
    <div class="bg-white rounded-xl shadow-sm border border-neutral-100 p-6">
        <h2 class="text-lg font-semibold text-neutral-900 mb-6">Tracking Codes</h2>

        <form action="/admin/marketing/tracking" method="POST">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="_section" value="tracking">

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
                    Save Tracking Codes
                </button>
            </div>
        </form>
    </div>
</div>
