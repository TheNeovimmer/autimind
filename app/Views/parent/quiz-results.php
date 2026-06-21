<div class="dash-header">
  <div>
    <h1>Quiz Results</h1>
    <p>For: <?= htmlspecialchars($child['name']) ?> · <?= htmlspecialchars($attempt['completed_at'] ?? 'In progress') ?></p>
  </div>
</div>

<?php
$riskColors = ['low' => '#22c55e', 'moderate' => '#f59e0b', 'high' => '#ef4444'];
$riskColor = $riskColors[$result['risk_level']] ?? '#6b7280';
?>

<div class="dash-grid dash-grid-2 mb-2">
  <div class="dash-card result-summary" style="border-left: 4px solid <?= $riskColor ?>;">
    <h2>Total Score: <?= (int)$result['total_score'] ?>/50</h2>
    <p class="risk-badge risk-<?= htmlspecialchars($result['risk_level']) ?>">Risk Level: <?= ucfirst(htmlspecialchars($result['risk_level'])) ?></p>
    <div class="score-bar">
      <div class="score-bar-fill" style="width: <?= min(100, ((int)$result['total_score'] / 50) * 100) ?>%; background: <?= $riskColor ?>;"></div>
    </div>
  </div>

  <div class="dash-card">
    <h3>Category Breakdown</h3>
    <div class="category-scores">
      <?php foreach ($result['category_scores'] as $cat => $score): ?>
        <div class="category-item">
          <span class="category-label"><?= ucwords(str_replace('_', ' ', htmlspecialchars($cat))) ?></span>
          <span class="category-score"><?= (int)$score ?>/<?= (int)$result['category_max'][$cat] ?> (<?= (float)$result['category_percentages'][$cat] ?>%)</span>
          <div class="score-bar-sm">
            <div class="score-bar-fill" style="width: <?= min(100, (float)$result['category_percentages'][$cat]) ?>%; background: <?= $riskColor ?>;"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="dash-card mb-2">
  <h3>Answer Details</h3>
  <table class="dash-table">
    <thead><tr><th>#</th><th>Question</th><th>Category</th><th>Your Answer</th><th>Weight</th></tr></thead>
    <tbody>
      <?php foreach ($answers as $ans): ?>
        <tr>
          <td><?= (int)$ans['weight'] >= 3 ? '<i class="fas fa-circle" style="color: var(--warning); font-size: 0.6rem;"></i>' : '' ?></td>
          <td><?= htmlspecialchars($ans['question_text']) ?></td>
          <td><?= ucwords(str_replace('_', ' ', htmlspecialchars($ans['category']))) ?></td>
          <td><?= htmlspecialchars($ans['option_text']) ?></td>
          <td><?= (int)$ans['weight'] ?>/5</td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if (count($previousAttempts) > 1): ?>
<div class="dash-card">
  <h3>Score History</h3>
  <table class="dash-table">
    <thead><tr><th>Date</th><th>Score</th><th>Risk Level</th></tr></thead>
    <tbody>
      <?php foreach ($previousAttempts as $pa): ?>
        <tr>
          <td><?= htmlspecialchars($pa['completed_at']) ?></td>
          <td><?= (int)$pa['total_score'] ?>/50</td>
          <td><span class="risk-<?= htmlspecialchars($pa['risk_level']) ?>"><?= ucfirst(htmlspecialchars($pa['risk_level'])) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<div class="form-actions mt-2">
  <a href="/parent/quiz" class="btn btn-outline">Back to Quiz</a>
  <a href="/parent/quiz/start/<?= (int)$child['id'] ?>" class="btn btn-primary">Take Another Quiz</a>
</div>
