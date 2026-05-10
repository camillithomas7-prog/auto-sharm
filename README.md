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

## Deploy Hostinger

1. **hPanel → Database → MySQL**: crea un nuovo database. Annota host (di solito `localhost`), nome db, user, password.
2. **hPanel → Git**: crea repository con URL `https://github.com/camillithomas7-prog/auto-sharm.git`, branch `main`, percorso `public_html`, abilita **Auto-deploy**.
3. **File Manager** → in `public_html/` crea `config.php` copiando `config.sample.php`. Commenta il blocco SQLite e decommenta quello MySQL con le credenziali del punto 1. Aggiorna `site.url` con il tuo dominio.
4. Apri `https://tuo-dominio/setup.php` UNA volta per creare lo schema + utente admin demo + flotta demo + coupon `SHARM10`.
5. Login admin (`admin@autosharm.com` / `admin123`) → cambia subito la password in **Impostazioni**.
6. **Sicurezza post-deploy**: rinomina o proteggi `setup.php` (puoi cancellarlo dal File Manager — sarà rigenerato a ogni deploy ma non è esposto come endpoint sensibile, comunque meglio toglierlo dopo il primo run).

### Update successivi
Push su `main` → Hostinger ricostruisce automaticamente. Niente è da fare manualmente. Se aggiungi tabelle: visita di nuovo `setup.php` (idempotente, non cancella dati).

### Sezione Transfer aeroporto
Per attivarla sul sito: vai in **Admin → Impostazioni → Sezioni del sito** e abilita "Transfer aeroporto". Default OFF.
