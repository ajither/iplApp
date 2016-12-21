<?php

require '../application/bootstrap.php';

use library\SX\Notification\NotificationManager as NotificationManager;

try {
    NotificationManager::noReplyReminder();
} catch (\Exception $ex) {
    library\SX\Common\Logger::error_logger($ex->getMessage());
}