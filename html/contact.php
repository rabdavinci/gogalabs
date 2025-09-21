<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://gogalabs.com');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Rate limiting with session
session_start();
$current_time = time();
$window = 900; // 15 minutes
$max_requests = 5;

if (!isset($_SESSION['requests'])) {
    $_SESSION['requests'] = [];
}

// Clean old requests
$_SESSION['requests'] = array_filter($_SESSION['requests'], function($time) use ($current_time, $window) {
    return ($current_time - $time) < $window;
});

if (count($_SESSION['requests']) >= $max_requests) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Слишком много запросов. Попробуйте позже.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не разрешен']);
    exit;
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    // Fallback to form data
    $data = $_POST;
}

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$message = trim($data['message'] ?? '');

// Validation
if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Все поля обязательны для заполнения']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Неверный формат email']);
    exit;
}

// Sanitize input
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// Email configuration
$to = 'usmonovgayrat89@gmail.com';
$subject = 'Новое сообщение с сайта GoGaLabs от ' . $name;

$email_body = "
<html>
<head>
    <meta charset='UTF-8'>
    <title>Новое сообщение с GoGaLabs</title>
</head>
<body>
    <h2>Новое сообщение с контактной формы GoGaLabs</h2>
    <p><strong>Имя:</strong> {$name}</p>
    <p><strong>Email:</strong> {$email}</p>
    <p><strong>Сообщение:</strong></p>
    <p>" . nl2br($message) . "</p>
    <hr>
    <p><em>Отправлено: " . date('d.m.Y H:i:s') . "</em></p>
</body>
</html>
";

$headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: GoGaLabs Contact Form <noreply@gogalabs.com>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion()
];

try {
    $success = mail($to, $subject, $email_body, implode("\r\n", $headers));
    
    if ($success) {
        // Add to rate limiting
        $_SESSION['requests'][] = $current_time;
        
        echo json_encode(['success' => true, 'message' => 'Сообщение успешно отправлено!']);
    } else {
        throw new Exception('Mail function failed');
    }
} catch (Exception $e) {
    error_log('Contact form error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Ошибка отправки сообщения. Попробуйте позже.']);
}
?>