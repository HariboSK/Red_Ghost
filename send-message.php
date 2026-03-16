<?php
// Spracovanie kontaktného formulára
// send-message.php

include 'config.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Honeypot kontrola
    if(!empty($_POST['robot'])) {
        die('SPAM: neplatná požiadavka');
    }

    // Sanitácia vstupov
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Validácia emailu
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Neplatný email');
    }

    // TODO: Skontrolujte reCAPTCHA token
    // Viac info: https://www.google.com/recaptcha/admin

    // Uloženie do databázy (keď ju nastavíte)
    // $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
    // $stmt->bind_param("sss", $name, $email, $message);
    // $stmt->execute();

    // Zatiaľ sa správa iba zobrazí
    echo "Správa bola prijatá:<br>";
    echo "Meno: " . $name . "<br>";
    echo "Email: " . $email . "<br>";
    echo "Správa: " . $message . "<br>";

    // Odoslanie emailu (voliteľné)
    $to = "vase@email.sk";
    $subject = "Nová správa z kontaktného formulára - Red Ghost";
    $mailBody = "Meno: $name\nEmail: $email\n\nSpráva:\n$message";
    mail($to, $subject, $mailBody);

    // Presmerování na index.php
    header("Location: index.php?success=1");
    exit();
}
?>
