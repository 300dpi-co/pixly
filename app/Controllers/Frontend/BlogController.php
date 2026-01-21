<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\Response;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\BlogComment;
use App\Models\Like;
use App\Models\Save;

/**
 * Frontend Blog Controller
 */
class BlogController extends Controller
{
    /**
     * Blog index - list all posts
     */
    public function index(): Response
    {
        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = (int) setting('blog_posts_per_page', 12);
        $offset = ($page - 1) * $perPage;

        $posts = BlogPost::published($perPage, $offset);
        $total = BlogPost::publishedCount();
        $totalPages = (int) ceil($total / $perPage);

        // Get featured posts for hero
        $featuredPosts = BlogPost::featured(3);

        // Get categories and popular tags for sidebar
        $categories = BlogCategory::withPostCounts();
        $popularTags = BlogTag::popular(15);

        return $this->view('frontend/blog/index', [
            'title' => 'Blog',
            'meta_description' => 'Read our latest articles, tutorials, and insights.',
            'posts' => $posts,
            'featuredPosts' => $featuredPosts,
            'categories' => $categories,
            'popularTags' => $popularTags,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    /**
     * Single blog post
     */
    public function show(string $slug): Response
    {
        $post = BlogPost::findBySlug($slug);

        if (!$post || $post->status !== 'published') {
            return $this->notFound();
        }

        // Increment view count
        $post->incrementViews();

        // Get related data
        $category = $post->category();
        $tags = $post->tags();
        $relatedPosts = $post->relatedPosts(4);
        $comments = $post->allow_comments ? $post->comments() : [];

        // Check if user/guest has liked (likes work for guests too)
        $userId = $_SESSION['user_id'] ?? null;
        $isLiked = Like::hasLiked($userId, 'blog_post', $post->id);

        // Saves only for logged-in users
        $isSaved = false;
        if ($this->isAuthenticated()) {
            $isSaved = Save::hasSaved($_SESSION['user_id'], 'blog_post', $post->id);
        }

        // Get previous and next posts
        $prevPost = $this->db()->fetch(
            "SELECT slug, title FROM blog_posts
             WHERE status = 'published' AND published_at < :date
             ORDER BY published_at DESC LIMIT 1",
            ['date' => $post->published_at]
        );

        $nextPost = $this->db()->fetch(
            "SELECT slug, title FROM blog_posts
             WHERE status = 'published' AND published_at > :date
             ORDER BY published_at ASC LIMIT 1",
            ['date' => $post->published_at]
        );

        // Schema.org data
        $schemaData = $post->getSchemaData(config('app.url'));

        return $this->view('frontend/blog/show', [
            'title' => $post->getSeoTitle(),
            'meta_description' => $post->getSeoDescription(),
            'og_image' => $post->featured_image ? config('app.url') . '/uploads/' . $post->featured_image : null,
            'post' => $post,
            'category' => $category,
            'tags' => $tags,
            'relatedPosts' => $relatedPosts,
            'comments' => $comments,
            'prevPost' => $prevPost,
            'nextPost' => $nextPost,
            'schemaData' => $schemaData,
            'isLiked' => $isLiked,
            'isSaved' => $isSaved,
            'isLoggedIn' => $this->isAuthenticated(),
        ]);
    }

    /**
     * Posts by category
     */
    public function category(string $slug): Response
    {
        $category = BlogCategory::findBySlug($slug);

        if (!$category || !$category->is_active) {
            return $this->notFound();
        }

        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = (int) setting('blog_posts_per_page', 12);
        $offset = ($page - 1) * $perPage;

        $posts = BlogPost::byCategory($category->id, $perPage, $offset);
        $total = $category->postsCount();
        $totalPages = (int) ceil($total / $perPage);

        // Get all categories for sidebar
        $categories = BlogCategory::withPostCounts();
        $popularTags = BlogTag::popular(15);

        return $this->view('frontend/blog/category', [
            'title' => $category->getSeoTitle(),
            'meta_description' => $category->getSeoDescription(),
            'category' => $category,
            'posts' => $posts,
            'categories' => $categories,
            'popularTags' => $popularTags,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    /**
     * Posts by tag
     */
    public function tag(string $slug): Response
    {
        $tag = BlogTag::findBySlug($slug);

        if (!$tag) {
            return $this->notFound();
        }

        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = (int) setting('blog_posts_per_page', 12);
        $offset = ($page - 1) * $perPage;

        $posts = BlogPost::byTag($tag->id, $perPage, $offset);
        $total = $tag->postsCount();
        $totalPages = (int) ceil($total / $perPage);

        // Get categories and tags for sidebar
        $categories = BlogCategory::withPostCounts();
        $popularTags = BlogTag::popular(15);

        return $this->view('frontend/blog/tag', [
            'title' => 'Posts tagged: ' . $tag->name,
            'meta_description' => "Browse all blog posts tagged with {$tag->name}",
            'tag' => $tag,
            'posts' => $posts,
            'categories' => $categories,
            'popularTags' => $popularTags,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    /**
     * Search blog posts
     */
    public function search(): Response
    {
        $query = trim($this->request->input('q', ''));

        if (empty($query)) {
            return Response::redirect('/blog');
        }

        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = (int) setting('blog_posts_per_page', 12);
        $offset = ($page - 1) * $perPage;

        $posts = BlogPost::search($query, $perPage, $offset);

        // Get total from similar query
        $total = count(BlogPost::search($query, 1000, 0));
        $totalPages = (int) ceil($total / $perPage);

        // Get categories and tags for sidebar
        $categories = BlogCategory::withPostCounts();
        $popularTags = BlogTag::popular(15);

        return $this->view('frontend/blog/search', [
            'title' => 'Search: ' . $query,
            'meta_description' => "Search results for \"{$query}\"",
            'query' => $query,
            'posts' => $posts,
            'categories' => $categories,
            'popularTags' => $popularTags,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    /**
     * Post a comment
     */
    public function comment(string $slug): Response
    {
        $post = BlogPost::findBySlug($slug);

        if (!$post || !$post->allow_comments) {
            return Response::json(['error' => 'Comments are disabled'], 403);
        }

        $data = $this->request->all();

        // Validate
        $errors = [];
        if (empty($data['content'])) {
            $errors['content'] = 'Comment is required';
        }

        // Guest validation
        if (!isset($_SESSION['user_id'])) {
            if (empty($data['guest_name'])) {
                $errors['guest_name'] = 'Name is required';
            }
            if (empty($data['guest_email']) || !filter_var($data['guest_email'], FILTER_VALIDATE_EMAIL)) {
                $errors['guest_email'] = 'Valid email is required';
            }
        }

        if (!empty($errors)) {
            session_flash('errors', $errors);
            session_flash('old', $data);
            return Response::redirect('/blog/' . $slug . '#comments');
        }

        // Rate limit check
        $recentComment = $this->db()->fetch(
            "SELECT id FROM blog_comments WHERE ip_address = :ip AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
            ['ip' => $_SERVER['REMOTE_ADDR'] ?? '']
        );

        if ($recentComment) {
            session_flash('error', 'Please wait before posting another comment.');
            return Response::redirect('/blog/' . $slug . '#comments');
        }

        // Create comment
        $comment = new BlogComment();
        $comment->post_id = $post->id;
        $comment->user_id = $_SESSION['user_id'] ?? null;
        $comment->parent_id = !empty($data['parent_id']) ? (int) $data['parent_id'] : null;
        $comment->guest_name = $data['guest_name'] ?? null;
        $comment->guest_email = $data['guest_email'] ?? null;
        $comment->guest_website = $data['guest_website'] ?? null;
        $comment->content = strip_tags($data['content']);
        $comment->status = setting('blog_comments_moderation', '1') === '1' ? 'pending' : 'approved';
        $comment->ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $comment->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $comment->save();

        if ($comment->status === 'pending') {
            session_flash('success', 'Your comment has been submitted and is awaiting moderation.');
        } else {
            session_flash('success', 'Your comment has been posted.');
        }

        return Response::redirect('/blog/' . $slug . '#comments');
    }
}
