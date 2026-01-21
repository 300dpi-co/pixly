<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Sitemap Generator
 *
 * Generates XML sitemaps for SEO.
 */
class SitemapGenerator
{
    private string $baseUrl;
    private array $urls = [];

    public function __construct()
    {
        $this->baseUrl = rtrim(config('app.url'), '/');
    }

    /**
     * Generate complete sitemap
     */
    public function generate(): string
    {
        $db = app()->getDatabase();

        // Static pages
        $this->addUrl('/', '1.0', 'daily');
        $this->addUrl('/gallery', '0.9', 'daily');
        $this->addUrl('/trending', '0.9', 'daily');
        $this->addUrl('/blog', '0.9', 'daily');
        $this->addUrl('/search', '0.5', 'weekly');

        // Categories
        $categories = $db->fetchAll(
            "SELECT slug, updated_at FROM categories WHERE is_active = 1"
        );
        foreach ($categories as $cat) {
            $this->addUrl('/category/' . $cat['slug'], '0.8', 'daily', $cat['updated_at']);
        }

        // Tags (top 500 by usage)
        $tags = $db->fetchAll(
            "SELECT slug, updated_at FROM tags WHERE usage_count > 0 ORDER BY usage_count DESC LIMIT 500"
        );
        foreach ($tags as $tag) {
            $this->addUrl('/tag/' . $tag['slug'], '0.6', 'weekly', $tag['updated_at']);
        }

        // Images (published only)
        $images = $db->fetchAll(
            "SELECT slug, updated_at FROM images
             WHERE status = 'published' AND moderation_status = 'approved'
             ORDER BY created_at DESC
             LIMIT 50000"
        );
        foreach ($images as $image) {
            $this->addUrl('/image/' . $image['slug'], '0.7', 'monthly', $image['updated_at']);
        }

        return $this->buildXml();
    }

    /**
     * Generate sitemap index for large sites
     */
    public function generateIndex(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $sitemaps = [
            'sitemap-pages.xml',
            'sitemap-images.xml',
            'sitemap-categories.xml',
            'sitemap-tags.xml',
            'sitemap-blog.xml',
        ];

        foreach ($sitemaps as $sitemap) {
            $xml .= "  <sitemap>\n";
            $xml .= "    <loc>{$this->baseUrl}/{$sitemap}</loc>\n";
            $xml .= "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
            $xml .= "  </sitemap>\n";
        }

        $xml .= '</sitemapindex>';

        return $xml;
    }

    /**
     * Generate images sitemap with image extensions
     */
    public function generateImagesSitemap(): string
    {
        $db = app()->getDatabase();

        $images = $db->fetchAll(
            "SELECT slug, title, alt_text, storage_path, updated_at FROM images
             WHERE status = 'published' AND moderation_status = 'approved'
             ORDER BY created_at DESC
             LIMIT 50000"
        );

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
        $xml .= 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        foreach ($images as $image) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($this->baseUrl . '/image/' . $image['slug']) . "</loc>\n";
            $xml .= "    <lastmod>" . date('Y-m-d', strtotime($image['updated_at'])) . "</lastmod>\n";
            $xml .= "    <changefreq>monthly</changefreq>\n";
            $xml .= "    <priority>0.7</priority>\n";
            $xml .= "    <image:image>\n";
            $xml .= "      <image:loc>" . htmlspecialchars($this->baseUrl . '/uploads/' . $image['storage_path']) . "</image:loc>\n";
            $xml .= "      <image:title>" . htmlspecialchars($image['title']) . "</image:title>\n";
            if ($image['alt_text']) {
                $xml .= "      <image:caption>" . htmlspecialchars($image['alt_text']) . "</image:caption>\n";
            }
            $xml .= "    </image:image>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Add URL to sitemap
     */
    private function addUrl(string $path, string $priority = '0.5', string $changefreq = 'weekly', ?string $lastmod = null): void
    {
        $this->urls[] = [
            'loc' => $this->baseUrl . $path,
            'priority' => $priority,
            'changefreq' => $changefreq,
            'lastmod' => $lastmod ? date('Y-m-d', strtotime($lastmod)) : date('Y-m-d'),
        ];
    }

    /**
     * Build XML from URLs
     */
    private function buildXml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($this->urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
            $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Generate blog sitemap
     */
    public function generateBlogSitemap(): string
    {
        $db = app()->getDatabase();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
        $xml .= 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        // Blog index
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($this->baseUrl . '/blog') . "</loc>\n";
        $xml .= "    <changefreq>daily</changefreq>\n";
        $xml .= "    <priority>0.9</priority>\n";
        $xml .= "  </url>\n";

        // Blog categories
        $categories = $db->fetchAll(
            "SELECT slug, updated_at FROM blog_categories WHERE is_active = 1"
        );
        foreach ($categories as $cat) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($this->baseUrl . '/blog/category/' . $cat['slug']) . "</loc>\n";
            $xml .= "    <lastmod>" . date('Y-m-d', strtotime($cat['updated_at'])) . "</lastmod>\n";
            $xml .= "    <changefreq>daily</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "  </url>\n";
        }

        // Blog tags (top 200 by usage)
        $tags = $db->fetchAll(
            "SELECT t.slug, MAX(p.updated_at) as updated_at
             FROM blog_tags t
             JOIN blog_post_tags pt ON t.id = pt.tag_id
             JOIN blog_posts p ON pt.post_id = p.id
             WHERE p.status = 'published'
             GROUP BY t.id
             ORDER BY COUNT(*) DESC
             LIMIT 200"
        );
        foreach ($tags as $tag) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($this->baseUrl . '/blog/tag/' . $tag['slug']) . "</loc>\n";
            if ($tag['updated_at']) {
                $xml .= "    <lastmod>" . date('Y-m-d', strtotime($tag['updated_at'])) . "</lastmod>\n";
            }
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.6</priority>\n";
            $xml .= "  </url>\n";
        }

        // Blog posts with featured images
        $posts = $db->fetchAll(
            "SELECT slug, title, featured_image, featured_image_alt, updated_at, published_at
             FROM blog_posts
             WHERE status = 'published' AND published_at <= NOW()
             ORDER BY published_at DESC
             LIMIT 10000"
        );
        foreach ($posts as $post) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($this->baseUrl . '/blog/' . $post['slug']) . "</loc>\n";
            $xml .= "    <lastmod>" . date('Y-m-d', strtotime($post['updated_at'])) . "</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";

            // Include featured image if exists
            if (!empty($post['featured_image'])) {
                $xml .= "    <image:image>\n";
                $xml .= "      <image:loc>" . htmlspecialchars($this->baseUrl . '/uploads/' . $post['featured_image']) . "</image:loc>\n";
                $xml .= "      <image:title>" . htmlspecialchars($post['title']) . "</image:title>\n";
                if (!empty($post['featured_image_alt'])) {
                    $xml .= "      <image:caption>" . htmlspecialchars($post['featured_image_alt']) . "</image:caption>\n";
                }
                $xml .= "    </image:image>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Save sitemap to file
     */
    public function saveToFile(string $filename = 'sitemap.xml'): bool
    {
        $content = $this->generate();
        $path = \ROOT_PATH . '/public_html/' . $filename;

        return file_put_contents($path, $content) !== false;
    }
}
