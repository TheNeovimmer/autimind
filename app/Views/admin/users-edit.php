<div class="dash-header-premium">
  <div>
    <h1>Edit User</h1>
    <p><?= htmlspecialchars($user['name']) ?></p>
  </div>
  <a href="/admin/users" class="dash-btn dash-btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card">
  <?php if (!empty($errors)): ?>
    <div class="dash-alert dash-alert--danger">
      <?php foreach ($errors as $field => $msgs): ?>
        <?php foreach ($msgs as $msg): ?>
          <p><?= htmlspecialchars($msg) ?></p>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="dash-field">
      <label for="name">Name</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
    </div>

    <div class="dash-field">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
    </div>

    <div class="dash-field">
      <label for="phone">Phone</label>
      <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
    </div>

    <div class="dash-field">
      <label for="role">Role</label>
      <select id="role" name="role">
        <option value="parent" <?= $user['role'] === 'parent' ? 'selected' : '' ?>>Parent</option>
        <option value="specialist" <?= $user['role'] === 'specialist' ? 'selected' : '' ?>>Specialist</option>
        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
    </div>

    <div class="dash-field">
      <div>
        <input type="checkbox" name="is_active" value="1" id="is_active" <?= $user['is_active'] ? 'checked' : '' ?>>
        <label for="is_active">Active</label>
      </div>
    </div>

    <div class="dash-field">
      <label for="password">New Password (leave blank to keep current)</label>
      <input type="password" id="password" name="password" minlength="8">
    </div>

    <button type="submit" class="dash-btn dash-btn-primary">Update User</button>
  </form>
</div>
