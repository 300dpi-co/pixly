<div class="max-w-lg mx-auto px-4 py-8">
    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
            <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Upload Status</h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1" id="statusMessage">
                <?php
                switch ($queueStatus['status']) {
                    case 'processing':
                        echo 'Generating title and tags...';
                        break;
                    case 'queued':
                        echo 'In queue for processing...';
                        break;
                    case 'published':
                        echo 'Published successfully!';
                        break;
                    default:
                        echo 'Processing your image...';
                }
                ?>
            </p>
        </div>

        <!-- Image Preview -->
        <?php if ($image['thumbnail_path']): ?>
        <div class="aspect-video bg-neutral-100 dark:bg-neutral-900 flex items-center justify-center">
            <img src="<?= $view->url('/uploads/' . $image['thumbnail_path']) ?>"
                 alt="<?= e($image['title']) ?>"
                 class="max-w-full max-h-full object-contain">
        </div>
        <?php endif; ?>

        <!-- Status Details -->
        <div class="p-6">
            <!-- Progress Indicator -->
            <div id="progressSection" class="mb-6 <?= $image['status'] === 'published' ? 'hidden' : '' ?>">
                <div class="flex items-center justify-center">
                    <div class="relative">
                        <!-- Spinning loader -->
                        <div id="spinner" class="w-12 h-12 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin"></div>
                    </div>
                </div>

                <!-- Queue Position -->
                <?php if ($queueStatus['queue_position'] && $queueStatus['queue_position'] > 1): ?>
                <div class="text-center mt-4">
                    <span class="text-sm text-neutral-500 dark:text-neutral-400">
                        Position in queue: <span id="queuePosition" class="font-medium text-neutral-900 dark:text-white"><?= $queueStatus['queue_position'] ?></span>
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Success State -->
            <div id="successSection" class="<?= $image['status'] !== 'published' ? 'hidden' : '' ?>">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
                <p class="text-center text-neutral-600 dark:text-neutral-300">
                    Your image has been published!
                </p>
            </div>

            <!-- Image Details -->
            <div class="mt-6 p-4 bg-neutral-50 dark:bg-neutral-900 rounded-lg">
                <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Image Details</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-neutral-500 dark:text-neutral-400">Title</dt>
                        <dd id="imageTitle" class="text-neutral-900 dark:text-white font-medium"><?= e($image['title']) ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-neutral-500 dark:text-neutral-400">Status</dt>
                        <dd>
                            <span id="statusBadge" class="px-2 py-0.5 text-xs rounded-full
                                <?php
                                $statusColors = [
                                    'processing' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    'queued' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                    'published' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                    'draft' => 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300',
                                ];
                                echo $statusColors[$image['status']] ?? 'bg-neutral-100 text-neutral-800';
                                ?>">
                                <?= ucfirst($image['status']) ?>
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Actions -->
            <div class="mt-6 space-y-3">
                <a href="#" id="viewImageLink" class="<?= $image['status'] !== 'published' ? 'hidden' : '' ?> block w-full px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white text-center font-medium rounded-lg transition">
                    View Image
                </a>
                <a href="<?= $view->url('/upload') ?>" class="block w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 text-neutral-700 dark:text-neutral-300 text-center font-medium rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 transition">
                    Upload Another
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageId = <?= (int) $image['id'] ?>;
    const statusMessage = document.getElementById('statusMessage');
    const progressSection = document.getElementById('progressSection');
    const successSection = document.getElementById('successSection');
    const statusBadge = document.getElementById('statusBadge');
    const queuePosition = document.getElementById('queuePosition');
    const viewImageLink = document.getElementById('viewImageLink');
    const imageTitle = document.getElementById('imageTitle');

    let pollInterval = null;
    let pollCount = 0;
    const maxPolls = 120; // Stop after 2 minutes (1 poll/second)

    function updateUI(data) {
        // Update status message
        statusMessage.textContent = data.message || 'Processing...';

        // Update queue position
        if (data.queue_position && queuePosition) {
            queuePosition.textContent = data.queue_position;
        }

        // Update title if changed
        if (data.title && imageTitle) {
            imageTitle.textContent = data.title;
        }

        // Update status badge
        if (statusBadge) {
            statusBadge.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);

            const colors = {
                'processing': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                'queued': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                'published': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
            };
            statusBadge.className = 'px-2 py-0.5 text-xs rounded-full ' + (colors[data.status] || 'bg-neutral-100 text-neutral-800');
        }

        // Handle completion
        if (data.is_complete) {
            stopPolling();

            if (data.status === 'published') {
                progressSection.classList.add('hidden');
                successSection.classList.remove('hidden');
                viewImageLink.classList.remove('hidden');
                viewImageLink.href = data.redirect_url || '/image/' + data.slug;
            }
        }
    }

    function pollStatus() {
        pollCount++;
        if (pollCount > maxPolls) {
            stopPolling();
            statusMessage.textContent = 'Processing is taking longer than expected. Please check back later.';
            return;
        }

        fetch('/api/queue/status/' + imageId, {
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateUI(data);
            }
        })
        .catch(error => {
            console.error('Poll error:', error);
        });
    }

    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    // Start polling if not already complete
    const isComplete = <?= $image['status'] === 'published' ? 'true' : 'false' ?>;
    if (!isComplete) {
        // Initial poll after 1 second
        setTimeout(pollStatus, 1000);

        // Then poll every 2 seconds
        pollInterval = setInterval(pollStatus, 2000);
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', stopPolling);
});
</script>
