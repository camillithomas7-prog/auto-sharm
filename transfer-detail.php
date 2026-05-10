<?php
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/utils.php';

if (!featureEnabled('transfer')) {
    http_response_code(404);
    redirect('/');
}

$slug = $_GET['slug'] ?? '';
$tr = row('SELECT * FROM transfers WHERE slug = ? AND active = 1', [$slug]);
if (!$tr) {
    http_response_code(404);
    $title = '404';
    require __DIR__ . '/partials/head.php';
    require __DIR__ . '/partials/site-header.php';
    echo '<div class="container-narrow card p-14 mt-20 text-center"><h1 class="font-serif text-3xl">' . e(t('fleet.no_items')) . '</h1><a href="/transfer.php" class="btn-primary mt-6 inline-flex">' . e(t('transfer.see_routes')) . '</a></div>';
    require __DIR__ . '/partials/site-footer.php';
    exit;
}

$title = $tr['name'];
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/site-header.php';
$_lp = currentLang() !== 'it' ? '?lang=' . urlencode(currentLang()) : '';
?>
<div class="container-wide pt-8 pb-4">
  <a href="/transfer.php<?= $_lp ?>" class="text-sm text-ink-500 hover:text-brand-600 inline-flex items-center gap-1"><i data-lucide="chevron-left" class="size-[14px]"></i> <?= e(t('transfer.see_routes')) ?></a>
  <div class="mt-4">
    <div class="flex flex-wrap gap-2 mb-3">
      <span class="badge-brand"><?= e(t('transfer.veh.' . $tr['vehicle_type'])) ?></span>
      <span class="badge-soft"><i data-lucide="users" class="size-[12px]"></i> <?= (int)$tr['vehicle_capacity'] ?> <?= e(t('transfer.passengers')) ?></span>
      <span class="badge-soft"><i data-lucide="clock" class="size-[12px]"></i> <?= (int)$tr['duration_min'] ?> <?= e(t('transfer.minutes')) ?></span>
    </div>
    <h1 class="font-serif text-4xl md:text-6xl font-semibold tracking-tight max-w-3xl text-balance"><?= e($tr['name']) ?></h1>
    <div class="text-ink-500 mt-2 text-lg"><?= e($tr['from_location']) ?> → <?= e($tr['to_location']) ?></div>
  </div>
</div>

<div class="container-wide pb-4">
  <div class="rounded-3xl overflow-hidden h-[260px] sm:h-[400px] bg-gradient-to-br from-ink-100 to-ink-200 dark:from-ink-900 dark:to-ink-800 flex items-center justify-center">
    <?php if ($tr['cover_image'] && file_exists(__DIR__ . $tr['cover_image'])): ?>
      <img src="<?= e($tr['cover_image']) ?>" alt="<?= e($tr['name']) ?>" class="h-full w-full object-cover">
    <?php else: ?>
      <i data-lucide="route" class="size-[180px] text-ink-300 dark:text-ink-700"></i>
    <?php endif; ?>
  </div>
</div>

<div class="container-wide pb-20">
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
    <div class="lg:col-span-2 space-y-8">
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <div class="card p-4">
          <div class="text-ink-500 text-xs flex items-center gap-1.5 uppercase tracking-wider"><i data-lucide="map-pin" class="size-[14px]"></i> <?= e(t('transfer.from')) ?></div>
          <div class="font-display font-bold text-lg mt-1 truncate"><?= e($tr['from_location']) ?></div>
        </div>
        <div class="card p-4">
          <div class="text-ink-500 text-xs flex items-center gap-1.5 uppercase tracking-wider"><i data-lucide="map-pin-check" class="size-[14px]"></i> <?= e(t('transfer.to')) ?></div>
          <div class="font-display font-bold text-lg mt-1 truncate"><?= e($tr['to_location']) ?></div>
        </div>
        <div class="card p-4">
          <div class="text-ink-500 text-xs flex items-center gap-1.5 uppercase tracking-wider"><i data-lucide="clock" class="size-[14px]"></i> <?= e(t('transfer.duration')) ?></div>
          <div class="font-display font-bold text-lg mt-1"><?= (int)$tr['duration_min'] ?> <?= e(t('transfer.minutes')) ?></div>
        </div>
      </div>

      <?php if ($tr['description']): ?>
        <div>
          <h2 class="font-serif text-3xl font-semibold tracking-tight mb-3"><?= e(t('car.description')) ?></h2>
          <p class="text-ink-700 dark:text-ink-300 whitespace-pre-line leading-relaxed"><?= e($tr['description']) ?></p>
        </div>
      <?php endif; ?>
    </div>

    <aside id="book" class="lg:sticky lg:top-24 self-start scroll-mt-24" x-data="transferBookingForm()">
      <div class="card-elev p-6 shadow-card">
        <div class="flex items-baseline gap-1 mb-4">
          <span class="font-display text-3xl font-bold text-brand-600"><?= fmtMoney((float)$tr['price']) ?></span>
          <span class="text-sm text-ink-500"><?= e(t('transfer.veh.' . $tr['vehicle_type'])) ?></span>
        </div>

        <template x-if="done">
          <div class="text-center py-6 animate-fade-in">
            <div class="h-16 w-16 mx-auto rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-3"><i data-lucide="check" class="size-[32px]"></i></div>
            <div class="font-display text-xl font-bold"><?= e(t('transfer.success.title')) ?></div>
            <p class="text-sm text-ink-500 mt-1"><?= e(t('book.code')) ?></p>
            <p class="font-mono text-base mt-1" x-text="done"></p>
            <p class="text-xs text-ink-500 mt-3"><?= e(t('transfer.success.sub')) ?></p>
          </div>
        </template>

        <form x-show="!done" @submit.prevent="submit" class="space-y-2.5">
          <h3 class="font-display font-bold text-lg mb-2"><?= e(t('transfer.book.title')) ?></h3>

          <div class="grid grid-cols-2 gap-0 rounded-2xl border border-ink-100 dark:border-ink-700/60 bg-white dark:bg-ink-900/40 shadow-sm overflow-hidden divide-x divide-ink-100 dark:divide-ink-700/60 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/15 transition-all">
            <label class="block px-3.5 py-2.5 cursor-pointer hover:bg-ink-50/60">
              <span class="text-[10px] font-semibold uppercase tracking-wider text-ink-400"><?= e(t('transfer.field.arrival_date')) ?></span>
              <input type="date" required class="w-full bg-transparent outline-none text-[15px] font-medium text-ink-800 dark:text-ink-100 mt-0.5" x-model="arrival_date">
            </label>
            <label class="block px-3.5 py-2.5 cursor-pointer hover:bg-ink-50/60">
              <span class="text-[10px] font-semibold uppercase tracking-wider text-ink-400"><?= e(t('transfer.field.arrival_time')) ?></span>
              <input type="time" class="w-full bg-transparent outline-none text-[15px] font-medium text-ink-800 dark:text-ink-100 mt-0.5" x-model="arrival_time">
            </label>
          </div>

          <label class="block px-3.5 py-2.5 rounded-2xl border border-ink-100 bg-white shadow-sm focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/15 transition-all">
            <span class="text-[10px] font-semibold uppercase tracking-wider text-ink-400"><?= e(t('transfer.field.flight')) ?></span>
            <input class="w-full bg-transparent outline-none text-[15px] font-medium mt-0.5" placeholder="ES1234" x-model="flight">
          </label>

          <label class="block px-3.5 py-2.5 rounded-2xl border border-ink-100 bg-white shadow-sm focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/15 transition-all">
            <span class="text-[10px] font-semibold uppercase tracking-wider text-ink-400"><?= e(t('transfer.field.destination')) ?></span>
            <input class="w-full bg-transparent outline-none text-[15px] font-medium mt-0.5" placeholder="Hilton, Naama Bay" x-model="destination">
          </label>

          <label class="block px-3.5 py-2.5 rounded-2xl border border-ink-100 bg-white shadow-sm focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/15 transition-all">
            <span class="text-[10px] font-semibold uppercase tracking-wider text-ink-400"><?= e(t('transfer.field.passengers')) ?></span>
            <input type="number" min="1" max="<?= (int)$tr['vehicle_capacity'] ?>" required class="w-full bg-transparent outline-none text-[15px] font-medium mt-0.5" x-model.number="passengers">
          </label>

          <div class="space-y-2 pt-2">
            <input required placeholder="<?= e(t('transfer.field.name')) ?>" class="input" x-model="name">
            <input type="email" required placeholder="<?= e(t('transfer.field.email')) ?>" class="input" x-model="email">

            <div class="relative flex w-full max-w-full" @click.outside="dialOpen=false">
              <button type="button" @click="dialOpen=!dialOpen; if(dialOpen){$nextTick(()=>$refs.dialSearch.focus())}"
                class="rounded-xl rounded-r-none has-border border-r-0 border-ink-200 bg-white px-2.5 py-2.5 flex items-center gap-1 cursor-pointer shrink-0 w-[96px] hover:bg-ink-50 transition">
                <span class="text-base leading-none" x-text="flagOf(dial)"></span>
                <span class="text-sm font-medium tabular-nums" x-text="'+' + dialCodeOf(dial)"></span>
                <i data-lucide="chevron-down" class="size-[12px] text-ink-400 shrink-0 transition" :class="dialOpen && 'rotate-180'"></i>
              </button>
              <div class="flex-1 min-w-0">
                <input required type="tel" placeholder="<?= e(t('transfer.field.phone')) ?>"
                  class="w-full rounded-xl rounded-l-none has-border border-ink-200 bg-white px-3.5 py-2.5 text-sm placeholder:text-ink-400 focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition-all"
                  x-model="phone" inputmode="tel">
              </div>
              <div x-show="dialOpen" x-cloak x-transition.opacity.duration.150ms
                   class="absolute z-50 top-full mt-1 left-0 right-0 sm:w-[280px] sm:right-auto bg-white has-border border-ink-200 rounded-2xl shadow-pop overflow-hidden"
                   style="display:none">
                <div class="p-2 border-b border-ink-100">
                  <div class="relative">
                    <i data-lucide="search" class="size-[14px] absolute left-2.5 top-1/2 -translate-y-1/2 text-ink-400"></i>
                    <input x-ref="dialSearch" x-model="dialSearch" type="text" placeholder="<?= e(t('form.country_search')) ?>"
                           class="w-full pl-8 pr-3 py-2 text-sm bg-ink-50 rounded-xl outline-none focus:ring-2 focus:ring-brand-500/30">
                  </div>
                </div>
                <ul class="max-h-[260px] overflow-y-auto py-1">
                  <template x-for="c in filteredDials()" :key="c.code">
                    <li>
                      <button type="button" @click="dial=c.code; dialOpen=false; dialSearch=''"
                        class="w-full px-3 py-2 flex items-center gap-2.5 text-sm text-left hover:bg-brand-50 transition"
                        :class="dial===c.code && 'bg-brand-50 text-brand-700'">
                        <span class="text-base leading-none" x-text="flagOf(c.code)"></span>
                        <span class="flex-1 truncate" x-text="c.name"></span>
                        <span class="text-ink-500 tabular-nums text-xs" x-text="'+' + c.dial"></span>
                      </button>
                    </li>
                  </template>
                </ul>
              </div>
            </div>

            <input placeholder="<?= e(t('transfer.field.notes')) ?>" class="input" x-model="notes">
          </div>

          <div x-show="err" x-text="err" class="text-sm text-red-600 p-2 rounded-lg bg-red-50"></div>

          <button :disabled="busy" class="btn-primary w-full h-12 text-base">
            <span x-show="!busy"><?= e(t('transfer.cta')) ?></span>
            <span x-show="busy" class="flex items-center gap-2"><i data-lucide="loader-2" class="size-[18px] animate-spin"></i> <?= e(t('book.sending')) ?></span>
          </button>
          <p class="text-[11px] text-ink-500 text-center"><?= e(t('book.no_charge')) ?></p>
        </form>
      </div>
    </aside>
  </div>
</div>

<script>
function transferBookingForm() {
  return {
    transferId: <?= json_encode($tr['id']) ?>,
    arrival_date: '', arrival_time: '', flight: '', destination: '', passengers: 1,
    name: '', email: '', phone: '', notes: '',
    dial: 'IT', dialOpen: false, dialSearch: '',
    phoneCountries: <?= json_encode(phoneCountryList(currentLang())) ?>,
    busy: false, done: null, err: '',
    flagOf(code) {
      if (!code || code.length !== 2) return '';
      return code.toUpperCase().replace(/./g, c => String.fromCodePoint(127397 + c.charCodeAt(0)));
    },
    dialCodeOf(code) {
      const f = this.phoneCountries.find(c => c.code === code);
      return f ? f.dial : '';
    },
    filteredDials() {
      const q = (this.dialSearch || '').trim().toLowerCase();
      if (!q) return this.phoneCountries;
      return this.phoneCountries.filter(c => c.name.toLowerCase().includes(q) || c.dial.includes(q) || c.code.toLowerCase().includes(q));
    },
    async submit() {
      this.busy = true; this.err = '';
      const dialCode = this.dialCodeOf(this.dial) || '39';
      const phoneFull = '+' + dialCode + ' ' + (this.phone || '').replace(/^\+?\d{1,4}\s*/, '');
      try {
        const r = await fetch('/api/transfer-booking.php', { method: 'POST', headers: { 'content-type': 'application/json' },
          body: JSON.stringify({
            transfer_id: this.transferId,
            arrival_date: this.arrival_date, arrival_time: this.arrival_time,
            flight_number: this.flight, destination: this.destination, passengers: this.passengers,
            name: this.name, email: this.email, phone: phoneFull, notes: this.notes
          }) });
        const d = await r.json();
        if (!r.ok) throw new Error(d.error || 'Errore');
        this.done = d.code;
      } catch (e) { this.err = e.message; } finally { this.busy = false; }
    }
  };
}
</script>

<?php require __DIR__ . '/partials/site-footer.php';
