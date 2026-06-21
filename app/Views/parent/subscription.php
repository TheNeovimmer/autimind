<div class="dash-header">
  <div>
    <h1>Subscription</h1>
    <p>Manage your subscription plan</p>
  </div>
</div>

<?php if ($currentSubscription): ?>
<div class="card mb-2">
  <h3>Current Plan</h3>
  <div class="current-plan-badge">
    <span class="plan-label"><?= ucfirst(htmlspecialchars($currentSubscription['plan'])) ?></span>
    <span class="plan-status badge bg-success">Active</span>
  </div>
  <p><strong>Started:</strong> <?= htmlspecialchars($currentSubscription['started_at'] ?? 'N/A') ?></p>
  <p><strong>Expires:</strong> <?= htmlspecialchars($currentSubscription['ends_at'] ?? 'N/A') ?></p>
</div>
<?php endif; ?>

<h2 class="mb-2">Available Plans</h2>
<div class="pricing-grid">
  <?php foreach ($plans as $planKey => $plan): ?>
    <div class="pricing-card <?= $currentSubscription && $currentSubscription['plan'] === $planKey ? 'pricing-current' : '' ?>">
      <h3><?= htmlspecialchars($plan['name']) ?></h3>
      <p class="pricing-price"><?= htmlspecialchars($plan['price']) ?></p>
      <ul class="pricing-features">
        <?php foreach ($plan['features'] as $feature): ?>
          <li><i class="fas fa-check"></i> <?= htmlspecialchars($feature) ?></li>
        <?php endforeach; ?>
      </ul>
      <?php if ($currentSubscription && $currentSubscription['plan'] === $planKey): ?>
        <span class="btn btn-outline-secondary" disabled>Current Plan</span>
      <?php else: ?>
        <form method="POST" action="/parent/subscription/upgrade" onsubmit="return confirm('Change to <?= htmlspecialchars($plan['name']) ?> plan?')">
          <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
          <input type="hidden" name="plan" value="<?= htmlspecialchars($planKey) ?>">
          <button type="submit" class="btn btn-primary">Upgrade</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
