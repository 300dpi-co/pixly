<!-- Checkout Header -->
<section class="bg-gradient-to-br from-blue-600 to-indigo-600 text-white py-12">
    <div class="max-w-2xl mx-auto px-4 text-center">
        <h1 class="text-3xl font-bold mb-2">Complete Your Purchase</h1>
        <p class="text-blue-100">You're one step away from an ad-free experience!</p>
    </div>
</section>

<section class="py-12 bg-neutral-50 dark:bg-neutral-800">
    <div class="max-w-lg mx-auto px-4">
        <!-- Order Summary -->
        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Order Summary</h2>

            <div class="flex justify-between items-center py-3 border-b border-neutral-100 dark:border-neutral-800">
                <div>
                    <p class="font-medium text-neutral-900 dark:text-white">Premium <?= ucfirst($plan) ?> Plan</p>
                    <p class="text-sm text-neutral-500"><?= $plan === 'yearly' ? '12 months of premium access' : '1 month of premium access' ?></p>
                </div>
                <span class="font-semibold text-neutral-900 dark:text-white"><?= $currency === 'INR' ? '₹' : '$' ?><?= $price ?></span>
            </div>

            <div class="flex justify-between items-center py-3">
                <span class="font-semibold text-neutral-900 dark:text-white">Total</span>
                <span class="text-2xl font-bold text-blue-600"><?= $currency === 'INR' ? '₹' : '$' ?><?= $price ?></span>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-6">Payment Method</h2>

            <form id="payment-form" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="plan" value="<?= e($plan) ?>">

                <!-- Payment Method Selection -->
                <div class="space-y-3">
                    <label class="flex items-center p-4 border border-neutral-200 dark:border-neutral-700 rounded-lg cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="payment_method" value="upi" checked class="text-blue-600">
                        <span class="ml-3 flex items-center gap-2">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                            </svg>
                            <span class="font-medium text-neutral-900 dark:text-white">UPI</span>
                            <span class="text-xs text-neutral-500">GPay, PhonePe, Paytm</span>
                        </span>
                    </label>

                    <label class="flex items-center p-4 border border-neutral-200 dark:border-neutral-700 rounded-lg cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="payment_method" value="card" class="text-blue-600">
                        <span class="ml-3 flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <span class="font-medium text-neutral-900 dark:text-white">Credit/Debit Card</span>
                        </span>
                    </label>

                    <label class="flex items-center p-4 border border-neutral-200 dark:border-neutral-700 rounded-lg cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="payment_method" value="netbanking" class="text-blue-600">
                        <span class="ml-3 flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="font-medium text-neutral-900 dark:text-white">Net Banking</span>
                        </span>
                    </label>
                </div>

                <!-- UPI ID Input (shown when UPI selected) -->
                <div id="upi-input" class="mt-4">
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">UPI ID</label>
                    <input type="text" name="upi_id" placeholder="yourname@upi"
                           class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Submit Button -->
                <button type="submit" id="pay-btn"
                        class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Pay <?= $currency === 'INR' ? '₹' : '$' ?><?= $price ?>
                </button>
            </form>

            <p class="text-xs text-neutral-500 text-center mt-4">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Secure payment. Your data is encrypted.
            </p>
        </div>

        <!-- Back Link -->
        <p class="text-center mt-6">
            <a href="<?= $view->url('/premium') ?>" class="text-blue-600 hover:text-blue-700">
                &larr; Back to plans
            </a>
        </p>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('payment-form');
    const upiInput = document.getElementById('upi-input');
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');

    // Toggle UPI input visibility
    paymentMethods.forEach(radio => {
        radio.addEventListener('change', function() {
            upiInput.style.display = this.value === 'upi' ? 'block' : 'none';
        });
    });

    // Handle form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const btn = document.getElementById('pay-btn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Processing...';
        btn.disabled = true;

        try {
            const formData = new FormData(form);
            const response = await fetch('<?= $view->url('/premium/process-payment') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': formData.get('_token')
                },
                body: JSON.stringify(Object.fromEntries(formData))
            });

            const data = await response.json();

            if (data.success) {
                alert('Payment successful! Activating your premium membership...');
                window.location.href = data.redirect || '/premium';
            } else {
                alert(data.error || 'Payment failed. Please try again.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        } catch (error) {
            alert('An error occurred. Please try again.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
});
</script>
