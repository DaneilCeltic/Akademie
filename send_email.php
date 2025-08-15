<?php
// BEZPEČNÁ VERZE - Mailer pro Akademii nevšedního vzdělávání

// === BEZPEČNOSTNÍ KONTROLY ===

// 1. Kontrola metody požadavku - pouze POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// 2. Kontrola Content-Type
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($content_type, 'application/json') === false) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid Content-Type']);
    exit;
}

// 3. Rate limiting na IP adresu
$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
$rate_limit_file = 'rate_limit_' . md5($client_ip) . '.tmp';
$current_time = time();

// Kontrola rate limitu (max 5 požadavků za 10 minut)
if (file_exists($rate_limit_file)) {
    $last_requests = json_decode(file_get_contents($rate_limit_file), true) ?: [];
    $recent_requests = array_filter($last_requests, function($time) use ($current_time) {
        return ($current_time - $time) < 600; // 10 minut
    });
    
    if (count($recent_requests) >= 5) {
        http_response_code(429);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Příliš mnoho požadavků. Zkuste to později.']);
        exit;
    }
    
    $recent_requests[] = $current_time;
} else {
    $recent_requests = [$current_time];
}

file_put_contents($rate_limit_file, json_encode($recent_requests));

// === HLAVNÍ LOGIKA ===

// Nastavení error reporting (pouze pro debug)
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Získání a validace dat
$raw_input = file_get_contents('php://input');
if (empty($raw_input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Prázdná data']);
    exit;
}

$input = json_decode($raw_input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Neplatná JSON data']);
    exit;
}

// Ověření povinných polí
$required_fields = ['company', 'contact-name', 'email', 'phone', 'participants', 'seminar'];
$errors = [];

foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        $errors[] = $field;
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Chybí povinná pole: ' . implode(', ', $errors)]);
    exit;
}

// Sanitizace a validace dat
$company = filter_var(trim($input['company']), FILTER_SANITIZE_STRING);
$contact_name = filter_var(trim($input['contact-name']), FILTER_SANITIZE_STRING);
$email = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);
$phone = filter_var(trim($input['phone']), FILTER_SANITIZE_STRING);
$participants = filter_var($input['participants'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000]]);
$seminar = filter_var(trim($input['seminar']), FILTER_SANITIZE_STRING);
$preferred_date = !empty($input['preferred-date']) ? filter_var(trim($input['preferred-date']), FILTER_SANITIZE_STRING) : 'Neurčeno';
$message = !empty($input['message']) ? filter_var(trim($input['message']), FILTER_SANITIZE_STRING) : 'Bez dalších informací';

// Dodatečná validace
if (!$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Neplatná emailová adresa']);
    exit;
}

if (!$participants) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Neplatný počet účastníků']);
    exit;
}

// Omezení délky textu
if (strlen($company) > 200 || strlen($contact_name) > 100 || strlen($message) > 2000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Text je příliš dlouhý']);
    exit;
}

// Načtení konfigurací s bezpečnostní kontrolou
if (!file_exists('email_config.php')) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Konfigurační soubor nenalezen']);
    exit;
}

// Načtení PHPMailer
if (!file_exists('vendor/autoload.php')) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'PHPMailer není nainstalován']);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require_once 'email_config.php';

// Kontrola konfigurace
if (!defined('SMTP_HOST') || !defined('SMTP_USERNAME') || !defined('RECIPIENT_EMAIL')) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Neúplná konfigurace']);
    exit;
}

// Kontrola výchozích hodnot
if (SMTP_USERNAME === 'vas-email@gmail.com') {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Výchozí konfigurace - nastavte SMTP údaje']);
    exit;
}

// Mapování seminářů
$seminar_names = [
    'ai-beginners' => 'AI I. - pro začátečníky',
    'ai-intermediate' => 'AI II. - pro mírně pokročilé',
    'ai-advanced' => 'AI III. - pro pokročilé',
    'ai-promotion' => 'AI IV. - propagace města',
    'ai-experts' => 'AI VII. - pro profíky',
    'legislativa-dokumenty' => 'Současná legislativa a její dopad na úpravu dokumentů',
    'pisemna-komunikace' => 'Písemná komunikace v praxi - asertivita a empatie',
    'politicka-korektnost' => 'Politická korektnost při jednání s občany',
    'psychicka-odolnost' => 'Zvyšování psychické odolnosti zaměstnanců',
    'time-management' => 'Jak se v práci prací neunavit',
    'mbti-typologie' => 'Kdo jsem já – kdo jsi ty?',
    'akademie-zad' => 'Akademie zad aneb aby záda nebolela',
    'other' => 'Jiný seminář'
];

$seminar_display = isset($seminar_names[$seminar]) ? $seminar_names[$seminar] : htmlspecialchars($seminar);

try {
    // Vytvoření PHPMailer instance
    $mail = new PHPMailer(true);
    
    // SMTP konfigurace
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_ENCRYPTION;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';
    
    // Bezpečnostní nastavení
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Nastavení odesílatele a příjemce
    $mail->setFrom(SMTP_USERNAME, 'Akademie nevšedního vzdělávání - Rezervace');
    $mail->addAddress(RECIPIENT_EMAIL, 'Radim Martynek');
    $mail->addReplyTo($email, htmlspecialchars($contact_name));
    
    // Předmět emailu
    $mail->Subject = "Nová poptávka semináře: " . $seminar_display;
    
    // HTML tělo emailu
    $mail->isHTML(true);
    $mail->Body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background-color: #3b82f6; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .info-table th, .info-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            .info-table th { background-color: #f8fafc; font-weight: bold; }
            .message-box { background-color: #f8fafc; padding: 15px; border-left: 4px solid #3b82f6; margin: 20px 0; }
            .footer { background-color: #f8fafc; padding: 15px; text-align: center; font-size: 0.9em; color: #666; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>Nová poptávka semináře</h1>
            <p>Akademie nevšedního vzdělávání</p>
        </div>
        
        <div class='content'>
            <p><strong>Dobrý den,</strong></p>
            <p>byla zaslána nová poptávka semináře prostřednictvím webových stránek:</p>
            
            <table class='info-table'>
                <tr><th>Organizace</th><td>" . htmlspecialchars($company) . "</td></tr>
                <tr><th>Kontaktní osoba</th><td>" . htmlspecialchars($contact_name) . "</td></tr>
                <tr><th>E-mail</th><td><a href='mailto:$email'>" . htmlspecialchars($email) . "</a></td></tr>
                <tr><th>Telefon</th><td>" . htmlspecialchars($phone) . "</td></tr>
                <tr><th>Počet účastníků</th><td>" . htmlspecialchars($participants) . "</td></tr>
                <tr><th>Požadovaný seminář</th><td><strong>" . htmlspecialchars($seminar_display) . "</strong></td></tr>
                <tr><th>Preferovaný termín</th><td>" . htmlspecialchars($preferred_date) . "</td></tr>
            </table>
            
            <div class='message-box'>
                <h3>Doplňující informace:</h3>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
            </div>
            
            <p><strong>Datum poptávky:</strong> " . date('d.m.Y H:i:s') . "</p>
            <p><strong>IP adresa:</strong> " . htmlspecialchars($client_ip) . "</p>
        </div>
        
        <div class='footer'>
            <p>Tato zpráva byla automaticky vygenerována prostřednictvím zabezpečeného kontaktního formuláře<br>
            <strong>Akademie nevšedního vzdělávání, s. r. o.</strong></p>
        </div>
    </body>
    </html>
    ";
    
    // Plain text verze
    $mail->AltBody = "
NOVÁ POPTÁVKA SEMINÁŘE
======================

ORGANIZACE: " . $company . "
KONTAKTNÍ OSOBA: " . $contact_name . "
E-MAIL: " . $email . "
TELEFON: " . $phone . "
POČET ÚČASTNÍKŮ: " . $participants . "
POŽADOVANÝ SEMINÁŘ: " . $seminar_display . "
PREFEROVANÝ TERMÍN: " . $preferred_date . "

DOPLŇUJÍCÍ INFORMACE:
" . $message . "

Datum poptávky: " . date('d.m.Y H:i:s') . "
IP adresa: " . $client_ip . "

--
Akademie nevšedního vzdělávání, s. r. o.
    ";
    
    // Odeslání emailu
    $mail->send();
    
    // Bezpečný log (bez citlivých údajů)
    $log_entry = date('Y-m-d H:i:s') . " - Email odeslán z IP: " . $client_ip . " - Seminář: " . $seminar_display . "\n";
    file_put_contents('email_log.txt', $log_entry, FILE_APPEND | LOCK_EX);
    
    // Úspěšná odpověď
    echo json_encode([
        'success' => true, 
        'message' => 'Vaše poptávka byla úspěšně odeslána! Brzy se vám ozveme.'
    ]);
    
} catch (Exception $e) {
    // Bezpečný error log (bez citlivých údajů)
    $error_log = date('Y-m-d H:i:s') . " - Chyba při odesílání z IP: " . $client_ip . " - Typ chyby: Mail error\n";
    file_put_contents('email_errors.txt', $error_log, FILE_APPEND | LOCK_EX);
    
    // Obecná chybová zpráva (bez odhalení interních detailů)
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Nastala chyba při odesílání emailu. Kontaktujte nás prosím přímo.',
        'debug' => (defined('DEBUG_MODE') && DEBUG_MODE) ? $e->getMessage() : null
    ]);
}
?>