<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

$booking_id_string = $_GET['booking_id'] ?? '';
$customer_email = $_GET['email'] ?? '';
$type = $_GET['type'] ?? 'cod'; // 'stripe' or 'cod'

if (empty($booking_id_string) || empty($customer_email)) {
    die("Error: Missing order information.");
}

// Verify Stripe Session if it's Stripe
if ($type === 'stripe') {
    $session_id = $_GET['session_id'] ?? '';
    if (empty($session_id)) {
        die("Error: Missing payment session ID.");
    }
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    try {
        $session = \Stripe\Checkout\Session::retrieve($session_id);
        if ($session->payment_status !== 'paid') {
            die("Error: Payment has not been completed.");
        }
    } catch (Exception $e) {
        die("Error verifying payment: " . htmlspecialchars($e->getMessage()));
    }
}

// Send Invoice Email
$email_status = "";

$methodText = $type === 'stripe' ? 'Paid via Credit Card' : 'Cash on Delivery (COD)';
$subject = "Your WhiskerShop Invoice!";
$body = "
    <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; max-width: 500px;'>
        <h2 style='color: #ffb6c1;'>WhiskerShop Purchase</h2>
        <hr style='border: 0; border-top: 1px solid #eee;'>
        <p>Hello! 🐾</p>
        <p>Your order has been successfully placed.</p>
        <p><strong>Order ID(s):</strong> $booking_id_string</p>
        <p><strong>Payment Method:</strong> $methodText</p>
        <br>
        <p>The seller has been notified and will prepare your order for shipping.</p>
        <p>Thank you for shopping at WhiskerHub!</p>
    </div>
";

$sent = sendBrevoEmail($customer_email, '', $subject, $body, 'no-reply@whiskerhub.com', 'WhiskerShop');
if ($sent) {
    $email_status = "Official invoice has been sent to your email ($customer_email).";
} else {
    $email_status = "Invoice email could not be sent (please check Brevo setup).";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Successful - WhiskerShop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f7f9fa; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .success-container { background: white; max-width: 450px; width: 90%; padding: 40px 30px; border-radius: 20px; text-align: center; border-top: 8px solid #ffb6c1; }
        .success-icon { font-size: 70px; color: #4caf50; margin-bottom: 20px; }
        .email-info { background: #f1f8e9; color: #33691e; padding: 12px; border-radius: 10px; font-size: 13.5px; margin-bottom: 30px; }
        .btn { display: inline-block; padding: 14px 25px; border-radius: 12px; font-size: 16px; font-weight: 600; text-decoration: none; background: #333; color: white; margin-top: 10px; }
    </style>
</head>
<body>

<div class="success-container">
    <div class="success-icon"><i class="fa-solid fa-bag-shopping"></i></div>
    <h1>ORDER PLACED!</h1>
    
    <div class="email-info"><i class="fa-solid fa-envelope-open-text"></i> <?php echo htmlspecialchars($email_status); ?></div>
    
    <p>We've received your order and the seller is working on it.</p>
    
    <a href="myorders.php" class="btn">View My Orders</a>
</div>

<script type="module">
import { db } from "../js/firebase.js";
import { doc, updateDoc } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";

const orderIdsStr = "<?php echo $booking_id_string; ?>";
const paymentType = "<?php echo $type; ?>";

async function processOrders() {
    if (paymentType === 'stripe') {
        const orderIds = orderIdsStr.split(",");
        for (const id of orderIds) {
            if (id.trim() !== '') {
                try {
                    await updateDoc(doc(db, "pesanan", id.trim()), {
                        fld_status: "Paid"
                    });
                } catch(e) {
                    console.error("Error updating order status:", e);
                }
            }
        }
    }
}

processOrders();
</script>

</body>
</html>
