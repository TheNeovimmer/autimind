<div class="dash-header-premium">
  <div>
    <h1>Child Progress</h1>
    <p>Monitor children's activity and quiz progress</p>
  </div>
</div>

<?php if (!empty($children)): ?>
<div class="dash-table-wrapper">
  <table class="dash-table">
    <thead>
      <tr><th>Child Name</th><th>Parent</th><th>Activities</th><th>Avg Score</th><th>Quizzes</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($children as $c): ?>
        <tr>
          <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
          <td><?= htmlspecialchars($c['parent_name']) ?></td>
          <td><?= (int)$c['activities_completed'] ?></td>
          <td><?= $c['avg_score'] !== null ? (float)$c['avg_score'] . '%' : '-' ?></td>
          <td><?= (int)$c['quiz_count'] ?></td>
          <td><a href="/admin/progress/child/<?= (int)$c['id'] ?>" class="dash-btn dash-btn-sm dash-btn-outline">View</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="dash-empty-state"><h3>No children</h3><p>No children registered in the system.</p></div>
<?php endif; ?>
