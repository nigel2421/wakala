<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the log file path to your web directory
$log_file_path = __DIR__ . '/php_error.log';
ini_set('log_errors', 1);
ini_set('error_log', $log_file_path);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log the raw JSON input
    $json = file_get_contents('php://input');
    error_log('Raw JSON input: ' . $json);

    // Decode JSON input and log the decoded data
    $data = json_decode($json, true);
    error_log('Decoded JSON data: ' . print_r($data, true));

    // Check if the necessary form data is present
    if (!isset($data['name'], $data['email'], $data['phone'], $data['subject'], $data['message'])) {
        error_log('Missing form data');
        http_response_code(400);
        echo json_encode(['message' => 'Missing form data']);
        exit;
    }

    $name = $data['name'];
    $email = $data['email'];
    $phone = $data['phone'];
    $subject = $data['subject'];
    $message = $data['message'];

    // Log individual fields for debugging
    error_log("Form data - Name: $name, Email: $email, Phone: $phone, Subject: $subject, Message: $message");

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log('Invalid email address: ' . $email);
        http_response_code(400);
        echo json_encode(['message' => 'Invalid email address']);
        exit;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'mail.wakala.africa'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'info@wakala.africa'; // Replace with your SMTP username
        $mail->Password = 'I(J]!UG5M*zG'; // Replace with your SMTP password
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        // Set From email address
        $mail->setFrom($email, $name);

        // Log From address for debugging
        error_log('Setting From address: ' . $email);

        $mail->addAddress('sales@wakala.africa');

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = nl2br("Name: $name\nEmail: $email\nPhone: $phone\nMessage:\n$message");
        $mail->AltBody = "Name: $name\nEmail: $email\nPhone: $phone\nMessage:\n$message";

        $mail->send();
        http_response_code(200);
        echo json_encode(['message' => 'Email sent successfully']);
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        http_response_code(500);
        echo json_encode(['message' => 'Email sending failed. Mailer Error: ' . $mail->ErrorInfo]);
    }
} else {
    error_log('Invalid request method');
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
}
