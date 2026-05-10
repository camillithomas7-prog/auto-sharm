<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/utils.php';
$user = requireAdmin();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck($_POST['csrf'] ?? null);
    $a = $_POST['action'] ?? '';
    if ($a === 'save') {
        foreach (['site_name','contact_email','contact_phone','contact_whatsapp','contact_address','social_facebook','social_instagram'] as $k) {
            $v = trim($_POST[$k] ?? '');
            $exists = (int)val('SELECT COUNT(*) FROM settings WHERE setting_key = ?', [$k]);
            if ($exists) q('UPDATE settings SET setting_value = ? WHERE setting_key = ?', [$v, $k]);
            else q('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)', [$k, $v]);
        }
        $msg = 'Impostazioni salvate';
    }
    if ($a === 'features') {
        $v = isset($_POST['feature_transfer']) ? '1' : '0';
        $exists = (int)val('SELECT COUNT(*) FROM settings WHERE setting_key = ?', ['feature_transfer']);
        if ($exists) q('UPDATE settings SET setting_value = ? WHERE setting_key = ?', [$v, 'feature_transfer']);
        else q('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)', ['feature_transfer', $v]);
        $msg = 'Sezioni del sito aggiornate';
    }
    if ($a === 'password') {
        $u = row('SELECT * FROM users WHERE id = ?', [$user['id']]);
        if (!password_verify($_POST['old'] ?? '', $u['password'])) $msg = 'Password attuale errata';
        elseif (strlen($_POST['next'] ?? '') < 6) $msg = 'Password troppo corta';
        else { q('UPDATE users SET password = ? WHERE id = ?', [password_hash($_POST['next'], PASSWORD_DEFAULT), $u['id']]); $msg = 'Password aggiornata'; }
    }
}

$s = [];
foreach (rows('SELECT * FROM settings') as $row) $s[$row['setting_key']] = $row['setting_value'];

$title = 'Impostazioni';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/admin-shell-top.php';
?>
<div class="space-y-5">
  <h1 class="font-display text-2xl sm:text-3xl font-bold">Impostazioni</h1>
  <?php if ($msg): ?><div class="card p-3 bg-emerald-50 text-emerald-700 border-emerald-200 text-sm"><?= e($msg) ?></div><?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <form method="post" class="card p-5 space-y-3">
      <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="save">
      <h3 class="font-display font-bold flex items-center gap-2"><i data-lucide="globe" class="size-[18px]"></i> Sito</h3>
      <label class="block"><span class="label">Nome sito</span><input class="input" name="site_name" value="<?= e($s['site_name'] ?? cfg('site.name')) ?>"></label>
      <div class="grid grid-cols-2 gap-3">
        <label class="block"><span class="label">Email</span><input class="input" type="email" name="contact_email" value="<?= e($s['contact_email'] ?? cfg('site.email')) ?>"></label>
        <label class="block"><span class="label">Telefono</span><input class="input" name="contact_phone" value="<?= e($s['contact_phone'] ?? cfg('site.phone')) ?>"></label>
        <label class="block"><span class="label">WhatsApp</span><input class="input" name="contact_whatsapp" value="<?= e($s['contact_whatsapp'] ?? cfg('site.whatsapp')) ?>"></label>
        <label class="block"><span class="label">Indirizzo</span><input class="input" name="contact_address" value="<?= e($s['contact_address'] ?? cfg('site.address')) ?>"></label>
      </div>
      <div class="pt-3 border-t border-ink-100 grid grid-cols-2 gap-3">
        <label class="block"><span class="label">Facebook</span><input class="input" name="social_facebook" value="<?= e($s['social_facebook'] ?? '') ?>"></label>
        <label class="block"><span class="label">Instagram</span><input class="input" name="social_instagram" value="<?= e($s['social_instagram'] ?? '') ?>"></label>
      </div>
      <button class="btn-primary"><i data-lucide="save" class="size-[16px]"></i> Salva</button>
    </form>

    <form method="post" class="card p-5 space-y-3">
      <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="features">
      <h3 class="font-display font-bold flex items-center gap-2"><i data-lucide="toggle-right" class="size-[18px]"></i> Sezioni del sito</h3>
      <p class="text-xs text-ink-500">Attiva o disattiva moduli aggiuntivi nel sito pubblico.</p>
      <label class="flex items-start gap-3 p-3 rounded-xl bg-ink-50/60 border border-ink-100 cursor-pointer">
        <input type="checkbox" name="feature_transfer" class="mt-1" <?= featureEnabled('transfer') ? 'checked' : '' ?>>
        <div>
          <div class="font-medium text-sm">Transfer aeroporto</div>
          <div class="text-xs text-ink-500 mt-0.5">Mostra la sezione "Transfer" e abilita la prenotazione delle tratte aeroporto.</div>
        </div>
      </label>
      <button class="btn-primary"><i data-lucide="save" class="size-[16px]"></i> Salva sezioni</button>
    </form>

    <form method="post" class="card p-5 space-y-3">
      <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="password">
      <h3 class="font-display font-bold">Cambia password</h3>
      <div class="text-sm text-ink-500">Connesso come <strong><?= e($user['email']) ?></strong></div>
      <label class="block"><span class="label">Password attuale</span><input class="input" type="password" name="old"></label>
      <label class="block"><span class="label">Nuova password</span><input class="input" type="password" name="next"></label>
      <button class="btn-secondary">Aggiorna password</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/../partials/admin-shell-bottom.php';
