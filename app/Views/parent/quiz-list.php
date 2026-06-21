<div class="dash-header">
  <div>
    <h1>Screening Quiz</h1>
    <p>Developmental screening for your children</p>
  </div>
</div>

<div class="dash-info-box">
  <i class="fas fa-info-circle"></i>
  <p>This screening quiz consists of <strong>10 questions</strong> across key developmental areas. It takes about 5-10 minutes to complete. Results are for informational purposes only and not a medical diagnosis.</p>
</div>

<?php if (!empty($quizData)): ?>
  <?php foreach ($quizData as $data): ?>
    <div class="dash-card mb-2">
      <div class="dash-card-header">
        <h3><i class="fas fa-child"></i> <?= htmlspecialchars($data['child']['name']) ?></h3>
        <?php if ($data['attemptCount'] > 0): ?>
          <span class="dash-badge"><?= $data['attemptCount'] ?> attempt(s)</span>
        <?php endif; ?>
      </div>
      
      <?php if ($data['latest']): ?>
        <p>Latest result: Score <strong><?= (int)$data['latest']['total_score'] ?>/50</strong> · 
        Risk: <span class="risk-<?= htmlspecialchars($data['latest']['risk_level']) ?>"><?= ucfirst(htmlspecialchars($data['latest']['risk_level'])) ?></span> · 
        <?= htmlspecialchars($data['latest']['completed_at']) ?></p>
        <div class="mt-1">
          <a href="/parent/quiz/results/<?= (int)$data['latest']['id'] ?>" class="btn btn-outline btn-sm">View Results</a>
          <a href="/parent/quiz/start/<?= (int)$data['child']['id'] ?>" class="btn btn-primary btn-sm">Take New Quiz</a>
        </div>
      <?php else: ?>
        <p class="dash-empty">No screening completed yet.</p>
        <a href="/parent/quiz/start/<?= (int)$data['child']['id'] ?>" class="btn btn-primary btn-sm">Start Screening</a>
      <?php endif; ?>
      
      <?php if (count($data['attempts']) > 1): ?>
        <details class="mt-1">
          <summary>View History (<?= count($data['attempts']) ?> attempts)</summary>
          <table class="dash-table mt-1">
            <thead>
              <tr><th>Date</th><th>Score</th><th>Risk Level</th><th></th></tr>
            </thead>
            <tbody>
              <?php foreach ($data['attempts'] as $attempt): ?>
                <tr>
                  <td><?= htmlspecialchars($attempt['completed_at']) ?></td>
                  <td><?= (int)$attempt['total_score'] ?>/50</td>
                  <td><span class="risk-<?= htmlspecialchars($attempt['risk_level']) ?>"><?= ucfirst(htmlspecialchars($attempt['risk_level'])) ?></span></td>
                  <td><a href="/parent/quiz/results/<?= (int)$attempt['id'] ?>">View</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </details>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php else: ?>
  <div class="dash-empty-state">
    <h3>No children added</h3>
    <p>Add a child first to take the screening quiz.</p>
    <a href="/parent/children/add" class="btn btn-primary">Add Child</a>
  </div>
<?php endif; ?>
