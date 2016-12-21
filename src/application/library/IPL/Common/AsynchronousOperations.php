<?php

/**
 * @author     Ajith E R, <ajith@salesx.io>
 * @date       December 19, 2016
 * @brief
 */

namespace library\IPL\Common;

use \library\IPL\Email\GmailIMAPHandler as GmailIMAPHandler;
use \library\IPL\Email\OtherIMAPHandler as IMAPHandler;
use \library\IPL\Email\Utils as EmailUtils;
use \library\IPL\Email\EmailManager as EmailManager;
use \models\Google_Access_Tokens as Google_Access_Tokens;
use \models\Imap_Smtp_Credentials as Imap_Smtp_Credentials;

class AsynchronousOperations {
    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief      Login API
     */
    public static function fetchIncomingGmail($batchData) {
        foreach ($batchData as $processData) {
            $searchParams = null;
            $pidbatch = pcntl_fork();
            if ($pidbatch == -1) {
                Logger::error_logger('Error: could not fork');
            } else if ($pidbatch) {
                $status = pcntl_wait($status);
            } else {
                foreach ($processData as $dataChunk) {
                    if (EmailUtils::mailQuotaCheck($dataChunk->user_id)) {
                        $pidInternal = pcntl_fork();
                        if ($pidInternal == -1) {
                            Logger::error_logger('Error: could not fork');
                        } else if ($pidInternal) {
                            //Parent thread. Do nothing,
                        } else {
                            $googleTokenModel = new Google_Access_Tokens();
                            $googleTokenModel->syncLockAccount($dataChunk->email_id, $dataChunk->user_id, 1);
                            GmailIMAPHandler::syncAllMail($dataChunk->email_id, $dataChunk->id, $dataChunk->user_id, true, null);
                            $googleTokenModel->syncLockAccount($dataChunk->email_id, $dataChunk->user_id, 0);
                            return;
                        }
                    }
                }
                return;
            }
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief
     */
    public static function fetchIncomingImapMail($batchData) {
        foreach ($batchData as $processData) {
            $searchParams = null;
            $pidbatch = pcntl_fork();
            if ($pidbatch == -1) {
                Logger::error_logger('Error: could not fork');
            } else if ($pidbatch) {
                $status = pcntl_wait($status);
            } else {
                foreach ($processData as $dataChunk) {
                    if (EmailUtils::mailQuotaCheck($dataChunk->user_id)) {
                        $pidInternal = pcntl_fork();
                        if ($pidInternal == -1) {
                            Logger::error_logger('Error: could not fork');
                        } else if ($pidInternal) {
                            //Parent thread. Do nothing,
                        } else {
                            $imapModel = new Imap_Smtp_Credentials();
                            $imapModel->syncLockAccount($dataChunk->user_name, $dataChunk->user_id, 1);
                            IMAPHandler::syncAllMail($dataChunk);
                            $imapModel->syncLockAccount($dataChunk->user_name, $dataChunk->user_id, 0);
                            return;
                        }
                    }
                }
                return;
            }
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief
     */
    public static function updateGoogleSentMail($arguments) {
        $pidInternal = pcntl_fork();
        if ($pidInternal == -1) {
            Logger::error_logger('Error: could not fork');
        } else if ($pidInternal) {
            //Parent thread. Do nothing,
        } else {
            GmailIMAPHandler::updateGoogleSentMail($arguments);
            return;
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief
     */
    public static function initialMailSync($userId) {
        $pidInternal = pcntl_fork();
        if ($pidInternal == -1) {
            Logger::error_logger('Error: could not fork');
        } else if ($pidInternal) {
            //Parent thread. Do nothing,
        } else {
            EmailManager::fetchInitialUserMail($userId);
            return;
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief
     */
    public static function updateOtherSentMail($arguments) {
        $pidInternal = pcntl_fork();
        if ($pidInternal == -1) {
            Logger::error_logger('Error: could not fork');
        } else if ($pidInternal) {
            //Parent thread. Do nothing,
        } else {
            IMAPHandler::updateSentMail($arguments);
            return;
        }
    }

}
