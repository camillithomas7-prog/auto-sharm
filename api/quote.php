<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/utils.php';
require_once __DIR__ . '/../lib/pricing.php';

$body = json_decode(file_get_contents('php://input'), true) ?: [];
$carId = $body['car_id'] ?? '';
$from = $body['from'] ?? '';
$to = $body['to'] ?? '';
$coupon = $body['coupon'] ?? null;

$car = row('SELECT * FROM cars WHERE id = ?', [$carId]);
if (!$car) { http_response_code(404); echo json_encode(['error' => 'Auto non trovata']); exit; }

if (!$from || !$to || strtotime($to) <= strtotime($from)) {
    echo json_encode(['days' => 0, 'subtotal' => 0, 'discount' => 0, 'total' => 0]);
    exit;
}

echo json_encode(quoteRental($car, $from, $to, $coupon));
