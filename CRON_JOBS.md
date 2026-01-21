# Cron Jobs Configuration

Set up these cron jobs on your production server.

## Required Cron Jobs

```bash
# AI queue processing - processes pending images through DeepSeek API
# Runs every 5 minutes
*/5 * * * * php /path/to/fwp/app/Console/cli.php ai:process

# Fetch trends - updates tag trend scores based on recent activity
# Runs daily at 3:00 AM
0 3 * * * php /path/to/fwp/app/Console/cli.php trends:fetch

# Generate sitemaps - creates sitemap.xml and sitemap-images.xml
# Runs daily at 4:00 AM
0 4 * * * php /path/to/fwp/app/Console/cli.php sitemap:generate

# Cache cleanup - removes expired cache files
# Runs every hour
0 * * * * php /path/to/fwp/app/Console/cli.php cache:cleanup
```

## Available CLI Commands

Run `php app/Console/cli.php help` to see all commands:

| Command | Description |
|---------|-------------|
| `help` | Show available commands |
| `ai:process` | Process AI queue for pending images |
| `trends:fetch` | Update tag trend scores |
| `sitemap:generate` | Generate XML sitemaps |
| `cache:cleanup` | Remove expired cache files |
| `cache:clear` | Clear all cache (use manually) |

## Manual Usage

```bash
# Process AI queue manually
php app/Console/cli.php ai:process

# Process specific number of items
php app/Console/cli.php ai:process --limit=10

# Generate sitemaps
php app/Console/cli.php sitemap:generate

# Clear all cache
php app/Console/cli.php cache:clear
```

## Notes

- Replace `/path/to/fwp` with your actual installation path
- Ensure PHP is in your system PATH or use full path to PHP binary
- Log output can be redirected: `>> /var/log/fwp-cron.log 2>&1`
