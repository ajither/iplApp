<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       May 14, 2016
 * @brief      This class is used to track emails being opened.
 */

namespace library\SX\Email;

use \models\Email_Content as Email_Content;
use \models\Mailbox as Mailbox;
use \models\Timeline as Timeline;

class EmailTracking {

    public static function handeEmailOpen($payload) {
        $geoDb = getenv('APPLICATION.PATH') . getenv('GEOLOCATION.DB');
        $geoip = geoip_open($geoDb, GEOIP_STANDARD);
        $ip_address = $_SESSION['ip_address'];
        $locationDetails = GeoIP_record_by_addr($geoip, $ip_address);
        $payload['details'] = array('location' => $locationDetails->city, 'time' => date("Y-m-d H:i:s"));
        geoip_close($geoip);
        $emailContent = new Email_Content('Mailbox');
        $emailContent->updateEmailOpened($payload);

        $filesize = filesize(getenv('APPLICATION.PATH') . '/application/resources/blank.png');
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Disposition: attachment; filename="blank.png"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . $filesize);
        readfile(getenv('APPLICATION.PATH') . '/application/resources/blank.png');

        $mailboxModel = new Mailbox();
        $mail = $mailboxModel->getSingleMailDetails($payload['sxuid']);
        if ($mail != null) {
            $fetchMailData = EmailManager::fetchMailLeadContactDetails($mail);
            $timelineData['type_id'] = $payload['sxuid'];
            $timelineData['type'] = 'MAIL';
            $timelineData['header'] = 'Email Opened';
            $timelineData['time'] = date("Y-m-d H:i:s");
            $timelineData['user_id'] = $fetchMailData['user_id'];
            $timelineData['description'] = $fetchMailData['subject'];

            if ($fetchMailData['lead_id'] != "") {
                $timelineData['lead_id'] = $fetchMailData['lead_id'];
            }
            if ($fetchMailData['lead_name'] != "") {
                $timelineData['lead_name'] = $fetchMailData['lead_name'];
            }
            if ($fetchMailData['contact_id'] != "") {
                $timelineData['contact_id'] = $fetchMailData['contact_id'][0];
            }
            if ($fetchMailData['contact_name'] != "") {
                $timelineData['contact_name'] = $fetchMailData['contact_name'];
            }

            $timelineModel = new Timeline();
            $timelineModel->updateMailOpenRecord($timelineData);
        }
    }

}
