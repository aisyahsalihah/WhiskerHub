<?php
require __DIR__ . '/../config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Brevo Email Diagnostic Test</h2>";

$toEmail = $_GET['email'] ?? 'test@example.com';
echo "Target Email: " . htmlspecialchars($toEmail) . "<br><br>";

echo "Attempting to send email via Brevo...<br>";
$subject = "Brevo Diagnostic Test";
$body = "<h3>Brevo is Working!</h3><p>If you see this, your Brevo API integration is functioning correctly.</p>";

$sent = sendBrevoEmail($toEmail, 'Tester', $subject, $body);

if ($sent) {
    echo "<span style='color: green; font-weight: bold;'>SUCCESS:</span> Email sent successfully!";
} else {
    echo "<span style='color: red; font-weight: bold;'>FAILURE:</span> Email could not be sent. Check your Brevo API key or check the logs.";
}
