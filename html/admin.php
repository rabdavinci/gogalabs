<?php
// Simple admin interface to view contact form submissions
// Access this at: https://gogalabs.com/admin.php

// Basic security - you should add proper authentication
$admin_password = 'gogalabs2025'; // Change this!
$entered_password = $_GET['password'] ?? '';

if ($entered_password !== $admin_password) {
    die('Access denied. Add ?password=gogalabs2025 to URL');
}

$log_file = '/tmp/contact_submissions.log';

echo '<html><head><title>GoGaLabs Contact Submissions</title>';
echo '<style>body{font-family:Arial;margin:20px} .submission{border:1px solid #ccc;margin:10px 0;padding:15px;background:#f9f9f9}</style>';
echo '</head><body>';
echo '<h1>GoGaLabs Contact Form Submissions</h1>';

if (file_exists($log_file)) {
    $submissions = file($log_file, FILE_IGNORE_NEW_LINES);
    
    if (empty($submissions)) {
        echo '<p>No submissions yet.</p>';
    } else {
        echo '<p>Total submissions: ' . count($submissions) . '</p>';
        
        // Show most recent first
        $submissions = array_reverse($submissions);
        
        foreach ($submissions as $line) {
            $data = json_decode($line, true);
            if ($data) {
                echo '<div class="submission">';
                echo '<strong>Date:</strong> ' . htmlspecialchars($data['timestamp']) . '<br>';
                echo '<strong>Name:</strong> ' . htmlspecialchars($data['name']) . '<br>';
                echo '<strong>Email:</strong> ' . htmlspecialchars($data['email']) . '<br>';
                echo '<strong>IP:</strong> ' . htmlspecialchars($data['ip']) . '<br>';
                echo '<strong>Message:</strong><br>' . nl2br(htmlspecialchars($data['message']));
                echo '</div>';
            }
        }
    }
} else {
    echo '<p>No submissions log file found.</p>';
}

echo '<p><a href="?password=' . $admin_password . '&clear=1">Clear all submissions</a></p>';

// Clear submissions if requested
if (isset($_GET['clear']) && $_GET['clear'] === '1') {
    if (file_exists($log_file)) {
        unlink($log_file);
        echo '<p style="color:green">All submissions cleared!</p>';
        echo '<script>setTimeout(function(){location.href="?password=' . $admin_password . '";}, 2000);</script>';
    }
}

echo '</body></html>';
?>