<?php
/**
 * Halaman Admin: Log Aktivitas
 * FR-15 — Activity log sistem
 *
 * @var array $logs
 */
?>

<div class="dash-head">
    <div>
        <span class="eyebrow">KEAMANAN & TRANSPARANSI</span>
        <h1>Log Aktivitas</h1>
        <p>Rekam jejak aktivitas sistem untuk keamanan data.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2><i class="fa fa-history" style="color:var(--green);margin-right:8px"></i>Aktivitas Sistem</h2>
        <span><?= count($logs) ?> aktivitas terbaru</span>
    </div>

    <?php foreach ($logs as $log): ?>
    <div class="log-row">
        <div class="log-dot"></div>
        <div class="log-row-content">
            <b><?= e($log['activity']) ?></b>
            <small>
                <i class="fa fa-user" style="margin-right:4px;color:var(--green)"></i>
                <?= e($log['name'] ?? 'Sistem') ?>
                &nbsp;·&nbsp;
                <i class="fa fa-clock" style="margin-right:4px"></i>
                <?= dateId($log['created_at']) ?>
            </small>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (!$logs): ?>
    <div class="empty-state" style="border:0;padding:40px 0">
        <i class="fa fa-history"></i>
        <h3>Belum ada aktivitas tercatat</h3>
    </div>
    <?php endif; ?>
</div>
