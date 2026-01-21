<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Category Model
 */
class Category extends Model
{
    protected string $table = 'categories';

    protected array $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'cover_image_id',
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
     * Update image count
     */
    public function updateImageCount(): void
    {
        $count = (int) $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM image_categories WHERE category_id = :id",
            ['id' => $this->id]
        );
        $this->image_count = $count;
        $this->save();
    }

    /**
     * Check if category can be deleted
     */
    public function canDelete(): bool
    {
        // Check for child categories
        $childCount = (int) $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM categories WHERE parent_id = :id",
            ['id' => $this->id]
        );
        return $childCount === 0;
    }
}
