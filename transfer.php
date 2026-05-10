<?php
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/utils.php';

if (!featureEnabled('transfer')) {
    http_response_code(404);
    $title = '404';
    require __DIR__ . '/partials/head.php';
    require __DIR__ . '/partials/site-header.php';
    echo '<div class="container-narrow card p-14 mt-20 text-center"><h1 class="font-serif text-3xl">' . e(t('fleet.no_items')) . '</h1></div>';
    require __DIR__ . '/partials/site-footer.php';
    exit;
}

$transfers = rows('SELECT * FROM transfers WHERE active = 1 ORDER BY position ASC, price ASC');

$cPhone = setting('contact_phone', cfg('site.phone'));
$cWa = setting('contact_whatsapp', cfg('site.whatsapp') ?: $cPhone);
$waN = $cWa ? preg_replace('/\D/', '', $cWa) : '';
$waLink = $waN ? 'https://wa.me/' . $waN . '?text=' . rawurlencode(t('contact.wa_msg')) : '/contatti.php';

$title = t('meta.transfers_title');
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/site-header.php';
$_lp = currentLang() !== 'it' ? '?lang=' . urlencode(currentLang()) : '';
?>
<section class="relative overflow-hidden">
  <div aria-hidden="true" class="absolute inset-x-0 bottom-0 h-[300px] pointer-events-none"
    style="background-image: linear-gradient(rgba(255,30,30,.15) 1px, transparent 1px), linear-gradient(90deg, rgba(255,30,30,.15) 1px, transparent 1px); background-size: 60px 60px; mask-image: linear-gradient(to top, #000 0%, transparent 80%); transform: perspective(700px) rotateX(58deg); transform-origin: bottom;"></div>

  <div class="container-wide relative pt-14 pb-14 sm:pt-20 sm:pb-20">
    <div class="badge-brand"><i data-lucide="plane-landing" class="size-[12px]"></i> <?= e(t('transfer.badge')) ?></div>
    <h1 class="font-serif text-5xl md:text-7xl font-semibold tracking-tight mt-5 max-w-3xl text-balance">
      <span class="text-gradient-chrome"><?= e(t('transfer.hero.title')) ?></span>
    </h1>
    <p class="text-lg text-ink-300 mt-5 max-w-2xl"><?= e(t('transfer.hero.sub')) ?></p>
  </div>
</section>

<section class="container-wide pb-16 -mt-2">
  <div class="flex items-end justify-between mb-10 flex-wrap gap-3">
    <div>
      <h2 class="font-serif text-3xl md:text-4xl font-semibold tracking-tight text-white"><?= e(t('transfer.list.title')) ?></h2>
      <p class="text-ink-400 mt-1.5 max-w-xl text-sm"><?= e(t('transfer.list.sub')) ?></p>
    </div>
    <a href="<?= e($waLink) ?>" target="_blank" rel="noopener" class="btn-outline hidden sm:inline-flex"><i data-lucide="message-circle" class="size-[14px]"></i> WhatsApp</a>
  </div>

  <?php if (!$transfers): ?>
    <div class="card p-14 text-center">
      <div class="h-14 w-14 mx-auto rounded-2xl bg-white/[.05] flex items-center justify-center text-ink-400 mb-3"><i data-lucide="route" class="size-[24px]"></i></div>
      <p class="text-ink-400"><?= e(t('fleet.no_items')) ?></p>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($transfers as $i => $tr): ?>
        <a href="/transfer-detail.php?slug=<?= e($tr['slug']) ?><?= $_lp ? '&lang=' . currentLang() : '' ?>" class="group block animate-slide-up" style="animation-delay:<?= ($i % 6) * 60 ?>ms">
          <div data-tilt class="tilt-wrap card-neon relative card-elev rounded-3xl overflow-hidden">
            <div class="tilt-inner relative">
              <span class="tilt-shine"></span>
              <div class="aspect-[4/3] overflow-hidden bg-gradient-to-br from-ink-900 to-ink-950 relative">
                <?php if ($tr['cover_image'] && file_exists(__DIR__ . $tr['cover_image'])): ?>
                  <img src="<?= e($tr['cover_image']) ?>" alt="<?= e($tr['name']) ?>" class="h-full w-full object-cover group-hover:scale-105 transition duration-700">
                <?php else: ?>
                  <div class="h-full w-full flex items-center justify-center">
                    <i data-lucide="route" class="size-[100px] text-ink-700 group-hover:text-brand-500/60 transition"></i>
                  </div>
                <?php endif; ?>
                <span class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-[#06030a] to-transparent"></span>
                <div class="absolute top-3 left-3 badge bg-black/55 backdrop-blur text-white border border-white/[.08]"><i data-lucide="users" class="size-[12px]"></i> <?= (int)$tr['vehicle_capacity'] ?> <?= e(t('transfer.passengers')) ?></div>
                <div class="absolute top-3 right-3 badge bg-black/55 backdrop-blur text-white border border-white/[.08]"><i data-lucide="clock" class="size-[12px]"></i> <?= (int)$tr['duration_min'] ?> <?= e(t('transfer.minutes')) ?></div>
              </div>
              <div class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                  <div class="font-display font-bold text-lg truncate text-white"><?= e($tr['from_location']) ?> → <?= e($tr['to_location']) ?></div>
                  <div class="flex items-center gap-3 text-xs text-ink-400 mt-1.5">
                    <span class="flex items-center gap-1"><i data-lucide="car" class="size-[12px]"></i> <?= e(t('transfer.veh.' . $tr['vehicle_type'])) ?></span>
                  </div>
                </div>
                <div class="text-right shrink-0">
                  <span class="font-display font-extrabold text-2xl tabular-nums text-gradient-brand text-glow-brand"><?= fmtMoney((float)$tr['price']) ?></span>
                </div>
              </div>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/partials/site-footer.php';
