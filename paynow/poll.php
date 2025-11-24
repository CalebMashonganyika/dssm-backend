<?php
// poll.php - Poll Paynow for payment status

require_once __DIR__ . '/../paynow/config.php';
require_once __DIR__ . '/../utils/db.php';
require_once __DIR__ . '/../utils/logger.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$reference = $_GET['reference'] ?? '';

if (!$reference) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Reference required']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Get poll URL from database
    $stmt = $pdo->prepare("SELECT payload FROM payment_logs WHERE reference = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$reference]);
    $log = $stmt->fetch();

    if (!$log) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Payment not found']);
        exit;
    }

    $payload = json_decode($log['payload'], true);
    $pollUrl = $payload['pollurl'] ?? '';

    if (!$pollUrl) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Poll URL not available']);
        exit;
    }

    // Poll Paynow
    $ch = curl_init($pollUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        logError("Poll error: HTTP $httpCode for $reference");
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Polling failed']);
        exit;
    }

    parse_str($response, $result);

    $status = $result['status'] ?? 'unknown';

    logInfo("Polled $reference: $status");

    echo json_encode([
        'success' => true,
        'status' => $status,
        'reference' => $reference
    ]);

} catch (Exception $e) {
    logError("Poll error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>