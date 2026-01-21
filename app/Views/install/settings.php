<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-6 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Site Settings</h1>
                <p class="text-sm text-slate-500">Configure your site's basic information</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="<?= url('/install/settings') ?>" method="POST" class="p-6">
        <?= csrf_field() ?>

        <?php if ($view->error('database')): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
            <?= e($view->error('database')) ?>
        </div>
        <?php endif; ?>

        <div class="space-y-5">
            <!-- Site Name -->
            <div>
                <label for="site_name" class="block text-sm font-medium text-slate-700 mb-1">Site Name</label>
                <input type="text" id="site_name" name="site_name"
                       value="<?= e(old('site_name', $siteSettings['site_name'] ?? 'FWP Image Gallery')) ?>"
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 <?= $view->error('site_name') ? 'border-red-300 bg-red-50' : '' ?>"
                       placeholder="My Image Gallery">
                <?php if ($view->error('site_name')): ?>
                <p class="mt-1 text-sm text-red-600"><?= e($view->error('site_name')) ?></p>
                <?php else: ?>
                <p class="mt-1 text-sm text-slate-500">The name of your site, displayed in the header and browser title.</p>
                <?php endif; ?>
            </div>

            <!-- Site URL -->
            <div>
                <label for="site_url" class="block text-sm font-medium text-slate-700 mb-1">Site URL</label>
                <input type="url" id="site_url" name="site_url"
                       value="<?= e(old('site_url', $siteSettings['site_url'] ?? $detectedUrl)) ?>"
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 <?= $view->error('site_url') ? 'border-red-300 bg-red-50' : '' ?>"
                       placeholder="https://example.com">
                <?php if ($view->error('site_url')): ?>
                <p class="mt-1 text-sm text-red-600"><?= e($view->error('site_url')) ?></p>
                <?php else: ?>
                <p class="mt-1 text-sm text-slate-500">The full URL of your site, without trailing slash.</p>
                <?php endif; ?>
            </div>

            <!-- Site Description -->
            <div>
                <label for="site_description" class="block text-sm font-medium text-slate-700 mb-1">Site Description</label>
                <textarea id="site_description" name="site_description" rows="3"
                          class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="A brief description of your site for search engines"><?= e(old('site_description', $siteSettings['site_description'] ?? 'Discover trending images, creative visuals, and inspiring content.')) ?></textarea>
                <p class="mt-1 text-sm text-slate-500">This will be used as the default meta description for SEO.</p>
            </div>

            <!-- Info Box -->
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h3 class="font-medium text-blue-800">More Settings Available</h3>
                        <p class="text-sm text-blue-700 mt-1">You can configure additional settings like theme colors, logo, social links, and more from the admin panel after installation.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-slate-200 flex justify-between items-center">
            <a href="<?= url('/install/admin') ?>" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-800 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
            <button type="submit" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg font-medium transition">
                Save & Complete Installation
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </form>
</div>
