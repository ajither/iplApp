<?php

require '../application/bootstrap.php';

use library\SX\Common\AsynchronousOperations as AsynchronousOperations;
use library\SX\Common\Logger as Logger;

$userId = $argv[1];
try {
    AsynchronousOperations::initialMailSync($userId);
    Logger::debug_logger("Initial Mail sync completed for " . $userId);
} catch (\Exception $ex) {
    Logger::error_logger($ex->getMessage());
    Logger::error_logger($ex->getLine());
    Logger::error_logger($ex->getFile());
}