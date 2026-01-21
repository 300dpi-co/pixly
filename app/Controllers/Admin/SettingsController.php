<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;

class SettingsController extends Controller
{
    /**
     * Settings grouped by category
     */
    private const SETTING_GROUPS = [
        'general' => [
            'label' => 'General',
            'icon' => 'cog',
            'settings' => ['site_name', 'site_description', 'site_url', 'timezone', 'date_format'],
        ],
        'features' => [
            'label' => 'Features',
            'icon' => 'toggle',
            'settings' => [
                'premium_enabled', 'registration_enabled', 'contributor_system_enabled', 'appreciate_system_enabled',
            ],
        ],
        'branding' => [
            'label' => 'Branding',
            'icon' => 'color-swatch',
            'settings' => ['site_logo', 'logo_height', 'dark_mode_toggle_enabled', 'primary_color', 'secondary_color', 'accent_color'],
        ],
        'site_mode' => [
            'label' => 'Site Mode',
            'icon' => 'adjustments',
            'settings' => [
                'adult_mode_enabled', 'layout_preset',
                'age_gate_enabled', 'age_gate_style', 'age_gate_title', 'age_gate_message', 'age_gate_min_age', 'age_gate_remember',
                'nsfw_blur_enabled', 'nsfw_blur_strength', 'nsfw_reveal_on',
                'quick_exit_enabled', 'quick_exit_url', 'quick_exit_text',
                'content_warning_enabled', 'content_warning_text',
                'right_click_disabled', 'private_browsing_notice',
                'disclaimer_enabled', 'disclaimer_text', 'compliance_2257_url',
            ],
        ],
        'images' => [
            'label' => 'Images',
            'icon' => 'photograph',
            'settings' => ['images_per_page', 'max_upload_size', 'allowed_extensions', 'auto_approve_images', 'enable_watermark'],
        ],
        'users' => [
            'label' => 'Users',
            'icon' => 'users',
            'settings' => ['enable_registration', 'require_email_verification', 'default_user_role'],
        ],
        'comments' => [
            'label' => 'Comments',
            'icon' => 'chat',
            'settings' => ['enable_comments', 'moderate_comments', 'comments_per_page'],
        ],
        'seo' => [
            'label' => 'SEO',
            'icon' => 'search',
            'settings' => ['meta_title_suffix', 'default_meta_description', 'enable_sitemap', 'google_analytics_id'],
        ],
        'api' => [
            'label' => 'API Keys',
            'icon' => 'key',
            'settings' => ['claude_api_key', 'deepseek_api_key', 'deepinfra_api_key', 'unsplash_api_key', 'pexels_api_key'],
        ],
        'ads' => [
            'label' => 'Ads',
            'icon' => 'currency-dollar',
            'settings' => ['enable_ads', 'ad_frequency', 'juicyads_site_id'],
        ],
    ];

    public function index(): Response
    {
        $db = $this->db();
        $allSettings = $db->fetchAll("SELECT * FROM settings ORDER BY setting_key");

        // Index settings by key
        $settingsByKey = [];
        foreach ($allSettings as $setting) {
            $settingsByKey[$setting['setting_key']] = $setting;
        }

        // Group settings
        $groupedSettings = [];
        foreach (self::SETTING_GROUPS as $groupKey => $group) {
            $groupedSettings[$groupKey] = [
                'label' => $group['label'],
                'icon' => $group['icon'],
                'settings' => [],
            ];
            foreach ($group['settings'] as $key) {
                if (isset($settingsByKey[$key])) {
                    $groupedSettings[$groupKey]['settings'][] = $settingsByKey[$key];
                    unset($settingsByKey[$key]);
                }
            }
        }

        // Add ungrouped settings to "other"
        if (!empty($settingsByKey)) {
            $groupedSettings['other'] = [
                'label' => 'Other',
                'icon' => 'dots-horizontal',
                'settings' => array_values($settingsByKey),
            ];
        }

        $activeGroup = $_GET['group'] ?? 'general';

        return $this->view('admin/settings/index', [
            'title' => 'Settings',
            'currentPage' => 'settings',
            'groupedSettings' => $groupedSettings,
            'activeGroup' => $activeGroup,
        ], 'admin');
    }

    public function update(): Response
    {
        $db = $this->db();
        $data = $_POST;
        $group = $data['_group'] ?? 'general';

        // Remove internal fields
        unset($data['_token']);
        unset($data['_group']);

        $updated = 0;

        foreach ($data as $key => $value) {
            // Skip empty keys
            if (empty($key)) {
                continue;
            }

            // Get setting info
            $setting = $db->fetch(
                "SELECT * FROM settings WHERE setting_key = :key",
                ['key' => $key]
            );

            if (!$setting) {
                continue;
            }

            // Handle different types
            switch ($setting['setting_type']) {
                case 'bool':
                    // Handle both string "1"/"0" and actual 1/0
                    $value = ($value === '1' || $value === 1 || $value === true) ? '1' : '0';
                    break;
                case 'int':
                    $value = (string) (int) $value;
                    break;
                case 'json':
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
                    break;
                case 'encrypted':
                    // Only update if value changed (not masked)
                    if ($value === '********' || empty($value)) {
                        continue 2;
                    }
                    // In production, encrypt the value
                    break;
            }

            $db->update(
                'settings',
                ['setting_value' => $value],
                'setting_key = :key',
                ['key' => $key]
            );
            $updated++;
        }

        // Clear all settings cache
        clear_settings_cache();

        session_flash('success', "Settings updated successfully. ({$updated} settings saved)");
        return Response::redirect("/admin/settings?group={$group}");
    }

    public function create(): Response
    {
        return $this->view('admin/settings/create', [
            'title' => 'Add Setting',
            'currentPage' => 'settings',
            'types' => ['string', 'int', 'bool', 'json', 'encrypted'],
        ], 'admin');
    }

    public function store(): Response
    {
        $db = $this->db();
        $data = $_POST;

        // Validate
        $errors = $this->validate($data, [
            'setting_key' => 'required',
            'setting_type' => 'required',
        ]);

        if (!empty($errors)) {
            session_flash('errors', $errors);
            session_flash('old', $data);
            return Response::redirect('/admin/settings/create');
        }

        // Check unique key
        $existing = $db->fetch(
            "SELECT id FROM settings WHERE setting_key = :key",
            ['key' => $data['setting_key']]
        );

        if ($existing) {
            session_flash('error', 'Setting key already exists.');
            session_flash('old', $data);
            return Response::redirect('/admin/settings/create');
        }

        // Insert
        $db->insert('settings', [
            'setting_key' => $data['setting_key'],
            'setting_value' => $data['setting_value'] ?? '',
            'setting_type' => $data['setting_type'],
            'description' => $data['description'] ?? null,
            'is_public' => !empty($data['is_public']) ? 1 : 0,
        ]);

        session_flash('success', 'Setting created successfully.');
        return Response::redirect('/admin/settings');
    }

    public function destroy(string|int $id): Response
    {
        $id = (int) $id;
        $db = $this->db();

        $setting = $db->fetch("SELECT * FROM settings WHERE id = :id", ['id' => $id]);

        if (!$setting) {
            session_flash('error', 'Setting not found.');
            return Response::redirect('/admin/settings');
        }

        $db->delete('settings', 'id = :id', ['id' => $id]);

        session_flash('success', 'Setting deleted successfully.');
        return Response::redirect('/admin/settings');
    }

    /**
     * Upload site logo
     */
    public function uploadLogo(): Response
    {
        if (empty($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            session_flash('error', 'No file uploaded or upload error.');
            return Response::redirect('/admin/settings?group=branding');
        }

        $file = $_FILES['logo'];
        $allowedTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/svg+xml', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            session_flash('error', 'Invalid file type. Allowed: PNG, JPG, GIF, SVG, WebP');
            return Response::redirect('/admin/settings?group=branding');
        }

        // Validate file size
        if ($file['size'] > $maxSize) {
            session_flash('error', 'File too large. Maximum size: 2MB');
            return Response::redirect('/admin/settings?group=branding');
        }

        // Generate filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . time() . '.' . $ext;
        $uploadDir = ROOT_PATH . '/uploads/branding/';
        $uploadPath = $uploadDir . $filename;

        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Delete old logo if exists
        $db = $this->db();
        $oldLogo = $db->fetch("SELECT setting_value FROM settings WHERE setting_key = 'site_logo'");
        if ($oldLogo && $oldLogo['setting_value']) {
            $oldPath = ROOT_PATH . '/uploads/' . $oldLogo['setting_value'];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            session_flash('error', 'Failed to save file.');
            return Response::redirect('/admin/settings?group=branding');
        }

        // Update or insert setting
        $relativePath = 'branding/' . $filename;
        $existing = $db->fetch("SELECT id FROM settings WHERE setting_key = 'site_logo'");

        if ($existing) {
            $db->update('settings', ['setting_value' => $relativePath], 'setting_key = :key', ['key' => 'site_logo']);
        } else {
            $db->insert('settings', [
                'setting_key' => 'site_logo',
                'setting_value' => $relativePath,
                'setting_type' => 'string',
                'description' => 'Site logo path',
                'is_public' => 1,
            ]);
        }

        // Clear all settings cache
        clear_settings_cache();

        session_flash('success', 'Logo uploaded successfully.');
        return Response::redirect('/admin/settings?group=branding');
    }

    /**
     * Delete site logo
     */
    public function deleteLogo(): Response
    {
        $db = $this->db();
        $logo = $db->fetch("SELECT setting_value FROM settings WHERE setting_key = 'site_logo'");

        if ($logo && $logo['setting_value']) {
            $logoPath = ROOT_PATH . '/uploads/' . $logo['setting_value'];
            if (file_exists($logoPath)) {
                unlink($logoPath);
            }
            $db->update('settings', ['setting_value' => ''], 'setting_key = :key', ['key' => 'site_logo']);
        }

        clear_settings_cache();

        session_flash('success', 'Logo removed.');
        return Response::redirect('/admin/settings?group=branding');
    }
}
