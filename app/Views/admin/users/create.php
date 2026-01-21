<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="/admin/users" class="text-neutral-500 hover:text-neutral-700 inline-flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Users
        </a>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold">Create User</h1>
        </div>

        <form method="POST" action="/admin/users" class="p-6 space-y-6">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Username *</label>
                    <input type="text" name="username" value="<?= e(old('username')) ?>" required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <?php if ($error = session_get_flash('errors')['username'] ?? null): ?>
                        <p class="text-red-500 text-sm mt-1"><?= e($error) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Email *</label>
                    <input type="email" name="email" value="<?= e(old('email')) ?>" required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Password *</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <p class="text-neutral-500 text-sm mt-1">Minimum 8 characters</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Role *</label>
                    <select name="role" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role ?>" <?= old('role') === $role ? 'selected' : '' ?>>
                                <?= ucfirst($role) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Status *</label>
                    <select name="status" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?= $status ?>" <?= (old('status') ?: 'active') === $status ? 'selected' : '' ?>>
                                <?= ucfirst($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Bio</label>
                <textarea name="bio" rows="3"
                          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= e(old('bio')) ?></textarea>
            </div>

            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="email_verified" value="1"
                           class="w-4 h-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-neutral-700">Mark email as verified</span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="/admin/users" class="px-4 py-2 text-neutral-700 hover:text-neutral-900">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
