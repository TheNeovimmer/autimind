<div class="dash-header-premium">
  <div>
    <h1><?= htmlspecialchars($child['name']) ?></h1>
    <p>Patient details</p>
  </div>
  <a href="/specialist/messages/thread/<?= (int)$parent['id'] ?>" class="dash-btn dash-btn-outline"><i class="fas fa-envelope"></i> Message Parent</a>
</div>

<div class="dash-grid-2">
  <div class="card">
    <h3>Child Information</h3>
    <p><strong>Age:</strong> <?= $child['age'] ? (int)$child['age'] . ' yrs' : '-' ?></p>
    <p><strong>Birth Date:</strong> <?= htmlspecialchars($child['birth_date'] ?? '-') ?></p>
    <p><strong>Diagnosis:</strong> <?= htmlspecialchars($child['diagnosis_status'] ?? 'Not specified') ?></p>
    <p><strong>Notes:</strong> <?= nl2br(htmlspecialchars($child['notes'] ?? 'None')) ?></p>
  </div>

  <div class="card">
    <h3>Parent Information</h3>
    <p><strong>Name:</strong> <?= htmlspecialchars($parent['name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($parent['email']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($parent['phone'] ?? '-') ?></p>
  </div>
</div>

<div class="card mb-2">
  <h3>Add Observation</h3>
  <form method="POST" action="/specialist/patients/<?= (int)$child['id'] ?>/notes" class="">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <div class="dash-field">
      <label for="notes">Observation Notes</label>
      <textarea id="notes" name="notes" class="" rows="5"><?= htmlspecialchars($child['notes'] ?? '') ?></textarea>
    </div>
    <div class="form-actions">
      <button type="submit" class="dash-btn dash-btn-primary">Save Observation</button>
    </div>
  </form>
</div>

<div class="card mb-2">
  <h3>Appointment History</h3>
  <?php if (!empty($appointments)): ?>
    <table class="dash-table">
      <thead><tr><th>Date</th><th>Time</th><th>Status</th><th>Notes</th></tr></thead>
      <tbody>
        <?php foreach ($appointments as $apt): ?>
          <tr>
            <td><?= htmlspecialchars($apt['date']) ?></td>
            <td><?= htmlspecialchars(substr($apt['time'], 0, 5)) ?></td>
            <td><span class="<?= htmlspecialchars(['pending'=>'status-badge status-badge-pending','confirmed'=>'status-badge status-badge-active','active'=>'status-badge status-badge-active','completed'=>'status-badge status-badge-completed','cancelled'=>'status-badge status-badge-cancelled','expired'=>'status-badge status-badge-cancelled'][$apt['status']] ?? 'status-badge') ?>"><?= ucfirst(htmlspecialchars($apt['status'])) ?></span></td>
            <td><?= htmlspecialchars($apt['notes'] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="dash-text-muted py-2">No appointments yet.</p>
  <?php endif; ?>
</div>

<?php if (!empty($quizHistory)): ?>
<div class="card">
  <h3>Screening History</h3>
  <table class="dash-table">
    <thead><tr><th>Date</th><th>Score</th><th>Risk Level</th><th>Category Breakdown</th></tr></thead>
    <tbody>
      <?php foreach ($quizHistory as $qh): ?>
        <tr>
          <td><?= htmlspecialchars($qh['completed_at']) ?></td>
          <td><?= (int)$qh['total_score'] ?>/50</td>
          <td><span class="risk-<?= htmlspecialchars($qh['risk_level']) ?>"><?= ucfirst(htmlspecialchars($qh['risk_level'])) ?></span></td>
          <td>
            <?php if (!empty($quizBreakdowns[$qh['id']])): ?>
              <div class="quiz-breakdown">
                <?php $cats = ['social_communication' => 'Social', 'behavior' => 'Behavior', 'sensory' => 'Sensory', 'developmental' => 'Developmental']; ?>
                <?php foreach ($quizBreakdowns[$qh['id']] as $cat): ?>
                  <span class="status-badge status-badge-completed">
                    <?= htmlspecialchars($cats[$cat['category']] ?? $cat['category']) ?>: <?= (int)$cat['score'] ?>
                  </span>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <span class="dash-text-muted">-</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
