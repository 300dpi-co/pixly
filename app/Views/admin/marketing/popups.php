<?php $currentPage = 'marketing'; $currentTab = 'popups'; ?>

<?php include __DIR__ . '/_subnav.php'; ?>

<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-semibold text-neutral-900">Popup Ads</h2>
        <p class="text-sm text-neutral-500">Create overlay popups for promotions and ads</p>
    </div>
    <a href="/admin/marketing/popups/create" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Create Popup
    </a>
</div>

<?php if (session_get_flash('success')): ?>
<div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
    <?= session_get_flash('success') ?>
</div>
<?php endif; ?>

<!-- Popups Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($popups)): ?>
    <div class="col-span-full bg-white rounded-xl shadow-sm border border-neutral-100 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10m-10 0a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2"/>
        </svg>
        <p class="text-neutral-500 mb-4">No popup ads yet</p>
        <a href="/admin/marketing/popups/create" class="text-primary-600 hover:underline">Create your first popup</a>
    </div>
    <?php else: ?>
    <?php foreach ($popups as $popup): ?>
    <div class="bg-white rounded-xl shadow-sm border border-neutral-100 overflow-hidden">
        <div class="p-5">
            <div class="flex items-start justify-between mb-3">
                <h3 class="font-semibold text-neutral-900"><?= e($popup['name']) ?></h3>
                <span class="px-2 py-1 text-xs rounded-full <?= $popup['is_active'] ? 'bg-green-100 text-green-700' : 'bg-neutral-100 text-neutral-600' ?>">
                    <?= $popup['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
            </div>

            <div class="space-y-2 text-sm text-neutral-500 mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>Trigger: <?= ucfirst(str_replace('_', ' ', $popup['trigger_type'])) ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Frequency: <?= ucfirst(str_replace('_', ' ', $popup['frequency'])) ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span><?= number_format($popup['impressions']) ?> views</span>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-4 border-t border-neutral-100">
                <a href="/admin/marketing/popups/<?= $popup['id'] ?>/edit"
                   class="flex-1 px-3 py-2 text-sm text-center text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 rounded-lg transition">
                    Edit
                </a>
                <form action="/admin/marketing/popups/<?= $popup['id'] ?>/delete" method="POST" class="flex-1"
                      onsubmit="return confirm('Delete this popup?')">
                    <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                    <button type="submit"
                            class="w-full px-3 py-2 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
