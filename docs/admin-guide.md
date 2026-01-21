# Admin Guide

Complete guide to managing your Pixly.

## Accessing Admin Panel

Navigate to `/admin` and log in with an admin account.

## Dashboard

The dashboard provides an overview of your site:

- **Statistics** - Total images, users, views
- **Recent Activity** - Latest uploads and registrations
- **Quick Actions** - Common tasks

## Content Management

### Images

**Managing Images**

1. Go to Admin > Images
2. View all images with filters:
   - Status (pending, approved, rejected)
   - Category
   - Date range

**Image Actions**
- **Edit** - Modify title, description, tags, category
- **Approve** - Make image public
- **Reject** - Remove from public view
- **Delete** - Permanently remove
- **Feature** - Highlight on homepage

**Bulk Actions**
- Select multiple images
- Apply action to all selected

### Upload

**Admin Upload**

1. Go to Admin > Upload
2. Drag & drop or select files
3. Images are auto-approved for admins

**AI Processing**
- Enable AI analysis during upload
- Automatically generates:
  - Title
  - Description
  - Tags
  - Category suggestion

### Categories

**Managing Categories**

1. Go to Admin > Categories
2. Create, edit, or delete categories

**Category Fields**
- Name
- Slug (URL-friendly)
- Description
- Parent category (for hierarchy)
- Featured image

### Tags

**Managing Tags**

1. Go to Admin > Tags
2. View tag usage statistics
3. Merge or delete tags

**Tag Actions**
- Edit tag name/slug
- Merge duplicate tags
- Delete unused tags

### Moderation

**Moderation Queue**

1. Go to Admin > Moderation
2. Review pending images
3. Approve or reject

**Moderation Tips**
- Check image quality
- Verify appropriate content
- Review AI-generated metadata
- Edit metadata if needed

## User Management

### Users

**Managing Users**

1. Go to Admin > Users
2. View all registered users

**User Actions**
- **Edit** - Change details, role
- **Activate/Deactivate** - Enable/disable account
- **Delete** - Remove user and content
- **Login As** - Impersonate user (superadmin only)

**User Roles**

| Role | Capabilities |
|------|--------------|
| User | Browse, favorite, like |
| Contributor | + Upload images |
| Moderator | + Moderate content |
| Admin | + Manage users, settings |
| Superadmin | Full access |

### Contributors

**Managing Contributor Requests**

1. Go to Admin > Contributors
2. Review pending requests

**Request Actions**
- **Approve** - Grant contributor role
- **Reject** - Deny with optional note

**Contributor Flow**
1. User submits request with reason
2. Admin reviews request
3. Admin approves or rejects
4. User is notified

## Blog System

### Posts

**Managing Posts**

1. Go to Admin > Blog > Posts
2. View all blog posts

**Post Fields**
- Title
- Content (rich text editor)
- Excerpt
- Featured image
- Category
- Tags
- Status (draft, published)
- Publish date

### Blog Categories

1. Go to Admin > Blog > Categories
2. Manage blog-specific categories

### Comments

1. Go to Admin > Blog > Comments
2. Moderate user comments

**Comment Actions**
- Approve
- Mark as spam
- Delete

## Marketing

### Ad Placements

**Managing Placements**

1. Go to Admin > Marketing > Placements
2. Configure where ads appear

**Placement Locations**
- Header
- Footer
- Sidebar
- In-content
- Gallery (between images)

### Ads

**Creating Ads**

1. Go to Admin > Marketing > Ads
2. Click "Create Ad"
3. Configure:
   - Name
   - Placement
   - Ad type (HTML, image, AdSense, JuicyAds)
   - Content
   - Targeting (device, dates)

### Popups

**Creating Popups**

1. Go to Admin > Marketing > Popups
2. Configure:
   - Content (HTML)
   - Trigger (page load, scroll, exit intent)
   - Frequency
   - Targeting

### Announcements

**Creating Announcement Bars**

1. Go to Admin > Marketing > Announcements
2. Configure:
   - Message
   - Link (optional)
   - Colors
   - Position (top/bottom)
   - Dismissible option

### Newsletter

**Managing Subscribers**

1. Go to Admin > Marketing > Newsletter
2. View subscribers by status:
   - Pending (unconfirmed)
   - Confirmed
   - Unsubscribed

**Export Subscribers**
- Download CSV of confirmed subscribers

## Analytics

**Viewing Analytics**

1. Go to Admin > Analytics
2. View:
   - Page views
   - Popular images
   - Search terms
   - Traffic sources
   - User activity

## Settings

### General

- Site name, description, URL
- Timezone, date format

### Features

Toggle features on/off:
- Premium subscriptions
- User registration
- Contributor system
- Appreciation system

### Branding

- Logo upload
- Color scheme
- Dark mode toggle

### Site Mode

Configure adult content features:
- Age gate
- NSFW blur
- Quick exit button
- Disclaimers

### Images

- Upload limits
- Allowed file types
- Auto-approve setting
- Watermark

### Users

- Registration settings
- Email verification
- Default user role

### Comments

- Enable/disable comments
- Moderation settings

### SEO

- Meta tags
- Sitemap
- Analytics integration

### API Keys

Configure external services:
- DeepSeek AI
- Unsplash
- Pexels

### Ads

Global ad settings:
- Enable/disable ads
- Ad frequency
- Network settings

## Pages

**Managing Static Pages**

1. Go to Admin > Pages
2. Create/edit pages like:
   - About
   - Contact
   - Privacy Policy
   - Terms of Service

## Best Practices

### Daily Tasks
- Check moderation queue
- Review contributor requests
- Monitor error logs

### Weekly Tasks
- Review analytics
- Check for spam users
- Update content

### Monthly Tasks
- Review settings
- Check for updates
- Backup database
- Clean up unused tags/categories

## Troubleshooting

### Common Issues

**Images not uploading**
- Check file size limits
- Verify folder permissions
- Check PHP settings

**AI not working**
- Verify API key
- Check API limits
- Review error logs

**Ads not showing**
- Check placement is active
- Verify ad is assigned to placement
- Check targeting settings
