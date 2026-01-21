<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Like;
use App\Models\Save;

/**
 * Favorite Controller
 *
 * Handles user favorites, likes, and saves pages.
 */
class FavoriteController extends Controller
{
    /**
     * Display user's favorites
     */
    public function index(): Response
    {
        $db = $this->db();
        $userId = $_SESSION['user_id'];

        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = config('pagination.per_page') ?? 24;
        $offset = ($page - 1) * $perPage;

        // Get total count
        $total = (int) $db->fetchColumn(
            "SELECT COUNT(*)
             FROM favorites f
             JOIN images i ON f.image_id = i.id
             WHERE f.user_id = :user_id
               AND i.status = 'published'",
            ['user_id' => $userId]
        );

        // Get favorites
        $images = $db->fetchAll(
            "SELECT i.id, i.uuid, i.title, i.slug, i.thumbnail_path, i.thumbnail_webp_path, i.storage_path, i.webp_path,
                    i.alt_text, i.view_count, i.favorite_count, i.is_animated, i.created_at,
                    f.created_at as favorited_at
             FROM favorites f
             JOIN images i ON f.image_id = i.id
             WHERE f.user_id = :user_id
               AND i.status = 'published'
             ORDER BY f.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            ['user_id' => $userId]
        );

        $totalPages = (int) ceil($total / $perPage);

        // Select appropriate view template based on layout preset
        $layoutPreset = setting('layout_preset', 'clean-minimal');
        $template = match ($layoutPreset) {
            'pexels-stock' => 'frontend/favorites-pexels',
            default => 'frontend/favorites',
        };

        return $this->view($template, [
            'title' => 'My Favorites',
            'meta_description' => 'Your saved favorite images.',
            'images' => $images,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Display user's liked images
     */
    public function likes(): Response
    {
        $db = $this->db();
        $userId = $_SESSION['user_id'];

        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = config('pagination.per_page') ?? 24;
        $offset = ($page - 1) * $perPage;

        // Get total count
        $total = (int) $db->fetchColumn(
            "SELECT COUNT(*)
             FROM likes l
             JOIN images i ON l.likeable_id = i.id
             WHERE l.user_id = :user_id
               AND l.likeable_type = 'image'
               AND i.status = 'published'",
            ['user_id' => $userId]
        );

        // Get liked images
        $images = $db->fetchAll(
            "SELECT i.id, i.uuid, i.title, i.slug, i.thumbnail_path, i.thumbnail_webp_path, i.storage_path, i.webp_path,
                    i.alt_text, i.view_count, i.like_count, i.is_animated, i.created_at,
                    l.created_at as liked_at
             FROM likes l
             JOIN images i ON l.likeable_id = i.id
             WHERE l.user_id = :user_id
               AND l.likeable_type = 'image'
               AND i.status = 'published'
             ORDER BY l.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            ['user_id' => $userId]
        );

        $totalPages = (int) ceil($total / $perPage);

        return $this->view('frontend/likes', [
            'title' => 'My Likes',
            'meta_description' => 'Images you have liked.',
            'images' => $images,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Display user's saved blog posts
     */
    public function saves(): Response
    {
        $db = $this->db();
        $userId = $_SESSION['user_id'];

        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = config('pagination.per_page') ?? 12;
        $offset = ($page - 1) * $perPage;

        // Get total count
        $total = (int) $db->fetchColumn(
            "SELECT COUNT(*)
             FROM saves s
             JOIN blog_posts p ON s.saveable_id = p.id
             WHERE s.user_id = :user_id
               AND s.saveable_type = 'blog_post'
               AND p.status = 'published'",
            ['user_id' => $userId]
        );

        // Get saved posts
        $posts = $db->fetchAll(
            "SELECT p.id, p.title, p.slug, p.excerpt, p.featured_image,
                    p.read_time_minutes, p.view_count, p.like_count, p.save_count,
                    p.created_at, p.published_at,
                    s.created_at as saved_at,
                    c.name as category_name, c.slug as category_slug
             FROM saves s
             JOIN blog_posts p ON s.saveable_id = p.id
             LEFT JOIN blog_categories c ON p.category_id = c.id
             WHERE s.user_id = :user_id
               AND s.saveable_type = 'blog_post'
               AND p.status = 'published'
             ORDER BY s.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            ['user_id' => $userId]
        );

        $totalPages = (int) ceil($total / $perPage);

        return $this->view('frontend/saves', [
            'title' => 'Saved Posts',
            'meta_description' => 'Blog posts you have saved.',
            'posts' => $posts,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
        ]);
    }
}
