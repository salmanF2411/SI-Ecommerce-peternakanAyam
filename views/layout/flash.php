<?php
/**
 * Layout: Flash Message
 *
 * @var array|null $flash  Consumed from session
 */
if ($flash): ?>
<div class="alert <?= e($flash['type']) ?>" role="alert" id="flash-alert">
    <?php if ($flash['type'] === 'success'): ?>
        <i class="fa fa-check-circle" style="color:var(--green);margin-right:8px"></i>
    <?php elseif ($flash['type'] === 'danger'): ?>
        <i class="fa fa-exclamation-circle" style="color:var(--red);margin-right:8px"></i>
    <?php elseif ($flash['type'] === 'warning'): ?>
        <i class="fa fa-exclamation-triangle" style="color:var(--yellow);margin-right:8px"></i>
    <?php endif; ?>
    <?= e($flash['message']) ?>
</div>
<?php endif; ?>
