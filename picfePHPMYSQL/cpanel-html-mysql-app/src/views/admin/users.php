<h1 class="text-2xl font-semibold mb-6">User Management</h1>
<div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
  <div class="mb-4 flex justify-between items-center">
    <div class="text-sm text-gray-400">All Users</div>
    <input type="text" placeholder="Search users..." class="bg-gray-700 border border-gray-600 rounded p-2 text-sm text-white" />
  </div>

  <?php if (!empty($_SESSION['admin_flash'])): ?>
    <div class="mb-4 p-3 rounded bg-gray-700 text-white text-sm"><?php echo htmlspecialchars($_SESSION['admin_flash'] ?? ''); ?></div>
    <?php unset($_SESSION['admin_flash']); ?>
  <?php endif; ?>

  <table class="w-full text-left text-sm">
    <thead class="text-gray-400">
      <tr>
        <th>User</th>
        <th>Role</th>
        <th>Credits</th>
        <th>Status</th>
        <th>Joined</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr class="border-t border-gray-700">
        <td class="py-3">
          <div class="text-sm font-semibold"><?php echo htmlspecialchars($u['full_name'] ?? ''); ?></div>
          <div class="text-xs text-gray-400"><?php echo htmlspecialchars($u['email'] ?? ''); ?></div>
        </td>
        <td><?php echo $u['is_admin'] ? '<span class="px-2 py-1 rounded bg-purple-600 text-white text-xs">Admin</span>' : '<span class="px-2 py-1 rounded bg-gray-700 text-gray-200 text-xs">User</span>'; ?></td>
        <td><?php echo htmlspecialchars((string)($u['credits'] ?? 0)); ?></td>
        <td><span class="text-yellow-300">Active</span></td>
        <td><?php echo htmlspecialchars(date('n/j/Y', strtotime($u['created_at'] ?? 'now'))); ?></td>
        <td class="text-sm">
          <!-- view -->
          <a href="/admin/users?id=<?php echo $u['id']; ?>" class="mr-2">🔍</a>
          <!-- edit -->
          <a href="/admin/users?edit=<?php echo $u['id']; ?>" class="mr-2">✏️</a>
          <!-- delete form -->
          <form style="display:inline" method="post" action="/admin/users" onsubmit="return confirm('Delete this user?');">
            <input type="hidden" name="action" value="delete" />
            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>" />
            <button type="submit" class="text-red-400 ml-2">🗑️</button>
          </form>
          <!-- toggle admin -->
          <form style="display:inline" method="post" action="/admin/users">
            <input type="hidden" name="action" value="toggle_admin" />
            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>" />
            <button type="submit" class="ml-2 text-sm px-2 py-1 bg-gray-700 rounded text-white"><?php echo $u['is_admin'] ? 'Demote' : 'Promote'; ?></button>
          </form>
          <!-- add credits -->
          <form style="display:inline" method="post" action="/admin/users" class="ml-2">
            <input type="hidden" name="action" value="add_credits" />
            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>" />
            <input type="number" name="amount" value="10" class="w-16 bg-gray-800 text-white rounded p-1 text-sm inline" />
            <button type="submit" class="ml-1 text-sm px-2 py-1 bg-green-600 rounded text-white">Add</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if (!empty($edit_user)): ?>
  <div class="mt-6 bg-gray-800 rounded p-6 border border-gray-700">
    <h2 class="text-lg font-semibold mb-3">Edit User</h2>
    <form method="post" action="/admin/users">
      <input type="hidden" name="action" value="update" />
      <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>" />
      <label class="block mb-2">Full name
        <input name="full_name" value="<?php echo htmlspecialchars($edit_user['full_name'] ?? ''); ?>" class="w-full bg-gray-900 border border-gray-700 rounded p-2 text-sm" />
      </label>
      <label class="block mb-2">Email
        <input name="email" value="<?php echo htmlspecialchars($edit_user['email'] ?? ''); ?>" class="w-full bg-gray-900 border border-gray-700 rounded p-2 text-sm" />
      </label>
      <label class="block mb-4">Credits
        <input name="credits" type="number" value="<?php echo htmlspecialchars((string)($edit_user['credits'] ?? 0)); ?>" class="w-32 bg-gray-900 border border-gray-700 rounded p-2 text-sm" />
      </label>
      <div>
        <button class="px-4 py-2 bg-blue-600 rounded text-white">Save</button>
        <a href="/admin/users" class="ml-3 text-sm text-gray-400">Cancel</a>
      </div>
    </form>
  </div>
<?php endif; ?>
