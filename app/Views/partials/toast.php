<div id="toast-container"></div>

<?php
$flashSuccess = \App\Core\Session::getFlash('success');
$flashError = \App\Core\Session::getFlash('error');
$flashWarning = \App\Core\Session::getFlash('warning');
$flashInfo = \App\Core\Session::getFlash('info');
?>

<?php if ($flashSuccess): ?>
<script>document.addEventListener('DOMContentLoaded', function(){ showToast('success', <?= json_encode($flashSuccess) ?>); });</script>
<?php endif; ?>
<?php if ($flashError): ?>
<script>document.addEventListener('DOMContentLoaded', function(){ showToast('error', <?= json_encode($flashError) ?>); });</script>
<?php endif; ?>
<?php if ($flashWarning): ?>
<script>document.addEventListener('DOMContentLoaded', function(){ showToast('warning', <?= json_encode($flashWarning) ?>); });</script>
<?php endif; ?>
<?php if ($flashInfo): ?>
<script>document.addEventListener('DOMContentLoaded', function(){ showToast('info', <?= json_encode($flashInfo) ?>); });</script>
<?php endif; ?>
