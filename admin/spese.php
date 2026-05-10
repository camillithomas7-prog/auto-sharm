<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/utils.php';
requireAdmin();

$cars = rows('SELECT id, name, manager_commission_pct FROM cars ORDER BY name');
$year = (int)($_GET['year'] ?? date('Y'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck($_POST['csrf'] ?? null);
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        q('INSERT INTO expenses (id, car_id, category, amount, date, description) VALUES (?,?,?,?,?,?)',
            [newId(), $_POST['car_id'] ?: null, $_POST['category'], (float)$_POST['amount'], $_POST['date'], $_POST['description'] ?: null]);
        flash('Spesa registrata');
    }
    if ($action === 'delete') {
        q('DELETE FROM expenses WHERE id = ?', [$_POST['expense_id']]);
    }
    redirect('/admin/spese.php?year=' . $year);
}

$expenses = rows('SELECT e.*, c.name AS car_name FROM expenses e LEFT JOIN cars c ON c.id = e.car_id WHERE date >= ? AND date < ? ORDER BY date DESC',
    ["$year-01-01", ($year+1)."-01-01"]);
$bookings = rows('SELECT b.*, c.manager_commission_pct, c.name AS car_name FROM bookings b JOIN cars c ON c.id = b.car_id WHERE b.status != "cancelled" AND b.pickup_date >= ? AND b.pickup_date < ?',
    ["$year-01-01", ($year+1)."-01-01"]);

$totalExp = array_sum(array_column($expenses, 'amount'));
$totalRev = array_sum(array_column($bookings, 'total'));
$totalCommission = 0; $byCarComm = [];
foreach ($bookings as $b) {
    $pct = (float)($b['manager_commission_pct'] ?? 0);
    $c = (float)$b['total'] * $pct / 100;
    $totalCommission += $c;
    $key = $b['car_id'];
    $byCarComm[$key] = $byCarComm[$key] ?? ['name' => $b['car_name'], 'pct' => $pct, 'rev' => 0, 'commission' => 0];
    $byCarComm[$key]['rev'] += (float)$b['total'];
    $byCarComm[$key]['commission'] += $c;
}
uasort($byCarComm, fn($a,$b) => $b['commission'] <=> $a['commission']);

// Transfer: ricavi e commissioni
$transferRev = 0; $transferComm = 0; $byTransferComm = [];
if (featureEnabled('transfer')) {
    $tBookings = rows('SELECT tb.*, t.manager_commission_pct, t.from_location, t.to_location FROM transfer_bookings tb JOIN transfers t ON t.id = tb.transfer_id WHERE tb.status != "cancelled" AND tb.arrival_date >= ? AND tb.arrival_date < ?',
        ["$year-01-01", ($year+1)."-01-01"]);
    foreach ($tBookings as $b) {
        $pct = (float)($b['manager_commission_pct'] ?? 0);
        $c = (float)$b['total'] * $pct / 100;
        $transferRev += (float)$b['total'];
        $transferComm += $c;
        $key = $b['transfer_id'];
        $byTransferComm[$key] = $byTransferComm[$key] ?? ['name' => $b['from_location'] . ' → ' . $b['to_location'], 'pct' => $pct, 'rev' => 0, 'commission' => 0];
        $byTransferComm[$key]['rev'] += (float)$b['total'];
        $byTransferComm[$key]['commission'] += $c;
    }
    uasort($byTransferComm, fn($a,$b) => $b['commission'] <=> $a['commission']);
}

$totalRevAll = $totalRev + $transferRev;
$totalCommissionAll = $totalCommission + $transferComm;
$ownerPayout = max(0, $totalRevAll - $totalExp - $totalCommissionAll);
$cats = ['carburante','manutenzione','assicurazione','tasse','pulizia','noleggio_garage','commissioni','straordinarie'];

$title = 'Spese & bilancio';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/admin-shell-top.php';
?>
<div class="space-y-5">
  <div class="flex items-end justify-between flex-wrap gap-3">
    <h1 class="font-display text-2xl sm:text-3xl font-bold">Spese & bilancio</h1>
    <form method="get"><select class="input" name="year" onchange="this.form.submit()">
      <?php for ($y=date('Y')-2; $y<=date('Y')+1; $y++): ?><option value="<?= $y ?>" <?= $y===$year?'selected':'' ?>><?= $y ?></option><?php endfor; ?>
    </select></form>
  </div>

  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
    <?php foreach ([
      ['Fatturato', fmtMoney($totalRevAll), 'bg-emerald-500'],
      ['Spese', fmtMoney($totalExp), 'bg-rose-500'],
      ['Tua quota', fmtMoney($totalCommissionAll), 'bg-brand-500'],
      ['Quota proprietari', fmtMoney($ownerPayout), 'bg-violet-500'],
      ['Bilancio', fmtMoney($totalRevAll - $totalExp), 'bg-sky-500'],
    ] as $s): ?>
      <div class="card p-4">
        <div class="text-xs text-ink-500 uppercase tracking-wide"><?= e($s[0]) ?></div>
        <div class="text-xl font-display font-bold mt-1 tabular-nums"><?= e($s[1]) ?></div>
        <div class="h-1 rounded-full mt-2 <?= $s[2] ?>"></div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($byCarComm): ?>
    <div class="card p-5">
      <div class="flex items-start gap-3 mb-4">
        <span class="h-9 w-9 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><i data-lucide="percent" class="size-[18px]"></i></span>
        <div><h3 class="font-display font-bold">Commissioni per auto · <?= $year ?></h3>
        <p class="text-xs text-ink-500">La tua quota su ogni veicolo gestito.</p></div>
      </div>
      <div class="overflow-x-auto"><table class="table-base">
        <thead><tr><th>Auto</th><th class="text-right">Fatturato</th><th class="text-center">%</th><th class="text-right">Tua quota</th></tr></thead>
        <tbody>
          <?php foreach ($byCarComm as $r): ?>
            <tr><td class="font-medium"><?= e($r['name']) ?></td>
                <td class="text-right tabular-nums"><?= fmtMoney((float)$r['rev']) ?></td>
                <td class="text-center"><span class="badge-soft tabular-nums"><?= number_format((float)$r['pct'], 0) ?>%</span></td>
                <td class="text-right tabular-nums font-semibold text-brand-600"><?= fmtMoney((float)$r['commission']) ?></td></tr>
          <?php endforeach; ?>
        </tbody></table></div>
    </div>
  <?php endif; ?>

  <?php if ($byTransferComm): ?>
    <div class="card p-5">
      <div class="flex items-start gap-3 mb-4">
        <span class="h-9 w-9 rounded-xl bg-cyan-100 text-cyan-600 flex items-center justify-center"><i data-lucide="plane-landing" class="size-[18px]"></i></span>
        <div><h3 class="font-display font-bold">Commissioni per tratta transfer · <?= $year ?></h3>
        <p class="text-xs text-ink-500">La tua quota su ogni tratta aeroporto-hotel.</p></div>
      </div>
      <div class="overflow-x-auto"><table class="table-base">
        <thead><tr><th>Tratta</th><th class="text-right">Fatturato</th><th class="text-center">%</th><th class="text-right">Tua quota</th></tr></thead>
        <tbody>
          <?php foreach ($byTransferComm as $r): ?>
            <tr><td class="font-medium"><?= e($r['name']) ?></td>
                <td class="text-right tabular-nums"><?= fmtMoney((float)$r['rev']) ?></td>
                <td class="text-center"><span class="badge-soft tabular-nums"><?= number_format((float)$r['pct'], 0) ?>%</span></td>
                <td class="text-right tabular-nums font-semibold text-brand-600"><?= fmtMoney((float)$r['commission']) ?></td></tr>
          <?php endforeach; ?>
        </tbody></table></div>
    </div>
  <?php endif; ?>

  <form method="post" class="card p-5 grid grid-cols-2 lg:grid-cols-6 gap-2">
    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="add">
    <select class="input" name="car_id"><option value="">Generica</option>
      <?php foreach ($cars as $c): ?><option value="<?= e($c['id']) ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
    </select>
    <select class="input" name="category"><?php foreach ($cats as $c): ?><option value="<?= $c ?>"><?= e($c) ?></option><?php endforeach; ?></select>
    <input class="input" type="date" name="date" value="<?= date('Y-m-d') ?>" required>
    <input class="input" type="number" step="0.01" name="amount" placeholder="€" required>
    <input class="input col-span-2 lg:col-span-1" name="description" placeholder="Note">
    <button class="btn-primary col-span-2 lg:col-span-1 h-12"><i data-lucide="plus" class="size-[16px]"></i> Aggiungi</button>
  </form>

  <div class="card p-5 overflow-x-auto">
    <h3 class="font-display font-bold mb-3">Voci spesa <?= $year ?></h3>
    <?php if (!$expenses): ?>
      <div class="text-sm text-ink-500 text-center py-6">Nessuna spesa.</div>
    <?php else: ?>
      <table class="table-base">
        <thead><tr><th>Data</th><th>Categoria</th><th>Auto</th><th>Importo</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($expenses as $e): ?>
            <tr>
              <td><?= fmtDate($e['date']) ?></td>
              <td class="capitalize"><?= e($e['category']) ?></td>
              <td><?= e($e['car_name'] ?: '—') ?></td>
              <td class="tabular-nums"><?= fmtMoney((float)$e['amount']) ?></td>
              <td><form method="post" onsubmit="return confirm('Eliminare?')">
                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="expense_id" value="<?= e($e['id']) ?>">
                <button class="btn-ghost text-red-600"><i data-lucide="trash-2" class="size-[14px]"></i></button>
              </form></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../partials/admin-shell-bottom.php';
