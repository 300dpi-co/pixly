<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-100 text-yellow-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold"><?= $stats['pending'] ?></p>
                    <p class="text-sm text-neutral-500">Pending</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 text-green-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold"><?= $stats['approved_today'] ?></p>
                    <p class="text-sm text-neutral-500">Approved Today</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-red-100 text-red-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold"><?= $stats['rejected_today'] ?></p>
                    <p class="text-sm text-neutral-500">Rejected Today</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Moderation Queue -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Pending Moderation (<?= count($pending) ?>)</h2>
                    <?php if (!empty($pending)): ?>
                    <div class="flex gap-2" id="bulk-actions" style="display: none;">
                        <button onclick="bulkApprove()" class="px-3 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                            Approve Selected
                        </button>
                        <button onclick="showBulkRejectModal()" class="px-3 py-1.5 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                            Reject Selected
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="p-6">
                    <?php if (empty($pending)): ?>
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="mt-4 text-neutral-500">All caught up! No images pending moderation.</p>
                        </div>
                    <?php else: ?>
                        <div class="mb-4">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" id="select-all" onchange="toggleSelectAll(this)"
                                       class="w-4 h-4 rounded border-neutral-300 text-primary-600">
                                Select All
                            </label>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            <?php foreach ($pending as $image): ?>
                            <div class="group relative aspect-square bg-neutral-100 rounded-lg overflow-hidden" data-image-id="<?= $image['id'] ?>">
                                <!-- Checkbox -->
                                <div class="absolute top-2 left-2 z-10">
                                    <input type="checkbox" class="image-checkbox w-5 h-5 rounded border-neutral-300 text-primary-600"
                                           value="<?= $image['id'] ?>" onchange="updateBulkActions()">
                                </div>

                                <?php if ($image['thumbnail_path']): ?>
                                    <img src="/uploads/<?= e($image['thumbnail_path']) ?>" alt="" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-neutral-400">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>

                                <!-- Uploader info -->
                                <?php if ($image['uploader_name']): ?>
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-2">
                                    <p class="text-white text-xs truncate">by <?= e($image['uploader_name']) ?></p>
                                </div>
                                <?php endif; ?>

                                <!-- Actions overlay -->
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2 p-2">
                                    <div class="flex gap-2">
                                        <button onclick="approveImage(<?= $image['id'] ?>, this)"
                                                class="px-3 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                            Approve
                                        </button>
                                        <button onclick="showRejectModal(<?= $image['id'] ?>)"
                                                class="px-3 py-1.5 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                                            Reject
                                        </button>
                                    </div>
                                    <a href="/admin/images/<?= $image['id'] ?>/edit"
                                       class="text-white text-xs hover:underline">View Details</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Trusted Users Sidebar -->
        <div>
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold">Trusted Users</h2>
                    <p class="text-sm text-neutral-500">Skip moderation for these users</p>
                </div>

                <div class="p-6">
                    <!-- Add trusted user -->
                    <?php if (!empty($availableUsers)): ?>
                    <form method="POST" action="/admin/moderation/trusted" class="mb-4">
                        <?= csrf_field() ?>
                        <div class="flex gap-2">
                            <select name="user_id" required class="flex-1 px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                                <option value="">Select user...</option>
                                <?php foreach ($availableUsers as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= e($user['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="px-3 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>

                    <!-- Trusted users list -->
                    <?php if (empty($trustedUsers)): ?>
                        <p class="text-neutral-500 text-sm text-center py-4">No trusted users yet.</p>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach ($trustedUsers as $user): ?>
                            <div class="flex items-center justify-between p-2 bg-neutral-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-sm"><?= e($user['username']) ?></p>
                                    <p class="text-xs text-neutral-500"><?= e($user['email']) ?></p>
                                </div>
                                <button onclick="removeTrusted(<?= $user['id'] ?>, this)"
                                        class="p-1 text-neutral-400 hover:text-red-600" title="Remove">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4 pt-4 border-t">
                        <p class="text-xs text-neutral-500">
                            <strong>Note:</strong> Admins and Moderators automatically bypass moderation.
                            Add regular users here to trust their uploads.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold">Reject Image</h3>
        </div>
        <form id="reject-form" method="POST">
            <?= csrf_field() ?>
            <div class="p-6">
                <label class="block text-sm font-medium text-neutral-700 mb-1">Reason (optional)</label>
                <textarea name="reason" rows="3" placeholder="Enter rejection reason..."
                          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"></textarea>
            </div>
            <div class="px-6 py-4 bg-neutral-50 flex justify-end gap-3 rounded-b-lg">
                <button type="button" onclick="hideRejectModal()" class="px-4 py-2 text-neutral-700">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Reject</button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Reject Modal -->
<div id="bulk-reject-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold">Reject Selected Images</h3>
        </div>
        <div class="p-6">
            <p class="mb-4 text-neutral-600">Rejecting <span id="bulk-reject-count">0</span> images.</p>
            <label class="block text-sm font-medium text-neutral-700 mb-1">Reason (optional)</label>
            <textarea id="bulk-reject-reason" rows="3" placeholder="Enter rejection reason..."
                      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"></textarea>
        </div>
        <div class="px-6 py-4 bg-neutral-50 flex justify-end gap-3 rounded-b-lg">
            <button type="button" onclick="hideBulkRejectModal()" class="px-4 py-2 text-neutral-700">Cancel</button>
            <button type="button" onclick="bulkReject()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Reject All</button>
        </div>
    </div>
</div>

<script>
const csrfToken = '<?= csrf_token() ?>';

function toggleSelectAll(checkbox) {
    document.querySelectorAll('.image-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checked = document.querySelectorAll('.image-checkbox:checked');
    const bulkActions = document.getElementById('bulk-actions');
    if (bulkActions) {
        bulkActions.style.display = checked.length > 0 ? 'flex' : 'none';
    }
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.image-checkbox:checked')).map(cb => cb.value);
}

async function approveImage(id, btn) {
    btn.disabled = true;
    btn.textContent = '...';

    try {
        const response = await fetch(`/admin/moderation/${id}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `_token=${csrfToken}`
        });

        const data = await response.json();
        if (data.success) {
            const card = btn.closest('[data-image-id]');
            card.remove();
            updateCounts();
        }
    } catch (e) {
        console.error(e);
        btn.disabled = false;
        btn.textContent = 'Approve';
    }
}

function showRejectModal(id) {
    document.getElementById('reject-form').action = `/admin/moderation/${id}/reject`;
    document.getElementById('reject-modal').classList.remove('hidden');
}

function hideRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
}

function showBulkRejectModal() {
    const ids = getSelectedIds();
    document.getElementById('bulk-reject-count').textContent = ids.length;
    document.getElementById('bulk-reject-modal').classList.remove('hidden');
}

function hideBulkRejectModal() {
    document.getElementById('bulk-reject-modal').classList.add('hidden');
}

async function bulkApprove() {
    const ids = getSelectedIds();
    if (ids.length === 0) return;

    const formData = new FormData();
    formData.append('_token', csrfToken);
    ids.forEach(id => formData.append('ids[]', id));

    try {
        const response = await fetch('/admin/moderation/bulk-approve', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            ids.forEach(id => {
                const card = document.querySelector(`[data-image-id="${id}"]`);
                if (card) card.remove();
            });
            updateCounts();
            updateBulkActions();
        }
    } catch (e) {
        console.error(e);
    }
}

async function bulkReject() {
    const ids = getSelectedIds();
    if (ids.length === 0) return;

    const reason = document.getElementById('bulk-reject-reason').value;
    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('reason', reason);
    ids.forEach(id => formData.append('ids[]', id));

    try {
        const response = await fetch('/admin/moderation/bulk-reject', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            ids.forEach(id => {
                const card = document.querySelector(`[data-image-id="${id}"]`);
                if (card) card.remove();
            });
            hideBulkRejectModal();
            updateCounts();
            updateBulkActions();
        }
    } catch (e) {
        console.error(e);
    }
}

async function removeTrusted(id, btn) {
    if (!confirm('Remove this user from trusted list?')) return;

    try {
        const response = await fetch(`/admin/moderation/trusted/${id}/remove`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `_token=${csrfToken}`
        });

        const data = await response.json();
        if (data.success) {
            btn.closest('.flex').remove();
        }
    } catch (e) {
        console.error(e);
    }
}

function updateCounts() {
    const remaining = document.querySelectorAll('[data-image-id]').length;
    const header = document.querySelector('h2');
    if (header && header.textContent.includes('Pending')) {
        header.textContent = `Pending Moderation (${remaining})`;
    }
}
</script>
