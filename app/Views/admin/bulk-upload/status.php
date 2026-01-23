<div class="max-w-4xl">
    <!-- Status Header -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-neutral-900">Batch Status</h2>
                <p class="text-sm text-neutral-500 mt-1">
                    Created <?= format_datetime($batch['created_at']) ?>
                </p>
            </div>
            <div>
                <?php
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'processing' => 'bg-blue-100 text-blue-800',
                    'completed' => 'bg-green-100 text-green-800',
                    'cancelled' => 'bg-neutral-100 text-neutral-800',
                ];
                $color = $statusColors[$batch['status']] ?? 'bg-neutral-100 text-neutral-800';
                ?>
                <span class="px-3 py-1 text-sm rounded-full <?= $color ?>">
                    <?= ucfirst($batch['status']) ?>
                </span>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mt-6">
            <div class="flex justify-between text-sm mb-2">
                <span class="text-neutral-600">Progress</span>
                <span class="font-medium text-neutral-900">
                    <?= $stats['published'] ?> / <?= $stats['total'] ?> published
                </span>
            </div>
            <?php $progress = $stats['total'] > 0 ? round(($stats['published'] / $stats['total']) * 100) : 0; ?>
            <div class="w-full bg-neutral-200 rounded-full h-3">
                <div class="bg-primary-600 h-3 rounded-full transition-all duration-500"
                     style="width: <?= $progress ?>%"></div>
            </div>
        </div>

        <!-- Stats -->
        <div class="mt-6 grid grid-cols-4 gap-4 text-center">
            <div class="p-3 bg-neutral-50 rounded-lg">
                <div class="text-2xl font-bold text-neutral-900"><?= $stats['total'] ?></div>
                <div class="text-xs text-neutral-500">Total</div>
            </div>
            <div class="p-3 bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600"><?= $stats['published'] ?></div>
                <div class="text-xs text-green-600">Published</div>
            </div>
            <div class="p-3 bg-blue-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600"><?= $stats['scheduled'] ?></div>
                <div class="text-xs text-blue-600">Scheduled</div>
            </div>
            <div class="p-3 bg-yellow-50 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600"><?= $stats['processing'] ?></div>
                <div class="text-xs text-yellow-600">Processing</div>
            </div>
        </div>

        <!-- Schedule Info -->
        <?php if ($batch['scheduled_start_at']): ?>
        <div class="mt-6 p-4 bg-neutral-50 rounded-lg">
            <div class="flex items-center text-sm">
                <svg class="w-5 h-5 text-neutral-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-neutral-600">
                    <?= $batch['schedule_type'] === 'auto_publish' ? 'Auto-publishing' : 'Scheduled publishing' ?>
                    started at <?= format_datetime($batch['scheduled_start_at'], 'M j, g:i A') ?>
                    with <?= $batch['publish_interval_minutes'] ?> minute intervals
                </span>
            </div>
        </div>
        <?php endif; ?>

        <?php
        // Count unpublished images
        $unpublishedCount = $stats['total'] - $stats['published'];
        ?>

        <!-- Action Buttons - Only show if there are unpublished images -->
        <?php if ($unpublishedCount > 0): ?>
        <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-yellow-800">
                        <?= $unpublishedCount ?> image<?= $unpublishedCount > 1 ? 's' : '' ?> remaining
                    </p>
                    <p class="text-xs text-yellow-600 mt-1">
                        Use these actions only if images are stuck or you want to change the schedule.
                    </p>
                </div>
                <div class="flex gap-2">
                    <form action="<?= $view->url('/admin/bulk-upload/reschedule/' . $batch['uuid']) ?>" method="POST" class="inline">
                        <?= $view->csrf() ?>
                        <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700"
                                onclick="return confirm('Reschedule all <?= $unpublishedCount ?> unpublished images starting from now?')">
                            Reschedule Now
                        </button>
                    </form>
                    <form action="<?= $view->url('/admin/bulk-upload/publish-all/' . $batch['uuid']) ?>" method="POST" class="inline">
                        <?= $view->csrf() ?>
                        <button type="submit" class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700"
                                onclick="return confirm('Publish all <?= $unpublishedCount ?> remaining images immediately without AI processing?')">
                            Publish All Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php elseif ($batch['status'] === 'completed'): ?>
        <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="text-sm font-medium text-green-800">
                    All images published successfully!
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php
    // Separate published and pending images
    $publishedImages = array_filter($images, fn($img) => $img['status'] === 'published');
    $pendingImages = array_filter($images, fn($img) => $img['status'] !== 'published');
    ?>

    <!-- Pending/Scheduled Images (show first if any) -->
    <?php if (!empty($pendingImages)): ?>
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="p-4 border-b border-neutral-200 bg-blue-50">
            <h3 class="font-semibold text-neutral-900">
                Pending Images (<?= count($pendingImages) ?>)
            </h3>
            <p class="text-xs text-neutral-500 mt-1">These images are scheduled or waiting to be published</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200">
                <thead class="bg-neutral-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Image</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Title</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Scheduled For</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200">
                    <?php foreach ($pendingImages as $image): ?>
                    <tr class="hover:bg-neutral-50">
                        <td class="px-4 py-3">
                            <div class="w-12 h-12 bg-neutral-100 rounded overflow-hidden">
                                <?php if ($image['thumbnail_path']): ?>
                                <img src="<?= $view->url('/uploads/' . $image['thumbnail_path']) ?>"
                                     class="w-full h-full object-cover" alt="">
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-neutral-900"><?= e($image['title']) ?></span>
                        </td>
                        <td class="px-4 py-3">
                            <?php if ($image['scheduled_at']): ?>
                            <span class="text-sm text-neutral-600">
                                <?= format_datetime($image['scheduled_at'], 'M j, g:i A') ?>
                            </span>
                            <?php else: ?>
                            <span class="text-sm text-neutral-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <?php
                            $imgStatusColors = [
                                'draft' => 'bg-neutral-100 text-neutral-800',
                                'processing' => 'bg-yellow-100 text-yellow-800',
                                'queued' => 'bg-blue-100 text-blue-800',
                                'scheduled' => 'bg-blue-100 text-blue-800',
                            ];
                            $imgColor = $imgStatusColors[$image['status']] ?? 'bg-neutral-100 text-neutral-800';
                            ?>
                            <span class="px-2 py-1 text-xs rounded-full <?= $imgColor ?>">
                                <?= ucfirst($image['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Published Images -->
    <?php if (!empty($publishedImages)): ?>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-neutral-200 bg-green-50">
            <h3 class="font-semibold text-neutral-900">
                Published Images (<?= count($publishedImages) ?>)
            </h3>
            <p class="text-xs text-neutral-500 mt-1">These images are live on your site</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200">
                <thead class="bg-neutral-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Image</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Title</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Published At</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200">
                    <?php foreach ($publishedImages as $image): ?>
                    <tr class="hover:bg-neutral-50">
                        <td class="px-4 py-3">
                            <div class="w-12 h-12 bg-neutral-100 rounded overflow-hidden">
                                <?php if ($image['thumbnail_path']): ?>
                                <img src="<?= $view->url('/uploads/' . $image['thumbnail_path']) ?>"
                                     class="w-full h-full object-cover" alt="">
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-neutral-900"><?= e($image['title']) ?></span>
                        </td>
                        <td class="px-4 py-3">
                            <?php if ($image['published_at']): ?>
                            <span class="text-sm text-neutral-600">
                                <?= format_datetime($image['published_at'], 'M j, g:i A') ?>
                            </span>
                            <?php else: ?>
                            <span class="text-sm text-neutral-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <?php if ($image['slug']): ?>
                            <a href="<?= $view->url('/image/' . $image['slug']) ?>"
                               target="_blank"
                               class="text-sm text-primary-600 hover:text-primary-800">
                                View
                            </a>
                            <?php else: ?>
                            <span class="text-sm text-neutral-400">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Back Link -->
    <div class="mt-6">
        <a href="<?= $view->url('/admin/upload') ?>"
           class="text-sm text-primary-600 hover:text-primary-800">
            &larr; Back to Upload
        </a>
    </div>
</div>

<?php if ($batch['status'] === 'processing' && $unpublishedCount > 0): ?>
<!-- Auto-refresh for processing batches -->
<script>
    // Refresh page every 30 seconds while processing
    setTimeout(function() {
        window.location.reload();
    }, 30000);
</script>
<?php endif; ?>
