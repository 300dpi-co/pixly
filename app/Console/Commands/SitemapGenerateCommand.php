<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;
use App\Services\SitemapGenerator;

/**
 * Sitemap Generate Command
 *
 * Generates and saves XML sitemaps.
 */
class SitemapGenerateCommand extends Command
{
    protected string $name = 'sitemap:generate';
    protected string $description = 'Generate XML sitemaps';

    public function run(array $args): int
    {
        $this->info('Generating sitemaps...');

        $generator = new SitemapGenerator();
        $basePath = \ROOT_PATH . '/public_html/';

        // Generate main sitemap
        $this->line('  - Generating sitemap.xml...');
        $mainSitemap = $generator->generate();
        file_put_contents($basePath . 'sitemap.xml', $mainSitemap);

        // Generate images sitemap
        $this->line('  - Generating sitemap-images.xml...');
        $imagesSitemap = $generator->generateImagesSitemap();
        file_put_contents($basePath . 'sitemap-images.xml', $imagesSitemap);

        // Count URLs
        $mainCount = substr_count($mainSitemap, '<url>');
        $imagesCount = substr_count($imagesSitemap, '<url>');

        $this->line('');
        $this->info('Sitemaps generated successfully!');
        $this->line("  - sitemap.xml: {$mainCount} URLs");
        $this->line("  - sitemap-images.xml: {$imagesCount} URLs");

        return 0;
    }
}
