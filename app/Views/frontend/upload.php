<div class="max-w-xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-semibold text-neutral-900 dark:text-white mb-6">Upload Image</h1>

    <form action="<?= $view->url('/upload') ?>" method="POST" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <!-- Image Upload -->
        <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Image *</label>
            <div id="dropZone" class="border-2 border-dashed border-neutral-300 dark:border-neutral-600 rounded-lg p-8 text-center hover:border-primary-500 transition cursor-pointer">
                <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/gif,image/webp" required class="hidden">
                <div id="dropContent">
                    <svg class="w-10 h-10 mx-auto text-neutral-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-neutral-600 dark:text-neutral-400 text-sm">Drop image here or <span class="text-primary-600">browse</span></p>
                    <p class="text-neutral-400 dark:text-neutral-500 text-xs mt-1">JPG, PNG, GIF, WebP up to 10MB</p>
                </div>
                <div id="previewContainer" class="hidden">
                    <img id="imagePreview" class="max-h-48 mx-auto rounded" alt="Preview">
                    <p id="fileName" class="text-sm text-neutral-600 dark:text-neutral-400 mt-2"></p>
                </div>
            </div>
        </div>

        <!-- Title -->
        <div>
            <label for="title" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Title</label>
            <input type="text" name="title" id="title" maxlength="100"
                   class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white rounded-lg focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                   placeholder="Leave empty to auto-generate from filename">
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Description</label>
            <textarea name="description" id="description" rows="3" maxlength="500"
                      class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white rounded-lg focus:ring-1 focus:ring-primary-500 focus:border-primary-500 resize-none"
                      placeholder="Optional description"></textarea>
        </div>

        <!-- Category -->
        <div>
            <label for="category_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Category</label>
            <select name="category_id" id="category_id"
                    class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white rounded-lg focus:ring-1 focus:ring-primary-500">
                <option value="">Select category...</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Tags -->
        <div>
            <label for="tags" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Tags</label>
            <input type="text" name="tags" id="tags" maxlength="200"
                   class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white rounded-lg focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                   placeholder="nature, landscape, sunset (comma separated)">
        </div>

        <!-- Guidelines -->
        <div class="bg-neutral-50 dark:bg-neutral-800 rounded-lg p-4 text-xs text-neutral-500 dark:text-neutral-400">
            <p class="font-medium text-neutral-700 dark:text-neutral-300 mb-2">Upload Guidelines:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>Only upload images you have rights to share</li>
                <li>No copyrighted content without permission</li>
                <li>Images are reviewed before publishing</li>
                <li>Maximum file size: 10MB</li>
            </ul>
        </div>

        <!-- Submit -->
        <button type="submit" class="w-full px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">
            Upload Image
        </button>
    </form>
</div>

<script>
const dropZone = document.getElementById('dropZone');
const imageInput = document.getElementById('imageInput');
const dropContent = document.getElementById('dropContent');
const previewContainer = document.getElementById('previewContainer');
const imagePreview = document.getElementById('imagePreview');
const fileName = document.getElementById('fileName');

dropZone.addEventListener('click', () => imageInput.click());

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-primary-500', 'bg-primary-50', 'dark:bg-primary-900/20');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-primary-500', 'bg-primary-50', 'dark:bg-primary-900/20');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-primary-500', 'bg-primary-50', 'dark:bg-primary-900/20');
    if (e.dataTransfer.files.length) {
        imageInput.files = e.dataTransfer.files;
        showPreview(e.dataTransfer.files[0]);
    }
});

imageInput.addEventListener('change', () => {
    if (imageInput.files.length) {
        showPreview(imageInput.files[0]);
    }
});

function showPreview(file) {
    if (!file.type.startsWith('image/')) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.src = e.target.result;
        fileName.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
        dropContent.classList.add('hidden');
        previewContainer.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}
</script>
