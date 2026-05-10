<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/utils.php';
requireAdmin();

$id = $_GET['id'] ?? null;
$car = $id ? row('SELECT * FROM cars WHERE id = ?', [$id]) : null;
if ($id && !$car) { flash('Auto non trovata', 'error'); redirect('/admin/auto.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck($_POST['csrf'] ?? null);
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete' && $car) {
        q('DELETE FROM cars WHERE id = ?', [$car['id']]);
        logActivity('delete', 'car', $car['id'], $car['name']);
        flash('Auto eliminata');
        redirect('/admin/auto.php');
    }

    // Cover upload
    $coverPath = $_POST['existing_cover'] ?? null;
    if (isset($_POST['remove_cover'])) $coverPath = null;
    if (!empty($_FILES['cover_image_file']['name']) && $_FILES['cover_image_file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cover_image_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $dir = __DIR__ . '/../uploads/photos';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $fname = 'cover_' . substr(uniqid(), -8) . '.' . $ext;
            if (move_uploaded_file($_FILES['cover_image_file']['tmp_name'], $dir . '/' . $fname)) {
                $coverPath = '/uploads/photos/' . $fname;
            }
        }
    }

    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => trim($_POST['slug'] ?? '') ?: slugify($_POST['name'] ?? ''),
        'brand' => trim($_POST['brand'] ?? ''),
        'model' => trim($_POST['model'] ?? ''),
        'description' => $_POST['description'] ?? '',
        'category' => $_POST['category'] ?? 'compact',
        'transmission' => $_POST['transmission'] ?? 'manual',
        'fuel' => $_POST['fuel'] ?? 'petrol',
        'seats' => (int)($_POST['seats'] ?? 5),
        'doors' => (int)($_POST['doors'] ?? 4),
        'luggage' => (int)($_POST['luggage'] ?? 2),
        'year' => (int)($_POST['year'] ?? date('Y')),
        'features' => json_encode(array_values(array_filter(array_map('trim', explode(',', $_POST['features'] ?? ''))))),
        'daily_price' => (float)$_POST['daily_price'],
        'weekly_price' => $_POST['weekly_price'] !== '' ? (float)$_POST['weekly_price'] : null,
        'biweekly_price' => $_POST['biweekly_price'] !== '' ? (float)$_POST['biweekly_price'] : null,
        'monthly_price' => $_POST['monthly_price'] !== '' ? (float)$_POST['monthly_price'] : null,
        'security_deposit' => (float)($_POST['security_deposit'] ?? 0),
        'license_required' => isset($_POST['license_required']) ? 1 : 0,
        'min_age' => (int)($_POST['min_age'] ?? 21),
        'manager_commission_pct' => (float)($_POST['manager_commission_pct'] ?? 20),
        'owner_name' => trim($_POST['owner_name'] ?? ''),
        'cover_image' => $coverPath,
        'active' => isset($_POST['active']) ? 1 : 0,
    ];

    if ($car) {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
        q("UPDATE cars SET $set WHERE id = ?", array_merge(array_values($data), [$car['id']]));
        logActivity('update', 'car', $car['id'], $data['name']);
        flash('Auto aggiornata');
        redirect('/admin/auto-edit.php?id=' . $car['id']);
    } else {
        $newId = newId();
        $cols = implode(',', array_keys($data));
        $ph = implode(',', array_fill(0, count($data), '?'));
        q("INSERT INTO cars (id, $cols) VALUES (?, $ph)", array_merge([$newId], array_values($data)));
        logActivity('create', 'car', $newId, $data['name']);
        flash('Auto creata');
        redirect('/admin/auto-edit.php?id=' . $newId);
    }
}

$defaults = [
    'name' => '', 'slug' => '', 'brand' => '', 'model' => '', 'description' => '',
    'category' => 'compact', 'transmission' => 'manual', 'fuel' => 'petrol',
    'seats' => 5, 'doors' => 4, 'luggage' => 2, 'year' => (int)date('Y'),
    'daily_price' => 30, 'weekly_price' => '', 'biweekly_price' => '', 'monthly_price' => '',
    'security_deposit' => 100, 'license_required' => 1, 'min_age' => 21,
    'manager_commission_pct' => 20, 'owner_name' => '',
    'cover_image' => '', 'active' => 1,
];
$f = $car ? array_merge($defaults, $car) : $defaults;
$featuresStr = $car ? implode(', ', parseFeatures($car['features'])) : 'Aria condizionata, Bluetooth, USB';

$title = $car ? 'Modifica auto' : 'Nuova auto';
$_subtitle = 'Auto';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/admin-shell-top.php';
?>
<form method="post" enctype="multipart/form-data" class="space-y-5">
  <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">

  <div class="flex items-end justify-between flex-wrap gap-3">
    <div>
      <h1 class="font-display text-2xl sm:text-3xl font-bold"><?= $car ? 'Modifica auto' : 'Nuova auto' ?></h1>
      <p class="text-ink-500 text-sm mt-1">Compila tutti i dati che vuoi mostrare ai clienti.</p>
    </div>
    <div class="flex gap-2">
      <?php if ($car): ?>
        <button type="submit" name="action" value="delete" class="btn-danger" onclick="return confirm('Eliminare questa auto?')"><i data-lucide="trash-2" class="size-[16px]"></i> Elimina</button>
      <?php endif; ?>
      <button class="btn-primary"><i data-lucide="save" class="size-[16px]"></i> Salva</button>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="card p-5 space-y-3">
      <h3 class="font-display font-bold">Dati principali</h3>
      <label class="block"><span class="label">Nome (visibile sul sito)</span><input class="input" name="name" required value="<?= e($f['name']) ?>" placeholder="es. Toyota Corolla 2024"></label>
      <div class="grid grid-cols-2 gap-3">
        <label class="block"><span class="label">Marca</span><input class="input" name="brand" value="<?= e($f['brand']) ?>" placeholder="Toyota"></label>
        <label class="block"><span class="label">Modello</span><input class="input" name="model" value="<?= e($f['model']) ?>" placeholder="Corolla"></label>
      </div>
      <label class="block"><span class="label">Slug URL (opz.)</span><input class="input" name="slug" value="<?= e($f['slug']) ?>" placeholder="toyota-corolla"></label>
      <label class="block"><span class="label">Descrizione</span><textarea class="input min-h-[120px]" name="description" placeholder="Comoda, affidabile, perfetta per le coppie..."><?= e($f['description']) ?></textarea></label>
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
      <h3 class="font-display font-bold">Caratteristiche</h3>
      <div class="grid grid-cols-2 gap-3">
        <label class="block"><span class="label">Categoria</span>
          <select class="input" name="category">
            <?php foreach (['economy','compact','suv','luxury','minivan'] as $cat): ?>
              <option value="<?= $cat ?>" <?= $f['category']===$cat?'selected':'' ?>><?= e(t('cat.' . $cat)) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="block"><span class="label">Anno</span><input class="input" type="number" name="year" min="2000" max="2030" value="<?= (int)$f['year'] ?>"></label>
        <label class="block"><span class="label">Cambio</span>
          <select class="input" name="transmission">
            <option value="manual" <?= $f['transmission']==='manual'?'selected':'' ?>>Manuale</option>
            <option value="automatic" <?= $f['transmission']==='automatic'?'selected':'' ?>>Automatico</option>
          </select>
        </label>
        <label class="block"><span class="label">Carburante</span>
          <select class="input" name="fuel">
            <option value="petrol" <?= $f['fuel']==='petrol'?'selected':'' ?>>Benzina</option>
            <option value="diesel" <?= $f['fuel']==='diesel'?'selected':'' ?>>Diesel</option>
            <option value="hybrid" <?= $f['fuel']==='hybrid'?'selected':'' ?>>Ibrida</option>
            <option value="electric" <?= $f['fuel']==='electric'?'selected':'' ?>>Elettrica</option>
          </select>
        </label>
        <label class="block"><span class="label">Posti</span><input class="input" type="number" name="seats" min="1" max="20" value="<?= (int)$f['seats'] ?>"></label>
        <label class="block"><span class="label">Porte</span><input class="input" type="number" name="doors" min="2" max="6" value="<?= (int)$f['doors'] ?>"></label>
        <label class="block col-span-2"><span class="label">Bagagli (numero valigie)</span><input class="input" type="number" name="luggage" min="0" max="10" value="<?= (int)$f['luggage'] ?>"></label>
      </div>
      <label class="block"><span class="label">Servizi inclusi (separati da virgola)</span><input class="input" name="features" value="<?= e($featuresStr) ?>" placeholder="Aria condizionata, Bluetooth, GPS..."></label>
      <p class="text-[11px] text-ink-500">Le voci comuni vengono tradotte automaticamente in 5 lingue.</p>
    </div>

    <div class="card p-5 space-y-3">
      <h3 class="font-display font-bold">Tariffe</h3>
      <div class="grid grid-cols-2 gap-3">
        <label class="block"><span class="label">Giornaliera (€) *</span><input class="input" type="number" step="0.01" name="daily_price" value="<?= e((string)$f['daily_price']) ?>" required></label>
        <label class="block"><span class="label">Settimanale (totale)</span><input class="input" type="number" step="0.01" name="weekly_price" value="<?= e((string)$f['weekly_price']) ?>"></label>
        <label class="block"><span class="label">2 settimane (totale)</span><input class="input" type="number" step="0.01" name="biweekly_price" value="<?= e((string)$f['biweekly_price']) ?>"></label>
        <label class="block"><span class="label">Mensile (totale)</span><input class="input" type="number" step="0.01" name="monthly_price" value="<?= e((string)$f['monthly_price']) ?>"></label>
        <label class="block"><span class="label">Cauzione (€)</span><input class="input" type="number" step="0.01" name="security_deposit" value="<?= e((string)$f['security_deposit']) ?>"></label>
        <label class="block"><span class="label">Età minima</span><input class="input" type="number" name="min_age" value="<?= (int)$f['min_age'] ?>"></label>
      </div>
      <div class="flex flex-wrap gap-4 pt-2">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="active" <?= $f['active']?'checked':'' ?>> Visibile sul sito</label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="license_required" <?= $f['license_required']?'checked':'' ?>> Patente obbligatoria</label>
      </div>
    </div>

    <div class="card p-5 space-y-3 border-2 border-brand-200 bg-brand-50/40">
      <div class="flex items-start gap-3">
        <span class="h-9 w-9 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center shrink-0"><i data-lucide="percent" class="size-[18px]"></i></span>
        <div>
          <h3 class="font-display font-bold">Property management</h3>
          <p class="text-xs text-ink-500 mt-0.5">Imposta la tua commissione di gestione (se l'auto è di un proprietario terzo).</p>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <label class="block">
          <span class="label">Commissione (%)</span>
          <div class="relative">
            <input class="input pr-10" type="number" step="0.01" min="0" max="100" name="manager_commission_pct" value="<?= e((string)$f['manager_commission_pct']) ?>">
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-400 text-sm">%</span>
          </div>
        </label>
        <label class="block"><span class="label">Proprietario (memo)</span><input class="input" name="owner_name" value="<?= e((string)$f['owner_name']) ?>" placeholder="Nome dell'imprenditore"></label>
      </div>
    </div>
  </div>
</form>
<?php require __DIR__ . '/../partials/admin-shell-bottom.php';
