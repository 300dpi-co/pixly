<?php $currentPage = 'marketing'; $currentTab = 'announcements'; ?>

<?php include __DIR__ . '/_subnav.php'; ?>

<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-semibold text-neutral-900">Announcement Bars</h2>
        <p class="text-sm text-neutral-500">Display promotional messages at the top or bottom of your site</p>
    </div>
    <a href="/admin/marketing/announcements/create" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Create Announcement
    </a>
</div>

<?php if (session_get_flash('success')): ?>
<div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
    <?= session_get_flash('success') ?>
</div>
<?php endif; ?>

<!-- Announcements List -->
<div class="space-y-4">
    <?php if (empty($announcements)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-neutral-100 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
        </svg>
        <p class="text-neutral-500 mb-4">No announcements yet</p>
        <a href="/admin/marketing/announcements/create" class="text-primary-600 hover:underline">Create your first announcement</a>
    </div>
    <?php else: ?>
    <?php foreach ($announcements as $ann): ?>
    <div class="bg-white rounded-xl shadow-sm border border-neutral-100 overflow-hidden">
        <!-- Preview Bar -->
        <div class="p-3 text-center text-sm" style="background-color: <?= e($ann['bg_color']) ?>; color: <?= e($ann['text_color']) ?>">
            <?= e($ann['message']) ?>
            <?php if ($ann['link_text']): ?>
            <span class="underline ml-2"><?= e($ann['link_text']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Details -->
        <div class="p-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="px-2 py-1 text-xs rounded-full <?= $ann['is_active'] ? 'bg-green-100 text-green-700' : 'bg-neutral-100 text-neutral-600' ?>">
                    <?= $ann['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
                <span class="text-sm text-neutral-500">
                    Position: <?= ucfirst($ann['position']) ?>
                    <?= $ann['is_sticky'] ? '(Sticky)' : '' ?>
                </span>
                <?php if ($ann['start_date'] || $ann['end_date']): ?>
                <span class="text-sm text-neutral-500">
                    <?= $ann['start_date'] ? date('M j', strtotime($ann['start_date'])) : '' ?>
                    -
                    <?= $ann['end_date'] ? date('M j, Y', strtotime($ann['end_date'])) : 'Forever' ?>
                </span>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-2">
                <a href="/admin/marketing/announcements/<?= $ann['id'] ?>/edit"
                   class="px-3 py-1.5 text-sm text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 rounded-lg transition">
                    Edit
                </a>
                <form action="/admin/marketing/announcements/<?= $ann['id'] ?>/delete" method="POST"
                      onsubmit="return confirm('Delete this announcement?')">
                    <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                    <button type="submit" class="px-3 py-1.5 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
