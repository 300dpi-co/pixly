<div class="max-w-xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-2">Contact Us</h1>
    <p class="text-neutral-500 dark:text-neutral-400 mb-8">Have a question or feedback? We'd love to hear from you.</p>

    <form action="<?= $view->url('/contact') ?>" method="POST" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div>
            <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Name *</label>
            <input type="text" name="name" id="name" required
                   class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white rounded-lg focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                   value="<?= e($_POST['name'] ?? '') ?>">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Email *</label>
            <input type="email" name="email" id="email" required
                   class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white rounded-lg focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                   value="<?= e($_POST['email'] ?? '') ?>">
        </div>

        <div>
            <label for="subject" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Subject *</label>
            <select name="subject" id="subject" required
                    class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white rounded-lg focus:ring-1 focus:ring-primary-500">
                <option value="">Select a subject...</option>
                <option value="general">General Inquiry</option>
                <option value="support">Technical Support</option>
                <option value="copyright">Copyright / DMCA</option>
                <option value="feedback">Feedback / Suggestion</option>
                <option value="business">Business Inquiry</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div>
            <label for="message" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Message *</label>
            <textarea name="message" id="message" rows="5" required
                      class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white rounded-lg focus:ring-1 focus:ring-primary-500 focus:border-primary-500 resize-none"
                      placeholder="How can we help you?"><?= e($_POST['message'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="w-full px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">
            Send Message
        </button>
    </form>

    <div class="mt-12 pt-8 border-t border-neutral-200 dark:border-neutral-700">
        <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mb-4">Other Ways to Reach Us</h2>

        <div class="space-y-3 text-sm text-neutral-600 dark:text-neutral-400">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span>support@<?= $_SERVER['HTTP_HOST'] ?? 'example.com' ?></span>
            </div>

            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Response time: 24-48 hours</span>
            </div>
        </div>

        <div class="mt-6 p-4 bg-neutral-50 dark:bg-neutral-800 rounded-lg">
            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                <strong>For DMCA/Copyright issues:</strong> Please visit our <a href="<?= $view->url('/dmca') ?>" class="text-primary-600 hover:underline">DMCA page</a> for faster processing.
            </p>
        </div>
    </div>
</div>
