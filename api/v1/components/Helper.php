<?php

namespace app\api\v1\components;

use app\components\UtilityHelper;
use app\models\AccessToken;
use Yii;
use yii\base\Exception;
use DateInterval;
use DateTime;

class Helper
{


    /**
     * @param $class
     * @param int $duration behaves like minute. Default value is 15.
     * @param null $user_id
     * @return string
     * @throws Exception
     * @throws \Exception
     */
    public static function generateToken($class, $user_id, int $duration = 15): string
    {
        $token = Yii::$app->security->generateRandomString(64);
        $model = new $class([
            'user_id' => $user_id ?? UtilityHelper::getUserInformation()->id,
            'token' => $token,
            'expires_at' => (new DateTime())->add(new DateInterval('PT'.$duration.'M'))->format('Y-m-d H:i:s'),
        ]);
        $model->save();
        return $token;
    }

    public static function deleteTokens($user_id, ...$params)
    {
        foreach ($params as $param) {
            $model = new $param();
            $token = $model::find()->where(['user_id' => $user_id])->one();
            if ($token){
                $token->delete();
            }
        }
    }

}