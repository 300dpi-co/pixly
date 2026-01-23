<div class="max-w-4xl">
    <!-- Info Banner -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex">
            <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-blue-800">Bulk Upload with Scheduling</h3>
                <p class="text-sm text-blue-600 mt-1">
                    Upload multiple images at once. For 4+ images, you'll be able to schedule
                    them for staggered publishing (3-5 minute intervals) to avoid flooding your gallery.
                </p>
            </div>
        </div>
    </div>

    <form action="<?= $view->url('/admin/bulk-upload') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <?= $view->csrf() ?>

        <!-- Upload Area -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-neutral-900 mb-4">Select Images</h2>

            <div class="border-2 border-dashed border-neutral-300 rounded-lg p-8 text-center hover:border-primary-500 transition-colors" id="dropzone">
                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="mt-4 text-neutral-600">Drag and drop images here, or click to select</p>
                <p class="mt-2 text-sm text-neutral-500">Supports JPG, PNG, GIF, WebP (max 10MB each)</p>
                <p class="mt-1 text-sm text-primary-600 font-medium">Select 4+ images for scheduling options</p>

                <input type="file" name="images[]" id="images" multiple accept="image/jpeg,image/png,image/gif,image/webp"
                       class="hidden">

                <button type="button" onclick="document.getElementById('images').click()"
                        class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Select Files
                </button>
            </div>

            <!-- Preview Area -->
            <div id="preview" class="mt-6 hidden">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium text-neutral-700">Selected Images (<span id="fileCount">0</span>)</h3>
                    <button type="button" onclick="clearFiles()" class="text-sm text-red-600 hover:text-red-800">
                        Clear All
                    </button>
                </div>
                <div id="previewGrid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3"></div>
            </div>
        </div>

        <!-- Options -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-neutral-900 mb-4">Options</h2>

            <div class="space-y-4">
                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-neutral-700">Category (optional)</label>
                    <select name="category_id" id="category_id"
                            class="mt-1 block w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= e($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-xs text-neutral-500">All images will be added to this category</p>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-4">
            <a href="<?= $view->url('/admin/images') ?>" class="px-6 py-2 border border-neutral-300 rounded-lg text-neutral-700 hover:bg-neutral-50">
                Cancel
            </a>
            <button type="submit" id="uploadBtn" disabled
                    class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed">
                Upload Images
            </button>
        </div>
    </form>

    <!-- Recent Batches -->
    <?php if (!empty($batches)): ?>
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-neutral-900 mb-4">Recent Batches</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 uppercase">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 uppercase">Images</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 uppercase">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200">
                    <?php foreach ($batches as $batch): ?>
                    <tr>
                        <td class="px-4 py-3 text-sm text-neutral-600">
                            <?= date('M j, g:i A', strtotime($batch['created_at'])) ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-neutral-600">
                            <?= $batch['published_count'] ?>/<?= $batch['image_count'] ?> published
                        </td>
                        <td class="px-4 py-3">
                            <?php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'processing' => 'bg-blue-100 text-blue-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-neutral-100 text-neutral-800',
                            ];
                            $color = $statusColors[$batch['status']] ?? 'bg-neutral-100 text-neutral-800';
                            ?>
                            <span class="px-2 py-1 text-xs rounded-full <?= $color ?>">
                                <?= ucfirst($batch['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <a href="<?= $view->url('/admin/bulk-upload/status/' . $batch['uuid']) ?>"
                               class="text-primary-600 hover:text-primary-800">View Status</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('images');
    const preview = document.getElementById('preview');
    const previewGrid = document.getElementById('previewGrid');
    const uploadBtn = document.getElementById('uploadBtn');
    const fileCount = document.getElementById('fileCount');
    const dropzone = document.getElementById('dropzone');

    input.addEventListener('change', handleFiles);

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('border-primary-500', 'bg-primary-50');
    });

    dropzone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-primary-500', 'bg-primary-50');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-primary-500', 'bg-primary-50');
        input.files = e.dataTransfer.files;
        handleFiles();
    });

    window.clearFiles = function() {
        input.value = '';
        handleFiles();
    };

    function handleFiles() {
        const files = input.files;
        previewGrid.innerHTML = '';

        if (files.length > 0) {
            preview.classList.remove('hidden');
            uploadBtn.disabled = false;
            fileCount.textContent = files.length;

            // Update button text based on count
            if (files.length >= 4) {
                uploadBtn.textContent = 'Upload & Schedule ' + files.length + ' Images';
            } else {
                uploadBtn.textContent = 'Upload ' + files.length + ' Image' + (files.length > 1 ? 's' : '');
            }

            Array.from(files).forEach((file, index) => {
                if (!file.type.startsWith('image/')) return;

                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'relative aspect-square bg-neutral-100 rounded-lg overflow-hidden';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-full object-cover" alt="">
                        <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs p-1.5 truncate">
                            ${file.name}
                        </div>
                    `;
                    previewGrid.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        } else {
            preview.classList.add('hidden');
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Upload Images';
        }
    }
});
</script>
