<?php

namespace app\controllers;

use app\controllers\Helper\BaseController;
use app\controllers\Helper\RedisHelper;
use app\controllers\Helper\UtilityHelper;
use app\models\Task;
use DateTime;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\web\UnauthorizedHttpException;

class TaskController extends BaseController
{

    public function behaviors()
    {
        return [
            [
                'class' => 'yii\filters\HttpCache',
                'only' => ['view'],
                'etagSeed' => function ($action, $params) {
                    $task = \Yii::$app->request->get('task');
                    $userName = UtilityHelper::username();
                    $post = UtilityHelper::loadTaskWithImage($task, $userName);
                    return serialize([$post->updated_at]);
                },
            ],
        ];
    }


    public function actionView(string $task, bool $edit_mode)
    {
        $userName = UtilityHelper::username();
        $fullTask = UtilityHelper::loadTaskWithImage($task, $userName);
        if (!$fullTask || ($fullTask->assignee !== $userName && $fullTask->created_by !== $userName)) {
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
    public function actionUpdate(string $task)
    {
        $userName = UtilityHelper::username();
        $fullTask = UtilityHelper::loadTaskWithOutImage($task, $userName);

        if (!$fullTask){
            Yii::$app->session->setFlash('error', 'Task not found.');
            return $this->redirect(['/site/index']);
        }
        else{
            $status = Yii::$app->request->post('status');
            if ($fullTask->status === 'Deployed'){
                Yii::$app->session->setFlash('error', "Can't change the status once it is in deployed state.");
            }
            else{
                if ($fullTask->status === 'New' && $fullTask->created_at == $fullTask->updated_at){
                    $fullTask->status = $status;
                    $fullTask->save();
                    Yii::$app->cache->set(RedisHelper::SINGLE_TASK_WITHOUT_IMAGE_PREFIX.$task, $fullTask);
                    Yii::$app->session->setFlash('success', 'Task status updated successfully.');
                }
                else{
                    if (in_array($fullTask->status, array('In Progress', 'Testing')) && $status == 'New'){
                        Yii::$app->session->setFlash('error', "Can't go back to the <code>New</code> state.");
                    }
                    $updatedAt = new DateTime($fullTask->updated_at);
                    $currentTime = new DateTime();
                    $interval = $updatedAt->diff($currentTime);
                    $minutesDifference = $interval->days * 24 * 60 + $interval->h * 60 + $interval->i;
                    if ($minutesDifference < 15){
                        Yii::$app->session->setFlash('error', "Can't change the status now. Please try again after <b>".(15 - $minutesDifference)."</b> minute(s)");
                    }
                    else{
                        $fullTask->status = $status;
                        $fullTask->save();
                        Yii::$app->cache->set(RedisHelper::SINGLE_TASK_WITHOUT_IMAGE_PREFIX.$task, $fullTask);
                        Yii::$app->session->setFlash('success', 'Task status updated successfully.');
                    }
                }
            }
            return $this->redirect(['/task/view', 'task' => $task, 'edit_mode' => true]);
        }
    }


    /**
     * @throws \Exception
     */
    public function actionDelete(string $task){
        $userName = UtilityHelper::username();
        $fullTask = UtilityHelper::loadTaskWithImage($task, $userName);
        if (!$fullTask || ($fullTask->assignee !== $userName && $fullTask->created_by !== $userName)) {
            Yii::$app->session->setFlash('error', '<b>Unauthorized</b>');
            return $this->redirect(['/']);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try{
            foreach ($fullTask->taskImages as $taskImage) {
                if (!$taskImage->softDelete()) {
                    throw new \Exception('Failed to delete associated task images.');
                }
            }
            if (!$fullTask->softDelete()) {
                throw new \Exception('Failed to delete the task.');
            }
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Task deleted successfully.');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect(['/']);
        } catch (Throwable $e) {
        }
        return $this->redirect(['/']);
    }
}