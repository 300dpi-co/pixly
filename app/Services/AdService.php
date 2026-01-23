<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AdPlacement;
use App\Models\Ad;
use App\Models\PopupAd;
use App\Models\AnnouncementBar;
use App\Models\MarketingSetting;

/**
 * Ad Service
 *
 * Handles ad placement, popups, announcements, and rendering.
 */
class AdService
{
    private bool $enabled;
    private ?array $settings = null;
    private ?array $placements = null;

    public function __construct()
    {
        $this->loadSettings();
    }

    /**
     * Load marketing settings
     */
    private function loadSettings(): void
    {
        $this->settings = MarketingSetting::getAll();
        $this->enabled = ($this->settings['ads_enabled'] ?? '1') === '1';

        // Check if ads should be hidden for logged-in users
        if ($this->enabled && ($this->settings['hide_ads_for_users'] ?? '0') === '1') {
            if (isset($_SESSION['user_id'])) {
                $this->enabled = false;
            }
        }

        // Check if current page is blocked
        if ($this->enabled) {
            $blockedPages = $this->settings['blocked_pages'] ?? '';
            if ($this->isCurrentPageBlocked($blockedPages)) {
                $this->enabled = false;
            }
        }
    }

    /**
     * Check if current page is blocked from showing ads
     */
    private function isCurrentPageBlocked(string $blockedPages): bool
    {
        if (empty($blockedPages)) {
            return false;
        }

        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $patterns = array_filter(array_map('trim', explode("\n", $blockedPages)));

        foreach ($patterns as $pattern) {
            // Convert wildcard pattern to regex
            $regex = '#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '$#';
            if (preg_match($regex, $currentPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if ads are enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get ad code for a placement
     */
    public function getAd(string $placementSlug): string
    {
        if (!$this->enabled) {
            return '';
        }

        // Get placement
        $placement = AdPlacement::findBySlug($placementSlug);
        if (!$placement || !$placement['is_active']) {
            return '';
        }

        // Get active ads for this placement
        try {
            $ads = Ad::getActiveForPlacement((int) $placement['id']);
        } catch (\Exception $e) {
            $ads = [];
        }

        if (empty($ads)) {
            return $this->getPlaceholder($placementSlug, $placement);
        }

        // Pick random ad (weighted would be better in production)
        $ad = $ads[array_rand($ads)];
        $this->logImpression((int) $ad['id']);

        return $this->renderAdContent($ad);
    }

    /**
     * Render ad content based on type
     */
    private function renderAdContent(array $ad): string
    {
        switch ($ad['ad_type'] ?? 'custom_html') {
            case 'image':
                $html = sprintf(
                    '<a href="%s" target="_blank" rel="noopener"><img src="%s" alt="%s" loading="lazy"></a>',
                    htmlspecialchars($ad['destination_url'] ?? '#'),
                    htmlspecialchars($ad['image_path'] ?? ''),
                    htmlspecialchars($ad['name'] ?? '')
                );
                return $html;

            case 'adsense':
                return $ad['content'] ?? '';

            case 'juicyads':
                return $ad['content'] ?? '';

            case 'custom_html':
            default:
                return $ad['content'] ?? '';
        }
    }

    /**
     * Render ad HTML with container
     */
    public function render(string $placement): string
    {
        $adCode = $this->getAd($placement);

        if (empty($adCode)) {
            return '';
        }

        $lazyLoad = ($this->settings['lazy_load_ads'] ?? '1') === '1';
        $lazyAttr = $lazyLoad ? ' data-lazy-ad="true"' : '';

        return sprintf(
            '<div class="ad-container ad-%s"%s data-placement="%s">%s</div>',
            htmlspecialchars($placement),
            $lazyAttr,
            htmlspecialchars($placement),
            $adCode
        );
    }

    /**
     * Should show ad between images
     */
    public function shouldShowBetweenImages(int $index): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $interval = (int) ($this->settings['images_between_ads'] ?? 8);
        if ($interval <= 0) {
            return false;
        }

        return ($index + 1) % $interval === 0;
    }

    /**
     * Get active popups for current page
     */
    public function getActivePopups(): array
    {
        if (!$this->enabled) {
            return [];
        }

        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $popups = PopupAd::getActive();

        return array_filter($popups, function ($popup) use ($currentPath) {
            // Check page exclusions
            $excludes = json_decode($popup['pages_exclude'] ?? '[]', true) ?: [];
            foreach ($excludes as $pattern) {
                $regex = '#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '$#';
                if (preg_match($regex, $currentPath)) {
                    return false;
                }
            }

            // Check mobile visibility
            if (!$popup['show_on_mobile'] && $this->isMobile()) {
                return false;
            }

            // Check if already shown based on frequency
            $cookieName = 'popup_' . $popup['id'];
            if (isset($_COOKIE[$cookieName])) {
                return false;
            }

            return true;
        });
    }

    /**
     * Get active announcement bar
     */
    public function getActiveAnnouncement(): ?array
    {
        $announcements = AnnouncementBar::getActive();

        if (empty($announcements)) {
            return null;
        }

        // Get the first (highest priority) active announcement
        $announcement = $announcements[0];

        // Check if user dismissed it
        $cookieName = 'dismissed_announcement_' . $announcement['id'];
        if (isset($_COOKIE[$cookieName])) {
            return null;
        }

        return $announcement;
    }

    /**
     * Render announcement bar HTML
     */
    public function renderAnnouncement(): string
    {
        $announcement = $this->getActiveAnnouncement();

        if (!$announcement) {
            return '';
        }

        $stickyClass = $announcement['is_sticky'] ? 'sticky top-0 z-50' : '';
        $dismissBtn = '';

        if ($announcement['is_dismissible']) {
            $dismissBtn = sprintf(
                '<button onclick="dismissAnnouncement(%d, %d)" class="ml-4 opacity-70 hover:opacity-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>',
                $announcement['id'],
                $announcement['cookie_days']
            );
        }

        $link = '';
        if (!empty($announcement['link_url']) && !empty($announcement['link_text'])) {
            $link = sprintf(
                '<a href="%s" class="ml-2 underline hover:no-underline">%s</a>',
                htmlspecialchars($announcement['link_url']),
                htmlspecialchars($announcement['link_text'])
            );
        }

        return sprintf(
            '<div id="announcement-%d" class="announcement-bar %s p-3 text-center text-sm" style="background-color:%s;color:%s">
                <span>%s</span>%s%s
            </div>',
            $announcement['id'],
            $stickyClass,
            htmlspecialchars($announcement['bg_color']),
            htmlspecialchars($announcement['text_color']),
            htmlspecialchars($announcement['message']),
            $link,
            $dismissBtn
        );
    }

    /**
     * Render popups JavaScript
     */
    public function renderPopupsScript(): string
    {
        $popups = $this->getActivePopups();

        if (empty($popups)) {
            return '';
        }

        $popupsJson = json_encode(array_map(function ($p) {
            return [
                'id' => $p['id'],
                'content' => $p['content'],
                'trigger_type' => $p['trigger_type'],
                'trigger_delay' => (int) $p['trigger_delay'],
                'trigger_scroll_percent' => (int) $p['trigger_scroll_percent'],
                'frequency' => $p['frequency'],
                'position' => $p['position'],
                'animation' => $p['animation'],
                'width' => $p['width'],
                'overlay_opacity' => (int) $p['overlay_opacity'],
                'cookie_days' => (int) $p['cookie_days'],
            ];
        }, $popups));

        return sprintf('<script>window.activePopups = %s;</script>', $popupsJson);
    }

    /**
     * Get tracking codes for head
     */
    public function getHeadTrackingCodes(): string
    {
        $codes = [];

        // Google Analytics
        $gaId = $this->settings['google_analytics_id'] ?? '';
        if (!empty($gaId)) {
            $codes[] = sprintf(
                '<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=%s"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag(\'js\', new Date());

  gtag(\'config\', \'%s\');
</script>',
                htmlspecialchars($gaId),
                htmlspecialchars($gaId)
            );
        }

        // Google Tag Manager
        $gtmId = $this->settings['gtm_id'] ?? '';
        if (!empty($gtmId)) {
            $codes[] = sprintf(
                '<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({"gtm.start":
                new Date().getTime(),event:"gtm.js"});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!="dataLayer"?"&l="+l:"";j.async=true;j.src=
                "https://www.googletagmanager.com/gtm.js?id="+i+dl;f.parentNode.insertBefore(j,f);
                })(window,document,"script","dataLayer","%s");</script>',
                htmlspecialchars($gtmId)
            );
        }

        // Facebook Pixel
        $fbPixel = $this->settings['facebook_pixel_id'] ?? '';
        if (!empty($fbPixel)) {
            $codes[] = sprintf(
                '<script>
                !function(f,b,e,v,n,t,s)
                {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version="2.0";
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,"script",
                "https://connect.facebook.net/en_US/fbevents.js");
                fbq("init", "%s");
                fbq("track", "PageView");
                </script>
                <noscript><img height="1" width="1" style="display:none"
                src="https://www.facebook.com/tr?id=%s&ev=PageView&noscript=1"/></noscript>',
                htmlspecialchars($fbPixel),
                htmlspecialchars($fbPixel)
            );
        }

        // Custom head scripts
        $customHead = $this->settings['custom_head_scripts'] ?? '';
        if (!empty($customHead)) {
            $codes[] = $customHead;
        }

        return implode("\n", $codes);
    }

    /**
     * Get tracking codes for body end
     */
    public function getBodyTrackingCodes(): string
    {
        $codes = [];

        // GTM noscript
        $gtmId = $this->settings['gtm_id'] ?? '';
        if (!empty($gtmId)) {
            $codes[] = sprintf(
                '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=%s"
                height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>',
                htmlspecialchars($gtmId)
            );
        }

        // Custom body scripts
        $customBody = $this->settings['custom_body_scripts'] ?? '';
        if (!empty($customBody)) {
            $codes[] = $customBody;
        }

        return implode("\n", $codes);
    }

    /**
     * Get placeholder for development
     */
    private function getPlaceholder(string $slug, array $placement): string
    {
        if (config('app.env') !== 'development') {
            return '';
        }

        // Parse default_size like "728x90" into width and height
        $width = 300;
        $height = 250;
        if (!empty($placement['default_size']) && strpos($placement['default_size'], 'x') !== false) {
            [$width, $height] = array_map('intval', explode('x', $placement['default_size']));
        }

        return sprintf(
            '<div class="bg-neutral-200 dark:bg-neutral-700 flex items-center justify-center text-neutral-500 dark:text-neutral-400 text-sm border-2 border-dashed border-neutral-300 dark:border-neutral-600" style="width:%dpx;height:%dpx;max-width:100%%;">
                Ad Placeholder: %s (%dx%d)
            </div>',
            $width,
            $height,
            htmlspecialchars($slug),
            $width,
            $height
        );
    }

    /**
     * Log ad impression
     */
    private function logImpression(int $adId): void
    {
        try {
            $db = app()->getDatabase();
            $db->execute(
                "UPDATE ads SET impressions = impressions + 1 WHERE id = :id",
                ['id' => $adId]
            );
        } catch (\Exception $e) {
            // Silently fail - ads table might not have impressions column
        }
    }

    /**
     * Simple mobile detection
     */
    private function isMobile(): bool
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent) === 1;
    }

    /**
     * Get setting value
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }
}
