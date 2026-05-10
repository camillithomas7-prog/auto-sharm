<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/utils.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck($_POST['csrf'] ?? null);
    $a = $_POST['action'] ?? '';
    if ($a === 'add') {
        q('INSERT INTO reviews (id, car_id, author_name, rating, title, body, approved) VALUES (?,?,?,?,?,?,1)',
            [newId(), $_POST['car_id'], $_POST['author_name'], (int)$_POST['rating'], $_POST['title'] ?: null, $_POST['body']]);
        flash('Recensione aggiunta');
    }
    if ($a === 'toggle') q('UPDATE reviews SET approved = 1 - approved WHERE id = ?', [$_POST['id']]);
    if ($a === 'delete') q('DELETE FROM reviews WHERE id = ?', [$_POST['id']]);
    redirect('/admin/recensioni.php');
}

$reviews = rows('SELECT r.*, c.name AS car_name FROM reviews r LEFT JOIN cars c ON c.id = r.car_id ORDER BY r.created_at DESC');
$cars = rows('SELECT id, name FROM cars ORDER BY name');

$title = 'Recensioni';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/admin-shell-top.php';
?>
<div class="space-y-5">
  <h1 class="font-display text-2xl sm:text-3xl font-bold">Recensioni</h1>
  <form method="post" class="card p-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="add">
    <select class="input" name="car_id" required><option value="">Auto…</option>
      <?php foreach ($cars as $c): ?><option value="<?= e($c['id']) ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
    </select>
    <input class="input" name="author_name" placeholder="Nome cliente" required>
    <select class="input" name="rating">
      <?php foreach ([5,4,3,2,1] as $r): ?><option value="<?= $r ?>"><?= str_repeat('★', $r) ?></option><?php endforeach; ?>
    </select>
    <input class="input" name="title" placeholder="Titolo (opz.)">
    <textarea class="input col-span-1 sm:col-span-2 min-h-[80px]" name="body" placeholder="Testo recensione" required></textarea>
    <button class="btn-primary col-span-1 sm:col-span-2 h-12"><i data-lucide="plus" class="size-[16px]"></i> Aggiungi recensione</button>
  </form>

  <div class="space-y-3">
    <?php foreach ($reviews as $r): ?>
      <div class="card p-5">
        <div class="flex items-start justify-between gap-3">
          <div>
            <div class="flex items-center gap-2 text-yellow-500"><?php for ($i=0; $i<(int)$r['rating']; $i++): ?>★<?php endfor; ?></div>
            <div class="font-display font-bold mt-1"><?= e($r['title'] ?: $r['author_name']) ?></div>
            <div class="text-xs text-ink-500"><?= e($r['author_name']) ?> · <?= e($r['car_name']) ?> · <?= fmtDate($r['created_at']) ?></div>
            <p class="text-sm mt-2"><?= e($r['body']) ?></p>
          </div>
          <div class="flex flex-col gap-1.5 shrink-0">
            <span class="<?= $r['approved']?'badge-success':'badge-soft' ?>"><?= $r['approved']?'Pubblica':'Nascosta' ?></span>
            <form method="post"><input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= e($r['id']) ?>"><button class="btn-ghost text-xs"><?= $r['approved']?'Nascondi':'Mostra' ?></button></form>
            <form method="post" onsubmit="return confirm('Eliminare?')"><input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($r['id']) ?>"><button class="btn-ghost text-red-600 text-xs">Elimina</button></form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$reviews): ?><div class="card p-10 text-center text-sm text-ink-500">Ancora nessuna recensione.</div><?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../partials/admin-shell-bottom.php';
