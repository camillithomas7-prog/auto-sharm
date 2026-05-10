<?php
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/utils.php';

$slug = $_GET['slug'] ?? '';
$c = row('SELECT * FROM cars WHERE slug = ? AND active = 1', [$slug]);
if (!$c) {
    http_response_code(404);
    $title = '404';
    require __DIR__ . '/partials/head.php';
    require __DIR__ . '/partials/site-header.php';
    echo '<div class="container-narrow card p-14 mt-20 text-center"><h1 class="font-serif text-3xl">' . e(t('fleet.no_items')) . '</h1><a href="/flotta.php" class="btn-primary mt-6 inline-flex">' . e(t('car.back')) . '</a></div>';
    require __DIR__ . '/partials/site-footer.php';
    exit;
}

$features = parseFeatures($c['features']);

// Bookings (rimappo i nomi colonna a check_in/check_out per riusare il partial calendario)
$_rawBookings = rows("SELECT pickup_date AS check_in, dropoff_date AS check_out FROM bookings WHERE car_id = ? AND status NOT IN ('cancelled','rejected')", [$c['id']]);
$bookings = $_rawBookings;
$blocks   = rows("SELECT start_date, end_date FROM date_blocks WHERE car_id = ?", [$c['id']]);

$title = $c['name'];
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/site-header.php';
$_lp = currentLang() !== 'it' ? '?lang=' . urlencode(currentLang()) : '';
?>
<div class="container-wide pt-8 pb-4">
  <a href="/flotta.php<?= $_lp ?>" class="text-sm text-ink-500 hover:text-brand-600 inline-flex items-center gap-1"><i data-lucide="chevron-left" class="size-[14px]"></i> <?= e(t('car.back')) ?></a>
  <div class="mt-4">
    <div class="flex flex-wrap gap-2 mb-3">
      <span class="badge-brand"><?= e(t('cat.' . $c['category'])) ?></span>
      <span class="badge-soft"><i data-lucide="cog" class="size-[12px]"></i> <?= e(t('car.transmission.' . $c['transmission'])) ?></span>
      <span class="badge-soft"><i data-lucide="fuel" class="size-[12px]"></i> <?= e(t('car.fuel.' . $c['fuel'])) ?></span>
      <?php if ($c['year']): ?><span class="badge-soft"><?= (int)$c['year'] ?></span><?php endif; ?>
    </div>
    <h1 class="font-serif text-4xl md:text-6xl font-semibold tracking-tight max-w-3xl text-balance"><?= e($c['name']) ?></h1>
    <?php if ($c['brand']): ?><div class="text-ink-500 mt-2"><?= e($c['brand']) ?> · <?= e($c['model']) ?></div><?php endif; ?>
  </div>
</div>

<div class="container-wide pb-4">
  <div class="rounded-3xl overflow-hidden h-[300px] sm:h-[440px] bg-gradient-to-br from-ink-100 to-ink-200 dark:from-ink-900 dark:to-ink-800 flex items-center justify-center">
    <?php if ($c['cover_image'] && file_exists(__DIR__ . $c['cover_image'])): ?>
      <img src="<?= e($c['cover_image']) ?>" alt="<?= e($c['name']) ?>" class="h-full w-full object-cover">
    <?php else: ?>
      <i data-lucide="car-front" class="size-[180px] text-ink-300 dark:text-ink-700"></i>
    <?php endif; ?>
  </div>
</div>

<div class="container-wide pb-20">
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
    <div class="lg:col-span-2 space-y-8">
      <!-- AVAILABILITY CALENDAR -->
      <div>
        <h2 class="font-serif text-3xl font-semibold tracking-tight mb-1"><?= e(t('car.cal.title')) ?></h2>
        <p class="text-ink-400 text-sm mb-4"><?= e(t('car.cal.subtitle')) ?></p>
        <?php require __DIR__ . '/partials/calendar-public.php'; ?>
      </div>

      <!-- SPECS STRIP -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <?php foreach ([
          ['users',     t('car.spec.seats'),    (int)$c['seats']],
          ['door-open', t('car.spec.doors'),    (int)$c['doors']],
          ['briefcase', t('car.spec.luggage'),  (int)$c['luggage']],
          ['cog',       t('car.spec.transmission'), t('car.transmission.' . $c['transmission'])],
        ] as $s): ?>
          <div class="card p-4">
            <div class="text-ink-500 text-xs flex items-center gap-1.5 uppercase tracking-wider"><i data-lucide="<?= $s[0] ?>" class="size-[14px]"></i> <?= e($s[1]) ?></div>
            <div class="font-display font-bold text-2xl mt-1"><?= e((string)$s[2]) ?></div>
          </div>
        <?php endforeach; ?>
      </div>

      <div>
        <h2 class="font-serif text-3xl font-semibold tracking-tight mb-3"><?= e(t('car.description')) ?></h2>
        <p class="text-ink-700 dark:text-ink-300 whitespace-pre-line leading-relaxed"><?= e($c['description']) ?></p>
      </div>

      <?php if ($features): ?>
        <div>
          <h2 class="font-serif text-3xl font-semibold tracking-tight mb-4"><?= e(t('car.included')) ?></h2>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <?php foreach ($features as $f): ?>
              <div class="flex items-center gap-3 p-3 rounded-xl bg-ink-50/60 dark:bg-ink-900/40 border border-ink-100/60 dark:border-ink-800/60">
                <span class="h-8 w-8 rounded-lg bg-white dark:bg-ink-900 border border-ink-100 dark:border-ink-800/80 flex items-center justify-center text-brand-500"><i data-lucide="check" class="size-[14px]"></i></span>
                <span class="text-sm font-medium"><?= e(tAmenity($f)) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="card p-6">
        <h2 class="font-serif text-2xl font-semibold tracking-tight mb-4"><?= e(t('car.rates')) ?></h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
          <?php
            $rates = [
              ['daily',    $c['daily_price']],
              ['weekly',   $c['weekly_price']],
              ['biweekly', $c['biweekly_price']],
              ['monthly',  $c['monthly_price']],
            ];
            foreach ($rates as $r): if (!$r[1]) continue; ?>
              <div class="flex items-center justify-between p-3 rounded-xl bg-ink-50/60 dark:bg-ink-900/40">
                <span class="text-ink-500"><?= e(t('car.rate.' . $r[0])) ?></span>
                <span class="font-display font-bold tabular-nums"><?= fmtMoney((float)$r[1]) ?></span>
              </div>
          <?php endforeach; ?>
        </div>
        <?php if ($c['security_deposit']): ?><div class="text-xs text-ink-500 mt-4"><?= e(t('car.deposit', ['p' => fmtMoney((float)$c['security_deposit'])])) ?></div><?php endif; ?>
        <?php if ($c['license_required']): ?><div class="text-xs text-ink-500 mt-1"><?= e(t('car.license')) ?></div><?php endif; ?>
        <?php if ($c['min_age']): ?><div class="text-xs text-ink-500 mt-1"><?= e(t('car.minage', ['n' => (int)$c['min_age']])) ?></div><?php endif; ?>
      </div>
    </div>

    <!-- BOOKING CARD -->
    <aside id="book" class="lg:sticky lg:top-24 self-start scroll-mt-24" x-data="bookingForm()" x-init="initFromStorage()" @as-cal-pick.window="syncFromCal($event.detail)">
      <div class="card-elev p-6 shadow-card">
        <div class="flex items-baseline gap-1 mb-4">
          <span class="font-display text-3xl font-bold text-brand-600"><?= fmtMoney((float)$c['daily_price']) ?></span>
          <span class="text-sm text-ink-500"><?= e(t('common.per_day')) ?></span>
        </div>

        <template x-if="done">
          <div class="text-center py-6 animate-fade-in">
            <div class="h-16 w-16 mx-auto rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-3"><i data-lucide="check" class="size-[32px]"></i></div>
            <div class="font-display text-xl font-bold"><?= e(t('book.req_sent')) ?></div>
            <p class="text-sm text-ink-500 mt-1"><?= e(t('book.code')) ?></p>
            <p class="font-mono text-base mt-1" x-text="done"></p>
            <p class="text-xs text-ink-500 mt-3"><?= e(t('book.contact_soon')) ?></p>
          </div>
        </template>

        <form x-show="!done" @submit.prevent="submit" class="space-y-2.5">
          <div class="grid grid-cols-2 gap-0 rounded-2xl border border-ink-100 dark:border-ink-700/60 bg-white dark:bg-ink-900/40 shadow-sm overflow-hidden divide-x divide-ink-100 dark:divide-ink-700/60 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/15 transition-all">
            <label class="block px-3.5 py-2.5 cursor-pointer hover:bg-ink-50/60 dark:hover:bg-ink-900/60">
              <span class="text-[10px] font-semibold uppercase tracking-wider text-ink-400"><?= e(t('book.pickup_date')) ?></span>
              <input type="date" required class="w-full bg-transparent outline-none text-[15px] font-medium text-ink-800 dark:text-ink-100 mt-0.5" x-model="from" @change="quote()">
            </label>
            <label class="block px-3.5 py-2.5 cursor-pointer hover:bg-ink-50/60 dark:hover:bg-ink-900/60">
              <span class="text-[10px] font-semibold uppercase tracking-wider text-ink-400"><?= e(t('book.dropoff_date')) ?></span>
              <input type="date" required class="w-full bg-transparent outline-none text-[15px] font-medium text-ink-800 dark:text-ink-100 mt-0.5" x-model="to" @change="quote()">
            </label>
          </div>

          <label class="block px-3.5 py-2.5 rounded-2xl border border-ink-100 dark:border-ink-700/60 bg-white dark:bg-ink-900/40 shadow-sm focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/15 transition-all">
            <span class="text-[10px] font-semibold uppercase tracking-wider text-ink-400"><?= e(t('book.pickup_loc')) ?></span>
            <input class="w-full bg-transparent outline-none text-[15px] font-medium text-ink-800 dark:text-ink-100 placeholder:text-ink-300 placeholder:font-normal mt-0.5" placeholder="<?= e(t('book.pickup_loc_ph')) ?>" x-model="pickup">
          </label>

          <label class="block px-3.5 py-2.5 rounded-2xl border border-ink-100 dark:border-ink-700/60 bg-white dark:bg-ink-900/40 shadow-sm focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/15 transition-all">
            <span class="text-[10px] font-semibold uppercase tracking-wider text-ink-400"><?= e(t('book.coupon')) ?></span>
            <input class="w-full bg-transparent outline-none text-[15px] font-medium text-ink-800 dark:text-ink-100 placeholder:text-ink-300 placeholder:font-normal mt-0.5" placeholder="<?= e(t('book.coupon_ph')) ?>" x-model="coupon" @input.debounce.500="quote()">
          </label>

          <template x-if="q && q.days > 0">
            <div class="rounded-xl bg-ink-50 dark:bg-ink-900/40 p-4 text-sm space-y-2">
              <div class="flex justify-between"><span class="text-ink-500"><span x-text="q.days"></span> <?= e(t('book.summary.days')) ?></span><span class="font-medium tabular-nums" x-text="fmt(q.subtotal)"></span></div>
              <template x-if="q.discount > 0">
                <div class="flex justify-between text-emerald-600"><span x-text="q.discountLabel || '<?= e(t('book.summary.discount')) ?>'"></span><span class="tabular-nums" x-text="'-' + fmt(q.discount)"></span></div>
              </template>
              <div class="flex justify-between font-display font-bold text-base pt-2 mt-1 border-t border-ink-200 dark:border-ink-700/80"><span><?= e(t('book.summary.total')) ?></span><span class="tabular-nums" x-text="fmt(q.total)"></span></div>
            </div>
          </template>

          <div class="space-y-2 pt-2">
            <input required placeholder="<?= e(t('book.fullname')) ?>" class="input" x-model="name">
            <input type="email" required placeholder="<?= e(t('book.email')) ?>" class="input" x-model="email">

            <!-- Phone + dial code prefix -->
            <div class="relative flex w-full max-w-full" @click.outside="dialOpen=false">
              <button type="button" @click="dialOpen=!dialOpen; if(dialOpen){$nextTick(()=>$refs.dialSearch.focus())}"
                class="rounded-xl rounded-r-none has-border border-r-0 border-ink-200 dark:border-ink-700/80 bg-white dark:bg-ink-900/80 px-2.5 py-2.5 flex items-center gap-1 cursor-pointer shrink-0 w-[96px] hover:bg-ink-50 transition">
                <span class="text-base leading-none" x-text="flagOf(dial)"></span>
                <span class="text-sm font-medium tabular-nums" x-text="'+' + dialCodeOf(dial)"></span>
                <i data-lucide="chevron-down" class="size-[12px] text-ink-400 shrink-0 transition" :class="dialOpen && 'rotate-180'"></i>
              </button>
              <div class="flex-1 min-w-0">
                <input required type="tel" placeholder="<?= e(t('book.phone')) ?>"
                  class="w-full rounded-xl rounded-l-none has-border border-ink-200 dark:border-ink-700/80 bg-white dark:bg-ink-900/80 px-3.5 py-2.5 text-sm placeholder:text-ink-400 focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition-all"
                  x-model="phone" inputmode="tel">
              </div>
              <div x-show="dialOpen" x-cloak x-transition.opacity.duration.150ms
                   class="absolute z-50 top-full mt-1 left-0 right-0 sm:w-[280px] sm:right-auto bg-white dark:bg-ink-900 has-border border-ink-200 dark:border-ink-700 rounded-2xl shadow-pop overflow-hidden"
                   style="display:none">
                <div class="p-2 border-b border-ink-100">
                  <div class="relative">
                    <i data-lucide="search" class="size-[14px] absolute left-2.5 top-1/2 -translate-y-1/2 text-ink-400"></i>
                    <input x-ref="dialSearch" x-model="dialSearch" type="text" placeholder="<?= e(t('form.country_search')) ?>"
                           class="w-full pl-8 pr-3 py-2 text-sm bg-ink-50 dark:bg-ink-800 rounded-xl outline-none focus:ring-2 focus:ring-brand-500/30">
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

            <input placeholder="<?= e(t('book.notes_ph')) ?>" class="input" x-model="notes">
          </div>

          <div x-show="err" x-text="err" class="text-sm text-red-600 p-2 rounded-lg bg-red-50"></div>

          <button :disabled="busy" class="btn-primary w-full h-12 text-base">
            <span x-show="!busy"><?= e(t('book.submit')) ?></span>
            <span x-show="busy" class="flex items-center gap-2"><i data-lucide="loader-2" class="size-[18px] animate-spin"></i> <?= e(t('book.sending')) ?></span>
          </button>
          <p class="text-[11px] text-ink-500 text-center"><?= e(t('book.no_charge')) ?></p>
        </form>
      </div>
    </aside>
  </div>
</div>

<script>
function bookingForm() {
  return {
    carId: <?= json_encode($c['id']) ?>,
    from: '', to: '', pickup: '', coupon: '',
    name: '', email: '', phone: '', notes: '',
    dial: 'IT', dialOpen: false, dialSearch: '',
    phoneCountries: <?= json_encode(phoneCountryList(currentLang())) ?>,
    q: null, busy: false, done: null, err: '',
    initFromStorage() {
      try {
        const saved = JSON.parse(sessionStorage.getItem('as_book_' + this.carId) || '{}');
        if (saved.from) this.from = saved.from;
        if (saved.to)   this.to   = saved.to;
      } catch(e) {}
      if (this.from && this.to) this.quote();
    },
    syncFromCal(d) {
      this.from = d && d.from || '';
      this.to   = d && d.to   || '';
      if (this.from && this.to) this.quote(); else this.q = null;
    },
    fmt(n) { return new Intl.NumberFormat('it-IT', { style:'currency', currency:'EUR' }).format(n || 0); },
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
    async quote() {
      if (!this.from || !this.to) return;
      try {
        const r = await fetch('/api/quote.php', { method: 'POST', headers: { 'content-type': 'application/json' },
          body: JSON.stringify({ car_id: this.carId, from: this.from, to: this.to, coupon: this.coupon }) });
        this.q = await r.json();
      } catch (e) {}
    },
    async submit() {
      this.busy = true; this.err = '';
      const dialCode = this.dialCodeOf(this.dial) || '39';
      const phoneFull = '+' + dialCode + ' ' + (this.phone || '').replace(/^\+?\d{1,4}\s*/, '');
      try {
        const r = await fetch('/api/booking.php', { method: 'POST', headers: { 'content-type': 'application/json' },
          body: JSON.stringify({ car_id: this.carId, from: this.from, to: this.to, pickup: this.pickup, coupon: this.coupon, name: this.name, email: this.email, phone: phoneFull, notes: this.notes }) });
        const d = await r.json();
        if (!r.ok) throw new Error(d.error || 'Errore');
        this.done = d.code;
      } catch (e) { this.err = e.message; } finally { this.busy = false; }
    }
  };
}
</script>

<?php require __DIR__ . '/partials/site-footer.php';
