<div class="dash-header">
  <div>
    <h1><?= htmlspecialchars($child['name']) ?> - Activities</h1>
    <p>Completed activities and progress</p>
  </div>
  <a href="/parent/progress" class="dash-btn dash-btn-outline"><i class="fas fa-arrow-left"></i> Back to Progress</a>
</div>

<?php if ($averageScore !== null): ?>
<div class="card mb-2">
  <h3>Overall Performance</h3>
  <p><strong>Average Score:</strong> <span class="score-highlight"><?= round($averageScore, 1) ?></span></p>
  <p><strong>Total Activities Completed:</strong> <?= count($activities) ?></p>
</div>
<?php endif; ?>

<div class="card">
  <?php if (!empty($activities)): ?>
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 small">
      <thead>
        <tr>
          <th>Activity</th>
          <th>Category</th>
          <th>Difficulty</th>
          <th>Score</th>
          <th>Completed</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($activities as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['title']) ?></td>
            <td><?= ucfirst(htmlspecialchars($a['category'])) ?></td>
            <td><span class="difficulty-<?= htmlspecialchars($a['difficulty'] ?? 'medium') ?>"><?= ucfirst(htmlspecialchars($a['difficulty'] ?? '-')) ?></span></td>
            <td><?= $a['score'] !== null ? (int)$a['score'] : '-' ?></td>
            <td><?= htmlspecialchars($a['completed_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php else: ?>
    <div class="dash-empty-state">
      <i class="fas fa-gamepad dash-empty-icon"></i>
      <h3>No activities completed yet</h3>
      <p>Activities completed by your child will appear here.</p>
    </div>
  <?php endif; ?>
</div>
