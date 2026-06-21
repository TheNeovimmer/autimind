<div class="dash-header">
  <div>
    <h1>Settings</h1>
    <p>Update your profile</p>
  </div>
</div>

<form method="POST" action="/admin/settings" class="" enctype="multipart/form-data">
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
    <h3>Profile</h3>
    <div class="mb-3">
      <label for="name">Name</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>"
             class="<?= !empty($errors['name']) ? 'is-invalid' : '' ?>">
      <?php if (!empty($errors['name'])): ?><span class="invalid-feedback d-block"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($errors['name'][0]) ?></span><?php endif; ?>
    </div>

    <div class="mb-3">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
             class="<?= !empty($errors['email']) ? 'is-invalid' : '' ?>">
    </div>

    <div class="mb-3">
      <label for="phone">Phone</label>
      <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
    </div>

    <div class="mb-3">
      <label for="password">New Password (leave blank to keep current)</label>
      <div class="password-wrapper">
        <input type="password" id="password" name="password" minlength="8">
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
  const filename = this.files[0]?.name;
  if (filename) {
    const span = document.createElement('span');
    span.className = 'avatar-filename';
    span.textContent = ' ' + filename;
    this.closest('.avatar-upload-btn').querySelector('.avatar-upload-label').parentNode.appendChild(span);
  }
});
</script>
