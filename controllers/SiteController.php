<?php

namespace app\controllers;

use app\components\TaskHelper;
use app\components\UtilityHelper;
use app\controllers\Helper\BaseController;
use app\models\LoginForm;
use app\models\RegisterForm;
use app\models\TaskForm;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\db\Query;
use yii\web\Response;
use yii\web\UploadedFile;

class SiteController extends BaseController
{

    public function actionIndex(): string
    {
        if (Yii::$app->user->isGuest) {
            return $this->render('index');
        }

        $tasks = new ActiveDataProvider([
            'query' => TaskHelper::getTasksQuery(),
        ]);
        return $this->render('index', [
            'model' => new TaskForm(),
            'tasks' => $tasks->getModels() ? $tasks : null,
        ]);
    }

    public function actionUserList($q = null, $id = null): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $out = ['results' => []];
        if (!is_null($q)) {
            $data = User::find()
                    ->select(['id' => 'username', 'text' => 'fullname'])
                    ->where(['like', 'fullname', $q])
                    ->andWhere(['!=', 'id', UtilityHelper::getUserInformation()->id])
                    ->limit(20)
                    ->asArray()
                    ->all();
            $out['results'] = array_values($data);
        }
        return $out;
    }

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
    public function actionCreateTask(): Response
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

    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
