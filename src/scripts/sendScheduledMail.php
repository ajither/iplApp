<?php

require '../application/bootstrap.php';

use library\SX\Email\EmailManager as EmailManager;

$_SESSION['api_version'] = "v1.0";
$emailManager = new EmailManager();
$emailManager->sendScheduledMail();
