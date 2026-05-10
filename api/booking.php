<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/utils.php';
require_once __DIR__ . '/../lib/pricing.php';

$body = json_decode(file_get_contents('php://input'), true) ?: [];

$required = ['car_id','from','to','name','email','phone'];
foreach ($required as $r) {
    if (empty($body[$r])) { http_response_code(422); echo json_encode(['error' => "Campo mancante: $r"]); exit; }
}

$car = row('SELECT * FROM cars WHERE id = ? AND active = 1', [$body['car_id']]);
if (!$car) { http_response_code(404); echo json_encode(['error' => 'Auto non disponibile']); exit; }

$from = $body['from']; $to = $body['to'];
if (strtotime($to) <= strtotime($from)) { http_response_code(422); echo json_encode(['error' => 'Date non valide']); exit; }

// Sovrapposizione con altre prenotazioni confermate
$conflict = row(
    "SELECT id FROM bookings WHERE car_id = ? AND status NOT IN ('cancelled','rejected')
     AND NOT (dropoff_date <= ? OR pickup_date >= ?) LIMIT 1",
    [$car['id'], $from, $to]
);
if ($conflict) { http_response_code(409); echo json_encode(['error' => 'Auto già prenotata in queste date']); exit; }

$q = quoteRental($car, $from, $to, $body['coupon'] ?? null);

// Customer (find or create by email/phone)
$cust = null;
if (!empty($body['email'])) $cust = row('SELECT * FROM customers WHERE email = ?', [$body['email']]);
if (!$cust && !empty($body['phone'])) $cust = row('SELECT * FROM customers WHERE phone = ?', [$body['phone']]);
if (!$cust) {
    $cid = newId();
    q('INSERT INTO customers (id, name, email, phone, notes) VALUES (?, ?, ?, ?, ?)',
        [$cid, $body['name'], $body['email'] ?? null, $body['phone'] ?? null, $body['notes'] ?? null]);
} else {
    $cid = $cust['id'];
}

$bid = newId();
$seq = (int)val('SELECT COUNT(*) FROM bookings') + 1;
$code = bookingCode($seq);
q('INSERT INTO bookings (id, code, car_id, customer_id, pickup_date, dropoff_date, pickup_location, days, status, source, subtotal, discount, total, currency, notes, coupon_code) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
    [$bid, $code, $car['id'], $cid, $from, $to, $body['pickup'] ?? '', $q['days'], 'pending', 'web', $q['subtotal'], $q['discount'], $q['total'], cfg('site.currency') ?: 'EUR', $body['notes'] ?? null, $body['coupon'] ?? null]);

// Notifica
try {
    q('INSERT INTO notifications (id, type, title, body, link) VALUES (?, ?, ?, ?, ?)',
        [newId(), 'new_booking', 'Nuova prenotazione: ' . $car['name'], 'Cliente: ' . $body['name'] . ' · ' . $q['days'] . ' giorni · ' . fmtMoney((float)$q['total']), '/admin/prenotazione.php?id=' . $bid]);
} catch (Throwable $e) {}

echo json_encode(['code' => $code, 'id' => $bid, 'total' => $q['total']]);
