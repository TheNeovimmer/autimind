<div class="dash-header-premium">
  <div>
    <h1>New Message</h1>
    <p>To: <?= htmlspecialchars($receiver['name']) ?></p>
  </div>
  <a href="/specialist/messages" class="dash-btn dash-btn-outline"><i class="fas fa-arrow-left"></i> Back to Messages</a>
</div>

<div class="card">
  <form method="POST" action="/specialist/messages/send" class="">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="hidden" name="receiver_id" value="<?= (int)$receiver['id'] ?>">

    <div class="dash-field">
      <label>To</label>
      <input type="text" class="" value="<?= htmlspecialchars($receiver['name']) ?> (<?= htmlspecialchars($receiver['email']) ?>)" disabled>
    </div>

    <div class="dash-field">
      <label for="subject">Subject</label>
      <input type="text" id="subject" name="subject" class="" required placeholder="Message subject">
    </div>

    <div class="dash-field">
      <label for="body">Message</label>
      <textarea id="body" name="body" rows="6" class="" required placeholder="Type your message..."></textarea>
    </div>

    <div class="form-actions">
      <button type="submit" class="dash-btn dash-btn-primary">Send Message</button>
      <a href="/specialist/messages" class="dash-btn dash-btn-outline">Cancel</a>
    </div>
  </form>
</div>
