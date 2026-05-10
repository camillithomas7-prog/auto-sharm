<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/utils.php';
requireAdmin();

$car_count = (int)val('SELECT COUNT(*) FROM cars WHERE active = 1');
$bookings = rows("SELECT b.*, c.name as car_name, c.daily_price, c.manager_commission_pct, cu.name as customer_name FROM bookings b JOIN cars c ON c.id = b.car_id JOIN customers cu ON cu.id = b.customer_id WHERE b.status != 'cancelled' ORDER BY b.created_at DESC");
$expenses = rows('SELECT * FROM expenses');

$revenue = array_sum(array_column($bookings, 'total'));
$paid = array_sum(array_column($bookings, 'paid'));
$due = $revenue - $paid;
$exp_total = array_sum(array_column($expenses, 'amount'));

$manager_commission = 0;
foreach ($bookings as $b) {
    $pct = (float)($b['manager_commission_pct'] ?? 0);
    $manager_commission += (float)$b['total'] * $pct / 100;
}

$pending_count = (int)val("SELECT COUNT(*) FROM bookings WHERE status = 'pending'");
$upcoming = rows("SELECT b.*, c.name as car_name, cu.name as customer_name FROM bookings b JOIN cars c ON c.id = b.car_id JOIN customers cu ON cu.id = b.customer_id WHERE b.status IN ('confirmed','pending') AND b.pickup_date >= " . (dbDriver() === 'sqlite' ? "date('now')" : 'CURRENT_DATE') . " ORDER BY b.pickup_date ASC LIMIT 5");
$recent = array_slice($bookings, 0, 5);

// Transfer KPIs (se la sezione è attiva)
$transfer_revenue = 0; $transfer_commission = 0; $transfer_count = 0; $transfer_upcoming = [];
if (featureEnabled('transfer')) {
    $tBookings = rows("SELECT tb.*, t.manager_commission_pct, t.from_location, t.to_location, cu.name AS customer_name FROM transfer_bookings tb JOIN transfers t ON t.id = tb.transfer_id JOIN customers cu ON cu.id = tb.customer_id WHERE tb.status != 'cancelled'");
    $transfer_count = count($tBookings);
    foreach ($tBookings as $b) {
        $transfer_revenue += (float)$b['total'];
        $transfer_commission += (float)$b['total'] * (float)($b['manager_commission_pct'] ?? 0) / 100;
    }
    $transfer_upcoming = rows("SELECT tb.*, t.from_location, t.to_location, cu.name AS customer_name FROM transfer_bookings tb JOIN transfers t ON t.id = tb.transfer_id JOIN customers cu ON cu.id = tb.customer_id WHERE tb.status IN ('confirmed','pending') AND tb.arrival_date >= " . (dbDriver() === 'sqlite' ? "date('now')" : 'CURRENT_DATE') . " ORDER BY tb.arrival_date ASC, tb.arrival_time ASC LIMIT 5");
}

$by_car = [];
foreach ($bookings as $b) {
    $by_car[$b['car_id']] = $by_car[$b['car_id']] ?? ['name' => $b['car_name'], 'total' => 0, 'commission' => 0, 'pct' => (float)($b['manager_commission_pct'] ?? 0)];
    $by_car[$b['car_id']]['total'] += (float)$b['total'];
    $by_car[$b['car_id']]['commission'] += (float)$b['total'] * (float)($b['manager_commission_pct'] ?? 0) / 100;
}
usort($by_car, fn($a, $b) => $b['total'] <=> $a['total']);
$top = array_slice($by_car, 0, 5);

$title = 'Dashboard';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/admin-shell-top.php';
?>
<div class="space-y-6">
  <div class="flex items-end justify-between flex-wrap gap-3">
    <div>
      <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-semibold tracking-tight">Buongiorno 👋</h1>
      <p class="text-ink-500 mt-1 text-sm">Auto Sharm · stato della flotta</p>
    </div>
    <div class="flex gap-2">
      <a href="/admin/auto-edit.php" class="btn-outline text-sm"><i data-lucide="plus" class="size-[16px]"></i> Nuova auto</a>
    </div>
  </div>

  <!-- KPI -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <?php
      $kpis = [
        ['Fatturato', fmtMoney($revenue), count($bookings) . ' prenotazioni', 'trending-up', 'from-emerald-400 to-emerald-600'],
        ['Tua quota gestione', fmtMoney($manager_commission), 'commissioni', 'percent', 'from-brand-400 to-brand-600'],
        ['Saldo da incassare', fmtMoney($due), $due > 0 ? 'da sollecitare' : 'tutto incassato', 'clock', 'from-amber-400 to-amber-600'],
        ['Spese totali', fmtMoney($exp_total), count($expenses) . ' voci', 'wallet', 'from-rose-400 to-rose-600'],
      ];
      foreach ($kpis as $i => $k):
    ?>
      <div class="card p-5 relative overflow-hidden">
        <div class="h-11 w-11 rounded-2xl bg-gradient-to-br <?= $k[4] ?> text-white flex items-center justify-center shadow-md mb-3"><i data-lucide="<?= $k[3] ?>" class="size-[20px]"></i></div>
        <div class="text-[11px] font-semibold uppercase tracking-wider text-ink-500"><?= e($k[0]) ?></div>
        <div class="text-2xl sm:text-[28px] font-display font-bold tracking-tight mt-0.5 tabular-nums"><?= e($k[1]) ?></div>
        <div class="text-xs text-ink-500 mt-1"><?= e($k[2]) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <?php
    $miniKpis = [
      ['Auto attive', $car_count, 'car', 'text-sky-500 bg-sky-500/10'],
      ['Prossimi ritiri', count($upcoming), 'log-in', 'text-violet-500 bg-violet-500/10'],
      ['Prenotazioni totali', count($bookings), 'bookmark-check', 'text-indigo-500 bg-indigo-500/10'],
      ['In attesa', $pending_count, 'alert-triangle', 'text-amber-500 bg-amber-500/10'],
    ];
    if (featureEnabled('transfer')) {
      $miniKpis[] = ['Transfer prenotati', $transfer_count, 'plane-landing', 'text-cyan-500 bg-cyan-500/10'];
      $miniKpis[] = ['Ricavi transfer', fmtMoney($transfer_revenue), 'route', 'text-teal-500 bg-teal-500/10'];
    }
    foreach ($miniKpis as $s): ?>
      <div class="card p-4 flex items-center gap-3">
        <div class="h-10 w-10 rounded-xl flex items-center justify-center <?= $s[3] ?>"><i data-lucide="<?= $s[2] ?>" class="size-[18px]"></i></div>
        <div>
          <div class="font-display font-bold text-2xl"><?= is_numeric($s[1]) ? (int)$s[1] : e($s[1]) ?></div>
          <div class="text-xs text-ink-500"><?= e($s[0]) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (featureEnabled('transfer') && $transfer_upcoming): ?>
    <div class="card p-5">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-display font-bold flex items-center gap-2"><i data-lucide="plane-landing" class="size-[18px] text-cyan-500"></i> Prossimi transfer</h2>
        <a href="/admin/transfer-bookings.php" class="text-sm text-brand-600 hover:underline">Tutte →</a>
      </div>
      <ul class="space-y-2.5">
        <?php foreach ($transfer_upcoming as $b): ?>
          <li>
            <a href="/admin/transfer-booking.php?id=<?= e($b['id']) ?>" class="flex items-center gap-3 p-3 rounded-xl hover:bg-ink-50 dark:hover:bg-ink-900/40 transition">
              <span class="h-11 w-11 rounded-xl bg-cyan-100 text-cyan-600 flex items-center justify-center"><i data-lucide="plane-landing" class="size-[18px]"></i></span>
              <div class="flex-1 min-w-0">
                <div class="font-medium text-sm truncate"><?= e($b['customer_name']) ?> · <?= e($b['from_location']) ?> → <?= e($b['to_location']) ?></div>
                <div class="text-xs text-ink-500"><?= fmtDate($b['arrival_date']) ?> <?= e($b['arrival_time'] ?: '') ?> · <?= (int)$b['passengers'] ?> pax · volo <?= e($b['flight_number'] ?: '—') ?></div>
              </div>
              <span class="font-display font-bold text-sm tabular-nums"><?= fmtMoney((float)$b['total']) ?></span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="card p-5 lg:col-span-2">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-display font-bold">Prossimi ritiri</h2>
        <a href="/admin/calendario.php" class="text-sm text-brand-600 hover:underline">Calendario →</a>
      </div>
      <?php if (!$upcoming): ?>
        <div class="text-center py-8 text-sm text-ink-500">Nessun ritiro programmato.</div>
      <?php else: ?>
        <ul class="space-y-2.5">
          <?php foreach ($upcoming as $b): ?>
            <li>
              <a href="/admin/prenotazione.php?id=<?= e($b['id']) ?>" class="flex items-center gap-3 p-3 rounded-xl hover:bg-ink-50 dark:hover:bg-ink-900/40 transition group">
                <span class="h-11 w-11 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><i data-lucide="car" class="size-[18px]"></i></span>
                <div class="flex-1 min-w-0">
                  <div class="font-medium text-sm truncate"><?= e($b['customer_name']) ?> · <?= e($b['car_name']) ?></div>
                  <div class="text-xs text-ink-500"><?= fmtDate($b['pickup_date']) ?> → <?= fmtDate($b['dropoff_date']) ?> · <?= (int)$b['days'] ?> giorni</div>
                </div>
                <span class="font-display font-bold text-sm tabular-nums"><?= fmtMoney((float)$b['total']) ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="card p-5">
      <h2 class="font-display font-bold mb-3">Top auto</h2>
      <?php if (!$top): ?>
        <div class="text-sm text-ink-500">Nessun dato.</div>
      <?php else: ?>
        <ul class="space-y-2.5 text-sm">
          <?php foreach ($top as $i => $t): ?>
            <li>
              <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 min-w-0"><span class="h-2.5 w-2.5 rounded-full bg-brand-500 shrink-0"></span><span class="truncate"><?= e($t['name']) ?></span></span>
                <span class="font-semibold tabular-nums"><?= fmtMoney((float)$t['total']) ?></span>
              </div>
              <div class="flex items-center justify-between text-[11px] text-ink-500 ml-5 mt-0.5">
                <span>tua quota <?= number_format((float)$t['pct'], 0) ?>%</span>
                <span class="font-medium tabular-nums text-brand-600"><?= fmtMoney((float)$t['commission']) ?></span>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>

  <div class="card p-5">
    <h2 class="font-display font-bold mb-4">Prenotazioni recenti</h2>
    <?php if (!$recent): ?>
      <div class="text-center py-6 text-sm text-ink-500">Ancora nessuna prenotazione.</div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="table-base">
          <thead><tr><th>Codice</th><th>Cliente</th><th>Auto</th><th>Periodo</th><th>Totale</th><th>Stato</th></tr></thead>
          <tbody>
            <?php foreach ($recent as $b): ?>
              <tr class="cursor-pointer" onclick="location.href='/admin/prenotazione.php?id=<?= e($b['id']) ?>'">
                <td class="font-mono text-xs"><?= e($b['code']) ?></td>
                <td class="font-medium"><?= e($b['customer_name']) ?></td>
                <td><?= e($b['car_name']) ?></td>
                <td class="text-xs"><?= fmtDate($b['pickup_date']) ?> → <?= fmtDate($b['dropoff_date']) ?></td>
                <td class="font-semibold tabular-nums"><?= fmtMoney((float)$b['total']) ?></td>
                <td><span class="badge-soft text-[10px] capitalize"><?= e($b['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../partials/admin-shell-bottom.php';
