<?php

require '../application/bootstrap.php';

use library\SX\Common\AsynchronousOperations as AsynchronousOperations;

$arguments = json_decode($argv[1], true);
if ($arguments['type'] == 'GMAIL') {
    AsynchronousOperations::updateGoogleSentMail($arguments);
} else {
    AsynchronousOperations::updateOtherSentMail($arguments);
}