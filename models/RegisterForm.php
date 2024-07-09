<?php

namespace app\models;

use yii\base\Exception;
use yii\base\Model;

class RegisterForm extends Model
{
    public string $username='';
    public string $fullname='';
    public string $email='';
    public string $password='';
    public string $password_repeat='';

    public function rules(): array
    {
        return [
            [['username', 'email', 'fullname', 'password', 'password_repeat'], 'required'],
            ['email', 'email'],
            ['password', 'string', 'min' => 6],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Password did not match'],
            ['username', 'match', 'pattern' => '/^[a-z0-9]+$/', 'message' => 'Username can only contain lowercase letters and numbers.'],
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'This username has already been taken.'],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'This email has already been taken.'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels(): array
    {
        return [
            'username' => 'Username',
            'email' => 'Email',
            'fullname' => 'Full Name',
            'password' => 'Password',
            'password_repeat' => 'Repeat Password',
        ];
    }

    /**
     * @throws Exception
     */
    public function register()
    {

        if (!$this->validate()) {
            return false;
        }

        if (User::find()->where(['username' => $this->username])->exists()) {
            $this->addError('username', 'Username already taken.');
            return $this->getErrors();
        }

        if (User::find()->where(['email' => $this->email])->exists()) {
            $this->addError('email', 'This email has already been taken.');
            return $this->getErrors();
        }

        $user = new User();
        $user->username = $this->username;
        $user->fullname = $this->fullname;
        $user->email = $this->email;
        $user->generateAuthKey();
        $user->setPassword($this->password);
        return $user->save() ? $user : false;
    }
}