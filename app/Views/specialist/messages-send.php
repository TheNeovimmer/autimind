<div class="dash-header">
  <div>
    <h1>New Message</h1>
    <p>To: <?= htmlspecialchars($receiver['name']) ?></p>
  </div>
  <a href="/specialist/messages" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Messages</a>
</div>

<div class="card">
  <form method="POST" action="/specialist/messages/send" class="">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="hidden" name="receiver_id" value="<?= (int)$receiver['id'] ?>">

    <div class="mb-3">
      <label>To</label>
      <input type="text" value="<?= htmlspecialchars($receiver['name']) ?> (<?= htmlspecialchars($receiver['email']) ?>)" disabled>
    </div>

    <div class="mb-3">
      <label for="subject">Subject</label>
      <input type="text" id="subject" name="subject" required placeholder="Message subject">
    </div>

    <div class="mb-3">
      <label for="body">Message</label>
      <textarea id="body" name="body" rows="6" required placeholder="Type your message..."></textarea>
    </div>

    <div class="d-flex gap-2 align-items-center">
      <button type="submit" class="btn btn-primary">Send Message</button>
      <a href="/specialist/messages" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
