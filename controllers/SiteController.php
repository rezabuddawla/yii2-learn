<?php

namespace app\controllers;

use app\controllers\Helper\BaseController;
use app\controllers\Helper\RedisHelper;
use app\models\RegisterForm;
use app\models\Task;
use app\models\TaskForm;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\web\Response;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\web\UploadedFile;

class SiteController extends BaseController
{

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        if (!Yii::$app->user->isGuest) {
            $currentUser = Yii::$app->user->identity;
            $cacheKeyUser = RedisHelper::DROPDOWN_USER_PREFIX.$currentUser->auth_key;
            $cacheKeyTask = RedisHelper::TASK_PREFIX.$currentUser->auth_key;
            if (!($users = Yii::$app->cache->get($cacheKeyUser))) {
                $users = User::find()
                    ->select(['username', 'fullname'])
                    ->where(['not', ['id' => $currentUser->id]])
                    ->asArray()
                    ->all();
                Yii::$app->cache->set($cacheKeyUser, $users, 3600);
            }

            if (!($tasks = Yii::$app->cache->get($cacheKeyTask))) {
                $tasks = Task::find()
                    ->select(['title', 'slug', 'priority', 'created_by', 'description'])
                    ->where(['assignee' => $currentUser->username])
                    ->andWhere(['task.deleted_at' => null]);
                Yii::$app->cache->set($cacheKeyTask, $tasks, 3600);
            }
            $model = new TaskForm();
            $tasks = new ActiveDataProvider([
                'query' => $tasks,
            ]);
            $ifHasTask = $tasks->getModels();
            return $this->render('index',[
                'users' => $users,
                'model' => $model,
                'tasks' => $ifHasTask ? $tasks : null,
            ]);
        }
        return $this->render('index');
    }


    public function action()
    {

    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * @throws \yii\base\Exception
     */
    public function actionRegister()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new RegisterForm();
        if ($model->load(Yii::$app->request->post()) && $model->register()) {
            Yii::$app->session->setFlash('success', 'Thank you for registering. Please log in.');
            return $this->redirect(['site/login']);
        }

        return $this->render('register', [
            'model' => $model,
        ]);

    }


    /**
     * @throws Exception
     */
    public function actionCreateTask()
    {
        $model = new TaskForm();

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $uploadedFiles = UploadedFile::getInstances($model, 'images');
            if (!empty($uploadedFiles)) {
                $model->images = $uploadedFiles;
            }
            if ($model->saveData()) {
                Yii::$app->session->setFlash('success', 'Task created successfully.');
            } else {
                Yii::$app->session->setFlash('error', 'Failed to create task.');
                $errors = $model->errors;
                Yii::error("Failed to create task: " . json_encode($errors));
            }
        }
        return $this->goHome();
    }


    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
