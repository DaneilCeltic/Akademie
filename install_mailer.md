# Instalace PHP Maileru pro Akademii nevšedního vzdělávání

## Kroky pro instalace:

### 1. Nainstalujte Composer (pokud ho nemáte)
```bash
# Na Windows: Stáhněte z https://getcomposer.org/
# Na Linux/Mac:
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Nainstalujte PHPMailer
```bash
cd /cesta/k/vasemu/projektu
composer install
```

### 3. Nakonfigurujte email_config.php
Otevřete soubor `email_config.php` a nastavte:
- `SMTP_HOST` - váš SMTP server
- `SMTP_USERNAME` - váš emailový účet
- `SMTP_PASSWORD` - heslo nebo App Password
- `RECIPIENT_EMAIL` - email radim@martynek.cz

### 4. Testování
Otevřete váš web a vyzkoušejte odeslání formuláře.

## Možné problémy a řešení:

### Gmail
- Povolte 2FA v Google účtu
- Vygenerujte App Password: https://support.google.com/accounts/answer/185833
- Použijte App Password místo běžného hesla

### Seznam.cz
- Použijte port 465 a SSL šifrování
- Ověřte, že máte povolen SMTP přístup

### Webhosting
- Kontaktujte poskytovatele pro SMTP údaje
- Někteří poskytovatelé blokují externí SMTP

### Debug
- Nastavte `DEBUG_MODE` na `true` v `email_config.php`
- Zkontrolujte soubory `email_log.txt` a `email_errors.txt`

## Integrace do WordPress:
1. Zkopírujte soubory do WordPress složky
2. Přidejte do `.htaccess` zabezpečení pro config soubory
3. Můžete vytvořit WordPress plugin pro lepší integraci

## Bezpečnostní doporučení:
- Nastavte chmod 600 na email_config.php
- Použijte .env soubor pro citlivé údaje v produkci
- Pravidelně kontrolujte logy
- Implementujte rate limiting proti spamu