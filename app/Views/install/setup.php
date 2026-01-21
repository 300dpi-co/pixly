<!-- Hidden CSRF token for AJAX request -->
<?= csrf_field() ?>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-6 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Database Setup</h1>
                <p class="text-sm text-slate-500">Creating tables and running migrations</p>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="p-6">
        <!-- Initial State -->
        <div id="setup-initial">
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-slate-800 mb-2">Ready to Set Up Database</h2>
                <p class="text-slate-600 mb-6">Click the button below to create all necessary tables and run migrations.</p>
                <button id="run-setup" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Run Database Setup
                </button>
            </div>
        </div>

        <!-- Running State -->
        <div id="setup-running" class="hidden">
            <div class="text-center py-8">
                <div class="loading-spinner mx-auto mb-4"></div>
                <h2 class="text-lg font-semibold text-slate-800 mb-2">Setting Up Database...</h2>
                <p class="text-slate-600">Please wait while we create tables and run migrations.</p>
            </div>

            <!-- Progress Log -->
            <div id="progress-log" class="mt-6 bg-slate-900 rounded-lg p-4 font-mono text-sm text-slate-300 max-h-64 overflow-y-auto">
                <div class="text-slate-500">Initializing...</div>
            </div>
        </div>

        <!-- Success State -->
        <div id="setup-success" class="hidden">
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-slate-800 mb-2">Database Setup Complete!</h2>
                <p class="text-slate-600 mb-6">All tables have been created successfully.</p>
            </div>

            <!-- Results -->
            <div id="setup-results" class="mt-4 space-y-2"></div>
        </div>

        <!-- Error State -->
        <div id="setup-error" class="hidden">
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-slate-800 mb-2">Setup Failed</h2>
                <p id="error-message" class="text-red-600 mb-6"></p>
                <button onclick="location.reload()" class="inline-flex items-center gap-2 bg-slate-600 hover:bg-slate-700 text-white px-6 py-2.5 rounded-lg font-medium transition">
                    Try Again
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center">
        <a href="<?= url('/install/database') ?>" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-800 font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back
        </a>
        <a href="<?= url('/install/admin') ?>" id="continue-btn" class="hidden inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg font-medium transition">
            Continue
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</div>

<script>
document.getElementById('run-setup').addEventListener('click', async function() {
    const initialEl = document.getElementById('setup-initial');
    const runningEl = document.getElementById('setup-running');
    const successEl = document.getElementById('setup-success');
    const errorEl = document.getElementById('setup-error');
    const progressLog = document.getElementById('progress-log');
    const resultsEl = document.getElementById('setup-results');
    const continueBtn = document.getElementById('continue-btn');

    // Show running state
    initialEl.classList.add('hidden');
    runningEl.classList.remove('hidden');

    // Add log entry
    function addLog(message, type = 'info') {
        const colors = {
            info: 'text-slate-300',
            success: 'text-green-400',
            error: 'text-red-400',
        };
        const div = document.createElement('div');
        div.className = colors[type] || colors.info;
        div.textContent = message;
        progressLog.appendChild(div);
        progressLog.scrollTop = progressLog.scrollHeight;
    }

    addLog('Starting database setup...');

    try {
        // Get CSRF token
        const csrfMeta = document.querySelector('input[name="_token"]');
        const csrfToken = csrfMeta ? csrfMeta.value : '';

        addLog('Running schema and migrations...');

        const response = await fetch('<?= url('/install/setup') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: '_token=' + encodeURIComponent(csrfToken),
        });

        const data = await response.json();

        if (data.success) {
            addLog('Setup completed successfully!', 'success');

            // Show results
            if (data.results) {
                data.results.forEach(result => {
                    addLog('  - ' + result.file + ': ' + result.status, 'success');

                    const div = document.createElement('div');
                    div.className = 'flex items-center gap-2 p-3 bg-green-50 border border-green-100 rounded-lg';
                    div.innerHTML = `
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-green-800">${result.file}</span>
                    `;
                    resultsEl.appendChild(div);
                });
            }

            // Show success state
            runningEl.classList.add('hidden');
            successEl.classList.remove('hidden');
            continueBtn.classList.remove('hidden');
        } else {
            throw new Error(data.error || 'Unknown error occurred');
        }
    } catch (error) {
        addLog('Error: ' + error.message, 'error');

        // Show error state
        runningEl.classList.add('hidden');
        errorEl.classList.remove('hidden');
        document.getElementById('error-message').textContent = error.message;
    }
});
</script>
