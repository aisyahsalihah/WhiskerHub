<?php
// Fail sementara untuk menguji sama ada kod terkini telah dikemas kini pada server
$booking_id = 'test';
$customer_email = 'test@example.com';
$type = 'booking';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$current_dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$base_url = $protocol . $host . $current_dir . "/";

$success_url = $type === 'shop' ? $base_url . "success_shop.php?booking_id=$booking_id&email=$customer_email&session_id={CHECKOUT_SESSION_ID}&type=stripe" : $base_url . "success.php?booking_id=$booking_id&email=$customer_email&session_id={CHECKOUT_SESSION_ID}";
echo "Success URL yang sedang aktif di Server: " . htmlspecialchars($success_url);
