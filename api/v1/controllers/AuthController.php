<?php

namespace app\api\v1\controllers;

use app\api\v1\components\Helper;
use app\api\v1\filters\auth\MyHttpBearerAuth;
use app\components\UtilityHelper;
use app\models\AccessToken;
use app\models\LoginForm;
use app\models\RefreshToken;
use app\models\RegisterForm;
use app\models\User;
use Yii;
use yii\base\Exception;
use yii\rest\ActiveController;

class AuthController extends ActiveController
{
    public $modelClass = User::class;

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['only'] = ['get-users', 'refresh-token'];
        $behaviors['authenticator']['authMethods'] = [
            [
                'class' => MyHttpBearerAuth::class,
                'tokenType' => 'access',
                'only' => ['get-users'],
            ],
            [
                'class' => MyHttpBearerAuth::class,
                'tokenType' => 'refresh',
                'only' => ['refresh-token'],
            ]
        ];

        $methodRule = array(
            "get-users" => ["GET", "HEAD"],
            "refresh-token" => ["GET", "HEAD"],
            "register" => ["POST"],
            "logout" => ["POST"],
            "login" => ["POST"],
        );

        $behaviors['verbFilter']['actions'] = $behaviors['verbFilter']['actions'] + $methodRule;
        return $behaviors;
    }

    /**
     * @throws Exception
     */
    public function actionRegister(): array
    {
        $form = new RegisterForm();
        $form->attributes = Yii::$app->request->post();
        $form->validate();
        if ($form->register()){
            return [
                "message" => "Registration Successful. Please login!",
            ];
        }
        return [
            "message" => $form->errors,
        ];
    }

    /**
     * @throws Exception
     */
    public function actionLogin(): array
    {
        $form = new LoginForm();
        $form->attributes = Yii::$app->request->post();
        $form->validate();
        if ($form->login()){
            $user = UtilityHelper::getUserInformation();
            Helper::deleteTokens($user->id, AccessToken::class, RefreshToken::class);
            $accessToken = Helper::generateToken(AccessToken::class, $user->id);
            $refreshToken = Helper::generateToken(RefreshToken::class, $user->id, 30 * 24 * 60);
            return [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken
            ];
        }
        return $form->errors;
    }

    /**
     * @throws Exception
     */
    public function actionRefreshToken(): array
    {
        $user = UtilityHelper::getUserInformation();
        Helper::deleteTokens($user->id, AccessToken::class);
        $accessToken = Helper::generateToken(AccessToken::class, $user->id);
        return [
            'access_token' => $accessToken,
        ];
    }

    public function actionLogout()
    {
        if (Yii::$app->user->isGuest) {
            return "You must login first";
        }
        $user_id = UtilityHelper::getUserInformation()->id;
        Helper::deleteTokens($user_id, AccessToken::class, RefreshToken::class);
        Yii::$app->user->logout();
        return [
            'message' => "Logout successful"
        ];
    }

    public function actionGetUsers($query = null): array
    {
        $usersQuery = User::find()
            ->where(['!=', 'id', UtilityHelper::getUserInformation()->id])
            ->select(['username', 'fullname']);
        if ($query !== null) {
            $usersQuery->andWhere(
                ['like', 'fullname', $query],
            );
        }
        return $usersQuery->limit(20)->asArray()->all();
    }

}