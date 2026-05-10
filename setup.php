<?php
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';

header('Content-Type: text/plain; charset=utf-8');

$pdo = db();
$reset = isset($_GET['reset']) && $_GET['reset'] === 'YES';
$driver = dbDriver();

// Schema definito una volta usando un mini-DSL: la funzione lo traduce in
// SQL SQLite o MySQL.
$tables = [
    'users' => [
        'id PK',
        'email TEXT UNIQUE NOT NULL',
        'password TEXT NOT NULL',
        'name TEXT',
        'role TEXT NOT NULL DEFAULT \'admin\'',
        'created_at TS',
    ],
    'cars' => [
        'id PK',
        'slug TEXT UNIQUE NOT NULL',
        'name TEXT NOT NULL',
        'brand TEXT DEFAULT \'\'',
        'model TEXT DEFAULT \'\'',
        'description TEXT',
        'category TEXT DEFAULT \'compact\'',     // economy, compact, suv, luxury, minivan
        'transmission TEXT DEFAULT \'manual\'',  // manual, automatic
        'fuel TEXT DEFAULT \'petrol\'',          // petrol, diesel, hybrid, electric
        'seats INT DEFAULT 5',
        'doors INT DEFAULT 4',
        'luggage INT DEFAULT 2',
        'year INT',
        'features TEXT',                         // JSON array
        'daily_price NUM DEFAULT 25',
        'weekly_price NUM',
        'biweekly_price NUM',
        'monthly_price NUM',
        'security_deposit NUM DEFAULT 0',
        'license_required INT DEFAULT 1',
        'min_age INT DEFAULT 21',
        'manager_commission_pct NUM DEFAULT 20',
        'owner_name TEXT DEFAULT \'\'',
        'cover_image TEXT',
        'active INT DEFAULT 1',
        'position INT DEFAULT 0',
        'created_at TS',
        'updated_at TS',
    ],
    'photos' => [
        'id PK',
        'car_id TEXT NOT NULL',
        'url TEXT NOT NULL',
        'alt TEXT DEFAULT \'\'',
        'position INT DEFAULT 0',
    ],
    'customers' => [
        'id PK',
        'name TEXT NOT NULL',
        'email TEXT',
        'phone TEXT',
        'document TEXT',
        'license_number TEXT',
        'country TEXT',
        'notes TEXT',
        'created_at TS',
    ],
    'bookings' => [
        'id PK',
        'code TEXT UNIQUE NOT NULL',
        'car_id TEXT NOT NULL',
        'customer_id TEXT NOT NULL',
        'pickup_date DATE NOT NULL',
        'dropoff_date DATE NOT NULL',
        'pickup_location TEXT DEFAULT \'\'',
        'dropoff_location TEXT DEFAULT \'\'',
        'days INT DEFAULT 1',
        'status TEXT DEFAULT \'pending\'',
        'source TEXT DEFAULT \'direct\'',
        'subtotal NUM DEFAULT 0',
        'discount NUM DEFAULT 0',
        'total NUM DEFAULT 0',
        'paid NUM DEFAULT 0',
        'currency TEXT DEFAULT \'EUR\'',
        'notes TEXT',
        'coupon_code TEXT',
        'created_at TS',
        'updated_at TS',
    ],
    'date_blocks' => [
        'id PK',
        'car_id TEXT NOT NULL',
        'start_date DATE NOT NULL',
        'end_date DATE NOT NULL',
        'reason TEXT',
        'created_at TS',
    ],
    'expenses' => [
        'id PK',
        'car_id TEXT',
        'category TEXT NOT NULL',
        'amount NUM NOT NULL',
        'date DATE NOT NULL',
        'description TEXT',
        'created_at TS',
    ],
    'reviews' => [
        'id PK',
        'car_id TEXT NOT NULL',
        'author_name TEXT',
        'rating INT NOT NULL',
        'title TEXT',
        'body TEXT',
        'approved INT DEFAULT 1',
        'created_at TS',
    ],
    'coupons' => [
        'id PK',
        'code TEXT UNIQUE NOT NULL',
        'percent NUM NOT NULL',
        'active INT DEFAULT 1',
        'expires_at DATE',
        'created_at TS',
    ],
    'notifications' => [
        'id PK',
        'type TEXT NOT NULL',
        'title TEXT',
        'body TEXT',
        'link TEXT',
        'read_at TS NULL',
        'created_at TS',
    ],
    'activity_logs' => [
        'id PK',
        'user_id TEXT',
        'action TEXT NOT NULL',
        'entity TEXT',
        'entity_id TEXT',
        'detail TEXT',
        'created_at TS',
    ],
    'settings' => [
        'setting_key TEXT PRIMARY KEY',
        'setting_value TEXT',
    ],
    'message_templates' => [
        'id PK',
        'template_key TEXT UNIQUE NOT NULL',
        'name TEXT',
        'channel TEXT DEFAULT \'whatsapp\'',
        'subject TEXT',
        'body TEXT',
        'active INT DEFAULT 1',
        'created_at TS',
    ],
    'transfers' => [
        'id PK',
        'slug TEXT UNIQUE NOT NULL',
        'name TEXT NOT NULL',
        'description TEXT',
        'from_location TEXT NOT NULL',
        'to_location TEXT NOT NULL',
        'vehicle_type TEXT DEFAULT \'sedan\'',  // sedan, minivan, bus
        'vehicle_capacity INT DEFAULT 4',
        'duration_min INT',
        'price NUM NOT NULL DEFAULT 25',
        'cover_image TEXT',
        'manager_commission_pct NUM DEFAULT 20',
        'active INT DEFAULT 1',
        'position INT DEFAULT 0',
        'created_at TS',
        'updated_at TS',
    ],
    'transfer_bookings' => [
        'id PK',
        'code TEXT UNIQUE NOT NULL',
        'transfer_id TEXT NOT NULL',
        'customer_id TEXT NOT NULL',
        'arrival_date DATE NOT NULL',
        'arrival_time TEXT',
        'flight_number TEXT',
        'destination TEXT',
        'passengers INT DEFAULT 1',
        'status TEXT DEFAULT \'pending\'',
        'total NUM DEFAULT 0',
        'paid NUM DEFAULT 0',
        'currency TEXT DEFAULT \'EUR\'',
        'notes TEXT',
        'created_at TS',
    ],
];

function ddlFor(string $table, array $cols, string $driver): string {
    $lines = [];
    foreach ($cols as $c) {
        if (preg_match('/^id PK$/', $c)) {
            $lines[] = $driver === 'mysql' ? 'id VARCHAR(32) PRIMARY KEY' : 'id TEXT PRIMARY KEY';
            continue;
        }
        // sostituisce TS → tipo timestamp con default now
        $c = preg_replace_callback('/\bTS(\s+NULL)?\b/', function($m) use ($driver) {
            $null = $m[1] ?? '';
            if ($null) return $driver === 'mysql' ? 'DATETIME NULL DEFAULT NULL' : 'TEXT';
            return $driver === 'mysql' ? 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP' : 'TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP';
        }, $c);
        // NUM → DECIMAL/REAL
        $c = preg_replace('/\bNUM\b/', $driver === 'mysql' ? 'DECIMAL(10,2)' : 'REAL', $c);
        // INT → INT
        $c = preg_replace('/\bINT\b/', 'INTEGER', $c);
        // DATE
        $c = preg_replace('/\bDATE\b/', $driver === 'mysql' ? 'DATE' : 'TEXT', $c);
        // MySQL non supporta TEXT come UNIQUE/PRIMARY KEY senza key length: usa VARCHAR(190)
        if ($driver === 'mysql' && preg_match('/\b(UNIQUE|PRIMARY KEY)\b/i', $c)) {
            $c = preg_replace('/\bTEXT\b/', 'VARCHAR(190)', $c);
        }
        $lines[] = $c;
    }
    $sql = "CREATE TABLE IF NOT EXISTS `$table` (\n  " . implode(",\n  ", $lines) . "\n)";
    if ($driver === 'mysql') $sql .= ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
    return $sql;
}

foreach ($tables as $name => $cols) {
    $pdo->exec(ddlFor($name, $cols, $driver));
}
echo "✓ Schema creato/verificato\n";

if ($reset) {
    foreach (array_keys($tables) as $t) {
        try { $pdo->exec("DELETE FROM `$t`"); } catch (Throwable $e) {}
    }
    echo "✓ Dati cancellati\n";
}

ensureAdminUser();
echo "✓ Utente admin: " . cfg('admin_default.email') . " / " . cfg('admin_default.password') . "\n";

// Seed flotta demo (idempotente: solo se non ci sono auto)
if ((int)val('SELECT COUNT(*) FROM cars') === 0) {
    $cars = [
        [
            'name' => 'Hyundai i10', 'brand' => 'Hyundai', 'model' => 'i10',
            'description' => "Compatta perfetta per la città e gli spostamenti brevi a Sharm. Bassi consumi, parcheggio facile, aria condizionata sempre forte.",
            'category' => 'economy', 'transmission' => 'manual', 'fuel' => 'petrol',
            'seats' => 4, 'doors' => 5, 'luggage' => 1, 'year' => 2024,
            'daily_price' => 22, 'weekly_price' => 130, 'biweekly_price' => 240, 'monthly_price' => 460,
            'security_deposit' => 100, 'min_age' => 21,
            'features' => ['Aria condizionata', 'Bluetooth', 'USB', 'Km illimitati'],
            'cover_image' => '/assets/car-i10.jpg',
        ],
        [
            'name' => 'Toyota Corolla', 'brand' => 'Toyota', 'model' => 'Corolla',
            'description' => "Berlina affidabile e comoda anche per i lunghi tragitti verso Dahab o Ras Mohammed. Cambio automatico, cruise control, telecamera posteriore.",
            'category' => 'compact', 'transmission' => 'automatic', 'fuel' => 'petrol',
            'seats' => 5, 'doors' => 4, 'luggage' => 3, 'year' => 2024,
            'daily_price' => 38, 'weekly_price' => 230, 'biweekly_price' => 430, 'monthly_price' => 820,
            'security_deposit' => 200, 'min_age' => 23,
            'features' => ['Aria condizionata', 'Bluetooth', 'GPS', 'Cruise control', 'Telecamera posteriore', 'Km illimitati'],
            'cover_image' => '/assets/car-corolla.jpg',
        ],
        [
            'name' => 'Jeep Wrangler', 'brand' => 'Jeep', 'model' => 'Wrangler',
            'description' => "L'icona 4×4 per chi vuole esplorare il deserto del Sinai. Cambio automatico, trazione integrale, tetto rimovibile per le foto al tramonto.",
            'category' => 'suv', 'transmission' => 'automatic', 'fuel' => 'petrol',
            'seats' => 5, 'doors' => 4, 'luggage' => 3, 'year' => 2023,
            'daily_price' => 75, 'weekly_price' => 470, 'biweekly_price' => 880, 'monthly_price' => 1750,
            'security_deposit' => 500, 'min_age' => 25,
            'features' => ['Aria condizionata', '4×4', 'Bluetooth', 'GPS', 'Cruise control', 'Km illimitati'],
            'cover_image' => '/assets/car-wrangler.jpg',
        ],
        [
            'name' => 'Mercedes-Benz E-Class', 'brand' => 'Mercedes-Benz', 'model' => 'E-Class',
            'description' => "Lusso silenzioso per business o cene importanti. Interni in pelle, sensori di parcheggio, cruise control adattivo.",
            'category' => 'luxury', 'transmission' => 'automatic', 'fuel' => 'diesel',
            'seats' => 5, 'doors' => 4, 'luggage' => 3, 'year' => 2024,
            'daily_price' => 130, 'weekly_price' => 820, 'biweekly_price' => 1550, 'monthly_price' => 3000,
            'security_deposit' => 800, 'min_age' => 25,
            'features' => ['Aria condizionata', 'Bluetooth', 'GPS', 'Cruise control', 'Sensori parcheggio', 'Telecamera posteriore', 'Km illimitati'],
            'cover_image' => '/assets/car-eclass.jpg',
        ],
        [
            'name' => 'Hyundai H1', 'brand' => 'Hyundai', 'model' => 'H1',
            'description' => "9 posti per gruppi, famiglie e team trip. Spazio enorme per bagagli e valigie. Aria condizionata anche per i posti posteriori.",
            'category' => 'minivan', 'transmission' => 'manual', 'fuel' => 'diesel',
            'seats' => 9, 'doors' => 5, 'luggage' => 6, 'year' => 2023,
            'daily_price' => 70, 'weekly_price' => 440, 'biweekly_price' => 820, 'monthly_price' => 1600,
            'security_deposit' => 400, 'min_age' => 25,
            'features' => ['Aria condizionata', 'Bluetooth', 'USB', 'Km illimitati'],
            'cover_image' => '/assets/car-h1.jpg',
        ],
    ];
    $pos = 1;
    foreach ($cars as $c) {
        $features = json_encode($c['features']);
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($c['name']));
        q('INSERT INTO cars (id, slug, name, brand, model, description, category, transmission, fuel, seats, doors, luggage, year, features, daily_price, weekly_price, biweekly_price, monthly_price, security_deposit, min_age, cover_image, active, position) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,?)',
            [newId(), $slug, $c['name'], $c['brand'], $c['model'], $c['description'], $c['category'], $c['transmission'], $c['fuel'], $c['seats'], $c['doors'], $c['luggage'], $c['year'], $features, $c['daily_price'], $c['weekly_price'] ?? null, $c['biweekly_price'] ?? null, $c['monthly_price'] ?? null, $c['security_deposit'] ?? 0, $c['min_age'] ?? 21, $c['cover_image'] ?? null, $pos++]);
    }
    echo "✓ Flotta demo: " . count($cars) . " auto inserite\n";
}

// Coupon demo
if ((int)val('SELECT COUNT(*) FROM coupons') === 0) {
    q('INSERT INTO coupons (id, code, percent, active) VALUES (?, ?, ?, 1)', [newId(), 'SHARM10', 10]);
    echo "✓ Coupon demo: SHARM10 (10% di sconto)\n";
}

// Seed transfer demo (idempotente)
if ((int)val('SELECT COUNT(*) FROM transfers') === 0) {
    $transfers = [
        [
            'name' => 'Aeroporto SSH → Sharm centro',
            'description' => "Trasferimento privato dall'aeroporto di Sharm El Sheikh al centro città. Autista in attesa con cartello, 60 minuti di sosta gratuiti.",
            'from_location' => 'Aeroporto SSH', 'to_location' => 'Sharm El Maya',
            'vehicle_type' => 'sedan', 'vehicle_capacity' => 3, 'duration_min' => 20,
            'price' => 25, 'cover_image' => '/assets/transfer-sedan.jpg',
        ],
        [
            'name' => 'Aeroporto SSH → Naama Bay',
            'description' => "Tratta più richiesta: dall'aeroporto a tutti gli hotel della Baia di Naama in 25 minuti.",
            'from_location' => 'Aeroporto SSH', 'to_location' => 'Naama Bay',
            'vehicle_type' => 'sedan', 'vehicle_capacity' => 3, 'duration_min' => 25,
            'price' => 30, 'cover_image' => '/assets/transfer-sedan.jpg',
        ],
        [
            'name' => 'Aeroporto SSH → Nabq Bay',
            'description' => "Resort di Nabq, Hilton Sharks Bay, Sunrise Arabian Beach: ti portiamo direttamente alla reception del tuo hotel.",
            'from_location' => 'Aeroporto SSH', 'to_location' => 'Nabq Bay',
            'vehicle_type' => 'minivan', 'vehicle_capacity' => 7, 'duration_min' => 30,
            'price' => 40, 'cover_image' => '/assets/transfer-minivan.jpg',
        ],
        [
            'name' => 'Aeroporto SSH → Dahab',
            'description' => "Transfer privato fino a Dahab (1h 20'), incluso checkpoint del Sinai. Bottiglia d'acqua omaggio a bordo.",
            'from_location' => 'Aeroporto SSH', 'to_location' => 'Dahab',
            'vehicle_type' => 'minivan', 'vehicle_capacity' => 7, 'duration_min' => 80,
            'price' => 95, 'cover_image' => '/assets/transfer-minivan.jpg',
        ],
    ];
    $pos = 1;
    foreach ($transfers as $t) {
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($t['from_location'] . '-' . $t['to_location']));
        $slug = trim($slug, '-');
        q('INSERT INTO transfers (id, slug, name, description, from_location, to_location, vehicle_type, vehicle_capacity, duration_min, price, cover_image, active, position) VALUES (?,?,?,?,?,?,?,?,?,?,?,1,?)',
            [newId(), $slug, $t['name'], $t['description'], $t['from_location'], $t['to_location'], $t['vehicle_type'], $t['vehicle_capacity'], $t['duration_min'], $t['price'], $t['cover_image'] ?? null, $pos++]);
    }
    echo "✓ Transfer demo: " . count($transfers) . " tratte inserite\n";
}

echo "\n✅ Setup completato.\n";
echo "   → Login admin: " . cfg('admin_default.email') . "\n";
echo "   → Password: " . cfg('admin_default.password') . "\n";
echo "   → Apri: " . cfg('site.url') . "\n";
