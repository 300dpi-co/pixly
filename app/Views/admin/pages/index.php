<?php $currentPage = 'pages'; ?>

<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-neutral-900">Pages</h1>
        <p class="text-sm text-neutral-500">Manage your site's content pages</p>
    </div>
    <a href="/admin/pages/create" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Add Page
    </a>
</div>

<?php if (session_get_flash('success')): ?>
<div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
    <?= session_get_flash('success') ?>
</div>
<?php endif; ?>

<?php if (session_get_flash('error')): ?>
<div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
    <?= session_get_flash('error') ?>
</div>
<?php endif; ?>

<!-- Info Box -->
<div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
    <div class="flex">
        <svg class="w-5 h-5 text-blue-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="text-sm text-blue-700">
            <p class="font-medium">Template Variables</p>
            <p class="mt-1">Use these in your content: <code class="bg-blue-100 px-1 rounded">{{site_name}}</code>, <code class="bg-blue-100 px-1 rounded">{{current_date}}</code>, <code class="bg-blue-100 px-1 rounded">{{current_year}}</code></p>
        </div>
    </div>
</div>

<!-- Pages Table -->
<div class="bg-white rounded-xl shadow-sm border border-neutral-100 overflow-hidden">
    <table class="min-w-full divide-y divide-neutral-200">
        <thead class="bg-neutral-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Page</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">URL</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Footer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-neutral-200">
            <?php if (empty($pages)): ?>
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-neutral-500">
                    <svg class="mx-auto h-12 w-12 text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p>No pages yet</p>
                    <a href="/admin/pages/create" class="text-primary-600 hover:underline">Create your first page</a>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($pages as $page): ?>
            <tr class="hover:bg-neutral-50">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div>
                            <div class="font-medium text-neutral-900"><?= e($page['title']) ?></div>
                            <?php if ($page['is_system']): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-neutral-100 text-neutral-600">
                                System Page
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <a href="/<?= e($page['slug']) ?>" target="_blank" class="text-sm text-primary-600 hover:underline">
                        /<?= e($page['slug']) ?>
                    </a>
                </td>
                <td class="px-6 py-4">
                    <?php if ($page['show_in_footer']): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Yes
                    </span>
                    <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-600">
                        No
                    </span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4">
                    <button onclick="toggleStatus(<?= $page['id'] ?>, this)"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors <?= $page['is_active'] ? 'bg-primary-600' : 'bg-neutral-200' ?>">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform <?= $page['is_active'] ? 'translate-x-6' : 'translate-x-1' ?>"></span>
                    </button>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="/admin/pages/<?= $page['id'] ?>/edit" class="text-neutral-400 hover:text-primary-600" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <?php if (!$page['is_system']): ?>
                        <form action="/admin/pages/<?= $page['id'] ?>/delete" method="POST" class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this page?')">
                            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                            <button type="submit" class="text-neutral-400 hover:text-red-600" title="Delete">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        <?php endif; ?>
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
        const res = await fetch(`/admin/pages/${id}/toggle`, {
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
