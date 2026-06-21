<section class="contact-hero fade-in">
  <div class="section-tag" style="justify-content:center">
    <span>CONTACT</span>
  </div>
  <h1>Let's <span class="highlight">talk</span></h1>
  <p>Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
</section>

<div class="contact-layout fade-in">
  <div class="contact-form-wrap">
    <h2>Send a Message</h2>
    <?php if (isset($errors) && !empty($errors)): ?>
    <div class="form-errors">
      <?php foreach ($errors as $field => $error): ?>
        <p class="error-message"><?= htmlspecialchars(is_array($error) ? $error[0] : $error) ?></p>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <form class="contact-form" action="/contact" method="POST" data-validate>
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
      <input type="text" name="name" placeholder="Your Name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
      <input type="email" name="email" placeholder="Your Email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
      <input type="text" name="subject" placeholder="Subject" value="<?= htmlspecialchars($old['subject'] ?? '') ?>" required>
      <textarea name="message" placeholder="Your Message" required><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
      <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> Send Message</button>
    </form>
  </div>
  <div class="contact-info-wrap">
    <div class="contact-info-card">
      <i class="fas fa-phone"></i>
      <div>
        <h4>Phone</h4>
        <p>+91 6232-1151-22</p>
        <p>Mon-Fri 9am-6pm</p>
      </div>
    </div>
    <div class="contact-info-card">
      <i class="fas fa-envelope"></i>
      <div>
        <h4>Email</h4>
        <p>Autimind@autism.com</p>
        <p>We reply within 24 hours</p>
      </div>
    </div>
    <div class="contact-info-card">
      <i class="fas fa-location-dot"></i>
      <div>
        <h4>Location</h4>
        <p>123 Autism Care Street,<br>Health District, NY 10001</p>
      </div>
    </div>
    <div class="contact-social">
      <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
      <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
      <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
      <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
    </div>
  </div>
</div>
