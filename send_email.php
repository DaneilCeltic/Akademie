<?php
// Nastavení error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers pro Ajax požadavky
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Kontrola metody požadavku
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Pouze POST metoda je povolena']);
    exit;
}

// Získání dat z formuláře
$input = json_decode(file_get_contents('php://input'), true);

// Ověření povinných polí
$required_fields = ['company', 'contact-name', 'email', 'phone', 'participants', 'seminar'];
$errors = [];

foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        $errors[] = "Pole '$field' je povinné";
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Chybí povinná pole: ' . implode(', ', $errors)]);
    exit;
}

// Sanitizace dat
$company = htmlspecialchars(trim($input['company']), ENT_QUOTES, 'UTF-8');
$contact_name = htmlspecialchars(trim($input['contact-name']), ENT_QUOTES, 'UTF-8');
$email = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);
$phone = htmlspecialchars(trim($input['phone']), ENT_QUOTES, 'UTF-8');
$participants = intval($input['participants']);
$seminar = htmlspecialchars(trim($input['seminar']), ENT_QUOTES, 'UTF-8');
$preferred_date = !empty($input['preferred-date']) ? htmlspecialchars(trim($input['preferred-date']), ENT_QUOTES, 'UTF-8') : 'Neurčeno';
$message = !empty($input['message']) ? htmlspecialchars(trim($input['message']), ENT_QUOTES, 'UTF-8') : 'Bez dalších informací';

// Ověření emailové adresy
if (!$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Neplatná emailová adresa']);
    exit;
}

// Mapování seminářů na lidsky čitelné názvy
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

$seminar_display = isset($seminar_names[$seminar]) ? $seminar_names[$seminar] : $seminar;

// Načtení PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Načtení konfigurace
require_once 'email_config.php';

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
    
    // Nastavení odesílatele a příjemce
    $mail->setFrom(SMTP_USERNAME, 'Akademie nevšedního vzdělávání - Rezervace');
    $mail->addAddress(RECIPIENT_EMAIL, 'Radim Martynek');
    $mail->addReplyTo($email, $contact_name);
    
    // Předmět emailu
    $mail->Subject = "Nová poptávka semináře: $seminar_display";
    
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
                <tr>
                    <th>Organizace</th>
                    <td>$company</td>
                </tr>
                <tr>
                    <th>Kontaktní osoba</th>
                    <td>$contact_name</td>
                </tr>
                <tr>
                    <th>E-mail</th>
                    <td><a href='mailto:$email'>$email</a></td>
                </tr>
                <tr>
                    <th>Telefon</th>
                    <td><a href='tel:$phone'>$phone</a></td>
                </tr>
                <tr>
                    <th>Počet účastníků</th>
                    <td>$participants</td>
                </tr>
                <tr>
                    <th>Požadovaný seminář</th>
                    <td><strong>$seminar_display</strong></td>
                </tr>
                <tr>
                    <th>Preferovaný termín</th>
                    <td>$preferred_date</td>
                </tr>
            </table>
            
            <div class='message-box'>
                <h3>Doplňující informace:</h3>
                <p>" . nl2br($message) . "</p>
            </div>
            
            <p><strong>Datum poptávky:</strong> " . date('d.m.Y H:i:s') . "</p>
        </div>
        
        <div class='footer'>
            <p>Tato zpráva byla automaticky vygenerována prostřednictvím kontaktního formuláře na webu<br>
            <strong>Akademie nevšedního vzdělávání, s. r. o.</strong></p>
        </div>
    </body>
    </html>
    ";
    
    // Plain text verze pro klienty, kteří nepodporují HTML
    $mail->AltBody = "
NOVÁ POPTÁVKA SEMINÁŘE
======================

ORGANIZACE: $company
KONTAKTNÍ OSOBA: $contact_name
E-MAIL: $email
TELEFON: $phone
POČET ÚČASTNÍKŮ: $participants
POŽADOVANÝ SEMINÁŘ: $seminar_display
PREFEROVANÝ TERMÍN: $preferred_date

DOPLŇUJÍCÍ INFORMACE:
$message

Datum poptávky: " . date('d.m.Y H:i:s') . "

--
Akademie nevšedního vzdělávání, s. r. o.
    ";
    
    // Odeslání emailu
    $mail->send();
    
    // Log úspěšného odeslání (volitelné)
    $log_entry = date('Y-m-d H:i:s') . " - Email odeslán: $email - $seminar_display - $company\n";
    file_put_contents('email_log.txt', $log_entry, FILE_APPEND);
    
    // Odpověď pro frontend
    echo json_encode([
        'success' => true, 
        'message' => 'Vaša poptávka byla úspěšně odeslána! Brzy se vám ozveme.'
    ]);
    
} catch (Exception $e) {
    // Log chyby
    $error_log = date('Y-m-d H:i:s') . " - Chyba při odesílání: " . $e->getMessage() . "\n";
    file_put_contents('email_errors.txt', $error_log, FILE_APPEND);
    
    // Odpověď s chybou
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Nastala chyba při odesílání emailu. Zkuste to prosím později.',
        'debug' => DEBUG_MODE ? $e->getMessage() : null
    ]);
}
?>