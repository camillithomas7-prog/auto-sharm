<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/utils.php';
requireAdmin();

$cars = rows('SELECT id, name FROM cars WHERE active = 1 ORDER BY name');
$ref = $_GET['m'] ?? date('Y-m');
$first = strtotime($ref . '-01');
$next = date('Y-m', strtotime('+1 month', $first));
$prev = date('Y-m', strtotime('-1 month', $first));
$bookings = rows("SELECT b.car_id, b.pickup_date, b.dropoff_date, b.status, cu.name AS customer_name FROM bookings b JOIN customers cu ON cu.id = b.customer_id WHERE b.status NOT IN ('cancelled','rejected')");

$title = 'Calendario';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/admin-shell-top.php';
?>
<div class="space-y-5">
  <div class="flex items-center justify-between flex-wrap gap-3">
    <h1 class="font-display text-2xl sm:text-3xl font-bold"><?= date('F Y', $first) ?></h1>
    <div class="flex gap-2">
      <a href="?m=<?= $prev ?>" class="btn-outline"><i data-lucide="chevron-left" class="size-[16px]"></i></a>
      <a href="?m=<?= date('Y-m') ?>" class="btn-outline">Oggi</a>
      <a href="?m=<?= $next ?>" class="btn-outline"><i data-lucide="chevron-right" class="size-[16px]"></i></a>
    </div>
  </div>

  <div class="card p-5 overflow-x-auto">
    <?php if (!$cars): ?>
      <div class="text-sm text-ink-500 text-center py-10">Aggiungi un'auto per vedere il calendario.</div>
    <?php else: ?>
      <?php $daysInMonth = (int)date('t', $first); ?>
      <table class="text-xs border-collapse min-w-full">
        <thead>
          <tr>
            <th class="sticky left-0 bg-white text-left px-3 py-2 border-b border-ink-100">Auto</th>
            <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
              <th class="px-1.5 py-2 border-b border-ink-100 text-center font-medium text-ink-500"><?= $d ?></th>
            <?php endfor; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cars as $car): ?>
            <tr>
              <td class="sticky left-0 bg-white px-3 py-2 border-b border-ink-100 font-medium whitespace-nowrap"><?= e($car['name']) ?></td>
              <?php for ($d = 1; $d <= $daysInMonth; $d++):
                $day = date('Y-m-d', mktime(0,0,0, (int)date('n', $first), $d, (int)date('Y', $first)));
                $busy = false;
                foreach ($bookings as $b) {
                  if ($b['car_id'] !== $car['id']) continue;
                  if ($day >= $b['pickup_date'] && $day < $b['dropoff_date']) { $busy = true; break; }
                }
              ?>
                <td class="px-1 py-1 border-b border-ink-100 text-center">
                  <span class="inline-block h-6 w-6 rounded-md <?= $busy ? 'bg-brand-500' : 'bg-emerald-100' ?>"></span>
                </td>
              <?php endfor; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../partials/admin-shell-bottom.php';
