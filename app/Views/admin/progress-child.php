<div class="dash-header">
  <div>
    <h1><?= htmlspecialchars($child['name']) ?></h1>
    <p>Parent: <?= htmlspecialchars($parent['parent_name'] ?? '') ?></p>
  </div>
  <a href="/admin/progress" class="btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="dash-grid-3" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;">
  <div class="dash-card" style="text-align:center;padding:1.5rem;">
    <h3 style="margin:0;font-size:2rem;color:var(--primary);"><?= (int)$totalActivities ?></h3>
    <p style="margin:0.25rem 0 0;color:var(--text-muted);">Activities Completed</p>
  </div>
  <div class="dash-card" style="text-align:center;padding:1.5rem;">
    <h3 style="margin:0;font-size:2rem;color:var(--primary);"><?= $avgScore !== null ? round($avgScore, 1) . '%' : '-' ?></h3>
    <p style="margin:0.25rem 0 0;color:var(--text-muted);">Average Score</p>
  </div>
  <div class="dash-card" style="text-align:center;padding:1.5rem;">
    <h3 style="margin:0;font-size:2rem;color:var(--primary);"><?= count($quizAttempts) ?></h3>
    <p style="margin:0.25rem 0 0;color:var(--text-muted);">Recent Quizzes</p>
  </div>
</div>

<div class="dash-card">
  <h3>Activity History</h3>
  <?php if (!empty($activities)): ?>
  <div class="table-responsive">
    <table class="dash-table">
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

<div class="dash-card">
  <h3>Recent Quiz Attempts</h3>
  <?php if (!empty($quizAttempts)): ?>
  <div class="table-responsive">
    <table class="dash-table">
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
