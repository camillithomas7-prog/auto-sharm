<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/utils.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck($_POST['csrf'] ?? null);
    $a = $_POST['action'] ?? '';
    if ($a === 'add') {
        q('INSERT INTO coupons (id, code, percent, active) VALUES (?,?,?,1)',
            [newId(), strtoupper(trim($_POST['code'])), (float)$_POST['percent']]);
        flash('Coupon creato');
    }
    if ($a === 'toggle') {
        q('UPDATE coupons SET active = 1 - active WHERE id = ?', [$_POST['id']]);
    }
    if ($a === 'delete') {
        q('DELETE FROM coupons WHERE id = ?', [$_POST['id']]);
    }
    redirect('/admin/coupon.php');
}

$coupons = rows('SELECT * FROM coupons ORDER BY created_at DESC');

$title = 'Coupon';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/admin-shell-top.php';
?>
<div class="space-y-5">
  <h1 class="font-display text-2xl sm:text-3xl font-bold">Codici sconto</h1>
  <form method="post" class="card p-5 grid grid-cols-1 sm:grid-cols-3 gap-2">
    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="add">
    <input class="input" name="code" placeholder="es. SHARM10" required style="text-transform:uppercase">
    <input class="input" type="number" step="0.01" name="percent" placeholder="% di sconto" required>
    <button class="btn-primary"><i data-lucide="plus" class="size-[16px]"></i> Crea coupon</button>
  </form>
  <div class="card p-5 overflow-x-auto">
    <?php if (!$coupons): ?>
      <div class="text-sm text-ink-500 text-center py-6">Nessun coupon.</div>
    <?php else: ?>
      <table class="table-base">
        <thead><tr><th>Codice</th><th>Sconto</th><th>Stato</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($coupons as $c): ?>
            <tr>
              <td class="font-mono"><?= e($c['code']) ?></td>
              <td><?= number_format((float)$c['percent'], 0) ?>%</td>
              <td><span class="<?= $c['active']?'badge-success':'badge-soft' ?>"><?= $c['active']?'Attivo':'Off' ?></span></td>
              <td class="flex gap-1">
                <form method="post"><input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= e($c['id']) ?>"><button class="btn-ghost"><i data-lucide="<?= $c['active']?'eye-off':'eye' ?>" class="size-[14px]"></i></button></form>
                <form method="post" onsubmit="return confirm('Eliminare?')"><input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($c['id']) ?>"><button class="btn-ghost text-red-600"><i data-lucide="trash-2" class="size-[14px]"></i></button></form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../partials/admin-shell-bottom.php';
