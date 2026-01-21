<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Blog Category Model
 */
class BlogCategory extends Model
{
    protected string $table = 'blog_categories';

    protected array $fillable = [
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    /**
     * Find category by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return self::firstWhere('slug', $slug);
    }

    /**
     * Get all active categories
     */
    public static function active(): array
    {
        $instance = new static();
        $rows = $instance->db()->fetchAll(
            "SELECT * FROM {$instance->table} WHERE is_active = 1 ORDER BY sort_order, name"
        );
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get categories with post counts
     */
    public static function withPostCounts(): array
    {
        $instance = new static();
        $rows = $instance->db()->fetchAll(
            "SELECT c.*, COUNT(p.id) as post_count
             FROM {$instance->table} c
             LEFT JOIN blog_posts p ON c.id = p.category_id AND p.status = 'published'
             WHERE c.is_active = 1
             GROUP BY c.id
             ORDER BY c.sort_order, c.name"
        );
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get parent category
     */
    public function parent(): ?self
    {
        if (!$this->parent_id) {
            return null;
        }
        return self::find($this->parent_id);
    }

    /**
     * Get child categories
     */
    public function children(): array
    {
        return self::where('parent_id', $this->id);
    }

    /**
     * Get all root categories (no parent)
     */
    public static function roots(): array
    {
        $instance = new static();
        $rows = $instance->db()->fetchAll(
            "SELECT * FROM {$instance->table} WHERE parent_id IS NULL ORDER BY sort_order, name"
        );
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get categories as nested tree
     */
    public static function tree(): array
    {
        $instance = new static();
        $all = $instance->db()->fetchAll(
            "SELECT * FROM {$instance->table} ORDER BY sort_order, name"
        );

        $indexed = [];
        foreach ($all as $row) {
            $indexed[$row['id']] = $row;
            $indexed[$row['id']]['children'] = [];
        }

        $tree = [];
        foreach ($indexed as $id => &$node) {
            if ($node['parent_id'] && isset($indexed[$node['parent_id']])) {
                $indexed[$node['parent_id']]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }

        return $tree;
    }

    /**
     * Get flattened categories for select dropdown
     */
    public static function forSelect(?int $excludeId = null): array
    {
        $tree = self::tree();
        $flat = [];
        self::flattenTree($tree, $flat, 0, $excludeId);
        return $flat;
    }

    /**
     * Helper to flatten tree with indentation
     */
    private static function flattenTree(array $nodes, array &$flat, int $depth = 0, ?int $excludeId = null): void
    {
        foreach ($nodes as $node) {
            if ($excludeId && $node['id'] == $excludeId) {
                continue;
            }
            $flat[$node['id']] = str_repeat('— ', $depth) . $node['name'];
            if (!empty($node['children'])) {
                self::flattenTree($node['children'], $flat, $depth + 1, $excludeId);
            }
        }
    }

    /**
     * Update post count for category
     */
    public static function updatePostCount(int $categoryId): void
    {
        $instance = new static();
        $count = (int) $instance->db()->fetchColumn(
            "SELECT COUNT(*) FROM blog_posts WHERE category_id = :id AND status = 'published'",
            ['id' => $categoryId]
        );
        $instance->db()->update('blog_categories', ['post_count' => $count], 'id = :category_id', ['category_id' => $categoryId]);
    }

    /**
     * Update all post counts
     */
    public static function updateAllPostCounts(): void
    {
        $instance = new static();
        $instance->db()->query(
            "UPDATE blog_categories c SET post_count = (
                SELECT COUNT(*) FROM blog_posts p
                WHERE p.category_id = c.id AND p.status = 'published'
            )"
        );
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
     * Get posts in this category
     */
    public function posts(int $limit = 12, int $offset = 0): array
    {
        return BlogPost::byCategory($this->id, $limit, $offset);
    }

    /**
     * Count published posts
     */
    public function postsCount(): int
    {
        return (int) $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM blog_posts WHERE category_id = :id AND status = 'published'",
            ['id' => $this->id]
        );
    }

    /**
     * Get SEO title
     */
    public function getSeoTitle(): string
    {
        return $this->meta_title ?: $this->name;
    }

    /**
     * Get SEO description
     */
    public function getSeoDescription(): string
    {
        return $this->meta_description ?: $this->description ?: "Browse all posts in {$this->name}";
    }
}
