<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-neutral-200">
        <h2 class="text-lg font-semibold">Tags (<?= count($tags) ?>)</h2>
    </div>
    <div class="p-6">
        <?php if (empty($tags)): ?>
            <p class="text-neutral-500 text-center py-8">No tags found. Tags are created when you add them to images.</p>
        <?php else: ?>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($tags as $tag): ?>
                <span class="inline-flex items-center px-3 py-1.5 bg-neutral-100 text-neutral-700 rounded-full text-sm">
                    <?= e($tag['name']) ?>
                    <span class="ml-2 text-xs text-neutral-500">(<?= $tag['usage_count'] ?>)</span>
                </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
