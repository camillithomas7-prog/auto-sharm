<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/utils.php';
requireAdmin();

$id = $_GET['id'] ?? null;
$tr = $id ? row('SELECT * FROM transfers WHERE id = ?', [$id]) : null;
if ($id && !$tr) { flash('Tratta non trovata', 'error'); redirect('/admin/transfer.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck($_POST['csrf'] ?? null);
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete' && $tr) {
        q('DELETE FROM transfers WHERE id = ?', [$tr['id']]);
        logActivity('delete', 'transfer', $tr['id'], $tr['name']);
        flash('Tratta eliminata');
        redirect('/admin/transfer.php');
    }

    $coverPath = $_POST['existing_cover'] ?? null;
    if (isset($_POST['remove_cover'])) $coverPath = null;
    if (!empty($_FILES['cover_image_file']['name']) && $_FILES['cover_image_file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cover_image_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $dir = __DIR__ . '/../uploads/photos';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $fname = 'transfer_' . substr(uniqid(), -8) . '.' . $ext;
            if (move_uploaded_file($_FILES['cover_image_file']['tmp_name'], $dir . '/' . $fname)) {
                $coverPath = '/uploads/photos/' . $fname;
            }
        }
    }

    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => trim($_POST['slug'] ?? '') ?: slugify(($_POST['from_location'] ?? '') . '-' . ($_POST['to_location'] ?? '')),
        'description' => $_POST['description'] ?? '',
        'from_location' => trim($_POST['from_location'] ?? ''),
        'to_location' => trim($_POST['to_location'] ?? ''),
        'vehicle_type' => $_POST['vehicle_type'] ?? 'sedan',
        'vehicle_capacity' => (int)($_POST['vehicle_capacity'] ?? 3),
        'duration_min' => (int)($_POST['duration_min'] ?? 30),
        'price' => (float)($_POST['price'] ?? 25),
        'manager_commission_pct' => (float)($_POST['manager_commission_pct'] ?? 20),
        'cover_image' => $coverPath,
        'active' => isset($_POST['active']) ? 1 : 0,
    ];

    if ($tr) {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
        q("UPDATE transfers SET $set WHERE id = ?", array_merge(array_values($data), [$tr['id']]));
        logActivity('update', 'transfer', $tr['id'], $data['name']);
        flash('Tratta aggiornata');
        redirect('/admin/transfer-edit.php?id=' . $tr['id']);
    } else {
        $newId = newId();
        $cols = implode(',', array_keys($data));
        $ph = implode(',', array_fill(0, count($data), '?'));
        q("INSERT INTO transfers (id, $cols) VALUES (?, $ph)", array_merge([$newId], array_values($data)));
        logActivity('create', 'transfer', $newId, $data['name']);
        flash('Tratta creata');
        redirect('/admin/transfer-edit.php?id=' . $newId);
    }
}

$defaults = [
    'name' => '', 'slug' => '', 'description' => '',
    'from_location' => 'Aeroporto SSH', 'to_location' => '',
    'vehicle_type' => 'sedan', 'vehicle_capacity' => 3,
    'duration_min' => 30, 'price' => 30,
    'manager_commission_pct' => 20,
    'cover_image' => '', 'active' => 1,
];
$f = $tr ? array_merge($defaults, $tr) : $defaults;

$title = $tr ? 'Modifica tratta' : 'Nuova tratta';
$_subtitle = 'Transfer';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/admin-shell-top.php';
?>
<form method="post" enctype="multipart/form-data" class="space-y-5">
  <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">

  <div class="flex items-end justify-between flex-wrap gap-3">
    <div>
      <h1 class="font-display text-2xl sm:text-3xl font-bold"><?= $tr ? 'Modifica tratta' : 'Nuova tratta' ?></h1>
      <p class="text-ink-500 text-sm mt-1">Tariffa fissa per veicolo (non per persona).</p>
    </div>
    <div class="flex gap-2">
      <?php if ($tr): ?>
        <button type="submit" name="action" value="delete" class="btn-danger" onclick="return confirm('Eliminare questa tratta?')"><i data-lucide="trash-2" class="size-[16px]"></i> Elimina</button>
      <?php endif; ?>
      <button class="btn-primary"><i data-lucide="save" class="size-[16px]"></i> Salva</button>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="card p-5 space-y-3">
      <h3 class="font-display font-bold">Tratta</h3>
      <label class="block"><span class="label">Nome (visibile sul sito)</span><input class="input" name="name" required value="<?= e($f['name']) ?>" placeholder="Aeroporto SSH → Naama Bay"></label>
      <div class="grid grid-cols-2 gap-3">
        <label class="block"><span class="label">Da</span><input class="input" name="from_location" required value="<?= e($f['from_location']) ?>" placeholder="Aeroporto SSH"></label>
        <label class="block"><span class="label">A</span><input class="input" name="to_location" required value="<?= e($f['to_location']) ?>" placeholder="Naama Bay"></label>
      </div>
      <label class="block"><span class="label">Slug URL (opz.)</span><input class="input" name="slug" value="<?= e($f['slug']) ?>" placeholder="aeroporto-ssh-naama-bay"></label>
      <label class="block"><span class="label">Descrizione</span><textarea class="input min-h-[120px]" name="description" placeholder="Cosa è incluso, durata, attesa..."><?= e($f['description']) ?></textarea></label>
      <label class="block">
        <span class="label">Foto di copertina</span>
        <input type="hidden" name="existing_cover" value="<?= e($f['cover_image']) ?>">
        <?php if ($f['cover_image']): ?>
          <div class="relative inline-block mb-2">
            <img src="<?= e($f['cover_image']) ?>" class="h-32 rounded-xl object-cover border border-ink-200">
            <label class="absolute -top-2 -right-2 inline-flex items-center justify-center h-6 w-6 rounded-full bg-red-500 text-white cursor-pointer text-xs">
              <input type="checkbox" name="remove_cover" value="1" class="sr-only">×
            </label>
          </div>
        <?php endif; ?>
        <input type="file" name="cover_image_file" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm">
      </label>
    </div>

    <div class="card p-5 space-y-3">
      <h3 class="font-display font-bold">Veicolo & prezzo</h3>
      <div class="grid grid-cols-2 gap-3">
        <label class="block"><span class="label">Tipo veicolo</span>
          <select class="input" name="vehicle_type">
            <?php foreach (['sedan','minivan','bus'] as $vt): ?>
              <option value="<?= $vt ?>" <?= $f['vehicle_type']===$vt?'selected':'' ?>><?= e(t('transfer.veh.' . $vt)) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="block"><span class="label">Capienza (passeggeri max)</span><input class="input" type="number" name="vehicle_capacity" min="1" max="50" value="<?= (int)$f['vehicle_capacity'] ?>"></label>
        <label class="block"><span class="label">Durata stimata (min)</span><input class="input" type="number" name="duration_min" min="5" max="480" value="<?= (int)$f['duration_min'] ?>"></label>
        <label class="block"><span class="label">Prezzo (€) *</span><input class="input" type="number" step="0.01" name="price" required value="<?= e((string)$f['price']) ?>"></label>
      </div>
      <div class="flex items-center gap-2 text-sm pt-2"><input type="checkbox" name="active" <?= $f['active']?'checked':'' ?>> Visibile sul sito</div>
    </div>

    <div class="card p-5 space-y-3 border-2 border-brand-200 bg-brand-50/40 lg:col-span-2">
      <div class="flex items-start gap-3">
        <span class="h-9 w-9 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center shrink-0"><i data-lucide="percent" class="size-[18px]"></i></span>
        <div>
          <h3 class="font-display font-bold">Commissione gestione</h3>
          <p class="text-xs text-ink-500 mt-0.5">% trattenuta sul prezzo della tratta (per il calcolo del margine in "Spese & bilancio").</p>
        </div>
      </div>
      <label class="block max-w-xs">
        <span class="label">Commissione (%)</span>
        <div class="relative">
          <input class="input pr-10" type="number" step="0.01" min="0" max="100" name="manager_commission_pct" value="<?= e((string)$f['manager_commission_pct']) ?>">
          <span class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-400 text-sm">%</span>
        </div>
      </label>
    </div>
  </div>
</form>
<?php require __DIR__ . '/../partials/admin-shell-bottom.php';
