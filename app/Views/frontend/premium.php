<!-- Premium Hero -->
<section class="bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700 text-white py-20">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <?php if ($isPremium): ?>
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-500 rounded-full text-sm font-medium mb-6">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            You're a Premium Member!
        </div>
        <?php else: ?>
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 rounded-full text-sm font-medium mb-6">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            Premium Membership
        </div>
        <?php endif; ?>

        <h1 class="text-4xl md:text-5xl font-bold mb-6">
            <?= $isPremium ? 'Thank You for Being Premium!' : 'Upgrade to Premium' ?>
        </h1>
        <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
            <?= $isPremium
                ? 'Enjoy your ad-free experience and exclusive benefits.'
                : 'Enjoy an ad-free experience, unlimited downloads, and exclusive features for just ₹99/year.'
            ?>
        </p>
    </div>
</section>

<?php if ($isPremium && $subscription): ?>
<!-- Current Subscription Info -->
<section class="py-12 bg-white dark:bg-neutral-900">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-8 border border-blue-100 dark:border-blue-800">
            <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">Your Subscription</h2>

            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-neutral-600 dark:text-neutral-400">Plan</span>
                    <span class="font-medium text-neutral-900 dark:text-white"><?= ucfirst($subscription['plan_type']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-600 dark:text-neutral-400">Status</span>
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        Active
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-600 dark:text-neutral-400">Started</span>
                    <span class="font-medium text-neutral-900 dark:text-white"><?= date('M j, Y', strtotime($subscription['starts_at'])) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-600 dark:text-neutral-400">Renews</span>
                    <span class="font-medium text-neutral-900 dark:text-white"><?= date('M j, Y', strtotime($subscription['expires_at'])) ?></span>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-blue-200 dark:border-blue-700">
                <form action="<?= $view->url('/premium/cancel') ?>" method="POST" onsubmit="return confirm('Are you sure you want to cancel? You will keep access until <?= date('M j, Y', strtotime($subscription['expires_at'])) ?>.');">
                    <?= csrf_field() ?>
                    <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">
                        Cancel Subscription
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php else: ?>
<!-- Pricing Cards -->
<section class="py-16 bg-neutral-50 dark:bg-neutral-800">
    <div class="max-w-5xl mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-8 max-w-3xl mx-auto">
            <!-- Monthly Plan -->
            <div class="bg-white dark:bg-neutral-900 rounded-2xl p-8 border border-neutral-200 dark:border-neutral-700 hover:shadow-lg transition">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Monthly</h3>
                <div class="mb-6">
                    <span class="text-4xl font-bold text-neutral-900 dark:text-white">₹<?= $monthlyPrice ?></span>
                    <span class="text-neutral-500">/month</span>
                </div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Ad-free browsing
                    </li>
                    <li class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Unlimited downloads
                    </li>
                    <li class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Cancel anytime
                    </li>
                </ul>
                <form action="<?= $view->url('/premium/checkout') ?>" method="GET">
                    <input type="hidden" name="plan" value="monthly">
                    <button type="submit" class="w-full py-3 px-6 border-2 border-blue-600 text-blue-600 font-semibold rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                        Choose Monthly
                    </button>
                </form>
            </div>

            <!-- Yearly Plan - Featured -->
            <div class="bg-white dark:bg-neutral-900 rounded-2xl p-8 border-2 border-blue-600 relative hover:shadow-xl transition">
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-blue-600 text-white text-sm font-semibold rounded-full">
                    Best Value - Save 45%
                </div>
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Yearly</h3>
                <div class="mb-6">
                    <span class="text-4xl font-bold text-neutral-900 dark:text-white">₹<?= $yearlyPrice ?></span>
                    <span class="text-neutral-500">/year</span>
                    <p class="text-sm text-green-600 mt-1">Just ₹<?= round($yearlyPrice / 12, 2) ?>/month</p>
                </div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Ad-free browsing
                    </li>
                    <li class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Unlimited downloads
                    </li>
                    <li class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Priority support
                    </li>
                    <li class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Early access to new features
                    </li>
                </ul>
                <form action="<?= $view->url('/premium/checkout') ?>" method="GET">
                    <input type="hidden" name="plan" value="yearly">
                    <button type="submit" class="w-full py-3 px-6 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                        Choose Yearly
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Features Section -->
<section class="py-16 bg-white dark:bg-neutral-900">
    <div class="max-w-5xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center text-neutral-900 dark:text-white mb-12">Premium Benefits</h2>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-neutral-900 dark:text-white mb-2">Ad-Free</h3>
                <p class="text-neutral-600 dark:text-neutral-400 text-sm">Browse without any advertisements or interruptions</p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-neutral-900 dark:text-white mb-2">Unlimited Downloads</h3>
                <p class="text-neutral-600 dark:text-neutral-400 text-sm">Download as many images as you want</p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-neutral-900 dark:text-white mb-2">Early Access</h3>
                <p class="text-neutral-600 dark:text-neutral-400 text-sm">Get first look at new features and content</p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-neutral-900 dark:text-white mb-2">Priority Support</h3>
                <p class="text-neutral-600 dark:text-neutral-400 text-sm">Get faster responses to your queries</p>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-16 bg-neutral-50 dark:bg-neutral-800">
    <div class="max-w-3xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center text-neutral-900 dark:text-white mb-12">Frequently Asked Questions</h2>

        <div class="space-y-4">
            <details class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700">
                <summary class="px-6 py-4 cursor-pointer font-medium text-neutral-900 dark:text-white">
                    Can I cancel anytime?
                </summary>
                <div class="px-6 pb-4 text-neutral-600 dark:text-neutral-400">
                    Yes! You can cancel your subscription at any time. You'll continue to have premium access until the end of your billing period.
                </div>
            </details>

            <details class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700">
                <summary class="px-6 py-4 cursor-pointer font-medium text-neutral-900 dark:text-white">
                    What payment methods do you accept?
                </summary>
                <div class="px-6 pb-4 text-neutral-600 dark:text-neutral-400">
                    We accept UPI, credit/debit cards, net banking, and popular wallets like Paytm and PhonePe.
                </div>
            </details>

            <details class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700">
                <summary class="px-6 py-4 cursor-pointer font-medium text-neutral-900 dark:text-white">
                    Is there a free trial?
                </summary>
                <div class="px-6 pb-4 text-neutral-600 dark:text-neutral-400">
                    We offer free registration with limited features. Try it out and upgrade to premium when you're ready!
                </div>
            </details>

            <details class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700">
                <summary class="px-6 py-4 cursor-pointer font-medium text-neutral-900 dark:text-white">
                    Can I get a refund?
                </summary>
                <div class="px-6 pb-4 text-neutral-600 dark:text-neutral-400">
                    We offer a 7-day money-back guarantee. If you're not satisfied within the first week, contact us for a full refund.
                </div>
            </details>
        </div>
    </div>
</section>
