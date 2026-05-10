<?php
/**
 * Engine prezzi auto-sharm.
 * Calcola il totale di un noleggio scegliendo lo scaglione migliore (giorno/
 * settimana/2 sett/mensile) per il numero di giorni richiesto, applica un
 * coupon % se presente.
 */
function quoteRental(array $car, string $from, string $to, ?string $couponCode = null): array {
    $days = max(1, daysBetween($from, $to));
    $daily = (float)($car['daily_price'] ?? 0);
    $weekly = (float)($car['weekly_price'] ?? 0);
    $biweekly = (float)($car['biweekly_price'] ?? 0);
    $monthly = (float)($car['monthly_price'] ?? 0);

    // Calcolo "best fit" combinando blocchi: usa il prezzo per lo scaglione più
    // vicino. Se l'utente prende 10 giorni e c'è il prezzo settimanale, si usa
    // 1×weekly + 3×daily.
    $remaining = $days;
    $sub = 0;
    if ($monthly > 0 && $remaining >= 28) {
        $blocks = intdiv($remaining, 28);
        $sub += $blocks * $monthly;
        $remaining -= $blocks * 28;
    }
    if ($biweekly > 0 && $remaining >= 14) {
        $blocks = intdiv($remaining, 14);
        $sub += $blocks * $biweekly;
        $remaining -= $blocks * 14;
    }
    if ($weekly > 0 && $remaining >= 7) {
        $blocks = intdiv($remaining, 7);
        $sub += $blocks * $weekly;
        $remaining -= $blocks * 7;
    }
    $sub += $remaining * $daily;

    // Fallback: se non ci sono altri scaglioni, usa solo il giornaliero.
    if ($sub <= 0) $sub = $days * $daily;

    $discount = 0; $couponLabel = '';
    if ($couponCode) {
        $c = row('SELECT * FROM coupons WHERE code = ? AND active = 1', [strtoupper($couponCode)]);
        if ($c) {
            $pct = (float)$c['percent'];
            $discount = round($sub * $pct / 100, 2);
            $couponLabel = "Coupon $pct%";
        }
    }

    $total = max(0, $sub - $discount);
    return [
        'days' => $days,
        'subtotal' => round($sub, 2),
        'discount' => $discount,
        'discountLabel' => $couponLabel,
        'total' => round($total, 2),
    ];
}
