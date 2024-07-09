<?php

namespace app\controllers\Helper;

use app\models\Task;
use app\models\User;
use Yii;

class UtilityHelper
{

    public static function username(): string
    {

        $user = User::find()->where(['id'=>Yii::$app->user->id])->cache(7200)->one();
        return $user->username;
    }

    public static function loadTaskWithImage(string $task, string $userName)
    {
        if (!($fullTask = Yii::$app->cache->get(RedisHelper::SINGLE_TASK_WITH_IMAGE_PREFIX.$task))) {
            $fullTask = Task::find()
                ->joinWith('taskImages')
                ->where(['slug' => $task])
                ->andWhere([
                    'or',
                    ['assignee' => $userName],
                    ['created_by' => $userName]
                ])
                ->one();
            Yii::$app->cache->set(RedisHelper::SINGLE_TASK_WITH_IMAGE_PREFIX.$task, $fullTask);
        }
        return $fullTask;
    }


    public static function loadTaskWithOutImage(string $task, string $userName)
    {
        if (!($fullTask = Yii::$app->cache->get(RedisHelper::SINGLE_TASK_WITHOUT_IMAGE_PREFIX.$task))) {
            $fullTask = Task::find()
                ->where(['slug' => $task])
                ->andWhere([
                    'or',
                    ['assignee' => $userName],
                    ['created_by' => $userName]
                ])
                ->one();
            Yii::$app->cache->set(RedisHelper::SINGLE_TASK_WITHOUT_IMAGE_PREFIX.$task, $fullTask);
        }
        return $fullTask;
    }
}