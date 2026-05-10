<?php
require_once __DIR__ . '/../lib/auth.php';

if (currentUser()) redirect('/admin/index.php');

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck($_POST['csrf'] ?? null);
    if (login($_POST['email'] ?? '', $_POST['password'] ?? '')) redirect('/admin/index.php');
    $err = 'Email o password errate';
}

$title = 'Login Admin';
require __DIR__ . '/../partials/head.php';
?>
<div class="min-h-screen grid place-items-center bg-gradient-to-br from-ink-950 via-ink-900 to-brand-950 p-5">
  <div class="card-elev w-full max-w-md p-8 bg-white">
    <div class="flex justify-center mb-6">
      <img src="/assets/logo.png" alt="Auto Sharm" class="h-12">
    </div>
    <h1 class="font-display text-2xl font-bold text-center">Area Admin</h1>
    <p class="text-sm text-ink-500 text-center mt-1">Accedi per gestire la flotta</p>

    <?php if ($err): ?><div class="mt-4 p-3 rounded-xl bg-red-50 text-red-700 text-sm border border-red-200"><?= e($err) ?></div><?php endif; ?>

    <form method="post" class="mt-6 space-y-3">
      <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
      <label class="block">
        <span class="label">Email</span>
        <input class="input" type="email" name="email" required autofocus value="admin@autosharm.com">
      </label>
      <label class="block">
        <span class="label">Password</span>
        <input class="input" type="password" name="password" required>
      </label>
      <button class="btn-primary w-full h-12">Accedi <i data-lucide="arrow-right" class="size-[16px]"></i></button>
    </form>
    <div class="text-center mt-4">
      <a href="/" class="text-xs text-ink-500 hover:text-brand-600">← Torna al sito</a>
    </div>
  </div>
</div>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>
