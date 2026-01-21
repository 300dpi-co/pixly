<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\Response;

/**
 * Profile Controller
 *
 * Handles public profiles and user settings.
 */
class ProfileController extends Controller
{
    /**
     * Show public user profile by username
     */
    public function publicProfile(string $username): Response
    {
        $db = $this->db();

        // Get user by username
        $user = $db->fetch(
            "SELECT id, username, display_name, avatar_path, bio, website, location,
                    twitter_handle, instagram_handle, is_public, role, created_at
             FROM users
             WHERE username = :username AND status = 'active'",
            ['username' => $username]
        );

        if (!$user) {
            return $this->notFound();
        }

        // Check if profile is public (or viewer is the owner)
        $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['id'];
        if (!$user['is_public'] && !$isOwner) {
            return $this->view('frontend/profile-private', [
                'title' => $user['display_name'] ?: $user['username'],
                'username' => $user['username'],
            ]);
        }

        // Get user's public images
        $images = $db->fetchAll(
            "SELECT id, uuid, title, slug, thumbnail_path, thumbnail_webp_path,
                    view_count, favorite_count, created_at
             FROM images
             WHERE user_id = :user_id AND status = 'published' AND moderation_status = 'approved'
             ORDER BY created_at DESC
             LIMIT 24",
            ['user_id' => $user['id']]
        );

        // Get user stats
        $stats = [
            'uploads' => (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM images WHERE user_id = :id AND status = 'published'",
                ['id' => $user['id']]
            ),
            'total_views' => (int) $db->fetchColumn(
                "SELECT COALESCE(SUM(view_count), 0) FROM images WHERE user_id = :id AND status = 'published'",
                ['id' => $user['id']]
            ),
            'total_favorites' => (int) $db->fetchColumn(
                "SELECT COALESCE(SUM(favorite_count), 0) FROM images WHERE user_id = :id AND status = 'published'",
                ['id' => $user['id']]
            ),
        ];

        return $this->view('frontend/profile-public', [
            'title' => $user['display_name'] ?: $user['username'],
            'user' => $user,
            'images' => $images,
            'stats' => $stats,
            'isOwner' => $isOwner,
        ]);
    }

    /**
     * Show current user's settings/profile page
     */
    public function settings(): Response
    {
        $user = $this->user();

        // Get full user data
        $userData = $this->db()->fetch(
            "SELECT * FROM users WHERE id = :id",
            ['id' => $user['id']]
        );

        // Get user stats
        $stats = [
            'uploads' => (int) $this->db()->fetchColumn(
                "SELECT COUNT(*) FROM images WHERE user_id = :id",
                ['id' => $user['id']]
            ),
            'favorites' => (int) $this->db()->fetchColumn(
                "SELECT COUNT(*) FROM favorites WHERE user_id = :id",
                ['id' => $user['id']]
            ),
        ];

        return $this->view('frontend/settings', [
            'title' => 'Settings',
            'user' => $userData,
            'stats' => $stats,
        ]);
    }

    /**
     * Update profile information
     */
    public function updateProfile(): Response
    {
        $user = $this->user();
        $db = $this->db();

        $displayName = trim($this->request->input('display_name') ?? '');
        $bio = trim($this->request->input('bio') ?? '');
        $website = trim($this->request->input('website') ?? '');
        $location = trim($this->request->input('location') ?? '');
        $twitter = trim($this->request->input('twitter_handle') ?? '');
        $instagram = trim($this->request->input('instagram_handle') ?? '');
        $isPublic = $this->request->input('is_public') === '1' ? 1 : 0;

        // Validate
        $errors = [];

        if (strlen($displayName) > 100) {
            $errors['display_name'] = 'Display name must be 100 characters or less.';
        }

        if (strlen($bio) > 500) {
            $errors['bio'] = 'Bio must be 500 characters or less.';
        }

        if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
            $errors['website'] = 'Please enter a valid URL.';
        }

        // Clean social handles (remove @ if present)
        $twitter = ltrim($twitter, '@');
        $instagram = ltrim($instagram, '@');

        if (!empty($errors)) {
            session_flash('errors', $errors);
            session_flash('old', $this->request->all());
            return Response::redirect('/profile');
        }

        // Update profile
        $db->query(
            "UPDATE users SET
                display_name = :display_name,
                bio = :bio,
                website = :website,
                location = :location,
                twitter_handle = :twitter,
                instagram_handle = :instagram,
                is_public = :is_public
             WHERE id = :id",
            [
                'display_name' => $displayName ?: null,
                'bio' => $bio ?: null,
                'website' => $website ?: null,
                'location' => $location ?: null,
                'twitter' => $twitter ?: null,
                'instagram' => $instagram ?: null,
                'is_public' => $isPublic,
                'id' => $user['id'],
            ]
        );

        session_flash('success', 'Profile updated successfully.');
        return Response::redirect('/profile');
    }

    /**
     * Update avatar
     */
    public function updateAvatar(): Response
    {
        $user = $this->user();
        $db = $this->db();

        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            session_flash('error', 'Please select an image to upload.');
            return Response::redirect('/profile');
        }

        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            session_flash('error', 'Invalid file type. Please upload a JPG, PNG, GIF, or WebP image.');
            return Response::redirect('/profile');
        }

        if ($file['size'] > $maxSize) {
            session_flash('error', 'Image too large. Maximum size is 2MB.');
            return Response::redirect('/profile');
        }

        // Generate filename and path
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
        $uploadDir = ROOT_PATH . '/public_html/uploads/avatars/';
        $uploadPath = $uploadDir . $filename;

        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Delete old avatar if exists
        $oldAvatar = $db->fetch("SELECT avatar_path FROM users WHERE id = :id", ['id' => $user['id']]);
        if ($oldAvatar && $oldAvatar['avatar_path']) {
            $oldPath = ROOT_PATH . '/public_html/uploads/' . $oldAvatar['avatar_path'];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            session_flash('error', 'Failed to upload image. Please try again.');
            return Response::redirect('/profile');
        }

        // Update database
        $relativePath = 'avatars/' . $filename;
        $db->query(
            "UPDATE users SET avatar_path = :path WHERE id = :id",
            ['path' => $relativePath, 'id' => $user['id']]
        );

        session_flash('success', 'Avatar updated successfully.');
        return Response::redirect('/profile');
    }

    /**
     * Update password
     */
    public function updatePassword(): Response
    {
        $user = $this->user();
        $db = $this->db();

        $currentPassword = $this->request->input('current_password') ?? '';
        $newPassword = $this->request->input('new_password') ?? '';
        $confirmPassword = $this->request->input('confirm_password') ?? '';

        // Get current password hash
        $userData = $db->fetch("SELECT password_hash FROM users WHERE id = :id", ['id' => $user['id']]);

        // Validate current password
        if (!password_verify($currentPassword, $userData['password_hash'])) {
            session_flash('error', 'Current password is incorrect.');
            return Response::redirect('/profile#security');
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            session_flash('error', 'New password must be at least 8 characters.');
            return Response::redirect('/profile#security');
        }

        if ($newPassword !== $confirmPassword) {
            session_flash('error', 'New passwords do not match.');
            return Response::redirect('/profile#security');
        }

        // Update password
        $hash = password_hash($newPassword, PASSWORD_ARGON2ID);
        $db->query(
            "UPDATE users SET password_hash = :hash WHERE id = :id",
            ['hash' => $hash, 'id' => $user['id']]
        );

        session_flash('success', 'Password updated successfully.');
        return Response::redirect('/profile#security');
    }

    /**
     * Legacy show method - redirect to settings
     */
    public function show(): Response
    {
        return $this->settings();
    }
}
