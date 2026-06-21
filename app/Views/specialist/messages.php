<div class="dash-header">
  <div>
    <h1>Messages</h1>
    <p>Communicate with parents</p>
  </div>
  <div class="dash-header-actions">
    <button class="btn btn-primary" onclick="document.getElementById('newMessageModal').style.display='block'">
      <i class="fas fa-plus"></i> New Message
    </button>
  </div>
</div>

<div id="newMessageModal" class="modal" style="display:none;">
  <div class="modal-backdrop" onclick="this.parentElement.style.display='none'"></div>
  <div class="modal-content">
    <div class="modal-header">
      <h3>Send New Message</h3>
      <button type="button" class="modal-close" onclick="document.getElementById('newMessageModal').style.display='none'">&times;</button>
    </div>
    <form method="POST" action="/specialist/messages/send" class="dash-form">
      <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
      <div class="form-group">
        <label for="parent_select">Select Parent</label>
        <select id="parent_select" name="receiver_id" required>
          <option value="">-- Choose a parent --</option>
          <?php foreach ($parents as $parent): ?>
            <option value="<?= (int)$parent['id'] ?>"><?= htmlspecialchars($parent['name']) ?> (<?= htmlspecialchars($parent['email']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="new_subject">Subject</label>
        <input type="text" id="new_subject" name="subject" required placeholder="Message subject">
      </div>
      <div class="form-group">
        <label for="new_body">Message</label>
        <textarea id="new_body" name="body" rows="5" required placeholder="Type your message..."></textarea>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Send Message</button>
        <button type="button" class="btn btn-outline" onclick="document.getElementById('newMessageModal').style.display='none'">Cancel</button>
      </div>
    </form>
  </div>
</div>

<div class="dash-grid dash-grid-2">
  <div class="dash-card">
    <h3><i class="fas fa-inbox"></i> Inbox (<?= count($inbox) ?>)</h3>
    <?php if (!empty($inbox)): ?>
      <div class="message-list">
        <?php foreach ($inbox as $msg): ?>
          <a href="/specialist/messages/thread/<?= (int)$msg['sender_id'] ?>" class="message-item <?= !$msg['is_read'] ? 'unread' : '' ?>">
            <div class="msg-sender"><?= htmlspecialchars($msg['sender_name']) ?></div>
            <div class="msg-subject"><?= htmlspecialchars($msg['subject']) ?></div>
            <div class="msg-date"><?= htmlspecialchars($msg['created_at']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="dash-empty">No messages.</p>
    <?php endif; ?>
  </div>

  <div class="dash-card">
    <h3><i class="fas fa-users"></i> Conversations</h3>
    <?php if (!empty($partners)): ?>
      <div class="message-list">
        <?php foreach ($partners as $partner): ?>
          <a href="/specialist/messages/thread/<?= (int)$partner['id'] ?>" class="message-item">
            <div class="msg-sender"><?= htmlspecialchars($partner['name']) ?></div>
            <div class="msg-subject"><?= ucfirst(htmlspecialchars($partner['role'])) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="dash-empty">No conversations yet.</p>
    <?php endif; ?>
  </div>
</div>

<style>
.modal { position:fixed; top:0; left:0; width:100%; height:100%; z-index:1000; display:flex; align-items:center; justify-content:center; }
.modal-backdrop { position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); }
.modal-content { position:relative; background:var(--card-bg,#fff); border-radius:12px; padding:2rem; width:90%; max-width:520px; box-shadow:0 20px 60px rgba(0,0,0,0.3); }
.modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; }
.modal-close { background:none; border:none; font-size:1.5rem; cursor:pointer; padding:0.25rem; }
</style>
