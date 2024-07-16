<?php

namespace app\models;

use app\components\RedisHelper;
use app\components\UtilityHelper;
use app\models\helper\BaseActiveRecord;
use Yii;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\db\ActiveQuery;
use yii\web\HttpException;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $fullname
 * @property string $email
 * @property string $password_hash
 * @property string $access_token
 * @property string $auth_key
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class User extends BaseActiveRecord implements IdentityInterface
{

    public function fields()
    {
        return ['username', 'fullname', 'email'];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['username', 'fullname', 'email', 'password_hash', 'auth_key'], 'required'],
            [['username'], 'string', 'max' => 15],
            [['fullname'], 'string', 'max' => 256],
            [['email', 'password_hash'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['username', 'email'], 'unique'],
            ['username', 'match', 'pattern' => '/^[a-z0-9]+$/', 'message' => 'Username can only contain lowercase letters and numbers.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'fullname' => 'Fullname',
            'email' => 'Email',
            'password_hash' => 'Password Hash',
            'auth_key' => 'Auth Key',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    public static function findIdentity($id)
    {
        return static::find()
            ->select(['id', 'username', 'fullname', 'email', 'auth_key', 'created_at', 'updated_at'])
            ->where(['id' => $id])
            ->one();
    }

    public function afterSave($insert, $changedAttributes)
    {
        Yii::$app->cache->delete(RedisHelper::ALL_USER);
    }

    /**
     * @throws HttpException
     */
    public static function findIdentityByAccessToken($token, $type = 'access')
    {

        $tokenModel = ($type === 'refresh') ? RefreshToken::class : AccessToken::class;

        $tokenRecord = $tokenModel::find()
            ->where(['token' => $token])
            ->one();

        if (!$tokenRecord) {
            throw new HttpException(404, 'Access token not found.');
        }
        $expiresAtTimestamp = strtotime($tokenRecord->expires_at);
        if ($expiresAtTimestamp < time()) {
            throw new HttpException(401, $type == 'access' ? 'Token has expired.' : 'Token has expired. You must login again');
        }
        $user = static::findOne(['id' => $tokenRecord->user_id]);
        if (!$user) {
            throw new HttpException(404, 'User not found. Please login again.');
        }
        return $user;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public static function findByUsername(string $username): ?User
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * @throws Exception
     */
    public function setPassword(string $password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * @throws Exception
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function generateAccessToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString();
    }


    //---------------------------------------Relationship function-------------------------------------------\\
    /**
     * Gets query for [[Tasks]].
     *
     * @return ActiveQuery
     */
    public function getTasks(): ActiveQuery
    {
        return $this->hasMany(Task::class, ['assignee' => 'username']);
    }

    /**
     * Gets query for [[Tasks0]].
     *
     * @return ActiveQuery
     */
    public function getTasks0(): ActiveQuery
    {
        return $this->hasMany(Task::class, ['created_by' => 'username']);
    }

    /**
     * Gets query for [[Tasks1]].
     *
     * @return ActiveQuery
     */
    public function getTasks1(): ActiveQuery
    {
        return $this->hasMany(Task::class, ['updated_by' => 'username']);
    }

    public function getAccessTokens(): ActiveQuery
    {
        return $this->hasOne(AccessToken::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[RefreshTokens]].
     *
     * @return ActiveQuery
     */
    public function getRefreshTokens(): ActiveQuery
    {
        return $this->hasOne(RefreshToken::class, ['user_id' => 'id']);
    }

    public static function getAllUser()
    {
        $userKey = RedisHelper::ALL_USER;
        if (!($users = Yii::$app->cache->get($userKey))) {
            $users = User::find()
                ->select(['username', 'fullname'])
                ->asArray()
                ->all();
            Yii::$app->cache->set($userKey, $users, 3600);
        }

        return $users;
    }
}
