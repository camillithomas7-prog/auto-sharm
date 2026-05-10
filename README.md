# Auto Sharm

Autonoleggio premium a Sharm El Sheikh — gestionale + sito vetrina prenotazioni dirette.

Stessa filosofia di Casa Vacanza (Patrizia Mancini): PHP + MySQL/SQLite, Tailwind via CDN, Alpine.js, i18n in 5 lingue.

## Stack

- **PHP 8** + **PDO** (SQLite per dev locale, MySQL per Hostinger)
- **Tailwind CSS** via CDN con palette rossa brand (auto sportiva)
- **Alpine.js** per interattività
- **Lucide icons**
- **Plus Jakarta Sans** + **Fraunces** (fonts)

## Struttura

```
auto-sharm/
├─ assets/         # logo SVG (auto rossa + scritta)
├─ config.php      # config locale (gitignored)
├─ config.sample.php
├─ config-storage/ # DB SQLite locale (gitignored)
├─ index.php       # homepage
├─ flotta.php      # lista auto con filtri categoria
├─ auto.php        # dettaglio auto + form prenotazione
├─ contatti.php
├─ setup.php       # crea schema + admin demo + 5 auto demo + coupon SHARM10
├─ lib/
│  ├─ db.php       # PDO con auto-switch sqlite/mysql
│  ├─ auth.php     # session + bcrypt + CSRF
│  ├─ utils.php
│  ├─ i18n.php     # 5 lingue + tAmenity()
│  └─ pricing.php  # quote engine: best fit giornaliero/settimanale/mensile + coupon
├─ api/
│  ├─ quote.php    # POST { car_id, from, to, coupon } → totale
│  └─ booking.php  # POST { car_id, from, to, name, email, phone... } → code
├─ admin/
│  ├─ login.php / logout.php
│  ├─ index.php           # dashboard con KPI commissione
│  ├─ auto.php            # lista auto
│  ├─ auto-edit.php       # crea/modifica con commissione gestione
│  ├─ prenotazioni.php / prenotazione.php / prenotazione-nuova.php
│  ├─ clienti.php
│  ├─ calendario.php      # tabella giorno × auto
│  ├─ spese.php           # bilancio + tabella commissioni per auto
│  ├─ coupon.php
│  ├─ recensioni.php
│  ├─ notifiche.php
│  └─ impostazioni.php
└─ partials/       # head, header, footer, admin-shell-top/bottom
```

## Avvio dev locale

```bash
cd ~/auto-sharm
cp config.sample.php config.php   # già fatto, SQLite di default
php -S 127.0.0.1:8110 -t .
# poi visita: http://127.0.0.1:8110/setup.php (1 volta)
# admin: http://127.0.0.1:8110/admin/login.php
#        admin@autosharm.com / admin123
```

## i18n — 5 lingue

IT (default), EN, RU, ES, DE. Tutte le stringhe del sito pubblico passano da `t($key)` (cf. `lib/i18n.php`).
Lingua via `?lang=xx` (cookie `as_lang`, 1 anno).

## Modello business

Identico a Casa Vacanza: la persona che gestisce Auto Sharm è un property manager
che noleggia auto per conto di proprietari terzi e trattiene una **% commissione** sui
ricavi (`cars.manager_commission_pct`, default 20%). Visibile in dashboard e in
"Spese & bilancio" come KPI separato + tabella per auto.

## Deploy Hostinger (TODO)

Quando pronto:
1. In `config.php` togliere il blocco SQLite e attivare quello MySQL con le credenziali Hostinger
2. Push su GitHub → auto-deploy
3. Visitare `setup.php` una volta sul dominio per creare lo schema
