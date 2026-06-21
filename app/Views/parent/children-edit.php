<div class="dash-header">
  <div>
    <h1>Edit Child</h1>
    <p>Update <?= htmlspecialchars($child['name']) ?>'s profile</p>
  </div>
</div>

<form method="POST" action="/parent/children/<?= (int)$child['id'] ?>/edit"  enctype="multipart/form-data">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

  <div class="card mb-2">
    <h3>Profile Picture</h3>
    <div class="avatar-upload-wrapper">
      <div class="avatar-preview" id="avatar-preview">
        <?php if (!empty($child['avatar'])): ?>
          <img src="<?= htmlspecialchars($child['avatar']) ?>" alt="Avatar">
        <?php else: ?>
          <span id="avatar-placeholder"><?= strtoupper(substr($child['name'], 0, 1)) ?></span>
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
  </div>
  
  <div class="mb-3">
    <label for="name" class="form-label">Child's Name *</label>
    <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($old['name'] ?? $child['name']) ?>" required>
    <?php if (!empty($errors['name'])): ?><span class="invalid-feedback d-block"><?= htmlspecialchars($errors['name'][0]) ?></span><?php endif; ?>
  </div>

  <div class="row row-cols-1 row-cols-md-2 g-3">
    <div class="mb-3">
      <label for="age" class="form-label">Age</label>
      <input type="number" id="age" name="age" class="form-control" min="0" max="18" value="<?= htmlspecialchars($old['age'] ?? $child['age']) ?>">
    </div>
    <div class="mb-3">
      <label for="birth_date" class="form-label">Birth Date</label>
      <input type="date" id="birth_date" name="birth_date" class="form-control" value="<?= htmlspecialchars($old['birth_date'] ?? $child['birth_date']) ?>">
    </div>
  </div>

  <div class="mb-3">
    <label for="diagnosis_status" class="form-label">Diagnosis Status</label>
    <input type="text" id="diagnosis_status" name="diagnosis_status" class="form-control" value="<?= htmlspecialchars($old['diagnosis_status'] ?? $child['diagnosis_status']) ?>">
  </div>

  <div class="mb-3">
    <label for="notes" class="form-label">Notes</label>
    <textarea id="notes" name="notes" rows="3" class="form-control"><?= htmlspecialchars($old['notes'] ?? $child['notes']) ?></textarea>
  </div>

  <div class="d-flex gap-2 align-items-center">
    <a href="/parent/children" class="dash-btn dash-btn-outline">Cancel</a>
    <button type="submit" class="dash-btn dash-btn-primary">Update Child</button>
  </div>
</form>

<script>
document.querySelector('input[name="avatar"]')?.addEventListener('change', function() {
  const file = this.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    const preview = document.getElementById('avatar-preview');
    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
  };
  reader.readAsDataURL(file);
});
</script>
