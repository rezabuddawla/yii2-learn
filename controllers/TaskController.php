<?php

namespace app\controllers;

use app\controllers\Helper\BaseController;
use app\controllers\Helper\RedisHelper;
use app\controllers\Helper\UtilityHelper;
use app\models\Task;
use app\models\TaskImages;
use DateTime;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\db\Expression;
use yii\web\UnauthorizedHttpException;

class TaskController extends BaseController
{

//    public function behaviors()
//    {
//        return [
//            [
//                'class' => 'yii\filters\HttpCache',
//                'only' => ['view'],
//                'etagSeed' => function ($action, $params) {
//                    $task = \Yii::$app->request->get('task');
//                    $user = UtilityHelper::getUserInformation();
//                    $post = UtilityHelper::loadTaskWithImage($task, $user->username);
//                    return serialize([$post->updated_at]);
//                },
//            ],
//        ];
//    }


    public function actionView(string $task, bool $edit_mode)
    {
        $user = UtilityHelper::getUserInformation();
        $fullTask = UtilityHelper::loadTaskWithImage($task, $user->username);
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
    public function actionUpdate(string $task)
    {
        $user = UtilityHelper::getUserInformation();
        $fullTask = UtilityHelper::loadTaskWithOutImage($task, $user->username);
        if (!$fullTask){
            Yii::$app->session->setFlash('error', 'Task not found.');
            return $this->redirect(['/site/index']);
        }
        else{
            $flag = false;
            $status = Yii::$app->request->post('status');
            if ($fullTask->status === 'Deployed'){
                Yii::$app->session->setFlash('error', "Can't change the status once it is in deployed state.");
            }
            else{
                if ($fullTask->status === 'New' && $fullTask->created_at == $fullTask->updated_at){
                    $fullTask->status = $status;
                    $flag = true;
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
                        $flag = true;
                    }
                }
            }
            if ($flag){
                $fullTask->save();
                $fullTask->refresh();
                $taskWithImage = Yii::$app->cache->get(RedisHelper::SINGLE_TASK_WITH_IMAGE_PREFIX.$task);
                $taskWithImage->status = $status;
                $taskWithImage->updated_at = $fullTask->updated_at;
                Yii::$app->cache->set(RedisHelper::SINGLE_TASK_WITH_IMAGE_PREFIX.$task, $taskWithImage);
                Yii::$app->cache->set(RedisHelper::SINGLE_TASK_WITHOUT_IMAGE_PREFIX.$task, $fullTask);
                Yii::$app->session->setFlash('success', 'Task status updated successfully.');
            }
            return $this->redirect(['/task/view', 'task' => $task, 'edit_mode' => true]);
        }
    }


    /**
     * @throws \Exception
     */
    public function actionDelete(string $task){
        $user = UtilityHelper::getUserInformation();
        $fullTask = UtilityHelper::loadTaskWithImage($task, $user->username);
        if (!$fullTask || ($fullTask->assignee !== $user->username && $fullTask->created_by !== $user->username)) {
            Yii::$app->session->setFlash('error', '<b>Unauthorized</b>');
            return $this->redirect(['/']);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try{

//            Yii::getLogger()->flushInterval = 1;
//            Yii::getLogger()->messages = [];

            $taskImagesUpdate = TaskImages::updateAll(
                ['deleted_at' => New Expression('NOW()')],
                ['task_id' => $fullTask->id]
            );
            if (!$taskImagesUpdate) {
                throw new \Exception('Failed to delete associated task images.');
            }

            if (!$fullTask->softDelete()) {
                throw new \Exception('Failed to delete the task.');
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Task deleted successfully.');
            Yii::$app->cache->delete(RedisHelper::SINGLE_TASK_WITH_IMAGE_PREFIX.$task);
            Yii::$app->cache->delete(RedisHelper::SINGLE_TASK_WITHOUT_IMAGE_PREFIX.$task);

//            Yii::getLogger()->flush(true);
//            $logFile = Yii::getAlias('@runtime/logs/queries.log');
//            if (file_exists($logFile)) {
//                $queries = file_get_contents($logFile);
//                dd($queries);
//            }

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect(['/']);
        } catch (Throwable $e) {
        }
        return $this->redirect(['/']);
    }
}