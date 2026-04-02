<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../library/PHPMailer-7.0.2/src/Exception.php';
require_once __DIR__ . '/../library/PHPMailer-7.0.2/src/PHPMailer.php';
require_once __DIR__ . '/../library/PHPMailer-7.0.2/src/SMTP.php';

if (!function_exists('app_env')) {
    require_once __DIR__ . '/middleware/function.php';
    app_env_load(dirname(__DIR__, 2) . '/config/config.env');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

if (!empty($_POST['robot'])) {
    die('SPAM: neplatna poziadavka');
}

if (!isset($_POST['consent']) || $_POST['consent'] !== 'on') {
    die('Musis suhlasit so spracovanim osobnych udajov.');
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? 'general'));
$message = trim((string) ($_POST['message'] ?? ''));
$recaptchaToken = trim((string) ($_POST['g-recaptcha-response'] ?? ''));

if ($name === '' || $email === '' || $message === '') {
    exit('Vypln vsetky povinne polia.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('Neplatny email.');
}

if ($recaptchaToken === '') {
    die('Potvrd prosim reCAPTCHA.');
}

$secretKey = trim((string) app_env('RECAPTCHA_SECRET_KEY', ''));
if ($secretKey === '') {
    die('Chyba konfiguracie: chyba RECAPTCHA_SECRET_KEY v config.env');
}

$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
$verifyResponse = @file_get_contents($verifyUrl . '?' . http_build_query([
    'secret' => $secretKey,
    'response' => $recaptchaToken,
    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
]));

if ($verifyResponse === false) {
    die('reCAPTCHA server je docasne nedostupny.');
}

$captchaResult = json_decode((string) $verifyResponse, true);
if (!is_array($captchaResult) || empty($captchaResult['success'])) {
    die('reCAPTCHA overenie zlyhalo.');
}

$smtpEnabled = in_array(strtolower((string) app_env('SMTP_ENABLED', 'false')), ['1', 'true', 'yes', 'on'], true);
$smtpHost = trim((string) app_env('SMTP_HOST', 'smtp.gmail.com'));
$smtpPort = (int) app_env('SMTP_PORT', '587');
$smtpEncryption = strtolower(trim((string) app_env('SMTP_ENCRYPTION', 'tls')));
$smtpUsername = trim((string) app_env('SMTP_USERNAME', ''));
$smtpPassword = (string) app_env('SMTP_PASSWORD', '');
$fromEmail = trim((string) app_env('SMTP_FROM_EMAIL', app_env('CONTACT_FORM_FROM_EMAIL', 'noreply@localhost')));
$fromName = trim((string) app_env('SMTP_FROM_NAME', 'Red Ghost Web'));
$mailTo = trim((string) app_env('CONTACT_FORM_TO_EMAIL', 'test@email.com'));

$topicMap = [
    'general' => 'Vseobecny dotaz',
    'trouble' => 'Nieco nefunguje',
    'order' => 'Dotaz ohladom objednavky',
    'feedback' => 'Spatna vazba',
    'other' => 'Ine',
];

$topicKey = strtolower($subject);
$topicLabel = $topicMap[$topicKey] ?? $subject;
$mailSubject = 'Kontakt | ' . $topicLabel;
$sentAt = date('d.m.Y H:i');
$logoUrl = trim((string) app_env('MAIL_LOGO_URL', ''));
$logoPathEnv = trim((string) app_env('MAIL_LOGO_PATH', ''));
$logoAbsolutePath = '';
if ($logoPathEnv !== '') {
    $candidate = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $logoPathEnv);
    $logoAbsolutePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . ltrim($candidate, DIRECTORY_SEPARATOR);
}

$normalizedMessage = preg_replace('/\r\n?|\n/', "\n", $message) ?? $message;
$body = implode("\n", [
    'Nova sprava z kontaktneho formulara',
    '=================================',
    '',
    'Tema: ' . $topicLabel,
    'Meno: ' . $name,
    'Email: ' . $email,
    'Cas odoslania: ' . $sentAt,
    'IP adresa: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
    '',
    'Sprava:',
    '-------',
    $normalizedMessage,
]);

$safeTopic = htmlspecialchars($topicLabel, ENT_QUOTES, 'UTF-8');
$safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$safeSentAt = htmlspecialchars($sentAt, ENT_QUOTES, 'UTF-8');
$safeIp = htmlspecialchars((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), ENT_QUOTES, 'UTF-8');
$safeMessage = nl2br(htmlspecialchars($normalizedMessage, ENT_QUOTES, 'UTF-8'));

$logoBlock = '<div style="text-align:center;font-size:34px;font-weight:800;letter-spacing:2px;color:#ff2b2b;">RED GHOST</div>';
if ($logoPathEnv !== '' && is_file($logoAbsolutePath)) {
    $logoBlock = '<div style="text-align:center;"><img src="cid:redghost_logo" alt="Red Ghost" style="max-width:180px;height:auto;display:inline-block;"></div>';
} elseif ($logoUrl !== '') {
    $safeLogoUrl = htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8');
    $logoBlock = '<div style="text-align:center;"><img src="' . $safeLogoUrl . '" alt="Red Ghost" style="max-width:180px;height:auto;display:inline-block;"></div>';
}

$htmlBody = '<!DOCTYPE html>'
    . '<html><head><meta charset="UTF-8"><title>' . htmlspecialchars($mailSubject, ENT_QUOTES, 'UTF-8') . '</title></head><body style="margin:0;padding:20px;background:#000000;">'
    . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:700px;margin:0 auto;background:#0e0e0e;border:1px solid #2a2a2a;border-radius:12px;overflow:hidden;">'
    . '<tr><td style="padding:28px 24px 18px 24px;background:#000000;">'
    . $logoBlock
    . '<div style="text-align:center;color:#ff4d4d;font-size:14px;margin-top:10px;">Nova sprava z kontaktneho formulara</div>'
    . '</td></tr>'
    . '<tr><td style="padding:20px 24px;color:#ff6363;font-family:Arial,sans-serif;line-height:1.6;">'
    . '<div style="margin-bottom:12px;"><strong style="color:#ff1f1f;">Tema:</strong> ' . $safeTopic . '</div>'
    . '<div style="margin-bottom:8px;"><strong style="color:#ff1f1f;">Meno:</strong> ' . $safeName . '</div>'
    . '<div style="margin-bottom:8px;"><strong style="color:#ff1f1f;">Email:</strong> ' . $safeEmail . '</div>'
    . '<div style="margin-bottom:8px;"><strong style="color:#ff1f1f;">Cas odoslania:</strong> ' . $safeSentAt . '</div>'
    . '<div style="margin-bottom:20px;"><strong style="color:#ff1f1f;">IP adresa:</strong> ' . $safeIp . '</div>'
    . '<div style="border-top:1px solid #2d2d2d;padding-top:16px;">'
    . '<div style="color:#ff1f1f;font-weight:700;margin-bottom:8px;">Sprava:</div>'
    . '<div style="background:#121212;border:1px solid #2d2d2d;border-radius:8px;padding:14px;color:#ff7a7a;">' . $safeMessage . '</div>'
    . '</div>'
    . '</td></tr>'
    . '</table>'
    . '</body></html>';

$mail = new PHPMailer(true);

try {
    if ($smtpEnabled) {
        if ($smtpUsername === '' || $smtpPassword === '') {
            die('Chyba konfiguracie: nastav SMTP_USERNAME a SMTP_PASSWORD v config.env.');
        }

        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUsername;
        $mail->Password = $smtpPassword;
        $mail->SMTPSecure = ($smtpEncryption === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = ($smtpPort > 0) ? $smtpPort : 587;
    }

    $mail->CharSet = 'UTF-8';
    $mail->setFrom($fromEmail, $fromName);
    $mail->addAddress($mailTo);
    $mail->addReplyTo($email, $name);
    if ($logoPathEnv !== '' && is_file($logoAbsolutePath)) {
        $mail->addEmbeddedImage($logoAbsolutePath, 'redghost_logo', 'logo');
    }
    $mail->Subject = $mailSubject;
    $mail->isHTML(true);
    $mail->Body = $htmlBody;
    $mail->AltBody = $body;
    $mail->send();
} catch (Exception $e) {
    if (!$smtpEnabled) {
        $headers = "From: {$fromEmail}\r\nReply-To: {$email}\r\n";
        $plainSent = mail($mailTo, $mailSubject, $body, $headers);
        if ($plainSent) {
            header('Location: ' . route('/home?success=1#contact'));
            exit;
        }
    }

    http_response_code(500);
    echo 'Chyba pri odoslani: ' . $mail->ErrorInfo;
    exit;
}

header('Location: ' . route('/home?success=1#contact'));
exit;