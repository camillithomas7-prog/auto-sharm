<?php
// Calendario di disponibilità (vanilla JS, no Alpine — più robusto).
// Si aspetta:
//   $c       — record cars
//   $bookings — array con keys: check_in, check_out
//   $blocks   — array con keys: start_date, end_date
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
$_carIdJs = htmlspecialchars(json_encode($c['id']), ENT_QUOTES);
?>
<div id="calendar" class="card-elev rounded-3xl p-5 sm:p-6 scroll-mt-24" data-as-calendar data-car-id='<?= $_carIdJs ?>'>
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
        <button type="button" data-day="<?= $d ?>" class="as-cell aspect-square min-h-[44px] flex items-center justify-center text-sm font-semibold rounded-lg transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-500 hover:ring-2 hover:ring-emerald-300 hover:scale-[1.05] <?= $base ?>">
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
  <div data-as-cal-summary class="mt-4 p-3 rounded-xl bg-brand-500/10 border border-brand-500/30 text-sm hidden" >
    <div class="flex items-center justify-between gap-2">
      <div class="flex items-center gap-2 text-brand-200">
        <i data-lucide="calendar-check" class="size-[16px]"></i>
        <span data-as-cal-msg></span>
      </div>
      <button type="button" data-as-cal-reset class="text-xs font-semibold text-brand-200 hover:text-white hover:underline">Reset</button>
    </div>
  </div>
</div>
<script>
(function(){
  var T = {
    pickup:    <?= json_encode(t('car.cal.pickup')) ?>,
    chooseOut: <?= json_encode(t('car.cal.choose_drop')) ?>,
    days:      <?= json_encode(t('car.cal.days_count')) ?>,
  };
  function fmtIt(d){ if(!d) return ''; var p=d.split('-'); return p[2]+'/'+p[1]+'/'+p[0]; }

  document.addEventListener('DOMContentLoaded', function(){
    var root = document.querySelector('[data-as-calendar]');
    if (!root) return;
    var carId = root.getAttribute('data-car-id') || '';
    var key = 'as_book_' + carId;
    var state = { from: '', to: '' };
    try {
      var saved = JSON.parse(sessionStorage.getItem(key) || '{}');
      if (saved.from) state.from = saved.from;
      if (saved.to)   state.to   = saved.to;
    } catch(e) {}

    var summary = root.querySelector('[data-as-cal-summary]');
    var summaryMsg = root.querySelector('[data-as-cal-msg]');
    var resetBtn = root.querySelector('[data-as-cal-reset]');
    var cells = Array.prototype.slice.call(root.querySelectorAll('button.as-cell'));

    function persist(){ try { sessionStorage.setItem(key, JSON.stringify(state)); } catch(e){} }

    function nights(){
      if (!state.from || !state.to) return 0;
      return Math.round((new Date(state.to) - new Date(state.from)) / 86400000);
    }

    function render(){
      cells.forEach(function(btn){
        var d = btn.getAttribute('data-day');
        // reset overrides
        btn.style.background = '';
        btn.style.color = '';
        btn.style.boxShadow = '';
        btn.classList.remove('cell-selected','cell-range');
        if (d === state.from || d === state.to) {
          btn.classList.add('cell-selected');
        } else if (state.from && state.to && d > state.from && d < state.to) {
          btn.classList.add('cell-range');
        }
      });
      if (state.from || state.to) {
        summary.classList.remove('hidden');
        if (state.from && !state.to) {
          summaryMsg.innerHTML = T.pickup + ': <strong>' + fmtIt(state.from) + '</strong> · ' + T.chooseOut;
        } else {
          summaryMsg.innerHTML = '<strong>' + fmtIt(state.from) + '</strong> → <strong>' + fmtIt(state.to) + '</strong> · ' + nights() + ' ' + T.days;
        }
      } else {
        summary.classList.add('hidden');
      }
      // refresh icons
      if (window.lucide) try { window.lucide.createIcons(); } catch(e){}
    }

    function broadcast(){
      window.dispatchEvent(new CustomEvent('as-cal-pick', { detail: { from: state.from, to: state.to } }));
    }

    function pick(d){
      if (!state.from || (state.from && state.to)) {
        state.from = d; state.to = '';
      } else if (d <= state.from) {
        state.from = d; state.to = '';
      } else {
        state.to = d;
      }
      persist();
      render();
      broadcast();
      if (state.from && state.to) {
        setTimeout(function(){
          var f = document.getElementById('book');
          if (f) f.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 150);
      }
    }

    cells.forEach(function(btn){
      btn.addEventListener('click', function(ev){ ev.preventDefault(); pick(btn.getAttribute('data-day')); });
    });
    if (resetBtn) resetBtn.addEventListener('click', function(){ state.from=''; state.to=''; persist(); render(); broadcast(); });

    render();
    if (state.from || state.to) broadcast();
  });
})();
</script>
<style>
  /* Selezione e range — usano !important per battere le classi base statiche del button */
  [data-as-calendar] button.as-cell.cell-selected {
    background: rgb(220 28 28) !important;
    color: #fff !important;
    box-shadow: 0 0 24px -2px rgba(255,30,30,.95), 0 0 0 2px rgb(255 80 80) !important;
  }
  [data-as-calendar] button.as-cell.cell-range {
    background: rgba(220 28 28 / .42) !important;
    color: #fff !important;
    box-shadow: 0 0 0 1px rgba(255 80 80 / .55) !important;
  }
</style>
