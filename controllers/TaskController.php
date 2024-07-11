<?php

namespace app\controllers;

use app\components\RedisHelper;
use app\components\UtilityHelper;
use app\controllers\Helper\BaseController;
use app\models\TaskImages;
use DateTime;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\db\Expression;
use yii\web\Response;

class TaskController extends BaseController
{

    public function behaviors(): array
    {
        return [
            [
                'class' => 'yii\filters\HttpCache',
                'only' => ['view'],
                'etagSeed' => function ($action, $params) {
                    $task = \Yii::$app->request->get('task');
                    $user = UtilityHelper::getUserInformation();
                    $post = UtilityHelper::loadTask($task, $user->username);
                    $flashMessages = \Yii::$app->session->getAllFlashes();
                    $flashMessageState = serialize($flashMessages);
                    return serialize([$post->updated_at, $flashMessageState]);
                },
            ],
        ];
    }


    public function actionView(string $task, bool $edit_mode)
    {
        $user = UtilityHelper::getUserInformation();
        $fullTask = UtilityHelper::loadTask($task, $user->username);
        if (!$fullTask || ($fullTask->assignee !== $user->username && $fullTask->created_by !== $user->username)) {
            Yii::$app->session->setFlash('error', '<b>Unauthorized</b>');
            return $this->redirect(['/']);
        }
        return $this->render('index', [
            'task' => $fullTask,
            'edit_mode' => $edit_mode
        ]);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function actionUpdate(string $task): Response
    {
        $fullTask = UtilityHelper::loadTask($task, UtilityHelper::getUserInformation()->username);
        if (!$fullTask) {
            Yii::$app->session->setFlash('error', 'Task not found.');
            return $this->goHome();
        }
        $minutesDifference = UtilityHelper::getTimeDifference($fullTask->updated_at);
        $status = Yii::$app->request->post('status');
        $currStatus = $fullTask->status;
        if ($currStatus === 'Deployed') {
            Yii::$app->session->setFlash('error', "Can't change the status once it is in deployed state.");
        }
        elseif (in_array($currStatus, array('In Progress', 'Testing')) && $status == 'New') {
            Yii::$app->session->setFlash('error', "Can't go back to the <code>New</code> state.");
        }
        elseif (!($currStatus == "New") && $minutesDifference < 15) {
            Yii::$app->session->setFlash('error', "Can't change the status now. Please try again after <b>" . (15 - $minutesDifference) . "</b> minute(s)");
        }
        else {
            $fullTask->status = $status;
            $fullTask->save();
            Yii::$app->session->setFlash('success', "Task updated successfully.");
        }
        return $this->redirect(['/task/view', 'task' => $task, 'edit_mode' => true]);
    }


    /**
     * @throws \Exception
     */
    public function actionDelete(string $task): Response
    {
        $user = UtilityHelper::getUserInformation();
        $fullTask = UtilityHelper::loadTask($task, $user->username);
        if (!$fullTask || ($fullTask->assignee !== $user->username && $fullTask->created_by !== $user->username)) {
            Yii::$app->session->setFlash('error', '<b>Unauthorized</b>');
            return $this->goHome();
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($fullTask->taskImages) {
                TaskImages::updateAll(
                    ['deleted_at' => new Expression('NOW()')],
                    ['task_id' => $fullTask->id]
                );
            }
            $fullTask->softDelete();
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Task deleted successfully.');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
        } catch (Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->goHome();
    }
}