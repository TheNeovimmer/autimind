<nav class="dash-topbar">
  <div class="dash-topbar-left">
    <button class="dash-sidebar-toggle" id="dashSidebarToggle" aria-label="Toggle sidebar">
      <i class="fas fa-bars"></i>
    </button>
    <h2><?= htmlspecialchars($title ?? 'Dashboard') ?></h2>
  </div>
  <div class="dash-topbar-right">
    <div class="profile-dropdown">
      <div class="profile-trigger" onclick="toggleProfileMenu(this)">
        <span class="dash-user-name"><?= htmlspecialchars(\App\Core\Session::get('user_name')) ?></span>
        <div class="dash-user-avatar">
          <?php
          $userId = \App\Core\Session::get('user_id');
          $user = $userId ? \App\Models\User::findById($userId) : null;
          ?>
          <?php if ($user && !empty($user['avatar'])): ?>
            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
          <?php else: ?>
            <?= strtoupper(substr(\App\Core\Session::get('user_name'), 0, 1)) ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="profile-menu">
        <?php
        $role = \App\Core\Session::get('role');
        $dashLinks = [
          'parent' => '/parent/dashboard',
          'specialist' => '/specialist/dashboard',
          'admin' => '/admin/dashboard',
        ];
        $dashUrl = $dashLinks[$role] ?? '/parent/dashboard';
        ?>
        <a href="<?= $dashUrl ?>"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="/<?= $role ?>/settings"><i class="fas fa-cog"></i> Settings</a>
        <div class="menu-divider"></div>
        <a href="/logout" class="menu-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>
  </div>
</nav>
