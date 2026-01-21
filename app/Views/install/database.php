<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-6 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Database Configuration</h1>
                <p class="text-sm text-slate-500">Enter your MySQL database credentials</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="<?= url('/install/database') ?>" method="POST" class="p-6">
        <?= csrf_field() ?>

        <?php if ($view->error('connection')): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span><?= e($view->error('connection')) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($view->error('config')): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
            <?= e($view->error('config')) ?>
        </div>
        <?php endif; ?>

        <div class="space-y-5">
            <!-- Host & Port -->
            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label for="host" class="block text-sm font-medium text-slate-700 mb-1">Database Host</label>
                    <input type="text" id="host" name="host"
                           value="<?= e(old('host', $dbConfig['host'] ?? 'localhost')) ?>"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 <?= $view->error('host') ? 'border-red-300 bg-red-50' : '' ?>"
                           placeholder="localhost">
                    <?php if ($view->error('host')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($view->error('host')) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="port" class="block text-sm font-medium text-slate-700 mb-1">Port</label>
                    <input type="number" id="port" name="port"
                           value="<?= e(old('port', $dbConfig['port'] ?? '3306')) ?>"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="3306">
                </div>
            </div>

            <!-- Database Name -->
            <div>
                <label for="database" class="block text-sm font-medium text-slate-700 mb-1">Database Name</label>
                <input type="text" id="database" name="database"
                       value="<?= e(old('database', $dbConfig['database'] ?? '')) ?>"
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 <?= $view->error('database') ? 'border-red-300 bg-red-50' : '' ?>"
                       placeholder="fwp_gallery">
                <?php if ($view->error('database')): ?>
                <p class="mt-1 text-sm text-red-600"><?= e($view->error('database')) ?></p>
                <?php endif; ?>
            </div>

            <!-- Username -->
            <div>
                <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                <input type="text" id="username" name="username"
                       value="<?= e(old('username', $dbConfig['username'] ?? '')) ?>"
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 <?= $view->error('username') ? 'border-red-300 bg-red-50' : '' ?>"
                       placeholder="root">
                <?php if ($view->error('username')): ?>
                <p class="mt-1 text-sm text-red-600"><?= e($view->error('username')) ?></p>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" id="password" name="password"
                       value="<?= e(old('password', '')) ?>"
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Enter password (leave empty if none)">
            </div>

            <!-- Create Database Option -->
            <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-lg">
                <input type="checkbox" id="create_database" name="create_database" value="1"
                       class="w-4 h-4 text-primary-600 border-slate-300 rounded focus:ring-primary-500">
                <label for="create_database" class="text-sm text-slate-700">
                    Create database if it doesn't exist
                </label>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-slate-200 flex justify-between items-center">
            <a href="<?= url('/install') ?>" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-800 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
            <button type="submit" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg font-medium transition">
                Test Connection & Continue
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </form>
</div>
