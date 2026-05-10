<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/utils.php';
requireAdmin();

$transfers = rows('SELECT * FROM transfers ORDER BY position ASC, name ASC');

$title = 'Transfer';
$_subtitle = 'Tratte aeroporto';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/admin-shell-top.php';
?>
<div class="space-y-5">
  <div class="flex items-end justify-between flex-wrap gap-3">
    <div>
      <h1 class="font-display text-2xl sm:text-3xl font-bold">Tratte transfer</h1>
      <p class="text-ink-500 text-sm mt-1">Tratte aeroporto-hotel a prezzo fisso. La sezione è visibile sul sito solo se attivata in Impostazioni.</p>
    </div>
    <a href="/admin/transfer-edit.php" class="btn-primary"><i data-lucide="plus" class="size-[16px]"></i> Nuova tratta</a>
  </div>

  <?php if (!featureEnabled('transfer')): ?>
    <div class="card p-4 bg-amber-50 border-amber-200 text-sm text-amber-800 flex items-center gap-3">
      <i data-lucide="alert-triangle" class="size-[18px]"></i>
      <div class="flex-1">La sezione Transfer è <strong>disattivata</strong> sul sito pubblico. Vai in <a href="/admin/impostazioni.php" class="underline">Impostazioni</a> per attivarla.</div>
    </div>
  <?php endif; ?>

  <?php if (!$transfers): ?>
    <div class="card p-12 text-center">
      <div class="h-14 w-14 mx-auto rounded-2xl bg-brand-100 text-brand-600 flex items-center justify-center mb-3"><i data-lucide="route" class="size-[24px]"></i></div>
      <p class="text-sm text-ink-500 mb-4">Nessuna tratta. Aggiungi la prima.</p>
      <a href="/admin/transfer-edit.php" class="btn-primary inline-flex">Aggiungi tratta</a>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($transfers as $tr): ?>
        <a href="/admin/transfer-edit.php?id=<?= e($tr['id']) ?>" class="card p-4 card-hover group">
          <div class="aspect-[4/3] rounded-xl overflow-hidden bg-gradient-to-br from-ink-100 to-ink-200 dark:from-ink-900 dark:to-ink-800 mb-3 flex items-center justify-center">
            <?php if ($tr['cover_image'] && file_exists(__DIR__ . '/..' . $tr['cover_image'])): ?>
              <img src="<?= e($tr['cover_image']) ?>" class="h-full w-full object-cover">
            <?php else: ?>
              <i data-lucide="route" class="size-[64px] text-ink-300 dark:text-ink-700"></i>
            <?php endif; ?>
          </div>
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <div class="font-display font-bold truncate"><?= e($tr['name']) ?></div>
              <div class="text-xs text-ink-500 mt-0.5 truncate"><?= e($tr['from_location']) ?> → <?= e($tr['to_location']) ?></div>
            </div>
            <span class="<?= $tr['active'] ? 'badge-success' : 'badge-soft' ?> text-[10px] shrink-0"><?= $tr['active'] ? 'Attiva' : 'Off' ?></span>
          </div>
          <div class="flex items-center justify-between mt-3 pt-3 border-t border-ink-100 dark:border-ink-800/80">
            <span class="text-xs text-ink-500"><?= e(t('transfer.veh.' . $tr['vehicle_type'])) ?> · <?= (int)$tr['vehicle_capacity'] ?> pax</span>
            <span class="font-display font-bold text-brand-600 tabular-nums"><?= fmtMoney((float)$tr['price']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/admin-shell-bottom.php';
