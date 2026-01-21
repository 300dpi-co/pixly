<div class="max-w-4xl mx-auto px-4 py-12">
    <article class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm p-8 md:p-12">
        <header class="mb-8 pb-6 border-b border-neutral-200 dark:border-neutral-700">
            <h1 class="text-3xl md:text-4xl font-bold text-neutral-900 dark:text-white">
                <?= e($page['title']) ?>
            </h1>
        </header>

        <div class="prose prose-neutral dark:prose-invert max-w-none
                    prose-headings:text-neutral-900 dark:prose-headings:text-white
                    prose-h2:text-2xl prose-h2:font-semibold prose-h2:mt-8 prose-h2:mb-4
                    prose-h3:text-xl prose-h3:font-medium prose-h3:mt-6 prose-h3:mb-3
                    prose-p:text-neutral-600 dark:prose-p:text-neutral-300 prose-p:leading-relaxed
                    prose-a:text-primary-600 dark:prose-a:text-primary-400 prose-a:no-underline hover:prose-a:underline
                    prose-ul:my-4 prose-li:text-neutral-600 dark:prose-li:text-neutral-300
                    prose-ol:my-4
                    prose-strong:text-neutral-900 dark:prose-strong:text-white">
            <?= $content ?>
        </div>
    </article>
</div>
