# FWP Image Gallery - Project Context

## Overview

FWP (Free Website Platform) is an SEO-focused, AI-powered image gallery platform built with vanilla PHP 8.4 and MySQL. Open core model - public repo with premium features planned for later.

## Tech Stack

- **Backend:** Vanilla PHP 8.4 (no framework)
- **Database:** MySQL 8.0+
- **Frontend:** Tailwind CSS
- **Architecture:** Modular monolith with bounded-context modules in `app/Modules/`

## Local Development

- **URL:** http://fwp.local
- **Apache config:** C:/xamp/apache/conf/extra/httpd-vhosts.conf
- **Document root:** C:/Users/mpshop/Documents/fwp/public_html
- **Database:** fwp_gallery (root, no password, localhost:3306)

## Key Directories

```
app/
├── Config/         # Configuration files
├── Controllers/    # HTTP controllers
├── Core/           # Framework core (Application, Database, Router)
├── Modules/        # Feature modules (Auth, Users, Images, etc.)
├── Services/       # Business logic services
└── Views/          # PHP templates
public_html/        # Web root (Apache points here)
```

## Future Setup - Premium & Licensing

See `future-setup.md` for full details. Key decisions:

### Architecture

- **Phase 1 (Now):** GitHub Raw as permanent router, no server needed
- **Phase 2 (1K+ installs):** VPS with license server

### GitHub Router Pattern

App always checks GitHub first. GitHub tells app where real server is:

```
https://raw.githubusercontent.com/USERNAME/fwp/main/remote/status.json
```

Phase 1 response (no server):
```json
{"api_server": null, "latest_version": "1.0.0", "message": null}
```

Phase 2 response (server live):
```json
{"api_server": "https://api.DOMAIN.com", "latest_version": "1.2.0", ...}
```

### Silent Phone-Home System

- No errors shown to users
- No UI hints about the system
- App works normally if server unreachable
- Users don't know it exists
- All existing installs auto-connect when server goes live

### Files to Implement

- `remote/status.json` - GitHub router file
- `app/Services/UpdateChecker.php` - Silent phone-home
- `app/Services/LicenseService.php` - License placeholder (returns free for now)
- `app/Config/features.php` - Feature flags

### Data Collected (When Server Live)

On admin visit: domain, version, PHP version, license key

### Premium Features (Future)

- Advanced AI analysis
- Bulk upload
- API access
- White-label
- Priority support

## Phase 1 Checklist

- [ ] Create `remote/status.json` in repo
- [ ] Implement `UpdateChecker.php` (silent)
- [ ] Implement `LicenseService.php` (placeholder)
- [ ] Create `features.php` config
- [ ] Add check on admin dashboard load
- [ ] Installation wizard
