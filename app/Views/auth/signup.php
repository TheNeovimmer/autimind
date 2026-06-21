<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <img src="https://static.codia.ai/image/2026-06-19/6vuxJTHMOw.png" alt="AutiMind">
    </div>
    <h1>Create Account</h1>
    <p class="auth-subtitle">Join AutiMind today</p>

    <?php if (!empty($errors)): ?>
      <div class="auth-form">
        <?php foreach ($errors as $fieldErrors): ?>
          <?php foreach ($fieldErrors as $error): ?>
            <div class="form-group"><span class="field-error"><?= htmlspecialchars($error) ?></span></div>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="/signup" class="auth-form">
      <div class="form-group">
        <div class="input-wrap">
          <i class="fas fa-user"></i>
          <input type="text" name="name" placeholder="Full Name" required
                 value="<?= htmlspecialchars($old['name'] ?? '') ?>">
        </div>
      </div>
      <div class="form-group">
        <div class="input-wrap">
          <i class="fas fa-envelope"></i>
          <input type="email" name="email" placeholder="Email" required
                 value="<?= htmlspecialchars($old['email'] ?? '') ?>">
        </div>
      </div>
      <div class="form-group">
        <div class="input-wrap">
          <i class="fas fa-user-tag"></i>
          <select name="role" required>
            <option value="">Select Role</option>
            <option value="parent" <?= ($old['role'] ?? '') === 'parent' ? 'selected' : '' ?>>Parent</option>
            <option value="specialist" <?= ($old['role'] ?? '') === 'specialist' ? 'selected' : '' ?>>Specialist</option>
            <option value="educator" <?= ($old['role'] ?? '') === 'educator' ? 'selected' : '' ?>>Educator</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <div class="input-wrap">
          <i class="fas fa-lock"></i>
          <input type="password" name="password" placeholder="Password" required>
          <i class="fas fa-eye password-toggle" onclick="togglePassword(this)" style="pointer-events:auto;cursor:pointer;"></i>
        </div>
      </div>
      <div class="form-group">
        <div class="input-wrap">
          <i class="fas fa-lock"></i>
          <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
        </div>
      </div>
      <div class="form-row">
        <label><input type="checkbox" name="terms" required> I agree to the Terms & Conditions</label>
      </div>
      <button type="submit" class="btn-primary">Create Account</button>
    </form>
    <p class="auth-footer-text">Already have an account? <a href="/login">Sign In</a></p>
  </div>
</div>
