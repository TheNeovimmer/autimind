<div class="dash-header">
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

    <div class="mb-3">
      <label class="form-label">To</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($receiver['name']) ?> (<?= htmlspecialchars($receiver['email']) ?>)" disabled>
    </div>

    <div class="mb-3">
      <label for="subject" class="form-label">Subject</label>
      <input type="text" id="subject" name="subject" class="form-control" required placeholder="Message subject">
    </div>

    <div class="mb-3">
      <label for="body" class="form-label">Message</label>
      <textarea id="body" name="body" rows="6" class="form-control" required placeholder="Type your message..."></textarea>
    </div>

    <div class="d-flex gap-2 align-items-center">
      <button type="submit" class="dash-btn dash-btn-primary">Send Message</button>
      <a href="/specialist/messages" class="dash-btn dash-btn-outline">Cancel</a>
    </div>
  </form>
</div>
