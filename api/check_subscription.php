<?php
// check_subscription.php - Check subscription status

require_once __DIR__ . '/../paynow/config.php';
require_once __DIR__ . '/../utils/db.php';
require_once __DIR__ . '/../utils/logger.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$phone = $_GET['phone'] ?? '';

if (!$phone) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Phone required']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Find user by phone
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['subscribed' => false, 'expires' => null]);
        exit;
    }

    // Check active subscription
    $stmt = $pdo->prepare("SELECT expires_at FROM subscriptions WHERE user_id = ? AND status = 'active' AND expires_at > NOW() ORDER BY expires_at DESC LIMIT 1");
    $stmt->execute([$user['id']]);
    $subscription = $stmt->fetch();

    if ($subscription) {
        echo json_encode([
            'subscribed' => true,
            'expires' => date('Y-m-d', strtotime($subscription['expires_at']))
        ]);
    } else {
        echo json_encode(['subscribed' => false, 'expires' => null]);
    }

} catch (Exception $e) {
    logError("Check subscription error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>