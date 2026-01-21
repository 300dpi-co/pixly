<?php if ($success): ?>
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Success Header -->
    <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-12 text-white text-center">
        <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold mb-3">Installation Complete!</h1>
        <p class="text-green-100 text-lg">Pixly has been installed successfully.</p>
    </div>

    <!-- Content -->
    <div class="p-6">
        <!-- What's Next -->
        <h2 class="text-lg font-semibold text-slate-800 mb-4">What's Next?</h2>

        <div class="space-y-4">
            <div class="flex items-start gap-4 p-4 bg-slate-50 rounded-lg">
                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-primary-600 font-bold">1</span>
                </div>
                <div>
                    <h3 class="font-medium text-slate-800">Log in to Admin Panel</h3>
                    <p class="text-sm text-slate-600 mt-1">Use your admin credentials to access the dashboard and configure your site.</p>
                </div>
            </div>

            <div class="flex items-start gap-4 p-4 bg-slate-50 rounded-lg">
                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-primary-600 font-bold">2</span>
                </div>
                <div>
                    <h3 class="font-medium text-slate-800">Upload Images</h3>
                    <p class="text-sm text-slate-600 mt-1">Start adding images to your gallery through the admin panel.</p>
                </div>
            </div>

            <div class="flex items-start gap-4 p-4 bg-slate-50 rounded-lg">
                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-primary-600 font-bold">3</span>
                </div>
                <div>
                    <h3 class="font-medium text-slate-800">Customize Settings</h3>
                    <p class="text-sm text-slate-600 mt-1">Configure theme colors, logo, social links, and other settings.</p>
                </div>
            </div>
        </div>

        <!-- Security Reminder -->
        <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <h3 class="font-medium text-amber-800">Security Recommendation</h3>
                    <p class="text-sm text-amber-700 mt-1">For security reasons, you should remove or restrict access to the <code class="bg-amber-100 px-1 rounded">/install</code> routes in production. The installer is now disabled and cannot be accessed again.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row gap-3 justify-center">
        <a href="<?= url('/login') ?>" class="inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg font-medium transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            Go to Login
        </a>
        <a href="<?= url('/') ?>" class="inline-flex items-center justify-center gap-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 px-6 py-2.5 rounded-lg font-medium transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Visit Homepage
        </a>
    </div>
</div>
<?php else: ?>
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Error Header -->
    <div class="bg-gradient-to-r from-red-500 to-rose-600 px-6 py-12 text-white text-center">
        <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold mb-3">Installation Incomplete</h1>
        <p class="text-red-100 text-lg">There was an issue completing the installation.</p>
    </div>

    <!-- Content -->
    <div class="p-6">
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
            <p><?= e($error ?? 'Unknown error occurred') ?></p>
        </div>

        <div class="mt-6">
            <h3 class="font-medium text-slate-800 mb-2">What you can try:</h3>
            <ul class="list-disc list-inside text-slate-600 space-y-1">
                <li>Check that the <code class="bg-slate-100 px-1 rounded">storage/</code> directory is writable</li>
                <li>Ensure PHP has permission to write files</li>
                <li>Check for any error messages in your server logs</li>
            </ul>
        </div>
    </div>

    <!-- Actions -->
    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-center">
        <a href="<?= url('/install/settings') ?>" class="inline-flex items-center gap-2 bg-slate-600 hover:bg-slate-700 text-white px-6 py-2.5 rounded-lg font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Go Back and Try Again
        </a>
    </div>
</div>
<?php endif; ?>
