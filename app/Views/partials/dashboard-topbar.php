<div class="dash-topbar">
  <div class="dash-topbar-left">
    <button class="dash-sidebar-toggle"><i class="fas fa-bars"></i></button>
    <h2><?= htmlspecialchars($title ?? 'Dashboard') ?></h2>
  </div>
  <div class="dash-topbar-right">
    <span class="dash-user-name"><?= htmlspecialchars(\App\Core\Session::get('user_name')) ?></span>
    <div class="dash-user-avatar">
      <?php
      $userId = \App\Core\Session::get('user_id');
      $user = $userId ? \App\Models\User::findById($userId) : null;
      if ($user && !empty($user['avatar'])): ?>
        <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
      <?php else: ?>
        <?= strtoupper(substr(\App\Core\Session::get('user_name'), 0, 1)) ?>
      <?php endif; ?>
    </div>
  </div>
</div>
