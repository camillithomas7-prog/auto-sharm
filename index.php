<?php
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/utils.php';

$cars = rows('SELECT * FROM cars WHERE active = 1 ORDER BY position ASC, daily_price ASC LIMIT 6');
$cheapest = (float)(val('SELECT MIN(daily_price) FROM cars WHERE active = 1') ?: 22);

$transfersHome = [];
if (featureEnabled('transfer')) {
    $transfersHome = rows('SELECT * FROM transfers WHERE active = 1 ORDER BY position ASC, price ASC LIMIT 3');
}
$transferCheapest = (float)(val('SELECT MIN(price) FROM transfers WHERE active = 1') ?: 0);

$waLink = whatsappLink();

$title = t('meta.home_title');
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/site-header.php';
$_lp = currentLang() !== 'it' ? '?lang=' . urlencode(currentLang()) : '';
?>
<!-- ====================== HERO ====================== -->
<section class="relative overflow-hidden">
  <!-- griglia prospettica al ground -->
  <div aria-hidden="true" class="absolute inset-x-0 bottom-0 h-[420px] pointer-events-none"
    style="background-image: linear-gradient(rgba(255,30,30,.18) 1px, transparent 1px), linear-gradient(90deg, rgba(255,30,30,.18) 1px, transparent 1px); background-size: 60px 60px; mask-image: linear-gradient(to top, #000 0%, transparent 78%); transform: perspective(700px) rotateX(58deg); transform-origin: bottom;"></div>

  <div class="container-wide relative pt-16 pb-24 sm:pt-24 sm:pb-32">
    <div class="grid lg:grid-cols-12 grid-cols-1 gap-12 items-center">
      <div class="lg:col-span-7 animate-rise">
        <div class="badge-brand"><span class="h-1.5 w-1.5 rounded-full bg-brand-500 animate-breathe shadow-[0_0_10px_2px_rgba(255,30,30,.8)]"></span> <?= e(t('home.hero.kicker')) ?></div>

        <h1 class="font-serif text-[44px] sm:text-7xl lg:text-[88px] leading-[.95] font-semibold tracking-tight mt-5 text-balance">
          <span class="block text-gradient-chrome">Auto Sharm</span>
          <span class="block text-gradient-brand text-glow-brand"><?= e(t('home.hero.title')) ?></span>
        </h1>

        <p class="mt-6 text-lg text-ink-300 max-w-xl text-pretty"><?= e(t('home.hero.sub')) ?></p>

        <div class="flex flex-wrap gap-3 mt-8">
          <a href="/flotta.php<?= $_lp ?>" class="btn-primary text-base h-12 px-7"><?= e(t('home.hero.cta_primary')) ?> <i data-lucide="arrow-right" class="size-[16px]"></i></a>
          <a href="<?= e($waLink) ?>" target="_blank" rel="noopener" class="btn-outline text-base h-12 px-6"><i data-lucide="message-circle" class="size-[16px]"></i> <?= e(t('home.hero.cta_secondary')) ?></a>
        </div>

        <!-- Mini-stat strip neon -->
        <div class="mt-10 grid grid-cols-3 max-w-md gap-0 rounded-2xl border border-white/[.08] bg-white/[.025] backdrop-blur-xl overflow-hidden">
          <?php foreach ([
            ['truck',  '24/7', t('home.feature.delivery.title')],
            ['shield', '100%', t('home.feature.insurance.title')],
            ['headphones', 'IT/EN', t('home.feature.support.title')],
          ] as $i => $kpi): ?>
            <div class="px-4 py-3 text-center <?= $i ? 'border-l border-white/[.06]' : '' ?>">
              <div class="font-display font-bold text-xl text-gradient-brand tabular-nums"><?= e($kpi[1]) ?></div>
              <div class="text-[10px] uppercase tracking-wider text-ink-400 mt-0.5"><?= e($kpi[2]) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- HERO CARD 3D -->
      <div class="lg:col-span-5 relative animate-rise" style="animation-delay: .15s">
        <div data-tilt class="tilt-wrap relative">
          <!-- alone radiale -->
          <div class="absolute -inset-10 bg-[radial-gradient(closest-side,rgba(255,30,30,.55),transparent_70%)] blur-2xl"></div>

          <div class="tilt-inner relative card-elev p-8 rounded-[28px] overflow-hidden">
            <span class="tilt-shine"></span>
            <!-- bordo conic neon -->
            <span aria-hidden="true" class="absolute inset-0 rounded-[28px]" style="padding:1px; background: conic-gradient(from var(--ang,0deg), rgba(255,30,30,0) 35%, rgba(255,80,80,.7) 50%, rgba(255,30,30,0) 65%, rgba(255,30,30,0) 100%); -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); -webkit-mask-composite: xor; mask-composite: exclude;"></span>

            <div class="flex items-center gap-3 mb-5">
              <span class="h-12 w-12 rounded-2xl flex items-center justify-center bg-gradient-to-br from-brand-400 to-brand-700 shadow-[0_0_24px_-4px_rgba(255,30,30,.85)]">
                <i data-lucide="key-round" class="size-[22px] text-white"></i>
              </span>
              <div>
                <div class="text-xs uppercase tracking-[0.2em] text-ink-400">Premium key</div>
                <div class="font-display font-bold text-white">Auto Sharm Club</div>
              </div>
            </div>

            <div class="space-y-2.5">
              <?php foreach ([
                ['check-circle-2', t('amenity.km_illimitati')],
                ['check-circle-2', t('amenity.assicurazione')],
                ['check-circle-2', t('amenity.consegna_hotel')],
                ['check-circle-2', t('amenity.aria_condizionata')],
              ] as $r): ?>
                <div class="flex items-center gap-2.5 text-sm text-ink-200">
                  <span class="h-6 w-6 rounded-full flex items-center justify-center bg-brand-500/15 ring-1 ring-brand-500/40">
                    <i data-lucide="check" class="size-[12px] text-brand-300"></i>
                  </span>
                  <span><?= e($r[1]) ?></span>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="mt-7 pt-6 border-t border-white/[.07]">
              <div class="text-[10px] uppercase tracking-[0.25em] text-ink-400"><?= e(t('common.from')) ?></div>
              <div class="flex items-baseline gap-1.5 mt-1">
                <span class="font-display text-6xl font-extrabold tabular-nums text-gradient-brand text-glow-brand"><?= number_format($cheapest, 0) ?>€</span>
                <span class="text-ink-400"><?= e(t('common.per_day')) ?></span>
              </div>
              <div class="mt-3 h-1 rounded-full bg-gradient-to-r from-brand-500 via-brand-400 to-transparent shadow-[0_0_18px_2px_rgba(255,30,30,.55)]"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ====================== STAT RIBBON ====================== -->
<section class="container-wide relative">
  <div class="rounded-2xl border border-white/[.07] bg-white/[.02] backdrop-blur-xl px-6 py-5 grid grid-cols-2 sm:grid-cols-4 gap-4 overflow-hidden relative">
    <span aria-hidden="true" class="absolute -left-10 -top-10 h-40 w-40 rounded-full bg-brand-500/30 blur-3xl"></span>
    <?php foreach ([
      ['truck',  t('home.feature.delivery.title'),  t('home.feature.delivery.sub')],
      ['shield', t('home.feature.insurance.title'), t('home.feature.insurance.sub')],
      ['headphones', t('home.feature.support.title'), t('home.feature.support.sub')],
      ['fuel',   t('home.feature.fuel.title'),      t('home.feature.fuel.sub')],
    ] as $i => $f): ?>
      <div class="flex items-start gap-3 relative">
        <span class="h-10 w-10 shrink-0 rounded-xl bg-brand-500/10 ring-1 ring-brand-500/35 flex items-center justify-center text-brand-300 shadow-[0_0_18px_-4px_rgba(255,30,30,.6)]">
          <i data-lucide="<?= $f[0] ?>" class="size-[18px]"></i>
        </span>
        <div>
          <div class="font-semibold text-white text-sm"><?= e($f[1]) ?></div>
          <div class="text-xs text-ink-400 mt-0.5"><?= e($f[2]) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ====================== FLEET PREVIEW ====================== -->
<section class="container-wide py-20">
  <div class="flex items-end justify-between mb-12 flex-wrap gap-3">
    <div>
      <div class="badge-brand mb-3"><i data-lucide="car" class="size-[12px]"></i> <?= e(t('home.fleet.badge')) ?></div>
      <h2 class="font-serif text-4xl md:text-5xl font-semibold tracking-tight text-balance"><?= e(t('home.fleet.title')) ?></h2>
      <p class="text-ink-400 mt-2 max-w-xl"><?= e(t('home.fleet.sub')) ?></p>
    </div>
    <a href="/flotta.php<?= $_lp ?>" class="btn-outline hidden sm:inline-flex"><?= e(t('cta.see_fleet')) ?> <i data-lucide="arrow-right" class="size-[14px]"></i></a>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($cars as $i => $c): ?>
      <a href="/auto.php?slug=<?= e($c['slug']) ?><?= $_lp ? '&lang=' . currentLang() : '' ?>" class="group block animate-slide-up" style="animation-delay:<?= $i * 60 ?>ms">
        <div data-tilt class="tilt-wrap card-neon relative card-elev rounded-3xl overflow-hidden">
          <div class="tilt-inner relative">
            <span class="tilt-shine"></span>
            <div class="aspect-[4/3] overflow-hidden bg-gradient-to-br from-ink-900 to-ink-950 relative">
              <?php if ($c['cover_image'] && file_exists(__DIR__ . $c['cover_image'])): ?>
                <img src="<?= e($c['cover_image']) ?>" alt="<?= e($c['name']) ?>" class="h-full w-full object-cover group-hover:scale-105 transition duration-700">
              <?php else: ?>
                <div class="h-full w-full flex items-center justify-center">
                  <i data-lucide="car-front" class="size-[100px] text-ink-700 group-hover:text-brand-500/60 transition"></i>
                </div>
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
                  <span class="flex items-center gap-1"><i data-lucide="snowflake" class="size-[12px]"></i> A/C</span>
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

  <div class="mt-10 sm:hidden text-center">
    <a href="/flotta.php<?= $_lp ?>" class="btn-outline inline-flex"><?= e(t('cta.see_fleet')) ?> <i data-lucide="arrow-right" class="size-[14px]"></i></a>
  </div>
</section>

<!-- ====================== TRANSFER PREVIEW ====================== -->
<?php if ($transfersHome): ?>
<section class="container-wide py-20 relative">
  <span aria-hidden="true" class="absolute -left-24 top-20 h-72 w-72 rounded-full bg-brand-500/15 blur-3xl pointer-events-none"></span>
  <div class="flex items-end justify-between mb-12 flex-wrap gap-3 relative">
    <div>
      <div class="badge-brand mb-3"><i data-lucide="plane-landing" class="size-[12px]"></i> <?= e(t('transfer.badge')) ?></div>
      <h2 class="font-serif text-4xl md:text-5xl font-semibold tracking-tight text-balance"><?= e(t('transfer.hero.title')) ?></h2>
      <p class="text-ink-400 mt-2 max-w-xl"><?= e(t('transfer.hero.sub')) ?></p>
    </div>
    <a href="/transfer.php<?= $_lp ?>" class="btn-outline hidden sm:inline-flex"><?= e(t('transfer.see_routes')) ?> <i data-lucide="arrow-right" class="size-[14px]"></i></a>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($transfersHome as $i => $tr): ?>
      <a href="/transfer-detail.php?slug=<?= e($tr['slug']) ?><?= $_lp ? '&lang=' . currentLang() : '' ?>" class="group block animate-slide-up" style="animation-delay:<?= $i * 60 ?>ms">
        <div data-tilt class="tilt-wrap card-neon relative card-elev rounded-3xl overflow-hidden">
          <div class="tilt-inner relative">
            <span class="tilt-shine"></span>
            <div class="aspect-[4/3] overflow-hidden bg-gradient-to-br from-ink-900 to-ink-950 relative">
              <?php if ($tr['cover_image'] && file_exists(__DIR__ . $tr['cover_image'])): ?>
                <img src="<?= e($tr['cover_image']) ?>" alt="<?= e($tr['name']) ?>" class="h-full w-full object-cover group-hover:scale-105 transition duration-700">
              <?php else: ?>
                <div class="h-full w-full flex items-center justify-center"><i data-lucide="route" class="size-[100px] text-ink-700 group-hover:text-brand-500/60 transition"></i></div>
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

  <div class="mt-10 sm:hidden text-center">
    <a href="/transfer.php<?= $_lp ?>" class="btn-outline inline-flex"><?= e(t('transfer.see_routes')) ?> <i data-lucide="arrow-right" class="size-[14px]"></i></a>
  </div>
</section>
<?php endif; ?>

<!-- ====================== HOW IT WORKS ====================== -->
<section id="come-funziona" class="container-wide py-24">
  <div class="text-center max-w-2xl mx-auto mb-14">
    <div class="badge-brand mb-3"><?= e(t('home.howit.badge')) ?></div>
    <h2 class="font-serif text-4xl md:text-5xl font-semibold tracking-tight text-balance"><?= e(t('home.howit.title')) ?></h2>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <?php foreach ([
      ['1', 'car-front',     t('home.howit.s1.title'), t('home.howit.s1.sub')],
      ['2', 'message-circle',t('home.howit.s2.title'), t('home.howit.s2.sub')],
      ['3', 'key-round',     t('home.howit.s3.title'), t('home.howit.s3.sub')],
    ] as $i => $s): ?>
      <div data-tilt class="tilt-wrap relative">
        <div class="tilt-inner card-elev card-neon p-7 rounded-3xl relative overflow-hidden animate-slide-up" style="animation-delay:<?= $i * 80 ?>ms">
          <span class="tilt-shine"></span>
          <span class="absolute top-4 right-5 font-serif font-bold text-[120px] leading-none text-brand-500/10"><?= e($s[0]) ?></span>
          <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-brand-400 via-brand-500 to-brand-700 text-white flex items-center justify-center shadow-[0_0_30px_-6px_rgba(255,30,30,.85)]">
            <i data-lucide="<?= $s[1] ?>" class="size-[22px]"></i>
          </div>
          <h3 class="font-display font-bold text-xl mt-5 relative text-white"><?= e($s[2]) ?></h3>
          <p class="text-ink-400 text-sm mt-2 relative leading-relaxed"><?= e($s[3]) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ====================== ABOUT ALEX (FOUNDER) ====================== -->
<section class="container-wide py-20 sm:py-24 relative">
  <span aria-hidden="true" class="absolute -right-32 top-10 h-96 w-96 rounded-full bg-brand-500/15 blur-3xl pointer-events-none"></span>
  <span aria-hidden="true" class="absolute -left-24 bottom-0 h-72 w-72 rounded-full bg-brand-700/20 blur-3xl pointer-events-none"></span>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center relative">
    <!-- Foto Alex con cornici decorative -->
    <div class="relative animate-rise">
      <div class="absolute -inset-4 bg-[radial-gradient(closest-side,rgba(255,30,30,.45),transparent_70%)] blur-2xl -z-10"></div>
      <div class="absolute -top-3 -left-3 h-24 w-24 rounded-2xl bg-brand-500/25 -z-10 ring-1 ring-brand-500/40"></div>
      <div class="absolute -bottom-3 -right-3 h-32 w-32 rounded-3xl bg-white/[.05] -z-10 ring-1 ring-white/[.08]"></div>
      <div class="relative rounded-3xl overflow-hidden ring-1 ring-white/[.08] shadow-pop">
        <img src="/assets/founder-alex.jpg?v=1" alt="Alex — fondatore Auto Sharm" loading="lazy" class="w-full h-auto block">
      </div>
      <div class="absolute bottom-5 left-5 bg-black/70 backdrop-blur-xl rounded-2xl px-4 py-2.5 ring-1 ring-white/[.1] flex items-center gap-2.5">
        <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-breathe shadow-[0_0_10px_2px_rgba(16,185,129,.7)]"></span>
        <span class="text-sm font-medium text-white"><?= e(t('home.about.available')) ?></span>
      </div>
    </div>

    <!-- Testo -->
    <div class="animate-rise" style="animation-delay:.15s">
      <div class="badge-brand mb-4"><i data-lucide="user-round" class="size-[14px]"></i> <?= e(t('home.about.badge')) ?></div>
      <h2 class="font-serif text-4xl md:text-5xl lg:text-6xl font-semibold tracking-tight text-balance">
        <span class="text-gradient-brand text-glow-brand">Alex</span><span class="text-white"><?= e(t('home.about.title_post')) ?></span>
      </h2>

      <div class="mt-6 space-y-4 text-ink-300 text-[17px] leading-relaxed text-pretty">
        <p><?= t('home.about.p1') ?></p>
        <p><?= t('home.about.p2') ?></p>
      </div>

      <div class="grid grid-cols-3 gap-4 mt-8">
        <?php foreach ([
          ['check-circle-2', t('home.about.feature1.t'), t('home.about.feature1.d')],
          ['message-circle', t('home.about.feature2.t'), t('home.about.feature2.d')],
          ['map-pin',        t('home.about.feature3.t'), t('home.about.feature3.d')],
        ] as $f): ?>
          <div>
            <div class="flex items-center gap-2"><i data-lucide="<?= $f[0] ?>" class="size-[20px] text-brand-400 shrink-0"></i><div class="font-display text-base sm:text-lg font-bold text-white"><?= e($f[1]) ?></div></div>
            <div class="text-xs text-ink-400 mt-1"><?= e($f[2]) ?></div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="flex flex-wrap gap-3 mt-8">
        <a href="<?= e($waLink) ?>" target="_blank" rel="noopener" class="btn-primary"><i data-lucide="message-circle" class="size-[16px]"></i> <?= e(t('home.about.cta_wa')) ?></a>
        <a href="/flotta.php<?= $_lp ?>" class="btn-outline"><i data-lucide="car" class="size-[16px]"></i> <?= e(t('home.about.cta_fleet')) ?></a>
      </div>

      <blockquote class="mt-8 pl-5 border-l-2 border-brand-500 italic text-ink-400 text-pretty">
        <?= e(t('home.about.quote')) ?>
      </blockquote>
    </div>
  </div>
</section>

<!-- ====================== CTA STRIP ====================== -->
<section class="container-wide pb-20">
  <div class="relative rounded-3xl overflow-hidden border border-white/[.07] bg-gradient-to-br from-[#1a0508] via-[#0a0306] to-[#06030a]">
    <span aria-hidden="true" class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-brand-500/40 blur-3xl"></span>
    <span aria-hidden="true" class="absolute -left-24 bottom-[-120px] h-80 w-80 rounded-full bg-brand-700/30 blur-3xl"></span>
    <span aria-hidden="true" class="absolute inset-0 opacity-30" style="background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size: 40px 40px;"></span>

    <div class="relative p-8 sm:p-14 grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
      <div class="lg:col-span-2">
        <div class="badge-brand mb-4"><i data-lucide="zap" class="size-[12px]"></i> Live now</div>
        <h3 class="font-serif text-3xl sm:text-5xl font-semibold tracking-tight text-balance text-white"><?= e(t('home.hero.title')) ?></h3>
        <p class="text-ink-300 mt-3 max-w-2xl"><?= e(t('home.hero.sub')) ?></p>
      </div>
      <div class="flex flex-wrap gap-3 lg:justify-end">
        <a href="/flotta.php<?= $_lp ?>" class="btn-primary h-12 px-6"><?= e(t('cta.book')) ?> <i data-lucide="arrow-right" class="size-[16px]"></i></a>
        <a href="<?= e($waLink) ?>" target="_blank" rel="noopener" class="btn-outline h-12"><i data-lucide="message-circle" class="size-[16px]"></i> WhatsApp</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/partials/site-footer.php';
