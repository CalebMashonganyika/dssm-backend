<?php
// logger.php - Logging utility

function logMessage($message, $level = 'INFO') {
    $logsDir = __DIR__ . '/../logs';

    // Create logs directory if it doesn't exist
    if (!is_dir($logsDir)) {
        mkdir($logsDir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;

    $logFile = $logsDir . '/app.log';
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

function logError($message) {
    logMessage($message, 'ERROR');
}

function logInfo($message) {
    logMessage($message, 'INFO');
}

function logDebug($message) {
    logMessage($message, 'DEBUG');
}
?>