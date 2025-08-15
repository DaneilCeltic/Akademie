<?php
// BEZPEČNÁ VERZE - Konfigurace pro odesílání emailů
// TENTO SOUBOR PŘESUŇTE MIMO WEB ROOT V PRODUKCI!

// Kontrola, že soubor není volán přímo z webu
if (isset($_SERVER['HTTP_HOST'])) {
    die('Direct access forbidden');
}

// SMTP server konfigurace
define('SMTP_HOST', 'smtp.gmail.com');                    // SMTP server
define('SMTP_PORT', 587);                                 // SMTP port
define('SMTP_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS); // Šifrování

// Přihlašovací údaje SMTP - ZMĚŇTE TYTO HODNOTY!
define('SMTP_USERNAME', 'vas-email@gmail.com');           // ZMĚŇTE!
define('SMTP_PASSWORD', 'vase-app-password');             // ZMĚŇTE!

// Příjemce emailů
define('RECIPIENT_EMAIL', 'radim@martynek.cz');           // Email pro poptávky

// Debug režim (POUZE PRO VÝVOJ!)
define('DEBUG_MODE', false);  // NASTAVTE NA FALSE V PRODUKCI!

// Bezpečnostní kontroly
define('ALLOWED_REFERERS', [
    'localhost',
    'akademie-vzdelavani.cz',
    'www.akademie-vzdelavani.cz'
    // Přidejte vaše domény
]);

// Rate limiting (počet emailů za hodinu z jedné IP)
define('RATE_LIMIT_PER_HOUR', 10);

// === BEZPEČNOSTNÍ POZNÁMKY ===
/*
KRITICKÉ BEZPEČNOSTNÍ KROKY:

1. ZMĚŇTE SMTP ÚDAJE VÝŠE!

2. PŘESUŇTE TENTO SOUBOR MIMO WEB ROOT:
   - Vytvořte složku /config/ mimo webový adresář
   - Přesuňte tento soubor tam
   - Upravte cestu v send_email.php

3. NASTAVTE SPRÁVNÁ OPRÁVNĚNÍ:
   chmod 600 email_config.php

4. V PRODUKCI:
   - Nastavte DEBUG_MODE na false
   - Smažte všechny debug a test soubory
   - Zkontrolujte .htaccess ochranu

5. MONITOROVÁNÍ:
   - Pravidelně kontrolujte logy
   - Nastavte alerting na neobvyklou aktivitu

6. DODATEČNÁ BEZPEČNOST:
   - Implementujte CAPTCHA proti spam
   - Nastavte firewall pravidla
   - Používejte HTTPS
*/

// Funkce pro bezpečnostní kontroly
function isRequestSecure() {
    // Kontrola refereru
    if (!isset($_SERVER['HTTP_REFERER'])) {
        return false;
    }
    
    $referer_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    return in_array($referer_host, ALLOWED_REFERERS);
}

function checkRateLimit($ip) {
    // Jednoduchý rate limiting na základě IP
    $log_file = 'rate_limit.log';
    $current_time = time();
    $one_hour_ago = $current_time - 3600;
    
    // Načti existující logy
    $requests = [];
    if (file_exists($log_file)) {
        $lines = file($log_file, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            list($timestamp, $request_ip) = explode('|', $line);
            if ($timestamp > $one_hour_ago) {
                $requests[] = ['time' => $timestamp, 'ip' => $request_ip];
            }
        }
    }
    
    // Počítej požadavky z této IP za poslední hodinu
    $ip_requests = array_filter($requests, function($req) use ($ip) {
        return $req['ip'] === $ip;
    });
    
    // Kontrola limitu
    if (count($ip_requests) >= RATE_LIMIT_PER_HOUR) {
        return false;
    }
    
    // Zapiš nový požadavek
    $new_requests = array_filter($requests, function($req) use ($one_hour_ago) {
        return $req['time'] > $one_hour_ago;
    });
    $new_requests[] = ['time' => $current_time, 'ip' => $ip];
    
    // Ulož zpět do souboru
    $log_data = array_map(function($req) {
        return $req['time'] . '|' . $req['ip'];
    }, $new_requests);
    
    file_put_contents($log_file, implode("\n", $log_data) . "\n");
    
    return true;
}
?>