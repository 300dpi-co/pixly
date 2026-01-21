<div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-8">Disclaimer</h1>

    <div class="prose prose-neutral dark:prose-invert max-w-none text-sm text-neutral-600 dark:text-neutral-400 space-y-6">
        <p class="text-neutral-500">Last updated: <?= date('F j, Y') ?></p>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">General Disclaimer</h2>
            <p>The information and content provided on <?= e(config('app.name')) ?> is for general informational and entertainment purposes only. We make no representations or warranties of any kind, express or implied, about the completeness, accuracy, reliability, or suitability of the content.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">User-Generated Content</h2>
            <p><?= e(config('app.name')) ?> allows users to upload and share images. We do not:</p>
            <ul class="list-disc list-inside space-y-2 ml-4">
                <li>Pre-screen all user-uploaded content</li>
                <li>Guarantee the accuracy or legality of user content</li>
                <li>Endorse any user-generated content</li>
                <li>Accept responsibility for third-party content</li>
            </ul>
            <p class="mt-2">Users are solely responsible for the content they upload and share.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">No Professional Advice</h2>
            <p>Content on this website does not constitute professional advice of any kind. Always seek appropriate professional guidance for specific situations.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">External Links</h2>
            <p>Our website may contain links to external sites. We have no control over and assume no responsibility for the content, privacy policies, or practices of any third-party sites.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Limitation of Liability</h2>
            <p>To the fullest extent permitted by law, <?= e(config('app.name')) ?> shall not be liable for any:</p>
            <ul class="list-disc list-inside space-y-2 ml-4">
                <li>Direct, indirect, incidental, or consequential damages</li>
                <li>Loss of profits, data, or business opportunities</li>
                <li>Damages arising from use or inability to use the service</li>
                <li>Damages from unauthorized access to your data</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Copyright Notice</h2>
            <p>Images on this platform may be protected by copyright. Users must ensure they have appropriate rights before downloading or using any content. We respond to valid DMCA takedown requests.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Age Restriction</h2>
            <p>This website may contain content intended for mature audiences. By using this website, you confirm that you are at least 18 years of age or the age of majority in your jurisdiction.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">"As Is" Basis</h2>
            <p>THE SERVICE IS PROVIDED ON AN "AS IS" AND "AS AVAILABLE" BASIS WITHOUT ANY WARRANTIES, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Changes to This Disclaimer</h2>
            <p>We reserve the right to modify this disclaimer at any time. Your continued use of the website constitutes acceptance of any changes.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Contact</h2>
            <p>If you have questions about this disclaimer, please <a href="<?= $view->url('/contact') ?>" class="text-primary-600 hover:underline">contact us</a>.</p>
        </section>
    </div>
</div>
