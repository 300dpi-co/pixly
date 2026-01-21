<div class="space-y-6">
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-neutral-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold">Ad Placements</h2>
        </div>
        <div class="p-6">
            <?php if (empty($placements)): ?>
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="mt-4 text-neutral-500">No ad placements configured yet.</p>
                    <p class="mt-2 text-sm text-neutral-400">Add JuicyAds or other ad network codes to start monetizing.</p>
                </div>
            <?php else: ?>
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-sm text-neutral-500 border-b">
                            <th class="pb-3">Name</th>
                            <th class="pb-3">Location</th>
                            <th class="pb-3">Size</th>
                            <th class="pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($placements as $placement): ?>
                        <tr>
                            <td class="py-3 font-medium"><?= e($placement['name']) ?></td>
                            <td class="py-3"><?= ucfirst(str_replace('_', ' ', $placement['location'] ?? '')) ?></td>
                            <td class="py-3"><?= e($placement['default_size'] ?? '-') ?></td>
                            <td class="py-3">
                                <span class="px-2 py-1 text-xs rounded-full <?= $placement['is_active'] ? 'bg-green-100 text-green-800' : 'bg-neutral-100 text-neutral-600' ?>">
                                    <?= $placement['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
