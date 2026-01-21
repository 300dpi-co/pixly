<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Blog Tag Model
 */
class BlogTag extends Model
{
    protected string $table = 'blog_tags';
    protected bool $timestamps = false;

    protected array $fillable = [
        'name',
        'slug',
    ];

    /**
     * Find tag by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return self::firstWhere('slug', $slug);
    }

    /**
     * Find or create tag by name
     */
    public static function findOrCreate(string $name): self
    {
        $slug = self::generateSlug($name);
        $existing = self::findBySlug($slug);

        if ($existing) {
            return $existing;
        }

        $tag = new static();
        $tag->name = trim($name);
        $tag->slug = $slug;
        $tag->save();

        return $tag;
    }

    /**
     * Get all tags ordered by usage
     */
    public static function popular(int $limit = 20): array
    {
        $instance = new static();
        $rows = $instance->db()->fetchAll(
            "SELECT t.*, COUNT(pt.post_id) as usage_count
             FROM {$instance->table} t
             LEFT JOIN blog_post_tags pt ON t.id = pt.tag_id
             LEFT JOIN blog_posts p ON pt.post_id = p.id AND p.status = 'published'
             GROUP BY t.id
             HAVING usage_count > 0
             ORDER BY usage_count DESC
             LIMIT {$limit}"
        );
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get all tags with counts
     */
    public static function withCounts(): array
    {
        $instance = new static();
        $rows = $instance->db()->fetchAll(
            "SELECT t.*, COUNT(pt.post_id) as post_count
             FROM {$instance->table} t
             LEFT JOIN blog_post_tags pt ON t.id = pt.tag_id
             LEFT JOIN blog_posts p ON pt.post_id = p.id AND p.status = 'published'
             GROUP BY t.id
             ORDER BY t.name"
        );
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Generate unique slug
     */
    public static function generateSlug(string $name, ?int $excludeId = null): string
    {
        $slug = strtolower(trim($name));
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
     * Get posts with this tag
     */
    public function posts(int $limit = 12, int $offset = 0): array
    {
        return BlogPost::byTag($this->id, $limit, $offset);
    }

    /**
     * Count posts with this tag
     */
    public function postsCount(): int
    {
        return (int) $this->db()->fetchColumn(
            "SELECT COUNT(*)
             FROM blog_post_tags pt
             INNER JOIN blog_posts p ON pt.post_id = p.id
             WHERE pt.tag_id = :id AND p.status = 'published'",
            ['id' => $this->id]
        );
    }

    /**
     * Update all tag counts
     */
    public static function updateAllCounts(): void
    {
        $instance = new static();
        $instance->db()->query(
            "UPDATE blog_tags t SET post_count = (
                SELECT COUNT(*)
                FROM blog_post_tags pt
                INNER JOIN blog_posts p ON pt.post_id = p.id
                WHERE pt.tag_id = t.id AND p.status = 'published'
            )"
        );
    }

    /**
     * Parse tags from comma-separated string
     */
    public static function parseFromString(string $tagString): array
    {
        $names = array_filter(array_map('trim', explode(',', $tagString)));
        $tags = [];

        foreach ($names as $name) {
            if (!empty($name)) {
                $tags[] = self::findOrCreate($name);
            }
        }

        return $tags;
    }

    /**
     * Get tag IDs from comma-separated string
     */
    public static function getIdsFromString(string $tagString): array
    {
        $tags = self::parseFromString($tagString);
        return array_map(fn($tag) => $tag->id, $tags);
    }
}
