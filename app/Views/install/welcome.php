<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-8 text-white text-center">
        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold mb-2">Welcome to Pixly</h1>
        <p class="text-primary-100">Let's get your image gallery up and running in just a few steps.</p>
    </div>

    <!-- Content -->
    <div class="p-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">System Requirements</h2>
        <p class="text-slate-600 mb-6">Before we begin, let's make sure your server meets all the requirements.</p>

        <!-- Requirements List -->
        <div class="space-y-3">
            <?php foreach ($requirements as $key => $req): ?>
            <div class="flex items-center justify-between p-3 rounded-lg <?= $req['passed'] ? 'bg-green-50 border border-green-100' : 'bg-red-50 border border-red-100' ?>">
                <div class="flex items-center gap-3">
                    <?php if ($req['passed']): ?>
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <?php else: ?>
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <?php endif; ?>
                    <span class="font-medium <?= $req['passed'] ? 'text-green-800' : 'text-red-800' ?>"><?= e($req['name']) ?></span>
                </div>
                <span class="text-sm <?= $req['passed'] ? 'text-green-600' : 'text-red-600' ?>"><?= e($req['current']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (!$allPassed): ?>
        <!-- Requirements Not Met -->
        <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <h3 class="font-medium text-amber-800">Requirements Not Met</h3>
                    <p class="text-sm text-amber-700 mt-1">Please fix the requirements marked in red before continuing with the installation.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center">
        <span class="text-sm text-slate-500">Step 1 of 6</span>
        <?php if ($allPassed): ?>
        <a href="<?= url('/install/database') ?>" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg font-medium transition">
            Continue
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <?php else: ?>
        <button disabled class="inline-flex items-center gap-2 bg-slate-300 text-slate-500 px-6 py-2.5 rounded-lg font-medium cursor-not-allowed">
            Continue
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
        <?php endif; ?>
    </div>
</div>
