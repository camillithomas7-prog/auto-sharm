<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/utils.php';
requireAdmin();

$cars = rows('SELECT * FROM cars ORDER BY position ASC, name ASC');

$title = 'Auto';
$_subtitle = 'Flotta';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/admin-shell-top.php';
?>
<div class="space-y-5">
  <div class="flex items-end justify-between flex-wrap gap-3">
    <div>
      <h1 class="font-display text-2xl sm:text-3xl font-bold">Le tue auto</h1>
      <p class="text-ink-500 text-sm mt-1">Aggiungi, modifica o disattiva i veicoli del catalogo.</p>
    </div>
    <a href="/admin/auto-edit.php" class="btn-primary"><i data-lucide="plus" class="size-[16px]"></i> Nuova auto</a>
  </div>

  <?php if (!$cars): ?>
    <div class="card p-12 text-center">
      <div class="h-14 w-14 mx-auto rounded-2xl bg-brand-100 text-brand-600 flex items-center justify-center mb-3"><i data-lucide="car" class="size-[24px]"></i></div>
      <p class="text-sm text-ink-500 mb-4">Nessuna auto in catalogo. Inizia aggiungendone una.</p>
      <a href="/admin/auto-edit.php" class="btn-primary inline-flex">Aggiungi la prima auto</a>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($cars as $c): ?>
        <a href="/admin/auto-edit.php?id=<?= e($c['id']) ?>" class="card p-4 card-hover group">
          <div class="aspect-[4/3] rounded-xl overflow-hidden bg-gradient-to-br from-ink-100 to-ink-200 dark:from-ink-900 dark:to-ink-800 mb-3 flex items-center justify-center">
            <?php if ($c['cover_image'] && file_exists(__DIR__ . '/..' . $c['cover_image'])): ?>
              <img src="<?= e($c['cover_image']) ?>" class="h-full w-full object-cover">
            <?php else: ?>
              <i data-lucide="car-front" class="size-[64px] text-ink-300 dark:text-ink-700"></i>
            <?php endif; ?>
          </div>
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <div class="font-display font-bold truncate"><?= e($c['name']) ?></div>
              <div class="text-xs text-ink-500 mt-0.5 truncate"><?= e($c['brand']) ?> · <?= e($c['model']) ?></div>
            </div>
            <span class="<?= $c['active'] ? 'badge-success' : 'badge-soft' ?> text-[10px] shrink-0"><?= $c['active'] ? 'Attiva' : 'Off' ?></span>
          </div>
          <div class="flex items-center justify-between mt-3 pt-3 border-t border-ink-100 dark:border-ink-800/80">
            <span class="text-xs text-ink-500"><?= e(t('cat.' . $c['category'])) ?></span>
            <span class="font-display font-bold text-brand-600 tabular-nums"><?= fmtMoney((float)$c['daily_price']) ?>/g</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/admin-shell-bottom.php';
