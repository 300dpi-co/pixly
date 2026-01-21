<div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-8">Cookie Policy</h1>

    <div class="prose prose-neutral dark:prose-invert max-w-none text-sm text-neutral-600 dark:text-neutral-400 space-y-6">
        <p class="text-neutral-500">Last updated: <?= date('F j, Y') ?></p>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">What Are Cookies?</h2>
            <p>Cookies are small text files stored on your device when you visit a website. They help the website remember your preferences and improve your experience.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">How We Use Cookies</h2>
            <p>We use cookies for the following purposes:</p>

            <h3 class="font-medium text-neutral-700 dark:text-neutral-300 mt-4 mb-2">Essential Cookies</h3>
            <p>Required for the website to function properly:</p>
            <ul class="list-disc list-inside space-y-1 ml-4">
                <li>Session management and authentication</li>
                <li>Security features (CSRF protection)</li>
                <li>Load balancing</li>
            </ul>

            <h3 class="font-medium text-neutral-700 dark:text-neutral-300 mt-4 mb-2">Preference Cookies</h3>
            <p>Remember your settings and preferences:</p>
            <ul class="list-disc list-inside space-y-1 ml-4">
                <li>Theme preference (light/dark mode)</li>
                <li>Language settings</li>
                <li>Display preferences</li>
            </ul>

            <h3 class="font-medium text-neutral-700 dark:text-neutral-300 mt-4 mb-2">Analytics Cookies</h3>
            <p>Help us understand how visitors use our site:</p>
            <ul class="list-disc list-inside space-y-1 ml-4">
                <li>Pages visited and time spent</li>
                <li>Traffic sources</li>
                <li>Error tracking</li>
            </ul>

            <h3 class="font-medium text-neutral-700 dark:text-neutral-300 mt-4 mb-2">Advertising Cookies</h3>
            <p>Used to deliver relevant advertisements:</p>
            <ul class="list-disc list-inside space-y-1 ml-4">
                <li>Track ad performance</li>
                <li>Limit ad frequency</li>
                <li>Personalize ad content</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Cookies We Use</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full border border-neutral-200 dark:border-neutral-700 mt-4">
                    <thead class="bg-neutral-50 dark:bg-neutral-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400">Cookie</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400">Purpose</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400">Duration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        <tr>
                            <td class="px-4 py-2">PHPSESSID</td>
                            <td class="px-4 py-2">Session management</td>
                            <td class="px-4 py-2">Session</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2">theme</td>
                            <td class="px-4 py-2">Dark/light mode preference</td>
                            <td class="px-4 py-2">1 year</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2">age_verified</td>
                            <td class="px-4 py-2">Age verification status</td>
                            <td class="px-4 py-2">7 days</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2">_ga, _gid</td>
                            <td class="px-4 py-2">Google Analytics</td>
                            <td class="px-4 py-2">2 years</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Third-Party Cookies</h2>
            <p>Some cookies are set by third-party services we use:</p>
            <ul class="list-disc list-inside space-y-1 ml-4">
                <li>Google Analytics - website analytics</li>
                <li>Advertising partners - ad delivery</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Managing Cookies</h2>
            <p>You can control cookies through your browser settings:</p>
            <ul class="list-disc list-inside space-y-1 ml-4">
                <li><strong>Chrome:</strong> Settings → Privacy and Security → Cookies</li>
                <li><strong>Firefox:</strong> Options → Privacy & Security → Cookies</li>
                <li><strong>Safari:</strong> Preferences → Privacy → Cookies</li>
                <li><strong>Edge:</strong> Settings → Cookies and Site Permissions</li>
            </ul>
            <p class="mt-2">Note: Disabling cookies may affect website functionality.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Updates to This Policy</h2>
            <p>We may update this Cookie Policy from time to time. Changes will be posted on this page with an updated revision date.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Contact Us</h2>
            <p>If you have questions about our use of cookies, please <a href="<?= $view->url('/contact') ?>" class="text-primary-600 hover:underline">contact us</a>.</p>
        </section>
    </div>
</div>
