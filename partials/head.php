<?php
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/utils.php';
require_once __DIR__ . '/../lib/i18n.php';
$siteName = cfg('site.name');
$pageTitle = $title ?? $siteName;
$lang = currentLang();
$_isAdminCtx = strpos($_SERVER['PHP_SELF'] ?? '', '/admin/') !== false;
?><!doctype html>
<html lang="<?= e($lang) ?>" class="<?= $_isAdminCtx ? '' : 'dark' ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta http-equiv="cache-control" content="no-cache, must-revalidate">
<title><?= e($pageTitle) ?> · <?= e($siteName) ?></title>
<meta name="description" content="<?= e($metaDesc ?? t('home.hero.sub')) ?>">
<meta name="theme-color" content="#0a0306">
<link rel="icon" type="image/png" href="/assets/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800;900&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700;9..144,800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com?plugins=forms,aspect-ratio"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>
tailwind.config = {
  darkMode: 'class',
  theme: { extend: {
    colors: {
      brand: {
        50:  '#fff1f1',
        100: '#ffdcdc',
        200: '#ffbdbd',
        300: '#ff8e8e',
        400: '#ff5757',
        500: '#dc1c1c',
        600: '#bb1515',
        700: '#9b1212',
        800: '#7d1212',
        900: '#641111',
        950: '#370606',
      },
      neon: {
        400: '#ff3838',
        500: '#ff1a1a',
        600: '#ff0000',
      },
      ink: {
        50: '#f7f7f8', 100: '#eeeef0', 200: '#d8d8dd', 300: '#b6b6c0',
        400: '#8b8b9a', 500: '#5e5e6e', 600: '#3f3f4d', 700: '#2c2c39',
        800: '#1a1a23', 900: '#0e0e15', 950: '#06060b',
      },
    },
    fontFamily: {
      sans: ['Inter','system-ui','sans-serif'],
      display: ['Plus Jakarta Sans','Inter','system-ui','sans-serif'],
      serif: ['Fraunces','Georgia','serif'],
      mono: ['JetBrains Mono','ui-monospace','monospace'],
    },
    boxShadow: {
      soft: '0 1px 3px rgb(0 0 0 / 0.04), 0 1px 2px rgb(0 0 0 / 0.06)',
      card: '0 4px 24px -8px rgb(0 0 0 / 0.10), 0 2px 6px -2px rgb(0 0 0 / 0.06)',
      pop:  '0 20px 50px -20px rgb(0 0 0 / 0.55), 0 6px 18px -8px rgb(0 0 0 / 0.30)',
      glow: '0 0 0 6px rgb(220 28 28 / 0.18)',
      neon: '0 0 0 1px rgba(255,30,30,.55), 0 0 18px 0 rgba(255,30,30,.45), 0 0 60px -8px rgba(255,30,30,.6)',
      'neon-lg': '0 0 0 1px rgba(255,30,30,.7), 0 0 28px 0 rgba(255,30,30,.55), 0 0 90px -10px rgba(255,30,30,.7), inset 0 0 22px -8px rgba(255,80,80,.55)',
      'neon-soft': '0 0 26px -6px rgba(255,30,30,.45), 0 0 70px -16px rgba(255,30,30,.55)',
    },
    keyframes: {
      'fade-in':   { '0%': { opacity: 0 }, '100%': { opacity: 1 } },
      'slide-up':  { '0%': { opacity: 0, transform: 'translateY(12px)' }, '100%': { opacity: 1, transform: 'translateY(0)' } },
      'pulse-neon':{ '0%,100%': { boxShadow:'0 0 0 1px rgba(255,30,30,.5), 0 0 18px 0 rgba(255,30,30,.4), 0 0 60px -8px rgba(255,30,30,.5)', filter:'brightness(1)' }, '50%':{ boxShadow:'0 0 0 1px rgba(255,30,30,.85), 0 0 32px 0 rgba(255,30,30,.7), 0 0 110px -8px rgba(255,30,30,.85)', filter:'brightness(1.15)' } },
      'orb-drift-a': { '0%,100%': { transform:'translate(-8%,-6%) scale(1)' }, '33%':{ transform:'translate(6%,8%) scale(1.12)' }, '66%':{ transform:'translate(-4%,12%) scale(.95)' } },
      'orb-drift-b': { '0%,100%': { transform:'translate(8%,4%) scale(.92)' }, '50%':{ transform:'translate(-10%,-12%) scale(1.18)' } },
      'spin-slow': { from:{ transform:'rotate(0deg)' }, to:{ transform:'rotate(360deg)' } },
      'scan-x':    { '0%':{ transform:'translateX(-110%)' }, '100%':{ transform:'translateX(220%)' } },
      'flicker':   { '0%,18%,22%,25%,53%,57%,100%':{ opacity:1, filter:'brightness(1.05)' }, '20%,24%,55%':{ opacity:.55, filter:'brightness(.7)' } },
      'breathe':   { '0%,100%':{ opacity:.55 }, '50%':{ opacity:1 } },
      'rise':      { '0%':{ opacity:0, transform:'translateY(20px)' }, '100%':{ opacity:1, transform:'translateY(0)' } },
    },
    animation: {
      'fade-in':  'fade-in .35s ease-out both',
      'slide-up': 'slide-up .5s cubic-bezier(.16,1,.3,1) both',
      'pulse-neon':'pulse-neon 3.6s ease-in-out infinite',
      'orb-a': 'orb-drift-a 22s ease-in-out infinite',
      'orb-b': 'orb-drift-b 28s ease-in-out infinite',
      'spin-slow': 'spin-slow 18s linear infinite',
      'scan-x':  'scan-x 1.6s ease-in-out',
      'flicker': 'flicker 4.2s linear infinite',
      'breathe': 'breathe 5s ease-in-out infinite',
      'rise':    'rise .7s cubic-bezier(.16,1,.3,1) both',
    },
  } }
};
try { if (localStorage.getItem('as-theme') === 'light' && <?= $_isAdminCtx ? 'true' : 'false' ?>) document.documentElement.classList.remove('dark'); } catch(e){}
</script>
<style type="text/tailwindcss">
@layer base {
  html { -webkit-font-smoothing: antialiased; scroll-behavior: smooth; overflow-x: clip; }
  body { @apply bg-white text-ink-900 dark:text-ink-50; overflow-x: clip; width: 100%; }
  html.dark body {
    background-color: #07030a;
    background-image:
      radial-gradient(1100px 700px at 12% -10%, rgba(255,30,30,.18), transparent 60%),
      radial-gradient(900px 600px at 92% 8%, rgba(120,5,30,.28), transparent 55%),
      radial-gradient(1300px 900px at 50% 110%, rgba(40,5,15,.55), transparent 60%),
      linear-gradient(180deg, #06030a 0%, #050207 100%);
    background-attachment: fixed;
  }
  /* Sottile grana + griglia */
  html.dark body::before {
    content:''; position: fixed; inset:0; pointer-events:none; z-index: 0;
    background-image:
      linear-gradient(rgba(255,255,255,.018) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,.018) 1px, transparent 1px);
    background-size: 56px 56px, 56px 56px;
    mask-image: radial-gradient(ellipse 90% 60% at 50% 30%, #000 30%, transparent 80%);
  }
  html.dark body::after {
    content:''; position: fixed; inset:0; pointer-events:none; z-index: 0; opacity:.06; mix-blend-mode: overlay;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='200' height='200'><filter id='n'><feTurbulence baseFrequency='0.9' numOctaves='2'/></filter><rect width='100%' height='100%' filter='url(%23n)' opacity='1'/></svg>");
  }
  ::selection { @apply bg-brand-500/40 text-white; }
  [x-cloak] { display: none !important; }
  img, video { max-width: 100%; height: auto; }
  input, textarea, select, button {
    -webkit-appearance: none; -moz-appearance: none; appearance: none;
    background-clip: padding-box; border: 0;
  }
  input[type="checkbox"], input[type="radio"] { -webkit-appearance: auto; appearance: auto; }
  input[type="date"]:not(:focus):invalid::-webkit-datetime-edit { color: rgb(148 163 184); font-weight: 400; }
  input[type="date"]::-webkit-calendar-picker-indicator {
    opacity: .55; cursor: pointer; transition: opacity .2s;
    filter: invert(45%) sepia(8%) saturate(380%) hue-rotate(176deg) brightness(95%) contrast(85%);
  }
  html.dark input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(75%) brightness(1.4); }
  input[type="date"]:hover::-webkit-calendar-picker-indicator { opacity: 1; }
  input[type="date"]:focus { outline: none; }
  .input, .has-border { border-width: 1px; }

  /* Section-level positioning sopra i layer fissi */
  main, header, footer, section { position: relative; z-index: 1; }
}
@layer components {
  .container-wide  { @apply max-w-[1240px] mx-auto px-5 sm:px-6 lg:px-8; }
  .container-narrow{ @apply max-w-[920px] mx-auto px-5 sm:px-6; }

  /* ===== CARDS ===== */
  .card { @apply bg-white border border-ink-100 rounded-2xl shadow-soft dark:bg-white/[.025] dark:border-white/[.07] dark:backdrop-blur-xl; }
  .card-elev { @apply bg-white border border-ink-100 rounded-2xl shadow-card dark:bg-white/[.035] dark:border-white/[.08] dark:backdrop-blur-xl; }
  html.dark .card-elev { box-shadow: 0 24px 60px -30px rgba(0,0,0,.9), inset 0 1px 0 rgba(255,255,255,.06); }
  .card-hover { @apply transition-all duration-300 hover:-translate-y-0.5 hover:shadow-pop; }

  /* Card "neon" — bordo conic gradient che si accende al hover */
  .card-neon { position: relative; isolation: isolate; }
  .card-neon::before{
    content:''; position:absolute; inset:-1px; border-radius: inherit; padding:1px; z-index:-1;
    background: conic-gradient(from var(--ang,0deg), rgba(255,30,30,0) 0%, rgba(255,30,30,.0) 35%, rgba(255,80,80,.85) 50%, rgba(255,30,30,0) 65%, rgba(255,30,30,0) 100%);
    -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    -webkit-mask-composite: xor; mask-composite: exclude;
    opacity: 0; transition: opacity .4s ease;
  }
  .card-neon:hover::before { opacity: 1; animation: spin-slow 4s linear infinite; }
  .card-neon::after{
    content:''; position:absolute; inset:0; border-radius: inherit; pointer-events:none; opacity:0; transition:opacity .35s;
    box-shadow: 0 0 0 1px rgba(255,30,30,.4), 0 0 30px -4px rgba(255,30,30,.55), 0 30px 80px -30px rgba(255,30,30,.45);
  }
  .card-neon:hover::after{ opacity:1; }

  /* ===== BUTTONS ===== */
  .btn { @apply inline-flex items-center justify-center gap-2 rounded-xl font-medium transition-all duration-200 active:scale-[0.97] disabled:opacity-50 disabled:pointer-events-none; }
  .btn-primary {
    @apply btn relative overflow-hidden bg-brand-500 text-white px-5 py-2.5 hover:bg-brand-600;
    box-shadow: 0 8px 24px -8px rgba(220,28,28,.65), inset 0 1px 0 rgba(255,255,255,.18), inset 0 -2px 0 rgba(0,0,0,.25), 0 0 0 1px rgba(255,80,80,.35);
    background-image: linear-gradient(180deg, #ff3838 0%, #dc1c1c 50%, #9b1212 100%);
    text-shadow: 0 1px 0 rgba(0,0,0,.35), 0 0 14px rgba(255,90,90,.55);
  }
  .btn-primary:hover { box-shadow: 0 10px 28px -6px rgba(255,30,30,.85), inset 0 1px 0 rgba(255,255,255,.25), inset 0 -2px 0 rgba(0,0,0,.25), 0 0 0 1px rgba(255,120,120,.55), 0 0 60px -10px rgba(255,30,30,.95); }
  .btn-primary::before{
    content:''; position:absolute; top:0; bottom:0; width:55%; left:0; pointer-events:none;
    background: linear-gradient(110deg, transparent 0%, rgba(255,255,255,.45) 50%, transparent 100%);
    transform: translateX(-110%); opacity:.7;
  }
  .btn-primary:hover::before{ animation: scan-x 1.1s cubic-bezier(.16,1,.3,1); }
  .btn-secondary { @apply btn bg-ink-900 text-white px-5 py-2.5 hover:bg-ink-800 dark:bg-white dark:text-ink-900; }
  .btn-outline   {
    @apply btn px-5 py-2.5;
    border: 1px solid rgba(255,255,255,.14);
    background: rgba(255,255,255,.04);
    color: #f1f1f5;
    backdrop-filter: blur(10px);
  }
  html:not(.dark) .btn-outline { border-color: #d8d8dd; color: #1a1a23; background: white; }
  .btn-outline:hover { border-color: rgba(255,80,80,.6); color:#fff; box-shadow: 0 0 0 1px rgba(255,30,30,.35), 0 0 24px -6px rgba(255,30,30,.55); background: rgba(255,30,30,.06); }
  html:not(.dark) .btn-outline:hover { background: #f7f7f8; color:#1a1a23; box-shadow: none; }
  .btn-ghost     { @apply btn text-ink-700 dark:text-ink-200 px-3 py-2 hover:bg-ink-100 dark:hover:bg-white/5; }
  .btn-danger    { @apply btn bg-red-600 text-white px-5 py-2.5 hover:bg-red-700; }

  /* ===== FORMS ===== */
  .input {
    @apply w-full rounded-xl px-3.5 py-2.5 text-sm transition-all;
    border: 1px solid #d8d8dd;
    background: #ffffff;
    color: #1a1a23;
  }
  .input::placeholder { color: #b6b6c0; }
  .input:focus { outline:none; border-color:#dc1c1c; box-shadow: 0 0 0 4px rgba(220,28,28,.18); }
  html.dark .input {
    background: rgba(255,255,255,.03);
    border-color: rgba(255,255,255,.10);
    color: #f1f1f5;
    backdrop-filter: blur(8px);
  }
  html.dark .input::placeholder { color: rgba(255,255,255,.30); }
  html.dark .input:focus { border-color: rgba(255,80,80,.7); box-shadow: 0 0 0 4px rgba(255,30,30,.18), 0 0 30px -8px rgba(255,30,30,.55); background: rgba(255,30,30,.04); }
  .label { @apply text-xs font-semibold uppercase tracking-wider mb-1.5 block; color: rgba(255,255,255,.55); }
  html:not(.dark) .label { color: #5e5e6e; }

  /* ===== BADGES ===== */
  .badge { @apply inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium; }
  .badge-soft    { @apply badge bg-ink-100 text-ink-700 dark:bg-white/5 dark:text-ink-200; }
  .badge-brand   {
    @apply badge text-brand-300;
    border: 1px solid rgba(255,30,30,.35);
    background: linear-gradient(180deg, rgba(255,30,30,.18), rgba(255,30,30,.04));
    box-shadow: inset 0 1px 0 rgba(255,255,255,.1), 0 0 14px -4px rgba(255,30,30,.6);
    color: #ffb4b4;
  }
  html:not(.dark) .badge-brand { background:#ffdcdc; color:#9b1212; border-color: transparent; box-shadow: none; }
  .badge-success { @apply badge bg-emerald-100 text-emerald-700; }

  /* ===== UTILS ===== */
  .gradient-mesh {
    background-image:
      radial-gradient(at 14% 10%, rgba(255,90,90,.35) 0px, transparent 45%),
      radial-gradient(at 85% 0%, rgba(220,28,28,.35) 0px, transparent 45%),
      radial-gradient(at 70% 90%, rgba(255,40,40,.18) 0px, transparent 50%);
  }
  html.dark .gradient-mesh {
    background-image:
      radial-gradient(at 14% 10%, rgba(255,40,40,.35) 0px, transparent 45%),
      radial-gradient(at 85% 0%, rgba(220,28,28,.45) 0px, transparent 45%),
      radial-gradient(at 70% 90%, rgba(80,5,15,.55) 0px, transparent 55%);
  }

  .text-gradient-brand { background-image: linear-gradient(135deg, #ff8e8e 0%, #ff3838 35%, #ff1a1a 60%, #9b1212 100%); -webkit-background-clip: text; background-clip: text; color: transparent; }
  .text-gradient-chrome {
    background-image: linear-gradient(180deg, #ffffff 0%, #f0f0f0 35%, #b0b0b8 50%, #ffffff 70%, #d8d8dd 100%);
    -webkit-background-clip: text; background-clip: text; color: transparent;
    text-shadow: 0 1px 0 rgba(255,255,255,.15);
  }
  .text-glow-brand { text-shadow: 0 0 18px rgba(255,30,30,.55), 0 0 60px rgba(255,30,30,.35); }
  .text-balance { text-wrap: balance; }

  .neon-divider {
    height: 1px; background: linear-gradient(90deg, transparent 0%, rgba(255,30,30,.55) 50%, transparent 100%);
    box-shadow: 0 0 18px 0 rgba(255,30,30,.55);
  }
  .ring-neon { box-shadow: 0 0 0 1px rgba(255,30,30,.55), 0 0 24px -4px rgba(255,30,30,.7); }
  .ring-neon-strong { box-shadow: 0 0 0 1.5px rgba(255,80,80,.85), 0 0 32px 0 rgba(255,30,30,.7), 0 0 80px -8px rgba(255,30,30,.7); }

  /* Hero blob orbs */
  .orb { position:absolute; border-radius:9999px; filter:blur(70px); pointer-events:none; }
  .orb-red   { width:560px; height:560px; background: radial-gradient(closest-side, rgba(255,30,30,.55), rgba(255,30,30,0) 70%); }
  .orb-deep  { width:620px; height:620px; background: radial-gradient(closest-side, rgba(120,5,30,.7), rgba(0,0,0,0) 70%); }
  .orb-pink  { width:380px; height:380px; background: radial-gradient(closest-side, rgba(255,120,140,.35), rgba(255,40,80,0) 70%); }

  /* Tilt 3D wrapper (Alpine helper, vedi sotto) */
  .tilt-wrap { transform-style: preserve-3d; perspective: 1000px; }
  .tilt-inner { transition: transform .35s cubic-bezier(.16,1,.3,1); transform-style: preserve-3d; will-change: transform; }
  .tilt-shine { position:absolute; inset:0; border-radius: inherit; pointer-events:none; opacity:0; mix-blend-mode: screen;
    background: radial-gradient(circle at var(--mx,50%) var(--my,50%), rgba(255,90,90,.45), rgba(255,255,255,0) 35%);
    transition: opacity .25s; }
  .tilt-wrap:hover .tilt-shine{ opacity:1; }

  /* TABLES */
  table.table-base { @apply w-full text-sm; }
  table.table-base thead th { @apply text-left text-[11px] font-semibold uppercase tracking-wider px-4 py-3 border-b; }
  html.dark table.table-base thead th { color:#b6b6c0; border-color: rgba(255,255,255,.07); background: rgba(255,255,255,.02); }
  html:not(.dark) table.table-base thead th { color:#5e5e6e; border-color:#eeeef0; background: #f7f7f880; }
  table.table-base tbody td { @apply px-4 py-3.5 border-b; }
  html.dark table.table-base tbody td { border-color: rgba(255,255,255,.05); }
  html:not(.dark) table.table-base tbody td { border-color:#eeeef0; }
  html.dark table.table-base tbody tr:hover { background: rgba(255,30,30,.045); }
  html:not(.dark) table.table-base tbody tr:hover { background:#f7f7f8b3; }
}
</style>
</head>
<body x-data>
<!-- AMBIENTE: orb di luce + gridline (solo dark) -->
<div aria-hidden="true" class="dark:block hidden fixed inset-0 -z-10 overflow-hidden pointer-events-none">
  <div class="orb orb-red animate-orb-a" style="top:-180px; left:-180px;"></div>
  <div class="orb orb-deep animate-orb-b" style="bottom:-260px; right:-180px;"></div>
  <div class="orb orb-pink animate-orb-a" style="top:38%; left:55%; opacity:.55;"></div>
</div>
