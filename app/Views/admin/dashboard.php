<?php if (!empty($updateInfo['available'])): ?>
<!-- Update Available Banner -->
<div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow-lg p-4 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center text-white">
            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <div>
                <p class="font-semibold">Update Available!</p>
                <p class="text-sm text-blue-100">Version <?= e($updateInfo['latest_version']) ?> is available. You're running <?= e($updateInfo['current_version']) ?>.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="https://github.com/300dpi-co/pixly/releases" target="_blank" class="px-4 py-2 bg-white/20 text-white rounded-lg font-medium hover:bg-white/30 transition">
                Changelog
            </a>
            <button onclick="performUpdate()" id="updateBtn" class="px-4 py-2 bg-white text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Update Now
            </button>
        </div>
    </div>
</div>
<script>
function performUpdate() {
    if (!confirm('Update to version <?= e($updateInfo['latest_version']) ?>? This will download and install the latest version.')) {
        return;
    }
    const btn = document.getElementById('updateBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Updating...';

    fetch('<?= $view->url('/admin/system/update') ?>', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '<?= csrf_token() ?>',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Update successful! The page will now reload.');
            window.location.reload();
        } else {
            alert('Update failed: ' + (data.error || 'Unknown error'));
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Update Now';
        }
    })
    .catch(err => {
        alert('Update failed: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Update Now';
    });
}
</script>
<?php endif; ?>

<?php if (!empty($updateInfo['announcement'])): ?>
<!-- Announcement Banner -->
<div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
    <div class="flex items-start">
        <svg class="w-5 h-5 text-amber-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
        </svg>
        <p class="text-amber-800"><?= e($updateInfo['announcement']) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Version Badge -->
<div class="flex justify-end mb-2">
    <span class="text-xs text-neutral-400">Pixly v<?= e($updateInfo['current_version'] ?? '1.0.0') ?></span>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-neutral-500">Total Images</p>
                <p class="text-2xl font-semibold text-neutral-900"><?= number_format($stats['total_images']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-neutral-500">Pending Moderation</p>
                <p class="text-2xl font-semibold text-neutral-900"><?= number_format($stats['pending_moderation']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-neutral-500">Published</p>
                <p class="text-2xl font-semibold text-neutral-900"><?= number_format($stats['published_images']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-neutral-500">Total Users</p>
                <p class="text-2xl font-semibold text-neutral-900"><?= number_format($stats['total_users']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-neutral-500">Categories</p>
                <p class="text-2xl font-semibold text-neutral-900"><?= number_format($stats['total_categories']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-pink-100 text-pink-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-neutral-500">Tags</p>
                <p class="text-2xl font-semibold text-neutral-900"><?= number_format($stats['total_tags']) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Quick Actions</h2>
    <div class="flex flex-wrap gap-3">
        <a href="<?= $view->url('/admin/images/upload') ?>" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Upload Images
        </a>
        <a href="<?= $view->url('/admin/moderation') ?>" class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Review Pending (<?= $stats['pending_moderation'] ?>)
        </a>
        <a href="<?= $view->url('/admin/ai') ?>" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            AI Processing
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Images -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-neutral-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-neutral-900">Recent Images</h2>
            <a href="<?= $view->url('/admin/images') ?>" class="text-sm text-primary-600 hover:text-primary-700">View all</a>
        </div>
        <div class="p-6">
            <?php if (empty($recentImages)): ?>
                <p class="text-neutral-500 text-center py-4">No images uploaded yet.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentImages as $image): ?>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-neutral-200 rounded-lg flex-shrink-0 overflow-hidden">
                            <?php if ($image['thumbnail_path']): ?>
                                <img src="<?= $view->url('/uploads/' . $image['thumbnail_path']) ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-neutral-400">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-neutral-900 truncate"><?= e($image['title']) ?></p>
                            <p class="text-xs text-neutral-500"><?= date('M j, Y g:i A', strtotime($image['created_at'])) ?></p>
                        </div>
                        <div class="flex gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                <?php if ($image['moderation_status'] === 'approved'): ?>bg-green-100 text-green-800
                                <?php elseif ($image['moderation_status'] === 'pending'): ?>bg-yellow-100 text-yellow-800
                                <?php else: ?>bg-red-100 text-red-800<?php endif; ?>">
                                <?= ucfirst($image['moderation_status']) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-neutral-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-neutral-900">Recent Users</h2>
            <a href="<?= $view->url('/admin/users') ?>" class="text-sm text-primary-600 hover:text-primary-700">View all</a>
        </div>
        <div class="p-6">
            <?php if (empty($recentUsers)): ?>
                <p class="text-neutral-500 text-center py-4">No users registered yet.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentUsers as $user): ?>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center font-semibold">
                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-neutral-900"><?= e($user['username']) ?></p>
                            <p class="text-xs text-neutral-500"><?= e($user['email']) ?></p>
                        </div>
                        <div class="flex gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                <?php if ($user['role'] === 'admin'): ?>bg-purple-100 text-purple-800
                                <?php else: ?>bg-neutral-100 text-neutral-800<?php endif; ?>">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
