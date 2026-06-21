<div class="dash-header">
  <div>
    <h1>Edit User</h1>
    <p><?= htmlspecialchars($user['name']) ?></p>
  </div>
  <a href="/admin/users" class="dash-btn dash-btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card">
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $field => $msgs): ?>
        <?php foreach ($msgs as $msg): ?>
          <p><?= htmlspecialchars($msg) ?></p>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="mb-3">
      <label for="name" class="form-label">Name</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="phone" class="form-label">Phone</label>
      <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" class="form-control">
    </div>

    <div class="mb-3">
      <label for="role" class="form-label">Role</label>
      <select id="role" name="role" class="form-select">
        <option value="parent" <?= $user['role'] === 'parent' ? 'selected' : '' ?>>Parent</option>
        <option value="specialist" <?= $user['role'] === 'specialist' ? 'selected' : '' ?>>Specialist</option>
        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
    </div>

    <div class="mb-3">
      <div class="form-check">
        <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" <?= $user['is_active'] ? 'checked' : '' ?>>
        <label for="is_active" class="form-check-label">Active</label>
      </div>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">New Password (leave blank to keep current)</label>
      <input type="password" id="password" name="password" class="form-control" minlength="8">
    </div>

    <button type="submit" class="dash-btn dash-btn-primary">Update User</button>
  </form>
</div>
