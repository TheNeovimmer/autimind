<div class="dash-header">
  <div>
    <h1>New Message</h1>
    <p>To: <?= htmlspecialchars($receiver['name']) ?></p>
  </div>
  <a href="/specialist/messages" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Messages</a>
</div>

<div class="dash-card">
  <form method="POST" action="/specialist/messages/send" class="dash-form">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="hidden" name="receiver_id" value="<?= (int)$receiver['id'] ?>">

    <div class="form-group">
      <label>To</label>
      <input type="text" value="<?= htmlspecialchars($receiver['name']) ?> (<?= htmlspecialchars($receiver['email']) ?>)" disabled>
    </div>

    <div class="form-group">
      <label for="subject">Subject</label>
      <input type="text" id="subject" name="subject" required placeholder="Message subject">
    </div>

    <div class="form-group">
      <label for="body">Message</label>
      <textarea id="body" name="body" rows="6" required placeholder="Type your message..."></textarea>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Send Message</button>
      <a href="/specialist/messages" class="btn btn-outline">Cancel</a>
    </div>
  </form>
</div>
