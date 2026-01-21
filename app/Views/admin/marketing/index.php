<?php $currentPage = 'marketing'; $currentTab = 'dashboard'; ?>

<?php include __DIR__ . '/_subnav.php'; ?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Ad Impressions -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-neutral-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-500">Total Impressions</p>
                <p class="text-2xl font-bold text-neutral-900 mt-1"><?= number_format($adStats['total_impressions'] ?? 0) ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Ad Clicks -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-neutral-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-500">Total Clicks</p>
                <p class="text-2xl font-bold text-neutral-900 mt-1"><?= number_format($adStats['total_clicks'] ?? 0) ?></p>
            </div>
            <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Active Placements -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-neutral-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-500">Active Placements</p>
                <p class="text-2xl font-bold text-neutral-900 mt-1"><?= number_format($adStats['active_placements'] ?? 0) ?> / <?= number_format($adStats['total_placements'] ?? 0) ?></p>
            </div>
            <div class="w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Newsletter Subscribers -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-neutral-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-500">Subscribers</p>
                <p class="text-2xl font-bold text-neutral-900 mt-1"><?= number_format($subscriberCounts['confirmed'] ?? 0) ?></p>
            </div>
            <div class="w-12 h-12 bg-orange-50 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-neutral-100">
        <div class="px-6 py-4 border-b border-neutral-100">
            <h2 class="text-lg font-semibold text-neutral-900">Quick Actions</h2>
        </div>
        <div class="p-6 grid grid-cols-2 gap-4">
            <a href="/admin/marketing/placements/create" class="flex flex-col items-center p-4 rounded-lg border-2 border-dashed border-neutral-200 hover:border-primary-300 hover:bg-primary-50 transition">
                <svg class="w-8 h-8 text-neutral-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span class="text-sm font-medium text-neutral-700">New Ad Placement</span>
            </a>
            <a href="/admin/marketing/popups/create" class="flex flex-col items-center p-4 rounded-lg border-2 border-dashed border-neutral-200 hover:border-primary-300 hover:bg-primary-50 transition">
                <svg class="w-8 h-8 text-neutral-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10m-10 0a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2"/>
                </svg>
                <span class="text-sm font-medium text-neutral-700">New Popup</span>
            </a>
            <a href="/admin/marketing/announcements/create" class="flex flex-col items-center p-4 rounded-lg border-2 border-dashed border-neutral-200 hover:border-primary-300 hover:bg-primary-50 transition">
                <svg class="w-8 h-8 text-neutral-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                <span class="text-sm font-medium text-neutral-700">New Announcement</span>
            </a>
            <a href="/admin/marketing/newsletter/export" class="flex flex-col items-center p-4 rounded-lg border-2 border-dashed border-neutral-200 hover:border-primary-300 hover:bg-primary-50 transition">
                <svg class="w-8 h-8 text-neutral-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-sm font-medium text-neutral-700">Export Subscribers</span>
            </a>
        </div>
    </div>

    <!-- Recent Ad Placements -->
    <div class="bg-white rounded-xl shadow-sm border border-neutral-100">
        <div class="px-6 py-4 border-b border-neutral-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-neutral-900">Recent Ad Placements</h2>
            <a href="/admin/marketing/placements" class="text-sm text-primary-600 hover:text-primary-700">View All</a>
        </div>
        <div class="divide-y divide-neutral-100">
            <?php if (empty($recentAds)): ?>
            <div class="p-6 text-center text-neutral-500">
                No ad placements yet. <a href="/admin/marketing/placements/create" class="text-primary-600 hover:underline">Create one</a>
            </div>
            <?php else: ?>
            <?php foreach ($recentAds as $ad): ?>
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="font-medium text-neutral-900"><?= e($ad['name']) ?></p>
                    <p class="text-sm text-neutral-500"><?= ucfirst(str_replace('_', ' ', $ad['location'] ?? '')) ?> &middot; <?= e($ad['default_size'] ?? 'No size') ?></p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-2 py-1 text-xs rounded-full <?= $ad['is_active'] ? 'bg-green-100 text-green-700' : 'bg-neutral-100 text-neutral-600' ?>">
                        <?= $ad['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                    <a href="/admin/marketing/placements/<?= $ad['id'] ?>/edit" class="text-neutral-400 hover:text-neutral-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Configuration Status -->
<div class="mt-8 bg-white rounded-xl shadow-sm border border-neutral-100">
    <div class="px-6 py-4 border-b border-neutral-100">
        <h2 class="text-lg font-semibold text-neutral-900">Configuration Status</h2>
    </div>
    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="flex items-center gap-3">
            <?php $ga = $settings['tracking']['google_analytics_id'] ?? ''; ?>
            <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $ga ? 'bg-green-100' : 'bg-neutral-100' ?>">
                <?php if ($ga): ?>
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <?php else: ?>
                <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <?php endif; ?>
            </div>
            <div>
                <p class="font-medium text-neutral-900">Google Analytics</p>
                <p class="text-sm text-neutral-500"><?= $ga ? 'Configured' : 'Not configured' ?></p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <?php $adsense = $settings['adsense']['adsense_publisher_id'] ?? ''; ?>
            <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $adsense ? 'bg-green-100' : 'bg-neutral-100' ?>">
                <?php if ($adsense): ?>
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <?php else: ?>
                <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <?php endif; ?>
            </div>
            <div>
                <p class="font-medium text-neutral-900">Google AdSense</p>
                <p class="text-sm text-neutral-500"><?= $adsense ? 'Configured' : 'Not configured' ?></p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <?php $juicy = $settings['juicyads']['juicyads_publisher_id'] ?? ''; ?>
            <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $juicy ? 'bg-green-100' : 'bg-neutral-100' ?>">
                <?php if ($juicy): ?>
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <?php else: ?>
                <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <?php endif; ?>
            </div>
            <div>
                <p class="font-medium text-neutral-900">JuicyAds</p>
                <p class="text-sm text-neutral-500"><?= $juicy ? 'Configured' : 'Not configured' ?></p>
            </div>
        </div>
    </div>
</div>
