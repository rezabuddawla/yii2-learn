<?php

namespace app\components;


use app\models\Task;
use Yii;
use yii\db\ActiveQuery;

class TaskHelper{
    const TASK_PREFIX = "_tasks";

    public static function getTasksQuery() : ActiveQuery
    {
        $currentUser = UtilityHelper::getUserInformation();
        $cacheKey = self::TASK_PREFIX . $currentUser->auth_key;

        $tasks = Yii::$app->cache->get($cacheKey);

        if (!($tasks)) {
            $tasks = self::prepareTaskQuery($currentUser->username);
            Yii::$app->cache->set($cacheKey, $tasks, 3600);
        }

        return $tasks;
    }

    private static function prepareTaskQuery($username) : ActiveQuery{
        return Task::find()
            ->select(['title', 'slug', 'priority', 'created_by', 'description'])
            ->where(['assignee' => $username])
            ->andWhere(['task.deleted_at' => null]);
    }
}