<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       September 28, 2016
 * @brief      Fetches all emails from mongdb and writes the contents into files,
 *             splitting them into pre-defined batches. 
 * @version    v1.0
 */
require '../application/bootstrap.php';

$batchSize = 100;
$skip = 0;
$limit = $batchSize;
$fileCount = 0;
while (true) {
    $fileCount++;
    echo "Writing file no. " . $fileCount . PHP_EOL;
    $file = fopen("data_set_" . $fileCount . ".csv", "w");
    fputcsv($file, array("From", "to", "Subject", "Text-Plain"));
    $mailboxModel = new models\Mailbox();
    $result = $mailboxModel->getMailForDataset($skip, $limit);
    $collectionCount = 0;
    foreach ($result as $mail) {
        $collectionCount++;
        if ($mail['fromAddress'] == "") {
            $mail['fromAddress'] = $mail['fromName'];
        }
        $mail['fromAddress'] = str_replace(",", "", $mail['fromAddress']);
        $mail['fromAddress'] = str_replace(PHP_EOL, "", $mail['fromAddress']);
        $mail['fromAddress'] = str_replace('\n', "", $mail['fromAddress']);
        $mail['fromAddress'] = str_replace('\r', "", $mail['fromAddress']);
        $mail['fromAddress'] = str_replace('\r\n', "", $mail['fromAddress']);
        $mail['fromAddress'] = json_encode($mail['fromAddress']);

        $mail['to'] = str_replace(',\r\n', " ", $mail['to']);
        $mail['to'] = str_replace(",", "", $mail['to']);
        $mail['to'] = str_replace(PHP_EOL, "", $mail['to']);
        $mail['to'] = str_replace('\n', "", $mail['to']);
        $mail['to'] = str_replace('\r', "", $mail['to']);
        $mail['to'] = json_encode($mail['to']);


        $mail['subject'] = str_replace(",", "", $mail['subject']);
        $mail['subject'] = str_replace(PHP_EOL, "", $mail['subject']);
        $mail['subject'] = str_replace('\n', "", $mail['subject']);
        $mail['subject'] = str_replace('\r', "", $mail['subject']);
        $mail['subject'] = str_replace('\r\n', "", $mail['subject']);
        $mail['subject'] = json_encode($mail['subject']);

        if ($mail['textPlain'] == "") {
            $mail['textPlain'] = strip_tags($mail['textHtml']);
        }

        $mail['textPlain'] = str_replace(",", "", $mail['textPlain']);
        $mail['textPlain'] = str_replace(PHP_EOL, "", $mail['textPlain']);
        $mail['textPlain'] = str_replace('\n', "", $mail['textPlain']);
        $mail['textPlain'] = str_replace('\r', "", $mail['textPlain']);
        $mail['textPlain'] = json_encode($mail['textPlain']);

        $mailData = array($mail['fromAddress'], $mail['to'], $mail['subject'], $mail['textPlain']);
        fputcsv($file, $mailData);
    }

    fclose($file);
    $fileContents = file_get_contents("data_set_" . $fileCount . ".csv");
    $fileContents = str_replace('"""', '', $fileContents);
    file_put_contents("data_set_" . $fileCount . ".csv", $fileContents);
    $skip = $skip + $batchSize;
    $limit = $limit + $batchSize;
    if ($collectionCount < $batchSize) {
        break;
    }
}