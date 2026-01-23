<div class="max-w-2xl mx-auto">
    <!-- Success Banner -->
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <div class="flex">
            <svg class="w-5 h-5 text-green-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-green-800">Images Uploaded Successfully!</h3>
                <p class="text-sm text-green-600 mt-1">
                    <?= $count ?> images have been uploaded. Choose how you want to publish them.
                </p>
            </div>
        </div>
    </div>

    <form action="<?= $view->url('/admin/bulk-upload/schedule/' . $batch['uuid']) ?>" method="POST" class="space-y-6">
        <?= $view->csrf() ?>

        <!-- Scheduling Options -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-neutral-900 mb-4">Choose Publishing Schedule</h2>

            <div class="space-y-4">
                <!-- Option 1: Auto-publish now -->
                <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-neutral-50 transition schedule-option" data-option="auto_publish">
                    <input type="radio" name="schedule_type" value="auto_publish" checked
                           class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300">
                    <div class="ml-4">
                        <span class="block font-medium text-neutral-900">24/7 Auto-Publish (Recommended)</span>
                        <span class="block text-sm text-neutral-500 mt-1">
                            Start publishing immediately with automatic intervals.
                            Images will be published one by one with gaps between them.
                        </span>
                    </div>
                </label>

                <!-- Option 2: Schedule for later -->
                <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-neutral-50 transition schedule-option" data-option="scheduled">
                    <input type="radio" name="schedule_type" value="scheduled"
                           class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300">
                    <div class="ml-4">
                        <span class="block font-medium text-neutral-900">Schedule for Later</span>
                        <span class="block text-sm text-neutral-500 mt-1">
                            Pick a specific date and time to start publishing.
                        </span>
                    </div>
                </label>
            </div>

            <!-- Scheduled Date/Time (hidden by default) -->
            <div id="scheduledOptions" class="mt-6 hidden">
                <label class="block text-sm font-medium text-neutral-700">Start Date & Time</label>
                <input type="datetime-local" name="start_at" id="start_at"
                       min="<?= date('Y-m-d\TH:i') ?>"
                       class="mt-1 block w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <p class="mt-1 text-xs text-neutral-500">Select when you want to start publishing</p>
            </div>

            <!-- Interval -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-neutral-700">Publish Interval</label>
                <select name="interval" class="mt-1 block w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="3">Every 3 minutes</option>
                    <option value="4" selected>Every 4 minutes (Recommended)</option>
                    <option value="5">Every 5 minutes</option>
                    <option value="10">Every 10 minutes</option>
                </select>
                <p class="mt-1 text-xs text-neutral-500">
                    With <?= $count ?> images at 4 minute intervals, publishing will complete in approximately
                    <strong><?= round(($count * 4) / 60, 1) ?> hours</strong>
                </p>
            </div>
        </div>

        <!-- Preview -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-neutral-900 mb-4">Images to Publish (<?= $count ?>)</h2>
            <div class="grid grid-cols-4 md:grid-cols-6 gap-2 max-h-48 overflow-y-auto">
                <?php foreach ($images as $image): ?>
                <div class="aspect-square bg-neutral-100 rounded overflow-hidden">
                    <?php if ($image['thumbnail_path']): ?>
                    <img src="<?= $view->url('/uploads/' . $image['thumbnail_path']) ?>"
                         class="w-full h-full object-cover"
                         alt="<?= e($image['title']) ?>">
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-neutral-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-between">
            <a href="<?= $view->url('/admin/bulk-upload') ?>"
               class="px-6 py-2 border border-neutral-300 rounded-lg text-neutral-700 hover:bg-neutral-50">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                Start Publishing
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scheduledOptions = document.getElementById('scheduledOptions');
    const radios = document.querySelectorAll('input[name="schedule_type"]');
    const options = document.querySelectorAll('.schedule-option');

    function updateUI() {
        radios.forEach(radio => {
            const option = radio.closest('.schedule-option');
            if (radio.checked) {
                option.classList.add('border-primary-500', 'bg-primary-50');
            } else {
                option.classList.remove('border-primary-500', 'bg-primary-50');
            }
        });

        const scheduled = document.querySelector('input[value="scheduled"]').checked;
        if (scheduled) {
            scheduledOptions.classList.remove('hidden');
        } else {
            scheduledOptions.classList.add('hidden');
        }
    }

    radios.forEach(radio => {
        radio.addEventListener('change', updateUI);
    });

    options.forEach(option => {
        option.addEventListener('click', function(e) {
            if (e.target.tagName !== 'INPUT') {
                this.querySelector('input[type="radio"]').checked = true;
                updateUI();
            }
        });
    });

    updateUI();
});
</script>
