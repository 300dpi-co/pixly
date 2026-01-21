<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Models\User;

class UserController extends Controller
{
    private const ROLES = ['user', 'moderator', 'admin', 'superadmin'];
    private const STATUSES = ['pending', 'active', 'suspended', 'banned'];

    public function index(): Response
    {
        $db = $this->db();

        // Get filters
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        $search = $_GET['q'] ?? '';
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;

        // Build query
        $where = [];
        $params = [];

        if ($role && in_array($role, self::ROLES)) {
            $where[] = "role = :role";
            $params['role'] = $role;
        }

        if ($status && in_array($status, self::STATUSES)) {
            $where[] = "status = :status";
            $params['status'] = $status;
        }

        if ($search) {
            $where[] = "(username LIKE :search OR email LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count
        $total = (int) $db->fetchColumn("SELECT COUNT(*) FROM users {$whereClause}", $params);

        // Get users (LIMIT/OFFSET interpolated directly as they're integers)
        $offset = ($page - 1) * $perPage;

        $users = $db->fetchAll(
            "SELECT * FROM users {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return $this->view('admin/users/index', [
            'title' => 'Users',
            'currentPage' => 'users',
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => (int) ceil($total / $perPage),
            'roles' => self::ROLES,
            'statuses' => self::STATUSES,
            'filters' => [
                'role' => $role,
                'status' => $status,
                'q' => $search,
            ],
        ], 'admin');
    }

    public function create(): Response
    {
        return $this->view('admin/users/create', [
            'title' => 'Create User',
            'currentPage' => 'users',
            'roles' => self::ROLES,
            'statuses' => self::STATUSES,
        ], 'admin');
    }

    public function store(): Response
    {
        $data = $_POST;

        // Validate
        $errors = $this->validate($data, [
            'username' => 'required|min:3|max:50',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'role' => 'required',
            'status' => 'required',
        ]);

        if (!empty($errors)) {
            session_flash('errors', $errors);
            session_flash('old', $data);
            return Response::redirect('/admin/users/create');
        }

        // Check unique username/email
        if (User::findByUsername($data['username'])) {
            session_flash('error', 'Username already taken.');
            session_flash('old', $data);
            return Response::redirect('/admin/users/create');
        }

        if (User::findByEmail($data['email'])) {
            session_flash('error', 'Email already registered.');
            session_flash('old', $data);
            return Response::redirect('/admin/users/create');
        }

        // Create user
        $user = new User([
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_ARGON2ID),
            'role' => $data['role'],
            'status' => $data['status'],
            'bio' => $data['bio'] ?? null,
        ]);

        if (!empty($data['email_verified'])) {
            $user->email_verified_at = date('Y-m-d H:i:s');
        }

        $user->save();

        session_flash('success', 'User created successfully.');
        return Response::redirect('/admin/users');
    }

    public function show(string|int $id): Response
    {
        $id = (int) $id;
        $user = User::find($id);

        if (!$user) {
            session_flash('error', 'User not found.');
            return Response::redirect('/admin/users');
        }

        $db = $this->db();

        // Get user stats
        $stats = [
            'favorites' => (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM favorites WHERE user_id = :id",
                ['id' => $id]
            ),
            'comments' => (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM comments WHERE user_id = :id",
                ['id' => $id]
            ),
            'uploads' => (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM images WHERE uploaded_by = :id",
                ['id' => $id]
            ),
        ];

        // Get recent activity
        $recentActivity = $db->fetchAll(
            "SELECT event_type, ip_address, created_at FROM auth_logs
             WHERE user_id = :id ORDER BY created_at DESC LIMIT 10",
            ['id' => $id]
        );

        return $this->view('admin/users/show', [
            'title' => $user->username,
            'currentPage' => 'users',
            'user' => $user,
            'stats' => $stats,
            'recentActivity' => $recentActivity,
        ], 'admin');
    }

    public function edit(string|int $id): Response
    {
        $id = (int) $id;
        $user = User::find($id);

        if (!$user) {
            session_flash('error', 'User not found.');
            return Response::redirect('/admin/users');
        }

        return $this->view('admin/users/edit', [
            'title' => 'Edit User',
            'currentPage' => 'users',
            'user' => $user,
            'roles' => self::ROLES,
            'statuses' => self::STATUSES,
        ], 'admin');
    }

    public function update(string|int $id): Response
    {
        $id = (int) $id;
        $user = User::find($id);

        if (!$user) {
            session_flash('error', 'User not found.');
            return Response::redirect('/admin/users');
        }

        $data = $_POST;

        // Validate
        $errors = $this->validate($data, [
            'username' => 'required|min:3|max:50',
            'email' => 'required|email',
            'role' => 'required',
            'status' => 'required',
        ]);

        if (!empty($errors)) {
            session_flash('errors', $errors);
            session_flash('old', $data);
            return Response::redirect("/admin/users/{$id}/edit");
        }

        // Check unique username/email (excluding current user)
        $existingUsername = User::findByUsername($data['username']);
        if ($existingUsername && $existingUsername->id != $id) {
            session_flash('error', 'Username already taken.');
            session_flash('old', $data);
            return Response::redirect("/admin/users/{$id}/edit");
        }

        $existingEmail = User::findByEmail($data['email']);
        if ($existingEmail && $existingEmail->id != $id) {
            session_flash('error', 'Email already registered.');
            session_flash('old', $data);
            return Response::redirect("/admin/users/{$id}/edit");
        }

        // Update user
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->status = $data['status'];
        $user->bio = $data['bio'] ?? null;

        // Update password if provided
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 8) {
                session_flash('error', 'Password must be at least 8 characters.');
                session_flash('old', $data);
                return Response::redirect("/admin/users/{$id}/edit");
            }
            $user->password_hash = password_hash($data['password'], PASSWORD_ARGON2ID);
        }

        // Handle email verification
        if (!empty($data['email_verified']) && !$user->email_verified_at) {
            $user->email_verified_at = date('Y-m-d H:i:s');
        } elseif (empty($data['email_verified'])) {
            $user->email_verified_at = null;
        }

        $user->save();

        session_flash('success', 'User updated successfully.');
        return Response::redirect('/admin/users');
    }

    public function destroy(string|int $id): Response
    {
        $id = (int) $id;
        $user = User::find($id);

        if (!$user) {
            session_flash('error', 'User not found.');
            return Response::redirect('/admin/users');
        }

        // Prevent self-deletion
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            session_flash('error', 'You cannot delete your own account.');
            return Response::redirect('/admin/users');
        }

        // Prevent deleting superadmin
        if ($user->role === 'superadmin') {
            session_flash('error', 'Cannot delete superadmin account.');
            return Response::redirect('/admin/users');
        }

        $user->delete();

        session_flash('success', 'User deleted successfully.');
        return Response::redirect('/admin/users');
    }

    public function updateStatus(string|int $id): Response
    {
        $id = (int) $id;
        $user = User::find($id);

        if (!$user) {
            return Response::json(['error' => 'User not found'], 404);
        }

        $status = $_POST['status'] ?? '';

        if (!in_array($status, self::STATUSES)) {
            return Response::json(['error' => 'Invalid status'], 400);
        }

        // Prevent self-status change
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            return Response::json(['error' => 'Cannot change your own status'], 400);
        }

        $user->status = $status;
        $user->save();

        return Response::json(['success' => true, 'status' => $status]);
    }

    public function updateRole(string|int $id): Response
    {
        $id = (int) $id;
        $user = User::find($id);

        if (!$user) {
            return Response::json(['error' => 'User not found'], 404);
        }

        $role = $_POST['role'] ?? '';

        if (!in_array($role, self::ROLES)) {
            return Response::json(['error' => 'Invalid role'], 400);
        }

        // Prevent self-role change
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            return Response::json(['error' => 'Cannot change your own role'], 400);
        }

        $user->role = $role;
        $user->save();

        return Response::json(['success' => true, 'role' => $role]);
    }
}
