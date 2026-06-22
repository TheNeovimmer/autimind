<div class="dash-header-premium">
  <div>
    <h1>Add User</h1>
    <p>Create a new platform user</p>
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
      <input type="text" id="name" name="name" required>
    </div>

    <div class="dash-field">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>
    </div>

    <div class="dash-field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required minlength="8">
    </div>

    <div class="dash-field">
      <label for="role">Role</label>
      <select id="role" name="role" required>
        <option value="parent">Parent</option>
        <option value="specialist">Specialist</option>
        <option value="admin">Admin</option>
      </select>
    </div>

    <button type="submit" class="dash-btn dash-btn-primary">Create User</button>
  </form>
</div>
