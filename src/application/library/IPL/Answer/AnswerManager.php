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
}