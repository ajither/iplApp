<?php
/**
 * Created by PhpStorm.
 * User: ajith
 * Date: 23/12/16
 * Time: 1:43 PM
 */
namespace library\IPL\Answer;

use models\Answer;

class AnswerManager {

    public static function updateMatchAnswer($payload)
    {
        $data['user_id'] = $_SESSION['user_id'];
        $data['answer'] = strtolower($payload['answer']);
        $data['matchno'] = $payload['matchNo'];
        $answerModel = new Answer();
        $answerModel->setCurrentMatchAnswer($data);
        $response['success'] = "true";
        $response['message'] = "Answer Successfully Submitted";
        return json_encode($response, JSON_NUMERIC_CHECK);

    }
}