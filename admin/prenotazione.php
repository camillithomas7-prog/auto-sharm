<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/utils.php';
requireAdmin();

$id = $_GET['id'] ?? '';
$b = row('SELECT b.*, c.name AS car_name, c.daily_price, cu.name AS customer_name, cu.email AS customer_email, cu.phone AS customer_phone, cu.notes AS customer_notes FROM bookings b JOIN cars c ON c.id = b.car_id JOIN customers cu ON cu.id = b.customer_id WHERE b.id = ?', [$id]);
if (!$b) { flash('Prenotazione non trovata', 'error'); redirect('/admin/prenotazioni.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck($_POST['csrf'] ?? null);
    $action = $_POST['action'] ?? '';
    if ($action === 'status') {
        q('UPDATE bookings SET status = ? WHERE id = ?', [$_POST['status'], $b['id']]);
        flash('Stato aggiornato');
    }
    if ($action === 'paid') {
        q('UPDATE bookings SET paid = ? WHERE id = ?', [(float)$_POST['paid'], $b['id']]);
        flash('Importo pagato aggiornato');
    }
    if ($action === 'delete') {
        q('DELETE FROM bookings WHERE id = ?', [$b['id']]);
        flash('Prenotazione eliminata');
        redirect('/admin/prenotazioni.php');
    }
    redirect('/admin/prenotazione.php?id=' . $b['id']);
}

$waN = preg_replace('/\D/', '', $b['customer_phone']);
$waLink = $waN ? 'https://wa.me/' . $waN : null;

$title = 'Prenotazione ' . $b['code'];
$_subtitle = 'Prenotazioni';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/admin-shell-top.php';
?>
<div class="space-y-5">
  <div class="flex items-end justify-between flex-wrap gap-3">
    <div>
      <a href="/admin/prenotazioni.php" class="text-sm text-ink-500 hover:text-brand-600 inline-flex items-center gap-1"><i data-lucide="chevron-left" class="size-[14px]"></i> Tutte le prenotazioni</a>
      <h1 class="font-display text-2xl sm:text-3xl font-bold mt-2"><?= e($b['code']) ?></h1>
      <div class="text-sm text-ink-500"><?= e($b['car_name']) ?> · <?= fmtDate($b['pickup_date']) ?> → <?= fmtDate($b['dropoff_date']) ?></div>
    </div>
    <span class="badge-soft capitalize text-sm"><?= e($b['status']) ?></span>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
      <div class="card p-5">
        <h3 class="font-display font-bold mb-3">Cliente</h3>
        <div class="flex items-start gap-3">
          <span class="h-12 w-12 rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 text-white flex items-center justify-center font-bold"><?= e(strtoupper(mb_substr($b['customer_name'], 0, 2))) ?></span>
          <div class="flex-1 min-w-0">
            <div class="font-display font-bold"><?= e($b['customer_name']) ?></div>
            <div class="text-sm text-ink-500"><?= e($b['customer_email']) ?> · <?= e($b['customer_phone']) ?></div>
          </div>
          <?php if ($waLink): ?>
            <a href="<?= e($waLink) ?>" target="_blank" class="btn-outline text-sm"><i data-lucide="message-circle" class="size-[14px]"></i> WhatsApp</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="card p-5">
        <h3 class="font-display font-bold mb-3">Dettagli noleggio</h3>
        <dl class="grid grid-cols-2 gap-y-2 text-sm">
          <dt class="text-ink-500">Auto</dt><dd class="font-medium"><?= e($b['car_name']) ?></dd>
          <dt class="text-ink-500">Ritiro</dt><dd class="font-medium"><?= fmtDate($b['pickup_date']) ?></dd>
          <dt class="text-ink-500">Riconsegna</dt><dd class="font-medium"><?= fmtDate($b['dropoff_date']) ?></dd>
          <dt class="text-ink-500">Giorni</dt><dd class="font-medium tabular-nums"><?= (int)$b['days'] ?></dd>
          <dt class="text-ink-500">Luogo ritiro</dt><dd class="font-medium"><?= e($b['pickup_location'] ?: '—') ?></dd>
          <?php if ($b['notes']): ?><dt class="text-ink-500">Note</dt><dd><?= e($b['notes']) ?></dd><?php endif; ?>
          <?php if ($b['coupon_code']): ?><dt class="text-ink-500">Coupon</dt><dd class="font-mono"><?= e($b['coupon_code']) ?></dd><?php endif; ?>
        </dl>
      </div>

      <div class="card p-5">
        <h3 class="font-display font-bold mb-3">Pagamento</h3>
        <div class="grid grid-cols-3 gap-3 text-sm mb-4">
          <div><div class="text-ink-500 text-xs">Subtotale</div><div class="font-display font-bold"><?= fmtMoney((float)$b['subtotal']) ?></div></div>
          <?php if ((float)$b['discount'] > 0): ?>
            <div><div class="text-ink-500 text-xs">Sconto</div><div class="font-display font-bold text-emerald-600">- <?= fmtMoney((float)$b['discount']) ?></div></div>
          <?php endif; ?>
          <div><div class="text-ink-500 text-xs">Totale</div><div class="font-display font-bold text-brand-600"><?= fmtMoney((float)$b['total']) ?></div></div>
        </div>
        <form method="post" class="flex gap-2 items-end">
          <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
          <input type="hidden" name="action" value="paid">
          <label class="block flex-1"><span class="label">Importo pagato (€)</span><input class="input" type="number" step="0.01" name="paid" value="<?= e((string)$b['paid']) ?>"></label>
          <button class="btn-primary"><i data-lucide="save" class="size-[16px]"></i></button>
        </form>
        <div class="text-xs text-ink-500 mt-2">Saldo da incassare: <strong><?= fmtMoney((float)$b['total'] - (float)$b['paid']) ?></strong></div>
      </div>
    </div>

    <div class="space-y-5">
      <form method="post" class="card p-5 space-y-3">
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="status">
        <h3 class="font-display font-bold">Cambia stato</h3>
        <select class="input" name="status">
          <?php foreach (['pending'=>'In attesa','confirmed'=>'Confermata','checked_in'=>'In corso','completed'=>'Conclusa','cancelled'=>'Annullata'] as $k => $v): ?>
            <option value="<?= $k ?>" <?= $b['status']===$k?'selected':'' ?>><?= e($v) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn-primary w-full">Aggiorna</button>
      </form>

      <form method="post" class="card p-5 border-red-100">
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="delete">
        <h3 class="font-display font-bold text-red-600">Elimina</h3>
        <p class="text-xs text-ink-500 mt-1 mb-3">L'azione è irreversibile.</p>
        <button class="btn-danger w-full" onclick="return confirm('Eliminare la prenotazione?')"><i data-lucide="trash-2" class="size-[14px]"></i> Elimina</button>
      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../partials/admin-shell-bottom.php';
