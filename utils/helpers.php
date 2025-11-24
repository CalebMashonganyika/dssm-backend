<?php
// helpers.php - Helper functions

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validatePhone($phone) {
    return preg_match('/^\+?263\d{9}$/', $phone) || preg_match('/^0\d{10}$/', $phone);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateReference() {
    return 'sub_' . time() . '_' . rand(1000, 9999);
}
?>