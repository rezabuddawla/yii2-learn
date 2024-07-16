<?php

namespace app\api\v1\controllers;

use app\components\UtilityHelper;
use app\models\Task;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class TaskController extends ActiveController
{
    public $modelClass = Task::class;

//    public function behaviors(): array
//    {
//        $behaviors = parent::behaviors();
//        $behaviors['authenticator']['authMethods'] = [
//            'class' => HttpBearerAuth::class
//        ];
//
//        return $behaviors;
//    }


    /**
     * @param $action
     * @param Task $model
     * @param $params
     * @return void
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        $user = UtilityHelper::getUserInformation();
        if (in_array($action, ['view', 'update', 'delete']) && ($model->assignee !== $user->username || $model->created_by !== $user->username)) {
            throw new ForbiddenHttpException("You don't have permission to perform this action.");
        }
    }
    public function actionTest(): string{
        return "test";
    }
}