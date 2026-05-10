<?php
// Calendario di disponibilità per noleggio auto.
// Si aspetta:
//   $c       — record cars
//   $bookings (array)  con keys: check_in, check_out  — già rimappato in auto.php
//   $blocks   (array)  con keys: start_date, end_date
$cal_ref = !empty($_GET['m']) ? strtotime($_GET['m'] . '-01') : strtotime(date('Y-m-01'));
$cal_year = (int)date('Y', $cal_ref); $cal_month = (int)date('n', $cal_ref);
$first = mktime(0,0,0,$cal_month,1,$cal_year);
$first_dow = ((int)date('N', $first) - 1);
$start_grid = $first - $first_dow * 86400;
$days = [];
for ($i = 0; $i < 42; $i++) $days[] = $start_grid + $i * 86400;

if (!function_exists('dayCellStatusCar')) {
  function dayCellStatusCar($ts, $bookings, $blocks) {
    $d = date('Y-m-d', $ts);
    foreach ($blocks as $b) if ($d >= $b['start_date'] && $d < $b['end_date']) return 'blocked';
    foreach ($bookings as $b) {
      if ($d === $b['check_in'])  return 'check_in';
      if ($d === $b['check_out']) return 'check_out';
      if ($d > $b['check_in'] && $d < $b['check_out']) return 'booked';
    }
    return 'free';
  }
}
$prev_m = date('Y-m', strtotime('-1 month', $cal_ref));
$next_m = date('Y-m', strtotime('+1 month', $cal_ref));
$today = date('Y-m-d');
$dowKeys = ['mon','tue','wed','thu','fri','sat','sun'];
$_lpCal = currentLang() !== 'it' ? '&lang=' . urlencode(currentLang()) : '';
?>
<div id="calendar" class="card-elev rounded-3xl p-5 sm:p-6 scroll-mt-24" x-data="asCalPicker(<?= json_encode($c['id']) ?>)" x-init="init()">
  <div class="flex items-center justify-between mb-4">
    <a href="?slug=<?= e($c['slug']) ?>&m=<?= $prev_m ?><?= $_lpCal ?>#calendar" class="h-10 w-10 rounded-xl border border-white/[.08] bg-white/[.03] flex items-center justify-center hover:bg-white/[.07] hover:border-brand-500/40 transition" aria-label="prev"><i data-lucide="chevron-left" class="size-[16px]"></i></a>
    <div class="font-display font-bold text-lg sm:text-xl text-white"><?= e(tMonth($cal_month)) ?> <?= $cal_year ?></div>
    <a href="?slug=<?= e($c['slug']) ?>&m=<?= $next_m ?><?= $_lpCal ?>#calendar" class="h-10 w-10 rounded-xl border border-white/[.08] bg-white/[.03] flex items-center justify-center hover:bg-white/[.07] hover:border-brand-500/40 transition" aria-label="next"><i data-lucide="chevron-right" class="size-[16px]"></i></a>
  </div>
  <div class="grid grid-cols-7 gap-1 text-[11px] font-semibold uppercase tracking-wider text-ink-400 mb-2">
    <?php foreach ($dowKeys as $k): ?><div class="text-center"><?= e(t('common.dow.' . $k)) ?></div><?php endforeach; ?>
  </div>
  <div class="grid grid-cols-7 gap-1 sm:gap-1.5">
    <?php foreach ($days as $ts):
      $in = (int)date('n',$ts) === $cal_month;
      $st = dayCellStatusCar($ts, $bookings, $blocks);
      $d = date('Y-m-d', $ts);
      $past = $d < $today;
      $clickable = $in && !$past && ($st === 'free' || $st === 'check_out');
      $base = $st === 'booked'    ? 'bg-red-500/30 text-red-200 ring-1 ring-red-500/40 line-through' :
             ($st === 'check_in'  ? 'bg-amber-400/30 text-amber-100 ring-1 ring-amber-400/40' :
             ($st === 'check_out' ? 'bg-amber-400/30 text-amber-100 ring-1 ring-amber-400/40' :
             ($st === 'blocked'   ? 'bg-white/[.04] text-ink-500 ring-1 ring-white/[.06]' :
                                    'bg-emerald-500/35 text-emerald-100 ring-1 ring-emerald-400/50')));
      $not_in_cls = 'text-ink-700';
      $past_cls = 'text-ink-700 line-through';
    ?>
      <?php if ($clickable): ?>
        <button type="button" @click="pick('<?= $d ?>')" :class="overlayCls('<?= $d ?>')" class="aspect-square min-h-[44px] flex items-center justify-center text-sm font-semibold rounded-lg transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-500 hover:ring-2 hover:ring-emerald-300 hover:scale-[1.05] <?= $base ?>">
          <?= (int)date('j', $ts) ?>
        </button>
      <?php else: ?>
        <div class="aspect-square min-h-[44px] flex items-center justify-center text-sm rounded-lg <?= !$in ? $not_in_cls : ($past ? $past_cls : $base) ?>">
          <?= (int)date('j', $ts) ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <div class="flex flex-wrap gap-x-5 gap-y-2 text-xs mt-5 text-ink-400">
    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-md bg-emerald-500/40"></span> <?= e(t('car.cal.legend.free')) ?></span>
    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-md bg-red-500/40"></span> <?= e(t('car.cal.legend.busy')) ?></span>
    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-md bg-amber-400/50"></span> <?= e(t('car.cal.legend.pickdrop')) ?></span>
    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-md bg-brand-500"></span> <?= e(t('car.cal.legend.selected')) ?></span>
  </div>
  <div x-show="from || to" class="mt-4 p-3 rounded-xl bg-brand-500/10 border border-brand-500/30 text-sm flex items-center justify-between gap-2 animate-fade-in">
    <div class="flex items-center gap-2 text-brand-200">
      <i data-lucide="calendar-check" class="size-[16px]"></i>
      <span>
        <span x-show="from && !to"><?= e(t('car.cal.pickup')) ?>: <strong x-text="fmtIt(from)"></strong> · <?= e(t('car.cal.choose_drop')) ?></span>
        <span x-show="from && to"><strong x-text="fmtIt(from)"></strong> → <strong x-text="fmtIt(to)"></strong> · <span x-text="days"></span> <?= e(t('car.cal.days_count')) ?></span>
      </span>
    </div>
    <button type="button" @click="reset()" class="text-xs font-semibold text-brand-200 hover:text-white hover:underline">Reset</button>
  </div>
</div>
<script>
function asCalPicker(carId) {
  return {
    carId,
    from: '',
    to: '',
    get days() {
      if (!this.from || !this.to) return 0;
      return Math.round((new Date(this.to) - new Date(this.from)) / 86400000);
    },
    init() {
      try {
        const saved = JSON.parse(sessionStorage.getItem('as_book_' + this.carId) || '{}');
        if (saved.from) this.from = saved.from;
        if (saved.to) this.to = saved.to;
      } catch(e) {}
      this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
      // se torno alla pagina con date già scelte, propaga al booking form
      if (this.from || this.to) {
        window.dispatchEvent(new CustomEvent('as-cal-pick', { detail: { from: this.from, to: this.to } }));
      }
    },
    fmtIt(d) {
      if (!d) return '';
      const [y, m, day] = d.split('-');
      return `${day}/${m}/${y}`;
    },
    overlayCls(d) {
      // ritorna SOLO le classi additive per l'evidenziazione — il colore base è statico nel button
      if (d === this.from || d === this.to) return '!bg-brand-500 !text-white shadow-[0_0_22px_-2px_rgba(255,30,30,.95)] !ring-2 !ring-brand-400';
      if (this.from && this.to && d > this.from && d < this.to) return '!bg-brand-500/40 !text-white !ring-1 !ring-brand-400/60';
      return '';
    },
    pick(d) {
      if (!this.from || (this.from && this.to)) {
        this.from = d; this.to = '';
      } else if (d <= this.from) {
        this.from = d; this.to = '';
      } else {
        this.to = d;
      }
      this.persist();
      window.dispatchEvent(new CustomEvent('as-cal-pick', { detail: { from: this.from, to: this.to } }));
      if (this.from && this.to) {
        setTimeout(() => {
          const f = document.getElementById('book');
          if (f) f.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 150);
      }
    },
    reset() {
      this.from = ''; this.to = '';
      this.persist();
      window.dispatchEvent(new CustomEvent('as-cal-pick', { detail: { from: '', to: '' } }));
    },
    persist() {
      try { sessionStorage.setItem('as_book_' + this.carId, JSON.stringify({ from: this.from, to: this.to })); } catch(e) {}
    }
  };
}
</script>
