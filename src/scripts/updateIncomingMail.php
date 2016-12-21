<?php

require '../application/bootstrap.php';

use library\SX\Email\EmailManager as EmailManager;

$batchSize = 5;

$emailManager = new EmailManager();
$emailManager->updateIncomingEmails($batchSize);
