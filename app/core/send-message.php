<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

// Honeypot kontrola
if (!empty($_POST['robot'])) {
    die('SPAM: neplatna poziadavka');
}

$rawName = trim($_POST['name'] ?? '');
$rawEmail = trim($_POST['email'] ?? '');
$rawMessage = trim($_POST['message'] ?? '');

// Odfiltruj HTML tagy a nebezpecne znaky pre dalsie spracovanie.
$name = strip_tags($rawName);
$email = filter_var($rawEmail, FILTER_SANITIZE_EMAIL);
$message = strip_tags($rawMessage);

$name = preg_replace('/\s+/', ' ', $name);
$message = preg_replace('/\r\n?|\n/', "\n", $message);

// Validacia povinnych poli a dlzky.
if ($name === '' || $email === '' || $message === '') {
    die('Vsetky polia su povinne.');
}

if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    die('Meno musi mat 2 az 100 znakov.');
}

if (!preg_match('/^[\p{L}\p{N} .\-\']+$/u', $name)) {
    die('Meno obsahuje nepovolene znaky.');
}

if (mb_strlen($message) < 5 || mb_strlen($message) > 2000) {
    die('Sprava musi mat 5 az 2000 znakov.');
}

// Validacia emailu
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Neplatny email');
}

// Ochrana proti header injection cez email.
if (preg_match('/[\r\n]/', $email)) {
    die('Neplatny format emailu.');
}

// TODO: Skontrolujte reCAPTCHA token
// Viac info: https://www.google.com/recaptcha/admin

// Ulozenie do databazy (ked ju nastavite)
// $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
// $stmt->bind_param("sss", $name, $email, $message);
// $stmt->execute();

// Odoslanie emailu (volitelne)
$to = 'test@email.com';
$subject = 'Nova sprava z kontaktneho formulara - Red Ghost';
$mailBody = "Meno: $name\nEmail: $email\n\nSprava:\n$message";
$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'From: Red Ghost <noreply@localhost>',
    "Reply-To: $email",
    'X-Mailer: PHP/' . phpversion(),
];

$mailSent = mail($to, $subject, $mailBody, implode("\r\n", $headers));
if (!$mailSent) {
    $lastError = error_get_last();
    $errorMessage = $lastError['message'] ?? 'mail() returned false without PHP error details';
    app_log('error', 'Contact form mail send failed', [
        'error' => $errorMessage,
        'email' => $email,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ]);
} else {
    app_log('info', 'Contact form mail sent', [
        'email' => $email,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ]);
}

// Presmerovanie na home s vysledkom odoslania.
$resultFlag = $mailSent ? '1' : '0';
header('Location: ' . route('/home?success=' . $resultFlag . '#contact'));
exit();
