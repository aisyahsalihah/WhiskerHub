<?php
// The ../ means "go up one folder"
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

// Put your SECRET key here
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Ambil data dari URL
$booking_id = $_GET['booking_id'] ?? 'unknown';
$amount_rm = $_GET['amount'] ?? 0;
$amount_cents = $amount_rm * 100; // Stripe guna sen
$customer_email = $_GET['email'] ?? '';
$type = $_GET['type'] ?? 'booking'; // 'booking' or 'shop'

$product_name = $type === 'shop' ? "WhiskerShop Order #$booking_id" : "WhiskerHub Booking #$booking_id";
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$current_dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$base_url = $protocol . $host . $current_dir . "/";

$success_url = $type === 'shop' ? $base_url . "success_shop.php?booking_id=$booking_id&email=$customer_email&session_id={CHECKOUT_SESSION_ID}&type=stripe" : $base_url . "success.php?booking_id=$booking_id&email=$customer_email&session_id={CHECKOUT_SESSION_ID}";
$cancel_url = $type === 'shop' ? $base_url . "myorders.php" : $base_url . "booking.php";

try {
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'myr',
                'product_data' => [
                    'name' => $product_name,
                ],
                'unit_amount' => $amount_cents,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => $success_url,
        'cancel_url' => $cancel_url,
    ]);

    header("Location: " . $session->url);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}