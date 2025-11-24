<?php
// api.php - API information

header('Content-Type: application/json');

echo json_encode([
    'message' => 'DSSM Backend API Endpoints',
    'endpoints' => [
        'POST /paynow/initiate.php' => 'Initiate payment',
        'GET /paynow/poll.php?reference=...' => 'Poll payment status',
        'GET /api/check_subscription.php?phone=...' => 'Check subscription',
        'POST /api/activate_subscription.php' => 'Activate subscription'
    ]
]);
?>