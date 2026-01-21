<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Blog Post Model
 */
class BlogPost extends Model
{
    protected string $table = 'blog_posts';

    protected array $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'featured_image_alt',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'og_image',
        'category_id',
        'author_id',
        'status',
        'visibility',
        'password_hash',
        'is_featured',
        'allow_comments',
        'ai_generated',
        'ai_prompt',
        'ai_model',
        'read_time_minutes',
        'published_at',
        'scheduled_at',
    ];

    /**
     * Find post by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return self::firstWhere('slug', $slug);
    }

    /**
     * Get published posts
     */
    public static function published(int $limit = 12, int $offset = 0): array
    {
        $instance = new static();
        $rows = $instance->db()->fetchAll(
            "SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug
             FROM {$instance->table} p
             LEFT JOIN users u ON p.author_id = u.id
             LEFT JOIN blog_categories c ON p.category_id = c.id
             WHERE p.status = 'published'
             ORDER BY p.is_featured DESC, p.created_at DESC
             LIMIT {$limit} OFFSET {$offset}"
        );
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get total published count
     */
    public static function publishedCount(): int
    {
        $instance = new static();
        return (int) $instance->db()->fetchColumn(
            "SELECT COUNT(*) FROM {$instance->table} WHERE status = 'published'"
        );
    }

    /**
     * Get featured posts
     */
    public static function featured(int $limit = 5): array
    {
        $instance = new static();
        $rows = $instance->db()->fetchAll(
            "SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug
             FROM {$instance->table} p
             LEFT JOIN users u ON p.author_id = u.id
             LEFT JOIN blog_categories c ON p.category_id = c.id
             WHERE p.status = 'published' AND p.is_featured = 1
             ORDER BY p.published_at DESC
             LIMIT {$limit}"
        );
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get posts by category
     */
    public static function byCategory(int $categoryId, int $limit = 12, int $offset = 0): array
    {
        $instance = new static();
        $rows = $instance->db()->fetchAll(
            "SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug
             FROM {$instance->table} p
             LEFT JOIN users u ON p.author_id = u.id
             LEFT JOIN blog_categories c ON p.category_id = c.id
             WHERE p.category_id = :category_id AND p.status = 'published'
             ORDER BY p.published_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            ['category_id' => $categoryId]
        );
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get posts by tag
     */
    public static function byTag(int $tagId, int $limit = 12, int $offset = 0): array
    {
        $instance = new static();
        $rows = $instance->db()->fetchAll(
            "SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug
             FROM {$instance->table} p
             INNER JOIN blog_post_tags pt ON p.id = pt.post_id
             LEFT JOIN users u ON p.author_id = u.id
             LEFT JOIN blog_categories c ON p.category_id = c.id
             WHERE pt.tag_id = :tag_id AND p.status = 'published'
             ORDER BY p.published_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            ['tag_id' => $tagId]
        );
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Search posts
     */
    public static function search(string $query, int $limit = 12, int $offset = 0): array
    {
        $instance = new static();
        $rows = $instance->db()->fetchAll(
            "SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug,
                    MATCH(p.title, p.excerpt, p.content) AGAINST(:query IN NATURAL LANGUAGE MODE) as relevance
             FROM {$instance->table} p
             LEFT JOIN users u ON p.author_id = u.id
             LEFT JOIN blog_categories c ON p.category_id = c.id
             WHERE p.status = 'published'
               AND MATCH(p.title, p.excerpt, p.content) AGAINST(:query2 IN NATURAL LANGUAGE MODE)
             ORDER BY relevance DESC
             LIMIT {$limit} OFFSET {$offset}",
            ['query' => $query, 'query2' => $query]
        );
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get related posts
     */
    public function relatedPosts(int $limit = 4): array
    {
        // First try manually curated related posts
        $rows = $this->db()->fetchAll(
            "SELECT p.*, u.username as author_name
             FROM blog_related_posts rp
             INNER JOIN blog_posts p ON rp.related_post_id = p.id
             LEFT JOIN users u ON p.author_id = u.id
             WHERE rp.post_id = :post_id AND p.status = 'published'
             ORDER BY rp.sort_order
             LIMIT {$limit}",
            ['post_id' => $this->id]
        );

        if (count($rows) >= $limit) {
            return array_map(fn($row) => static::hydrate($row), $rows);
        }

        // Fill with auto-related posts (same category or tags)
        $existingIds = array_column($rows, 'id');
        $existingIds[] = $this->id;
        $placeholders = implode(',', array_fill(0, count($existingIds), '?'));
        $remaining = (int) ($limit - count($rows));

        $autoRelated = $this->db()->fetchAll(
            "SELECT DISTINCT p.*, u.username as author_name
             FROM blog_posts p
             LEFT JOIN users u ON p.author_id = u.id
             LEFT JOIN blog_post_tags pt ON p.id = pt.post_id
             WHERE p.status = 'published'
               AND p.id NOT IN ({$placeholders})
               AND (p.category_id = ? OR pt.tag_id IN (
                   SELECT tag_id FROM blog_post_tags WHERE post_id = ?
               ))
             ORDER BY p.published_at DESC
             LIMIT {$remaining}",
            [...$existingIds, $this->category_id, $this->id]
        );

        $rows = array_merge($rows, $autoRelated);
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get post author
     */
    public function author(): ?User
    {
        return User::find($this->author_id);
    }

    /**
     * Get post category
     */
    public function category(): ?BlogCategory
    {
        return $this->category_id ? BlogCategory::find($this->category_id) : null;
    }

    /**
     * Get post tags
     */
    public function tags(): array
    {
        $rows = $this->db()->fetchAll(
            "SELECT t.* FROM blog_tags t
             INNER JOIN blog_post_tags pt ON t.id = pt.tag_id
             WHERE pt.post_id = :post_id
             ORDER BY t.name",
            ['post_id' => $this->id]
        );
        return array_map(fn($row) => BlogTag::hydrate($row), $rows);
    }

    /**
     * Sync tags
     */
    public function syncTags(array $tagIds): void
    {
        $this->db()->delete('blog_post_tags', 'post_id = ?', [$this->id]);

        foreach ($tagIds as $tagId) {
            $this->db()->insert('blog_post_tags', [
                'post_id' => $this->id,
                'tag_id' => (int) $tagId,
            ]);
        }

        // Update tag counts
        BlogTag::updateAllCounts();
    }

    /**
     * Get approved comments
     */
    public function comments(): array
    {
        return BlogComment::forPost($this->id);
    }

    /**
     * Increment view count
     */
    public function incrementViews(): void
    {
        $this->db()->query(
            "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = :id",
            ['id' => $this->id]
        );
        $this->view_count++;
    }

    /**
     * Calculate read time from content
     */
    public function calculateReadTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        $readTime = max(1, (int) ceil($wordCount / 200)); // 200 words per minute
        return $readTime;
    }

    /**
     * Generate excerpt from content
     */
    public function generateExcerpt(int $length = 200): string
    {
        $text = strip_tags($this->content ?? '');
        $text = preg_replace('/\s+/', ' ', $text);

        if (strlen($text) <= $length) {
            return $text;
        }

        $excerpt = substr($text, 0, $length);
        $lastSpace = strrpos($excerpt, ' ');

        if ($lastSpace !== false) {
            $excerpt = substr($excerpt, 0, $lastSpace);
        }

        return $excerpt . '...';
    }

    /**
     * Generate unique slug
     */
    public static function generateSlug(string $title, ?int $excludeId = null): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        $instance = new static();
        $baseSlug = $slug;
        $counter = 1;

        while (true) {
            $params = ['slug' => $slug];
            $excludeClause = '';

            if ($excludeId) {
                $excludeClause = ' AND id != :exclude_id';
                $params['exclude_id'] = $excludeId;
            }

            $exists = $instance->db()->fetchColumn(
                "SELECT COUNT(*) FROM {$instance->table} WHERE slug = :slug{$excludeClause}",
                $params
            );

            if (!$exists) {
                break;
            }

            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get SEO meta title
     */
    public function getSeoTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }

    /**
     * Get SEO meta description
     */
    public function getSeoDescription(): string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }
        return $this->excerpt ?: $this->generateExcerpt(160);
    }

    /**
     * Get Schema.org Article data
     */
    public function getSchemaData(string $baseUrl): array
    {
        $author = $this->author();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $this->title,
            'description' => $this->getSeoDescription(),
            'image' => $this->featured_image ? $baseUrl . '/uploads/' . $this->featured_image : null,
            'author' => [
                '@type' => 'Person',
                'name' => $author ? $author->username : 'Unknown',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $baseUrl . '/assets/images/logo.png',
                ],
            ],
            'datePublished' => $this->published_at,
            'dateModified' => $this->updated_at,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $baseUrl . '/blog/' . $this->slug,
            ],
            'wordCount' => str_word_count(strip_tags($this->content ?? '')),
            'articleSection' => $this->category_name ?? 'Blog',
        ];
    }

    /**
     * Create revision
     */
    public function createRevision(?string $note = null): void
    {
        $this->db()->insert('blog_post_revisions', [
            'post_id' => $this->id,
            'user_id' => $_SESSION['user_id'] ?? $this->author_id,
            'title' => $this->title,
            'content' => $this->content,
            'revision_note' => $note,
        ]);
    }

    /**
     * Get revisions
     */
    public function revisions(): array
    {
        return $this->db()->fetchAll(
            "SELECT r.*, u.username
             FROM blog_post_revisions r
             LEFT JOIN users u ON r.user_id = u.id
             WHERE r.post_id = :post_id
             ORDER BY r.created_at DESC",
            ['post_id' => $this->id]
        );
    }

    /**
     * Publish post
     */
    public function publish(): void
    {
        $this->status = 'published';
        $this->published_at = date('Y-m-d H:i:s');
        $this->save();

        // Update category post count
        if ($this->category_id) {
            BlogCategory::updatePostCount($this->category_id);
        }
    }

    /**
     * Get all for admin
     */
    public static function allForAdmin(array $filters = []): array
    {
        $instance = new static();
        $where = '1=1';
        $params = [];

        if (!empty($filters['status'])) {
            $where .= ' AND p.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['category_id'])) {
            $where .= ' AND p.category_id = :category_id';
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $where .= ' AND (p.title LIKE :search OR p.content LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $rows = $instance->db()->fetchAll(
            "SELECT p.*, u.username as author_name, c.name as category_name
             FROM {$instance->table} p
             LEFT JOIN users u ON p.author_id = u.id
             LEFT JOIN blog_categories c ON p.category_id = c.id
             WHERE {$where}
             ORDER BY p.created_at DESC",
            $params
        );

        return array_map(fn($row) => static::hydrate($row), $rows);
    }
}
