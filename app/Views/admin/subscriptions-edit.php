<div class="dash-header">
  <div>
    <h1>Edit Subscription</h1>
    <p><?= htmlspecialchars($subscription['user_name']) ?> - <?= ucfirst(htmlspecialchars($subscription['plan'])) ?></p>
  </div>
  <a href="/admin/subscriptions" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card">
  <form method="POST" class="">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="mb-3">
      <label for="plan" class="form-label">Plan</label>
      <select id="plan" name="plan" class="form-select" required>
        <option value="standard" <?= $subscription['plan'] === 'standard' ? 'selected' : '' ?>>Standard</option>
        <option value="premium" <?= $subscription['plan'] === 'premium' ? 'selected' : '' ?>>Premium</option>
        <option value="family" <?= $subscription['plan'] === 'family' ? 'selected' : '' ?>>Family</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="status" class="form-label">Status</label>
      <select id="status" name="status" class="form-select" required>
        <option value="active" <?= $subscription['status'] === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="cancelled" <?= $subscription['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        <option value="expired" <?= $subscription['status'] === 'expired' ? 'selected' : '' ?>>Expired</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="ends_at" class="form-label">End Date</label>
      <input type="date" id="ends_at" name="ends_at" value="<?= htmlspecialchars($subscription['ends_at'] ?? '') ?>" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">Update Subscription</button>
  </form>
</div>
