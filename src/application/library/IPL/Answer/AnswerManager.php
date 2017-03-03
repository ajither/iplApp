<?php
/**
 * Created by PhpStorm.
 * User: ajith
 * Date: 23/12/16
 * Time: 1:43 PM
 */
namespace library\IPL\Answer;

use models\Answer;
use models\Match_Update;
use \models\Match_Point as Match_Point;
use \models\User as User;
use models\User_Refferal;
use models\User_Total_Point;

class AnswerManager {

    public static function updateMatchAnswer($payload)
    {
        $data['user_id'] = $_SESSION['user_id'];
        $data['answer'] = strtolower($payload['answer']);
        $data['matchno'] = $payload['matchNo'];
        $data['update_time'] = date("Y-m-d H:i:s");

        $time = date("Y-m-d H:i:s");
        $matchUpdateModel = new Match_Update();
        $matchDetails = $matchUpdateModel->matchStartTime($payload['matchNo']);
        $timeUp = date("Y-m-d H:i:s", strtotime("-30 minutes", strtotime($matchDetails['startTime'])));
        if($time >= $timeUp){
            $response['success'] = "false";
            $response['message'] = "Time Up Wait For Next Match!";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }

        $answerModel = new Answer();
        $answerModel->setCurrentMatchAnswer($data);
        $response['success'] = "true";
        $response['message'] = "Answer Successfully Submitted";
        return json_encode($response, JSON_NUMERIC_CHECK);

    }

    public static function updateMatchScore($payload)
    {
        $answ = strtolower($payload['answer']);
        $matchno = $payload['matchNo'];
        $answerModel = new Answer();
        $userid = $answerModel->fetchWinners($answ,$matchno);
        foreach ($userid as $key => $value){
            $matchPointModel = new Match_Point();
            $point = $matchPointModel->getPoint($value->user_id);
            $point = $point+2;

            $matchPointModel = new Match_Point();
            $totalcorrectGuess = $matchPointModel->getCurrectGuessNo($value->user_id);
            $totalcorrectGuess = $totalcorrectGuess+1;

            $matchPoint['user_id'] = $value->user_id;
            $matchPoint['matchpoint'] = $point;
            $matchPoint['nocurrectguess'] = $totalcorrectGuess;
            $matchPointModel = new Match_Point();
            $matchPointModel->updateMatchpoint($matchPoint);

            $userReferralModel = new User_Refferal();
            $referralPoint = $userReferralModel->getReferralPoint($value->user_id);
            $totalPoint['totalpoint'] = $point+$referralPoint;
            $totalPoint['user_id'] = $value->user_id;
            $totalPointModel = new User_Total_Point();
            $totalPointModel->updateTotalPoint($totalPoint);

        }

        $response['success'] = "true";
        $response['message'] = "winner score updated";
        return json_encode($response, JSON_NUMERIC_CHECK);

    }
}