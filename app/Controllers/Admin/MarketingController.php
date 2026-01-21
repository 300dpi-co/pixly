<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Models\AdPlacement;
use App\Models\Ad;
use App\Models\PopupAd;
use App\Models\AnnouncementBar;
use App\Models\NewsletterSubscriber;
use App\Models\MarketingSetting;

/**
 * Marketing Controller (Admin)
 */
class MarketingController extends Controller
{
    /**
     * Marketing Dashboard
     */
    public function index(): Response
    {
        $adStats = AdPlacement::getStats();
        $subscriberCounts = NewsletterSubscriber::getCounts();
        $settings = MarketingSetting::getAllGrouped();

        // Get recent activity
        $recentAds = $this->db()->fetchAll(
            "SELECT * FROM ad_placements ORDER BY updated_at DESC LIMIT 5"
        );

        return $this->view('admin/marketing/index', [
            'title' => 'Marketing Dashboard',
            'adStats' => $adStats,
            'subscriberCounts' => $subscriberCounts,
            'settings' => $settings,
            'recentAds' => $recentAds,
        ], 'admin');
    }

    // =====================
    // AD PLACEMENTS
    // =====================

    /**
     * List ad placements
     */
    public function placements(): Response
    {
        $placements = $this->db()->fetchAll(
            "SELECT * FROM ad_placements ORDER BY sort_order, name"
        );

        return $this->view('admin/marketing/placements', [
            'title' => 'Ad Placements',
            'placements' => $placements,
        ], 'admin');
    }

    /**
     * Create ad placement form
     */
    public function createPlacement(): Response
    {
        return $this->view('admin/marketing/placement-form', [
            'title' => 'Create Ad Placement',
            'placement' => null,
        ], 'admin');
    }

    /**
     * Store new ad placement
     */
    public function storePlacement(): Response
    {
        $data = $this->request->all();

        $this->db()->insert('ad_placements', [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? slug($data['name']),
            'description' => $data['description'] ?? '',
            'location' => $data['location'] ?? 'header',
            'default_size' => $data['default_size'] ?? '',
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        session_flash('success', 'Ad placement created successfully.');
        return Response::redirect('/admin/marketing/placements');
    }

    /**
     * Edit ad placement form
     */
    public function editPlacement(int|string $id): Response
    {
        $placement = $this->db()->fetch(
            "SELECT * FROM ad_placements WHERE id = :id",
            ['id' => $id]
        );

        if (!$placement) {
            return $this->notFound();
        }

        return $this->view('admin/marketing/placement-form', [
            'title' => 'Edit Ad Placement',
            'placement' => $placement,
        ], 'admin');
    }

    /**
     * Update ad placement
     */
    public function updatePlacement(int|string $id): Response
    {
        $data = $this->request->all();

        $this->db()->query(
            "UPDATE ad_placements SET
                name = :name,
                slug = :slug,
                description = :description,
                location = :location,
                default_size = :default_size,
                is_active = :is_active,
                sort_order = :sort_order
             WHERE id = :id",
            [
                'name' => $data['name'],
                'slug' => $data['slug'] ?? slug($data['name']),
                'description' => $data['description'] ?? '',
                'location' => $data['location'] ?? 'header',
                'default_size' => $data['default_size'] ?? '',
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'id' => $id,
            ]
        );

        session_flash('success', 'Ad placement updated successfully.');
        return Response::redirect('/admin/marketing/placements');
    }

    /**
     * Delete ad placement
     */
    public function deletePlacement(int|string $id): Response
    {
        $this->db()->delete('ad_placements', 'id = :id', ['id' => $id]);
        session_flash('success', 'Ad placement deleted.');
        return Response::redirect('/admin/marketing/placements');
    }

    /**
     * Toggle ad placement status
     */
    public function togglePlacement(int|string $id): Response
    {
        $this->db()->query(
            "UPDATE ad_placements SET is_active = NOT is_active WHERE id = :id",
            ['id' => $id]
        );

        return Response::json(['success' => true]);
    }

    // =====================
    // ADS (Content)
    // =====================

    /**
     * List all ads
     */
    public function ads(): Response
    {
        $ads = $this->db()->fetchAll(
            "SELECT a.*, p.name as placement_name, p.slug as placement_slug
             FROM ads a
             LEFT JOIN ad_placements p ON a.placement_id = p.id
             ORDER BY a.created_at DESC"
        );

        return $this->view('admin/marketing/ads', [
            'title' => 'Manage Ads',
            'ads' => $ads,
        ], 'admin');
    }

    /**
     * Create ad form
     */
    public function createAd(): Response
    {
        $placements = $this->db()->fetchAll(
            "SELECT * FROM ad_placements WHERE is_active = 1 ORDER BY sort_order, name"
        );

        return $this->view('admin/marketing/ad-form', [
            'title' => 'Create Ad',
            'ad' => null,
            'placements' => $placements,
        ], 'admin');
    }

    /**
     * Store new ad
     */
    public function storeAd(): Response
    {
        $data = $this->request->all();

        $this->db()->insert('ads', [
            'name' => $data['name'],
            'placement_id' => $data['placement_id'] ?: null,
            'ad_type' => $data['ad_type'] ?? 'custom_html',
            'content' => $data['content'] ?? '',
            'image_path' => $data['image_path'] ?? null,
            'destination_url' => $data['destination_url'] ?? null,
            'device_target' => $data['device_target'] ?? 'all',
            'start_date' => $data['start_date'] ?: null,
            'end_date' => $data['end_date'] ?: null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'priority' => (int) ($data['priority'] ?? 0),
        ]);

        session_flash('success', 'Ad created successfully.');
        return Response::redirect('/admin/marketing/ads');
    }

    /**
     * Edit ad form
     */
    public function editAd(int|string $id): Response
    {
        $ad = $this->db()->fetch(
            "SELECT * FROM ads WHERE id = :id",
            ['id' => $id]
        );

        if (!$ad) {
            return $this->notFound();
        }

        $placements = $this->db()->fetchAll(
            "SELECT * FROM ad_placements WHERE is_active = 1 ORDER BY sort_order, name"
        );

        return $this->view('admin/marketing/ad-form', [
            'title' => 'Edit Ad',
            'ad' => $ad,
            'placements' => $placements,
        ], 'admin');
    }

    /**
     * Update ad
     */
    public function updateAd(int|string $id): Response
    {
        $data = $this->request->all();

        $this->db()->query(
            "UPDATE ads SET
                name = :name,
                placement_id = :placement_id,
                ad_type = :ad_type,
                content = :content,
                image_path = :image_path,
                destination_url = :destination_url,
                device_target = :device_target,
                start_date = :start_date,
                end_date = :end_date,
                is_active = :is_active,
                priority = :priority
             WHERE id = :id",
            [
                'name' => $data['name'],
                'placement_id' => $data['placement_id'] ?: null,
                'ad_type' => $data['ad_type'] ?? 'custom_html',
                'content' => $data['content'] ?? '',
                'image_path' => $data['image_path'] ?? null,
                'destination_url' => $data['destination_url'] ?? null,
                'device_target' => $data['device_target'] ?? 'all',
                'start_date' => $data['start_date'] ?: null,
                'end_date' => $data['end_date'] ?: null,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'priority' => (int) ($data['priority'] ?? 0),
                'id' => $id,
            ]
        );

        session_flash('success', 'Ad updated successfully.');
        return Response::redirect('/admin/marketing/ads');
    }

    /**
     * Delete ad
     */
    public function deleteAd(int|string $id): Response
    {
        $this->db()->delete('ads', 'id = :id', ['id' => $id]);
        session_flash('success', 'Ad deleted.');
        return Response::redirect('/admin/marketing/ads');
    }

    // =====================
    // POPUP ADS
    // =====================

    /**
     * List popup ads
     */
    public function popups(): Response
    {
        $popups = $this->db()->fetchAll(
            "SELECT * FROM popup_ads ORDER BY priority DESC, created_at DESC"
        );

        return $this->view('admin/marketing/popups', [
            'title' => 'Popup Ads',
            'popups' => $popups,
        ], 'admin');
    }

    /**
     * Create popup form
     */
    public function createPopup(): Response
    {
        return $this->view('admin/marketing/popup-form', [
            'title' => 'Create Popup Ad',
            'popup' => null,
        ], 'admin');
    }

    /**
     * Store new popup
     */
    public function storePopup(): Response
    {
        $data = $this->request->all();

        $this->db()->insert('popup_ads', [
            'name' => $data['name'],
            'content' => $data['content'],
            'trigger_type' => $data['trigger_type'],
            'trigger_delay' => (int) ($data['trigger_delay'] ?? 0),
            'trigger_scroll_percent' => (int) ($data['trigger_scroll_percent'] ?? 50),
            'show_on_mobile' => isset($data['show_on_mobile']) ? 1 : 0,
            'overlay_opacity' => (int) ($data['overlay_opacity'] ?? 50),
            'position' => $data['position'] ?? 'center',
            'animation' => $data['animation'] ?? 'fade',
            'width' => $data['width'] ?? '500px',
            'frequency' => $data['frequency'] ?? 'once_session',
            'cookie_days' => (int) ($data['cookie_days'] ?? 7),
            'pages_exclude' => $data['pages_exclude'] ? json_encode(array_filter(explode("\n", $data['pages_exclude']))) : null,
            'device_target' => $data['device_target'] ?? 'all',
            'start_date' => $data['start_date'] ?: null,
            'end_date' => $data['end_date'] ?: null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'priority' => (int) ($data['priority'] ?? 0),
        ]);

        session_flash('success', 'Popup ad created successfully.');
        return Response::redirect('/admin/marketing/popups');
    }

    /**
     * Edit popup form
     */
    public function editPopup(int|string $id): Response
    {
        $popup = $this->db()->fetch(
            "SELECT * FROM popup_ads WHERE id = :id",
            ['id' => $id]
        );

        if (!$popup) {
            return $this->notFound();
        }

        return $this->view('admin/marketing/popup-form', [
            'title' => 'Edit Popup Ad',
            'popup' => $popup,
        ], 'admin');
    }

    /**
     * Update popup
     */
    public function updatePopup(int|string $id): Response
    {
        $data = $this->request->all();

        $this->db()->query(
            "UPDATE popup_ads SET
                name = :name,
                content = :content,
                trigger_type = :trigger_type,
                trigger_delay = :trigger_delay,
                trigger_scroll_percent = :trigger_scroll_percent,
                show_on_mobile = :show_on_mobile,
                overlay_opacity = :overlay_opacity,
                position = :position,
                animation = :animation,
                width = :width,
                frequency = :frequency,
                cookie_days = :cookie_days,
                pages_exclude = :pages_exclude,
                device_target = :device_target,
                start_date = :start_date,
                end_date = :end_date,
                is_active = :is_active,
                priority = :priority
             WHERE id = :id",
            [
                'name' => $data['name'],
                'content' => $data['content'],
                'trigger_type' => $data['trigger_type'],
                'trigger_delay' => (int) ($data['trigger_delay'] ?? 0),
                'trigger_scroll_percent' => (int) ($data['trigger_scroll_percent'] ?? 50),
                'show_on_mobile' => isset($data['show_on_mobile']) ? 1 : 0,
                'overlay_opacity' => (int) ($data['overlay_opacity'] ?? 50),
                'position' => $data['position'] ?? 'center',
                'animation' => $data['animation'] ?? 'fade',
                'width' => $data['width'] ?? '500px',
                'frequency' => $data['frequency'] ?? 'once_session',
                'cookie_days' => (int) ($data['cookie_days'] ?? 7),
                'pages_exclude' => $data['pages_exclude'] ? json_encode(array_filter(explode("\n", $data['pages_exclude']))) : null,
                'device_target' => $data['device_target'] ?? 'all',
                'start_date' => $data['start_date'] ?: null,
                'end_date' => $data['end_date'] ?: null,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'priority' => (int) ($data['priority'] ?? 0),
                'id' => $id,
            ]
        );

        session_flash('success', 'Popup ad updated successfully.');
        return Response::redirect('/admin/marketing/popups');
    }

    /**
     * Delete popup
     */
    public function deletePopup(int|string $id): Response
    {
        $this->db()->delete('popup_ads', 'id = :id', ['id' => $id]);
        session_flash('success', 'Popup ad deleted.');
        return Response::redirect('/admin/marketing/popups');
    }

    // =====================
    // ANNOUNCEMENT BAR
    // =====================

    /**
     * Announcement bars list
     */
    public function announcements(): Response
    {
        $announcements = $this->db()->fetchAll(
            "SELECT * FROM announcement_bars ORDER BY priority DESC, created_at DESC"
        );

        return $this->view('admin/marketing/announcements', [
            'title' => 'Announcement Bars',
            'announcements' => $announcements,
        ], 'admin');
    }

    /**
     * Create announcement form
     */
    public function createAnnouncement(): Response
    {
        return $this->view('admin/marketing/announcement-form', [
            'title' => 'Create Announcement',
            'announcement' => null,
        ], 'admin');
    }

    /**
     * Store announcement
     */
    public function storeAnnouncement(): Response
    {
        $data = $this->request->all();

        $this->db()->insert('announcement_bars', [
            'message' => $data['message'],
            'link_url' => $data['link_url'] ?: null,
            'link_text' => $data['link_text'] ?: null,
            'bg_color' => $data['bg_color'] ?? '#3b82f6',
            'text_color' => $data['text_color'] ?? '#ffffff',
            'is_dismissible' => isset($data['is_dismissible']) ? 1 : 0,
            'cookie_days' => (int) ($data['cookie_days'] ?? 1),
            'position' => $data['position'] ?? 'top',
            'is_sticky' => isset($data['is_sticky']) ? 1 : 0,
            'start_date' => $data['start_date'] ?: null,
            'end_date' => $data['end_date'] ?: null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'priority' => (int) ($data['priority'] ?? 0),
        ]);

        session_flash('success', 'Announcement created successfully.');
        return Response::redirect('/admin/marketing/announcements');
    }

    /**
     * Edit announcement form
     */
    public function editAnnouncement(int|string $id): Response
    {
        $announcement = $this->db()->fetch(
            "SELECT * FROM announcement_bars WHERE id = :id",
            ['id' => $id]
        );

        if (!$announcement) {
            return $this->notFound();
        }

        return $this->view('admin/marketing/announcement-form', [
            'title' => 'Edit Announcement',
            'announcement' => $announcement,
        ], 'admin');
    }

    /**
     * Update announcement
     */
    public function updateAnnouncement(int|string $id): Response
    {
        $data = $this->request->all();

        $this->db()->query(
            "UPDATE announcement_bars SET
                message = :message,
                link_url = :link_url,
                link_text = :link_text,
                bg_color = :bg_color,
                text_color = :text_color,
                is_dismissible = :is_dismissible,
                cookie_days = :cookie_days,
                position = :position,
                is_sticky = :is_sticky,
                start_date = :start_date,
                end_date = :end_date,
                is_active = :is_active,
                priority = :priority
             WHERE id = :id",
            [
                'message' => $data['message'],
                'link_url' => $data['link_url'] ?: null,
                'link_text' => $data['link_text'] ?: null,
                'bg_color' => $data['bg_color'] ?? '#3b82f6',
                'text_color' => $data['text_color'] ?? '#ffffff',
                'is_dismissible' => isset($data['is_dismissible']) ? 1 : 0,
                'cookie_days' => (int) ($data['cookie_days'] ?? 1),
                'position' => $data['position'] ?? 'top',
                'is_sticky' => isset($data['is_sticky']) ? 1 : 0,
                'start_date' => $data['start_date'] ?: null,
                'end_date' => $data['end_date'] ?: null,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'priority' => (int) ($data['priority'] ?? 0),
                'id' => $id,
            ]
        );

        session_flash('success', 'Announcement updated successfully.');
        return Response::redirect('/admin/marketing/announcements');
    }

    /**
     * Delete announcement
     */
    public function deleteAnnouncement(int|string $id): Response
    {
        $this->db()->delete('announcement_bars', 'id = :id', ['id' => $id]);
        session_flash('success', 'Announcement deleted.');
        return Response::redirect('/admin/marketing/announcements');
    }

    // =====================
    // NEWSLETTER
    // =====================

    /**
     * Newsletter subscribers
     */
    public function newsletter(): Response
    {
        $status = $this->request->input('status', 'confirmed');
        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $subscribers = NewsletterSubscriber::byStatus($status, $perPage, $offset);
        $counts = NewsletterSubscriber::getCounts();

        $total = match ($status) {
            'confirmed' => $counts['confirmed'],
            'pending' => $counts['pending'],
            'unsubscribed' => $counts['unsubscribed'],
            default => $counts['total'],
        };

        return $this->view('admin/marketing/newsletter', [
            'title' => 'Newsletter Subscribers',
            'subscribers' => $subscribers,
            'counts' => $counts,
            'status' => $status,
            'page' => $page,
            'totalPages' => (int) ceil($total / $perPage),
        ], 'admin');
    }

    /**
     * Export subscribers CSV
     */
    public function exportSubscribers(): Response
    {
        $csv = NewsletterSubscriber::exportCsv();

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="newsletter_subscribers_' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Delete subscriber
     */
    public function deleteSubscriber(int|string $id): Response
    {
        $this->db()->delete('newsletter_subscribers', 'id = :id', ['id' => $id]);
        session_flash('success', 'Subscriber deleted.');
        return Response::redirect('/admin/marketing/newsletter');
    }

    // =====================
    // TRACKING & SETTINGS
    // =====================

    /**
     * Tracking codes settings
     */
    public function tracking(): Response
    {
        $settings = MarketingSetting::byGroup('tracking');

        return $this->view('admin/marketing/tracking', [
            'title' => 'Tracking Codes',
            'settings' => $settings,
        ], 'admin');
    }

    /**
     * Update tracking codes
     */
    public function updateTracking(): Response
    {
        $data = $this->request->all();

        MarketingSetting::updateMany([
            'google_analytics_id' => $data['google_analytics_id'] ?? '',
            'gtm_id' => $data['gtm_id'] ?? '',
            'facebook_pixel_id' => $data['facebook_pixel_id'] ?? '',
            'custom_head_scripts' => $data['custom_head_scripts'] ?? '',
            'custom_body_scripts' => $data['custom_body_scripts'] ?? '',
        ], 'tracking');

        session_flash('success', 'Tracking codes updated successfully.');
        return Response::redirect('/admin/marketing/tracking');
    }

    /**
     * Social media settings
     */
    public function social(): Response
    {
        $settings = MarketingSetting::byGroup('social');

        return $this->view('admin/marketing/social', [
            'title' => 'Social Media',
            'settings' => $settings,
        ], 'admin');
    }

    /**
     * Update social settings
     */
    public function updateSocial(): Response
    {
        $data = $this->request->all();

        MarketingSetting::updateMany([
            'social_facebook' => $data['social_facebook'] ?? '',
            'social_twitter' => $data['social_twitter'] ?? '',
            'social_instagram' => $data['social_instagram'] ?? '',
            'social_pinterest' => $data['social_pinterest'] ?? '',
            'social_youtube' => $data['social_youtube'] ?? '',
            'social_tiktok' => $data['social_tiktok'] ?? '',
            'share_buttons_enabled' => isset($data['share_buttons_enabled']) ? '1' : '0',
            'pinterest_pin_button' => isset($data['pinterest_pin_button']) ? '1' : '0',
        ], 'social');

        session_flash('success', 'Social media settings updated.');
        return Response::redirect('/admin/marketing/social');
    }

    /**
     * General ad settings
     */
    public function adSettings(): Response
    {
        $settings = MarketingSetting::byGroup('ads');
        $adsenseSettings = MarketingSetting::byGroup('adsense');
        $juicyadsSettings = MarketingSetting::byGroup('juicyads');

        return $this->view('admin/marketing/ad-settings', [
            'title' => 'Ad Settings',
            'settings' => $settings,
            'adsenseSettings' => $adsenseSettings,
            'juicyadsSettings' => $juicyadsSettings,
        ], 'admin');
    }

    /**
     * Update ad settings
     */
    public function updateAdSettings(): Response
    {
        $data = $this->request->all();

        MarketingSetting::updateMany([
            'ads_enabled' => isset($data['ads_enabled']) ? '1' : '0',
            'ads_logged_in_users' => isset($data['ads_logged_in_users']) ? '1' : '0',
            'gallery_ad_frequency' => $data['gallery_ad_frequency'] ?? '6',
            'blog_list_ad_frequency' => $data['blog_list_ad_frequency'] ?? '4',
        ], 'ads');

        MarketingSetting::updateMany([
            'adsense_publisher_id' => $data['adsense_publisher_id'] ?? '',
            'adsense_auto_ads' => isset($data['adsense_auto_ads']) ? '1' : '0',
        ], 'adsense');

        MarketingSetting::updateMany([
            'juicyads_publisher_id' => $data['juicyads_publisher_id'] ?? '',
        ], 'juicyads');

        session_flash('success', 'Ad settings updated.');
        return Response::redirect('/admin/marketing/ad-settings');
    }
}
