<?php
/**
 * Pexels-Style Premium Page
 * Clean, minimal, flat design
 */
?>

<!-- Premium Header -->
<div class="bg-white border-b border-neutral-200">
    <div class="max-w-4xl mx-auto px-4 py-12 text-center">
        <?php if ($isPremium): ?>
        <span class="inline-flex items-center gap-2 px-3 py-1 bg-teal-50 text-teal-700 rounded-full text-sm font-medium mb-4">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Premium Member
        </span>
        <?php endif; ?>

        <h1 class="text-3xl font-semibold text-neutral-900 mb-3">
            <?= $isPremium ? 'You\'re Premium' : 'Go Premium' ?>
        </h1>
        <p class="text-neutral-500 max-w-lg mx-auto">
            <?= $isPremium
                ? 'Enjoy your ad-free experience and exclusive benefits.'
                : 'Ad-free browsing, unlimited downloads, and priority support.'
            ?>
        </p>
    </div>
</div>

<div class="bg-neutral-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 py-12">

        <?php if ($isPremium && $subscription): ?>
        <!-- Current Subscription -->
        <div class="bg-white rounded-lg border border-neutral-200 p-6 mb-8">
            <h2 class="text-lg font-medium text-neutral-900 mb-4">Your Subscription</h2>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-neutral-500">Plan</span>
                    <p class="font-medium text-neutral-900"><?= ucfirst($subscription['plan_type']) ?></p>
                </div>
                <div>
                    <span class="text-neutral-500">Status</span>
                    <p class="font-medium text-teal-600">Active</p>
                </div>
                <div>
                    <span class="text-neutral-500">Started</span>
                    <p class="font-medium text-neutral-900"><?= date('M j, Y', strtotime($subscription['starts_at'])) ?></p>
                </div>
                <div>
                    <span class="text-neutral-500">Renews</span>
                    <p class="font-medium text-neutral-900"><?= date('M j, Y', strtotime($subscription['expires_at'])) ?></p>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-neutral-100">
                <form action="<?= $view->url('/premium/cancel') ?>" method="POST" onsubmit="return confirm('Cancel subscription? You\'ll keep access until <?= date('M j, Y', strtotime($subscription['expires_at'])) ?>.');">
                    <?= csrf_field() ?>
                    <button type="submit" class="text-sm text-neutral-500 hover:text-red-600 transition-colors">
                        Cancel Subscription
                    </button>
                </form>
            </div>
        </div>

        <?php else: ?>
        <!-- Pricing Cards -->
        <div class="grid md:grid-cols-2 gap-6 mb-12">
            <!-- Monthly -->
            <div class="bg-white rounded-lg border border-neutral-200 p-6">
                <h3 class="text-neutral-900 font-medium mb-1">Monthly</h3>
                <div class="mb-4">
                    <span class="text-3xl font-semibold text-neutral-900">₹<?= $monthlyPrice ?></span>
                    <span class="text-neutral-500 text-sm">/month</span>
                </div>
                <ul class="space-y-2 mb-6 text-sm text-neutral-600">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Ad-free browsing
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Unlimited downloads
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Cancel anytime
                    </li>
                </ul>
                <form action="<?= $view->url('/premium/checkout') ?>" method="GET">
                    <input type="hidden" name="plan" value="monthly">
                    <button type="submit" class="w-full py-2.5 border border-neutral-300 text-neutral-700 font-medium rounded-lg hover:bg-neutral-50 transition-colors">
                        Choose Monthly
                    </button>
                </form>
            </div>

            <!-- Yearly -->
            <div class="bg-white rounded-lg border-2 border-teal-500 p-6 relative">
                <span class="absolute -top-3 left-4 px-2 py-0.5 bg-teal-500 text-white text-xs font-medium rounded">Best Value</span>
                <h3 class="text-neutral-900 font-medium mb-1">Yearly</h3>
                <div class="mb-1">
                    <span class="text-3xl font-semibold text-neutral-900">₹<?= $yearlyPrice ?></span>
                    <span class="text-neutral-500 text-sm">/year</span>
                </div>
                <p class="text-xs text-teal-600 mb-4">₹<?= round($yearlyPrice / 12, 2) ?>/month</p>
                <ul class="space-y-2 mb-6 text-sm text-neutral-600">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Ad-free browsing
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Unlimited downloads
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Priority support
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Early access
                    </li>
                </ul>
                <form action="<?= $view->url('/premium/checkout') ?>" method="GET">
                    <input type="hidden" name="plan" value="yearly">
                    <button type="submit" class="w-full py-2.5 bg-teal-500 hover:bg-teal-600 text-white font-medium rounded-lg transition-colors">
                        Choose Yearly
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Benefits -->
        <div class="mb-12">
            <h2 class="text-lg font-medium text-neutral-900 mb-6 text-center">What You Get</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg border border-neutral-200 p-4 text-center">
                    <div class="w-10 h-10 bg-neutral-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-neutral-900">Ad-Free</h3>
                    <p class="text-xs text-neutral-500 mt-1">No interruptions</p>
                </div>

                <div class="bg-white rounded-lg border border-neutral-200 p-4 text-center">
                    <div class="w-10 h-10 bg-neutral-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-neutral-900">Downloads</h3>
                    <p class="text-xs text-neutral-500 mt-1">Unlimited access</p>
                </div>

                <div class="bg-white rounded-lg border border-neutral-200 p-4 text-center">
                    <div class="w-10 h-10 bg-neutral-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-neutral-900">Early Access</h3>
                    <p class="text-xs text-neutral-500 mt-1">New features first</p>
                </div>

                <div class="bg-white rounded-lg border border-neutral-200 p-4 text-center">
                    <div class="w-10 h-10 bg-neutral-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-neutral-900">Support</h3>
                    <p class="text-xs text-neutral-500 mt-1">Priority help</p>
                </div>
            </div>
        </div>

        <!-- FAQ -->
        <div class="bg-white rounded-lg border border-neutral-200 p-6">
            <h2 class="text-lg font-medium text-neutral-900 mb-4">FAQ</h2>
            <div class="space-y-4 text-sm">
                <div>
                    <h3 class="font-medium text-neutral-900">Can I cancel anytime?</h3>
                    <p class="text-neutral-500 mt-1">Yes. You'll keep access until your billing period ends.</p>
                </div>
                <div>
                    <h3 class="font-medium text-neutral-900">Payment methods?</h3>
                    <p class="text-neutral-500 mt-1">UPI, cards, net banking, and wallets.</p>
                </div>
                <div>
                    <h3 class="font-medium text-neutral-900">Refund policy?</h3>
                    <p class="text-neutral-500 mt-1">7-day money-back guarantee.</p>
                </div>
            </div>
        </div>

    </div>
</div>
