<?php $currentPage = 'marketing'; $currentTab = 'ads'; ?>

<?php include __DIR__ . '/_subnav.php'; ?>

<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-semibold text-neutral-900">Manage Ads</h2>
        <p class="text-sm text-neutral-500">Create and manage ad content for your placements</p>
    </div>
    <a href="/admin/marketing/ads/create" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Create Ad
    </a>
</div>

<?php if (session_get_flash('success')): ?>
<div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
    <?= session_get_flash('success') ?>
</div>
<?php endif; ?>

<!-- Ads Table -->
<div class="bg-white rounded-xl shadow-sm border border-neutral-100 overflow-hidden">
    <table class="min-w-full divide-y divide-neutral-200">
        <thead class="bg-neutral-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Placement</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Stats</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-neutral-200">
            <?php if (empty($ads)): ?>
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-neutral-500">
                    <svg class="mx-auto h-12 w-12 text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                    <p>No ads created yet</p>
                    <a href="/admin/marketing/ads/create" class="text-primary-600 hover:underline">Create your first ad</a>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($ads as $ad): ?>
            <tr class="hover:bg-neutral-50">
                <td class="px-6 py-4">
                    <div class="font-medium text-neutral-900"><?= e($ad['name']) ?></div>
                </td>
                <td class="px-6 py-4">
                    <?php if ($ad['placement_name']): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <?= e($ad['placement_name']) ?>
                    </span>
                    <?php else: ?>
                    <span class="text-neutral-400 text-sm">Not assigned</span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800">
                        <?= ucfirst(str_replace('_', ' ', $ad['ad_type'] ?? 'custom_html')) ?>
                    </span>
                </td>
                <td class="px-6 py-4">
                    <?php if ($ad['is_active']): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Active
                    </span>
                    <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-600">
                        Inactive
                    </span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4 text-sm text-neutral-500">
                    <?= number_format($ad['impressions'] ?? 0) ?> views
                    <?php if (($ad['clicks'] ?? 0) > 0): ?>
                    / <?= number_format($ad['clicks']) ?> clicks
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="/admin/marketing/ads/<?= $ad['id'] ?>/edit" class="text-neutral-400 hover:text-primary-600" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form action="/admin/marketing/ads/<?= $ad['id'] ?>/delete" method="POST" class="inline"
                              onsubmit="return confirm('Delete this ad?')">
                            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                            <button type="submit" class="text-neutral-400 hover:text-red-600" title="Delete">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
