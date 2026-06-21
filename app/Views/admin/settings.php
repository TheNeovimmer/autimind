<div class="dash-header">
  <div>
    <h1>Settings</h1>
    <p>Update your profile</p>
  </div>
</div>

<?php if (\App\Core\Session::hasFlash('success')): ?>
  <div class="flash-success"><?= \App\Core\Session::getFlash('success') ?></div>
<?php endif; ?>
<?php if (\App\Core\Session::hasFlash('error')): ?>
  <div class="flash-error"><?= \App\Core\Session::getFlash('error') ?></div>
<?php endif; ?>

<form method="POST" action="/admin/settings" class="dash-form" enctype="multipart/form-data">
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

  <div class="dash-card">
    <div class="form-group">
      <label for="name">Name</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
      <?php if (!empty($errors['name'])): ?><span class="form-error"><?= htmlspecialchars($errors['name'][0]) ?></span><?php endif; ?>
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
      <label for="password">New Password (leave blank to keep current)</label>
      <input type="password" id="password" name="password" minlength="8">
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-primary">Save Settings</button>
    </div>
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
