<?php

/**
 * @author     Ajith E R, <ajith@salesx.io>
 * @date       December 19, 2016
 * @brief
 */

namespace library\IPL\Signup;

use \library\IPL\Hash\HashManager as HashManager;
use \library\IPL\Login\LoginManager as LoginManager;
use \models\User as User;
use \models\Organization as Organization;
use \models\Organization_User_Mapping as Organization_User_Mapping;
use \models\Lead as Lead;
use \models\Activity as Activity;
use \models\Activity_Assigned_Mapping as Activity_Assigned_Mapping;
use \models\Notes as Notes;
use \models\User_Profile as User_Profile;
use \models\Pipeline as Pipeline;
use \library\IPL\Common\Utils as Utils;
use \models\Pipeline_Stages as Pipeline_Stages;
use \library\IPL\Common\Constants as Constants;
use \models\Lead_Custom_Status_Settings as Lead_Custom_Status_Settings;
use \models\Lead_Custom_Status as Lead_Custom_Status;

class SignupManager {

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 27, 2016
     * @authorEdited Ajith E R,<ajith@salesx.io>
     * @editDate : October 17 ,2016
     * @brief      Signup operation.
     * @param      $payload   Payload data.
     * @return     Json response
     */
    public static function signupAction($payload) {
        $data = array();
        $data['user_name'] = $payload['email'];

        $data['password'] = HashManager::passwordHash($payload['password']);
        $data['role_id'] = $payload['role_id'];
        $data['is_active'] = 1;
        try {
            $user = new User();
            $user_id = $user->addUser($data);
        } catch (\Exception $e) {
            $response['success'] = "false";
            $response['message'] = "User name already exists";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
        $data = array();
        $data['name'] = $payload['organisation'];
        $data['owner_id'] = $user_id;

        try {
            $organization = new Organization();
            $orgId = $organization->addOrganisation($data);
        } catch (\Exception $e) {
            $user = new User();
            $user->deleteUser($user_id);
            $response['success'] = "false";
            $response['message'] = "Organisation already exists";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
        $orgUserMapping = new Organization_User_Mapping();
        $mapping['user_id'] = $user_id;
        $mapping['organization_id'] = $orgId;
        $orgUserMapping->addOrganizationUserMapping($mapping);
        
        foreach (Constants::$DEFAULT_LEAD_STATUS as $key => $value) {        
             $dataStatus['organization_id'] = $orgId;
             $dataStatus['status'] = $value;
             $leadCustomStatusModel = new Lead_Custom_Status_Settings();
             $leadCustomStatusModel->addLeadCustomStatus($dataStatus);
        }
            
        $lead = new Lead();
        $leadData['name'] = "SalesX.io";
        $leadData['owner_id'] = $user_id;
        $leadData['status'] = "Active";
        $leadData['organisation_id'] = $orgId;
        $leadData['updated_by'] = $user_id;
        $lead->addLead($leadData);
        
        $pipeline = new Pipeline();
        $pipelinestage = new Pipeline_Stages();
        $pipelineDefault['name'] = "Sales Pipe Line";
        $pipelineDefault['organization_id'] = $orgId;
        $pipelineDefault['created_by'] = $user_id;
        $pipelineDefault['updated_by'] = $user_id;
        $pipelineId = $pipeline->addPipeline($pipelineDefault);
        foreach (Constants::$DEFAULT_PIPELINE as $key => $value) {
            $pipelineStage['pipeline_id'] = $pipelineId;
            $pipelineStage['sequence_number'] = $key;
            $pipelineStage['stage'] = $value;
            $pipelineStage['expiry_period'] = '5';
            $pipelinestage->addPipelineStage($pipelineStage);
        }
        
        $payload['user_id'] = $user_id;
        $payload['org_id'] = $orgId;
        return Utils::userInitialAction($payload);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       August 29, 2016
     * @brief      Change password operation.
     * @param      $payload   Payload data.
     * @return     Json response
     */
    public static function changePassword($payload) {
        $user = new User();
        $userDetails = $user->fetchUserDetails($_SESSION['user_id']);
        if (LoginManager::validateLoginCredentials($userDetails->user_name, $payload['old_password'])) {
            $data['id'] = $_SESSION['user_id'];
            $data['password'] = HashManager::passwordHash($payload['new_password']);
            $user->updateUser($data);
            $response['success'] = "true";
            $response['message'] = "Password changed successfully.";
            return json_encode($response, JSON_NUMERIC_CHECK);
        } else {
            $response['success'] = "false";
            $response['message'] = "Wrong password provided.";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
    }

}
