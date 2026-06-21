<div class="dash-header">
  <div>
    <h1>Progress Tracking</h1>
    <p>Monitor your child's development</p>
  </div>
</div>

<?php if (!empty($children)): ?>
<div class="card mb-2">
  <div class="mb-3">
    <label for="child-select" class="form-label">Select Child:</label>
    <select id="child-select" class="form-select" onchange="window.location.href='/parent/progress?child_id=' + this.value">
      <?php foreach ($children as $child): ?>
        <option value="<?= (int)$child['id'] ?>" <?= $child['id'] === $selectedChildId ? 'selected' : '' ?>>
          <?= htmlspecialchars($child['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<?php if ($selectedChildId): ?>
  <div class="row row-cols-1 row-cols-md-2 g-3 mb-3">
    <div class="card">
      <h3><i class="fas fa-clipboard-list"></i> Quiz History</h3>
      <?php if (!empty($quizHistory)): ?>
        <table class="table table-hover align-middle mb-0 small">
          <thead><tr><th>Date</th><th>Score</th><th>Risk</th></tr></thead>
          <tbody>
            <?php foreach ($quizHistory as $qh): ?>
              <tr>
                <td><?= htmlspecialchars($qh['completed_at']) ?></td>
                <td><?= (int)$qh['total_score'] ?>/50</td>
                <td><span class="risk-<?= htmlspecialchars($qh['risk_level']) ?>"><?= ucfirst(htmlspecialchars($qh['risk_level'])) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="text-muted py-2">No quiz data yet.</p>
      <?php endif; ?>
    </div>

    <div class="card">
      <h3><i class="fas fa-gamepad"></i> Recent Activity</h3>
      <?php if (!empty($progressData)): ?>
        <table class="table table-hover align-middle mb-0 small">
          <thead><tr><th>Activity</th><th>Category</th><th>Score</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($progressData as $pd): ?>
              <tr>
                <td><?= htmlspecialchars($pd['title']) ?></td>
                <td><?= ucfirst(htmlspecialchars($pd['category'])) ?></td>
                <td><?= $pd['score'] !== null ? (int)$pd['score'] : '-' ?></td>
                <td><?= htmlspecialchars($pd['completed_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="text-muted py-2">No activity data yet.</p>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<?php else: ?>
<div class="dash-empty-state">
  <h3>No children added</h3>
  <p>Add a child first to view progress tracking.</p>
  <a href="/parent/children/add" class="btn btn-primary">Add Child</a>
</div>
<?php endif; ?>
