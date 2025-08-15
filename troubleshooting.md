# 🔧 Troubleshooting Guide - PHP Mailer

## Časté chyby a jejich řešení

### 1. "Nastala chyba při odesílání formuláře"

**Možné příčiny:**
- PHP soubory neexistují
- PHPMailer není nainstalován
- Špatná konfigurace SMTP
- Server error

**Debugging kroky:**

#### Krok 1: Otevřete `debug_test.html`
```
http://localhost/debug_test.html
```
nebo
```
http://vase-domena.cz/debug_test.html
```

#### Krok 2: Zkontrolujte browser console
- Otevřete Developer Tools (F12)
- Podívejte se na Console tab
- Spusťte formulář a sledujte chybové zprávy

#### Krok 3: Zkontrolujte PHP error log
```bash
tail -f /var/log/apache2/error.log
# nebo
tail -f /var/log/nginx/error.log
```

### 2. "Failed to fetch" nebo "Network Error"

**Příčina:** JavaScript nemůže najít PHP endpoint

**Řešení:**
1. Zkontrolujte, zda `send_email.php` existuje
2. Ověřte cestu v JavaScript (aktuálně nastaveno na `debug_mailer.php`)
3. Zkontrolujte oprávnění souboru:
   ```bash
   chmod 644 send_email.php
   ```

### 3. "HTTP error! status: 500"

**Příčina:** PHP script má chybu

**Řešení:**
1. Zkontrolujte PHP error log
2. Otevřete `test_mailer.php` v prohlížeči
3. Ověřte, že PHPMailer je nainstalován:
   ```bash
   composer install
   ```

### 4. "PHPMailer není nainstalován"

**Řešení:**
```bash
# Nainstalujte Composer (pokud není)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Nainstalujte PHPMailer
composer install
```

### 5. Gmail SMTP chyby

**Časté chyby:**
- "Username and Password not accepted"
- "Less secure app access"

**Řešení:**
1. Povolte 2FA v Google účtu
2. Vygenerujte App Password:
   - https://myaccount.google.com/security
   - 2-Step Verification → App passwords
3. Použijte App Password místo běžného hesla

**Gmail konfigurace:**
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS);
define('SMTP_USERNAME', 'vase-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password'); // Ne běžné heslo!
```

### 6. Seznam.cz SMTP chyby

**Konfigurace:**
```php
define('SMTP_HOST', 'smtp.seznam.cz');
define('SMTP_PORT', 465);
define('SMTP_ENCRYPTION', PHPMailer::ENCRYPTION_SMTPS);
define('SMTP_USERNAME', 'vase-email@seznam.cz');
define('SMTP_PASSWORD', 'vase-heslo');
```

### 7. "JSON parse error"

**Příčina:** PHP vrací HTML místo JSON

**Debugging:**
1. Otevřete `send_email.php` přímo v prohlížeči
2. Zkontrolujte, zda neobsahuje HTML chyby
3. Ověřte PHP syntax:
   ```bash
   php -l send_email.php
   ```

### 8. Webhosting problémy

**Časté problémy:**
- Blokovaný SMTP port
- Zakázané `mail()` funkce
- Omezené PHP extensions

**Řešení:**
1. Kontaktujte poskytovatele hostingu
2. Zeptejte se na SMTP nastavení
3. Požádejte o odblokování portů 587/465

### 9. Oprávnění souborů

**Nastavení oprávnění:**
```bash
chmod 644 *.php
chmod 600 email_config.php
chmod 664 *.log
chmod 755 .
```

### 10. CORS chyby

**Příčina:** Browser blokuje AJAX požadavky

**Řešení:** Ověřte CORS headers v `send_email.php`:
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
```

## Debugging nástroje

### 1. Browser Console
```javascript
// Otevřete console a sledujte:
console.log('Odesílám data:', data);
console.log('Response:', response);
```

### 2. PHP Error Log
```bash
# Zapněte error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### 3. Network Tab
- Otevřete DevTools → Network
- Spusťte formulář
- Sledujte HTTP požadavky

### 4. Test endpoints
```bash
# Test debug endpointu
curl -X GET http://localhost/debug_mailer.php

# Test POST požadavku
curl -X POST http://localhost/debug_mailer.php \
  -H "Content-Type: application/json" \
  -d '{"company":"Test","email":"test@test.com","phone":"123"}'
```

## Produkční nasazení

### Checklist před nasazením:
- [ ] PHPMailer nainstalován (`composer install`)
- [ ] SMTP údaje nakonfigurovány v `email_config.php`
- [ ] Testování proběhlo úspěšně
- [ ] Debug soubory smazány (`debug_*`, `test_*`)
- [ ] Oprávnění nastavena (`chmod 600 email_config.php`)
- [ ] .htaccess ochrana aktivní
- [ ] Error reporting vypnut v produkci

### Soubory k smazání v produkci:
```bash
rm debug_test.html
rm debug_mailer.php
rm test_mailer.php
rm setup.sh
rm troubleshooting.md
```

### Monitorování:
- Kontrolujte `email_log.txt` pro úspěšná odeslání
- Kontrolujte `email_errors.txt` pro chyby
- Nastavte log rotation pro dlouhodobé použití

## Kontakt pro podporu

Pokud problém přetrvává:
1. Zkontrolujte všechny kroky výše
2. Shromážděte debug informace z `debug_test.html`
3. Zkontrolujte server logy
4. Kontaktujte vývojáře s detailními informacemi