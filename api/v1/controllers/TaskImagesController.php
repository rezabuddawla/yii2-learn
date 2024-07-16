<?php

namespace app\api\v1\controllers;

use app\models\TaskImages;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;

class TaskImagesController extends ActiveController
{
    public $modelClass = TaskImages::class;

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['authMethods'] = [
            'class' => HttpBearerAuth::class
        ];

        return $behaviors;
    }
}