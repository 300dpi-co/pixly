# Contributing to Pixly

Thank you for your interest in contributing to Pixly! This document provides guidelines for contributing.

## Contributor License Agreement (CLA)

**Important:** Before we can accept your contribution, you must sign our [Contributor License Agreement](CLA.md).

**Why a CLA?**

Pixly uses dual licensing (AGPL-3.0 + Commercial). The CLA grants us the rights to include your contribution in both versions. Without it, we legally cannot accept your code.

**How to sign:**

When you submit your first pull request, the CLA Assistant bot will automatically ask you to sign. It's a one-time process.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for everyone.

## How to Contribute

### Reporting Bugs

1. Check if the bug has already been reported in [Issues](https://github.com/300dpi-co/pixly/issues)
2. If not, create a new issue with:
   - Clear, descriptive title
   - Steps to reproduce
   - Expected vs actual behavior
   - PHP version, MySQL version, browser info
   - Screenshots if applicable

### Suggesting Features

1. Check existing issues for similar suggestions
2. Create a new issue with the "Feature Request" label
3. Describe the feature and its use case
4. Explain why it would benefit the project

### Pull Requests

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature-name`
3. Make your changes
4. Test thoroughly
5. Commit with clear messages: `git commit -m "Add: description of change"`
6. Push to your fork: `git push origin feature/your-feature-name`
7. Open a Pull Request

## Development Setup

1. Clone your fork:
   ```bash
   git clone https://github.com/300dpi-co/pixly.git
   cd pixly
   ```

2. Set up local environment:
   ```bash
   cp app/Config/database.php.example app/Config/database.php
   # Edit database.php with your local credentials
   ```

3. Import database:
   ```bash
   mysql -u root -p your_database < database/schema.sql
   ```

4. Configure for development in `app/Config/config.php`:
   ```php
   'env' => 'development',
   'debug' => true,
   ```

## Coding Standards

### PHP

- Follow PSR-12 coding style
- Use strict types: `declare(strict_types=1);`
- Use type hints for parameters and return types
- Keep methods focused and small
- Document complex logic with comments

### Example

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;

class ExampleController extends Controller
{
    public function index(): Response
    {
        $data = $this->getData();

        return $this->view('example/index', [
            'data' => $data,
        ]);
    }

    private function getData(): array
    {
        // Implementation
        return [];
    }
}
```

### Views

- Use `<?= e($variable) ?>` for escaped output
- Use `<?= $variable ?>` only for trusted HTML
- Keep logic minimal in views
- Use partials for reusable components

### JavaScript

- Use vanilla JavaScript (no jQuery)
- Use Alpine.js for interactive components
- Keep scripts at the bottom of templates

### CSS

- Use Tailwind CSS utility classes
- Avoid custom CSS when Tailwind classes exist
- Keep dark mode in mind (`dark:` variants)

## Database Changes

1. Create a migration file in `database/migrations/`
2. Name it descriptively: `add_feature_name.sql`
3. Include both the change and rollback if possible
4. Update `database/schema.sql` if adding new tables

## Testing

Before submitting a PR:

1. Test all affected functionality manually
2. Verify on different screen sizes
3. Test in both light and dark mode
4. Check for PHP errors in `storage/logs/error.log`
5. Verify database migrations work on fresh install

## Commit Messages

Use clear, descriptive commit messages:

- `Add: new feature description`
- `Fix: bug description`
- `Update: what was changed`
- `Remove: what was removed`
- `Refactor: what was refactored`
- `Docs: documentation changes`

## Questions?

Feel free to open an issue with the "Question" label or reach out to the maintainers.

Thank you for contributing!
