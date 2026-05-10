<?php $curLang = currentLang(); $_curPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?'); ?>
<header x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 8"
  class="sticky top-0 z-40 transition-all duration-300"
  :class="scrolled ? 'backdrop-blur-2xl bg-[rgba(8,3,10,.72)] dark:border-b dark:border-white/[.07]' : 'bg-transparent'">
  <div class="container-wide h-[76px] flex items-center justify-between gap-2">
    <a href="/<?= $curLang !== 'it' ? '?lang=' . e($curLang) : '' ?>" class="flex items-center gap-2 group shrink-0 relative">
      <span class="absolute -inset-3 rounded-full bg-brand-500/30 blur-2xl opacity-0 group-hover:opacity-100 transition"></span>
      <img src="/assets/logo.png" alt="Auto Sharm" class="h-11 w-auto relative drop-shadow-[0_0_12px_rgba(255,30,30,.45)] group-hover:scale-[1.05] transition-transform" />
    </a>

    <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
      <?php
      $_navItems = [
        ['/', 'nav.home'],
        ['/flotta.php', 'nav.fleet'],
      ];
      if (featureEnabled('transfer')) $_navItems[] = ['/transfer.php', 'nav.transfers'];
      $_navItems[] = ['/#come-funziona', 'nav.howit'];
      $_navItems[] = ['/contatti.php', 'nav.contact'];
      foreach ($_navItems as $n):
        $href = $n[0] . ($curLang !== 'it' && strpos($n[0], '#') === false ? '?lang=' . urlencode($curLang) : '');
        $active = $_curPath === $n[0] || ($n[0] === '/' && $_curPath === '/index.php');
      ?>
        <a href="<?= e($href) ?>" class="relative px-3.5 py-2 rounded-xl <?= $active ? 'text-white' : 'text-ink-300 hover:text-white' ?> transition group">
          <span class="relative z-10"><?= e(t($n[1])) ?></span>
          <?php if ($active): ?>
            <span class="absolute left-3.5 right-3.5 -bottom-0.5 h-px bg-gradient-to-r from-transparent via-brand-500 to-transparent shadow-[0_0_14px_2px_rgba(255,30,30,.7)]"></span>
          <?php endif; ?>
          <span class="absolute inset-0 rounded-xl opacity-0 group-hover:opacity-100 transition bg-white/[.04]"></span>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="flex items-center gap-1.5 sm:gap-2">
      <div x-data="{ open: false }" @click.outside="open=false" class="relative">
        <button @click="open=!open" :aria-expanded="open" aria-label="<?= e(t('meta.lang_label')) ?>" class="h-10 px-2.5 sm:px-3 rounded-xl flex items-center gap-1.5 text-ink-200 hover:text-white hover:bg-white/[.05] transition text-sm font-medium border border-white/[.08]">
          <span class="text-base leading-none"><?= $LANGUAGES[$curLang]['flag'] ?></span>
          <span class="hidden sm:inline uppercase tracking-wider text-xs"><?= e($curLang) ?></span>
          <i data-lucide="chevron-down" class="size-[14px] opacity-60"></i>
        </button>
        <div x-show="open" x-cloak x-transition.opacity class="absolute right-0 mt-2 w-44 rounded-xl bg-[rgba(15,8,15,.95)] backdrop-blur-xl border border-white/[.08] shadow-pop overflow-hidden z-50" style="display:none">
          <?php foreach ($LANGUAGES as $lc => $l): ?>
            <a href="<?= e(langSwitch($lc)) ?>" class="flex items-center gap-2.5 px-3.5 py-2.5 text-sm hover:bg-brand-500/10 <?= $lc === $curLang ? 'bg-brand-500/15 text-brand-200 font-semibold' : 'text-ink-200' ?>">
              <span class="text-lg leading-none"><?= $l['flag'] ?></span>
              <span class="flex-1"><?= e($l['native']) ?></span>
              <?php if ($lc === $curLang): ?><i data-lucide="check" class="size-[14px]"></i><?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <a href="/admin/login.php" class="hidden lg:inline-flex h-10 px-3.5 rounded-xl items-center text-sm text-ink-300 hover:text-white hover:bg-white/[.05] transition border border-white/[.08]"><?= e(t('nav.admin')) ?></a>
      <a href="/flotta.php<?= $curLang !== 'it' ? '?lang=' . e($curLang) : '' ?>" class="hidden md:inline-flex btn-primary"><?= e(t('cta.book')) ?> <i data-lucide="arrow-right" class="size-[14px]"></i></a>
      <button class="md:hidden btn-ghost p-2 text-white" @click="$dispatch('toggle-menu')" aria-label="Menu"><i data-lucide="menu" class="size-[20px]"></i></button>
    </div>
  </div>
  <!-- riga neon sotto header -->
  <div class="neon-divider"></div>
</header>

<div x-data="{ open: false }" @toggle-menu.window="open = !open" x-show="open" x-cloak
     class="md:hidden fixed inset-0 z-50 bg-[rgba(6,3,8,.97)] backdrop-blur-2xl p-6 animate-fade-in overflow-y-auto" @click.self="open=false">
  <div class="flex items-center justify-between">
    <img src="/assets/logo.png" alt="Auto Sharm" class="h-10 w-auto drop-shadow-[0_0_12px_rgba(255,30,30,.5)]">
    <button @click="open=false" class="btn-ghost text-white"><i data-lucide="x" class="size-[22px]"></i></button>
  </div>
  <div class="flex flex-col gap-1 mt-10 text-2xl font-display font-bold">
    <?php
    $_mobileNav = [
      ['/', 'nav.home'],
      ['/flotta.php', 'nav.fleet'],
    ];
    if (featureEnabled('transfer')) $_mobileNav[] = ['/transfer.php', 'nav.transfers'];
    $_mobileNav[] = ['/#come-funziona', 'nav.howit'];
    $_mobileNav[] = ['/contatti.php', 'nav.contact'];
    $_mobileNav[] = ['/admin/login.php', 'nav.admin'];
    foreach ($_mobileNav as $n):
      $href = $n[0] . ($curLang !== 'it' && strpos($n[0], '#') === false ? '?lang=' . urlencode($curLang) : '');
    ?>
      <a href="<?= e($href) ?>" class="px-4 py-3 rounded-xl hover:bg-white/[.05] hover:text-brand-300 text-white transition"><?= e(t($n[1])) ?></a>
    <?php endforeach; ?>
  </div>
</div>
<script>
window.addEventListener('alpine:init', () => { if (window.lucide) lucide.createIcons(); });
window.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });

// Tilt 3D helper (mouse parallax) - applicato a [data-tilt]
document.addEventListener('mousemove', (ev) => {
  document.querySelectorAll('[data-tilt]:hover').forEach(el => {
    const r = el.getBoundingClientRect();
    const x = (ev.clientX - r.left) / r.width;
    const y = (ev.clientY - r.top) / r.height;
    const rx = (0.5 - y) * 8;
    const ry = (x - 0.5) * 10;
    el.style.setProperty('--mx', (x*100)+'%');
    el.style.setProperty('--my', (y*100)+'%');
    const inner = el.querySelector('.tilt-inner');
    if (inner) inner.style.transform = `rotateX(${rx}deg) rotateY(${ry}deg) translateZ(0)`;
  });
});
document.addEventListener('mouseleave', (ev) => {
  document.querySelectorAll('[data-tilt]').forEach(el => {
    const inner = el.querySelector('.tilt-inner');
    if (inner) inner.style.transform = '';
  });
}, true);
// rotazione conic gradient (CSS variable @property fallback)
let _ang = 0;
function _tickAng(){ _ang = (_ang + 1.4) % 360; document.documentElement.style.setProperty('--ang', _ang + 'deg'); requestAnimationFrame(_tickAng); }
requestAnimationFrame(_tickAng);
</script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
