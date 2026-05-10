<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/utils.php';

if (!featureEnabled('transfer')) { http_response_code(404); echo json_encode(['error' => 'Sezione non attiva']); exit; }

$body = json_decode(file_get_contents('php://input'), true) ?: [];

$required = ['transfer_id','arrival_date','passengers','name','email','phone'];
foreach ($required as $r) {
    if (empty($body[$r])) { http_response_code(422); echo json_encode(['error' => "Campo mancante: $r"]); exit; }
}

$tr = row('SELECT * FROM transfers WHERE id = ? AND active = 1', [$body['transfer_id']]);
if (!$tr) { http_response_code(404); echo json_encode(['error' => 'Tratta non disponibile']); exit; }

$pax = max(1, (int)$body['passengers']);
if ($pax > (int)$tr['vehicle_capacity']) {
    http_response_code(422);
    echo json_encode(['error' => 'Passeggeri superiori alla capienza del veicolo (max ' . (int)$tr['vehicle_capacity'] . ')']);
    exit;
}

if (!strtotime($body['arrival_date'])) { http_response_code(422); echo json_encode(['error' => 'Data non valida']); exit; }

// Customer (find or create)
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
$seq = (int)val('SELECT COUNT(*) FROM transfer_bookings') + 1;
$code = 'TR-' . date('Y') . '-' . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
$total = (float)$tr['price'];

q('INSERT INTO transfer_bookings (id, code, transfer_id, customer_id, arrival_date, arrival_time, flight_number, destination, passengers, status, total, paid, currency, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
    [$bid, $code, $tr['id'], $cid, $body['arrival_date'], $body['arrival_time'] ?? null, $body['flight_number'] ?? null, $body['destination'] ?? null, $pax, 'pending', $total, 0, cfg('site.currency') ?: 'EUR', $body['notes'] ?? null]);

try {
    q('INSERT INTO notifications (id, type, title, body, link) VALUES (?, ?, ?, ?, ?)',
        [newId(), 'new_transfer', 'Nuovo transfer: ' . $tr['from_location'] . ' → ' . $tr['to_location'], 'Cliente: ' . $body['name'] . ' · ' . fmtDate($body['arrival_date']) . ' ' . ($body['arrival_time'] ?? '') . ' · ' . fmtMoney($total), '/admin/transfer-booking.php?id=' . $bid]);
} catch (Throwable $e) {}

echo json_encode(['code' => $code, 'id' => $bid, 'total' => $total]);
