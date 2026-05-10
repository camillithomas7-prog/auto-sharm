<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/utils.php';
require_once __DIR__ . '/../lib/pricing.php';
requireAdmin();

$cars = rows('SELECT * FROM cars WHERE active = 1 ORDER BY name');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck($_POST['csrf'] ?? null);
    $car = row('SELECT * FROM cars WHERE id = ?', [$_POST['car_id']]);
    if (!$car) { flash('Auto non valida', 'error'); redirect('/admin/prenotazione-nuova.php'); }
    $q = quoteRental($car, $_POST['pickup_date'], $_POST['dropoff_date'], $_POST['coupon'] ?? null);

    $cust = row('SELECT * FROM customers WHERE phone = ? OR email = ?', [$_POST['phone'], $_POST['email'] ?: '__']);
    if (!$cust) {
        $cid = newId();
        q('INSERT INTO customers (id, name, email, phone) VALUES (?,?,?,?)', [$cid, $_POST['name'], $_POST['email'] ?: null, $_POST['phone']]);
    } else {
        $cid = $cust['id'];
    }
    $bid = newId();
    $seq = (int)val('SELECT COUNT(*) FROM bookings') + 1;
    $code = bookingCode($seq);
    q('INSERT INTO bookings (id, code, car_id, customer_id, pickup_date, dropoff_date, pickup_location, days, status, source, subtotal, discount, total, currency, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
        [$bid, $code, $car['id'], $cid, $_POST['pickup_date'], $_POST['dropoff_date'], $_POST['pickup_location'] ?? '', $q['days'], 'confirmed', 'admin', $q['subtotal'], $q['discount'], $q['total'], cfg('site.currency') ?: 'EUR', $_POST['notes'] ?? null]);
    flash('Prenotazione creata');
    redirect('/admin/prenotazione.php?id=' . $bid);
}

$title = 'Nuova prenotazione';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/admin-shell-top.php';
?>
<div class="space-y-5 max-w-2xl">
  <h1 class="font-display text-2xl sm:text-3xl font-bold">Nuova prenotazione</h1>
  <form method="post" class="card p-5 space-y-3">
    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
    <label class="block"><span class="label">Auto</span><select class="input" name="car_id" required>
      <option value="">Scegli auto…</option>
      <?php foreach ($cars as $c): ?><option value="<?= e($c['id']) ?>"><?= e($c['name']) ?> · <?= fmtMoney((float)$c['daily_price']) ?>/g</option><?php endforeach; ?>
    </select></label>
    <div class="grid grid-cols-2 gap-3">
      <label class="block"><span class="label">Ritiro</span><input class="input" type="date" name="pickup_date" required></label>
      <label class="block"><span class="label">Riconsegna</span><input class="input" type="date" name="dropoff_date" required></label>
    </div>
    <label class="block"><span class="label">Luogo ritiro</span><input class="input" name="pickup_location" placeholder="Hotel, aeroporto..."></label>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-ink-100">
      <label class="block sm:col-span-3"><span class="label">Cliente</span><input class="input" name="name" required placeholder="Nome e cognome"></label>
      <label class="block"><span class="label">Email</span><input class="input" type="email" name="email"></label>
      <label class="block sm:col-span-2"><span class="label">Telefono</span><input class="input" name="phone" required></label>
    </div>
    <label class="block"><span class="label">Coupon (opz.)</span><input class="input" name="coupon" placeholder="es. SHARM10"></label>
    <label class="block"><span class="label">Note</span><textarea class="input" name="notes"></textarea></label>
    <button class="btn-primary w-full h-12"><i data-lucide="check" class="size-[16px]"></i> Crea prenotazione</button>
  </form>
</div>
<?php require __DIR__ . '/../partials/admin-shell-bottom.php';
