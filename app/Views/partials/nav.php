<nav class="navbar">
  <div class="nav-logo">
    <a href="/"><img src="https://static.codia.ai/image/2026-06-19/6vuxJTHMOw.png" alt="AutiMind"></a>
  </div>
  <button class="nav-toggle" aria-label="Toggle navigation">
    <span></span><span></span><span></span>
  </button>
  <ul class="nav-links">
    <li><a href="/" class="<?= $_SERVER['REQUEST_URI'] === '/' ? 'active' : '' ?>">Home</a></li>
    <li><a href="/about" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/about') ? 'active' : '' ?>">About</a></li>
    <li><a href="/program" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/program') ? 'active' : '' ?>">Program</a></li>
    <li><a href="/espaceenfant" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/espaceenfant') ? 'active' : '' ?>">Children</a></li>
    <li><a href="/espaceparent" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/espaceparent') ? 'active' : '' ?>">Parents</a></li>
    <li><a href="/specialists" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/specialists') ? 'active' : '' ?>">Specialists</a></li>
    <li><a href="/pricing" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/pricing') ? 'active' : '' ?>">Pricing</a></li>
    <li><a href="/contact" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/contact') ? 'active' : '' ?>">Contact</a></li>
  </ul>
  <div class="nav-actions">
    <?php if (\App\Core\Session::has('user_id')): ?>
      <?php
        $role = \App\Core\Session::get('role');
        $dashLinks = [
          'parent' => '/parent/dashboard',
          'specialist' => '/specialist/dashboard',
          'admin' => '/admin/dashboard',
        ];
        $dashUrl = $dashLinks[$role] ?? '/parent/dashboard';
        $userName = \App\Core\Session::get('user_name');
        $userInitial = strtoupper(substr($userName, 0, 1));
      ?>
      <div class="profile-dropdown">
        <div class="profile-trigger dash-user-avatar" onclick="toggleProfileMenu(this)">
          <?php
          $userId = \App\Core\Session::get('user_id');
          $user = \App\Models\User::findById($userId);
          ?>
          <?php if ($user && !empty($user['avatar'])): ?>
            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
          <?php else: ?>
            <?= $userInitial ?>
          <?php endif; ?>
        </div>
        <div class="profile-menu">
          <a href="<?= $dashUrl ?>"><i class="fas fa-th-large"></i> Dashboard</a>
          <a href="/<?= $role ?>/settings"><i class="fas fa-cog"></i> Settings</a>
          <div class="menu-divider"></div>
          <a href="/logout" class="menu-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
      </div>
    <?php else: ?>
      <a href="/signup" class="btn-signup">Get Started</a>
    <?php endif; ?>
  </div>
</nav>
