<?php
header('Content-Type: text/html; charset=UTF-8');

// Enable debugging (set to `false` in production)
$debug = true;

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

// Required fields validation
$required = ['name', 'email', 'message'];
$errors = [];

foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $errors[] = ucfirst($field) . " is required.";
    }
}

// Validate email format
if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}

if (!empty($errors)) {
    echo "<script>alert('" . implode("\\n", $errors) . "'); window.history.back();</script>";
    exit;
}

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "portfolio";

// Error logging function
function logError($error)
{
    file_put_contents("error_log.txt", date("Y-m-d H:i:s") . " - " . $error . "\n", FILE_APPEND);
}

// Sanitize inputs
$name = htmlspecialchars(trim($_POST['name']));
$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$project = htmlspecialchars(trim($_POST['project'] ?? ''));
$message = htmlspecialchars(trim($_POST['message']));

// =============================================
// DATABASE STORAGE
// =============================================
try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Create table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        project VARCHAR(100),
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if (!$conn->query($sql)) {
        throw new Exception("Table creation failed: " . $conn->error);
    }

    // Insert message
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, project, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $project, $message);
    $stmt->execute();
    $stmt->close();

    // Track visitor (optional)
    $page = $_SERVER['HTTP_REFERER'] ?? 'direct';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $trackStmt = $conn->prepare("INSERT INTO visitor_analytics (page_visited, ip_address, user_agent) VALUES (?, ?, ?)");
    if ($trackStmt) {
        $trackStmt->bind_param("sss", $page, $ip, $userAgent);
        $trackStmt->execute();
        $trackStmt->close();
    }

    $conn->close();

    // Success response
    echo "<script>
        alert('✅ Message sent successfully!');
        window.location.href = 'index.html';
    </script>";

} catch (Exception $e) {
    logError($e->getMessage());
    echo "<script>
        alert('Message saved, but processing failed. I\'ll contact you soon.');
        window.location.href = 'index.html';
    </script>";
}
?>