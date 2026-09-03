<?php
// Enable full error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the file containing your function
require_once __DIR__ . '/email.php';

// Define test parameters
$to = "adityasai9944@gmail.com"; // <-- REPLACE WITH YOUR EMAIL
$subject = "Test Email from PHP Function";
$body = "Hello! If you are reading this, your Gmail API sendEmail function is working correctly.";

echo "Attempting to send email to: $to ...\n";

// Call the function from email.php
$result = sendEmail($to, $subject, $body);

if ($result === true) {
    echo "SUCCESS: Email was sent successfully!\n";
} else {
    echo "FAILURE: The function returned false.\n";
    
}