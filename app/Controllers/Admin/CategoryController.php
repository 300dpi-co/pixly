<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $db = $this->db();

        // Get all categories with parent info
        $categories = $db->fetchAll(
            "SELECT c.*, p.name as parent_name
             FROM categories c
             LEFT JOIN categories p ON c.parent_id = p.id
             ORDER BY c.sort_order, c.name"
        );

        return $this->view('admin/categories/index', [
            'title' => 'Categories',
            'currentPage' => 'categories',
            'categories' => $categories,
        ], 'admin');
    }

    public function create(): Response
    {
        $parentCategories = Category::forSelect();

        return $this->view('admin/categories/create', [
            'title' => 'Create Category',
            'currentPage' => 'categories',
            'parentCategories' => $parentCategories,
        ], 'admin');
    }

    public function store(): Response
    {
        $data = $_POST;

        // Validate
        $errors = $this->validate($data, [
            'name' => 'required|max:100',
        ]);

        if (!empty($errors)) {
            session_flash('errors', $errors);
            session_flash('old', $data);
            return Response::redirect('/admin/categories/create');
        }

        // Generate slug if not provided
        $slug = !empty($data['slug']) ? slug($data['slug']) : slug($data['name']);

        // Check unique slug
        if (Category::findBySlug($slug)) {
            $slug = $slug . '-' . time();
        }

        // Create category
        $category = new Category([
            'name' => $data['name'],
            'slug' => $slug,
            'parent_id' => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
            'description' => $data['description'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);

        $category->save();

        session_flash('success', 'Category created successfully.');
        return Response::redirect('/admin/categories');
    }

    public function edit(string|int $id): Response
    {
        $id = (int) $id;
        $category = Category::find($id);

        if (!$category) {
            session_flash('error', 'Category not found.');
            return Response::redirect('/admin/categories');
        }

        // Get parent categories excluding self and descendants
        $parentCategories = Category::forSelect($id);

        return $this->view('admin/categories/edit', [
            'title' => 'Edit Category',
            'currentPage' => 'categories',
            'category' => $category,
            'parentCategories' => $parentCategories,
        ], 'admin');
    }

    public function update(string|int $id): Response
    {
        $id = (int) $id;
        $category = Category::find($id);

        if (!$category) {
            session_flash('error', 'Category not found.');
            return Response::redirect('/admin/categories');
        }

        $data = $_POST;

        // Validate
        $errors = $this->validate($data, [
            'name' => 'required|max:100',
        ]);

        if (!empty($errors)) {
            session_flash('errors', $errors);
            session_flash('old', $data);
            return Response::redirect("/admin/categories/{$id}/edit");
        }

        // Generate slug if changed
        $slug = !empty($data['slug']) ? slug($data['slug']) : slug($data['name']);

        // Check unique slug (excluding current)
        $existingSlug = Category::findBySlug($slug);
        if ($existingSlug && $existingSlug->id != $id) {
            $slug = $slug . '-' . time();
        }

        // Prevent setting self as parent
        $parentId = !empty($data['parent_id']) ? (int) $data['parent_id'] : null;
        if ($parentId === $id) {
            $parentId = null;
        }

        // Update category
        $category->name = $data['name'];
        $category->slug = $slug;
        $category->parent_id = $parentId;
        $category->description = $data['description'] ?? null;
        $category->meta_title = $data['meta_title'] ?? null;
        $category->meta_description = $data['meta_description'] ?? null;
        $category->sort_order = (int) ($data['sort_order'] ?? 0);
        $category->is_active = !empty($data['is_active']) ? 1 : 0;

        $category->save();

        session_flash('success', 'Category updated successfully.');
        return Response::redirect('/admin/categories');
    }

    public function destroy(string|int $id): Response
    {
        $id = (int) $id;
        $category = Category::find($id);

        if (!$category) {
            session_flash('error', 'Category not found.');
            return Response::redirect('/admin/categories');
        }

        // Check for child categories
        if (!$category->canDelete()) {
            session_flash('error', 'Cannot delete category with subcategories. Delete or move them first.');
            return Response::redirect('/admin/categories');
        }

        // Remove from pivot table
        $this->db()->delete('image_categories', 'category_id = :id', ['id' => $id]);

        // Delete category
        $category->delete();

        session_flash('success', 'Category deleted successfully.');
        return Response::redirect('/admin/categories');
    }

    public function updateOrder(): Response
    {
        $order = $_POST['order'] ?? [];

        if (!is_array($order)) {
            return Response::json(['error' => 'Invalid data'], 400);
        }

        $db = $this->db();

        foreach ($order as $position => $categoryId) {
            $db->update(
                'categories',
                ['sort_order' => (int) $position],
                'id = :id',
                ['id' => (int) $categoryId]
            );
        }

        return Response::json(['success' => true]);
    }

    public function toggleActive(string|int $id): Response
    {
        $id = (int) $id;
        $category = Category::find($id);

        if (!$category) {
            return Response::json(['error' => 'Category not found'], 404);
        }

        $category->is_active = $category->is_active ? 0 : 1;
        $category->save();

        return Response::json([
            'success' => true,
            'is_active' => (bool) $category->is_active,
        ]);
    }
}
