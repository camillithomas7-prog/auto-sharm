<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/utils.php';
requireAdmin();

$status = $_GET['status'] ?? 'all';
$where = []; $params = [];
if ($status !== 'all') { $where[] = 'tb.status = ?'; $params[] = $status; }
$sql = "SELECT tb.*, t.name AS transfer_name, t.from_location, t.to_location, cu.name AS customer_name, cu.phone AS customer_phone
        FROM transfer_bookings tb JOIN transfers t ON t.id = tb.transfer_id JOIN customers cu ON cu.id = tb.customer_id";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY tb.arrival_date DESC, tb.created_at DESC';
$bookings = rows($sql, $params);

$counts = [];
foreach (['all','pending','confirmed','completed','cancelled'] as $s) {
    if ($s === 'all') $counts[$s] = (int)val('SELECT COUNT(*) FROM transfer_bookings');
    else $counts[$s] = (int)val('SELECT COUNT(*) FROM transfer_bookings WHERE status = ?', [$s]);
}

$title = 'Prenotazioni transfer';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/admin-shell-top.php';
?>
<div class="space-y-5">
  <div class="flex items-end justify-between flex-wrap gap-3">
    <h1 class="font-display text-2xl sm:text-3xl font-bold">Prenotazioni transfer</h1>
  </div>

  <div class="flex flex-wrap gap-2">
    <?php foreach ([
      'all' => 'Tutte', 'pending' => 'In attesa', 'confirmed' => 'Confermate',
      'completed' => 'Concluse', 'cancelled' => 'Annullate',
    ] as $k => $label):
      $active = $status === $k;
    ?>
      <a href="?status=<?= $k ?>" class="px-3.5 py-2 rounded-xl text-sm font-medium <?= $active ? 'bg-brand-500 text-white' : 'bg-white border border-ink-200 text-ink-700 hover:bg-ink-50' ?>"><?= e($label) ?> <span class="opacity-70">(<?= $counts[$k] ?>)</span></a>
    <?php endforeach; ?>
  </div>

  <div class="card p-5 overflow-x-auto">
    <?php if (!$bookings): ?>
      <div class="text-center py-10 text-sm text-ink-500">Nessuna prenotazione transfer.</div>
    <?php else: ?>
      <table class="table-base">
        <thead><tr><th>Codice</th><th>Cliente</th><th>Tratta</th><th>Arrivo</th><th>Volo</th><th>Pax</th><th>Totale</th><th>Stato</th></tr></thead>
        <tbody>
          <?php foreach ($bookings as $b): ?>
            <tr class="cursor-pointer" onclick="location.href='/admin/transfer-booking.php?id=<?= e($b['id']) ?>'">
              <td class="font-mono text-xs"><?= e($b['code']) ?></td>
              <td>
                <div class="font-medium"><?= e($b['customer_name']) ?></div>
                <div class="text-[11px] text-ink-500"><?= e($b['customer_phone']) ?></div>
              </td>
              <td class="text-xs"><?= e($b['from_location']) ?> → <?= e($b['to_location']) ?></td>
              <td class="text-xs"><?= fmtDate($b['arrival_date']) ?> <span class="text-ink-500"><?= e($b['arrival_time'] ?: '') ?></span></td>
              <td class="text-xs font-mono"><?= e($b['flight_number'] ?: '—') ?></td>
              <td class="tabular-nums"><?= (int)$b['passengers'] ?></td>
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
