<div class="dash-header">
  <div>
    <h1>Settings</h1>
    <p>Manage your account settings</p>
  </div>
</div>

<?php if (\App\Core\Session::hasFlash('success')): ?>
  <div class="flash-success"><?= \App\Core\Session::getFlash('success') ?></div>
<?php endif; ?>
<?php if (\App\Core\Session::hasFlash('error')): ?>
  <div class="flash-error"><?= \App\Core\Session::getFlash('error') ?></div>
<?php endif; ?>

<form method="POST" action="/parent/settings" class="dash-form" enctype="multipart/form-data">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

  <div class="dash-card mb-2">
    <h3>Profile Picture</h3>
    <div class="avatar-upload-wrapper">
      <div class="avatar-preview">
        <?php if (!empty($user['avatar'])): ?>
          <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
        <?php else: ?>
          <?= strtoupper(substr($user['name'], 0, 1)) ?>
        <?php endif; ?>
      </div>
      <div>
        <div class="avatar-upload-btn">
          <span class="avatar-upload-label"><i class="fas fa-camera"></i> Upload Photo</span>
          <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif">
        </div>
        <p class="avatar-filename">JPG, PNG, WebP, GIF. Max 2MB.</p>
      </div>
    </div>
    <?php if (!empty($errors['avatar'])): ?>
      <span class="form-error"><?= htmlspecialchars($errors['avatar'][0]) ?></span>
    <?php endif; ?>
  </div>

  <div class="dash-card mb-2">
    <h3>Profile Information</h3>

    <div class="form-group">
      <label for="name">Full Name *</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? $user['name']) ?>" required>
      <?php if (!empty($errors['name'])): ?><span class="form-error"><?= htmlspecialchars($errors['name'][0]) ?></span><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
      <small>Email cannot be changed.</small>
    </div>

    <div class="form-group">
      <label for="phone">Phone</label>
      <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($old['phone'] ?? $user['phone'] ?? '') ?>">
    </div>
  </div>

  <div class="dash-card mb-2">
    <h3>Change Password</h3>
    <p class="text-muted">Leave blank to keep current password.</p>

    <div class="form-group">
      <label for="password">New Password</label>
      <input type="password" id="password" name="password" minlength="8">
    </div>

    <div class="form-group">
      <label for="password_confirmation">Confirm New Password</label>
      <input type="password" id="password_confirmation" name="password_confirmation">
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn-primary">Save Settings</button>
  </div>
</form>

<script>
document.querySelector('input[name="avatar"]')?.addEventListener('change', function() {
  const label = this.closest('.avatar-upload-btn').querySelector('.avatar-upload-label');
  const filename = this.files[0]?.name;
  if (filename) {
    const span = document.createElement('span');
    span.className = 'avatar-filename';
    span.textContent = ' ' + filename;
    label.parentNode.appendChild(span);
  }
});
</script>
