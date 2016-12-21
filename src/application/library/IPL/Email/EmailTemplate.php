<?php

/**
 * @author     Ajith E R, <ajith@salesx.io>
 * @date       December 8, 2016
 * @brief      This class handles all the operations related to Email Template
 * @details
 */

namespace library\SX\Email;
use \models\Email_Content as Email_Content;

class EmailTemplate {
     
    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 8, 2016
     * @brief      Save new Email templates.
     * @param      $data
     */
    public static function addEmailTemplate($payload) {
        $content['template_id'] = uniqid();
        $content['template_name'] = $payload['template_name'];
        if(isset($payload['template_subject'])){
          $content['template_subject'] = $payload['template_subject'];
        }
        if(isset($payload['template_body'])){
          $content['template_body'] = $payload['template_body'];  
        }        
        $content['user_id'] = (Integer) $_SESSION['user_id'];
        $content['template_sent'] = 0;
        $content['template_opened'] = 0;
        $content['template_replied'] = 0;
        $content['template_links'] = 0;
        $emailContent = new Email_Content('Mail_Template');
        $emailContent->saveTemplate($content);
        $response['success'] = "true";
        $response['template_id'] = $content['template_id'];
        $response['message'] = "Email Template Successfully Saved.";
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 8, 2016
     * @brief      Edit Email templates.
     * @param      $data
     */
    public static function editEmailTemplate($payload) {
      $emailContent = new Email_Content('Mail_Template');
      $template = $emailContent->fetchSingleTemplate($payload['template_id']);
      if(isset($payload['template_name'])){
        $template['template_name'] = $payload['template_name'];
      }
      if(isset($payload['template_subject'])){
        $template['template_subject'] = $payload['template_subject'];
      }
      if(isset($payload['template_body'])){
        $template['template_body'] = $payload['template_body'];
      }
      unset($template['_id']);
      $emailContent = new Email_Content('Mail_Template');
      $emailContent->editTemplate($template); 
      $response['success'] = "true";
      $response['message'] = "Email Template Successfully Edited.";
      return json_encode($response, JSON_NUMERIC_CHECK);
    }
    
    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 8, 2016
     * @brief      Delete Email templates.
     * @param      $data
     */
    public static function deleteEmailTemplate($payload) {
      $emailContent = new Email_Content('Mail_Template');
      $emailContent->deleteTemplate($payload['template_id']);            
      $response['success'] = "true";
      $response['message'] = "Email Template Successfully Deleted.";
      return json_encode($response, JSON_NUMERIC_CHECK);
    }
    
    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 8, 2016
     * @brief      Fetch Email templates.
     * @param      $data
     */
    public static function fetchEmailTemplate($payload) {
      $emailContent = new Email_Content('Mail_Template');
      $user_id = (Integer) $_SESSION['user_id'];
      $templates = $emailContent->fetchAllTemplate($user_id);           
      if($templates == null){
      $response['success'] = "false";
      $response['message'] = "Ooops No Templates";
      return json_encode($response, JSON_NUMERIC_CHECK); 
      }
      $mailTemplates = array();
      foreach ($templates as $emailtemplates){
          unset($emailtemplates['_id']);
          array_push($mailTemplates, $emailtemplates);
      }
      $response['success'] = "true";
      $response['templates'] = $mailTemplates;
      return json_encode($response, JSON_NUMERIC_CHECK);
    }
}