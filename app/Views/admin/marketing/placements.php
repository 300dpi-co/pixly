<?php $currentPage = 'marketing'; $currentTab = 'placements'; ?>

<?php include __DIR__ . '/_subnav.php'; ?>

<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-semibold text-neutral-900">Ad Placements</h2>
        <p class="text-sm text-neutral-500">Manage your advertising placements across the site</p>
    </div>
    <a href="/admin/marketing/placements/create" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Add Placement
    </a>
</div>

<?php if (session_get_flash('success')): ?>
<div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
    <?= session_get_flash('success') ?>
</div>
<?php endif; ?>

<!-- Placements Table -->
<div class="bg-white rounded-xl shadow-sm border border-neutral-100 overflow-hidden">
    <table class="min-w-full divide-y divide-neutral-200">
        <thead class="bg-neutral-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Location</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Size</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-neutral-200">
            <?php if (empty($placements)): ?>
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-neutral-500">
                    <svg class="mx-auto h-12 w-12 text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    <p>No ad placements yet</p>
                    <a href="/admin/marketing/placements/create" class="text-primary-600 hover:underline">Create your first placement</a>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($placements as $placement): ?>
            <tr class="hover:bg-neutral-50">
                <td class="px-6 py-4">
                    <div class="font-medium text-neutral-900"><?= e($placement['name']) ?></div>
                    <div class="text-sm text-neutral-500"><?= e($placement['slug']) ?></div>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800">
                        <?= ucfirst(str_replace('_', ' ', $placement['location'] ?? '')) ?>
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-neutral-500">
                    <?= e($placement['default_size'] ?? '-') ?>
                </td>
                <td class="px-6 py-4">
                    <button onclick="toggleStatus(<?= $placement['id'] ?>, this)"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors <?= $placement['is_active'] ? 'bg-primary-600' : 'bg-neutral-200' ?>">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform <?= $placement['is_active'] ? 'translate-x-6' : 'translate-x-1' ?>"></span>
                    </button>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="/admin/marketing/placements/<?= $placement['id'] ?>/edit" class="text-neutral-400 hover:text-primary-600" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form action="/admin/marketing/placements/<?= $placement['id'] ?>/delete" method="POST" class="inline"
                              onsubmit="return confirm('Delete this placement?')">
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

<script>
async function toggleStatus(id, btn) {
    try {
        const res = await fetch(`/admin/marketing/placements/${id}/toggle`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '<?= csrf_token() ?>' }
        });
        if (res.ok) {
            const isActive = btn.classList.contains('bg-primary-600');
            btn.classList.toggle('bg-primary-600', !isActive);
            btn.classList.toggle('bg-neutral-200', isActive);
            btn.querySelector('span').classList.toggle('translate-x-6', !isActive);
            btn.querySelector('span').classList.toggle('translate-x-1', isActive);
        }
    } catch (e) { console.error(e); }
}
</script>
