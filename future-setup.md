# FWP Future Setup - Premium & Licensing System

This document outlines the architecture decisions for premium features, licensing, and update system.

---

## Overview

**Model:** Open Core (public repo + premium features)

| Phase | Timeline | Infrastructure |
|-------|----------|----------------|
| Phase 1 | Now | GitHub Raw (free, no server) |
| Phase 2 | 1K-10K installs | VPS (Hostinger or similar) |

---

## Architecture

### GitHub as Permanent Router

The app always checks GitHub first. GitHub tells the app where the real server is (when it exists).

**GitHub URL (never changes):**
```
https://raw.githubusercontent.com/USERNAME/fwp/main/remote/status.json
```

**Phase 1 - No server yet:**
```json
{
    "api_server": null,
    "latest_version": "1.0.0",
    "message": null
}
```

**Phase 2 - Server live:**
```json
{
    "api_server": "https://api.YOURDOMAIN.com",
    "latest_version": "1.2.0",
    "message": {
        "type": "info",
        "text": "Premium features now available!",
        "link": "https://YOURDOMAIN.com/premium"
    }
}
```

### Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                         PHASE 1 (Now)                           │
└─────────────────────────────────────────────────────────────────┘

  Installed App                    GitHub Raw
  ┌──────────┐                     ┌─────────────────────────┐
  │  FWP     │ ─── GET ─────────►  │ remote/status.json      │
  │          │                     │ {                       │
  │          │ ◄── response ─────  │   "api_server": null,   │
  │          │                     │   "latest_version": ... │
  │ (works   │                     │ }                       │
  │  normally)│                    └─────────────────────────┘
  └──────────┘


┌─────────────────────────────────────────────────────────────────┐
│                    PHASE 2 (Server Live)                        │
└─────────────────────────────────────────────────────────────────┘

  Installed App                    GitHub Raw                Real Server
  ┌──────────┐                     ┌──────────────┐          ┌──────────────┐
  │  FWP     │ ─── GET ─────────►  │ status.json  │          │ License API  │
  │          │                     │ {api_server: │          │              │
  │          │ ◄── redirect ─────  │  "https://...│          │              │
  │          │                     │ }            │          │              │
  │          │                     └──────────────┘          │              │
  │          │                                               │              │
  │          │ ─── GET (domain, version, php) ────────────►  │              │
  │          │                                               │              │
  │          │ ◄── full response (updates, license, etc) ──  │              │
  │  Shows   │                                               │              │
  │  updates!│                                               │              │
  └──────────┘                                               └──────────────┘
```

---

## Silent Phone-Home System

**Requirements:**
- No errors shown to users
- No UI hints about the system
- No console logs
- App works normally if server unreachable
- Users don't know it exists

**Implementation:**
```php
class UpdateChecker {
    private string $router = 'https://raw.githubusercontent.com/USERNAME/fwp/main/remote/status.json';

    public function check(): ?array {
        try {
            $context = stream_context_create([
                'http' => ['timeout' => 2, 'ignore_errors' => true]
            ]);

            $config = @file_get_contents($this->router, false, $context);
            if (!$config) return null;

            $data = json_decode($config, true);
            if (!$data) return null;

            // If real server exists, call it
            if (!empty($data['api_server'])) {
                $serverUrl = $data['api_server'] . '/v1/status?' . http_build_query([
                    'domain' => $_SERVER['HTTP_HOST'] ?? '',
                    'version' => APP_VERSION,
                    'php' => PHP_VERSION,
                ]);
                $response = @file_get_contents($serverUrl, false, $context);
                return $response ? json_decode($response, true) : $data;
            }

            return $data;
        } catch (Throwable $e) {
            return null; // Silent fail
        }
    }
}
```

---

## Feature Flags & License Service

**Config file (app/Config/features.php):**
```php
return [
    'premium' => [
        'enabled' => false,  // Flip when ready
    ],
    'features' => [
        'advanced_ai' => false,
        'bulk_upload' => false,
        'api_access' => false,
        'white_label' => false,
        'priority_support' => false,
    ],
];
```

**License service placeholder (app/Services/LicenseService.php):**
```php
class LicenseService {
    public function isValid(): bool {
        // Phase 1: Always valid (free mode)
        // Phase 2: Call license server
        return true;
    }

    public function isPremium(): bool {
        // Phase 1: Everyone is free
        // Phase 2: Check license type
        return false;
    }

    public function getFeatures(): array {
        // Phase 1: Return empty
        // Phase 2: Return licensed features
        return [];
    }
}
```

---

## Data Collected When Server Goes Live

**On each admin visit, app sends:**
```php
[
    'domain' => 'example.com',
    'version' => '1.2.0',
    'php' => '8.4.0',
    'license_key' => 'xxx-xxx-xxx', // If premium
]
```

**Dashboard will show:**
- Total active installs (last 30 days)
- Domain list with versions
- Version distribution
- Outdated installs needing update
- Premium vs free breakdown

---

## Server API Endpoints (Phase 2)

| Endpoint | Purpose |
|----------|---------|
| `POST /v1/activate` | Activate license on domain |
| `POST /v1/verify` | Verify license validity |
| `GET /v1/status` | Check for updates + messages |
| `GET /v1/download` | Download update (licensed) |
| `POST /v1/heartbeat` | Periodic ping with stats |

---

## Server Response Format

```json
{
    "latest_version": "1.2.0",
    "update_available": true,
    "download_url": "https://api.DOMAIN.com/v1/download?token=xxx",
    "message": {
        "type": "info|warning|success",
        "text": "Message to show in admin",
        "link": "https://...",
        "dismissible": true
    },
    "license": {
        "valid": true,
        "plan": "premium",
        "expires": "2026-01-01",
        "features": ["advanced_ai", "bulk_upload"]
    }
}
```

---

## File Structure

**In public repo:**
```
fwp/
├── app/
│   ├── Config/
│   │   └── features.php         # Feature flags
│   ├── Services/
│   │   ├── LicenseService.php   # License placeholder
│   │   └── UpdateChecker.php    # Phone-home system
│   └── Modules/
│       └── Premium/             # Gated features (hidden)
├── remote/
│   └── status.json              # GitHub router file
└── future-setup.md              # This file
```

**On license server (Phase 2):**
```
license-server/
├── public/
│   └── index.php
├── app/
│   ├── Controllers/
│   │   ├── LicenseController.php
│   │   ├── StatusController.php
│   │   └── UpdateController.php
│   └── Models/
│       ├── License.php
│       └── Installation.php
├── admin/                        # Dashboard
└── storage/releases/             # Update files
```

---

## Database Tables (Phase 2)

```sql
-- Licenses
CREATE TABLE licenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    license_key VARCHAR(255) UNIQUE,
    email VARCHAR(255),
    plan ENUM('free', 'premium', 'enterprise'),
    domains_allowed INT DEFAULT 1,
    expires_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tracked Installations
CREATE TABLE installations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    license_id INT NULL,
    domain VARCHAR(255),
    ip VARCHAR(45),
    version VARCHAR(20),
    php_version VARCHAR(20),
    first_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_ping DATETIME DEFAULT CURRENT_TIMESTAMP,
    ping_count INT DEFAULT 1,
    FOREIGN KEY (license_id) REFERENCES licenses(id)
);

-- Releases
CREATE TABLE releases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    version VARCHAR(20),
    changelog TEXT,
    download_url VARCHAR(500),
    min_php_version VARCHAR(10),
    released_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

## Checklist

### Now (Phase 1)
- [ ] Create `remote/status.json` in repo
- [ ] Implement `UpdateChecker.php` (silent)
- [ ] Implement `LicenseService.php` (placeholder)
- [ ] Create `features.php` config
- [ ] Add check on admin dashboard load
- [ ] Installation wizard

### Later (Phase 2 - 1K+ installs)
- [ ] Set up VPS (Hostinger recommended)
- [ ] Deploy license server
- [ ] Create admin dashboard
- [ ] Update `remote/status.json` with api_server URL
- [ ] Announce premium features

---

## Notes

- **Domain:** TBD - will update `remote/status.json` when decided
- **Pricing:** TBD
- **Premium features:** TBD (AI analysis, bulk upload, API access, white-label, priority support)
- **Tracking disclosure:** Add to Terms of Service when server goes live
