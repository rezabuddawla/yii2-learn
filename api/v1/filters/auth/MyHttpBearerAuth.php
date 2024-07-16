<?php
namespace app\api\v1\filters\auth;

use yii\filters\auth\HttpBearerAuth;
use yii\web\HttpException;
use yii\web\IdentityInterface;

class MyHttpBearerAuth extends HttpBearerAuth
{

    public string $tokenType = '';


    /**
     * @throws HttpException
     */
    public function authenticate($user, $request, $response): ?IdentityInterface
    {
        $authHeader = $request->getHeaders()->get('Authorization');
        if ($authHeader !== null) {
            if ($this->pattern !== null && preg_match($this->pattern, $authHeader, $matches)) {
                $identity = $user->loginByAccessToken($matches[1], $this->tokenType);
                if ($identity === null) {
                    $this->handleFailure($response);
                }
                return $identity;
            }
        }
        return null;
    }

}