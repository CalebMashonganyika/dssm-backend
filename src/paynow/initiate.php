<?php
// initiate.php - Initiate Paynow payment

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

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$reference = $input['reference'] ?? '';
$amount = $input['amount'] ?? 0;
$phone = $input['phone'] ?? '';
$email = $input['email'] ?? '';

if (!$reference || !$amount || !$phone || !$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    // Prepare Paynow data
    $paynowData = [
        'resulturl' => RESULT_URL,
        'returnurl' => RETURN_URL,
        'reference' => $reference,
        'amount' => $amount,
        'phone' => $phone,
        'email' => $email,
        'sendemail' => 'true',
        'sendmessage' => 'true'
    ];

    // Generate hash
    $hashString = PAYNOW_ID . $reference . $amount . $email . RESULT_URL . RETURN_URL;
    $hash = hash('sha512', $hashString . PAYNOW_KEY);
    $paynowData['hash'] = $hash;

    // Send to Paynow
    $ch = curl_init('https://www.paynow.co.zw/interface/initiatetransaction');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($paynowData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        logError("Paynow API error: HTTP $httpCode");
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Payment gateway error']);
        exit;
    }

    parse_str($response, $result);

    if (!isset($result['status']) || $result['status'] !== 'Ok') {
        logError("Paynow initiation failed: " . $response);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Payment initiation failed']);
        exit;
    }

    // Store poll URL in database for later polling
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO payment_logs (reference, payload) VALUES (?, ?)");
    $stmt->execute([$reference, json_encode($result)]);

    logInfo("Payment initiated: $reference");

    echo json_encode([
        'success' => true,
        'redirect_url' => $result['browserurl'] ?? '',
        'poll_url' => $result['pollurl'] ?? '',
        'reference' => $reference
    ]);

} catch (Exception $e) {
    logError("Initiate error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>