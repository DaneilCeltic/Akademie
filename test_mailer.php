<?php
// Test soubor pro ověření PHP Maileru
// Spusťte tento soubor v prohlížeči pro otestování konfigurace

// Kontrola existence potřebných souborů
$files_to_check = [
    'email_config.php',
    'send_email.php',
    'vendor/autoload.php'
];

echo "<h1>Test PHP Maileru - Akademie nevšedního vzdělávání</h1>";

foreach ($files_to_check as $file) {
    echo "<p>";
    if (file_exists($file)) {
        echo "✅ <strong>$file</strong> - soubor existuje";
    } else {
        echo "❌ <strong>$file</strong> - soubor NEEXISTUJE!";
        if ($file === 'vendor/autoload.php') {
            echo " (Spusťte 'composer install')";
        }
    }
    echo "</p>";
}

// Test PHPMailer načtení
echo "<h2>Test načtení PHPMailer:</h2>";
try {
    if (file_exists('vendor/autoload.php')) {
        require 'vendor/autoload.php';
        echo "<p>✅ PHPMailer úspěšně načten</p>";
        
        // Test konfigurace
        if (file_exists('email_config.php')) {
            require_once 'email_config.php';
            echo "<h2>Konfigurace:</h2>";
            echo "<ul>";
            echo "<li><strong>SMTP Host:</strong> " . (defined('SMTP_HOST') ? SMTP_HOST : 'NEDEFINOVÁNO') . "</li>";
            echo "<li><strong>SMTP Port:</strong> " . (defined('SMTP_PORT') ? SMTP_PORT : 'NEDEFINOVÁNO') . "</li>";
            echo "<li><strong>SMTP Username:</strong> " . (defined('SMTP_USERNAME') ? SMTP_USERNAME : 'NEDEFINOVÁNO') . "</li>";
            echo "<li><strong>Recipient Email:</strong> " . (defined('RECIPIENT_EMAIL') ? RECIPIENT_EMAIL : 'NEDEFINOVÁNO') . "</li>";
            echo "<li><strong>Debug Mode:</strong> " . (defined('DEBUG_MODE') ? (DEBUG_MODE ? 'ZAPNUTO' : 'VYPNUTO') : 'NEDEFINOVÁNO') . "</li>";
            echo "</ul>";
            
            // Upozornění na výchozí hodnoty
            if (defined('SMTP_USERNAME') && SMTP_USERNAME === 'vas-email@gmail.com') {
                echo "<p style='color: red;'>⚠️ <strong>Upozornění:</strong> Používáte výchozí emailové nastavení. Změňte hodnoty v email_config.php!</p>";
            }
        }
        
    } else {
        echo "<p>❌ Vendor autoload neexistuje. Spusťte 'composer install'</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Chyba při načítání PHPMailer: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// PHP verze
echo "<h2>PHP informace:</h2>";
echo "<ul>";
echo "<li><strong>PHP verze:</strong> " . phpversion() . "</li>";
echo "<li><strong>OpenSSL:</strong> " . (extension_loaded('openssl') ? 'Dostupné' : 'NEDOSTUPNÉ') . "</li>";
echo "<li><strong>cURL:</strong> " . (extension_loaded('curl') ? 'Dostupné' : 'NEDOSTUPNÉ') . "</li>";
echo "<li><strong>JSON:</strong> " . (extension_loaded('json') ? 'Dostupné' : 'NEDOSTUPNÉ') . "</li>";
echo "</ul>";

// Test formulář
echo "<h2>Test formulář:</h2>";
echo "<p>Pokud je vše správně nakonfigurováno, můžete otestovat odeslání emailu:</p>";

echo "<form id='testForm' style='background: #f8fafc; padding: 20px; border-radius: 8px; max-width: 500px;'>
    <div style='margin-bottom: 15px;'>
        <label>Testovací email:</label><br>
        <input type='email' name='email' value='test@example.com' required style='width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;'>
    </div>
    <div style='margin-bottom: 15px;'>
        <label>Zpráva:</label><br>
        <textarea name='message' style='width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;' rows='3'>Toto je testovací zpráva z PHP Maileru.</textarea>
    </div>
    <button type='button' onclick='testEmail()' style='background: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>
        Odeslat testovací email
    </button>
    <div id='testResult' style='margin-top: 15px;'></div>
</form>";

echo "<script>
async function testEmail() {
    const form = document.getElementById('testForm');
    const result = document.getElementById('testResult');
    const button = form.querySelector('button');
    
    button.disabled = true;
    button.textContent = 'Odesílám...';
    result.innerHTML = '';
    
    try {
        const response = await fetch('send_email.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                'company': 'Test s.r.o.',
                'contact-name': 'Test User',
                'email': form.email.value,
                'phone': '+420123456789',
                'participants': '5',
                'seminar': 'ai-beginners',
                'preferred-date': '" . date('Y-m-d') . "',
                'message': form.message.value
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            result.innerHTML = '<p style=\"color: green;\">✅ Email byl úspěšně odeslán!</p>';
        } else {
            result.innerHTML = '<p style=\"color: red;\">❌ Chyba: ' + data.message + '</p>';
        }
    } catch (error) {
        result.innerHTML = '<p style=\"color: red;\">❌ Chyba při odesílání: ' + error.message + '</p>';
    }
    
    button.disabled = false;
    button.textContent = 'Odeslat testovací email';
}
</script>";

echo "<h2>Další kroky:</h2>";
echo "<ol>
<li>Nastavte správné SMTP údaje v <code>email_config.php</code></li>
<li>Spusťte <code>composer install</code> pro načtení PHPMailer</li>
<li>Otestujte odeslání emailu pomocí formuláře výše</li>
<li>Zkontrolujte logy v <code>email_log.txt</code> a <code>email_errors.txt</code></li>
<li>Po úspěšném testování smažte tento soubor z produkčního serveru</li>
</ol>";

echo "<p style='color: #666; font-size: 0.9em; margin-top: 40px;'>
<strong>Poznámka:</strong> Tento soubor slouží pouze pro testování. 
Po úspěšném nastavení ho smažte z produkčního serveru z bezpečnostních důvodů.
</p>";
?>