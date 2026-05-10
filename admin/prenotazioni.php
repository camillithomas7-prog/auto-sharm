<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/utils.php';
requireAdmin();

$status = $_GET['status'] ?? 'all';
$where = []; $params = [];
if ($status !== 'all') { $where[] = 'b.status = ?'; $params[] = $status; }
$sql = "SELECT b.*, c.name AS car_name, cu.name AS customer_name, cu.phone AS customer_phone
        FROM bookings b JOIN cars c ON c.id = b.car_id JOIN customers cu ON cu.id = b.customer_id";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY b.created_at DESC';
$bookings = rows($sql, $params);

$counts = [];
foreach (['all','pending','confirmed','checked_in','completed','cancelled'] as $s) {
    if ($s === 'all') $counts[$s] = (int)val('SELECT COUNT(*) FROM bookings');
    else $counts[$s] = (int)val('SELECT COUNT(*) FROM bookings WHERE status = ?', [$s]);
}

$title = 'Prenotazioni';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/admin-shell-top.php';
?>
<div class="space-y-5">
  <div class="flex items-end justify-between flex-wrap gap-3">
    <h1 class="font-display text-2xl sm:text-3xl font-bold">Prenotazioni</h1>
    <a href="/admin/prenotazione-nuova.php" class="btn-primary"><i data-lucide="plus" class="size-[16px]"></i> Nuova</a>
  </div>

  <div class="flex flex-wrap gap-2">
    <?php foreach ([
      'all' => 'Tutte', 'pending' => 'In attesa', 'confirmed' => 'Confermate',
      'checked_in' => 'In corso', 'completed' => 'Concluse', 'cancelled' => 'Annullate',
    ] as $k => $label):
      $active = $status === $k;
    ?>
      <a href="?status=<?= $k ?>" class="px-3.5 py-2 rounded-xl text-sm font-medium <?= $active ? 'bg-brand-500 text-white' : 'bg-white border border-ink-200 text-ink-700 hover:bg-ink-50' ?>"><?= e($label) ?> <span class="opacity-70">(<?= $counts[$k] ?>)</span></a>
    <?php endforeach; ?>
  </div>

  <div class="card p-5 overflow-x-auto">
    <?php if (!$bookings): ?>
      <div class="text-center py-10 text-sm text-ink-500">Nessuna prenotazione.</div>
    <?php else: ?>
      <table class="table-base">
        <thead><tr><th>Codice</th><th>Cliente</th><th>Auto</th><th>Periodo</th><th>Giorni</th><th>Totale</th><th>Stato</th></tr></thead>
        <tbody>
          <?php foreach ($bookings as $b): ?>
            <tr class="cursor-pointer" onclick="location.href='/admin/prenotazione.php?id=<?= e($b['id']) ?>'">
              <td class="font-mono text-xs"><?= e($b['code']) ?></td>
              <td>
                <div class="font-medium"><?= e($b['customer_name']) ?></div>
                <div class="text-[11px] text-ink-500"><?= e($b['customer_phone']) ?></div>
              </td>
              <td><?= e($b['car_name']) ?></td>
              <td class="text-xs"><?= fmtDate($b['pickup_date']) ?> → <?= fmtDate($b['dropoff_date']) ?></td>
              <td class="tabular-nums"><?= (int)$b['days'] ?></td>
              <td class="font-semibold tabular-nums"><?= fmtMoney((float)$b['total']) ?></td>
              <td><span class="badge-soft text-[10px] capitalize"><?= e($b['status']) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../partials/admin-shell-bottom.php';
