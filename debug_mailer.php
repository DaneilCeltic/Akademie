<?php
// Debug script pro identifikaci problémů s mailerem
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Základní informace pro debugging
$debug_info = [
    'php_version' => phpversion(),
    'current_time' => date('Y-m-d H:i:s'),
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
    'files_exist' => [
        'send_email.php' => file_exists('send_email.php'),
        'email_config.php' => file_exists('email_config.php'),
        'vendor/autoload.php' => file_exists('vendor/autoload.php'),
        'composer.json' => file_exists('composer.json')
    ],
    'extensions' => [
        'json' => extension_loaded('json'),
        'openssl' => extension_loaded('openssl'),
        'curl' => extension_loaded('curl'),
        'mysqli' => extension_loaded('mysqli')
    ]
];

// Pokud je to POST požadavek, zkusíme zpracovat data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw_input = file_get_contents('php://input');
    $debug_info['raw_input'] = $raw_input;
    
    $input = json_decode($raw_input, true);
    $debug_info['parsed_input'] = $input;
    $debug_info['json_last_error'] = json_last_error_msg();
    
    // Test základních funkcí
    if ($input) {
        $debug_info['validation'] = [
            'company' => !empty($input['company']),
            'email' => !empty($input['email']) && filter_var($input['email'], FILTER_VALIDATE_EMAIL),
            'phone' => !empty($input['phone']),
            'seminar' => !empty($input['seminar'])
        ];
    }
}

// Zkontrolujeme konfiguraci
if (file_exists('email_config.php')) {
    try {
        require_once 'email_config.php';
        $debug_info['config'] = [
            'smtp_host_defined' => defined('SMTP_HOST'),
            'smtp_username_defined' => defined('SMTP_USERNAME'),
            'smtp_password_defined' => defined('SMTP_PASSWORD'),
            'recipient_email_defined' => defined('RECIPIENT_EMAIL'),
            'smtp_host_value' => defined('SMTP_HOST') ? SMTP_HOST : 'not defined',
            'is_default_config' => defined('SMTP_USERNAME') && SMTP_USERNAME === 'vas-email@gmail.com'
        ];
    } catch (Exception $e) {
        $debug_info['config_error'] = $e->getMessage();
    }
}

// Zkontrolujeme PHPMailer
if (file_exists('vendor/autoload.php')) {
    try {
        require 'vendor/autoload.php';
        
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;
        
        $debug_info['phpmailer'] = [
            'loaded' => true,
            'version' => PHPMailer::VERSION ?? 'unknown'
        ];
        
        // Zkusíme vytvořit instanci
        $mail = new PHPMailer(true);
        $debug_info['phpmailer']['instance_created'] = true;
        
    } catch (Exception $e) {
        $debug_info['phpmailer'] = [
            'loaded' => false,
            'error' => $e->getMessage()
        ];
    }
} else {
    $debug_info['phpmailer'] = [
        'loaded' => false,
        'error' => 'vendor/autoload.php not found - run composer install'
    ];
}

// Zkontrolujeme oprávnění
$debug_info['permissions'] = [
    'current_dir_writable' => is_writable('.'),
    'email_config_readable' => file_exists('email_config.php') ? is_readable('email_config.php') : false,
    'send_email_readable' => file_exists('send_email.php') ? is_readable('send_email.php') : false
];

// Pokud je to GET požadavek, vrátíme debug informace
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'success' => true,
        'message' => 'Debug informace',
        'debug' => $debug_info
    ], JSON_PRETTY_PRINT);
    exit;
}

// Pro POST požadavek zkusíme simulovat základní funkčnost
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kontrola povinných polí
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode([
            'success' => false,
            'message' => 'Neplatná JSON data',
            'debug' => $debug_info
        ]);
        exit;
    }
    
    $required_fields = ['company', 'contact-name', 'email', 'phone', 'participants', 'seminar'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        echo json_encode([
            'success' => false,
            'message' => 'Chybí povinná pole: ' . implode(', ', $missing_fields),
            'debug' => $debug_info
        ]);
        exit;
    }
    
    // Pokud je vše v pořádku, vrátíme úspěch
    echo json_encode([
        'success' => true,
        'message' => 'Debug test úspěšný - všechna data jsou v pořádku',
        'received_data' => $input,
        'debug' => $debug_info
    ]);
    exit;
}

// Fallback
echo json_encode([
    'success' => false,
    'message' => 'Nepodporovaná metoda',
    'debug' => $debug_info
]);
?>