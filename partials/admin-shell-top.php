<?php
$_user = currentUser();
$_flash = flash();
$_unread = 0;
try { $_unread = (int)val("SELECT COUNT(*) FROM notifications WHERE read_at IS NULL"); } catch (Throwable $e) {}
?>
<div class="min-h-screen flex bg-ink-50/60 dark:bg-ink-950">
  <aside x-data="{ open: false }" @toggle-sidebar.window="open = !open"
         class="fixed lg:sticky lg:top-0 inset-y-0 left-0 w-[260px] bg-white dark:bg-ink-900 border-r border-ink-100 dark:border-ink-800/80 z-40 transform transition-transform"
         :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <div class="h-[68px] flex items-center px-5 border-b border-ink-100 dark:border-ink-800/80">
      <a href="/admin/index.php" class="flex items-center gap-2"><img src="/assets/logo.png" alt="Auto Sharm" class="h-8 w-auto"></a>
    </div>
    <nav class="p-3 space-y-0.5 text-sm overflow-y-auto h-[calc(100vh-68px-72px)]">
      <?php
        $cur = basename($_SERVER['PHP_SELF']);
        $_menuMain = [
            ['index.php',         'Dashboard',     'layout-dashboard'],
            ['auto.php',          'Auto',          'car'],
            ['calendario.php',    'Calendario',    'calendar-days'],
            ['prenotazioni.php',  'Prenotazioni',  'bookmark-check'],
            ['clienti.php',       'Clienti',       'users'],
        ];
        $_menuTransfer = [];
        if (featureEnabled('transfer')) {
            $_menuTransfer = [
                ['transfer.php',           'Transfer',              'route'],
                ['transfer-bookings.php',  'Prenotazioni transfer', 'plane-landing'],
            ];
        }
        $sections = [
          ['MENU', $_menuMain],
        ];
        if ($_menuTransfer) $sections[] = ['TRANSFER', $_menuTransfer];
        $sections[] = ['STRATEGIA', [
            ['spese.php',         'Spese & bilancio', 'wallet'],
            ['coupon.php',        'Coupon',           'tag'],
            ['recensioni.php',    'Recensioni',       'star'],
        ]];
        $sections[] = ['SISTEMA', [
            ['impostazioni.php',  'Impostazioni',     'settings'],
            ['notifiche.php',     'Notifiche',        'bell'],
        ]];
        foreach ($sections as $sec):
      ?>
        <div class="text-[10px] font-semibold tracking-wider text-ink-400 uppercase px-3 mt-4 mb-1.5"><?= e($sec[0]) ?></div>
        <?php foreach ($sec[1] as $i):
          $active = $cur === $i[0];
        ?>
          <a href="/admin/<?= e($i[0]) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl <?= $active ? 'bg-brand-500 text-white shadow-[0_8px_20px_-8px_rgba(220,28,28,.5)]' : 'text-ink-700 dark:text-ink-300 hover:bg-ink-100 dark:hover:bg-ink-800/60' ?>">
            <i data-lucide="<?= $i[2] ?>" class="size-[16px]"></i>
            <span><?= e($i[1]) ?></span>
            <?php if ($i[0] === 'notifiche.php' && $_unread > 0): ?>
              <span class="ml-auto badge bg-brand-500 text-white text-[10px]"><?= $_unread ?></span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </nav>
    <div class="absolute bottom-0 inset-x-0 p-3 border-t border-ink-100 dark:border-ink-800/80 flex items-center gap-2.5">
      <span class="h-9 w-9 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 text-white flex items-center justify-center font-semibold text-xs"><?= e(strtoupper(mb_substr($_user['name'] ?? 'A', 0, 1))) ?></span>
      <div class="min-w-0 flex-1">
        <div class="text-sm font-semibold truncate"><?= e($_user['name'] ?? 'Admin') ?></div>
        <div class="text-xs text-ink-500 truncate"><?= e($_user['email'] ?? '') ?></div>
      </div>
      <a href="/admin/logout.php" class="btn-ghost p-2" title="Esci"><i data-lucide="log-out" class="size-[16px]"></i></a>
    </div>
  </aside>

  <div class="flex-1 min-w-0">
    <header class="sticky top-0 z-30 h-[68px] bg-white/85 dark:bg-ink-950/85 backdrop-blur-xl border-b border-ink-100 dark:border-ink-800/80 flex items-center px-5 gap-3">
      <button @click="$dispatch('toggle-sidebar')" class="lg:hidden btn-ghost p-2"><i data-lucide="menu" class="size-[20px]"></i></button>
      <div class="flex-1">
        <?php if (!empty($_subtitle)): ?>
          <div class="text-xs text-ink-500"><?= e($_subtitle) ?></div>
        <?php endif; ?>
        <div class="font-display font-semibold"><?= e($title ?? 'Admin') ?></div>
      </div>
      <a href="/" target="_blank" class="btn-outline text-sm h-10 hidden sm:inline-flex"><i data-lucide="external-link" class="size-[14px]"></i> Sito</a>
    </header>
    <main class="p-5 lg:p-7 max-w-full">
      <?php if ($_flash): ?>
        <div class="mb-5 card p-3 text-sm <?= $_flash['type'] === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700' ?>"><?= e($_flash['msg']) ?></div>
      <?php endif; ?>
