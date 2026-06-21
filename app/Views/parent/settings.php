<div class="dash-header">
  <div>
    <h1>Settings</h1>
    <p>Manage your account settings</p>
  </div>
</div>

<form method="POST" action="/parent/settings"  enctype="multipart/form-data">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

  <div class="card mb-2">
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
      <span class="invalid-feedback d-block"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($errors['avatar'][0]) ?></span>
    <?php endif; ?>
  </div>

  <div class="card mb-2">
    <h3>Profile Information</h3>

    <div class="mb-3">
    <label for="name" class="form-label">Full Name *</label>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? $user['name']) ?>"
           class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>">
      <?php if (!empty($errors['name'])): ?><span class="invalid-feedback d-block"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($errors['name'][0]) ?></span><?php endif; ?>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" id="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
      <small>Email cannot be changed.</small>
    </div>

    <div class="mb-3">
      <label for="phone" class="form-label">Phone</label>
      <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($old['phone'] ?? $user['phone'] ?? '') ?>">
    </div>
  </div>

  <div class="card mb-2">
    <h3>Change Password</h3>
    <p class="text-muted">Leave blank to keep current password.</p>

    <div class="mb-3">
      <label for="password" class="form-label">New Password</label>
      <div class="password-wrapper">
        <input type="password" id="password" name="password" class="form-control" minlength="8">
        <i class="fas fa-eye password-toggle" onclick="togglePassword(this)"></i>
      </div>
    </div>

    <div class="mb-3">
      <label for="password_confirmation" class="form-label">Confirm New Password</label>
      <div class="password-wrapper">
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
        <i class="fas fa-eye password-toggle" onclick="togglePassword(this)"></i>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2 align-items-center">
    <button type="submit" class="btn btn-primary">Save Settings</button>
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
