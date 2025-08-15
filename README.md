# Akademie nevšedního vzdělávání - Zabezpečené HTML/CSS šablony

## ⚠️ KRITICKÁ BEZPEČNOSTNÍ AKTUALIZACE

Tento systém byl aktualizován s pokročilými bezpečnostními funkcemi.

## 🔒 Bezpečnostní funkce

### Implementovaná ochrana:
- ✅ Blokování přístupu k citlivým souborům (.md, .log, .json, config)
- ✅ Rate limiting (max 5 požadavků za 10 minut na IP)
- ✅ Validace a sanitizace všech vstupů
- ✅ CSRF ochrana
- ✅ XSS ochrana
- ✅ Content-Type validace
- ✅ IP tracking a logování
- ✅ Bezpečné error handling

### Chráněné soubory:
- `email_config.php` - SMTP konfigurace
- `*.log` - Log soubory
- `*.md` - Dokumentace
- `composer.json/lock` - Závislosti
- `debug_*`, `test_*` - Debug soubory

## 📁 Přístupné soubory (pouze tyto):
- `index.html` - Hlavní stránka
- `styles.css` - Styly
- `script.js` - JavaScript
- `seminar-detail.html` - Detailní stránka
- `seminar-detail.css` - Styly pro detail
- `send_email.php` - Mailer (pouze POST)

## 🚀 Instalace (BEZPEČNÁ)

### 1. Stáhněte soubory
```bash
# Pouze produkční soubory!
```

### 2. Nainstalujte závislosti
```bash
composer install
```

### 3. Nakonfigurujte email_config.php
```php
define('SMTP_USERNAME', 'vas-email@gmail.com');
define('SMTP_PASSWORD', 'app-password');
define('RECIPIENT_EMAIL', 'radim@martynek.cz');
```

### 4. Nastavte oprávnění
```bash
chmod 644 *.php *.html *.css *.js
chmod 600 email_config.php
chmod 755 .
```

### 5. SMAŽTE debug soubory v produkci!
```bash
rm debug_*.php debug_*.html test_*.php troubleshooting.md
```

## 🛡️ Bezpečnostní checklist

Před nasazením do produkce:
- [ ] SMTP údaje nakonfigurovány
- [ ] Debug soubory smazány
- [ ] Oprávnění nastavena
- [ ] .htaccess aktivní
- [ ] Rate limiting testován
- [ ] HTTPS povoleno

## 📊 Monitorování

### Log soubory:
- `email_log.txt` - Úspěšné odeslání
- `email_errors.txt` - Chyby při odesílání
- `rate_limit_*.tmp` - Rate limiting data

### Sledování:
```bash
tail -f email_log.txt email_errors.txt
```

## ⚡ Rychlé testování

1. Otevřete pouze `index.html`
2. Vyplňte formulář
3. Zkontrolujte email v schránce
4. Ověřte logy

## 🔧 Troubleshooting

### Časté problémy:
1. **"Method Not Allowed"** - Používáte GET místo POST
2. **"Rate limit exceeded"** - Příliš mnoho požadavků
3. **"Invalid Content-Type"** - Chybí JSON header
4. **"Výchozí konfigurace"** - Změňte SMTP údaje

### Debug (pouze pro vývoj):
```bash
# Povolte debug soubory v .htaccess pro vaši IP
Allow from YOUR_IP_ADDRESS
```

## 📞 Kontakt

Pro technickou podporu kontaktujte vývojáře s:
- Popisem chyby
- IP adresou
- Časem výskytu
- Browser/server informacemi

---

**⚠️ BEZPEČNOSTNÍ UPOZORNĚNÍ:** 
Nikdy necommitujte email_config.php s reálnými hesly do veřejného repo!