<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - Pixly</title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                    },
                },
            },
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }

        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }

        .step-dot {
            width: 2.5rem;
            height: 0.5rem;
            border-radius: 9999px;
            transition: all 0.3s;
        }

        .step-dot.completed {
            background-color: #0ea5e9;
        }

        .step-dot.current {
            background-color: #0284c7;
        }

        .step-dot.pending {
            background-color: #e5e7eb;
        }

        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #0284c7;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-sm py-4">
        <div class="max-w-3xl mx-auto px-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-xl font-bold text-slate-800">Pixly</span>
            </div>
            <span class="text-sm text-slate-500">Installation Wizard</span>
        </div>
    </header>

    <!-- Step Indicator -->
    <?php if (isset($step) && isset($totalSteps)): ?>
    <div class="bg-white border-b py-4">
        <div class="max-w-3xl mx-auto px-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-slate-600">Step <?= $step ?> of <?= $totalSteps ?></span>
                <span class="text-sm text-slate-500">
                    <?php
                    $stepNames = [
                        1 => 'Requirements',
                        2 => 'Database',
                        3 => 'Setup',
                        4 => 'Admin',
                        5 => 'Settings',
                        6 => 'Complete',
                    ];
                    echo $stepNames[$step] ?? '';
                    ?>
                </span>
            </div>
            <div class="step-indicator">
                <?php for ($i = 1; $i <= $totalSteps; $i++): ?>
                <div class="step-dot <?= $i < $step ? 'completed' : ($i === $step ? 'current' : 'pending') ?>"></div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Flash Messages -->
    <?php if ($view->hasFlash('success')): ?>
    <div class="max-w-3xl mx-auto px-4 mt-4">
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <?= e($view->flash('success')) ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($view->hasFlash('error')): ?>
    <div class="max-w-3xl mx-auto px-4 mt-4">
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <?= e($view->flash('error')) ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="flex-1 py-8">
        <div class="max-w-3xl mx-auto px-4">
            <?= $content ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t py-4 mt-auto">
        <div class="max-w-3xl mx-auto px-4 text-center text-sm text-slate-500">
            Pixly &copy; <?= date('Y') ?>
        </div>
    </footer>
</body>
</html>
