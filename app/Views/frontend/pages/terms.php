<div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-8">Terms of Service</h1>

    <div class="prose prose-neutral dark:prose-invert max-w-none text-sm text-neutral-600 dark:text-neutral-400 space-y-6">
        <p class="text-neutral-500">Last updated: <?= date('F j, Y') ?></p>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">1. Acceptance of Terms</h2>
            <p>By accessing and using <?= e(config('app.name')) ?> ("the Service"), you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use the Service.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">2. Description of Service</h2>
            <p>The Service provides a platform for users to view, upload, and share images. We reserve the right to modify, suspend, or discontinue the Service at any time without notice.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">3. User Accounts</h2>
            <ul class="list-disc list-inside space-y-2 ml-4">
                <li>You must be at least 18 years old to create an account</li>
                <li>You are responsible for maintaining the security of your account</li>
                <li>You are responsible for all activities that occur under your account</li>
                <li>You must provide accurate and complete information when creating an account</li>
                <li>We reserve the right to suspend or terminate accounts that violate these terms</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">4. User Content</h2>
            <p>By uploading content to the Service, you:</p>
            <ul class="list-disc list-inside space-y-2 ml-4">
                <li>Retain ownership of your content</li>
                <li>Grant us a non-exclusive, worldwide license to use, display, and distribute your content</li>
                <li>Represent that you have the right to upload and share the content</li>
                <li>Agree not to upload content that infringes on intellectual property rights</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">5. Prohibited Content</h2>
            <p>You agree not to upload, post, or share content that:</p>
            <ul class="list-disc list-inside space-y-2 ml-4">
                <li>Violates any applicable laws or regulations</li>
                <li>Infringes on copyrights, trademarks, or other intellectual property rights</li>
                <li>Contains malware, viruses, or harmful code</li>
                <li>Is fraudulent, deceptive, or misleading</li>
                <li>Harasses, threatens, or promotes violence against others</li>
                <li>Contains personal information of others without consent</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">6. Intellectual Property</h2>
            <p>The Service and its original content (excluding user-uploaded content) are owned by <?= e(config('app.name')) ?> and protected by copyright, trademark, and other intellectual property laws.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">7. Disclaimer of Warranties</h2>
            <p>THE SERVICE IS PROVIDED "AS IS" WITHOUT WARRANTIES OF ANY KIND, EXPRESS OR IMPLIED. WE DO NOT GUARANTEE THAT THE SERVICE WILL BE UNINTERRUPTED, SECURE, OR ERROR-FREE.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">8. Limitation of Liability</h2>
            <p>IN NO EVENT SHALL <?= e(strtoupper(config('app.name'))) ?> BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, OR CONSEQUENTIAL DAMAGES ARISING OUT OF YOUR USE OF THE SERVICE.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">9. Changes to Terms</h2>
            <p>We reserve the right to modify these terms at any time. Continued use of the Service after changes constitutes acceptance of the new terms.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">10. Contact</h2>
            <p>If you have questions about these Terms, please <a href="<?= $view->url('/contact') ?>" class="text-primary-600 hover:underline">contact us</a>.</p>
        </section>
    </div>
</div>
