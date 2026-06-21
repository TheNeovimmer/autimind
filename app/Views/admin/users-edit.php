<div class="dash-header">
  <div>
    <h1>Edit User</h1>
    <p><?= htmlspecialchars($user['name']) ?></p>
  </div>
  <a href="/admin/users" class="btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="dash-card">
  <?php if (!empty($errors)): ?>
    <div class="flash-error">
      <?php foreach ($errors as $field => $msgs): ?>
        <?php foreach ($msgs as $msg): ?>
          <p><?= htmlspecialchars($msg) ?></p>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="dash-form">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="form-group">
      <label for="name">Name</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
    </div>

    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
    </div>

    <div class="form-group">
      <label for="phone">Phone</label>
      <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="role">Role</label>
      <select id="role" name="role">
        <option value="parent" <?= $user['role'] === 'parent' ? 'selected' : '' ?>>Parent</option>
        <option value="specialist" <?= $user['role'] === 'specialist' ? 'selected' : '' ?>>Specialist</option>
        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
    </div>

    <div class="form-group">
      <label class="checkbox-label">
        <input type="checkbox" name="is_active" value="1" <?= $user['is_active'] ? 'checked' : '' ?>>
        Active
      </label>
    </div>

    <div class="form-group">
      <label for="password">New Password (leave blank to keep current)</label>
      <input type="password" id="password" name="password" minlength="8">
    </div>

    <button type="submit" class="btn-primary">Update User</button>
  </form>
</div>
