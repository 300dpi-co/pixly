<div class="max-w-4xl">
    <form action="<?= $view->url('/admin/images/upload') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
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

                <input type="file" name="images[]" id="images" multiple accept="image/jpeg,image/png,image/gif,image/webp"
                       class="hidden">

                <button type="button" onclick="document.getElementById('images').click()"
                        class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Select Files
                </button>
            </div>

            <!-- Preview Area -->
            <div id="preview" class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4 hidden"></div>
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
                </div>

                <!-- AI Processing -->
                <div class="flex items-center">
                    <input type="checkbox" name="ai_process" id="ai_process"
                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 rounded">
                    <label for="ai_process" class="ml-2 block text-sm text-neutral-700">
                        Queue for AI processing (auto-generate titles, descriptions, tags)
                    </label>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('images');
    const preview = document.getElementById('preview');
    const uploadBtn = document.getElementById('uploadBtn');
    const dropzone = document.getElementById('dropzone');

    // File input change
    input.addEventListener('change', handleFiles);

    // Drag and drop
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

        const dt = e.dataTransfer;
        input.files = dt.files;
        handleFiles();
    });

    function handleFiles() {
        const files = input.files;
        preview.innerHTML = '';

        if (files.length > 0) {
            preview.classList.remove('hidden');
            uploadBtn.disabled = false;

            Array.from(files).forEach((file, index) => {
                if (!file.type.startsWith('image/')) return;

                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'relative aspect-square bg-neutral-100 rounded-lg overflow-hidden';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-full object-cover" alt="">
                        <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs p-2 truncate">
                            ${file.name}
                        </div>
                    `;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        } else {
            preview.classList.add('hidden');
            uploadBtn.disabled = true;
        }
    }
});
</script>
