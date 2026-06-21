<div class="dash-header">
  <div>
    <h1>Add Subscription</h1>
    <p>Create a new subscription for a parent</p>
  </div>
  <a href="/admin/subscriptions" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card">
  <form method="POST" class="">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="mb-3">
      <label for="user_id" class="form-label">Parent</label>
      <select id="user_id" name="user_id" class="form-select" required>
        <option value="">Select parent...</option>
        <?php foreach ($parents as $p): ?>
          <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['email']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="plan" class="form-label">Plan</label>
      <select id="plan" name="plan" class="form-select" required>
        <option value="standard">Standard</option>
        <option value="premium">Premium</option>
        <option value="family">Family</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="status" class="form-label">Status</label>
      <select id="status" name="status" class="form-select" required>
        <option value="active">Active</option>
        <option value="cancelled">Cancelled</option>
        <option value="expired">Expired</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="ends_at" class="form-label">End Date</label>
      <input type="date" id="ends_at" name="ends_at" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">Create Subscription</button>
  </form>
</div>
