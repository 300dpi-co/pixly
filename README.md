# Pixly

A modern, SEO-focused image gallery platform built with vanilla PHP 8.4. Features AI-powered image analysis, contributor system, and comprehensive admin panel.

![PHP Version](https://img.shields.io/badge/PHP-8.4+-blue.svg)
![License](https://img.shields.io/badge/License-AGPL--3.0-blue.svg)

## Features

### Core Features
- **Image Management** - Upload, organize, and manage images with categories and tags
- **AI-Powered Analysis** - Automatic title, description, and tag generation using DeepSeek AI
- **SEO Optimized** - Clean URLs, meta tags, sitemaps, and structured data
- **Responsive Design** - Mobile-first design with Tailwind CSS
- **Dark Mode** - System-aware dark mode toggle

### User System
- **User Roles** - Superadmin, Admin, Moderator, Contributor, User
- **Contributor System** - Users can request contributor status to upload images
- **User Profiles** - Public profiles with uploaded images and favorites
- **Authentication** - Secure login, registration, password reset

### Admin Panel
- **Dashboard** - Overview of site statistics
- **Image Moderation** - Approve, reject, or edit uploaded images
- **User Management** - Manage users and roles
- **Marketing Tools** - Ad placements, popups, announcements, newsletter
- **Settings** - Comprehensive site configuration
- **Blog System** - Built-in blog with categories and comments

### Additional Features
- **Premium System** - Optional premium subscriptions (Stripe integration ready)
- **Appreciation System** - Users can appreciate images
- **External APIs** - Unsplash and Pexels integration for stock images
- **Adult Mode** - Age gate, NSFW blur, quick exit button
- **Analytics** - Built-in analytics dashboard

## Requirements

- PHP 8.4 or higher
- MySQL 8.0 or higher
- Apache with mod_rewrite enabled
- Composer (optional, no dependencies required)

## Installation

### Option 1: Installation Wizard (Recommended)

1. Clone the repository:
   ```bash
   git clone https://github.com/300dpi-co/pixly.git
   cd pixly
   ```

2. Point your web server to the `public_html` directory

3. Visit `http://yourdomain.com/install` in your browser

4. Follow the installation wizard to:
   - Check system requirements
   - Configure database connection
   - Create admin account
   - Complete setup

### Option 2: Manual Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/300dpi-co/pixly.git
   cd pixly
   ```

2. Copy the database config example:
   ```bash
   cp app/Config/database.php.example app/Config/database.php
   ```

3. Edit `app/Config/database.php` with your database credentials

4. Import the database schema:
   ```bash
   mysql -u username -p your_database < database/schema.sql
   ```

5. Run migrations:
   ```bash
   mysql -u username -p your_database < database/migrations/*.sql
   ```

6. Set permissions:
   ```bash
   chmod -R 755 storage/
   chmod -R 755 public_html/uploads/
   chmod -R 755 public_html/cache/
   ```

7. Point your web server to `public_html` directory

## Configuration

### Environment Settings

Edit `app/Config/config.php`:

```php
'app' => [
    'url' => 'https://yourdomain.com',
    'env' => 'production',  // development, staging, production
    'debug' => false,       // Set to false in production
],
```

### API Keys

Configure external services in `app/Config/config.php` or via Admin > Settings:

- **DeepSeek AI** - For AI-powered image analysis
- **Unsplash** - For stock image integration
- **Pexels** - For stock image integration
- **Stripe** - For premium subscriptions (optional)

## Directory Structure

```
fwp/
├── app/
│   ├── Config/         # Configuration files
│   ├── Controllers/    # HTTP controllers
│   ├── Core/           # Framework core classes
│   ├── Helpers/        # Helper functions
│   ├── Middleware/     # Request middleware
│   ├── Models/         # Database models
│   ├── Services/       # Business logic services
│   └── Views/          # PHP templates
├── database/
│   ├── migrations/     # Database migrations
│   └── schema.sql      # Base database schema
├── public_html/        # Web root (point server here)
│   ├── assets/         # CSS, JS, images
│   ├── uploads/        # User uploads
│   └── index.php       # Application entry point
└── storage/
    ├── cache/          # Application cache
    ├── logs/           # Error logs
    └── sessions/       # Session files
```

## User Roles

| Role | Permissions |
|------|-------------|
| **User** | Browse, favorite, like, appreciate, download |
| **Contributor** | All user permissions + upload images |
| **Moderator** | All contributor permissions + moderate content |
| **Admin** | All moderator permissions + manage users, settings |
| **Superadmin** | Full system access |

## Admin Panel

Access the admin panel at `/admin` with an admin account.

### Key Sections

- **Dashboard** - Site overview and statistics
- **Images** - Manage all images
- **Categories & Tags** - Organize content
- **Moderation** - Review pending content
- **Users** - User management
- **Contributors** - Review contributor requests
- **Marketing** - Ads, popups, announcements
- **Blog** - Manage blog posts
- **Settings** - Site configuration

## Feature Toggles

Control features via Admin > Settings > Features:

| Setting | Description |
|---------|-------------|
| `premium_enabled` | Enable/disable premium subscriptions |
| `registration_enabled` | Enable/disable user registration |
| `contributor_system_enabled` | Enable/disable contributor applications |
| `appreciate_system_enabled` | Enable/disable appreciation feature |

## Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover a security vulnerability, please email security@yourdomain.com instead of using the issue tracker.

## License

This project is dual-licensed:

- **AGPL-3.0** - Free for open source use. If you modify or run this software as a service, you must open-source your code. See [LICENSE-AGPL.txt](LICENSE-AGPL.txt).

- **Commercial License** - For businesses that cannot comply with AGPL requirements. Contact license@300dpi.co for pricing.

See [LICENSE](LICENSE) for details.

## Credits

- Built with [Tailwind CSS](https://tailwindcss.com/)
- Icons by [Heroicons](https://heroicons.com/)
- AI powered by [DeepSeek](https://deepseek.com/)

## Support

- Documentation: [docs/](docs/)
- Issues: [GitHub Issues](https://github.com/300dpi-co/pixly/issues)
