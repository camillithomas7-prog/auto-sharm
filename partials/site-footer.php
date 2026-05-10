<?php
$cPhone = setting('contact_phone', cfg('site.phone'));
$cEmail = setting('contact_email', cfg('site.email'));
$waN    = whatsappNumber();
$cAddr  = setting('contact_address', cfg('site.address'));
$year = date('Y');
?>
<footer class="relative mt-24 border-t border-white/[.06] bg-gradient-to-b from-[#06030a] to-[#03010a]">
  <div class="neon-divider absolute top-0 inset-x-0"></div>
  <div class="container-wide py-16 relative">
    <span aria-hidden="true" class="absolute -left-32 top-10 h-72 w-72 rounded-full bg-brand-500/15 blur-3xl"></span>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-10 relative">
      <div class="lg:col-span-2 max-w-md">
        <div class="inline-flex items-center gap-2.5">
          <img src="/assets/logo.png" alt="Auto Sharm" class="h-10 w-auto drop-shadow-[0_0_14px_rgba(255,30,30,.55)]">
          <span class="font-display font-extrabold text-xl text-gradient-chrome">Auto Sharm</span>
        </div>
        <p class="mt-4 text-sm text-ink-400 leading-relaxed"><?= e(t('footer.tagline')) ?></p>
        <div class="flex gap-2 mt-6">
          <?php if ($waN): ?>
            <a href="<?= e(whatsappLink()) ?>" target="_blank" rel="noopener" class="h-11 w-11 rounded-xl bg-emerald-500 text-white flex items-center justify-center hover:bg-emerald-400 transition shadow-[0_0_24px_-6px_rgba(16,185,129,.7)]" aria-label="WhatsApp"><i data-lucide="message-circle" class="size-[18px]"></i></a>
          <?php endif; ?>
          <?php if ($cPhone): ?>
            <a href="tel:<?= e(preg_replace('/[^0-9+]/','',$cPhone)) ?>" class="h-11 w-11 rounded-xl bg-white/[.04] border border-white/[.08] text-ink-200 hover:text-white hover:bg-white/[.08] flex items-center justify-center transition" aria-label="Phone"><i data-lucide="phone" class="size-[18px]"></i></a>
          <?php endif; ?>
          <?php if ($cEmail): ?>
            <a href="mailto:<?= e($cEmail) ?>" class="h-11 w-11 rounded-xl bg-white/[.04] border border-white/[.08] text-ink-200 hover:text-white hover:bg-white/[.08] flex items-center justify-center transition" aria-label="Email"><i data-lucide="mail" class="size-[18px]"></i></a>
          <?php endif; ?>
        </div>
      </div>

      <div>
        <h4 class="font-display font-bold text-white mb-3"><?= e(t('nav.fleet')) ?></h4>
        <ul class="space-y-2 text-sm text-ink-400">
          <?php foreach (['economy','compact','suv','luxury','minivan'] as $cat): ?>
            <li><a href="/flotta.php?cat=<?= e($cat) ?>" class="hover:text-brand-300 transition"><?= e(t('cat.' . $cat)) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div>
        <h4 class="font-display font-bold text-white mb-3"><?= e(t('nav.contact')) ?></h4>
        <ul class="space-y-2 text-sm text-ink-400">
          <?php if ($cPhone): ?><li class="flex items-center gap-2"><i data-lucide="phone" class="size-[14px] text-brand-400"></i> <?= e($cPhone) ?></li><?php endif; ?>
          <?php if ($cEmail): ?><li class="flex items-center gap-2"><i data-lucide="mail" class="size-[14px] text-brand-400"></i> <?= e($cEmail) ?></li><?php endif; ?>
          <?php if ($cAddr): ?><li class="flex items-start gap-2"><i data-lucide="map-pin" class="size-[14px] mt-0.5 text-brand-400"></i> <?= e($cAddr) ?></li><?php endif; ?>
        </ul>
      </div>
    </div>
    <div class="mt-12 pt-6 border-t border-white/[.06] flex flex-wrap items-center justify-between gap-3 text-xs text-ink-500">
      <div>© <?= $year ?> Auto Sharm · <?= e(t('footer.rights')) ?></div>
      <div class="font-mono tracking-wider">SHARM EL SHEIKH · EG</div>
    </div>
  </div>
</footer>
</body>
</html>
