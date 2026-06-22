<div class="dash-header-premium">
  <div>
    <h1>Subscription</h1>
    <p>Manage your subscription plan</p>
  </div>
</div>

<?php if ($currentSubscription): ?>
<div class="card dash-field">
  <h3>Current Plan</h3>
  <div class="current-plan-badge">
    <span class="plan-label"><?= ucfirst(htmlspecialchars($currentSubscription['plan'])) ?></span>
    <span class="plan-status status-badge status-badge-active">Active</span>
  </div>
  <p><strong>Started:</strong> <?= htmlspecialchars($currentSubscription['started_at'] ?? 'N/A') ?></p>
  <p><strong>Expires:</strong> <?= htmlspecialchars($currentSubscription['ends_at'] ?? 'N/A') ?></p>
</div>
<?php endif; ?>

<h2 class="dash-field">Available Plans</h2>
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
        <span class="dash-btn dash-btn-outline" disabled>Current Plan</span>
      <?php else: ?>
        <form method="POST" action="/parent/subscription/upgrade" onsubmit="return confirm('Change to <?= htmlspecialchars($plan['name']) ?> plan?')">
          <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
          <input type="hidden" name="plan" value="<?= htmlspecialchars($planKey) ?>">
          <button type="submit" class="dash-btn dash-btn-primary">Upgrade</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
