<?php
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/utils.php';

$cPhone = setting('contact_phone', cfg('site.phone'));
$cEmail = setting('contact_email', cfg('site.email'));
$waN    = whatsappNumber();
$cWa    = $waN ? '+' . $waN : '';
$cAddr  = setting('contact_address', cfg('site.address'));

$title = t('nav.contact');
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/site-header.php';
?>
<section class="container-narrow pt-12 pb-20">
  <div class="badge-brand"><i data-lucide="message-circle" class="size-[12px]"></i> <?= e(t('nav.contact')) ?></div>
  <h1 class="font-serif text-5xl md:text-6xl font-semibold tracking-tight mt-3"><?= e(t('contact.title')) ?></h1>
  <p class="text-ink-500 mt-3 max-w-xl"><?= e(t('contact.sub')) ?></p>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-8">
    <?php if ($waN): ?>
      <a href="<?= e(whatsappLink()) ?>" target="_blank" rel="noopener" class="card p-5 card-hover bg-emerald-500 text-white border-emerald-600 group">
        <i data-lucide="message-circle" class="size-[24px] mb-3"></i>
        <div class="font-display font-bold">WhatsApp</div>
        <div class="text-xs opacity-90 mt-1"><?= e($cWa) ?></div>
      </a>
    <?php endif; ?>
    <?php if ($cPhone): ?>
      <a href="tel:<?= e(preg_replace('/[^0-9+]/','',$cPhone)) ?>" class="card p-5 card-hover">
        <div class="h-10 w-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center mb-3"><i data-lucide="phone" class="size-[18px]"></i></div>
        <div class="font-display font-bold"><?= e(t('book.phone')) ?></div>
        <div class="text-xs text-ink-500 mt-1"><?= e($cPhone) ?></div>
      </a>
    <?php endif; ?>
    <?php if ($cEmail): ?>
      <a href="mailto:<?= e($cEmail) ?>" class="card p-5 card-hover">
        <div class="h-10 w-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center mb-3"><i data-lucide="mail" class="size-[18px]"></i></div>
        <div class="font-display font-bold">Email</div>
        <div class="text-xs text-ink-500 mt-1"><?= e($cEmail) ?></div>
      </a>
    <?php endif; ?>
  </div>

  <?php if ($cAddr): ?>
    <div class="card p-6 mt-6 flex items-start gap-3">
      <span class="h-10 w-10 rounded-xl bg-ink-100 dark:bg-ink-800 text-ink-600 flex items-center justify-center shrink-0"><i data-lucide="map-pin" class="size-[18px]"></i></span>
      <div>
        <div class="font-display font-bold">Sharm El Sheikh</div>
        <div class="text-sm text-ink-500 mt-0.5"><?= e($cAddr) ?></div>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/partials/site-footer.php';
