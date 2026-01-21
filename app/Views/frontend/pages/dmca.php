<div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-8">DMCA / Copyright Policy</h1>

    <div class="prose prose-neutral dark:prose-invert max-w-none text-sm text-neutral-600 dark:text-neutral-400 space-y-6">
        <p class="text-neutral-500">Last updated: <?= date('F j, Y') ?></p>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Copyright Policy</h2>
            <p><?= e(config('app.name')) ?> respects the intellectual property rights of others and expects users to do the same. We will respond to notices of alleged copyright infringement that comply with the Digital Millennium Copyright Act (DMCA).</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Reporting Copyright Infringement</h2>
            <p>If you believe your copyrighted work has been copied in a way that constitutes infringement, please provide our DMCA Agent with the following information:</p>
            <ol class="list-decimal list-inside space-y-2 ml-4">
                <li>A physical or electronic signature of the copyright owner or authorized agent</li>
                <li>Identification of the copyrighted work claimed to have been infringed</li>
                <li>Identification of the material that is claimed to be infringing, including the URL</li>
                <li>Your contact information (address, phone number, email)</li>
                <li>A statement that you have a good faith belief that the use is not authorized</li>
                <li>A statement, under penalty of perjury, that the information is accurate and that you are authorized to act on behalf of the copyright owner</li>
            </ol>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">How to Submit a DMCA Notice</h2>
            <p>You can submit a DMCA takedown notice by:</p>
            <ul class="list-disc list-inside space-y-2 ml-4">
                <li>Using the "Report" button on any image page</li>
                <li>Emailing us at: <strong>dmca@<?= $_SERVER['HTTP_HOST'] ?? 'example.com' ?></strong></li>
                <li>Using our <a href="<?= $view->url('/contact') ?>" class="text-primary-600 hover:underline">contact form</a></li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Counter-Notification</h2>
            <p>If you believe your content was removed by mistake or misidentification, you may submit a counter-notification containing:</p>
            <ol class="list-decimal list-inside space-y-2 ml-4">
                <li>Your physical or electronic signature</li>
                <li>Identification of the removed material and its former location</li>
                <li>A statement under penalty of perjury that you have a good faith belief the material was removed by mistake</li>
                <li>Your name, address, phone number, and email</li>
                <li>A statement consenting to jurisdiction of the federal court in your district</li>
            </ol>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Repeat Infringers</h2>
            <p>We maintain a policy of terminating accounts of users who are repeat infringers of copyright. If you repeatedly upload infringing content, your account may be permanently suspended.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Response Time</h2>
            <p>We aim to process valid DMCA notices within 24-48 business hours. Upon receipt of a valid notice, we will:</p>
            <ul class="list-disc list-inside space-y-2 ml-4">
                <li>Remove or disable access to the allegedly infringing content</li>
                <li>Notify the user who uploaded the content</li>
                <li>Provide information about counter-notification procedures</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">False Claims Warning</h2>
            <p class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200 p-4 rounded-lg">
                <strong>Warning:</strong> Under Section 512(f) of the DMCA, any person who knowingly materially misrepresents that material is infringing may be subject to liability for damages, including costs and attorney's fees.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Contact Information</h2>
            <p>DMCA Agent:<br>
            <?= e(config('app.name')) ?><br>
            Email: dmca@<?= $_SERVER['HTTP_HOST'] ?? 'example.com' ?></p>
        </section>
    </div>
</div>
