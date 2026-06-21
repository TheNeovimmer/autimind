<div class="dash-header">
  <div>
    <h1><?= htmlspecialchars($child['name']) ?></h1>
    <p>Parent: <?= htmlspecialchars($parent['parent_name'] ?? '') ?></p>
  </div>
  <a href="/admin/progress" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="dash-grid-auto mb-3">
  <div class="card dash-stat-card">
    <h3 class="dash-stat-value"><?= (int)$totalActivities ?></h3>
    <p class="dash-stat-label">Activities Completed</p>
  </div>
  <div class="card dash-stat-card">
    <h3 class="dash-stat-value"><?= $avgScore !== null ? round($avgScore, 1) . '%' : '-' ?></h3>
    <p class="dash-stat-label">Average Score</p>
  </div>
  <div class="card dash-stat-card">
    <h3 class="dash-stat-value"><?= count($quizAttempts) ?></h3>
    <p class="dash-stat-label">Recent Quizzes</p>
  </div>
</div>

<div class="card">
  <h3>Activity History</h3>
  <?php if (!empty($activities)): ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 small">
      <thead>
        <tr><th>Activity</th><th>Category</th><th>Difficulty</th><th>Score</th><th>Completed</th></tr>
      </thead>
      <tbody>
        <?php foreach ($activities as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['title'] ?? '') ?></td>
            <td><?= htmlspecialchars($a['category'] ?? '') ?></td>
            <td><?= htmlspecialchars($a['difficulty'] ?? '') ?></td>
            <td><?= $a['score'] !== null ? (int)$a['score'] . '%' : '-' ?></td>
            <td><?= htmlspecialchars($a['completed_at'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <p>No activities completed yet.</p>
  <?php endif; ?>
</div>

<div class="card">
  <h3>Recent Quiz Attempts</h3>
  <?php if (!empty($quizAttempts)): ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 small">
      <thead>
        <tr><th>Question</th><th>Score</th><th>Completed</th></tr>
      </thead>
      <tbody>
        <?php foreach ($quizAttempts as $qa): ?>
          <tr>
            <td><?= htmlspecialchars($qa['question_text'] ?? '') ?></td>
            <td><?= $qa['score'] !== null ? (int)$qa['score'] . '%' : 'In progress' ?></td>
            <td><?= htmlspecialchars($qa['completed_at'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <p>No quiz attempts yet.</p>
  <?php endif; ?>
</div>
