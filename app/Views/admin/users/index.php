<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Users</h1>
            <p class="text-neutral-500"><?= number_format($total) ?> total users</p>
        </div>
        <a href="/admin/users/create" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add User
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="q" value="<?= e($filters['q']) ?>" placeholder="Search users..."
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <select name="role" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All Roles</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role ?>" <?= $filters['role'] === $role ? 'selected' : '' ?>>
                        <?= ucfirst($role) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= $status ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>>
                        <?= ucfirst($status) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-neutral-100 text-neutral-700 rounded-lg hover:bg-neutral-200">
                Filter
            </button>
            <?php if ($filters['q'] || $filters['role'] || $filters['status']): ?>
                <a href="/admin/users" class="px-4 py-2 text-neutral-500 hover:text-neutral-700">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php if (empty($users)): ?>
            <div class="p-12 text-center">
                <svg class="w-12 h-12 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <p class="text-neutral-500">No users found.</p>
            </div>
        <?php else: ?>
            <table class="w-full">
                <thead class="bg-neutral-50">
                    <tr class="text-left text-sm text-neutral-500">
                        <th class="px-6 py-3 font-medium">User</th>
                        <th class="px-6 py-3 font-medium">Role</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium">Joined</th>
                        <th class="px-6 py-3 font-medium">Last Login</th>
                        <th class="px-6 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-neutral-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <?php if ($user['avatar_path']): ?>
                                    <img src="/uploads/<?= e($user['avatar_path']) ?>" alt="" class="w-10 h-10 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="w-10 h-10 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center font-semibold">
                                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <a href="/admin/users/<?= $user['id'] ?>" class="font-medium hover:text-primary-600">
                                        <?= e($user['username']) ?>
                                    </a>
                                    <p class="text-sm text-neutral-500"><?= e($user['email']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full
                                <?php
                                echo match($user['role']) {
                                    'superadmin' => 'bg-red-100 text-red-800',
                                    'admin' => 'bg-purple-100 text-purple-800',
                                    'moderator' => 'bg-blue-100 text-blue-800',
                                    default => 'bg-neutral-100 text-neutral-600'
                                };
                                ?>">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full
                                <?php
                                echo match($user['status']) {
                                    'active' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'suspended' => 'bg-orange-100 text-orange-800',
                                    'banned' => 'bg-red-100 text-red-800',
                                    default => 'bg-neutral-100 text-neutral-600'
                                };
                                ?>">
                                <?= ucfirst($user['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-neutral-500">
                            <?= date('M j, Y', strtotime($user['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-neutral-500">
                            <?= $user['last_login_at'] ? time_ago($user['last_login_at']) : 'Never' ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="/admin/users/<?= $user['id'] ?>" class="p-2 text-neutral-400 hover:text-neutral-600" title="View">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="/admin/users/<?= $user['id'] ?>/edit" class="p-2 text-neutral-400 hover:text-blue-600" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <?php if ($user['role'] !== 'superadmin'): ?>
                                <form method="POST" action="/admin/users/<?= $user['id'] ?>/delete" class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="p-2 text-neutral-400 hover:text-red-600" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($lastPage > 1): ?>
            <div class="px-6 py-4 border-t flex items-center justify-between">
                <p class="text-sm text-neutral-500">
                    Showing <?= (($page - 1) * $perPage) + 1 ?> to <?= min($page * $perPage, $total) ?> of <?= $total ?> users
                </p>
                <div class="flex gap-1">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&role=<?= e($filters['role']) ?>&status=<?= e($filters['status']) ?>&q=<?= e($filters['q']) ?>"
                           class="px-3 py-1 border rounded hover:bg-neutral-50">Prev</a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($lastPage, $page + 2); $i++): ?>
                        <a href="?page=<?= $i ?>&role=<?= e($filters['role']) ?>&status=<?= e($filters['status']) ?>&q=<?= e($filters['q']) ?>"
                           class="px-3 py-1 border rounded <?= $i === $page ? 'bg-primary-600 text-white border-primary-600' : 'hover:bg-neutral-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $lastPage): ?>
                        <a href="?page=<?= $page + 1 ?>&role=<?= e($filters['role']) ?>&status=<?= e($filters['status']) ?>&q=<?= e($filters['q']) ?>"
                           class="px-3 py-1 border rounded hover:bg-neutral-50">Next</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
