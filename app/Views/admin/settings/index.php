<div class="flex flex-col lg:flex-row gap-6">
    <!-- Sidebar Navigation -->
    <div class="lg:w-64 flex-shrink-0">
        <div class="bg-white rounded-lg shadow p-4">
            <nav class="space-y-1">
                <?php foreach ($groupedSettings as $groupKey => $group): ?>
                    <?php if (!empty($group['settings'])): ?>
                    <a href="?group=<?= $groupKey ?>"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $activeGroup === $groupKey ? 'bg-primary-50 text-primary-700' : 'text-neutral-600 hover:bg-neutral-50' ?>">
                        <?php
                        $iconMap = [
                            'toggle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>',
                            'adjustments' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>',
                            'color-swatch' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>',
                            'cog' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
                            'photograph' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
                            'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
                            'chat' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
                            'search' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
                            'key' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>',
                            'currency-dollar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                            'dots-horizontal' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/>',
                        ];
                        $icon = $iconMap[$group['icon']] ?? $iconMap['cog'];
                        ?>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $icon ?></svg>
                        <?= e($group['label']) ?>
                    </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>

        <div class="mt-4">
            <a href="/admin/settings/create" class="flex items-center gap-2 px-4 py-2 text-sm text-neutral-600 hover:text-primary-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Custom Setting
            </a>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="flex-1">
        <?php $currentGroup = $groupedSettings[$activeGroup] ?? reset($groupedSettings); ?>

        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h1 class="text-xl font-semibold"><?= e($currentGroup['label']) ?> Settings</h1>
            </div>

            <?php if ($activeGroup === 'features'): ?>
                <!-- Features Settings - Custom UI -->
                <?php
                $featureSettings = [];
                foreach ($currentGroup['settings'] as $s) {
                    $featureSettings[$s['setting_key']] = $s['setting_value'];
                }
                ?>

                <form method="POST" action="/admin/settings" class="p-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_group" value="features">

                    <div class="space-y-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <p class="text-sm text-blue-800">
                                <strong>Feature Toggles:</strong> Control which features are available on your site. Disabling a feature will hide related UI elements and block access to related endpoints.
                            </p>
                        </div>

                        <!-- Premium Feature -->
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-neutral-900">Premium Subscriptions</h4>
                                    <p class="text-sm text-neutral-600">Enable premium subscription feature with ad-free experience and additional benefits.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="premium_enabled" value="0">
                                    <input type="checkbox" name="premium_enabled" value="1"
                                           <?= ($featureSettings['premium_enabled'] ?? '1') === '1' ? 'checked' : '' ?>
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-neutral-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                </label>
                            </div>
                            <p class="text-xs text-neutral-500 mt-2">When OFF: Premium page hidden, nav link removed, upgrade prompts hidden.</p>
                        </div>

                        <!-- Registration -->
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-neutral-900">User Registration</h4>
                                    <p class="text-sm text-neutral-600">Allow new users to create accounts on your site.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="registration_enabled" value="0">
                                    <input type="checkbox" name="registration_enabled" value="1"
                                           <?= ($featureSettings['registration_enabled'] ?? '1') === '1' ? 'checked' : '' ?>
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-neutral-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                </label>
                            </div>
                            <p class="text-xs text-neutral-500 mt-2">When OFF: Register link hidden, /register page blocked.</p>
                        </div>

                        <!-- Contributor System -->
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-neutral-900">Contributor System</h4>
                                    <p class="text-sm text-neutral-600">Enable the contributor role system. Users can request to become contributors and upload images after approval.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="contributor_system_enabled" value="0">
                                    <input type="checkbox" name="contributor_system_enabled" value="1"
                                           <?= ($featureSettings['contributor_system_enabled'] ?? '0') === '1' ? 'checked' : '' ?>
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-neutral-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                </label>
                            </div>
                            <p class="text-xs text-neutral-500 mt-2">When OFF: All users treated as regular users, contributor features hidden. When ON: Users can apply to become contributors.</p>
                        </div>

                        <!-- Appreciate System -->
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-neutral-900">Appreciate System</h4>
                                    <p class="text-sm text-neutral-600">Enable the appreciate button on images (Pexels-style theme feature).</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="appreciate_system_enabled" value="0">
                                    <input type="checkbox" name="appreciate_system_enabled" value="1"
                                           <?= ($featureSettings['appreciate_system_enabled'] ?? '1') === '1' ? 'checked' : '' ?>
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-neutral-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                </label>
                            </div>
                            <p class="text-xs text-neutral-500 mt-2">When OFF: Appreciate buttons hidden, API endpoint disabled, appreciation counts not shown.</p>
                        </div>
                    </div>

                    <div class="pt-6 mt-6 border-t">
                        <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            Save Settings
                        </button>
                    </div>
                </form>

            <?php elseif ($activeGroup === 'site_mode'): ?>
                <!-- Site Mode Settings - Custom UI -->
                <?php
                // Extract setting values
                $siteSettings = [];
                foreach ($currentGroup['settings'] as $s) {
                    $siteSettings[$s['setting_key']] = $s['setting_value'];
                }
                $adultMode = ($siteSettings['adult_mode_enabled'] ?? '0') === '1';
                $currentLayout = $siteSettings['layout_preset'] ?? 'clean-minimal';
                ?>

                <form method="POST" action="/admin/settings" class="p-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_group" value="site_mode">

                    <!-- Layout Preset Selection -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-neutral-900 mb-4">Layout Preset</h3>
                        <p class="text-sm text-neutral-600 mb-4">Choose a visual style for your site. Adult-oriented layouts appear when Adult Mode is enabled.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="layoutPresets">
                            <!-- General Layouts -->
                            <label class="layout-preset cursor-pointer">
                                <input type="radio" name="layout_preset" value="clean-minimal" class="sr-only" <?= $currentLayout === 'clean-minimal' ? 'checked' : '' ?>>
                                <div class="border-2 rounded-lg p-4 transition <?= $currentLayout === 'clean-minimal' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' ?>">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded bg-gradient-to-br from-sky-400 to-blue-500"></div>
                                        <span class="font-medium">Clean Minimal</span>
                                    </div>
                                    <p class="text-sm text-neutral-600">Light, professional, modern design. Great for portfolios and stock photo sites.</p>
                                </div>
                            </label>

                            <label class="layout-preset cursor-pointer">
                                <input type="radio" name="layout_preset" value="magazine-grid" class="sr-only" <?= $currentLayout === 'magazine-grid' ? 'checked' : '' ?>>
                                <div class="border-2 rounded-lg p-4 transition <?= $currentLayout === 'magazine-grid' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' ?>">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded bg-gradient-to-br from-pink-400 to-rose-500"></div>
                                        <span class="font-medium">Magazine Grid</span>
                                    </div>
                                    <p class="text-sm text-neutral-600">Pinterest-style masonry layout. Perfect for mixed-size images and visual discovery.</p>
                                </div>
                            </label>

                            <label class="layout-preset cursor-pointer">
                                <input type="radio" name="layout_preset" value="bold-modern" class="sr-only" <?= $currentLayout === 'bold-modern' ? 'checked' : '' ?>>
                                <div class="border-2 rounded-lg p-4 transition <?= $currentLayout === 'bold-modern' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' ?>">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded bg-gradient-to-br from-violet-400 to-purple-500"></div>
                                        <span class="font-medium">Bold Modern</span>
                                    </div>
                                    <p class="text-sm text-neutral-600">Large cards, prominent titles, high contrast. Great for featured content.</p>
                                </div>
                            </label>

                            <label class="layout-preset cursor-pointer">
                                <input type="radio" name="layout_preset" value="pexels-stock" class="sr-only" <?= $currentLayout === 'pexels-stock' ? 'checked' : '' ?>>
                                <div class="border-2 rounded-lg p-4 transition <?= $currentLayout === 'pexels-stock' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' ?>">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded bg-gradient-to-br from-emerald-400 to-teal-600"></div>
                                        <span class="font-medium">Pexels Stock</span>
                                        <span class="text-xs px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded">Premium</span>
                                    </div>
                                    <p class="text-sm text-neutral-600">Professional stock photo style with masonry grid, collections, and modern UI. Pexels-inspired.</p>
                                </div>
                            </label>

                            <!-- Adult Layouts (shown when adult mode enabled) -->
                            <label class="layout-preset cursor-pointer adult-only-option <?= $adultMode ? '' : 'hidden opacity-50' ?>">
                                <input type="radio" name="layout_preset" value="dark-cinematic" class="sr-only" <?= $currentLayout === 'dark-cinematic' ? 'checked' : '' ?>>
                                <div class="border-2 rounded-lg p-4 transition <?= $currentLayout === 'dark-cinematic' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' ?>">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded bg-gradient-to-br from-neutral-700 to-neutral-900"></div>
                                        <span class="font-medium">Dark Cinematic</span>
                                        <span class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded">18+</span>
                                    </div>
                                    <p class="text-sm text-neutral-600">Deep blacks, subtle gradients, dramatic hover effects. Elegant and immersive.</p>
                                </div>
                            </label>

                            <label class="layout-preset cursor-pointer adult-only-option <?= $adultMode ? '' : 'hidden opacity-50' ?>">
                                <input type="radio" name="layout_preset" value="neon-nights" class="sr-only" <?= $currentLayout === 'neon-nights' ? 'checked' : '' ?>>
                                <div class="border-2 rounded-lg p-4 transition <?= $currentLayout === 'neon-nights' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' ?>">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded bg-gradient-to-br from-fuchsia-500 to-purple-900"></div>
                                        <span class="font-medium">Neon Nights</span>
                                        <span class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded">18+</span>
                                    </div>
                                    <p class="text-sm text-neutral-600">Dark theme with vibrant neon accents. Bold, edgy, attention-grabbing.</p>
                                </div>
                            </label>

                            <label class="layout-preset cursor-pointer adult-only-option <?= $adultMode ? '' : 'hidden opacity-50' ?>">
                                <input type="radio" name="layout_preset" value="premium-luxury" class="sr-only" <?= $currentLayout === 'premium-luxury' ? 'checked' : '' ?>>
                                <div class="border-2 rounded-lg p-4 transition <?= $currentLayout === 'premium-luxury' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' ?>">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded bg-gradient-to-br from-amber-400 to-yellow-600"></div>
                                        <span class="font-medium">Premium Luxury</span>
                                        <span class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded">18+</span>
                                    </div>
                                    <p class="text-sm text-neutral-600">Dark with gold/champagne accents. Sophisticated, premium feel.</p>
                                </div>
                            </label>

                            <label class="layout-preset cursor-pointer adult-only-option <?= $adultMode ? '' : 'hidden opacity-50' ?>">
                                <input type="radio" name="layout_preset" value="minimal-dark" class="sr-only" <?= $currentLayout === 'minimal-dark' ? 'checked' : '' ?>>
                                <div class="border-2 rounded-lg p-4 transition <?= $currentLayout === 'minimal-dark' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' ?>">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded bg-gradient-to-br from-neutral-800 to-black"></div>
                                        <span class="font-medium">Minimal Dark</span>
                                        <span class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded">18+</span>
                                    </div>
                                    <p class="text-sm text-neutral-600">Clean, distraction-free dark theme. Focus on content, nothing else.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Adult Mode Toggle -->
                    <div class="border-t pt-6 mb-6">
                        <div class="flex items-start gap-4 p-4 bg-neutral-50 rounded-lg">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-neutral-900">Adult Content Mode</h3>
                                <p class="text-sm text-neutral-600 mt-1">Enable adult-oriented features including age verification, NSFW blur, and adult-themed layouts.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="adult_mode_enabled" value="0">
                                <input type="checkbox" name="adult_mode_enabled" value="1" id="adultModeToggle"
                                       <?= $adultMode ? 'checked' : '' ?>
                                       class="sr-only peer">
                                <div class="w-14 h-7 bg-neutral-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-red-600"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Adult Features (shown when adult mode enabled) -->
                    <div id="adultFeaturesPanel" class="<?= $adultMode ? '' : 'hidden' ?> space-y-6">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                            <p class="text-sm text-red-800">
                                <strong>Note:</strong> Adult mode features are designed for legal adult content websites. Ensure your content complies with all applicable laws in your jurisdiction.
                            </p>
                        </div>

                        <!-- Age Verification -->
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="font-semibold text-neutral-900">Age Verification Gate</h4>
                                    <p class="text-sm text-neutral-600">Require visitors to confirm their age before accessing content.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="age_gate_enabled" value="0">
                                    <input type="checkbox" name="age_gate_enabled" value="1"
                                           <?= ($siteSettings['age_gate_enabled'] ?? '0') === '1' ? 'checked' : '' ?>
                                           class="sr-only peer feature-toggle" data-target="ageGateOptions">
                                    <div class="w-11 h-6 bg-neutral-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                </label>
                            </div>
                            <div id="ageGateOptions" class="<?= ($siteSettings['age_gate_enabled'] ?? '0') === '1' ? '' : 'hidden' ?> pt-4 border-t space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 mb-1">Gate Style</label>
                                        <select name="age_gate_style" class="w-full px-3 py-2 border rounded-lg">
                                            <option value="modal" <?= ($siteSettings['age_gate_style'] ?? 'modal') === 'modal' ? 'selected' : '' ?>>Modal Popup</option>
                                            <option value="fullpage" <?= ($siteSettings['age_gate_style'] ?? '') === 'fullpage' ? 'selected' : '' ?>>Full Page</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 mb-1">Minimum Age</label>
                                        <select name="age_gate_min_age" class="w-full px-3 py-2 border rounded-lg">
                                            <option value="18" <?= ($siteSettings['age_gate_min_age'] ?? '18') === '18' ? 'selected' : '' ?>>18 years</option>
                                            <option value="21" <?= ($siteSettings['age_gate_min_age'] ?? '') === '21' ? 'selected' : '' ?>>21 years</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 mb-1">Remember Verification</label>
                                        <select name="age_gate_remember" class="w-full px-3 py-2 border rounded-lg">
                                            <option value="session" <?= ($siteSettings['age_gate_remember'] ?? '') === 'session' ? 'selected' : '' ?>>This session only</option>
                                            <option value="24h" <?= ($siteSettings['age_gate_remember'] ?? '') === '24h' ? 'selected' : '' ?>>24 hours</option>
                                            <option value="7d" <?= ($siteSettings['age_gate_remember'] ?? '7d') === '7d' ? 'selected' : '' ?>>7 days</option>
                                            <option value="30d" <?= ($siteSettings['age_gate_remember'] ?? '') === '30d' ? 'selected' : '' ?>>30 days</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-1">Gate Title</label>
                                    <input type="text" name="age_gate_title" value="<?= e($siteSettings['age_gate_title'] ?? 'Age Verification Required') ?>"
                                           class="w-full px-3 py-2 border rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-1">Gate Message</label>
                                    <textarea name="age_gate_message" rows="2" class="w-full px-3 py-2 border rounded-lg"><?= e($siteSettings['age_gate_message'] ?? 'This website contains age-restricted content. By entering, you confirm that you are at least 18 years old.') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- NSFW Blur -->
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="font-semibold text-neutral-900">NSFW Thumbnail Blur</h4>
                                    <p class="text-sm text-neutral-600">Blur image thumbnails until user interaction reveals them.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="nsfw_blur_enabled" value="0">
                                    <input type="checkbox" name="nsfw_blur_enabled" value="1"
                                           <?= ($siteSettings['nsfw_blur_enabled'] ?? '0') === '1' ? 'checked' : '' ?>
                                           class="sr-only peer feature-toggle" data-target="nsfwBlurOptions">
                                    <div class="w-11 h-6 bg-neutral-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                </label>
                            </div>
                            <div id="nsfwBlurOptions" class="<?= ($siteSettings['nsfw_blur_enabled'] ?? '0') === '1' ? '' : 'hidden' ?> pt-4 border-t space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 mb-1">Blur Strength</label>
                                        <input type="range" name="nsfw_blur_strength" min="10" max="50" value="<?= e($siteSettings['nsfw_blur_strength'] ?? '20') ?>"
                                               class="w-full" id="blurStrengthSlider">
                                        <div class="flex justify-between text-xs text-neutral-500 mt-1">
                                            <span>Light (10px)</span>
                                            <span id="blurStrengthValue"><?= e($siteSettings['nsfw_blur_strength'] ?? '20') ?>px</span>
                                            <span>Heavy (50px)</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 mb-1">Reveal On</label>
                                        <select name="nsfw_reveal_on" class="w-full px-3 py-2 border rounded-lg">
                                            <option value="click" <?= ($siteSettings['nsfw_reveal_on'] ?? 'click') === 'click' ? 'selected' : '' ?>>Click</option>
                                            <option value="hover" <?= ($siteSettings['nsfw_reveal_on'] ?? '') === 'hover' ? 'selected' : '' ?>>Hover</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Exit -->
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="font-semibold text-neutral-900">Quick Exit Button</h4>
                                    <p class="text-sm text-neutral-600">Floating button that instantly redirects to a safe website.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="quick_exit_enabled" value="0">
                                    <input type="checkbox" name="quick_exit_enabled" value="1"
                                           <?= ($siteSettings['quick_exit_enabled'] ?? '0') === '1' ? 'checked' : '' ?>
                                           class="sr-only peer feature-toggle" data-target="quickExitOptions">
                                    <div class="w-11 h-6 bg-neutral-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                </label>
                            </div>
                            <div id="quickExitOptions" class="<?= ($siteSettings['quick_exit_enabled'] ?? '0') === '1' ? '' : 'hidden' ?> pt-4 border-t space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 mb-1">Exit URL</label>
                                        <input type="url" name="quick_exit_url" value="<?= e($siteSettings['quick_exit_url'] ?? 'https://www.google.com') ?>"
                                               class="w-full px-3 py-2 border rounded-lg" placeholder="https://www.google.com">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 mb-1">Button Text</label>
                                        <input type="text" name="quick_exit_text" value="<?= e($siteSettings['quick_exit_text'] ?? 'Exit') ?>"
                                               class="w-full px-3 py-2 border rounded-lg" placeholder="Exit">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Content Warning -->
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="font-semibold text-neutral-900">Content Warnings</h4>
                                    <p class="text-sm text-neutral-600">Show warning labels on sensitive content categories.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="content_warning_enabled" value="0">
                                    <input type="checkbox" name="content_warning_enabled" value="1"
                                           <?= ($siteSettings['content_warning_enabled'] ?? '0') === '1' ? 'checked' : '' ?>
                                           class="sr-only peer feature-toggle" data-target="contentWarningOptions">
                                    <div class="w-11 h-6 bg-neutral-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                </label>
                            </div>
                            <div id="contentWarningOptions" class="<?= ($siteSettings['content_warning_enabled'] ?? '0') === '1' ? '' : 'hidden' ?> pt-4 border-t">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-1">Warning Text</label>
                                    <input type="text" name="content_warning_text" value="<?= e($siteSettings['content_warning_text'] ?? 'This content may contain adult material.') ?>"
                                           class="w-full px-3 py-2 border rounded-lg">
                                </div>
                            </div>
                        </div>

                        <!-- Security & Privacy -->
                        <div class="border rounded-lg p-4">
                            <h4 class="font-semibold text-neutral-900 mb-4">Security & Privacy</h4>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-neutral-800">Disable Right-Click</p>
                                        <p class="text-sm text-neutral-600">Prevent right-click context menu (basic protection)</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="hidden" name="right_click_disabled" value="0">
                                        <input type="checkbox" name="right_click_disabled" value="1"
                                               <?= ($siteSettings['right_click_disabled'] ?? '0') === '1' ? 'checked' : '' ?>
                                               class="sr-only peer">
                                        <div class="w-11 h-6 bg-neutral-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-neutral-800">Private Browsing Notice</p>
                                        <p class="text-sm text-neutral-600">Suggest visitors use private/incognito mode</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="hidden" name="private_browsing_notice" value="0">
                                        <input type="checkbox" name="private_browsing_notice" value="1"
                                               <?= ($siteSettings['private_browsing_notice'] ?? '0') === '1' ? 'checked' : '' ?>
                                               class="sr-only peer">
                                        <div class="w-11 h-6 bg-neutral-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Legal Compliance -->
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="font-semibold text-neutral-900">Legal Disclaimer</h4>
                                    <p class="text-sm text-neutral-600">Display legal disclaimer in footer.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="disclaimer_enabled" value="0">
                                    <input type="checkbox" name="disclaimer_enabled" value="1"
                                           <?= ($siteSettings['disclaimer_enabled'] ?? '0') === '1' ? 'checked' : '' ?>
                                           class="sr-only peer feature-toggle" data-target="disclaimerOptions">
                                    <div class="w-11 h-6 bg-neutral-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                </label>
                            </div>
                            <div id="disclaimerOptions" class="<?= ($siteSettings['disclaimer_enabled'] ?? '0') === '1' ? '' : 'hidden' ?> pt-4 border-t space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-1">Disclaimer Text</label>
                                    <textarea name="disclaimer_text" rows="2" class="w-full px-3 py-2 border rounded-lg"><?= e($siteSettings['disclaimer_text'] ?? 'All models are 18 years of age or older. All content complies with applicable laws.') ?></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-1">18 U.S.C. 2257 Compliance URL (optional)</label>
                                    <input type="text" name="compliance_2257_url" value="<?= e($siteSettings['compliance_2257_url'] ?? '') ?>"
                                           class="w-full px-3 py-2 border rounded-lg" placeholder="/2257 or full URL">
                                    <p class="text-xs text-neutral-500 mt-1">Link to your 2257 compliance statement page (US requirement for adult content)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 mt-6 border-t">
                        <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            Save Settings
                        </button>
                    </div>
                </form>

                <script>
                // Toggle adult mode features visibility
                document.getElementById('adultModeToggle').addEventListener('change', function() {
                    const panel = document.getElementById('adultFeaturesPanel');
                    const adultOnlyOptions = document.querySelectorAll('.adult-only-option');

                    if (this.checked) {
                        panel.classList.remove('hidden');
                        adultOnlyOptions.forEach(el => el.classList.remove('hidden', 'opacity-50'));
                    } else {
                        panel.classList.add('hidden');
                        adultOnlyOptions.forEach(el => el.classList.add('hidden', 'opacity-50'));
                        // Reset adult layout selection to general layout
                        const generalLayout = document.querySelector('input[name="layout_preset"][value="clean-minimal"]');
                        if (generalLayout) generalLayout.checked = true;
                        updateLayoutPresetStyles();
                    }
                });

                // Toggle feature options visibility
                document.querySelectorAll('.feature-toggle').forEach(toggle => {
                    toggle.addEventListener('change', function() {
                        const target = document.getElementById(this.dataset.target);
                        if (target) {
                            target.classList.toggle('hidden', !this.checked);
                        }
                    });
                });

                // Update layout preset styling on selection
                function updateLayoutPresetStyles() {
                    document.querySelectorAll('.layout-preset').forEach(label => {
                        const radio = label.querySelector('input[type="radio"]');
                        const div = label.querySelector('div');
                        if (radio.checked) {
                            div.classList.add('border-primary-500', 'bg-primary-50');
                            div.classList.remove('border-neutral-200');
                        } else {
                            div.classList.remove('border-primary-500', 'bg-primary-50');
                            div.classList.add('border-neutral-200');
                        }
                    });
                }

                document.querySelectorAll('.layout-preset input[type="radio"]').forEach(radio => {
                    radio.addEventListener('change', updateLayoutPresetStyles);
                });

                // Blur strength slider
                document.getElementById('blurStrengthSlider')?.addEventListener('input', function() {
                    document.getElementById('blurStrengthValue').textContent = this.value + 'px';
                });
                </script>

            <?php elseif ($activeGroup === 'branding'): ?>
                <!-- Branding Settings - Logo Upload -->
                <div class="p-6 space-y-6">
                    <!-- Logo Upload -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-3">Site Logo</label>
                        <?php
                        $currentLogo = null;
                        foreach ($currentGroup['settings'] as $s) {
                            if ($s['setting_key'] === 'site_logo' && !empty($s['setting_value'])) {
                                $currentLogo = $s['setting_value'];
                            }
                        }
                        ?>

                        <?php if ($currentLogo): ?>
                        <div class="mb-4 p-4 bg-neutral-50 rounded-lg">
                            <p class="text-sm text-neutral-600 mb-2">Current Logo:</p>
                            <div class="flex items-center gap-4">
                                <img src="/uploads/<?= e($currentLogo) ?>" alt="Current Logo" class="h-12 object-contain bg-white border rounded p-2">
                                <form method="POST" action="/admin/settings/logo/delete" class="inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" onclick="return confirm('Remove logo?')"
                                            class="text-red-600 hover:text-red-700 text-sm">
                                        Remove Logo
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="/admin/settings/logo/upload" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="flex items-end gap-4">
                                <div class="flex-1">
                                    <input type="file" name="logo" accept="image/*"
                                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <p class="text-neutral-500 text-sm mt-1">Recommended: PNG or SVG with transparent background. Max 2MB.</p>
                                </div>
                                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                                    Upload Logo
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Logo Height & Color Theme Settings -->
                    <form method="POST" action="/admin/settings">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_group" value="branding">

                        <div class="space-y-6">
                            <!-- Logo Height -->
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-1">Logo Height (pixels)</label>
                                <?php
                                $logoHeight = '40';
                                $darkModeToggleEnabled = '1';
                                $primaryColor = '#0284c7';
                                $secondaryColor = '#6366f1';
                                $accentColor = '#f59e0b';
                                foreach ($currentGroup['settings'] as $s) {
                                    if ($s['setting_key'] === 'logo_height') $logoHeight = $s['setting_value'] ?: '40';
                                    if ($s['setting_key'] === 'dark_mode_toggle_enabled') $darkModeToggleEnabled = $s['setting_value'] ?? '1';
                                    if ($s['setting_key'] === 'primary_color') $primaryColor = $s['setting_value'] ?: '#0284c7';
                                    if ($s['setting_key'] === 'secondary_color') $secondaryColor = $s['setting_value'] ?: '#6366f1';
                                    if ($s['setting_key'] === 'accent_color') $accentColor = $s['setting_value'] ?: '#f59e0b';
                                }
                                ?>
                                <input type="number" name="logo_height" value="<?= e($logoHeight) ?>" min="20" max="100"
                                       class="w-32 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <p class="text-neutral-500 text-sm mt-1">Fixed height for logo in header (20-100px). Width auto-scales.</p>
                            </div>

                            <!-- Dark Mode Toggle -->
                            <div class="border-t pt-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700">Dark Mode Toggle</label>
                                        <p class="text-neutral-500 text-sm mt-1">Show light/dark mode toggle button in the header for visitors</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="hidden" name="dark_mode_toggle_enabled" value="0">
                                        <input type="checkbox" name="dark_mode_toggle_enabled" value="1"
                                               <?= $darkModeToggleEnabled === '1' ? 'checked' : '' ?>
                                               class="sr-only peer">
                                        <div class="w-11 h-6 bg-neutral-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                    </label>
                                </div>
                            </div>

                            <!-- Color Theme Section -->
                            <div class="border-t pt-6">
                                <h3 class="text-lg font-medium text-neutral-900 mb-4">Color Theme</h3>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                                    <!-- Primary Color -->
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 mb-2">Primary Color</label>
                                        <div class="flex items-center gap-3">
                                            <input type="color" name="primary_color" id="primary_color" value="<?= e($primaryColor) ?>"
                                                   class="w-12 h-12 rounded-lg border cursor-pointer">
                                            <div>
                                                <input type="text" id="primary_color_text" value="<?= e($primaryColor) ?>"
                                                       class="w-28 px-3 py-1 border rounded text-sm font-mono"
                                                       pattern="^#[0-9A-Fa-f]{6}$">
                                                <p class="text-xs text-neutral-500 mt-1">Buttons, links, header</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Secondary Color -->
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 mb-2">Secondary Color</label>
                                        <div class="flex items-center gap-3">
                                            <input type="color" name="secondary_color" id="secondary_color" value="<?= e($secondaryColor) ?>"
                                                   class="w-12 h-12 rounded-lg border cursor-pointer">
                                            <div>
                                                <input type="text" id="secondary_color_text" value="<?= e($secondaryColor) ?>"
                                                       class="w-28 px-3 py-1 border rounded text-sm font-mono"
                                                       pattern="^#[0-9A-Fa-f]{6}$">
                                                <p class="text-xs text-neutral-500 mt-1">Tags, badges, accents</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Accent Color -->
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 mb-2">Accent Color</label>
                                        <div class="flex items-center gap-3">
                                            <input type="color" name="accent_color" id="accent_color" value="<?= e($accentColor) ?>"
                                                   class="w-12 h-12 rounded-lg border cursor-pointer">
                                            <div>
                                                <input type="text" id="accent_color_text" value="<?= e($accentColor) ?>"
                                                       class="w-28 px-3 py-1 border rounded text-sm font-mono"
                                                       pattern="^#[0-9A-Fa-f]{6}$">
                                                <p class="text-xs text-neutral-500 mt-1">Highlights, warnings</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Color Preview -->
                                <div class="mt-6 p-4 bg-neutral-50 rounded-lg">
                                    <p class="text-sm font-medium text-neutral-700 mb-3">Preview</p>
                                    <div class="flex flex-wrap gap-3">
                                        <button type="button" id="preview_primary" style="background-color: <?= e($primaryColor) ?>"
                                                class="px-4 py-2 text-white rounded-lg text-sm">Primary Button</button>
                                        <button type="button" id="preview_secondary" style="background-color: <?= e($secondaryColor) ?>"
                                                class="px-4 py-2 text-white rounded-lg text-sm">Secondary</button>
                                        <span id="preview_accent" style="background-color: <?= e($accentColor) ?>"
                                              class="px-3 py-1 text-white rounded-full text-sm">Accent Tag</span>
                                    </div>
                                </div>

                                <!-- Preset Themes -->
                                <div class="mt-4">
                                    <p class="text-sm font-medium text-neutral-700 mb-2">Quick Presets</p>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" onclick="applyPreset('#0284c7', '#6366f1', '#f59e0b')"
                                                class="px-3 py-1 border rounded-full text-sm hover:bg-neutral-100">
                                            <span class="inline-block w-3 h-3 rounded-full mr-1" style="background:#0284c7"></span>Default Blue
                                        </button>
                                        <button type="button" onclick="applyPreset('#059669', '#10b981', '#f59e0b')"
                                                class="px-3 py-1 border rounded-full text-sm hover:bg-neutral-100">
                                            <span class="inline-block w-3 h-3 rounded-full mr-1" style="background:#059669"></span>Green
                                        </button>
                                        <button type="button" onclick="applyPreset('#7c3aed', '#8b5cf6', '#ec4899')"
                                                class="px-3 py-1 border rounded-full text-sm hover:bg-neutral-100">
                                            <span class="inline-block w-3 h-3 rounded-full mr-1" style="background:#7c3aed"></span>Purple
                                        </button>
                                        <button type="button" onclick="applyPreset('#dc2626', '#ef4444', '#f59e0b')"
                                                class="px-3 py-1 border rounded-full text-sm hover:bg-neutral-100">
                                            <span class="inline-block w-3 h-3 rounded-full mr-1" style="background:#dc2626"></span>Red
                                        </button>
                                        <button type="button" onclick="applyPreset('#0891b2', '#06b6d4', '#f97316')"
                                                class="px-3 py-1 border rounded-full text-sm hover:bg-neutral-100">
                                            <span class="inline-block w-3 h-3 rounded-full mr-1" style="background:#0891b2"></span>Cyan
                                        </button>
                                        <button type="button" onclick="applyPreset('#1f2937', '#374151', '#3b82f6')"
                                                class="px-3 py-1 border rounded-full text-sm hover:bg-neutral-100">
                                            <span class="inline-block w-3 h-3 rounded-full mr-1" style="background:#1f2937"></span>Dark
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 mt-6 border-t">
                            <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>

                <script>
                // Sync color picker with text input
                ['primary', 'secondary', 'accent'].forEach(type => {
                    const picker = document.getElementById(type + '_color');
                    const text = document.getElementById(type + '_color_text');
                    const preview = document.getElementById('preview_' + type);

                    picker.addEventListener('input', function() {
                        text.value = this.value;
                        if (preview) preview.style.backgroundColor = this.value;
                    });

                    text.addEventListener('input', function() {
                        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                            picker.value = this.value;
                            if (preview) preview.style.backgroundColor = this.value;
                        }
                    });
                });

                function applyPreset(primary, secondary, accent) {
                    document.getElementById('primary_color').value = primary;
                    document.getElementById('primary_color_text').value = primary;
                    document.getElementById('preview_primary').style.backgroundColor = primary;

                    document.getElementById('secondary_color').value = secondary;
                    document.getElementById('secondary_color_text').value = secondary;
                    document.getElementById('preview_secondary').style.backgroundColor = secondary;

                    document.getElementById('accent_color').value = accent;
                    document.getElementById('accent_color_text').value = accent;
                    document.getElementById('preview_accent').style.backgroundColor = accent;
                }
                </script>

            <?php elseif (empty($currentGroup['settings'])): ?>
                <div class="p-12 text-center">
                    <p class="text-neutral-500">No settings in this category.</p>
                </div>
            <?php else: ?>
                <form method="POST" action="/admin/settings" class="p-6 space-y-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_group" value="<?= e($activeGroup) ?>">

                    <?php foreach ($currentGroup['settings'] as $setting): ?>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">
                                <?= e(ucwords(str_replace('_', ' ', $setting['setting_key']))) ?>
                            </label>

                            <?php if ($setting['setting_type'] === 'bool'): ?>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="<?= e($setting['setting_key']) ?>" value="0">
                                    <input type="checkbox" name="<?= e($setting['setting_key']) ?>" value="1"
                                           <?= $setting['setting_value'] ? 'checked' : '' ?>
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-neutral-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                </label>

                            <?php elseif ($setting['setting_type'] === 'int'): ?>
                                <input type="number" name="<?= e($setting['setting_key']) ?>"
                                       value="<?= e($setting['setting_value']) ?>"
                                       class="w-full max-w-xs px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">

                            <?php elseif ($setting['setting_type'] === 'encrypted'): ?>
                                <input type="password" name="<?= e($setting['setting_key']) ?>"
                                       value="<?= $setting['setting_value'] ? '********' : '' ?>"
                                       placeholder="<?= $setting['setting_value'] ? 'Enter new value to change' : 'Enter value' ?>"
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <p class="text-neutral-500 text-sm mt-1">Sensitive data - hidden for security</p>

                            <?php elseif ($setting['setting_type'] === 'json'): ?>
                                <textarea name="<?= e($setting['setting_key']) ?>" rows="4"
                                          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono text-sm"><?= e($setting['setting_value']) ?></textarea>
                                <p class="text-neutral-500 text-sm mt-1">Enter valid JSON</p>

                            <?php else: ?>
                                <?php if (strlen($setting['setting_value'] ?? '') > 100): ?>
                                    <textarea name="<?= e($setting['setting_key']) ?>" rows="3"
                                              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= e($setting['setting_value']) ?></textarea>
                                <?php else: ?>
                                    <input type="text" name="<?= e($setting['setting_key']) ?>"
                                           value="<?= e($setting['setting_value']) ?>"
                                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($setting['description']): ?>
                                <p class="text-neutral-500 text-sm mt-1"><?= e($setting['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="pt-4 border-t">
                        <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            Save Settings
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
