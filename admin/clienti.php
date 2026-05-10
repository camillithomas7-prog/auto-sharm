<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/utils.php';
requireAdmin();

$q = $_GET['q'] ?? '';
if ($q) {
    $clients = rows('SELECT * FROM customers WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? ORDER BY created_at DESC',
        ["%$q%", "%$q%", "%$q%"]);
} else {
    $clients = rows('SELECT * FROM customers ORDER BY created_at DESC');
}

$title = 'Clienti';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/admin-shell-top.php';
?>
<div class="space-y-5">
  <div class="flex items-end justify-between flex-wrap gap-3">
    <h1 class="font-display text-2xl sm:text-3xl font-bold">Clienti</h1>
    <form method="get" class="flex gap-2"><input class="input" name="q" value="<?= e($q) ?>" placeholder="Cerca nome, email, telefono…"><button class="btn-outline">Cerca</button></form>
  </div>
  <div class="card p-5 overflow-x-auto">
    <?php if (!$clients): ?>
      <div class="text-center py-10 text-sm text-ink-500">Nessun cliente.</div>
    <?php else: ?>
      <table class="table-base">
        <thead><tr><th>Nome</th><th>Email</th><th>Telefono</th><th>Paese</th><th>Registrato</th></tr></thead>
        <tbody>
          <?php foreach ($clients as $c): ?>
            <tr><td class="font-medium"><?= e($c['name']) ?></td><td><?= e($c['email']) ?></td><td><?= e($c['phone']) ?></td><td><?= e($c['country']) ?></td><td class="text-xs text-ink-500"><?= fmtDate($c['created_at']) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../partials/admin-shell-bottom.php';
