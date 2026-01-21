---
name: fwp-image-gallery
description: >
  A SEO-focused image gallery platform built with vanilla PHP 8.4 + MySQL,
  featuring AI-powered metadata generation, trend prediction, modular architecture,
  user management, admin panel, and LLM integration. This document defines objectives,
  architecture, schema, routes, and implementation plan.
---

# FWP - SEO-Focused Image Gallery

You operate as a **senior architect + tech lead** for a vanilla PHP-based web app using:
- PHP 8.4 (Vanilla, no framework)
- MySQL 8.0+
- Tailwind CSS (SaaS-style UI)
- Modular architecture:
  - Feature/bounded-context based modules inside a single PHP app
  - Public, admin, and API served via separate route groups

Your goals:
- Design a **universal base** that can be extended for various gallery types.
- Ensure the app is:
  - SEO-ready (public URLs, schema markup, sitemaps)
  - LLM-ready (DeepSeek API, content/SEO helpers)
  - Secure by default (PHP + MySQL best practices)
  - Performance-focused (fast, light, shared-hosting compatible)
  - Equipped with a robust admin panel and user management.

---

## Project Overview & Goals

### Project Identity
- **Name**: FWP Image Gallery
- **One-line description**: AI-powered, SEO-optimized image gallery with trend prediction and smart monetization.
- **Primary purpose**: Content platform for visual media discovery and sharing.

### Target Users
1. **Visitors**: Browse, search, and discover images
2. **Registered Users**: Save favorites, comment, create collections
3. **Administrators**: Upload content, manage metadata, moderate, analyze trends

### Core User Tasks
1. Discover trending and relevant images quickly
2. Save and organize favorite images
3. Upload and publish images with auto-generated SEO metadata

### Platforms (v1)
- [x] Public marketing/gallery site
- [x] Web app (authenticated area for favorites/comments)
- [x] Admin panel (content management, AI processing, analytics)
- [x] Internal API (AJAX endpoints for frontend)
- [ ] Public API (future consideration)

### Hard Constraints
- **Tech Stack**: Vanilla PHP 8.4, MySQL, no frameworks
- **Hosting**: Shared hosting compatible (no Redis, no queue workers)
- **Budget**: Minimal - use free API tiers where possible
- **Must-have v1**: Gallery, search, AI metadata, admin uploads, basic monetization

---

## Domain & Modules

### Module Architecture

The application follows a **modular monolith** pattern with bounded contexts:

```
app/Modules/
├── Auth/           # Authentication & sessions
├── Users/          # User profiles, favorites, preferences
├── Images/         # Core image management & processing
├── Categories/     # Category hierarchy & management
├── Tags/           # Tag system & trending
├── Comments/       # Comment system & moderation
├── Search/         # Search functionality
├── AI/             # LLM integration (DeepSeek)
├── Trends/         # Trend prediction & analysis
├── SEO/            # Sitemaps, schema, meta tags
├── Ads/            # Ad placements & monetization
├── Analytics/      # Page views, statistics
├── Admin/          # Admin panel & settings
└── External/       # Third-party APIs (Unsplash, Pexels)
```

### Module Responsibilities

| Module | Responsibilities |
|--------|------------------|
| **Auth** | Login, logout, registration, password reset, session management, CSRF |
| **Users** | User profiles, roles (user/moderator/admin), preferences, favorites list |
| **Images** | Upload, processing, storage, CRUD, metadata, moderation status |
| **Categories** | Hierarchical categories, SEO fields, image associations |
| **Tags** | Tag CRUD, trend scores, image associations, popular/trending lists |
| **Comments** | Nested comments, moderation, spam detection |
| **Search** | Full-text search, filters, suggestions, search analytics |
| **AI** | DeepSeek integration, metadata generation, content moderation, queue processing |
| **Trends** | Google Trends integration, Pinterest trends, keyword analysis, prioritization |
| **SEO** | Sitemap generation, Schema.org markup, meta tags, robots.txt |
| **Ads** | JuicyAds integration, placement management, impression tracking |
| **Analytics** | Page views, popular content, traffic sources, admin dashboard stats |
| **Admin** | Dashboard, settings, user management, content management, bulk operations |
| **External** | Unsplash/Pexels API integration, licensed image imports |

---

## User Roles, Auth & Security

### Authentication Methods
- [x] Email + password (primary)
- [ ] Google OAuth (future)
- [ ] Magic link login (future)

### Roles & Permissions

| Role | Permissions |
|------|-------------|
| **Guest** | Browse, search, view images |
| **User** | + Save favorites, comment, profile |
| **Moderator** | + Approve/reject content, manage comments |
| **Admin** | + Full access: uploads, AI, settings, users |
| **Super Admin** | + System settings, dangerous operations |

### Password & Session Rules
- **Hashing**: Argon2id (PHP 8.4 default)
- **Minimum length**: 12 characters
- **Session timeout**: 2 hours idle, 30 days with remember-me
- **Session storage**: Database (not file-based)
- **Failed login limit**: 5 attempts, then 15-minute lockout

### Security Defaults (Non-negotiable)

**Input/Output Security**
- CSRF tokens on all state-changing requests
- XSS prevention via `htmlspecialchars()` on all output
- Input validation for all user inputs (whitelist approach)
- Prepared statements / PDO for all database queries

**Transport Security**
- HTTPS enforced (redirect HTTP)
- Secure, HttpOnly, SameSite=Lax cookies
- HSTS header enabled

**Database Security**
- Dedicated DB user with minimal privileges
- No root user in application
- Remote access disabled

**File Security**
- Uploads validated via magic bytes (not extension)
- Files renamed to UUID
- Uploads stored outside web root where possible
- Image reprocessing strips EXIF (privacy)

**Headers**
```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: [configured per page type]
Permissions-Policy: geolocation=(), microphone=(), camera=()
```

### Additional Security (Optional)
- [ ] 2FA via TOTP (future)
- [ ] Admin IP allowlist (future)
- [ ] Rate limiting on API endpoints

---

## SEO & Public Site

### SEO Focus Areas
- [x] Image gallery pages (primary)
- [x] Category landing pages
- [x] Tag landing pages
- [x] Single image pages
- [ ] Blog/content hub (future)

### URL Structure (Human-readable, SEO-friendly)

```
/                              Homepage (trending, featured)
/gallery                       All images listing
/gallery/page/2                Paginated gallery
/image/sunset-mountain-lake    Single image (slug-based)
/category/nature               Category page
/category/nature/page/2        Paginated category
/tag/landscape                 Tag page
/tag/landscape/page/2          Paginated tag
/search?q=mountains            Search results
/trending                      Trending images
```

### SEO Features

**Meta Tags**
- Dynamic `<title>` per page (max 60 chars)
- Dynamic `<meta description>` (max 160 chars)
- Canonical URLs on all pages
- Open Graph tags for social sharing
- Twitter Card tags

**Structured Data (Schema.org)**
- `ImageObject` on single image pages
- `ImageGallery` on listing pages
- `WebSite` with SearchAction sitewide
- `BreadcrumbList` for navigation

**Technical SEO**
- XML sitemaps (auto-generated daily)
  - `sitemap-images-{n}.xml` (max 50K URLs each)
  - `sitemap-categories.xml`
  - `sitemap-tags.xml`
- `robots.txt` with sitemap reference
- Clean 404 pages
- Proper HTTP status codes

### Performance Posture

**Server-side Rendering**
- All public pages server-rendered (no SPA)
- Minimal JavaScript for interactions
- Critical CSS inlined

**Image Optimization**
- WebP format with JPEG fallback
- Multiple sizes (thumbnail, medium, large)
- `<picture>` element for format selection
- `srcset` for responsive images
- Native `loading="lazy"` attribute
- Dominant color placeholders

**Caching**
- Browser cache: 1 year for versioned assets
- Page cache: 5-60 minutes (file-based)
- Fragment cache for widgets
- ETags for validation

**Compression**
- Gzip/Brotli for text responses
- Minified CSS/JS in production

---

## LLM Readiness & `llms.txt`

### LLM Integration Strategy

**Use Cases**
- [x] Image metadata generation (title, description, alt, tags)
- [x] Content moderation (safety scoring)
- [x] Category suggestion
- [ ] Search query enhancement (future)
- [ ] Chat assistant (future)

**Provider Strategy**
- Primary: DeepSeek API (free tier, vision capabilities)
- Architecture: Provider-agnostic abstraction layer
- Fallback: Manual metadata entry if API unavailable

**Integration Architecture**
```
app/Services/AI/
├── LLMServiceInterface.php    # Provider-agnostic interface
├── DeepSeekService.php        # DeepSeek implementation
├── MetadataGenerator.php      # High-level metadata service
├── ContentModerator.php       # Safety/moderation service
└── QueueProcessor.php         # Batch processing
```

### Data Safety Rules (Enforced)

**Never Send to LLM**
- User passwords or hashes
- Session tokens or API keys
- Raw email addresses
- Payment information
- Private user data without consent

**Safe to Send**
- Image binary data (for vision analysis)
- Public image metadata
- Anonymized analytics
- Category/tag lists

**Logging**
- Log all LLM requests (without sensitive data)
- Track token usage and costs
- Monitor for anomalies

### `llms.txt` Convention

Create `public_html/llms.txt` documenting LLM usage policy:

```
# LLM Usage Policy for [Site Name]

## What We Use AI For
- Generating image titles, descriptions, and alt text
- Suggesting relevant tags and categories
- Content moderation and safety scoring

## Data Sent to AI Services
- Image visual content (for analysis)
- Existing public metadata
- No personal user data is ever sent

## AI Provider
- DeepSeek API (https://deepseek.com)

## User Controls
- All AI-generated content is reviewed before publishing
- Users can report incorrect AI-generated metadata

## Contact
- Questions: [contact email]
- Last updated: [date]
```

**Footer Link**: Always include `llms.txt` link in public footer alongside Privacy Policy and Terms.

---

## Admin Panel & Logging

### Admin Panel Scope (v1)

**Dashboard**
- Key metrics (total images, users, views today)
- Recent uploads pending moderation
- AI processing queue status
- Trending keywords overview

**Image Management**
- List with filters (status, category, date, source)
- Single and bulk upload
- Edit metadata (override AI suggestions)
- Bulk actions (publish, archive, delete)
- AI processing trigger

**Category & Tag Management**
- CRUD for categories (hierarchical)
- CRUD for tags
- Merge duplicate tags
- View tag trend scores

**User Management**
- User list with search/filters
- View user details
- Change roles
- Suspend/ban users
- Impersonate user (for debugging)

**Moderation Queue**
- Images pending review
- Flagged content
- Approve/reject with reason

**AI Dashboard**
- Processing queue status
- API usage statistics
- Cost tracking
- Trigger batch processing

**Trends Dashboard**
- Current trending keywords
- Trend sources (Google, Pinterest, internal)
- Force refresh trends
- Keyword suggestions for uploads

**Analytics**
- Traffic overview
- Popular images
- Search terms
- Referrer analysis

**Ad Management**
- Configure ad placements
- Enable/disable ads
- Track impressions (manual or API)

**Settings**
- Site settings (name, description, logo)
- SEO defaults
- API key configuration
- Cache management

### Logging Strategy

**Auth Logs** (`auth_logs` table)
- Successful logins (user, IP, user agent, timestamp)
- Failed login attempts
- Password reset requests
- Logout events

**Admin Action Logs** (`admin_logs` table)
- Who changed what, when
- Before/after values for edits
- Bulk action summaries

**API Logs** (`api_logs` table)
- External API calls (DeepSeek, Unsplash, etc.)
- Request/response summaries
- Token usage, costs
- Errors

**Request Logs** (file-based)
- Critical endpoint access
- Error stack traces
- Performance slow queries

---

## Tailwind & Frontend Structure

### Setup Strategy

**Phase 1 (v1)**: Tailwind via CDN
```html
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          primary: {...},
          secondary: {...},
        }
      }
    }
  }
</script>
```

**Phase 2 (future)**: Build pipeline with PostCSS

### Design Tokens

**Colors**
```javascript
colors: {
  primary: {
    50: '#f0f9ff',
    500: '#0ea5e9',
    600: '#0284c7',
    700: '#0369a1',
  },
  secondary: {
    500: '#8b5cf6',
    600: '#7c3aed',
  },
  neutral: {
    50: '#fafafa',
    100: '#f5f5f5',
    200: '#e5e5e5',
    800: '#262626',
    900: '#171717',
  },
  success: '#22c55e',
  warning: '#f59e0b',
  error: '#ef4444',
}
```

**Spacing**: Default Tailwind scale
**Border Radius**: `rounded-lg` (8px) default
**Shadows**: Subtle, SaaS-style elevation

### UI Aesthetic
- Clean, minimal SaaS-style (Vercel/Linear inspired)
- Focus on content (images)
- Dark mode support (future)
- Mobile-first responsive

### Component Library (Base)

```
views/partials/components/
├── button.php          # Primary, secondary, ghost variants
├── input.php           # Text, email, password, textarea
├── select.php          # Dropdown select
├── checkbox.php        # Checkbox and radio
├── card.php            # Content card
├── modal.php           # Modal dialog
├── table.php           # Data table with sorting
├── pagination.php      # Page navigation
├── alert.php           # Success, error, warning, info
├── badge.php           # Status badges
├── dropdown.php        # Dropdown menu
├── nav.php             # Navigation components
└── image-card.php      # Gallery image card
```

### Layout Shells

**Public Layout** (`views/layouts/public.php`)
- Header with logo, nav, search
- Main content area
- Footer with links, llms.txt, ads

**App Layout** (`views/layouts/app.php`)
- Same as public + user menu
- Favorites indicator

**Admin Layout** (`views/layouts/admin.php`)
- Sidebar navigation
- Top bar with user menu
- Main content area
- Breadcrumbs

---

## Database Schema & Migrations

### Core Tables

```sql
-- Users & Auth
users                    -- User accounts
sessions                 -- Database sessions
password_resets          -- Reset tokens

-- Content
images                   -- Main content table
categories               -- Hierarchical categories
tags                     -- Keywords with trends
image_categories         -- M:N pivot
image_tags               -- M:N pivot with relevance

-- Engagement
favorites                -- User saved images
comments                 -- Nested comments

-- AI & Trends
ai_processing_queue      -- Batch AI jobs
trending_keywords        -- Cached trends

-- Monetization & Analytics
ad_placements            -- Ad configuration
page_views               -- Traffic tracking

-- System
settings                 -- App configuration
auth_logs                -- Auth events
admin_logs               -- Admin actions
api_logs                 -- External API calls
```

### Key Relationships

```
users (1) ----< (M) images
users (1) ----< (M) comments
users (1) ----< (M) favorites

images (M) >----< (M) categories
images (M) >----< (M) tags
images (1) ----< (M) comments
images (1) ----< (M) favorites

categories (1) ----< (M) categories [self-ref]
comments (1) ----< (M) comments [nested]
```

---

## Routes & Controller Map

### Route Groups

**Public Routes** (`routes/web.php`)
| Method | URL | Controller | Middleware |
|--------|-----|------------|------------|
| GET | `/` | `HomeController@index` | cache |
| GET | `/gallery` | `GalleryController@index` | cache |
| GET | `/gallery/page/{n}` | `GalleryController@index` | cache |
| GET | `/image/{slug}` | `ImageController@show` | cache, analytics |
| GET | `/category/{slug}` | `CategoryController@show` | cache |
| GET | `/tag/{slug}` | `TagController@show` | cache |
| GET | `/search` | `SearchController@index` | - |
| GET | `/trending` | `TrendingController@index` | cache |

**Auth Routes** (`routes/auth.php`)
| Method | URL | Controller | Middleware |
|--------|-----|------------|------------|
| GET | `/login` | `LoginController@show` | guest |
| POST | `/login` | `LoginController@login` | guest, csrf, ratelimit |
| GET | `/register` | `RegisterController@show` | guest |
| POST | `/register` | `RegisterController@register` | guest, csrf |
| GET | `/logout` | `LogoutController@logout` | auth |
| GET | `/forgot-password` | `PasswordController@forgot` | guest |
| POST | `/forgot-password` | `PasswordController@sendReset` | guest, csrf |
| GET | `/reset-password/{token}` | `PasswordController@reset` | guest |
| POST | `/reset-password` | `PasswordController@update` | guest, csrf |

**User Routes** (`routes/user.php`)
| Method | URL | Controller | Middleware |
|--------|-----|------------|------------|
| GET | `/profile` | `ProfileController@show` | auth |
| GET | `/favorites` | `FavoriteController@index` | auth |

**API Routes** (`routes/api.php`)
| Method | URL | Controller | Middleware |
|--------|-----|------------|------------|
| GET | `/api/images` | `ImageApiController@index` | - |
| GET | `/api/images/{slug}` | `ImageApiController@show` | - |
| POST | `/api/favorites/{id}` | `FavoriteApiController@store` | auth, csrf |
| DELETE | `/api/favorites/{id}` | `FavoriteApiController@destroy` | auth, csrf |
| POST | `/api/comments` | `CommentApiController@store` | auth, csrf |
| GET | `/api/search` | `SearchApiController@index` | - |

**Admin Routes** (`routes/admin.php`)
| Method | URL | Controller | Middleware |
|--------|-----|------------|------------|
| GET | `/admin` | `DashboardController@index` | auth, admin |
| GET | `/admin/images` | `ImageController@index` | auth, admin |
| GET | `/admin/images/upload` | `UploadController@show` | auth, admin |
| POST | `/admin/images` | `ImageController@store` | auth, admin, csrf |
| PUT | `/admin/images/{id}` | `ImageController@update` | auth, admin, csrf |
| DELETE | `/admin/images/{id}` | `ImageController@destroy` | auth, admin, csrf |
| POST | `/admin/ai/process` | `AIController@process` | auth, admin, csrf |
| GET | `/admin/moderation` | `ModerationController@index` | auth, moderator |
| GET | `/admin/trends` | `TrendsController@index` | auth, admin |
| GET | `/admin/analytics` | `AnalyticsController@index` | auth, admin |
| GET | `/admin/settings` | `SettingsController@index` | auth, superadmin |

---

## Implementation Phases

### Phase 1: Foundation (Core Infrastructure)
- [ ] Directory structure setup
- [ ] PSR-4 autoloader
- [ ] Core classes (Application, Router, Request, Response)
- [ ] Database class (PDO wrapper)
- [ ] Base Controller, Model, View
- [ ] Configuration system
- [ ] Error handling

### Phase 2: Auth & Users
- [ ] Database schema (users, sessions)
- [ ] Registration with email verification
- [ ] Login/logout
- [ ] Password reset flow
- [ ] Session management
- [ ] CSRF middleware
- [ ] Role-based access control

### Phase 3: Image Management
- [ ] Database schema (images, categories, tags)
- [ ] Upload service (validation, storage)
- [ ] Image processor (resize, WebP, thumbnails)
- [ ] Image model with CRUD
- [ ] Category/Tag models
- [ ] Admin image management UI

### Phase 4: AI Integration
- [ ] DeepSeek service class
- [ ] Metadata generator
- [ ] Content moderator
- [ ] Processing queue
- [ ] Admin AI dashboard
- [ ] Cron job for queue processing

### Phase 5: Frontend Gallery
- [ ] Public layouts (Tailwind)
- [ ] Homepage
- [ ] Gallery listing with pagination
- [ ] Single image page with prev/next
- [ ] Category/tag pages
- [ ] Search functionality
- [ ] Lazy loading

### Phase 6: User Features
- [ ] User profiles
- [ ] Favorites system
- [ ] Comment system
- [ ] Comment moderation

### Phase 7: Trends & SEO
- [ ] Google Trends integration
- [ ] Trend analyzer
- [ ] Schema.org markup
- [ ] Sitemap generator
- [ ] Meta tag builder
- [ ] `llms.txt` file

### Phase 8: Monetization & Polish
- [ ] JuicyAds integration
- [ ] Ad placement management
- [ ] Analytics dashboard
- [ ] Caching layer
- [ ] Performance optimization
- [ ] Security audit

---

## Security & Performance Checklist

### Security (Must Implement)
- [ ] Argon2id password hashing
- [ ] CSRF tokens on all forms
- [ ] Prepared statements everywhere
- [ ] Input validation (whitelist)
- [ ] Output escaping (XSS prevention)
- [ ] Secure session configuration
- [ ] HTTPS enforcement
- [ ] Security headers
- [ ] File upload validation (magic bytes)
- [ ] Rate limiting on auth endpoints
- [ ] SQL injection prevention
- [ ] Role-based access control

### Performance (Must Implement)
- [ ] Image optimization pipeline
- [ ] WebP with fallback
- [ ] Lazy loading images
- [ ] Page caching (file-based)
- [ ] Fragment caching
- [ ] Database query optimization
- [ ] Proper indexing
- [ ] Asset versioning
- [ ] Gzip compression
- [ ] Critical CSS inlining

---

## External Dependencies

### APIs (Free Tiers)
| Service | Purpose | Limit |
|---------|---------|-------|
| DeepSeek | AI metadata/moderation | Free tier |
| Unsplash | Stock photo imports | 50 req/hour |
| Pexels | Stock photo imports | 200 req/hour |
| Google Trends | Keyword trends | Unofficial |

### Ad Networks
| Network | Type | Min Payout |
|---------|------|------------|
| JuicyAds | Primary | $25 |
| ExoClick | Secondary | $20 |

---

## Guardrails

- Do not skip the planning phase; always understand requirements before coding.
- Do not weaken security rules unless explicitly requested with risk acknowledgment.
- Keep architecture modular; avoid mixing concerns across modules.
- Prefer simple, robust defaults over premature complexity.
- Always validate and sanitize user input.
- Never expose sensitive data in logs or error messages.
- Test all critical paths before deployment.

---

## Changelog

### v0.1.0 (Planning)
- Initial project planning
- Architecture design
- Database schema design
- Module definitions
- Security baseline established
