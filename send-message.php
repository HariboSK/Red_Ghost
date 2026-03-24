<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Honeypot kontrola
    if (!empty($_POST['robot'])) {
        die('SPAM: neplatná požiadavka');
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

    // Validácia emailu
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Neplatný email');
    }

    // Ochrana proti header injection cez email.
    if (preg_match('/[\r\n]/', $email)) {
        die('Neplatny format emailu.');
    }

    // TODO: Skontrolujte reCAPTCHA token
    // Viac info: https://www.google.com/recaptcha/admin

    // Uloženie do databázy (keď ju nastavíte)
    // $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
    // $stmt->bind_param("sss", $name, $email, $message);
    // $stmt->execute();

    // Odoslanie emailu (voliteľné)
    $to = "test@email.com";
    $subject = "Nova sprava z kontaktneho formulara - Red Ghost";
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
        error_log('[Red Ghost] Contact form mail send failed: ' . $errorMessage);
    }

    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $baseUrl = str_replace('\\', '/', dirname($scriptName));
    $baseUrl = rtrim($baseUrl, '/');
    if ($baseUrl === '/' || $baseUrl === '\\' || $baseUrl === '.') {
        $baseUrl = '';
    }

    // Presmerovanie na home s výsledkom odoslania.
    $resultFlag = $mailSent ? '1' : '0';
    header("Location: {$baseUrl}/home.php?success={$resultFlag}#contact");
    exit();
}
?>
