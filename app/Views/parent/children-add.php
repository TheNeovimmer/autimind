<div class="dash-header">
  <div>
    <h1>Add Child</h1>
    <p>Add a new child profile</p>
  </div>
</div>

<form method="POST" action="/parent/children/add" class="dash-form" enctype="multipart/form-data">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

  <div class="dash-card mb-2">
    <h3>Profile Picture</h3>
    <div class="avatar-upload-wrapper">
      <div class="avatar-preview" id="avatar-preview">
        <span id="avatar-placeholder"><?= strtoupper(substr($old['name'] ?? 'C', 0, 1)) ?></span>
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
  
  <div class="form-group">
    <label for="name">Child's Name *</label>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
    <?php if (!empty($errors['name'])): ?><span class="form-error"><?= htmlspecialchars($errors['name'][0]) ?></span><?php endif; ?>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label for="age">Age</label>
      <input type="number" id="age" name="age" min="0" max="18" value="<?= htmlspecialchars($old['age'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label for="birth_date">Birth Date</label>
      <input type="date" id="birth_date" name="birth_date" value="<?= htmlspecialchars($old['birth_date'] ?? '') ?>">
    </div>
  </div>

  <div class="form-group">
    <label for="diagnosis_status">Diagnosis Status</label>
    <input type="text" id="diagnosis_status" name="diagnosis_status" placeholder="e.g. ASD Level 1, Under evaluation..." value="<?= htmlspecialchars($old['diagnosis_status'] ?? '') ?>">
  </div>

  <div class="form-group">
    <label for="notes">Notes</label>
    <textarea id="notes" name="notes" rows="3"><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
  </div>

  <div class="form-actions">
    <a href="/parent/children" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Add Child</button>
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
