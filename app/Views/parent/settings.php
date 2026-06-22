<div class="dash-header-premium">
  <div>
    <h1>Settings</h1>
    <p>Manage your account settings</p>
  </div>
</div>

<form method="POST" action="/parent/settings"  enctype="multipart/form-data">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

  <div class="card dash-field">
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

  <div class="card dash-field">
    <h3>Profile Information</h3>

    <div class="dash-field">
    <label for="name" >Full Name *</label>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? $user['name']) ?>"
           class="<?= !empty($errors['name']) ? 'is-invalid' : '' ?>"
>
      <?php if (!empty($errors['name'])): ?><span class="invalid-feedback d-block"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($errors['name'][0]) ?></span><?php endif; ?>
    </div>

    <div class="dash-field">
      <label for="email" >Email</label>
      <input type="email" id="email"  value="<?= htmlspecialchars($user['email']) ?>" disabled>
      <small>Email cannot be changed.</small>
    </div>

    <div class="dash-field">
      <label for="phone" >Phone</label>
      <input type="text" id="phone" name="phone"  value="<?= htmlspecialchars($old['phone'] ?? $user['phone'] ?? '') ?>">
    </div>
  </div>

  <div class="card dash-field">
    <h3>Change Password</h3>
    <p class="dash-text-muted">Leave blank to keep current password.</p>

    <div class="dash-field">
      <label for="password" >New Password</label>
      <div class="password-wrapper">
        <input type="password" id="password" name="password"  minlength="8">
        <i class="fas fa-eye password-toggle" onclick="togglePassword(this)"></i>
      </div>
    </div>

    <div class="dash-field">
      <label for="password_confirmation" >Confirm New Password</label>
      <div class="password-wrapper">
        <input type="password" id="password_confirmation" name="password_confirmation" >
        <i class="fas fa-eye password-toggle" onclick="togglePassword(this)"></i>
      </div>
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="dash-btn dash-btn-primary">Save Settings</button>
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
