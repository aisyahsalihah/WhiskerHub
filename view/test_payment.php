<?php
// Fail sementara untuk menguji sama ada kod terkini telah dikemas kini pada server
$booking_id = 'test';
$customer_email = 'test@example.com';
$type = 'booking';
$success_url = $type === 'shop' ? "https://whiskerhub.tech/view/success_shop.php?booking_id=$booking_id&email=$customer_email&session_id={CHECKOUT_SESSION_ID}&type=stripe" : "https://whiskerhub.tech/view/success.php?booking_id=$booking_id&email=$customer_email&session_id={CHECKOUT_SESSION_ID}";
echo "Success URL yang sedang aktif di Server: " . htmlspecialchars($success_url);
