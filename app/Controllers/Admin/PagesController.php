<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Page;

/**
 * Pages Controller (Admin)
 *
 * Manages editable content pages
 */
class PagesController extends Controller
{
    /**
     * List all pages
     */
    public function index(): Response
    {
        $pages = Page::allForAdmin();

        return $this->view('admin/pages/index', [
            'title' => 'Pages',
            'pages' => $pages,
        ], 'admin');
    }

    /**
     * Create page form
     */
    public function create(): Response
    {
        return $this->view('admin/pages/form', [
            'title' => 'Create Page',
            'page' => null,
        ], 'admin');
    }

    /**
     * Store new page
     */
    public function store(): Response
    {
        $data = $this->request->all();

        // Validate
        $errors = $this->validatePage($data);
        if (!empty($errors)) {
            session_flash('errors', $errors);
            session_flash('old', $data);
            return Response::redirect('/admin/pages/create');
        }

        // Generate slug if empty
        $slug = !empty($data['slug']) ? slug($data['slug']) : slug($data['title']);

        // Check slug uniqueness
        if (Page::slugExists($slug)) {
            session_flash('error', 'A page with this slug already exists.');
            session_flash('old', $data);
            return Response::redirect('/admin/pages/create');
        }

        $this->db()->insert('pages', [
            'slug' => $slug,
            'title' => $data['title'],
            'content' => $data['content'] ?? '',
            'meta_description' => $data['meta_description'] ?? '',
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'is_system' => 0,
            'show_in_footer' => isset($data['show_in_footer']) ? 1 : 0,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        session_flash('success', 'Page created successfully.');
        return Response::redirect('/admin/pages');
    }

    /**
     * Edit page form
     */
    public function edit(int $id): Response
    {
        $page = $this->db()->fetch("SELECT * FROM pages WHERE id = :id", ['id' => $id]);

        if (!$page) {
            return $this->notFound();
        }

        return $this->view('admin/pages/form', [
            'title' => 'Edit Page',
            'page' => $page,
        ], 'admin');
    }

    /**
     * Update page
     */
    public function update(int $id): Response
    {
        $page = $this->db()->fetch("SELECT * FROM pages WHERE id = :id", ['id' => $id]);

        if (!$page) {
            return $this->notFound();
        }

        $data = $this->request->all();

        // Validate
        $errors = $this->validatePage($data);
        if (!empty($errors)) {
            session_flash('errors', $errors);
            session_flash('old', $data);
            return Response::redirect('/admin/pages/' . $id . '/edit');
        }

        // Generate slug if empty (but don't change system page slugs)
        if ($page['is_system']) {
            $slug = $page['slug']; // Keep original slug for system pages
        } else {
            $slug = !empty($data['slug']) ? slug($data['slug']) : slug($data['title']);
        }

        // Check slug uniqueness
        if (Page::slugExists($slug, $id)) {
            session_flash('error', 'A page with this slug already exists.');
            session_flash('old', $data);
            return Response::redirect('/admin/pages/' . $id . '/edit');
        }

        $this->db()->query(
            "UPDATE pages SET
                slug = :slug,
                title = :title,
                content = :content,
                meta_description = :meta_description,
                is_active = :is_active,
                show_in_footer = :show_in_footer,
                sort_order = :sort_order
             WHERE id = :id",
            [
                'slug' => $slug,
                'title' => $data['title'],
                'content' => $data['content'] ?? '',
                'meta_description' => $data['meta_description'] ?? '',
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'show_in_footer' => isset($data['show_in_footer']) ? 1 : 0,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'id' => $id,
            ]
        );

        session_flash('success', 'Page updated successfully.');
        return Response::redirect('/admin/pages');
    }

    /**
     * Delete page
     */
    public function delete(int $id): Response
    {
        $page = $this->db()->fetch("SELECT * FROM pages WHERE id = :id", ['id' => $id]);

        if (!$page) {
            return $this->notFound();
        }

        // Don't allow deleting system pages
        if ($page['is_system']) {
            session_flash('error', 'System pages cannot be deleted. You can deactivate them instead.');
            return Response::redirect('/admin/pages');
        }

        $this->db()->delete('pages', 'id = :id', ['id' => $id]);

        session_flash('success', 'Page deleted successfully.');
        return Response::redirect('/admin/pages');
    }

    /**
     * Toggle page active status
     */
    public function toggle(int $id): Response
    {
        $this->db()->query(
            "UPDATE pages SET is_active = NOT is_active WHERE id = :id",
            ['id' => $id]
        );

        return Response::json(['success' => true]);
    }

    /**
     * Validate page data
     */
    protected function validatePage(array $data): array
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors['title'] = 'Title is required.';
        } elseif (strlen($data['title']) > 200) {
            $errors['title'] = 'Title must be 200 characters or less.';
        }

        if (!empty($data['slug']) && !preg_match('/^[a-z0-9-]+$/', $data['slug'])) {
            $errors['slug'] = 'Slug can only contain lowercase letters, numbers, and hyphens.';
        }

        if (!empty($data['meta_description']) && strlen($data['meta_description']) > 300) {
            $errors['meta_description'] = 'Meta description must be 300 characters or less.';
        }

        return $errors;
    }
}
