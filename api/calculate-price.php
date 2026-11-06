<?php
/**
 * Bella Vista Oasis Spa - AJAX Endpoint: Calculate Booking Price
 * 
 * Computes base price, extra guest charges (for family lounge up to 6 guests),
 * and promo code discounts.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$serviceId = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
$guestCount = isset($_GET['guest_count']) ? (int)$_GET['guest_count'] : 1;
$promoCode = trim($_GET['promo_code'] ?? '');

if (!$serviceId) {
    json_response(['success' => false, 'message' => 'Please select a service.'], 400);
}

try {
    $pricing = calculate_total_amount($serviceId, $guestCount);
    $subtotal = $pricing['total_amount'];
    $discount = 0.00;
    $promoApplied = null;

    if (!empty($promoCode)) {
        $pdo = get_db_connection();
        $today = date('Y-m-d');
        $promoStmt = $pdo->prepare("SELECT * FROM promotions 
                                    WHERE promo_code = ? 
                                      AND is_active = 1 
                                      AND start_date <= ? 
                                      AND end_date >= ? 
                                      AND (max_uses = 0 OR current_uses < max_uses)
                                    LIMIT 1");
        $promoStmt->execute([strtoupper($promoCode), $today, $today]);
        $promo = $promoStmt->fetch();

        if ($promo) {
            $minAmount = (float)$promo['min_booking_amount'];
            if ($subtotal >= $minAmount) {
                if ($promo['discount_type'] === 'percentage') {
                    $discount = ($subtotal * (float)$promo['discount_value']) / 100.0;
                } else {
                    $discount = min($subtotal, (float)$promo['discount_value']);
                }
                $promoApplied = [
                    'code'          => $promo['promo_code'],
                    'title'         => $promo['title'],
                    'discount_type' => $promo['discount_type'],
                    'discount_val'  => (float)$promo['discount_value'],
                    'discount_amt'  => $discount
                ];
            }
        }
    }

    $finalTotal = max(0.00, $subtotal - $discount);

    json_response([
        'success'                  => true,
        'service_id'               => $pricing['service_id'],
        'service_name'             => $pricing['service_name'],
        'room_type'                => $pricing['room_type'],
        'guest_count'              => $pricing['guest_count'],
        'base_price'               => $pricing['base_price'],
        'additional_guest_fee'     => $pricing['additional_guest_fee'],
        'additional_guest_charges' => $pricing['additional_guest_charges'],
        'subtotal'                 => $subtotal,
        'discount'                 => $discount,
        'promo_applied'            => $promoApplied,
        'total_amount'             => $finalTotal,
        'formatted_total'          => format_currency($finalTotal)
    ]);
} catch (Exception $e) {
    json_response(['success' => false, 'message' => $e->getMessage()], 400);
}
