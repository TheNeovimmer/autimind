<div class="dash-header">
  <div>
    <h1><?= htmlspecialchars($child['name']) ?></h1>
    <p>Child profile and activity overview</p>
  </div>
  <a href="/parent/children" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Children</a>
</div>

<div class="card mb-2">
  <div class="child-profile-header">
    <div class="child-avatar-large">
      <?php if (!empty($child['avatar'])): ?>
        <img src="<?= htmlspecialchars($child['avatar']) ?>" alt="<?= htmlspecialchars($child['name']) ?>">
      <?php else: ?>
        <span class="avatar-initials"><?= strtoupper(substr($child['name'], 0, 1)) ?></span>
      <?php endif; ?>
    </div>
    <div class="child-info">
      <h2><?= htmlspecialchars($child['name']) ?></h2>
      <p><strong>Age:</strong> <?= $child['age'] ? (int)$child['age'] . ' yrs' : '-' ?></p>
      <p><strong>Birth Date:</strong> <?= htmlspecialchars($child['birth_date'] ?? '-') ?></p>
      <p><strong>Diagnosis Status:</strong> <?= htmlspecialchars($child['diagnosis_status'] ?? 'Not specified') ?></p>
      <?php if (!empty($child['notes'])): ?>
        <p><strong>Notes:</strong> <?= nl2br(htmlspecialchars($child['notes'])) ?></p>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="row row-cols-1 row-cols-md-2 g-3 mb-3">
  <div class="card">
    <h3><i class="fas fa-clipboard-list"></i> Quiz History</h3>
    <?php if (!empty($quizHistory)): ?>
      <table class="table table-hover align-middle mb-0 small">
        <thead><tr><th>Date</th><th>Score</th><th>Risk Level</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($quizHistory as $qh): ?>
            <tr>
              <td><?= htmlspecialchars($qh['completed_at']) ?></td>
              <td><?= (int)$qh['total_score'] ?>/50</td>
              <td><span class="risk-<?= htmlspecialchars($qh['risk_level']) ?>"><?= ucfirst(htmlspecialchars($qh['risk_level'])) ?></span></td>
              <td><a href="/parent/quiz/results/<?= (int)$qh['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="dash-empty">No quiz attempts yet. <a href="/parent/quiz/start/<?= (int)$child['id'] ?>">Start a screening quiz</a></p>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3><i class="fas fa-gamepad"></i> Activity Progress</h3>
    <?php if (!empty($progress)): ?>
      <table class="table table-hover align-middle mb-0 small">
        <thead><tr><th>Activity</th><th>Category</th><th>Difficulty</th><th>Score</th><th>Date</th></tr></thead>
        <tbody>
          <?php foreach ($progress as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['title']) ?></td>
              <td><?= ucfirst(htmlspecialchars($p['category'])) ?></td>
              <td><?= ucfirst(htmlspecialchars($p['difficulty'] ?? '-')) ?></td>
              <td><?= $p['score'] !== null ? (int)$p['score'] : '-' ?></td>
              <td><?= htmlspecialchars($p['completed_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if ($averageScore !== null): ?>
        <p class="mt-1"><strong>Average Score:</strong> <?= round($averageScore, 1) ?></p>
      <?php endif; ?>
    <?php else: ?>
      <p class="dash-empty">No activity data yet.</p>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <h3><i class="fas fa-calendar-check"></i> Appointments</h3>
  <?php if (!empty($appointments)): ?>
    <table class="table table-hover align-middle mb-0 small">
      <thead><tr><th>Specialist</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($appointments as $apt): ?>
          <tr>
            <td><?= htmlspecialchars($apt['specialist_name']) ?></td>
            <td><?= htmlspecialchars($apt['date']) ?></td>
            <td><?= htmlspecialchars(substr($apt['time'], 0, 5)) ?></td>
            <td><span class="status-<?= htmlspecialchars($apt['status']) ?>"><?= ucfirst(htmlspecialchars($apt['status'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="dash-empty">No appointments for this child. <a href="/parent/appointments/book">Book an appointment</a></p>
  <?php endif; ?>
</div>
