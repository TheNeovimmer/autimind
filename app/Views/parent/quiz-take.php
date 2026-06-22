<div class="dash-header-premium">
  <div>
    <h1>Screening Quiz</h1>
    <p>For: <?= htmlspecialchars($child['name']) ?></p>
  </div>
</div>

<div class="dash-alert dash-alert--info">
  <i class="fas fa-clock"></i>
  <p>Answer all 10 questions based on your observation of your child's typical behavior. There are no right or wrong answers.</p>
</div>

<form id="quizForm" method="POST" action="/parent/quiz/submit" class="quiz-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <input type="hidden" name="attempt_id" value="<?= (int)$attemptId ?>">

  <?php foreach ($questions as $index => $question): ?>
    <div class="card quiz-question" data-question="<?= $index + 1 ?>">
      <div class="question-header">
        <span class="question-number">Q<?= $index + 1 ?></span>
        <span class="question-category"><?= ucwords(str_replace('_', ' ', htmlspecialchars($question['category']))) ?></span>
      </div>
      <p class="question-text"><?= htmlspecialchars($question['question_text']) ?></p>
      
      <div class="question-options">
        <?php foreach ($question['options'] as $option): ?>
          <label class="option-label">
            <input type="radio" name="answers[<?= (int)$question['id'] ?>]" value="<?= (int)$option['id'] ?>" required>
            <span class="option-text"><?= htmlspecialchars($option['option_text']) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="form-actions">
    <a href="/parent/quiz" class="dash-btn dash-btn-outline">Cancel</a>
    <button type="submit" class="dash-btn dash-btn-primary">Submit Quiz</button>
  </div>
</form>

<script>
document.getElementById('quizForm')?.addEventListener('submit', function(e) {
  const total = document.querySelectorAll('.quiz-question').length;
  const answered = document.querySelectorAll('input[type="radio"]:checked').length;
  if (answered < total) {
    if (!confirm('You have answered ' + answered + ' of ' + total + ' questions. Submit anyway?')) {
      e.preventDefault();
    }
  }
});
</script>
