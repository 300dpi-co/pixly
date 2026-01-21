<?php $currentPage = 'marketing'; $currentTab = 'newsletter'; ?>

<?php include __DIR__ . '/_subnav.php'; ?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm border border-neutral-100 p-4">
        <p class="text-sm text-neutral-500">Confirmed</p>
        <p class="text-2xl font-bold text-green-600"><?= number_format($counts['confirmed'] ?? 0) ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-neutral-100 p-4">
        <p class="text-sm text-neutral-500">Pending</p>
        <p class="text-2xl font-bold text-yellow-600"><?= number_format($counts['pending'] ?? 0) ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-neutral-100 p-4">
        <p class="text-sm text-neutral-500">Unsubscribed</p>
        <p class="text-2xl font-bold text-neutral-400"><?= number_format($counts['unsubscribed'] ?? 0) ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-neutral-100 p-4">
        <p class="text-sm text-neutral-500">Total</p>
        <p class="text-2xl font-bold text-neutral-900"><?= number_format($counts['total'] ?? 0) ?></p>
    </div>
</div>

<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-semibold text-neutral-900">Newsletter Subscribers</h2>
    </div>
    <a href="/admin/marketing/newsletter/export" class="inline-flex items-center px-4 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-sm font-medium rounded-lg transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Export CSV
    </a>
</div>

<?php if (session_get_flash('success')): ?>
<div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
    <?= session_get_flash('success') ?>
</div>
<?php endif; ?>

<!-- Status Tabs -->
<div class="mb-6">
    <nav class="flex gap-2">
        <a href="?status=confirmed" class="px-4 py-2 text-sm font-medium rounded-lg <?= $status === 'confirmed' ? 'bg-primary-100 text-primary-700' : 'text-neutral-500 hover:bg-neutral-100' ?>">
            Confirmed
        </a>
        <a href="?status=pending" class="px-4 py-2 text-sm font-medium rounded-lg <?= $status === 'pending' ? 'bg-primary-100 text-primary-700' : 'text-neutral-500 hover:bg-neutral-100' ?>">
            Pending
        </a>
        <a href="?status=unsubscribed" class="px-4 py-2 text-sm font-medium rounded-lg <?= $status === 'unsubscribed' ? 'bg-primary-100 text-primary-700' : 'text-neutral-500 hover:bg-neutral-100' ?>">
            Unsubscribed
        </a>
    </nav>
</div>

<!-- Subscribers Table -->
<div class="bg-white rounded-xl shadow-sm border border-neutral-100 overflow-hidden">
    <table class="min-w-full divide-y divide-neutral-200">
        <thead class="bg-neutral-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Source</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-neutral-200">
            <?php if (empty($subscribers)): ?>
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-neutral-500">
                    No subscribers in this category
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($subscribers as $sub): ?>
            <tr class="hover:bg-neutral-50">
                <td class="px-6 py-4">
                    <div class="font-medium text-neutral-900"><?= e($sub['email']) ?></div>
                </td>
                <td class="px-6 py-4 text-sm text-neutral-500">
                    <?= e($sub['name'] ?? '-') ?>
                </td>
                <td class="px-6 py-4 text-sm text-neutral-500">
                    <?= ucfirst($sub['source']) ?>
                </td>
                <td class="px-6 py-4 text-sm text-neutral-500">
                    <?= date('M j, Y', strtotime($sub['created_at'])) ?>
                </td>
                <td class="px-6 py-4 text-right">
                    <form action="/admin/marketing/newsletter/<?= $sub['id'] ?>/delete" method="POST"
                          onsubmit="return confirm('Delete this subscriber?')">
                        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                        <button type="submit" class="text-red-600 hover:text-red-700 text-sm">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav class="mt-6 flex justify-center">
    <div class="flex items-center gap-2">
        <?php if ($page > 1): ?>
        <a href="?status=<?= $status ?>&page=<?= $page - 1 ?>" class="px-4 py-2 bg-white border border-neutral-300 rounded-lg hover:bg-neutral-50 text-sm">Previous</a>
        <?php endif; ?>

        <span class="px-4 py-2 text-sm text-neutral-500">Page <?= $page ?> of <?= $totalPages ?></span>

        <?php if ($page < $totalPages): ?>
        <a href="?status=<?= $status ?>&page=<?= $page + 1 ?>" class="px-4 py-2 bg-white border border-neutral-300 rounded-lg hover:bg-neutral-50 text-sm">Next</a>
        <?php endif; ?>
    </div>
</nav>
<?php endif; ?>
