<!-- Header Actions -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex items-center gap-4">
        <span class="text-neutral-600"><?= number_format($total) ?> images</span>
    </div>
    <a href="<?= $view->url('/admin/images/upload') ?>" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
        </svg>
        Upload Images
    </a>
</div>

<!-- Bulk Actions Bar (hidden by default) -->
<div id="bulkActionsBar" class="hidden bg-neutral-800 text-white rounded-lg shadow p-4 mb-6 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <span id="selectedCount">0</span> images selected
        <button onclick="selectAll()" class="text-sm text-neutral-300 hover:text-white underline">Select All</button>
        <button onclick="clearSelection()" class="text-sm text-neutral-300 hover:text-white underline">Clear</button>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="bulkAction('publish')" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 rounded text-sm">
            Publish
        </button>
        <button onclick="bulkAction('unpublish')" class="px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 rounded text-sm">
            Unpublish
        </button>
        <button onclick="bulkAction('delete')" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 rounded text-sm">
            Delete
        </button>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4">
        <div>
            <select name="status" class="px-3 py-2 border border-neutral-300 rounded-lg text-sm">
                <option value="">All Status</option>
                <option value="draft" <?= $filters['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= $filters['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="archived" <?= $filters['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>
        <div>
            <select name="moderation" class="px-3 py-2 border border-neutral-300 rounded-lg text-sm">
                <option value="">All Moderation</option>
                <option value="pending" <?= $filters['moderation'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $filters['moderation'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= $filters['moderation'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search images..."
                   class="w-full px-3 py-2 border border-neutral-300 rounded-lg text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-neutral-800 text-white rounded-lg text-sm hover:bg-neutral-700">
            Filter
        </button>
        <?php if (!empty($filters['status']) || !empty($filters['moderation']) || !empty($filters['search'])): ?>
        <a href="<?= $view->url('/admin/images') ?>" class="px-4 py-2 border border-neutral-300 rounded-lg text-sm hover:bg-neutral-50">
            Clear
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Images Grid -->
<?php if (empty($images)): ?>
<div class="bg-white rounded-lg shadow p-12 text-center">
    <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    <h3 class="mt-4 text-lg font-medium text-neutral-900">No images found</h3>
    <p class="mt-2 text-neutral-500">Get started by uploading your first image.</p>
    <a href="<?= $view->url('/admin/images/upload') ?>" class="mt-4 inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
        Upload Images
    </a>
</div>
<?php else: ?>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <!-- Select All Header -->
    <div class="px-4 py-3 border-b border-neutral-200 flex items-center gap-3 bg-neutral-50">
        <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" class="w-4 h-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
        <label for="selectAllCheckbox" class="text-sm text-neutral-600">Select all on this page</label>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-1 p-1">
        <?php foreach ($images as $image): ?>
        <div class="group relative aspect-square bg-neutral-100 rounded overflow-hidden" data-image-id="<?= $image['id'] ?>">
            <!-- Checkbox -->
            <div class="absolute top-2 left-2 z-20">
                <input type="checkbox"
                       class="image-checkbox w-5 h-5 rounded border-2 border-white bg-white/80 text-primary-600 focus:ring-primary-500 cursor-pointer shadow"
                       value="<?= $image['id'] ?>"
                       onchange="updateSelection()">
            </div>

            <?php if ($image['thumbnail_path']): ?>
                <img src="<?= $view->url('/uploads/' . $image['thumbnail_path']) ?>"
                     alt="<?= e($image['title']) ?>"
                     class="w-full h-full object-cover">
            <?php else: ?>
                <div class="w-full h-full flex items-center justify-center text-neutral-400">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            <?php endif; ?>

            <!-- Overlay -->
            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                <a href="<?= $view->url("/admin/images/{$image['id']}/edit") ?>"
                   class="p-2 bg-white rounded-full text-neutral-700 hover:bg-neutral-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </a>
                <button onclick="deleteImage(<?= $image['id'] ?>)"
                        class="p-2 bg-white rounded-full text-red-600 hover:bg-red-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>

            <!-- Status Badges -->
            <div class="absolute top-2 right-2 flex gap-1">
                <span class="px-1.5 py-0.5 text-xs rounded
                    <?php if ($image['status'] === 'published'): ?>bg-green-500 text-white
                    <?php elseif ($image['status'] === 'draft'): ?>bg-yellow-500 text-white
                    <?php else: ?>bg-neutral-500 text-white<?php endif; ?>">
                    <?= ucfirst($image['status']) ?>
                </span>
            </div>

            <?php if ($image['moderation_status'] === 'pending'): ?>
            <div class="absolute bottom-2 right-2">
                <span class="px-1.5 py-0.5 text-xs bg-orange-500 text-white rounded">Review</span>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div class="mt-6 flex justify-center">
    <nav class="flex gap-1">
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&status=<?= e($filters['status']) ?>&moderation=<?= e($filters['moderation']) ?>&search=<?= e($filters['search']) ?>"
           class="px-3 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50">Previous</a>
        <?php endif; ?>

        <?php
        $start = max(1, $page - 2);
        $end = min($totalPages, $page + 2);
        for ($i = $start; $i <= $end; $i++):
        ?>
        <a href="?page=<?= $i ?>&status=<?= e($filters['status']) ?>&moderation=<?= e($filters['moderation']) ?>&search=<?= e($filters['search']) ?>"
           class="px-3 py-2 border rounded-lg <?= $i === $page ? 'bg-primary-600 text-white border-primary-600' : 'border-neutral-300 hover:bg-neutral-50' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>&status=<?= e($filters['status']) ?>&moderation=<?= e($filters['moderation']) ?>&search=<?= e($filters['search']) ?>"
           class="px-3 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50">Next</a>
        <?php endif; ?>
    </nav>
</div>
<?php endif; ?>
<?php endif; ?>

<script>
const csrfToken = '<?= $_SESSION['_csrf_token'] ?? '' ?>';
const bulkUrl = '<?= $view->url('/admin/images/bulk') ?>';

// Track selected images
let selectedIds = new Set();

function updateSelection() {
    const checkboxes = document.querySelectorAll('.image-checkbox');
    selectedIds.clear();

    checkboxes.forEach(cb => {
        if (cb.checked) {
            selectedIds.add(parseInt(cb.value));
        }
    });

    // Update UI
    const bar = document.getElementById('bulkActionsBar');
    const count = document.getElementById('selectedCount');
    const selectAllCb = document.getElementById('selectAllCheckbox');

    if (selectedIds.size > 0) {
        bar.classList.remove('hidden');
        count.textContent = selectedIds.size;
    } else {
        bar.classList.add('hidden');
    }

    // Update select all checkbox state
    selectAllCb.checked = checkboxes.length > 0 && selectedIds.size === checkboxes.length;
    selectAllCb.indeterminate = selectedIds.size > 0 && selectedIds.size < checkboxes.length;
}

function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.image-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelection();
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.image-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = true;
    });
    document.getElementById('selectAllCheckbox').checked = true;
    updateSelection();
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.image-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = false;
    });
    document.getElementById('selectAllCheckbox').checked = false;
    updateSelection();
}

function bulkAction(action) {
    if (selectedIds.size === 0) {
        alert('No images selected');
        return;
    }

    const actionLabels = {
        'publish': 'publish',
        'unpublish': 'unpublish',
        'delete': 'permanently delete'
    };

    if (!confirm(`Are you sure you want to ${actionLabels[action]} ${selectedIds.size} image(s)?`)) {
        return;
    }

    // Show loading state
    const bar = document.getElementById('bulkActionsBar');
    bar.innerHTML = '<div class="flex items-center gap-2"><svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...</div>';

    // Build form data
    const formData = new FormData();
    formData.append('action', action);
    Array.from(selectedIds).forEach(id => {
        formData.append('ids[]', id);
    });

    fetch(bulkUrl, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Operation failed');
            location.reload();
        }
    })
    .catch(err => {
        alert('Error performing bulk action');
        location.reload();
    });
}

function deleteImage(id) {
    if (!confirm('Are you sure you want to delete this image?')) return;

    fetch(`<?= $view->url('/admin/images') ?>/${id}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to delete image');
        }
    })
    .catch(err => alert('Error deleting image'));
}
</script>
