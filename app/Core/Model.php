<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base Model
 *
 * Provides common database operations for models.
 * Uses the Active Record pattern.
 */
abstract class Model
{
    /**
     * The table associated with the model
     */
    protected string $table;

    /**
     * The primary key column
     */
    protected string $primaryKey = 'id';

    /**
     * Columns that can be mass assigned
     */
    protected array $fillable = [];

    /**
     * Columns that should be hidden from arrays
     */
    protected array $hidden = [];

    /**
     * Enable timestamps (created_at, updated_at)
     */
    protected bool $timestamps = true;

    /**
     * Model attributes
     */
    protected array $attributes = [];

    /**
     * Original attributes (for dirty checking)
     */
    protected array $original = [];

    /**
     * Get database instance
     */
    protected function db(): Database
    {
        return app()->getDatabase();
    }

    /**
     * Create a new model instance
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->original = $this->attributes;
    }

    /**
     * Fill model with attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (empty($this->fillable) || in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Get an attribute
     */
    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Set an attribute
     */
    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Check if attribute exists
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Get all attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Convert to array (excluding hidden)
     */
    public function toArray(): array
    {
        return array_diff_key($this->attributes, array_flip($this->hidden));
    }

    /**
     * Convert to JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Find by primary key
     */
    public static function find(int|string $id): ?static
    {
        $instance = new static();
        $row = $instance->db()->fetch(
            "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = :id LIMIT 1",
            ['id' => $id]
        );

        if ($row === null) {
            return null;
        }

        return static::hydrate($row);
    }

    /**
     * Find by primary key or throw exception
     */
    public static function findOrFail(int|string $id): static
    {
        $model = static::find($id);

        if ($model === null) {
            throw new \RuntimeException("Model not found: {$id}");
        }

        return $model;
    }

    /**
     * Get all records
     */
    public static function all(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $instance = new static();
        $rows = $instance->db()->fetchAll(
            "SELECT * FROM {$instance->table} ORDER BY {$orderBy} {$direction}"
        );

        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get records with conditions
     */
    public static function where(string $column, mixed $value, string $operator = '='): array
    {
        $instance = new static();
        $rows = $instance->db()->fetchAll(
            "SELECT * FROM {$instance->table} WHERE {$column} {$operator} :value",
            ['value' => $value]
        );

        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get first record with conditions
     */
    public static function firstWhere(string $column, mixed $value, string $operator = '='): ?static
    {
        $instance = new static();
        $row = $instance->db()->fetch(
            "SELECT * FROM {$instance->table} WHERE {$column} {$operator} :value LIMIT 1",
            ['value' => $value]
        );

        if ($row === null) {
            return null;
        }

        return static::hydrate($row);
    }

    /**
     * Create a new record
     */
    public static function create(array $attributes): static
    {
        $instance = new static($attributes);
        $instance->save();
        return $instance;
    }

    /**
     * Save the model (insert or update)
     */
    public function save(): bool
    {
        $now = date('Y-m-d H:i:s');

        if ($this->timestamps) {
            $this->attributes['updated_at'] = $now;
        }

        if (isset($this->attributes[$this->primaryKey])) {
            // Update
            $data = $this->getDirty();
            if (empty($data)) {
                return true;
            }

            $this->db()->update(
                $this->table,
                $data,
                "{$this->primaryKey} = :pk_value",
                ['pk_value' => $this->attributes[$this->primaryKey]]
            );
        } else {
            // Insert
            if ($this->timestamps) {
                $this->attributes['created_at'] = $now;
            }

            $data = array_intersect_key(
                $this->attributes,
                array_flip($this->fillable ?: array_keys($this->attributes))
            );

            // Add timestamps to data
            if ($this->timestamps) {
                $data['created_at'] = $this->attributes['created_at'];
                $data['updated_at'] = $this->attributes['updated_at'];
            }

            $id = $this->db()->insert($this->table, $data);
            $this->attributes[$this->primaryKey] = $id;
        }

        $this->original = $this->attributes;
        return true;
    }

    /**
     * Delete the model
     */
    public function delete(): bool
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return false;
        }

        $this->db()->delete(
            $this->table,
            "{$this->primaryKey} = :id",
            ['id' => $this->attributes[$this->primaryKey]]
        );

        return true;
    }

    /**
     * Get dirty attributes (changed since load)
     */
    public function getDirty(): array
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Check if model is dirty
     */
    public function isDirty(): bool
    {
        return !empty($this->getDirty());
    }

    /**
     * Hydrate a model from database row
     */
    protected static function hydrate(array $row): static
    {
        $instance = new static();
        $instance->attributes = $row;
        $instance->original = $row;
        return $instance;
    }

    /**
     * Count records
     */
    public static function count(?string $where = null, array $params = []): int
    {
        $instance = new static();
        $sql = "SELECT COUNT(*) FROM {$instance->table}";

        if ($where) {
            $sql .= " WHERE {$where}";
        }

        return (int) $instance->db()->fetchColumn($sql, $params);
    }

    /**
     * Paginate results
     */
    public static function paginate(int $page = 1, int $perPage = 24, ?string $orderBy = null): array
    {
        $instance = new static();
        $offset = ($page - 1) * $perPage;

        $orderClause = $orderBy ?: "{$instance->primaryKey} DESC";

        $rows = $instance->db()->fetchAll(
            "SELECT * FROM {$instance->table} ORDER BY {$orderClause} LIMIT :limit OFFSET :offset",
            ['limit' => $perPage, 'offset' => $offset]
        );

        $total = static::count();

        return [
            'data' => array_map(fn($row) => static::hydrate($row), $rows),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }
}
