<?php
/**
 * Created by PhpStorm.
 * User: ajith
 * Date: 3/3/17
 * Time: 2:30 PM
 */
namespace library\IPL\Reward;

use models\RechargeModel;
use models\User_Total_Point;

class RewardManager {

    public static function updateRedeemRequest()
    {

        $totalPointModel = new User_Total_Point();
        $point = $totalPointModel->getTotalPoint($_SESSION['user_id']);
        if($point >= 50){
            $rechargeModel = new RechargeModel();
            $status = $rechargeModel->getRechargeStatus($_SESSION['user_id']);
            if($status != null){
                    $response['success'] = "true";
                    $response['message'] = "Your Request Processing Please Wait";
                    return json_encode($response, JSON_NUMERIC_CHECK);
            }
            $data['user_id'] = $_SESSION['user_id'];
            $data['request_amount'] = 50;
            $data['status'] = 'Processing';
            $rechargeModel = new RechargeModel();
            $rechargeModel->updateRequest($data);
            $response['success'] = "true";
            $response['message'] = "Your Request Processing";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }else{
            $response['success'] = "false";
            $response['message'] = "You Don't have enough point.";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }

    }
}