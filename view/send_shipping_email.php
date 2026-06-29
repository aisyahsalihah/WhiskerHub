<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

$order_id = $_POST['order_id'] ?? '';
$customer_email = $_POST['email'] ?? '';
$tracking = $_POST['tracking'] ?? '';
$image_url = $_POST['image_url'] ?? '';

if (empty($order_id) || empty($customer_email) || empty($tracking)) {
    die("Error: Missing required shipping information.");
}

$subject = "Your WhiskerShop Order Has Shipped! 🚚";
$body = "
    <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; max-width: 500px;'>
        <h2 style='color: #4caf50;'>Order Shipped!</h2>
        <hr style='border: 0; border-top: 1px solid #eee;'>
        <p>Great news! The seller has shipped your order.</p>
        <p><strong>Order ID:</strong> $order_id</p>
        <p><strong>Tracking Number:</strong> <span style='background: #f1f8e9; padding: 3px 8px; border-radius: 5px; font-weight: bold;'>$tracking</span></p>
        <br>
        <p>You can view the seller's shipping proof or receipt image below:</p>
        <p><a href='$image_url' style='background: #ffb6c1; color: #333; text-decoration: none; padding: 10px 15px; border-radius: 5px; font-weight: bold; display: inline-block;' target='_blank'>View Shipping Proof</a></p>
        <br>
        <p>Thank you for shopping at WhiskerHub!</p>
    </div>
";

$sent = sendBrevoEmail($customer_email, '', $subject, $body, 'aisyahsalihah22@gmail.com', 'WhiskerShop');

if ($sent) {
    echo "Shipping email sent successfully to $customer_email.";
} else {
    http_response_code(500);
    echo "Email could not be sent. Please check Brevo API key configuration.";
}
?>
