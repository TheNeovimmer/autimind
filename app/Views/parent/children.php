<div class="dash-header">
  <div>
    <h1>My Children</h1>
    <p>Manage your children's profiles</p>
  </div>
  <a href="/parent/children/add" class="dash-btn dash-btn-primary"><i class="fas fa-plus"></i> Add Child</a>
</div>

<?php if (!empty($children)): ?>
<div class="table-responsive">
  <table class="table table-hover align-middle mb-0 small">
    <thead>
      <tr>
        <th></th>
        <th>Name</th>
        <th>Age</th>
        <th>Birth Date</th>
        <th>Diagnosis Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($children as $child): ?>
        <tr>
          <td>
            <?php if (!empty($child['avatar'])): ?>
              <img src="<?= htmlspecialchars($child['avatar']) ?>" alt="Avatar" class="child-avatar-thumb">
            <?php else: ?>
              <span class="child-avatar-placeholder"><?= strtoupper(substr($child['name'], 0, 1)) ?></span>
            <?php endif; ?>
          </td>
          <td><strong><?= htmlspecialchars($child['name']) ?></strong></td>
          <td><?= $child['age'] ? (int)$child['age'] . ' yrs' : '-' ?></td>
          <td><?= htmlspecialchars($child['birth_date'] ?? '-') ?></td>
          <td><?= htmlspecialchars($child['diagnosis_status'] ?? '-') ?></td>
          <td>
            <a href="/parent/children/<?= (int)$child['id'] ?>" class="dash-btn dash-btn-sm dash-btn-outline"><i class="fas fa-eye"></i></a>
            <a href="/parent/children/<?= (int)$child['id'] ?>/edit" class="dash-btn dash-btn-sm dash-btn-outline"><i class="fas fa-edit"></i></a>
            <form method="POST" action="/parent/children/<?= (int)$child['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Remove this child?')">
              <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
              <button type="submit" class="dash-btn dash-btn-sm dash-btn-danger"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="dash-empty-state">
  <i class="fas fa-child dash-empty-icon"></i>
  <h3>No children added yet</h3>
  <p>Add your first child to start using the screening quiz and progress tracking.</p>
  <a href="/parent/children/add" class="dash-btn dash-btn-primary">Add Child</a>
</div>
<?php endif; ?>
