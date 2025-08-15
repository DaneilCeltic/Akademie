#!/bin/bash

# Setup script pro PHP Mailer - Akademie nevšedního vzdělávání
echo "🚀 Nastavování PHP Maileru..."

# Kontrola PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP není nainstalováno!"
    exit 1
fi

echo "✅ PHP verze: $(php -v | head -n 1)"

# Kontrola Composeru
if ! command -v composer &> /dev/null; then
    echo "📦 Composer není nainstalován. Stahuji..."
    
    if command -v curl &> /dev/null; then
        curl -sS https://getcomposer.org/installer | php
        sudo mv composer.phar /usr/local/bin/composer
    elif command -v wget &> /dev/null; then
        wget -O - https://getcomposer.org/installer | php
        sudo mv composer.phar /usr/local/bin/composer
    else
        echo "❌ Nelze stáhnout Composer. Nainstalujte ho ručně z https://getcomposer.org/"
        exit 1
    fi
fi

echo "✅ Composer verze: $(composer --version)"

# Instalace závislostí
echo "📚 Instaluji PHPMailer..."
composer install

if [ ! -f "vendor/autoload.php" ]; then
    echo "❌ Instalace PHPMailer selhala!"
    exit 1
fi

echo "✅ PHPMailer nainstalován"

# Kontrola konfigurace
if [ ! -f "email_config.php" ]; then
    echo "❌ email_config.php neexistuje!"
    exit 1
fi

# Kontrola výchozích hodnot
if grep -q "vas-email@gmail.com" email_config.php; then
    echo "⚠️  POZOR: Používáte výchozí konfiguraci!"
    echo "   Upravte email_config.php s vašimi SMTP údaji"
fi

# Nastavení oprávnění
chmod 600 email_config.php
echo "🔒 Nastavena oprávnění pro email_config.php"

# Vytvoření log souborů
touch email_log.txt email_errors.txt
chmod 664 email_log.txt email_errors.txt
echo "📋 Vytvořeny log soubory"

# Kontrola webserveru
if pgrep -x "apache2" > /dev/null || pgrep -x "httpd" > /dev/null; then
    echo "✅ Apache běží"
elif pgrep -x "nginx" > /dev/null; then
    echo "✅ Nginx běží"
else
    echo "⚠️  Webserver možná neběží"
fi

echo ""
echo "🎉 Setup dokončen!"
echo ""
echo "Další kroky:"
echo "1. Upravte email_config.php s vašimi SMTP údaji"
echo "2. Otevřete debug_test.html v prohlížeči pro testování"
echo "3. Nebo otevřete test_mailer.php pro rychlý test"
echo "4. Po úspěšném testování smažte debug soubory z produkce"
echo ""
echo "Testovací soubory:"
echo "• debug_test.html - Kompletní debugging rozhraní"
echo "• test_mailer.php - Rychlý test"
echo "• debug_mailer.php - Debug endpoint"
echo ""

# Test základní funkčnosti
echo "🧪 Spouštím základní test..."
php -f test_mailer.php > /dev/null 2>&1

if [ $? -eq 0 ]; then
    echo "✅ Základní test prošel"
else
    echo "⚠️  Základní test neprošel - zkontrolujte konfiguraci"
fi

echo ""
echo "🔧 Pro debugging použijte:"
echo "   curl -X GET http://localhost/debug_mailer.php"
echo ""

# Konec
echo "✨ Setup hotový! Můžete začít testovat."