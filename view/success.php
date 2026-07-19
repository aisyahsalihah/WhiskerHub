<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

// Verify Stripe Session First
$session_id = $_GET['session_id'] ?? '';
if (empty($session_id)) {
    die("Error: Missing payment session ID. Payment cannot be verified.");
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

try {
    $session = \Stripe\Checkout\Session::retrieve($session_id);
    if ($session->payment_status !== 'paid') {
        die("Error: Payment has not been completed. Status: " . htmlspecialchars($session->payment_status));
    }
} catch (Exception $e) {
    die("Error verifying payment: " . htmlspecialchars($e->getMessage()));
}

$booking_id = $_GET['booking_id'] ?? 'unknown';
$customer_email = $_GET['email'] ?? '';

// Email Sending Logic
$email_status = "";

// Helper functions to fetch Firestore data via REST API
if (!function_exists('getFirestoreDoc')) {
    function getFirestoreDoc($collection, $docId) {
        $url = "https://firestore.googleapis.com/v1/projects/whiskerhub-67889/databases/(default)/documents/" . urlencode($collection) . "/" . urlencode($docId);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            $json = json_decode($response, true);
            $fields = [];
            if (isset($json['fields'])) {
                foreach ($json['fields'] as $key => $val) {
                    if (isset($val['stringValue'])) {
                        $fields[$key] = $val['stringValue'];
                    } elseif (isset($val['integerValue'])) {
                        $fields[$key] = (int)$val['integerValue'];
                    } elseif (isset($val['doubleValue'])) {
                        $fields[$key] = (double)$val['doubleValue'];
                    } elseif (isset($val['booleanValue'])) {
                        $fields[$key] = (bool)$val['booleanValue'];
                    }
                }
            }
            return $fields;
        }
        return null;
    }
}

if ($booking_id !== 'unknown' && !empty($customer_email)) {
    // 1. Fetch details from Firestore REST API
    $bookingData = getFirestoreDoc("tempahan", $booking_id);
    if ($bookingData) {
        $sitter_id = $bookingData['fld_penjaga_ID'] ?? '';
        $owner_id = $bookingData['fld_pemilik_ID'] ?? '';
        
        $sitterData = $sitter_id ? getFirestoreDoc("penjaga_kucing", $sitter_id) : null;
        $ownerData = $owner_id ? getFirestoreDoc("pengguna", $owner_id) : null;
        
        $sitter_name = $sitterData['fld_user_fullname'] ?? 'Cat Sitter';
        $sitter_mail = $sitterData['fld_user_email'] ?? '';
        $owner_name = $ownerData['fld_user_name'] ?? 'Cat Owner';
        
        $service = ucfirst(str_replace('_', ' ', $bookingData['fld_tempahan_servis'] ?? ''));
        $start_date = $bookingData['fld_tempahan_tkhMula'] ?? '-';
        $start_time = $bookingData['fld_tempahan_masaMula'] ?? '-';
        $end_date = $bookingData['fld_tempahan_tkhTamat'] ?? '-';
        $end_time = $bookingData['fld_tempahan_masaTamat'] ?? '-';
        $cats = $bookingData['fld_tempahan_bilKucing'] ?? 1;
        $location = $bookingData['fld_tempahan_alamat'] ?? '-';
        $amount = number_format($bookingData['fld_tempahan_jumlah'] ?? 0, 2);

        // 2. Format English Invoice Email for Cat Owner
        $subject_owner = "WhiskerHub Invoice - Booking #$booking_id";
        $body_owner = "
            <div style='font-family: \"Segoe UI\", Arial, sans-serif; padding: 30px; background-color: #f7f9fa; max-width: 600px; margin: auto; border-radius: 12px;'>
                <div style='text-align: center; margin-bottom: 20px;'>
                    <h2 style='color: #ff9800; font-size: 28px; margin: 0; font-weight: 800;'>🐾 WHISKERHUB</h2>
                    <p style='color: #777; margin: 5px 0 0 0; font-size: 14px;'>Booking Payment Invoice</p>
                </div>
                <div style='background: white; padding: 25px; border-radius: 10px; border-top: 5px solid #ff9800;'>
                    <p style='font-size: 16px; color: #333; margin-top: 0;'>Hello, <strong>$owner_name</strong>! Your booking has been successfully confirmed and paid.</p>
                    
                    <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                        <tr>
                            <td style='padding: 8px 0; color: #777; font-size: 14px;'>Booking ID:</td>
                            <td style='padding: 8px 0; font-weight: bold; color: #333; text-align: right;'>#$booking_id</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #777; font-size: 14px;'>Cat Sitter:</td>
                            <td style='padding: 8px 0; font-weight: bold; color: #333; text-align: right;'>$sitter_name</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #777; font-size: 14px;'>Service Type:</td>
                            <td style='padding: 8px 0; font-weight: bold; color: #333; text-align: right;'>$service</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #777; font-size: 14px;'>Dates:</td>
                            <td style='padding: 8px 0; font-weight: bold; color: #333; text-align: right;'>$start_date to $end_date</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #777; font-size: 14px;'>Start Time:</td>
                            <td style='padding: 8px 0; font-weight: bold; color: #333; text-align: right;'>$start_time</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #777; font-size: 14px;'>Number of Cats:</td>
                            <td style='padding: 8px 0; font-weight: bold; color: #333; text-align: right;'>$cats cat(s)</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #777; font-size: 14px;'>Care Address:</td>
                            <td style='padding: 8px 0; font-weight: bold; color: #333; text-align: right;'>$location</td>
                        </tr>
                        <tr style='border-top: 1px solid #eee;'>
                            <td style='padding: 15px 0 0 0; font-size: 16px; font-weight: bold; color: #ff9800;'>Total Amount Paid:</td>
                            <td style='padding: 15px 0 0 0; font-size: 18px; font-weight: bold; color: #333; text-align: right;'>RM $amount</td>
                        </tr>
                    </table>
                    
                    <p style='font-size: 14px; color: #666;'>You can contact your sitter directly via our chat system or WhatsApp to coordinate instructions.</p>
                </div>
                <div style='text-align: center; margin-top: 20px; color: #aaa; font-size: 12px;'>
                    Thank you for choosing WhiskerHub!
                </div>
            </div>
        ";

        $sent_owner = sendBrevoEmail($customer_email, $owner_name, $subject_owner, $body_owner, 'aisyahsalihah22@gmail.com', 'WhiskerHub System');
        if ($sent_owner) {
            $email_status = "Invoice email has been sent to your email ($customer_email).";
        } else {
            $email_status = "Invoice email could not be sent.";
        }

        // 3. Format & Send English Job Notification to Cat Sitter
        if (!empty($sitter_mail)) {
            $protocol = "http://";
            if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
                $protocol = "https://";
            }
            $host = $_SERVER['HTTP_HOST'];
            $current_dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $sitter_redirect_url = $protocol . $host . $current_dir . "/bookingterkini.php";

            $subject_sitter = "WhiskerHub - New Booking Job Received! (#$booking_id)";
            $body_sitter = "
                <div style='font-family: \"Segoe UI\", Arial, sans-serif; padding: 30px; background-color: #f7f9fa; max-width: 600px; margin: auto; border-radius: 12px;'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <h2 style='color: #4caf50; font-size: 28px; margin: 0; font-weight: 800;'>🐾 WHISKERHUB</h2>
                        <p style='color: #777; margin: 5px 0 0 0; font-size: 14px;'>New Booking Job Received</p>
                    </div>
                    <div style='background: white; padding: 25px; border-radius: 10px; border-top: 5px solid #4caf50;'>
                        <p style='font-size: 16px; color: #333; margin-top: 0;'>Hello, <strong>$sitter_name</strong>! You have received a new booking request from <strong>$owner_name</strong>.</p>
                        
                        <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                            <tr>
                                <td style='padding: 8px 0; color: #777; font-size: 14px;'>Booking ID:</td>
                                <td style='padding: 8px 0; font-weight: bold; color: #333; text-align: right;'>#$booking_id</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #777; font-size: 14px;'>Cat Owner:</td>
                                <td style='padding: 8px 0; font-weight: bold; color: #333; text-align: right;'>$owner_name</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #777; font-size: 14px;'>Service Type:</td>
                                <td style='padding: 8px 0; font-weight: bold; color: #333; text-align: right;'>$service</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #777; font-size: 14px;'>Dates:</td>
                                <td style='padding: 8px 0; font-weight: bold; color: #333; text-align: right;'>$start_date to $end_date</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #777; font-size: 14px;'>Start Time:</td>
                                <td style='padding: 8px 0; font-weight: bold; color: #333; text-align: right;'>$start_time</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #777; font-size: 14px;'>Number of Cats:</td>
                                <td style='padding: 8px 0; font-weight: bold; color: #333; text-align: right;'>$cats cat(s)</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #777; font-size: 14px;'>Care Location:</td>
                                <td style='padding: 8px 0; font-weight: bold; color: #333; text-align: right;'>$location</td>
                            </tr>
                            <tr style='border-top: 1px solid #eee;'>
                                <td style='padding: 15px 0 0 0; font-size: 16px; font-weight: bold; color: #4caf50;'>Estimated Earnings:</td>
                                <td style='padding: 15px 0 0 0; font-size: 18px; font-weight: bold; color: #333; text-align: right;'>RM $amount</td>
                            </tr>
                        </table>
                        
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='$sitter_redirect_url' style='background-color: #4caf50; color: white; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; font-size: 15px;'>Manage Booking Details</a>
                        </div>
                        
                        <p style='font-size: 12px; color: #888; text-align: center;'>If the button above doesn't work, copy & paste this link into your browser:<br>$sitter_redirect_url</p>
                    </div>
                    <div style='text-align: center; margin-top: 20px; color: #aaa; font-size: 12px;'>
                        WhiskerHub Sitter Community
                    </div>
                </div>
            ";
            
            sendBrevoEmail($sitter_mail, $sitter_name, $subject_sitter, $body_sitter, 'aisyahsalihah22@gmail.com', 'WhiskerHub System');
        }
    } else {
        $email_status = "Payment verified, but booking data could not be fetched for invoices.";
    }
} else {
    $email_status = "Incomplete booking details for email delivery.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - WhiskerHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <!-- FontAwesome for Button Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .success-container {
            background: #ffffff;
            max-width: 450px;
            width: 90%;
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            text-align: center;
            border-top: 8px solid #ff9800;
        }
        .success-icon {
            font-size: 70px;
            color: #4caf50;
            margin-bottom: 20px;
            animation: scaleUp 0.5s ease-out;
        }
        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .booking-id {
            background: #fff3e0;
            color: #e65100;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }
        p {
            color: #666;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .email-info {
            background: #f1f8e9;
            color: #33691e;
            padding: 12px;
            border-radius: 10px;
            font-size: 13.5px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }
        .btn-whatsapp {
            background-color: #25d366;
            color: white;
        }
        .btn-whatsapp:hover {
            background-color: #1ebd57;
            box-shadow: 0 5px 15px rgba(37, 211, 102, 0.3);
        }
        .btn-chat {
            background-color: #ff9800;
            color: white;
        }
        .btn-chat:hover {
            background-color: #e68a00;
            box-shadow: 0 5px 15px rgba(255, 152, 0, 0.3);
        }
        .btn-menu {
            background-color: #f5f5f5;
            color: #555;
        }
        .btn-menu:hover {
            background-color: #e0e0e0;
        }
        @keyframes scaleUp {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>

<div class="success-container">
    <div class="success-icon">
        <i class="fa-solid fa-circle-check"></i>
    </div>
    <h1>PAYMENT SUCCESSFUL!</h1>
    <div class="booking-id">Booking ID: #<?php echo htmlspecialchars($booking_id); ?></div>
    
    <div class="email-info">
        <i class="fa-solid fa-envelope-open-text"></i>
        <span><?php echo htmlspecialchars($email_status); ?></span>
    </div>

    <p>Meow! Your payment has been confirmed. Your booking details have been verified and updated in the system.</p>

    <div class="btn-group">
        <!-- Direct WhatsApp Button to Sitter (Updated dynamically via JS) -->
        <a id="whatsappBtn" href="#" target="_blank" class="btn btn-whatsapp" style="display: none;">
            <i class="fa-brands fa-whatsapp"></i> Contact Sitter via WhatsApp
        </a>
        
        <!-- Redirect Button to message.php -->
        <a href="message.php" class="btn btn-chat">
            <i class="fa-regular fa-comments"></i> Chat on WhiskerHub
        </a>
        
        <!-- Main Menu Redirect -->
        <a href="mainmenu.php" class="btn btn-menu">
            <i class="fa-solid fa-house"></i> Return to Main Menu
        </a>
    </div>
</div>

<!-- Firebase Logic to Update Booking Status & Link Chat Channels -->
<script type="module">
import { auth, db } from "../js/firebase.js";
import { doc, getDoc, updateDoc, setDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";

const bookingId = "<?php echo $booking_id; ?>";

auth.onAuthStateChanged(async (user) => {
    if (user && bookingId !== 'unknown') {
        try {
            // 1. Fetch booking details from Firestore
            const bookingDoc = await getDoc(doc(db, "tempahan", bookingId));
            
            if (bookingDoc.exists()) {
                const bookingData = bookingDoc.data();
                const sitterId = bookingData.fld_penjaga_ID;
                const userId = bookingData.fld_pemilik_ID;

                // 2. Update booking status to "Paid"
                await updateDoc(doc(db, "tempahan", bookingId), {
                    fld_tempahan_status: "Paid"
                });
                console.log("Booking status updated to Paid.");

                // 3. Resolve Sitter's phone number formatting for direct WhatsApp link
                const sitterDoc = await getDoc(doc(db, "penjaga_kucing", sitterId));
                if (sitterDoc.exists()) {
                    let phone = sitterDoc.data().fld_user_phone || "";
                    if (phone) {
                        // Sanitize non-numeric characters
                        phone = phone.replace(/[^0-9]/g, '');
                        // Prepend country code for Malaysia (60) if it starts with 0
                        if (phone.startsWith('0')) {
                            phone = '60' + phone.substring(1);
                        }
                        const waText = encodeURIComponent(`Hello! I am a pet owner from WhiskerHub (Booking #${bookingId}).`);
                        const waBtn = document.getElementById('whatsappBtn');
                        waBtn.href = `https://wa.me/${phone}?text=${waText}`;
                        waBtn.style.display = 'flex'; 
                    }
                }

                // 4. INITIALIZE AUTOMATIC CHAT ROOM INSIDE message.php LIST
                // Create unique chat room ID combining user ID and sitter ID
                const chatRoomId = [userId, sitterId].sort().join('_');

                // Arrange participants array according to message.php query requirements
                const chatRoomData = {
                    fld_chat_room_id: chatRoomId,
                    fld_pemilik_ID: userId,
                    fld_penjaga_ID: sitterId,
                    participants: [userId, sitterId], // IMPORTANT: Ensure this field exists for "array-contains" query
                    lastMessage: "Booking confirmed and settled! Let's talk about the cat care details. 🐾", // Change fld_last_message to lastMessage matching message.php code
                    lastMessageTime: serverTimestamp(), // Change fld_last_updated to lastMessageTime
                    lastSenderId: sitterId, // Put sitter as last sender so unread red dot displays on owner (user) account
                    isRead: false
                };

                // Set channel structure, merge fields safely
                await setDoc(doc(db, "chats", chatRoomId), chatRoomData, { merge: true });

                console.log("Chat room channel updated successfully with participants array.");
            }
        } catch (error) {
            console.error("Firebase Integration Error:", error);
        }
    }
});
</script>

</body>
</html>