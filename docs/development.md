# Development Guide

Guide for developers extending or modifying Pixly.

## Architecture Overview

FWP uses a simple MVC architecture without external dependencies.

```
Request → index.php → Application → Router → Controller → View → Response
                                       ↓
                                    Model → Database
```

## Core Classes

### Application (`app/Core/Application.php`)

Main application class that handles:
- Configuration loading
- Request routing
- Middleware execution
- Error handling

### Router (`app/Core/Router.php`)

Handles URL routing:
- Route registration
- Parameter extraction
- Middleware assignment

### Controller (`app/Core/Controller.php`)

Base controller providing:
- View rendering
- Database access
- Request handling
- Validation

### Model (`app/Core/Model.php`)

Base model with:
- Database connection
- Basic CRUD operations
- Query building helpers

### View (`app/Core/View.php`)

Template engine:
- Layout support
- Partial rendering
- Helper functions

### Response (`app/Core/Response.php`)

HTTP response handling:
- Status codes
- Headers
- Redirects
- JSON responses

## Creating a Controller

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;

class ExampleController extends Controller
{
    /**
     * List all items
     */
    public function index(): Response
    {
        $items = $this->db()->fetchAll("SELECT * FROM items");

        return $this->view('example/index', [
            'title' => 'Items',
            'items' => $items,
        ]);
    }

    /**
     * Show single item
     */
    public function show(int|string $id): Response
    {
        $item = $this->db()->fetch(
            "SELECT * FROM items WHERE id = :id",
            ['id' => $id]
        );

        if (!$item) {
            return $this->notFound();
        }

        return $this->view('example/show', [
            'title' => $item['name'],
            'item' => $item,
        ]);
    }

    /**
     * Create new item
     */
    public function store(): Response
    {
        $data = $this->request->all();

        // Validate
        $errors = $this->validate($data, [
            'name' => 'required|max:255',
            'description' => 'required',
        ]);

        if (!empty($errors)) {
            session_flash('errors', $errors);
            session_flash('old', $data);
            return Response::redirect('/example/create');
        }

        // Insert
        $this->db()->insert('items', [
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        session_flash('success', 'Item created.');
        return Response::redirect('/example');
    }
}
```

## Creating a Model

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Item extends Model
{
    protected string $table = 'items';

    protected array $fillable = [
        'name',
        'description',
        'status',
    ];

    /**
     * Find by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        $instance = new static();
        $data = $instance->db()->fetch(
            "SELECT * FROM {$instance->table} WHERE slug = :slug",
            ['slug' => $slug]
        );

        if (!$data) {
            return null;
        }

        $instance->fill($data);
        return $instance;
    }

    /**
     * Get active items
     */
    public static function active(): array
    {
        $instance = new static();
        return $instance->db()->fetchAll(
            "SELECT * FROM {$instance->table} WHERE status = 'active' ORDER BY created_at DESC"
        );
    }

    /**
     * Scope: Published
     */
    public function scopePublished(): array
    {
        return $this->db()->fetchAll(
            "SELECT * FROM {$this->table} WHERE status = 'published'"
        );
    }
}
```

## Creating a View

Views are PHP files in `app/Views/`.

### Basic View (`app/Views/example/index.php`)

```php
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6"><?= e($title) ?></h1>

    <?php if (empty($items)): ?>
        <p class="text-neutral-500">No items found.</p>
    <?php else: ?>
        <div class="grid grid-cols-3 gap-4">
            <?php foreach ($items as $item): ?>
                <div class="bg-white rounded-lg shadow p-4">
                    <h2 class="font-semibold"><?= e($item['name']) ?></h2>
                    <p class="text-sm text-neutral-600"><?= e($item['description']) ?></p>
                    <a href="/example/<?= $item['id'] ?>" class="text-primary-600 hover:underline">
                        View Details
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
```

### Using Layouts

Views automatically use the layout specified in the controller:

```php
// In controller
return $this->view('example/index', $data, 'main');  // Uses layouts/main.php
return $this->view('admin/dashboard', $data, 'admin');  // Uses layouts/admin.php
```

## Adding Routes

Edit `app/Config/routes.php`:

```php
// Frontend routes
$router->get('/example', 'App\Controllers\ExampleController@index');
$router->get('/example/{id}', 'App\Controllers\ExampleController@show');
$router->post('/example', 'App\Controllers\ExampleController@store', ['auth', 'csrf']);

// Admin routes (within admin group)
$router->group('/admin', ['auth', 'admin'], function ($router) {
    $router->get('/example', 'App\Controllers\Admin\ExampleController@index');
    $router->get('/example/create', 'App\Controllers\Admin\ExampleController@create');
    $router->post('/example', 'App\Controllers\Admin\ExampleController@store', ['csrf']);
    $router->get('/example/{id}/edit', 'App\Controllers\Admin\ExampleController@edit');
    $router->post('/example/{id}', 'App\Controllers\Admin\ExampleController@update', ['csrf']);
    $router->post('/example/{id}/delete', 'App\Controllers\Admin\ExampleController@delete', ['csrf']);
});
```

## Middleware

### Available Middleware

| Middleware | Description |
|------------|-------------|
| `auth` | Requires authenticated user |
| `guest` | Requires non-authenticated user |
| `admin` | Requires admin role |
| `csrf` | Validates CSRF token |

### Creating Middleware

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class ExampleMiddleware
{
    public function handle(Request $request, \Closure $next): Response
    {
        // Before logic
        if (!$this->check()) {
            return Response::redirect('/');
        }

        // Continue to next middleware/controller
        $response = $next($request);

        // After logic (optional)

        return $response;
    }

    private function check(): bool
    {
        // Your logic here
        return true;
    }
}
```

Register in `app/Core/Application.php`:

```php
private array $middlewareMap = [
    'example' => \App\Middleware\ExampleMiddleware::class,
];
```

## Database Operations

### Direct Queries

```php
$db = $this->db();

// Fetch single row
$user = $db->fetch("SELECT * FROM users WHERE id = :id", ['id' => 1]);

// Fetch all rows
$users = $db->fetchAll("SELECT * FROM users WHERE role = :role", ['role' => 'admin']);

// Fetch single column
$count = $db->fetchColumn("SELECT COUNT(*) FROM users");

// Insert
$db->insert('users', [
    'username' => 'john',
    'email' => 'john@example.com',
]);
$id = $db->lastInsertId();

// Update
$db->update('users', ['status' => 'active'], 'id = :id', ['id' => 1]);

// Delete
$db->delete('users', 'id = :id', ['id' => 1]);

// Raw query
$db->query("UPDATE users SET login_count = login_count + 1 WHERE id = :id", ['id' => 1]);
```

## Helper Functions

### Available Helpers

```php
// Escape output
e($string);

// Get config value
config('app.name');
config('database.host', 'localhost');

// Get setting from database
setting('site_name');
setting('feature_enabled', 'default');

// URL helpers
url('/path');
asset('/css/style.css');

// Session helpers
session_flash('success', 'Message');
session_get_flash('success');

// CSRF
csrf_token();
csrf_field();

// Authentication
current_user();
is_logged_in();
is_admin();

// Formatting
format_date($date);
format_number($number);
format_bytes($bytes);
time_ago($datetime);

// String helpers
slug($string);
truncate($string, $length);
```

## Services

### Creating a Service

```php
<?php

declare(strict_types=1);

namespace App\Services;

class ExampleService
{
    private \App\Core\Database $db;

    public function __construct()
    {
        $this->db = app()->getDatabase();
    }

    public function process(array $data): array
    {
        // Business logic here
        return $result;
    }
}
```

### Using a Service

```php
// In controller
$service = new \App\Services\ExampleService();
$result = $service->process($data);
```

## Database Migrations

### Creating a Migration

Create file: `database/migrations/add_example_table.sql`

```sql
-- Add example table
CREATE TABLE IF NOT EXISTS examples (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status ENUM('draft', 'active', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Running Migrations

```bash
mysql -u username -p database < database/migrations/add_example_table.sql
```

## Testing

### Manual Testing Checklist

- [ ] Test all CRUD operations
- [ ] Test with different user roles
- [ ] Test form validation
- [ ] Test error handling
- [ ] Test responsive design
- [ ] Test dark mode

### Debug Mode

Enable in `app/Config/config.php`:

```php
'debug' => true,
```

### Error Logs

Check `storage/logs/error.log` for errors.

## Performance Tips

### Database
- Add indexes for frequently queried columns
- Use prepared statements (automatic with PDO)
- Limit result sets with pagination

### Caching
- Use settings cache for database settings
- Enable page caching in production
- Cache expensive queries

### Images
- Optimize images on upload
- Use WebP format when supported
- Implement lazy loading

## Security Best Practices

### Input Validation
- Always validate user input
- Use parameterized queries
- Sanitize file uploads

### Output Escaping
- Always use `e()` for user content
- Use `<?= ?>` only for trusted HTML

### Authentication
- Use strong password hashing (Argon2ID)
- Implement rate limiting
- Use CSRF protection

### File Uploads
- Validate file types
- Generate random filenames
- Store outside web root when possible
