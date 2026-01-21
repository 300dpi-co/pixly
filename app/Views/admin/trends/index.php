<div class="space-y-6">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Trends Feature</h3>
                <p class="mt-1 text-sm text-blue-700">Trend data is fetched automatically via cron job. Configure Google Trends API access to enable this feature.</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-neutral-200">
            <h2 class="text-lg font-semibold">Trending Keywords</h2>
        </div>
        <div class="p-6">
            <?php if (empty($trends)): ?>
                <p class="text-neutral-500 text-center py-8">No trending keywords yet. Run the trends cron job to populate this data.</p>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($trends as $trend): ?>
                    <div class="flex items-center justify-between p-3 bg-neutral-50 rounded-lg">
                        <div>
                            <span class="font-medium"><?= e($trend['keyword']) ?></span>
                            <span class="ml-2 text-sm text-neutral-500"><?= $trend['source'] ?></span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-neutral-500">Score: <?= $trend['trend_score'] ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
