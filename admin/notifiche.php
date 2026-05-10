<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/utils.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck($_POST['csrf'] ?? null);
    if (($_POST['action'] ?? '') === 'mark_read') {
        $now = dbDriver() === 'sqlite' ? "datetime('now')" : 'CURRENT_TIMESTAMP';
        q("UPDATE notifications SET read_at = $now WHERE read_at IS NULL");
    }
    redirect('/admin/notifiche.php');
}

$notifs = rows('SELECT * FROM notifications ORDER BY created_at DESC LIMIT 100');
$title = 'Notifiche';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/admin-shell-top.php';
?>
<div class="space-y-5">
  <div class="flex items-end justify-between flex-wrap gap-3">
    <h1 class="font-display text-2xl sm:text-3xl font-bold">Notifiche</h1>
    <form method="post"><input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="mark_read"><button class="btn-outline text-sm"><i data-lucide="check-check" class="size-[14px]"></i> Segna tutte come lette</button></form>
  </div>
  <div class="space-y-2">
    <?php foreach ($notifs as $n): ?>
      <a href="<?= e($n['link'] ?: '#') ?>" class="card p-4 flex gap-3 hover:bg-ink-50 <?= $n['read_at'] ? 'opacity-70' : '' ?>">
        <span class="h-9 w-9 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center shrink-0"><i data-lucide="bell" class="size-[16px]"></i></span>
        <div class="flex-1 min-w-0">
          <div class="font-medium text-sm"><?= e($n['title']) ?></div>
          <div class="text-xs text-ink-500 mt-0.5"><?= e($n['body']) ?></div>
          <div class="text-[10px] text-ink-400 mt-1"><?= fmtDateTime($n['created_at']) ?></div>
        </div>
        <?php if (!$n['read_at']): ?><span class="h-2 w-2 rounded-full bg-brand-500 mt-2 shrink-0"></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
    <?php if (!$notifs): ?><div class="card p-10 text-center text-sm text-ink-500">Nessuna notifica.</div><?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../partials/admin-shell-bottom.php';
