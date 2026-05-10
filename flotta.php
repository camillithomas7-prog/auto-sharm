<?php
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/utils.php';

$cat = $_GET['cat'] ?? '';
$validCats = ['economy','compact','suv','luxury','minivan'];
$where = ['active = 1']; $params = [];
if (in_array($cat, $validCats, true)) { $where[] = 'category = ?'; $params[] = $cat; }
$cars = rows('SELECT * FROM cars WHERE ' . implode(' AND ', $where) . ' ORDER BY position ASC, daily_price ASC', $params);

$title = t('nav.fleet');
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/site-header.php';
$_lp = currentLang() !== 'it' ? '?lang=' . urlencode(currentLang()) : '';
?>
<section class="container-wide pt-12 pb-6 relative">
  <div class="badge-brand mb-3"><i data-lucide="car" class="size-[12px]"></i> Sharm El Sheikh</div>
  <h1 class="font-serif text-5xl md:text-7xl font-semibold tracking-tight text-gradient-chrome"><?= e(t('fleet.title')) ?></h1>
  <p class="text-ink-300 mt-4 max-w-2xl text-lg"><?= e(t('fleet.sub')) ?></p>

  <div class="flex flex-wrap gap-2 mt-8">
    <?php foreach (array_merge([['','fleet.filter.all']], array_map(fn($c) => [$c, 'fleet.filter.' . $c], $validCats)) as $f):
      $active = ($f[0] === '' && $cat === '') || ($f[0] === $cat);
      $href = '/flotta.php' . ($f[0] ? '?cat=' . urlencode($f[0]) : '') . ($f[0] && currentLang() !== 'it' ? '&lang=' . currentLang() : ((!$f[0] && currentLang() !== 'it') ? '?lang=' . currentLang() : ''));
    ?>
      <a href="<?= e($href) ?>" class="px-4 py-2 rounded-xl text-sm font-medium transition <?= $active ? 'bg-brand-500 text-white shadow-[0_0_28px_-6px_rgba(255,30,30,.85)] ring-1 ring-brand-400' : 'bg-white/[.04] text-ink-300 hover:text-white hover:bg-white/[.08] border border-white/[.07]' ?>"><?= e(t($f[1])) ?></a>
    <?php endforeach; ?>
  </div>
</section>

<section class="container-wide py-12">
  <?php if (!$cars): ?>
    <div class="card p-14 text-center">
      <div class="h-14 w-14 mx-auto rounded-2xl bg-white/[.05] flex items-center justify-center text-ink-400 mb-3"><i data-lucide="car-off" class="size-[24px]"></i></div>
      <p class="text-ink-400"><?= e(t('fleet.no_items')) ?></p>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($cars as $i => $c): ?>
        <a href="/auto.php?slug=<?= e($c['slug']) ?><?= $_lp ? '&lang=' . currentLang() : '' ?>" class="group block animate-slide-up" style="animation-delay:<?= ($i % 6) * 50 ?>ms">
          <div data-tilt class="tilt-wrap card-neon relative card-elev rounded-3xl overflow-hidden">
            <div class="tilt-inner relative">
              <span class="tilt-shine"></span>
              <div class="aspect-[4/3] overflow-hidden bg-gradient-to-br from-ink-900 to-ink-950 relative">
                <?php if ($c['cover_image'] && file_exists(__DIR__ . $c['cover_image'])): ?>
                  <img src="<?= e($c['cover_image']) ?>" alt="<?= e($c['name']) ?>" class="h-full w-full object-cover group-hover:scale-105 transition duration-700">
                <?php else: ?>
                  <div class="h-full w-full flex items-center justify-center"><i data-lucide="car-front" class="size-[100px] text-ink-700 group-hover:text-brand-500/60 transition"></i></div>
                <?php endif; ?>
                <span class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-[#06030a] to-transparent"></span>
                <div class="absolute top-3 left-3 badge bg-black/55 backdrop-blur text-white border border-white/[.08]"><?= e(t('cat.' . $c['category'])) ?></div>
                <div class="absolute top-3 right-3 badge bg-black/55 backdrop-blur text-white border border-white/[.08]"><i data-lucide="cog" class="size-[12px]"></i> <?= e(t('car.transmission.' . $c['transmission'])) ?></div>
              </div>
              <div class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                  <div class="font-display font-bold text-lg truncate text-white"><?= e($c['name']) ?></div>
                  <div class="flex items-center gap-3 text-xs text-ink-400 mt-1.5">
                    <span class="flex items-center gap-1"><i data-lucide="users" class="size-[12px]"></i> <?= (int)$c['seats'] ?></span>
                    <span class="flex items-center gap-1"><i data-lucide="briefcase" class="size-[12px]"></i> <?= (int)$c['luggage'] ?></span>
                    <span class="flex items-center gap-1"><i data-lucide="fuel" class="size-[12px]"></i> <?= e(t('car.fuel.' . $c['fuel'])) ?></span>
                  </div>
                </div>
                <div class="text-right shrink-0">
                  <span class="font-display font-extrabold text-2xl tabular-nums text-gradient-brand text-glow-brand"><?= fmtMoney((float)$c['daily_price']) ?></span>
                  <div class="text-[10px] uppercase tracking-wider text-ink-500"><?= e(t('common.per_day')) ?></div>
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
