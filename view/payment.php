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
$success_url = $type === 'shop' ? "https://168.144.99.134/view/success_shop.php?booking_id=$booking_id&email=$customer_email&session_id={CHECKOUT_SESSION_ID}&type=stripe" : "https://168.144.99.134/view/success.php?booking_id=$booking_id&email=$customer_email&session_id={CHECKOUT_SESSION_ID}";
$cancel_url = $type === 'shop' ? "https://168.144.99.134/view/myorders.php" : "https://168.144.99.134/view/booking.php";

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