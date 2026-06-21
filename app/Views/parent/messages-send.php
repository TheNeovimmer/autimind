<div class="dash-header">
  <div>
    <h1>Send Message</h1>
    <p>To: <?= htmlspecialchars($receiver['name']) ?></p>
  </div>
  <a href="/parent/messages" class="dash-btn dash-btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<form method="POST" action="/parent/messages/send" >
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <input type="hidden" name="receiver_id" value="<?= (int)$receiver['id'] ?>">

  <div class="mb-3">
    <label for="subject" class="form-label">Subject *</label>
    <input type="text" id="subject" name="subject" class="form-control" required placeholder="Enter message subject...">
  </div>

  <div class="mb-3">
    <label for="body" class="form-label">Message *</label>
    <textarea id="body" name="body" rows="6" class="form-control" required placeholder="Write your message here..."></textarea>
  </div>

  <div class="d-flex gap-2 align-items-center">
    <a href="/parent/messages" class="dash-btn dash-btn-outline">Cancel</a>
    <button type="submit" class="dash-btn dash-btn-primary">Send Message</button>
  </div>
</form>
