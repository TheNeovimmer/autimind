<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <img src="https://static.codia.ai/image/2026-06-19/6vuxJTHMOw.png" alt="AutiMind">
    </div>
    <h1>Reset Password</h1>
    <p class="auth-subtitle">Enter your new password</p>

    <?php if (!empty($errors)): ?>
      <div class="auth-form">
        <?php foreach ($errors as $fieldErrors): ?>
          <?php foreach ($fieldErrors as $error): ?>
            <div class="form-group"><span class="field-error"><?= htmlspecialchars($error) ?></span></div>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="/reset-password/<?= htmlspecialchars($token) ?>" class="auth-form">
      <div class="form-group">
        <div class="input-wrap">
          <i class="fas fa-lock"></i>
          <input type="password" name="password" placeholder="New Password" required>
          <i class="fas fa-eye password-toggle" onclick="togglePassword(this)" style="pointer-events:auto;cursor:pointer;"></i>
        </div>
      </div>
      <div class="form-group">
        <div class="input-wrap">
          <i class="fas fa-lock"></i>
          <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
        </div>
      </div>
      <button type="submit" class="btn-primary">Reset Password</button>
    </form>
  </div>
</div>
