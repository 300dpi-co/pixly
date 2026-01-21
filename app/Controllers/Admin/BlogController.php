<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\BlogComment;
use App\Services\BlogAIService;

/**
 * Admin Blog Controller
 *
 * Handles blog management in admin panel.
 */
class BlogController extends Controller
{
    /**
     * List all blog posts
     */
    public function index(): Response
    {
        $db = $this->db();

        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Filter parameters
        $status = $this->request->input('status');
        $categoryId = $this->request->input('category_id');
        $search = $this->request->input('search');

        // Build query
        $where = [];
        $params = [];

        if ($status) {
            $where[] = "p.status = :status";
            $params['status'] = $status;
        }

        if ($categoryId) {
            $where[] = "p.category_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        if ($search) {
            $where[] = "(p.title LIKE :search OR p.content LIKE :search2)";
            $params['search'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count
        $total = (int) $db->fetchColumn("SELECT COUNT(*) FROM blog_posts p {$whereClause}", $params);

        // Get posts
        $posts = $db->fetchAll(
            "SELECT p.*, u.username as author_name, c.name as category_name
             FROM blog_posts p
             LEFT JOIN users u ON p.author_id = u.id
             LEFT JOIN blog_categories c ON p.category_id = c.id
             {$whereClause}
             ORDER BY p.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = (int) ceil($total / $perPage);

        // Get categories for filter
        $categories = BlogCategory::active();

        // Get stats
        $stats = [
            'total' => (int) $db->fetchColumn("SELECT COUNT(*) FROM blog_posts"),
            'published' => (int) $db->fetchColumn("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'"),
            'drafts' => (int) $db->fetchColumn("SELECT COUNT(*) FROM blog_posts WHERE status = 'draft'"),
            'ai_generated' => (int) $db->fetchColumn("SELECT COUNT(*) FROM blog_posts WHERE ai_generated = 1"),
        ];

        return $this->view('admin/blog/index', [
            'title' => 'Blog Posts',
            'currentPage' => 'blog',
            'posts' => $posts,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'categories' => $categories,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'category_id' => $categoryId,
                'search' => $search,
            ],
        ], 'admin');
    }

    /**
     * Show create form
     */
    public function create(): Response
    {
        $categories = BlogCategory::forSelect();
        $tags = BlogTag::withCounts();

        return $this->view('admin/blog/create', [
            'title' => 'Create Post',
            'currentPage' => 'blog-create',
            'categories' => $categories,
            'tags' => $tags,
        ], 'admin');
    }

    /**
     * Store new post
     */
    public function store(): Response
    {
        $data = $this->request->all();

        // Validate
        $errors = $this->validate($data, [
            'title' => 'required|max:200',
            'content' => 'required',
        ]);

        if (!empty($errors)) {
            session_flash('errors', $errors);
            session_flash('old', $data);
            return Response::redirect('/admin/blog/create');
        }

        // Generate slug
        $slug = BlogPost::generateSlug($data['title']);

        // Handle featured image upload
        $featuredImage = null;
        if (!empty($_FILES['featured_image']['tmp_name'])) {
            $featuredImage = $this->uploadFeaturedImage($_FILES['featured_image']);
        }

        // Calculate read time
        $readTime = max(1, (int) ceil(str_word_count(strip_tags($data['content'])) / 200));

        // Create post
        $post = new BlogPost();
        $post->title = $data['title'];
        $post->slug = $slug;
        $post->excerpt = $data['excerpt'] ?? $this->generateExcerpt($data['content']);
        $post->content = $data['content'];
        $post->featured_image = $featuredImage;
        $post->featured_image_alt = $data['featured_image_alt'] ?? '';
        $post->meta_title = $data['meta_title'] ?? '';
        $post->meta_description = $data['meta_description'] ?? '';
        $post->meta_keywords = $data['meta_keywords'] ?? '';
        $post->category_id = !empty($data['category_id']) ? (int) $data['category_id'] : null;
        $post->author_id = $_SESSION['user_id'];
        $post->status = $data['status'] ?? 'draft';
        $post->is_featured = !empty($data['is_featured']) ? 1 : 0;
        $post->allow_comments = !empty($data['allow_comments']) ? 1 : 0;
        $post->read_time_minutes = $readTime;

        if ($post->status === 'published' && empty($post->published_at)) {
            $post->published_at = date('Y-m-d H:i:s');
        }

        if ($post->status === 'scheduled' && !empty($data['scheduled_at'])) {
            $post->scheduled_at = $data['scheduled_at'];
        }

        $post->save();

        // Sync tags
        if (!empty($data['tags'])) {
            $tagIds = BlogTag::getIdsFromString($data['tags']);
            $post->syncTags($tagIds);
        }

        // Update category post count
        if ($post->category_id) {
            BlogCategory::updatePostCount($post->category_id);
        }

        session_flash('success', 'Post created successfully.');
        return Response::redirect('/admin/blog/' . $post->id . '/edit');
    }

    /**
     * Show edit form
     */
    public function edit(string|int $id): Response
    {
        $post = BlogPost::find((int) $id);

        if (!$post) {
            session_flash('error', 'Post not found.');
            return Response::redirect('/admin/blog');
        }

        $categories = BlogCategory::forSelect();
        $tags = $post->tags();
        $tagString = implode(', ', array_map(fn($t) => $t->name, $tags));
        $allTags = BlogTag::withCounts();
        $revisions = $post->revisions();

        return $this->view('admin/blog/edit', [
            'title' => 'Edit Post',
            'currentPage' => 'blog',
            'post' => $post,
            'categories' => $categories,
            'tagString' => $tagString,
            'allTags' => $allTags,
            'revisions' => $revisions,
        ], 'admin');
    }

    /**
     * Update post
     */
    public function update(string|int $id): Response
    {
        $post = BlogPost::find((int) $id);

        if (!$post) {
            session_flash('error', 'Post not found.');
            return Response::redirect('/admin/blog');
        }

        $data = $this->request->all();

        // Validate
        $errors = $this->validate($data, [
            'title' => 'required|max:200',
            'content' => 'required',
        ]);

        if (!empty($errors)) {
            session_flash('errors', $errors);
            session_flash('old', $data);
            return Response::redirect('/admin/blog/' . $id . '/edit');
        }

        // Create revision before updating
        $post->createRevision('Auto-saved before update');

        // Update slug if title changed
        if ($data['title'] !== $post->title) {
            $post->slug = BlogPost::generateSlug($data['title'], $post->id);
        }

        // Handle featured image upload
        if (!empty($_FILES['featured_image']['tmp_name'])) {
            // Delete old image
            if ($post->featured_image) {
                $this->deleteFeaturedImage($post->featured_image);
            }
            $post->featured_image = $this->uploadFeaturedImage($_FILES['featured_image']);
        }

        // Check if image should be removed
        if (!empty($data['remove_featured_image']) && $post->featured_image) {
            $this->deleteFeaturedImage($post->featured_image);
            $post->featured_image = null;
        }

        // Calculate read time
        $readTime = max(1, (int) ceil(str_word_count(strip_tags($data['content'])) / 200));

        // Track old category for count update
        $oldCategoryId = $post->category_id;

        // Update post
        $post->title = $data['title'];
        $post->excerpt = $data['excerpt'] ?? $this->generateExcerpt($data['content']);
        $post->content = $data['content'];
        $post->featured_image_alt = $data['featured_image_alt'] ?? '';
        $post->meta_title = $data['meta_title'] ?? '';
        $post->meta_description = $data['meta_description'] ?? '';
        $post->meta_keywords = $data['meta_keywords'] ?? '';
        $post->category_id = !empty($data['category_id']) ? (int) $data['category_id'] : null;
        $post->status = $data['status'] ?? 'draft';
        $post->is_featured = !empty($data['is_featured']) ? 1 : 0;
        $post->allow_comments = !empty($data['allow_comments']) ? 1 : 0;
        $post->read_time_minutes = $readTime;

        // Handle publishing
        if ($post->status === 'published' && empty($post->published_at)) {
            $post->published_at = date('Y-m-d H:i:s');
        }

        if ($post->status === 'scheduled' && !empty($data['scheduled_at'])) {
            $post->scheduled_at = $data['scheduled_at'];
        }

        $post->save();

        // Sync tags
        $tagIds = !empty($data['tags']) ? BlogTag::getIdsFromString($data['tags']) : [];
        $post->syncTags($tagIds);

        // Update category post counts
        if ($oldCategoryId) {
            BlogCategory::updatePostCount($oldCategoryId);
        }
        if ($post->category_id && $post->category_id !== $oldCategoryId) {
            BlogCategory::updatePostCount($post->category_id);
        }

        session_flash('success', 'Post updated successfully.');
        return Response::redirect('/admin/blog/' . $id . '/edit');
    }

    /**
     * Delete post
     */
    public function destroy(string|int $id): Response
    {
        $post = BlogPost::find((int) $id);

        if (!$post) {
            session_flash('error', 'Post not found.');
            return Response::redirect('/admin/blog');
        }

        $categoryId = $post->category_id;

        // Delete featured image
        if ($post->featured_image) {
            $this->deleteFeaturedImage($post->featured_image);
        }

        $post->delete();

        // Update category count
        if ($categoryId) {
            BlogCategory::updatePostCount($categoryId);
        }

        session_flash('success', 'Post deleted successfully.');
        return Response::redirect('/admin/blog');
    }

    /**
     * AI Generate post
     */
    public function aiGenerate(): Response
    {
        $data = $this->request->json();

        if (empty($data['topic'])) {
            return Response::json(['error' => 'Topic is required'], 400);
        }

        try {
            $aiService = new BlogAIService();
            $result = $aiService->generatePost(
                $data['topic'],
                $data['tone'] ?? 'professional',
                $data['length'] ?? 'medium',
                $data['keywords'] ?? ''
            );

            return Response::json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * AI Improve content
     */
    public function aiImprove(): Response
    {
        $data = $this->request->json();

        if (empty($data['content'])) {
            return Response::json(['error' => 'Content is required'], 400);
        }

        try {
            $aiService = new BlogAIService();
            $result = $aiService->improveContent(
                $data['content'],
                $data['action'] ?? 'improve'
            );

            return Response::json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate excerpt from content
     */
    private function generateExcerpt(string $content, int $length = 200): string
    {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);

        if (strlen($text) <= $length) {
            return $text;
        }

        $excerpt = substr($text, 0, $length);
        $lastSpace = strrpos($excerpt, ' ');

        if ($lastSpace !== false) {
            $excerpt = substr($excerpt, 0, $lastSpace);
        }

        return $excerpt . '...';
    }

    /**
     * Upload featured image
     */
    private function uploadFeaturedImage(array $file): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return null;
        }

        if ($file['size'] > $maxSize) {
            return null;
        }

        // Generate filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'blog_' . uniqid() . '_' . time() . '.' . $ext;
        $uploadDir = ROOT_PATH . '/public_html/uploads/blog/';
        $uploadPath = $uploadDir . $filename;

        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return null;
        }

        return 'blog/' . $filename;
    }

    /**
     * Delete featured image
     */
    private function deleteFeaturedImage(string $path): void
    {
        $fullPath = ROOT_PATH . '/public_html/uploads/' . $path;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Category Management
    |--------------------------------------------------------------------------
    */

    /**
     * List categories
     */
    public function categories(): Response
    {
        $categories = $this->db()->fetchAll(
            "SELECT c.*, COUNT(p.id) as post_count
             FROM blog_categories c
             LEFT JOIN blog_posts p ON c.id = p.category_id AND p.status = 'published'
             GROUP BY c.id
             ORDER BY c.sort_order, c.name"
        );

        return $this->view('admin/blog/categories', [
            'title' => 'Blog Categories',
            'currentPage' => 'blog-categories',
            'categories' => $categories,
        ], 'admin');
    }

    /**
     * Store category
     */
    public function storeCategory(): Response
    {
        $data = $this->request->all();

        $errors = $this->validate($data, [
            'name' => 'required|max:100',
        ]);

        if (!empty($errors)) {
            session_flash('errors', $errors);
            session_flash('old', $data);
            return Response::redirect('/admin/blog/categories');
        }

        $category = new BlogCategory();
        $category->name = $data['name'];
        $category->slug = BlogCategory::generateSlug($data['name']);
        $category->description = $data['description'] ?? '';
        $category->meta_title = $data['meta_title'] ?? '';
        $category->meta_description = $data['meta_description'] ?? '';
        $category->parent_id = !empty($data['parent_id']) ? (int) $data['parent_id'] : null;
        $category->is_active = 1;
        $category->save();

        session_flash('success', 'Category created successfully.');
        return Response::redirect('/admin/blog/categories');
    }

    /**
     * Update category
     */
    public function updateCategory(string|int $id): Response
    {
        $category = BlogCategory::find((int) $id);

        if (!$category) {
            session_flash('error', 'Category not found.');
            return Response::redirect('/admin/blog/categories');
        }

        $data = $this->request->all();

        if ($data['name'] !== $category->name) {
            $category->slug = BlogCategory::generateSlug($data['name'], $category->id);
        }

        $category->name = $data['name'];
        $category->description = $data['description'] ?? '';
        $category->meta_title = $data['meta_title'] ?? '';
        $category->meta_description = $data['meta_description'] ?? '';
        $category->is_active = !empty($data['is_active']) ? 1 : 0;
        $category->save();

        session_flash('success', 'Category updated successfully.');
        return Response::redirect('/admin/blog/categories');
    }

    /**
     * Delete category
     */
    public function destroyCategory(string|int $id): Response
    {
        $category = BlogCategory::find((int) $id);

        if (!$category) {
            session_flash('error', 'Category not found.');
            return Response::redirect('/admin/blog/categories');
        }

        // Move posts to uncategorized
        $this->db()->query(
            "UPDATE blog_posts SET category_id = NULL WHERE category_id = :id",
            ['id' => $category->id]
        );

        $category->delete();

        session_flash('success', 'Category deleted successfully.');
        return Response::redirect('/admin/blog/categories');
    }

    /*
    |--------------------------------------------------------------------------
    | Comment Management
    |--------------------------------------------------------------------------
    */

    /**
     * List comments
     */
    public function comments(): Response
    {
        $status = $this->request->input('status', 'pending');
        $comments = BlogComment::allForAdmin(['status' => $status]);

        $counts = [
            'pending' => BlogComment::pendingCount(),
            'approved' => (int) $this->db()->fetchColumn("SELECT COUNT(*) FROM blog_comments WHERE status = 'approved'"),
            'spam' => (int) $this->db()->fetchColumn("SELECT COUNT(*) FROM blog_comments WHERE status = 'spam'"),
        ];

        return $this->view('admin/blog/comments', [
            'title' => 'Blog Comments',
            'currentPage' => 'blog-comments',
            'comments' => $comments,
            'status' => $status,
            'counts' => $counts,
        ], 'admin');
    }

    /**
     * Approve comment
     */
    public function approveComment(string|int $id): Response
    {
        $comment = BlogComment::find((int) $id);

        if ($comment) {
            $comment->approve();
            session_flash('success', 'Comment approved.');
        }

        return Response::redirect('/admin/blog/comments');
    }

    /**
     * Spam comment
     */
    public function spamComment(string|int $id): Response
    {
        $comment = BlogComment::find((int) $id);

        if ($comment) {
            $comment->markSpam();
            session_flash('success', 'Comment marked as spam.');
        }

        return Response::redirect('/admin/blog/comments');
    }

    /**
     * Delete comment
     */
    public function destroyComment(string|int $id): Response
    {
        $comment = BlogComment::find((int) $id);

        if ($comment) {
            $comment->delete();
            session_flash('success', 'Comment deleted.');
        }

        return Response::redirect('/admin/blog/comments');
    }
}
