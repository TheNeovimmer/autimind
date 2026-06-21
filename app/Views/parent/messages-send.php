<div class="dash-header">
  <div>
    <h1>Send Message</h1>
    <p>To: <?= htmlspecialchars($receiver['name']) ?></p>
  </div>
  <a href="/parent/messages" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<form method="POST" action="/parent/messages/send" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <input type="hidden" name="receiver_id" value="<?= (int)$receiver['id'] ?>">

  <div class="form-group">
    <label for="subject">Subject *</label>
    <input type="text" id="subject" name="subject" required placeholder="Enter message subject...">
  </div>

  <div class="form-group">
    <label for="body">Message *</label>
    <textarea id="body" name="body" rows="6" required placeholder="Write your message here..."></textarea>
  </div>

  <div class="form-actions">
    <a href="/parent/messages" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Send Message</button>
  </div>
</form>
