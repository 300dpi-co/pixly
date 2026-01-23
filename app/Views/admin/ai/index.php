<div class="space-y-6">
    <!-- Status Banner -->
    <?php if (!$isConfigured): ?>
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
            <svg class="h-5 w-5 text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">API Key Not Configured</h3>
                <p class="mt-1 text-sm text-yellow-700">Add your <?= e($providerName ?? 'AI') ?> API key in Settings > API Keys</p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex">
            <svg class="h-5 w-5 text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800"><?= e($providerName ?? 'AI') ?> Connected</h3>
                <p class="mt-1 text-sm text-green-700">AI processing is ready to use.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-neutral-500">Pending</p>
            <p class="text-2xl font-semibold text-yellow-600"><?= $stats['pending'] ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-neutral-500">Processing</p>
            <p class="text-2xl font-semibold text-blue-600"><?= $stats['processing'] ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-neutral-500">Completed</p>
            <p class="text-2xl font-semibold text-green-600"><?= $stats['completed'] ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-neutral-500">Failed</p>
            <p class="text-2xl font-semibold text-red-600"><?= $stats['failed'] ?></p>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Actions</h2>
        <?php if ($isConfigured): ?>
        <div class="flex flex-wrap gap-3">
            <form action="<?= $view->url('/admin/ai/queue') ?>" method="POST" class="inline">
                <?= $view->csrf() ?>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Queue All Unprocessed
                </button>
            </form>
            <form action="<?= $view->url('/admin/ai/process') ?>" method="POST" class="inline">
                <?= $view->csrf() ?>
                <input type="hidden" name="limit" value="5">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                        <?= $stats['pending'] == 0 ? 'disabled' : '' ?>>
                    Process Queue (5)
                </button>
            </form>
            <?php if ($stats['failed'] > 0): ?>
            <form action="<?= $view->url('/admin/ai/retry-failed') ?>" method="POST" class="inline">
                <?= $view->csrf() ?>
                <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    Retry Failed
                </button>
            </form>
            <form action="<?= $view->url('/admin/ai/clear-failed') ?>" method="POST" class="inline">
                <?= $view->csrf() ?>
                <button type="submit" class="px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50">
                    Clear Failed
                </button>
            </form>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-6">
            <svg class="w-12 h-12 mx-auto text-neutral-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            <p class="text-neutral-600 mb-4">Add your API key to enable AI processing</p>
            <a href="<?= $view->url('/admin/settings') ?>" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Go to Settings
            </a>
            <p class="text-sm text-neutral-500 mt-4">
                Get API keys from:
                <a href="https://openrouter.ai/keys" target="_blank" class="text-primary-600 hover:underline">OpenRouter</a> (recommended, $0.30/1000 images) |
                <a href="https://stablehorde.net/register" target="_blank" class="text-primary-600 hover:underline">AI Horde</a> (free, slow) |
                <a href="https://replicate.com/account/api-tokens" target="_blank" class="text-primary-600 hover:underline">Replicate</a>
            </p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Unprocessed Images -->
    <?php if (!empty($unprocessed)): ?>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-neutral-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold">Unprocessed Images (<?= count($unprocessed) ?>)</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php foreach ($unprocessed as $image): ?>
                <div class="group relative aspect-square bg-neutral-100 rounded-lg overflow-hidden">
                    <?php if ($image['thumbnail_path']): ?>
                        <img src="<?= $view->url('/uploads/' . $image['thumbnail_path']) ?>" alt="" class="w-full h-full object-cover">
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <form action="<?= $view->url('/admin/ai/process/' . $image['id']) ?>" method="POST">
                            <?= $view->csrf() ?>
                            <button type="submit" class="px-3 py-1.5 bg-white text-sm rounded hover:bg-neutral-100">
                                Process Now
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Queue -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-neutral-200">
            <h2 class="text-lg font-semibold">Processing Queue</h2>
        </div>
        <div class="p-6">
            <?php if (empty($queue)): ?>
                <p class="text-neutral-500 text-center py-8">No images in the queue.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($queue as $item): ?>
                    <div class="flex items-center gap-4 p-3 bg-neutral-50 rounded-lg">
                        <div class="w-12 h-12 bg-neutral-200 rounded overflow-hidden flex-shrink-0">
                            <?php if ($item['thumbnail_path']): ?>
                                <img src="<?= $view->url('/uploads/' . $item['thumbnail_path']) ?>" alt="" class="w-full h-full object-cover">
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium truncate"><?= e($item['title']) ?></p>
                            <p class="text-sm text-neutral-500">
                                <?= $item['task_type'] ?> | Attempts: <?= $item['attempts'] ?>
                                <?php if ($item['error_message']): ?>
                                    <span class="text-red-500">| <?= e($item['error_message']) ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full flex-shrink-0
                            <?= $item['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                               ($item['status'] === 'processing' ? 'bg-blue-100 text-blue-800' :
                               ($item['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) ?>">
                            <?= ucfirst($item['status']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- API Logs -->
    <?php if (!empty($apiLogs)): ?>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-neutral-200">
            <h2 class="text-lg font-semibold">Recent API Calls</h2>
        </div>
        <div class="p-6 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-neutral-500 border-b">
                        <th class="pb-2">Time</th>
                        <th class="pb-2">Endpoint</th>
                        <th class="pb-2">Tokens</th>
                        <th class="pb-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($apiLogs as $log): ?>
                    <tr>
                        <td class="py-2"><?= date('M j, g:i A', strtotime($log['created_at'])) ?></td>
                        <td class="py-2 text-neutral-600"><?= e($log['endpoint']) ?></td>
                        <td class="py-2"><?= $log['tokens_used'] ?? '-' ?></td>
                        <td class="py-2">
                            <?php if ($log['error_message']): ?>
                                <span class="text-red-600"><?= e($log['error_message']) ?></span>
                            <?php else: ?>
                                <span class="text-green-600">Success</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
