<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
$mail = new PHPMailer(true);
$email_status = "";

if ($booking_id !== 'unknown' && !empty($customer_email)) {
    try {
        // Server Settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipient
        $mail->setFrom('no-reply@whiskerhub.com', 'WhiskerHub System');
        $mail->addAddress($customer_email); 

        // Invoice Content
        $mail->isHTML(true);
        $mail->Subject = "WhiskerHub Invoice - Booking #$booking_id";
        
        // Email Template Design
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; max-width: 500px;'>
                <h2 style='color: #ff9800;'>WhiskerHub</h2>
                <hr style='border: 0; border-top: 1px solid #eee;'>
                <p>Hello Cat Lover! 🐾</p>
                <p>Your payment for booking <strong>#$booking_id</strong> has been successfully received.</p>
                <p>Your cat sitter will contact you shortly, or you can start a chat directly within the application.</p>
                <br>
                <p>Thank you for choosing WhiskerHub!</p>
            </div>
        ";

        $mail->send();
        $email_status = "Official invoice has been sent to your email ($customer_email).";
    } catch (Exception $e) {
        $email_status = "Email could not be sent. Error: {$mail->ErrorInfo}";
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
                const chatRoomId = `${userId}_${sitterId}`;

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