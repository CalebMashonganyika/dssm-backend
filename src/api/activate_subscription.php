<?php
// activate_subscription.php - Activate subscription after successful payment

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../utils/db.php';
require_once __DIR__ . '/../utils/logger.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$reference = $input['reference'] ?? '';

if (!$reference) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Reference required']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Check if payment was successful
    $stmt = $pdo->prepare("SELECT payload FROM payment_logs WHERE reference = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$reference]);
    $log = $stmt->fetch();

    if (!$log) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Payment not found']);
        exit;
    }

    $payload = json_decode($log['payload'], true);
    if (($payload['status'] ?? '') !== 'Paid') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Payment not completed']);
        exit;
    }

    // Find user from payment data
    $phone = $payload['phone'] ?? '';
    $email = $payload['email'] ?? '';

    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? OR email = ? LIMIT 1");
    $stmt->execute([$phone, $email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Create user if not exists
        $stmt = $pdo->prepare("INSERT INTO users (phone, email) VALUES (?, ?)");
        $stmt->execute([$phone, $email]);
        $userId = $pdo->lastInsertId();
    } else {
        $userId = $user['id'];
    }

    // Activate subscription for 30 days
    $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
    $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, reference, status, expires_at) VALUES (?, ?, 'active', ?) ON DUPLICATE KEY UPDATE status = 'active', expires_at = ?");
    $stmt->execute([$userId, $reference, $expiresAt, $expiresAt]);

    logInfo("Subscription activated: $reference for user $userId");

    echo json_encode([
        'success' => true,
        'message' => 'Subscription activated',
        'expires_at' => $expiresAt
    ]);

} catch (Exception $e) {
    logError("Activate subscription error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>