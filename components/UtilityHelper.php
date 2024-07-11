<?php

namespace app\components;

use app\models\Task;
use app\models\User;
use DateTime;
use Exception;
use Yii;

class UtilityHelper
{

    public static function getUserInformation()
    {
        return User::find()->where(['id' => Yii::$app->user->id])->cache(7200)->one();
    }
    public static function loadTask(string $task, string $userName)
    {
        return Task::find()
            ->where(['slug' => $task])
            ->andWhere([
                'or',
                ['assignee' => $userName],
                ['created_by' => $userName]
            ])
            ->one();
    }

    /**
     * @throws Exception
     */
    public static function getTimeDifference($time){
        $givenTime = new DateTime($time);
        $curTime = new DateTime();
        $diff = $curTime->diff($givenTime);
        return $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
    }
}