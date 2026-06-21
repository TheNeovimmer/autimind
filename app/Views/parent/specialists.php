<div class="dash-header">
  <div>
    <h1>Specialists</h1>
    <p>Browse our specialist directory</p>
  </div>
</div>

<div class="specialist-grid">
  <?php foreach ($specialists as $spec): ?>
    <div class="card specialist-card">
      <div class="specialist-avatar">
        <span class="avatar-initials"><?= strtoupper(substr(htmlspecialchars($spec['name']), 0, 1)) ?></span>
      </div>
      <h3><?= htmlspecialchars($spec['name']) ?></h3>
      <p class="specialist-title"><?= htmlspecialchars($spec['title'] ?? 'Specialist') ?></p>
      <p class="specialist-bio"><?= htmlspecialchars(substr($spec['bio'] ?? '', 0, 120)) ?></p>
      <?php if ($spec['specializations']): ?>
        <?php $specs = json_decode($spec['specializations'], true) ?? []; ?>
        <div class="specialist-tags">
          <?php foreach ($specs as $s): ?>
            <span class="tag"><?= htmlspecialchars($s) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <p class="specialist-exp"><?= (int)$spec['years_experience'] ?> years experience</p>
      <div class="specialist-actions">
        <a href="/parent/appointments/book?specialist_id=<?= (int)$spec['id'] ?>" class="btn btn-primary btn-sm">Book Appointment</a>
        <a href="/parent/messages/send/<?= (int)$spec['id'] ?>" class="btn btn-outline-secondary btn-sm">Send Message</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php if (empty($specialists)): ?>
<div class="dash-empty-state">
  <h3>No specialists available</h3>
  <p>Please check back later.</p>
</div>
<?php endif; ?>
