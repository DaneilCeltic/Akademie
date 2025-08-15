<?php
// Konfigurace pro odesílání emailů
// POZOR: Změňte tyto hodnoty podle vašeho SMTP serveru a emailového účtu

// SMTP server konfigurace
define('SMTP_HOST', 'smtp.gmail.com');                    // SMTP server (např. smtp.gmail.com, smtp.seznam.cz)
define('SMTP_PORT', 587);                                 // SMTP port (587 pro TLS, 465 pro SSL)
define('SMTP_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS); // Šifrování (PHPMailer::ENCRYPTION_STARTTLS nebo PHPMailer::ENCRYPTION_SMTPS)

// Přihlašovací údaje SMTP
define('SMTP_USERNAME', 'vas-email@gmail.com');           // Váš emailový účet pro odesílání
define('SMTP_PASSWORD', 'vase-heslo-nebo-app-password');  // Heslo nebo App Password

// Příjemce emailů
define('RECIPIENT_EMAIL', 'radim@martynek.cz');           // Email, kam se budou odesílat poptávky

// Debug režim (nastavte na false v produkci)
define('DEBUG_MODE', false);

/*
NÁVOD NA NASTAVENÍ:

1. GMAIL:
   - SMTP_HOST: 'smtp.gmail.com'
   - SMTP_PORT: 587
   - SMTP_ENCRYPTION: PHPMailer::ENCRYPTION_STARTTLS
   - Pro Gmail je třeba vygenerovat "App Password" v nastavení Google účtu
   - Návod: https://support.google.com/accounts/answer/185833

2. SEZNAM.CZ:
   - SMTP_HOST: 'smtp.seznam.cz'
   - SMTP_PORT: 465
   - SMTP_ENCRYPTION: PHPMailer::ENCRYPTION_SMTPS
   - Použijte běžné heslo k emailu

3. WEBHOSTING/cPanel:
   - SMTP_HOST: 'mail.vase-domena.cz' (nebo IP adresa serveru)
   - SMTP_PORT: 587 nebo 465
   - SMTP_ENCRYPTION: PHPMailer::ENCRYPTION_STARTTLS nebo PHPMailer::ENCRYPTION_SMTPS
   - Kontaktujte svého poskytovatele hostingu pro přesné údaje

4. OFFICE 365:
   - SMTP_HOST: 'smtp-mail.outlook.com'
   - SMTP_PORT: 587
   - SMTP_ENCRYPTION: PHPMailer::ENCRYPTION_STARTTLS
   - Použijte svoje Office 365 přihlašovací údaje

BEZPEČNOSTNÍ POZNÁMKY:
- Nikdy necommitujte tento soubor s reálnými hesly do veřejného repozitáře
- Pro produkční nasazení vytvořte kopii tohoto souboru mimo webový adresář
- Používejte App Password místo běžného hesla, kde je to možné
- Nastavte správná oprávnění souboru (chmod 600)
*/
?>