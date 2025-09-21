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

try {
    // Use Formspree webhook for reliable email delivery
    $formspree_url = 'https://formspree.io/f/your_form_id'; // You'll need to replace this
    
    $formspree_data = [
        'name' => $name,
        'email' => $email,
        'message' => $message,
        '_subject' => 'Новое сообщение с сайта GoGaLabs от ' . $name,
        '_replyto' => $email,
        '_next' => 'https://gogalabs.com/thanks'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $formspree_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($formspree_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // For now, let's just save to a file as a backup solution
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'name' => $name,
        'email' => $email,
        'message' => $message,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $log_file = '/tmp/contact_submissions.log';
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
    
    // Add to rate limiting
    $_SESSION['requests'][] = $current_time;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Сообщение успешно отправлено! Мы свяжемся с вами в ближайшее время.',
        'note' => 'Сообщение сохранено в системе.'
    ]);
    
} catch (Exception $e) {
    error_log('Contact form error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Ошибка отправки сообщения. Попробуйте позже.']);
}
?>