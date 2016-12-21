<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       May 12, 2016
 * @brief      This class defines the mail message.
 */

namespace library\IPL\Email;

class SXMailMessage {

    protected $_isHTML;
    protected $_From;
    protected $_FromName;
    protected $_To;
    protected $_ReplyTo;
    protected $_Subject;
    protected $_CC = array();
    protected $_BCC = array();
    protected $_Attachment = array();
    protected $_Body;
    protected $_AltBody;
    protected $_SMTPConfig = array();
    protected $_MailSettings = array();
    protected $_References = "";

    public function setMailSettings($mailSettings) {
        $this->_MailSettings = $mailSettings;
    }

    public function getMailSettings() {
        return $this->_MailSettings;
    }

    public function setReferences($references) {
        $this->_References = $references;
    }

    public function getReferences() {
        return $this->_References;
    }

    public function setAttachment($attachment) {
        $this->_Attachment = $attachment;
    }

    public function getAttachment() {
        return $this->_Attachment;
    }

    public function setBCC($bcc) {
        $this->_BCC = $bcc;
    }

    public function getBCC() {
        return $this->_BCC;
    }

    public function setCC($cc) {
        $this->_CC = $cc;
    }

    public function getCC() {
        return $this->_CC;
    }

    public function setSMTPConfig($smtpConfig) {
        $this->_SMTPConfig = $smtpConfig;
    }

    public function getSMTPConfig() {
        return $this->_SMTPConfig;
    }

    public function setIsHTML($isHtml) {
        $this->_isHTML = $isHtml;
    }

    public function getIsHTML() {
        return $this->_isHTML;
    }

    public function setFrom($from) {
        $this->_From = $from;
    }

    public function getFrom() {
        return $this->_From;
    }

    public function setFromName($fromName) {
        $this->_FromName = $fromName;
    }

    public function getFromName() {
        return $this->_FromName;
    }

    public function setTo($to) {
        $this->_To = $to;
    }

    public function getTo() {
        return $this->_To;
    }

    public function setReplyTo($replyTo) {
        $this->_ReplyTo = $replyTo;
    }

    public function getReplyTo() {
        return $this->_ReplyTo;
    }

    public function setSubject($subject) {
        $this->_Subject = $subject;
    }

    public function getSubject() {
        return $this->_Subject;
    }

    public function setBody($body) {
        $this->_Body = $body;
    }

    public function getBody() {
        return $this->_Body;
    }

    public function setAltBody($altBody) {
        $this->_AltBody = $altBody;
    }

    public function getAltBody() {
        return $this->_AltBody;
    }

}
