<?php
$LANGUAGES = [
    'it' => ['code' => 'it', 'name' => 'Italiano', 'native' => 'Italiano', 'flag' => '🇮🇹'],
    'en' => ['code' => 'en', 'name' => 'English',  'native' => 'English',  'flag' => '🇬🇧'],
    'ru' => ['code' => 'ru', 'name' => 'Russian',  'native' => 'Русский',  'flag' => '🇷🇺'],
    'es' => ['code' => 'es', 'name' => 'Spanish',  'native' => 'Español',  'flag' => '🇪🇸'],
    'de' => ['code' => 'de', 'name' => 'German',   'native' => 'Deutsch',  'flag' => '🇩🇪'],
];

function currentLang(): string {
    static $lang = null;
    if ($lang !== null) return $lang;
    global $LANGUAGES;
    if (!empty($_GET['lang']) && isset($LANGUAGES[$_GET['lang']])) {
        $lang = $_GET['lang'];
        if (!headers_sent()) setcookie('as_lang', $lang, time() + 86400 * 365, '/', '', false, false);
    } elseif (!empty($_COOKIE['as_lang']) && isset($LANGUAGES[$_COOKIE['as_lang']])) {
        $lang = $_COOKIE['as_lang'];
    } else {
        $lang = 'it';
    }
    return $lang;
}

function langSwitch(string $lang): string {
    $url = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    $params = $_GET;
    $params['lang'] = $lang;
    return $url . '?' . http_build_query($params);
}

$TRANSLATIONS = [

// ========== NAV ==========
'nav.home'      => ['it' => 'Home',          'en' => 'Home',         'ru' => 'Главная',      'es' => 'Inicio',     'de' => 'Start'],
'nav.fleet'     => ['it' => 'Le auto',       'en' => 'Our fleet',    'ru' => 'Автопарк',     'es' => 'Flota',      'de' => 'Fuhrpark'],
'nav.howit'     => ['it' => 'Come funziona', 'en' => 'How it works', 'ru' => 'Как это работает', 'es' => 'Cómo funciona', 'de' => 'So geht\'s'],
'nav.contact'   => ['it' => 'Contatti',      'en' => 'Contact',      'ru' => 'Контакты',     'es' => 'Contacto',   'de' => 'Kontakt'],
'nav.transfers' => ['it' => 'Transfer',      'en' => 'Transfers',    'ru' => 'Трансферы',    'es' => 'Traslados',  'de' => 'Transfers'],
'nav.admin'     => ['it' => 'Area admin',    'en' => 'Admin',        'ru' => 'Админ',        'es' => 'Admin',      'de' => 'Admin'],
'cta.book'      => ['it' => 'Prenota ora',   'en' => 'Book now',     'ru' => 'Забронировать','es' => 'Reservar',   'de' => 'Jetzt buchen'],
'cta.see_fleet' => ['it' => 'Vedi tutta la flotta', 'en' => 'See full fleet', 'ru' => 'Весь автопарк', 'es' => 'Ver toda la flota', 'de' => 'Gesamten Fuhrpark'],

// ========== HOME ==========
'home.hero.kicker' => [
    'it' => 'Autonoleggio premium · Sharm El Sheikh',
    'en' => 'Premium car rental · Sharm El Sheikh',
    'ru' => 'Премиум прокат авто · Шарм-эль-Шейх',
    'es' => 'Alquiler premium · Sharm El Sheikh',
    'de' => 'Premium-Autovermietung · Sharm El Sheikh',
],
'home.hero.title' => [
    'it' => 'La tua auto, pronta a Sharm.',
    'en' => 'Your car, ready in Sharm.',
    'ru' => 'Ваше авто уже ждёт в Шарме.',
    'es' => 'Tu coche, listo en Sharm.',
    'de' => 'Dein Auto wartet in Sharm.',
],
'home.hero.sub' => [
    'it' => 'Consegna direttamente in hotel o all\'aeroporto. Auto controllate, prezzi trasparenti, prenotazione in 60 secondi.',
    'en' => 'Delivered to your hotel or the airport. Inspected vehicles, clear pricing, booking in 60 seconds.',
    'ru' => 'Доставка в отель или аэропорт. Проверенные авто, прозрачные цены, бронирование за 60 секунд.',
    'es' => 'Entrega directa en tu hotel o el aeropuerto. Vehículos revisados, precios claros, reserva en 60 segundos.',
    'de' => 'Lieferung direkt zum Hotel oder Flughafen. Geprüfte Fahrzeuge, transparente Preise, Buchung in 60 Sekunden.',
],
'home.hero.cta_primary' => ['it' => 'Scegli la tua auto', 'en' => 'Pick your car', 'ru' => 'Выбрать авто', 'es' => 'Elige tu coche', 'de' => 'Auto auswählen'],
'home.hero.cta_secondary' => ['it' => 'Contattaci su WhatsApp', 'en' => 'WhatsApp us', 'ru' => 'Напишите в WhatsApp', 'es' => 'Escríbenos en WhatsApp', 'de' => 'Auf WhatsApp schreiben'],

'home.feature.delivery.title' => ['it' => 'Consegna in hotel', 'en' => 'Hotel delivery', 'ru' => 'Доставка в отель', 'es' => 'Entrega en el hotel', 'de' => 'Lieferung ins Hotel'],
'home.feature.delivery.sub'   => ['it' => 'Veniamo da te, niente file in agenzia.', 'en' => 'We come to you. No agency queues.', 'ru' => 'Привезём сами — без очередей.', 'es' => 'Vamos donde estés, sin colas.', 'de' => 'Wir kommen zu dir — ohne Wartezeiten.'],
'home.feature.insurance.title' => ['it' => 'Assicurazione inclusa', 'en' => 'Insurance included', 'ru' => 'Страховка включена', 'es' => 'Seguro incluido', 'de' => 'Versicherung inklusive'],
'home.feature.insurance.sub'   => ['it' => 'Copertura RC e furto, sempre attive.', 'en' => 'Liability and theft, always on.', 'ru' => 'ОСАГО и от угона — всегда.', 'es' => 'RC y robo, siempre activos.', 'de' => 'Haftpflicht & Diebstahl inklusive.'],
'home.feature.support.title'   => ['it' => 'Assistenza 24/7', 'en' => '24/7 support', 'ru' => 'Поддержка 24/7', 'es' => 'Asistencia 24/7', 'de' => '24/7 Support'],
'home.feature.support.sub'     => ['it' => 'In italiano, inglese, russo. WhatsApp diretto.', 'en' => 'In Italian, English and Russian via WhatsApp.', 'ru' => 'На итальянском, английском, русском — WhatsApp.', 'es' => 'En italiano, inglés y ruso por WhatsApp.', 'de' => 'Italienisch, Englisch, Russisch — direkt per WhatsApp.'],
'home.feature.fuel.title'      => ['it' => 'Pieno → pieno', 'en' => 'Full to full', 'ru' => 'Полный → полный', 'es' => 'Lleno → lleno', 'de' => 'Voll → voll'],
'home.feature.fuel.sub'        => ['it' => 'Riprendi l\'auto col pieno e riconsegnala col pieno.', 'en' => 'Pick up full, return full. Simple.', 'ru' => 'Заправлено при выдаче и при возврате.', 'es' => 'Lleno a la entrega y a la devolución.', 'de' => 'Voll abholen, voll abgeben. Fertig.'],

'home.fleet.badge' => ['it' => 'La nostra flotta', 'en' => 'Our fleet', 'ru' => 'Автопарк', 'es' => 'Nuestra flota', 'de' => 'Unser Fuhrpark'],
'home.fleet.title' => [
    'it' => 'Auto recenti, controllate, pulite.',
    'en' => 'Recent, inspected, spotless cars.',
    'ru' => 'Свежие, проверенные, чистые авто.',
    'es' => 'Coches recientes, revisados y limpios.',
    'de' => 'Aktuelle, geprüfte, saubere Fahrzeuge.',
],
'home.fleet.sub' => [
    'it' => 'Dalla city car al SUV: scegli quella perfetta per il tuo viaggio.',
    'en' => 'From city cars to SUVs — pick the right one for your trip.',
    'ru' => 'От компакта до внедорожника — выбирайте под себя.',
    'es' => 'Del utilitario al SUV: el coche ideal para tu viaje.',
    'de' => 'Vom Kleinwagen bis zum SUV — der passende für deine Reise.',
],

'home.howit.badge'   => ['it' => 'Come funziona', 'en' => 'How it works', 'ru' => 'Как это работает', 'es' => 'Cómo funciona', 'de' => 'So geht\'s'],
'home.howit.title'   => ['it' => 'Tre passi e sei alla guida.', 'en' => 'Three steps and you\'re driving.', 'ru' => 'Три шага — и вы за рулём.', 'es' => 'Tres pasos y al volante.', 'de' => 'Drei Schritte — und du fährst.'],
'home.howit.s1.title' => ['it' => 'Scegli la tua auto', 'en' => 'Pick your car', 'ru' => 'Выберите авто', 'es' => 'Elige tu coche', 'de' => 'Auto wählen'],
'home.howit.s1.sub'   => ['it' => 'Sfoglia la flotta, confronta i prezzi e blocca le date.', 'en' => 'Browse the fleet, compare prices, lock the dates.', 'ru' => 'Выберите авто, сравните цены, забронируйте даты.', 'es' => 'Explora la flota, compara precios y reserva las fechas.', 'de' => 'Fuhrpark durchsehen, Preise vergleichen, Daten reservieren.'],
'home.howit.s2.title' => ['it' => 'Conferma in 60 secondi', 'en' => 'Confirm in 60 seconds', 'ru' => 'Подтверждение за 60 секунд', 'es' => 'Confirma en 60 segundos', 'de' => 'In 60 Sekunden bestätigen'],
'home.howit.s2.sub'   => ['it' => 'Inserisci nome, telefono e hotel. Ti contattiamo via WhatsApp.', 'en' => 'Just name, phone, hotel — we\'ll WhatsApp you.', 'ru' => 'Имя, телефон, отель — мы напишем в WhatsApp.', 'es' => 'Nombre, teléfono y hotel. Te escribimos por WhatsApp.', 'de' => 'Name, Telefon, Hotel — Antwort per WhatsApp.'],
'home.howit.s3.title' => ['it' => 'Ritira o ricevi l\'auto', 'en' => 'Pick up or get it delivered', 'ru' => 'Заберите или получите доставку', 'es' => 'Recoge o recibe el coche', 'de' => 'Abholen oder liefern lassen'],
'home.howit.s3.sub'   => ['it' => 'Consegna in hotel, in aeroporto o presso il nostro ufficio.', 'en' => 'Hotel, airport or our office — your call.', 'ru' => 'В отель, в аэропорт или к нам в офис.', 'es' => 'En el hotel, el aeropuerto o nuestra oficina.', 'de' => 'Hotel, Flughafen oder unser Büro — du entscheidest.'],

// ========== FLEET LIST ==========
'fleet.title' => [
    'it' => 'La nostra flotta.',
    'en' => 'Our fleet.',
    'ru' => 'Наш автопарк.',
    'es' => 'Nuestra flota.',
    'de' => 'Unser Fuhrpark.',
],
'fleet.sub' => [
    'it' => 'Auto economiche, SUV, automatiche e cambio manuale. Tutte controllate, sempre pronte.',
    'en' => 'Compact cars, SUVs, automatic and manual. All inspected, always ready.',
    'ru' => 'Эконом, внедорожники, автомат и механика. Все проверены и готовы к выдаче.',
    'es' => 'Económicos, SUV, automáticos y manuales. Todos revisados y listos.',
    'de' => 'Kleinwagen, SUVs, Automatik und Handschaltung. Alle geprüft, sofort verfügbar.',
],
'fleet.filter.all'       => ['it' => 'Tutte', 'en' => 'All', 'ru' => 'Все', 'es' => 'Todos', 'de' => 'Alle'],
'fleet.filter.economy'   => ['it' => 'Economy', 'en' => 'Economy', 'ru' => 'Эконом', 'es' => 'Económico', 'de' => 'Economy'],
'fleet.filter.compact'   => ['it' => 'Compact', 'en' => 'Compact', 'ru' => 'Компакт', 'es' => 'Compacto', 'de' => 'Kompakt'],
'fleet.filter.suv'       => ['it' => 'SUV', 'en' => 'SUV', 'ru' => 'Внедорожник', 'es' => 'SUV', 'de' => 'SUV'],
'fleet.filter.luxury'    => ['it' => 'Luxury', 'en' => 'Luxury', 'ru' => 'Премиум', 'es' => 'Premium', 'de' => 'Luxus'],
'fleet.filter.minivan'   => ['it' => 'Minivan', 'en' => 'Minivan', 'ru' => 'Минивэн', 'es' => 'Monovolumen', 'de' => 'Minivan'],
'fleet.no_items' => ['it' => 'Nessuna auto disponibile in questa categoria.', 'en' => 'No cars available in this category.', 'ru' => 'Нет авто в этой категории.', 'es' => 'No hay coches en esta categoría.', 'de' => 'Keine Fahrzeuge in dieser Kategorie.'],

// ========== CAR DETAIL ==========
'car.back'           => ['it' => 'Tutta la flotta', 'en' => 'All cars', 'ru' => 'Весь автопарк', 'es' => 'Toda la flota', 'de' => 'Gesamter Fuhrpark'],
'car.description'    => ['it' => 'Descrizione',     'en' => 'Description', 'ru' => 'Описание', 'es' => 'Descripción', 'de' => 'Beschreibung'],
'car.specs'          => ['it' => 'Caratteristiche', 'en' => 'Specs',      'ru' => 'Характеристики', 'es' => 'Características', 'de' => 'Ausstattung'],
'car.included'       => ['it' => 'Cosa è incluso',  'en' => "What's included", 'ru' => 'Что включено', 'es' => 'Qué incluye', 'de' => 'Inklusive'],
'car.spec.transmission' => ['it' => 'Cambio',     'en' => 'Transmission', 'ru' => 'Коробка',  'es' => 'Cambio',     'de' => 'Getriebe'],
'car.spec.fuel'         => ['it' => 'Carburante', 'en' => 'Fuel',         'ru' => 'Топливо',  'es' => 'Combustible', 'de' => 'Kraftstoff'],
'car.spec.seats'        => ['it' => 'Posti',      'en' => 'Seats',        'ru' => 'Мест',     'es' => 'Plazas',     'de' => 'Sitze'],
'car.spec.doors'        => ['it' => 'Porte',      'en' => 'Doors',        'ru' => 'Дверей',   'es' => 'Puertas',    'de' => 'Türen'],
'car.spec.luggage'      => ['it' => 'Bagagli',    'en' => 'Luggage',      'ru' => 'Багаж',    'es' => 'Maletas',    'de' => 'Gepäck'],
'car.spec.year'         => ['it' => 'Anno',       'en' => 'Year',         'ru' => 'Год',      'es' => 'Año',        'de' => 'Baujahr'],
'car.spec.ac'           => ['it' => 'Aria condizionata', 'en' => 'Air conditioning', 'ru' => 'Кондиционер', 'es' => 'Aire acondicionado', 'de' => 'Klimaanlage'],

'car.transmission.manual'    => ['it' => 'Manuale',   'en' => 'Manual',    'ru' => 'Механика', 'es' => 'Manual',    'de' => 'Schaltung'],
'car.transmission.automatic' => ['it' => 'Automatico','en' => 'Automatic', 'ru' => 'Автомат',  'es' => 'Automático','de' => 'Automatik'],
'car.fuel.petrol'  => ['it' => 'Benzina', 'en' => 'Petrol', 'ru' => 'Бензин', 'es' => 'Gasolina', 'de' => 'Benzin'],
'car.fuel.diesel'  => ['it' => 'Diesel',  'en' => 'Diesel', 'ru' => 'Дизель', 'es' => 'Diésel',   'de' => 'Diesel'],
'car.fuel.hybrid'  => ['it' => 'Ibrida',  'en' => 'Hybrid', 'ru' => 'Гибрид', 'es' => 'Híbrido',  'de' => 'Hybrid'],
'car.fuel.electric'=> ['it' => 'Elettrica','en' => 'Electric','ru' => 'Электро','es' => 'Eléctrico','de' => 'Elektro'],

'car.rates'        => ['it' => 'Tariffe',         'en' => 'Rates',         'ru' => 'Тарифы',       'es' => 'Tarifas',     'de' => 'Tarife'],
'car.rate.daily'   => ['it' => 'Giornaliera',     'en' => 'Daily',         'ru' => 'За день',      'es' => 'Diaria',      'de' => 'Tagestarif'],
'car.rate.weekly'  => ['it' => 'Settimanale',     'en' => 'Weekly',        'ru' => 'За неделю',    'es' => 'Semanal',     'de' => 'Wochentarif'],
'car.rate.biweekly'=> ['it' => '2 settimane',     'en' => '2 weeks',       'ru' => '2 недели',     'es' => '2 semanas',   'de' => '2 Wochen'],
'car.rate.monthly' => ['it' => 'Mensile',         'en' => 'Monthly',       'ru' => 'За месяц',     'es' => 'Mensual',     'de' => 'Monatstarif'],
'car.deposit'      => ['it' => 'Cauzione: {p} (rimborsata alla riconsegna).', 'en' => 'Deposit: {p} (refunded on return).', 'ru' => 'Залог: {p} (возвращается при сдаче).', 'es' => 'Fianza: {p} (devuelta a la entrega).', 'de' => 'Kaution: {p} (Rückerstattung bei Rückgabe).'],
'car.license'      => ['it' => 'Patente di guida obbligatoria.', 'en' => 'Driver\'s license required.', 'ru' => 'Требуется водительское удостоверение.', 'es' => 'Carné de conducir obligatorio.', 'de' => 'Führerschein erforderlich.'],
'car.minage'       => ['it' => 'Età minima: {n} anni.', 'en' => 'Minimum age: {n}.', 'ru' => 'Минимальный возраст: {n} лет.', 'es' => 'Edad mínima: {n} años.', 'de' => 'Mindestalter: {n} Jahre.'],

// ========== BOOKING FORM ==========
'book.pickup_date'    => ['it' => 'Ritiro',           'en' => 'Pick-up',        'ru' => 'Получение',     'es' => 'Recogida',     'de' => 'Abholung'],
'book.dropoff_date'   => ['it' => 'Riconsegna',       'en' => 'Return',         'ru' => 'Возврат',       'es' => 'Devolución',   'de' => 'Rückgabe'],
'book.pickup_loc'     => ['it' => 'Luogo di ritiro',  'en' => 'Pick-up location','ru' => 'Место получения','es' => 'Lugar de recogida','de' => 'Abholort'],
'book.pickup_loc_ph'  => ['it' => 'es. Hotel Naama Bay, Aeroporto SSH', 'en' => 'e.g. Hotel Naama Bay, SSH Airport', 'ru' => 'напр. отель Naama Bay, аэропорт SSH', 'es' => 'ej. Hotel Naama Bay, Aeropuerto SSH', 'de' => 'z. B. Hotel Naama Bay, Flughafen SSH'],
'book.fullname'       => ['it' => 'Nome e cognome',   'en' => 'Full name',      'ru' => 'Имя и фамилия', 'es' => 'Nombre y apellido', 'de' => 'Vor- und Nachname'],
'book.email'          => ['it' => 'Email',            'en' => 'Email',          'ru' => 'Email',         'es' => 'Email',        'de' => 'E-Mail'],
'book.phone'          => ['it' => 'Telefono',         'en' => 'Phone',          'ru' => 'Телефон',       'es' => 'Teléfono',     'de' => 'Telefon'],
'book.notes_ph'       => ['it' => 'Note: numero patente, volo, ecc.', 'en' => 'Notes: license, flight, etc.', 'ru' => 'Примечания: права, рейс…', 'es' => 'Notas: licencia, vuelo, etc.', 'de' => 'Notizen: Führerschein, Flug, etc.'],
'book.coupon'         => ['it' => 'Codice sconto (opzionale)', 'en' => 'Discount code (optional)', 'ru' => 'Промокод (необязательно)', 'es' => 'Código de descuento (opcional)', 'de' => 'Rabattcode (optional)'],
'book.coupon_ph'      => ['it' => 'es. SHARM10', 'en' => 'e.g. SHARM10', 'ru' => 'напр. SHARM10', 'es' => 'ej. SHARM10', 'de' => 'z. B. SHARM10'],
'book.submit'         => ['it' => 'Richiedi noleggio','en' => 'Request rental', 'ru' => 'Запросить аренду', 'es' => 'Solicitar alquiler', 'de' => 'Miete anfragen'],
'book.sending'        => ['it' => 'Invio…',           'en' => 'Sending…',       'ru' => 'Отправка…',     'es' => 'Enviando…',    'de' => 'Wird gesendet…'],
'book.no_charge'      => ['it' => 'Nessun addebito ora. Confermiamo via WhatsApp e si paga al ritiro.', 'en' => 'No charge now. We confirm via WhatsApp; you pay at pick-up.', 'ru' => 'Сейчас не списывается. Подтверждаем в WhatsApp, оплата при получении.', 'es' => 'Sin cargo ahora. Confirmamos por WhatsApp y se paga al recoger.', 'de' => 'Keine Buchung jetzt. Bestätigung per WhatsApp, Zahlung bei Abholung.'],
'book.req_sent'       => ['it' => 'Richiesta inviata!','en' => 'Request sent!',  'ru' => 'Заявка отправлена!','es' => '¡Solicitud enviada!','de' => 'Anfrage gesendet!'],
'book.code'           => ['it' => 'Codice prenotazione','en' => 'Booking code',  'ru' => 'Номер брони',   'es' => 'Código de reserva','de' => 'Buchungscode'],
'book.contact_soon'   => ['it' => 'Ti scriviamo a breve via WhatsApp.', 'en' => 'We\'ll WhatsApp you shortly.', 'ru' => 'Скоро напишем в WhatsApp.', 'es' => 'Te escribimos pronto por WhatsApp.', 'de' => 'Wir melden uns gleich per WhatsApp.'],
'book.summary.days'   => ['it' => 'giorni',           'en' => 'days',           'ru' => 'дней',          'es' => 'días',         'de' => 'Tage'],
'book.summary.total'  => ['it' => 'Totale',           'en' => 'Total',          'ru' => 'Итого',         'es' => 'Total',        'de' => 'Gesamt'],
'book.summary.discount'=>['it' => 'Sconto',           'en' => 'Discount',       'ru' => 'Скидка',        'es' => 'Descuento',    'de' => 'Rabatt'],

'common.from'        => ['it' => 'da',     'en' => 'from',  'ru' => 'от',  'es' => 'desde', 'de' => 'ab'],
'common.per_day'     => ['it' => '/giorno','en' => '/day',  'ru' => '/день','es' => '/día', 'de' => '/Tag'],
'common.search'      => ['it' => 'Cerca',  'en' => 'Search','ru' => 'Поиск','es' => 'Buscar','de' => 'Suchen'],

// ========== CONTACT ==========
'contact.title' => ['it' => 'Parliamo.', 'en' => 'Let\'s talk.', 'ru' => 'Поговорим.', 'es' => 'Hablemos.', 'de' => 'Sprich mit uns.'],
'contact.sub'   => ['it' => 'Per qualunque domanda sulla flotta, le tariffe o la consegna, scrivici. Rispondiamo entro pochi minuti.', 'en' => 'Any question on cars, rates or delivery — drop us a line. We reply in minutes.', 'ru' => 'Любые вопросы по авто, тарифам или доставке — пишите. Отвечаем за минуты.', 'es' => 'Cualquier duda sobre coches, tarifas o entrega: escríbenos. Respondemos en minutos.', 'de' => 'Fragen zu Fahrzeugen, Tarifen oder Lieferung? Schreib uns — Antwort in Minuten.'],
'contact.wa_msg' => ['it' => 'Salve, vorrei informazioni su Auto Sharm.', 'en' => 'Hi, I\'d like info on Auto Sharm.', 'ru' => 'Здравствуйте, хочу узнать про Auto Sharm.', 'es' => 'Hola, quisiera información sobre Auto Sharm.', 'de' => 'Hallo, ich hätte gern Infos zu Auto Sharm.'],

// ========== FOOTER ==========
'footer.tagline' => [
    'it' => 'Autonoleggio premium a Sharm El Sheikh. Auto controllate, prezzi onesti, consegna in hotel.',
    'en' => 'Premium car rental in Sharm El Sheikh. Inspected cars, fair prices, hotel delivery.',
    'ru' => 'Премиум прокат в Шарм-эль-Шейхе. Проверенные авто, честные цены, доставка в отель.',
    'es' => 'Alquiler premium en Sharm El Sheikh. Coches revisados, precios justos, entrega en hotel.',
    'de' => 'Premium-Autovermietung in Sharm El Sheikh. Geprüfte Fahrzeuge, faire Preise, Hotel-Lieferung.',
],
'footer.rights' => ['it' => 'Tutti i diritti riservati', 'en' => 'All rights reserved', 'ru' => 'Все права защищены', 'es' => 'Todos los derechos reservados', 'de' => 'Alle Rechte vorbehalten'],

// ========== FORM COMMON ==========
'form.error'       => ['it' => 'Errore',       'en' => 'Error',       'ru' => 'Ошибка',     'es' => 'Error',       'de' => 'Fehler'],
'form.required'    => ['it' => 'Campo obbligatorio', 'en' => 'Required field', 'ru' => 'Обязательное поле', 'es' => 'Campo obligatorio', 'de' => 'Pflichtfeld'],
'form.country_search' => ['it' => 'Cerca paese…', 'en' => 'Search country…', 'ru' => 'Поиск страны…', 'es' => 'Buscar país…', 'de' => 'Land suchen…'],

// ========== AMENITIES dictionary ==========
'amenity.aria_condizionata' => ['it' => 'Aria condizionata', 'en' => 'Air conditioning', 'ru' => 'Кондиционер', 'es' => 'Aire acondicionado', 'de' => 'Klimaanlage'],
'amenity.bluetooth' => ['it' => 'Bluetooth', 'en' => 'Bluetooth', 'ru' => 'Bluetooth', 'es' => 'Bluetooth', 'de' => 'Bluetooth'],
'amenity.gps'       => ['it' => 'GPS',       'en' => 'GPS',       'ru' => 'GPS',       'es' => 'GPS',       'de' => 'GPS'],
'amenity.4x4'       => ['it' => '4×4',       'en' => '4×4',       'ru' => '4×4',       'es' => '4×4',       'de' => '4×4'],
'amenity.usb'       => ['it' => 'Porta USB', 'en' => 'USB port',  'ru' => 'USB',       'es' => 'Puerto USB','de' => 'USB-Anschluss'],
'amenity.cruise_control' => ['it' => 'Cruise control', 'en' => 'Cruise control', 'ru' => 'Круиз-контроль', 'es' => 'Control de crucero', 'de' => 'Tempomat'],
'amenity.parking_sensors' => ['it' => 'Sensori parcheggio', 'en' => 'Parking sensors', 'ru' => 'Парктроник', 'es' => 'Sensores de aparcamiento', 'de' => 'Einparkhilfe'],
'amenity.backup_camera' => ['it' => 'Telecamera posteriore', 'en' => 'Reverse camera', 'ru' => 'Камера заднего вида', 'es' => 'Cámara trasera', 'de' => 'Rückfahrkamera'],
'amenity.child_seat' => ['it' => 'Seggiolino bimbo', 'en' => 'Child seat', 'ru' => 'Детское кресло', 'es' => 'Silla infantil', 'de' => 'Kindersitz'],
'amenity.assicurazione' => ['it' => 'Assicurazione', 'en' => 'Insurance', 'ru' => 'Страховка', 'es' => 'Seguro', 'de' => 'Versicherung'],
'amenity.consegna_hotel' => ['it' => 'Consegna in hotel', 'en' => 'Hotel delivery', 'ru' => 'Доставка в отель', 'es' => 'Entrega en hotel', 'de' => 'Hotel-Lieferung'],
'amenity.consegna_aeroporto' => ['it' => 'Consegna aeroporto', 'en' => 'Airport delivery', 'ru' => 'Доставка в аэропорт', 'es' => 'Entrega aeropuerto', 'de' => 'Flughafen-Lieferung'],
'amenity.km_illimitati' => ['it' => 'Km illimitati', 'en' => 'Unlimited km', 'ru' => 'Без лимита км', 'es' => 'Km ilimitados', 'de' => 'Unbegrenzte km'],

// ========== CATEGORIES ==========
'cat.economy' => ['it' => 'Economy',  'en' => 'Economy', 'ru' => 'Эконом',     'es' => 'Económico', 'de' => 'Economy'],
'cat.compact' => ['it' => 'Compact',  'en' => 'Compact', 'ru' => 'Компакт',    'es' => 'Compacto',  'de' => 'Kompakt'],
'cat.suv'     => ['it' => 'SUV',      'en' => 'SUV',     'ru' => 'Внедорожник','es' => 'SUV',       'de' => 'SUV'],
'cat.luxury'  => ['it' => 'Luxury',   'en' => 'Luxury',  'ru' => 'Премиум',    'es' => 'Premium',   'de' => 'Luxus'],
'cat.minivan' => ['it' => 'Minivan',  'en' => 'Minivan', 'ru' => 'Минивэн',    'es' => 'Monovolumen','de' => 'Minivan'],

// ========== META ==========
'meta.home_title' => [
    'it' => 'Auto Sharm · Autonoleggio premium a Sharm El Sheikh',
    'en' => 'Auto Sharm · Premium car rental in Sharm El Sheikh',
    'ru' => 'Auto Sharm · Премиум прокат авто в Шарм-эль-Шейхе',
    'es' => 'Auto Sharm · Alquiler premium en Sharm El Sheikh',
    'de' => 'Auto Sharm · Premium-Autovermietung in Sharm El Sheikh',
],
'meta.lang_label' => ['it' => 'Cambia lingua', 'en' => 'Change language', 'ru' => 'Сменить язык', 'es' => 'Cambiar idioma', 'de' => 'Sprache ändern'],
'meta.transfers_title' => [
    'it' => 'Transfer aeroporto Sharm El Sheikh — prenotazione diretta',
    'en' => 'Sharm El Sheikh airport transfers — direct booking',
    'ru' => 'Трансферы из аэропорта Шарм-эль-Шейх — прямое бронирование',
    'es' => 'Traslados aeropuerto Sharm El Sheikh — reserva directa',
    'de' => 'Flughafen-Transfers Sharm El Sheikh — Direktbuchung',
],

// ========== TRANSFERS ==========
'transfer.badge'         => ['it' => 'Transfer aeroporto', 'en' => 'Airport transfers', 'ru' => 'Аэропорт-трансфер', 'es' => 'Traslado aeropuerto', 'de' => 'Flughafen-Transfer'],
'transfer.hero.title'    => [
    'it' => 'Dall\'aeroporto al tuo hotel, senza pensieri',
    'en' => 'From the airport to your hotel, hassle-free',
    'ru' => 'Из аэропорта в отель — без забот',
    'es' => 'Del aeropuerto a tu hotel, sin preocupaciones',
    'de' => 'Vom Flughafen zum Hotel — sorgenfrei',
],
'transfer.hero.sub'      => [
    'it' => 'Autista in italiano, attesa inclusa, prezzo fisso confermato in WhatsApp.',
    'en' => 'English-speaking driver, waiting time included, fixed price confirmed on WhatsApp.',
    'ru' => 'Водитель со знанием языка, ожидание включено, фиксированная цена в WhatsApp.',
    'es' => 'Chófer en tu idioma, espera incluida, precio fijo confirmado por WhatsApp.',
    'de' => 'Fahrer mit Sprachkenntnissen, Wartezeit inklusive, Festpreis per WhatsApp.',
],
'transfer.list.title'    => ['it' => 'Tratte e prezzi',     'en' => 'Routes & prices',  'ru' => 'Направления и цены', 'es' => 'Rutas y precios', 'de' => 'Strecken & Preise'],
'transfer.list.sub'      => ['it' => 'Tariffe a tratta, non a persona. Prezzo per veicolo.', 'en' => 'Fares per route, not per person. Price per vehicle.', 'ru' => 'Цена за маршрут, а не за человека. Цена за автомобиль.', 'es' => 'Tarifas por ruta, no por persona. Precio por vehículo.', 'de' => 'Preis pro Strecke, nicht pro Person. Pro Fahrzeug.'],
'transfer.from'          => ['it' => 'Da',          'en' => 'From',         'ru' => 'Откуда',     'es' => 'Desde',       'de' => 'Von'],
'transfer.to'            => ['it' => 'A',           'en' => 'To',           'ru' => 'Куда',       'es' => 'Hasta',       'de' => 'Nach'],
'transfer.duration'      => ['it' => 'Durata',      'en' => 'Duration',     'ru' => 'Время',      'es' => 'Duración',    'de' => 'Dauer'],
'transfer.capacity'      => ['it' => 'Capienza',    'en' => 'Capacity',     'ru' => 'Вместимость','es' => 'Capacidad',   'de' => 'Kapazität'],
'transfer.vehicle'       => ['it' => 'Veicolo',     'en' => 'Vehicle',      'ru' => 'Автомобиль', 'es' => 'Vehículo',    'de' => 'Fahrzeug'],
'transfer.book.title'    => ['it' => 'Prenota questa tratta', 'en' => 'Book this transfer', 'ru' => 'Забронировать трансфер', 'es' => 'Reservar este traslado', 'de' => 'Transfer buchen'],
'transfer.field.arrival_date' => ['it' => 'Data arrivo', 'en' => 'Arrival date', 'ru' => 'Дата прибытия', 'es' => 'Fecha de llegada', 'de' => 'Ankunftsdatum'],
'transfer.field.arrival_time' => ['it' => 'Ora arrivo (volo)', 'en' => 'Arrival time (flight)', 'ru' => 'Время прилёта', 'es' => 'Hora de llegada', 'de' => 'Ankunftszeit'],
'transfer.field.flight'  => ['it' => 'Numero volo', 'en' => 'Flight number','ru' => 'Номер рейса','es' => 'Número de vuelo','de' => 'Flugnummer'],
'transfer.field.destination' => ['it' => 'Hotel / destinazione', 'en' => 'Hotel / destination', 'ru' => 'Отель / адрес', 'es' => 'Hotel / destino', 'de' => 'Hotel / Ziel'],
'transfer.field.passengers'  => ['it' => 'Passeggeri', 'en' => 'Passengers', 'ru' => 'Пассажиры', 'es' => 'Pasajeros', 'de' => 'Passagiere'],
'transfer.field.name'    => ['it' => 'Nome e cognome','en' => 'Full name',    'ru' => 'Имя и фамилия','es' => 'Nombre y apellido','de' => 'Vor- und Nachname'],
'transfer.field.email'   => ['it' => 'Email',       'en' => 'Email',        'ru' => 'Email',      'es' => 'Email',       'de' => 'E-Mail'],
'transfer.field.phone'   => ['it' => 'Telefono / WhatsApp','en' => 'Phone / WhatsApp','ru' => 'Телефон / WhatsApp','es' => 'Teléfono / WhatsApp','de' => 'Telefon / WhatsApp'],
'transfer.field.notes'   => ['it' => 'Note',        'en' => 'Notes',        'ru' => 'Заметки',    'es' => 'Notas',       'de' => 'Hinweise'],
'transfer.cta'           => ['it' => 'Prenota transfer', 'en' => 'Book transfer', 'ru' => 'Забронировать', 'es' => 'Reservar', 'de' => 'Buchen'],
'transfer.success.title' => ['it' => 'Prenotazione ricevuta!', 'en' => 'Booking received!', 'ru' => 'Бронь принята!', 'es' => '¡Reserva recibida!', 'de' => 'Buchung erhalten!'],
'transfer.success.sub'   => ['it' => 'Ti contattiamo entro pochi minuti su WhatsApp per confermare.', 'en' => 'We will contact you on WhatsApp within minutes to confirm.', 'ru' => 'Мы свяжемся с вами в WhatsApp в течение нескольких минут.', 'es' => 'Te contactaremos en WhatsApp en pocos minutos para confirmar.', 'de' => 'Wir melden uns innerhalb weniger Minuten per WhatsApp.'],
'transfer.minutes'       => ['it' => 'min',         'en' => 'min',          'ru' => 'мин',        'es' => 'min',         'de' => 'Min'],
'transfer.passengers'    => ['it' => 'pax',         'en' => 'pax',          'ru' => 'чел',        'es' => 'pax',         'de' => 'Pers'],
'transfer.see_routes'    => ['it' => 'Vedi tutte le tratte', 'en' => 'See all routes', 'ru' => 'Все направления', 'es' => 'Ver todas las rutas', 'de' => 'Alle Strecken'],

// vehicle types
'transfer.veh.sedan'   => ['it' => 'Berlina',    'en' => 'Sedan',     'ru' => 'Седан',     'es' => 'Sedán',      'de' => 'Limousine'],
'transfer.veh.minivan' => ['it' => 'Minivan',    'en' => 'Minivan',   'ru' => 'Минивэн',   'es' => 'Monovolumen','de' => 'Minivan'],
'transfer.veh.bus'     => ['it' => 'Bus',        'en' => 'Coach',     'ru' => 'Автобус',   'es' => 'Autobús',    'de' => 'Bus'],

// ========== CALENDAR ==========
'car.cal.title'    => ['it' => 'Disponibilità',  'en' => 'Availability', 'ru' => 'Доступность',     'es' => 'Disponibilidad',  'de' => 'Verfügbarkeit'],
'car.cal.subtitle' => [
    'it' => 'Tocca le date verdi per scegliere ritiro e riconsegna.',
    'en' => 'Tap the green dates to pick pick-up and drop-off.',
    'ru' => 'Нажмите на зелёные даты, чтобы выбрать выдачу и возврат.',
    'es' => 'Toca las fechas verdes para elegir recogida y devolución.',
    'de' => 'Tippe auf die grünen Tage für Abholung und Rückgabe.',
],
'car.cal.legend.free'     => ['it' => 'Disponibile',     'en' => 'Available',    'ru' => 'Свободно',         'es' => 'Disponible',     'de' => 'Verfügbar'],
'car.cal.legend.busy'     => ['it' => 'Occupato',        'en' => 'Booked',       'ru' => 'Занято',           'es' => 'Ocupado',        'de' => 'Belegt'],
'car.cal.legend.pickdrop' => ['it' => 'Ritiro/Riconsegna','en' => 'Pick-up/Drop-off','ru' => 'Выдача/Возврат', 'es' => 'Recogida/Devolución','de' => 'Abholung/Rückgabe'],
'car.cal.legend.selected' => ['it' => 'Selezionato',     'en' => 'Selected',     'ru' => 'Выбрано',          'es' => 'Seleccionado',   'de' => 'Ausgewählt'],
'car.cal.choose_drop'     => ['it' => 'Scegli riconsegna','en' => 'Pick drop-off','ru' => 'Выберите возврат','es' => 'Elige la devolución','de' => 'Rückgabe wählen'],
'car.cal.days_count'      => ['it' => 'giorni',          'en' => 'days',         'ru' => 'дней',             'es' => 'días',           'de' => 'Tage'],
'car.cal.pickup'          => ['it' => 'Ritiro',          'en' => 'Pick-up',      'ru' => 'Выдача',           'es' => 'Recogida',       'de' => 'Abholung'],

// days of week (abbr.)
'common.dow.mon' => ['it' => 'Lun', 'en' => 'Mon', 'ru' => 'Пн',  'es' => 'Lun', 'de' => 'Mo'],
'common.dow.tue' => ['it' => 'Mar', 'en' => 'Tue', 'ru' => 'Вт',  'es' => 'Mar', 'de' => 'Di'],
'common.dow.wed' => ['it' => 'Mer', 'en' => 'Wed', 'ru' => 'Ср',  'es' => 'Mié', 'de' => 'Mi'],
'common.dow.thu' => ['it' => 'Gio', 'en' => 'Thu', 'ru' => 'Чт',  'es' => 'Jue', 'de' => 'Do'],
'common.dow.fri' => ['it' => 'Ven', 'en' => 'Fri', 'ru' => 'Пт',  'es' => 'Vie', 'de' => 'Fr'],
'common.dow.sat' => ['it' => 'Sab', 'en' => 'Sat', 'ru' => 'Сб',  'es' => 'Sáb', 'de' => 'Sa'],
'common.dow.sun' => ['it' => 'Dom', 'en' => 'Sun', 'ru' => 'Вс',  'es' => 'Dom', 'de' => 'So'],

// months
'month.1'  => ['it' => 'Gennaio',   'en' => 'January',   'ru' => 'Январь',   'es' => 'Enero',      'de' => 'Januar'   ],
'month.2'  => ['it' => 'Febbraio',  'en' => 'February',  'ru' => 'Февраль',  'es' => 'Febrero',    'de' => 'Februar'  ],
'month.3'  => ['it' => 'Marzo',     'en' => 'March',     'ru' => 'Март',     'es' => 'Marzo',      'de' => 'März'     ],
'month.4'  => ['it' => 'Aprile',    'en' => 'April',     'ru' => 'Апрель',   'es' => 'Abril',      'de' => 'April'    ],
'month.5'  => ['it' => 'Maggio',    'en' => 'May',       'ru' => 'Май',      'es' => 'Mayo',       'de' => 'Mai'      ],
'month.6'  => ['it' => 'Giugno',    'en' => 'June',      'ru' => 'Июнь',     'es' => 'Junio',      'de' => 'Juni'     ],
'month.7'  => ['it' => 'Luglio',    'en' => 'July',      'ru' => 'Июль',     'es' => 'Julio',      'de' => 'Juli'     ],
'month.8'  => ['it' => 'Agosto',    'en' => 'August',    'ru' => 'Август',   'es' => 'Agosto',     'de' => 'August'   ],
'month.9'  => ['it' => 'Settembre', 'en' => 'September', 'ru' => 'Сентябрь', 'es' => 'Septiembre', 'de' => 'September'],
'month.10' => ['it' => 'Ottobre',   'en' => 'October',   'ru' => 'Октябрь',  'es' => 'Octubre',    'de' => 'Oktober'  ],
'month.11' => ['it' => 'Novembre',  'en' => 'November',  'ru' => 'Ноябрь',   'es' => 'Noviembre',  'de' => 'November' ],
'month.12' => ['it' => 'Dicembre',  'en' => 'December',  'ru' => 'Декабрь',  'es' => 'Diciembre',  'de' => 'Dezember' ],

];

function tAmenity(string $text): string {
    static $index = null;
    if ($index === null) {
        global $TRANSLATIONS;
        $index = [];
        foreach ($TRANSLATIONS as $key => $vals) {
            if (strpos($key, 'amenity.') !== 0) continue;
            $italian = $vals['it'] ?? '';
            if ($italian === '') continue;
            $index[mb_strtolower(trim($italian))] = $key;
        }
    }
    $needle = mb_strtolower(trim($text));
    if (isset($index[$needle])) return t($index[$needle]);
    return $text;
}

function tMonth(int $m): string {
    return t('month.' . max(1, min(12, $m)));
}

function t(string $key, array $vars = []): string {
    global $TRANSLATIONS;
    $lang = currentLang();
    $val = $TRANSLATIONS[$key][$lang] ?? $TRANSLATIONS[$key]['it'] ?? $key;
    foreach ($vars as $k => $v) $val = str_replace('{' . $k . '}', (string)$v, $val);
    return $val;
}
