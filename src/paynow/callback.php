<?php
// callback.php - Paynow callback handler

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../utils/db.php';
require_once __DIR__ . '/../utils/logger.php';

// Log all incoming data
logInfo("Callback received: " . json_encode($_POST));

$reference = $_POST['reference'] ?? '';
$paynow_reference = $_POST['paynowreference'] ?? '';
$amount = $_POST['amount'] ?? '';
$status = $_POST['status'] ?? '';
$hash = $_POST['hash'] ?? '';

if (!$reference || !$amount || !$status) {
    logError("Invalid callback data");
    http_response_code(400);
    exit;
}

// Verify hash
$hashString = PAYNOW_ID . $reference . $paynow_reference . $amount . $status;
$expectedHash = hash('sha512', $hashString . PAYNOW_KEY);

if ($hash !== $expectedHash) {
    logError("Hash verification failed for $reference");
    http_response_code(400);
    exit;
}

try {
    $pdo = getDBConnection();

    // Update payment log
    $stmt = $pdo->prepare("UPDATE payment_logs SET payload = JSON_SET(payload, '$.status', ?, '$.paynow_reference', ?) WHERE reference = ?");
    $stmt->execute([$status, $paynow_reference, $reference]);

    // If paid, activate subscription
    if ($status === 'Paid') {
        // Find or create user
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';

        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? OR email = ? LIMIT 1");
        $stmt->execute([$phone, $email]);
        $user = $stmt->fetch();

        if (!$user) {
            $stmt = $pdo->prepare("INSERT INTO users (phone, email) VALUES (?, ?)");
            $stmt->execute([$phone, $email]);
            $userId = $pdo->lastInsertId();
        } else {
            $userId = $user['id'];
        }

        // Create or update subscription
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, reference, status, expires_at) VALUES (?, ?, 'active', ?) ON DUPLICATE KEY UPDATE status = 'active', expires_at = ?");
        $stmt->execute([$userId, $reference, $expiresAt, $expiresAt]);

        logInfo("Subscription activated for user $userId, reference $reference");
    }

    logInfo("Callback processed: $reference - $status");

} catch (Exception $e) {
    logError("Callback processing error: " . $e->getMessage());
    http_response_code(500);
    exit;
}

// Return 200 OK to Paynow
http_response_code(200);
echo 'OK';
?>