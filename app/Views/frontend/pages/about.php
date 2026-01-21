<div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-8">About Us</h1>

    <div class="prose prose-neutral dark:prose-invert max-w-none text-neutral-600 dark:text-neutral-400 space-y-6">
        <p class="text-lg">Welcome to <?= e(config('app.name')) ?>, your destination for discovering and sharing stunning visual content.</p>

        <section>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Our Mission</h2>
            <p>We believe in the power of visual storytelling. Our platform brings together creators and enthusiasts from around the world, providing a space to discover, share, and appreciate beautiful imagery.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">What We Offer</h2>
            <ul class="list-disc list-inside space-y-2 ml-4">
                <li><strong>Curated Content:</strong> A constantly updated collection of high-quality images</li>
                <li><strong>Easy Discovery:</strong> Browse by categories, tags, or trending content</li>
                <li><strong>Community Features:</strong> Save favorites, leave comments, and engage with others</li>
                <li><strong>User Uploads:</strong> Share your own images with the community</li>
                <li><strong>Free Access:</strong> Enjoy our content without barriers</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Our Values</h2>
            <div class="grid sm:grid-cols-2 gap-4 mt-4">
                <div class="p-4 bg-neutral-50 dark:bg-neutral-800 rounded-lg">
                    <h3 class="font-semibold text-neutral-800 dark:text-neutral-200 mb-2">Quality</h3>
                    <p class="text-sm">We prioritize quality over quantity, ensuring our collection features the best content.</p>
                </div>
                <div class="p-4 bg-neutral-50 dark:bg-neutral-800 rounded-lg">
                    <h3 class="font-semibold text-neutral-800 dark:text-neutral-200 mb-2">Community</h3>
                    <p class="text-sm">We foster a respectful community where creators and viewers can connect.</p>
                </div>
                <div class="p-4 bg-neutral-50 dark:bg-neutral-800 rounded-lg">
                    <h3 class="font-semibold text-neutral-800 dark:text-neutral-200 mb-2">Respect</h3>
                    <p class="text-sm">We respect intellectual property rights and respond promptly to concerns.</p>
                </div>
                <div class="p-4 bg-neutral-50 dark:bg-neutral-800 rounded-lg">
                    <h3 class="font-semibold text-neutral-800 dark:text-neutral-200 mb-2">Privacy</h3>
                    <p class="text-sm">We protect your data and provide transparent privacy practices.</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Join Us</h2>
            <p>Become part of our growing community. Create an account to unlock features like saving favorites, uploading images, and more.</p>
            <div class="flex gap-3 mt-4">
                <a href="<?= $view->url('/register') ?>" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition">Create Account</a>
                <a href="<?= $view->url('/gallery') ?>" class="px-6 py-2 border border-neutral-300 dark:border-neutral-600 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 font-medium rounded-lg transition">Browse Gallery</a>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-200 mt-8 mb-3">Get in Touch</h2>
            <p>Have questions, feedback, or partnership inquiries? We'd love to hear from you.</p>
            <p class="mt-2"><a href="<?= $view->url('/contact') ?>" class="text-primary-600 hover:underline">Contact us →</a></p>
        </section>
    </div>
</div>
